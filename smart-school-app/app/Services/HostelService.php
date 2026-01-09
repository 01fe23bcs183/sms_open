<?php

namespace App\Services;

use App\Models\Hostel;
use App\Models\HostelRoomType;
use App\Models\HostelRoom;
use App\Models\HostelAssignment;
use App\Models\Student;
use App\Services\FileUploadService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;

/**
 * Hostel Service
 * 
 * Prompt 333: Create Hostel Service
 * Prompt 412: Implement Hostel Media Uploads
 * 
 * Manages hostel rooms and allocations. Assigns rooms and tracks occupancy.
 * Handles hostel fees, maintains occupancy counts, and manages hostel
 * and room image uploads.
 */
class HostelService
{
    protected FileUploadService $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Create a hostel.
     * 
     * @param array $data
     * @return Hostel
     */
    public function createHostel(array $data): Hostel
    {
        return Hostel::create([
            'name' => $data['name'],
            'type' => $data['type'] ?? 'boys', // 'boys', 'girls', 'mixed'
            'address' => $data['address'] ?? null,
            'warden_name' => $data['warden_name'] ?? null,
            'warden_phone' => $data['warden_phone'] ?? null,
            'capacity' => $data['capacity'] ?? 0,
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * Update a hostel.
     * 
     * @param Hostel $hostel
     * @param array $data
     * @return Hostel
     */
    public function updateHostel(Hostel $hostel, array $data): Hostel
    {
        $hostel->update($data);
        return $hostel->fresh();
    }

    /**
     * Create a room type.
     * 
     * @param array $data
     * @return HostelRoomType
     */
    public function createRoomType(array $data): HostelRoomType
    {
        return HostelRoomType::create([
            'name' => $data['name'],
            'capacity' => $data['capacity'],
            'monthly_fee' => $data['monthly_fee'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * Create a room.
     * 
     * @param array $data
     * @return HostelRoom
     */
    public function createRoom(array $data): HostelRoom
    {
        return DB::transaction(function () use ($data) {
            $roomType = HostelRoomType::findOrFail($data['room_type_id']);
            
            $room = HostelRoom::create([
                'hostel_id' => $data['hostel_id'],
                'room_type_id' => $data['room_type_id'],
                'room_number' => $data['room_number'],
                'floor' => $data['floor'] ?? null,
                'capacity' => $data['capacity'] ?? $roomType->capacity,
                'current_occupancy' => 0,
                'description' => $data['description'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);
            
            // Update hostel capacity
            $this->updateHostelCapacity($data['hostel_id']);
            
            return $room;
        });
    }

    /**
     * Update a room.
     * 
     * @param HostelRoom $room
     * @param array $data
     * @return HostelRoom
     * @throws \Exception
     */
    public function updateRoom(HostelRoom $room, array $data): HostelRoom
    {
        // Check if reducing capacity below current occupancy
        if (isset($data['capacity']) && $data['capacity'] < $room->current_occupancy) {
            throw new \Exception('Cannot reduce capacity below current occupancy.');
        }
        
        $room->update($data);
        
        // Update hostel capacity if needed
        $this->updateHostelCapacity($room->hostel_id);
        
        return $room->fresh();
    }

    /**
     * Assign student to a room.
     * 
     * @param int $studentId
     * @param int $roomId
     * @param int $academicSessionId
     * @param float|null $monthlyFee
     * @return HostelAssignment
     * @throws \Exception
     */
    public function assignStudent(
        int $studentId,
        int $roomId,
        int $academicSessionId,
        ?float $monthlyFee = null
    ): HostelAssignment {
        return DB::transaction(function () use ($studentId, $roomId, $academicSessionId, $monthlyFee) {
            $room = HostelRoom::with('roomType')->findOrFail($roomId);
            
            // Check room availability
            if ($room->current_occupancy >= $room->capacity) {
                throw new \Exception('Room is at full capacity.');
            }
            
            // Check if student is already assigned for this session
            $existing = HostelAssignment::where('student_id', $studentId)
                ->where('academic_session_id', $academicSessionId)
                ->whereNull('checkout_date')
                ->first();
            
            if ($existing) {
                throw new \Exception('Student is already assigned to a hostel room for this session.');
            }
            
            // Get fee from room type if not provided
            if ($monthlyFee === null) {
                $monthlyFee = $room->roomType->monthly_fee ?? 0;
            }
            
            // Create assignment
            $assignment = HostelAssignment::create([
                'student_id' => $studentId,
                'room_id' => $roomId,
                'academic_session_id' => $academicSessionId,
                'checkin_date' => now(),
                'monthly_fee' => $monthlyFee,
            ]);
            
            // Update room occupancy
            $room->increment('current_occupancy');
            
            return $assignment->load(['student.user', 'room.hostel']);
        });
    }

    /**
     * Checkout student from hostel.
     * 
     * @param int $assignmentId
     * @param string|null $reason
     * @return HostelAssignment
     */
    public function checkoutStudent(int $assignmentId, ?string $reason = null): HostelAssignment
    {
        return DB::transaction(function () use ($assignmentId, $reason) {
            $assignment = HostelAssignment::with('room')->findOrFail($assignmentId);
            
            if ($assignment->checkout_date) {
                throw new \Exception('Student has already been checked out.');
            }
            
            $assignment->update([
                'checkout_date' => now(),
                'checkout_reason' => $reason,
            ]);
            
            // Update room occupancy
            $assignment->room->decrement('current_occupancy');
            
            return $assignment->fresh();
        });
    }

    /**
     * Transfer student to another room.
     * 
     * @param int $assignmentId
     * @param int $newRoomId
     * @param string|null $reason
     * @return HostelAssignment
     */
    public function transferStudent(int $assignmentId, int $newRoomId, ?string $reason = null): HostelAssignment
    {
        return DB::transaction(function () use ($assignmentId, $newRoomId, $reason) {
            $assignment = HostelAssignment::with('room')->findOrFail($assignmentId);
            $newRoom = HostelRoom::with('roomType')->findOrFail($newRoomId);
            
            // Check new room availability
            if ($newRoom->current_occupancy >= $newRoom->capacity) {
                throw new \Exception('Target room is at full capacity.');
            }
            
            // Update old room occupancy
            $assignment->room->decrement('current_occupancy');
            
            // Update assignment
            $assignment->update([
                'room_id' => $newRoomId,
                'monthly_fee' => $newRoom->roomType->monthly_fee ?? $assignment->monthly_fee,
            ]);
            
            // Update new room occupancy
            $newRoom->increment('current_occupancy');
            
            return $assignment->fresh(['student.user', 'room.hostel']);
        });
    }

    /**
     * Get all hostels.
     * 
     * @param bool $activeOnly
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getHostels(bool $activeOnly = true)
    {
        $query = Hostel::with(['rooms.roomType']);
        
        if ($activeOnly) {
            $query->where('is_active', true);
        }
        
        return $query->orderBy('name')->get();
    }

    /**
     * Get hostel with details.
     * 
     * @param int $hostelId
     * @return Hostel
     */
    public function getHostelDetails(int $hostelId): Hostel
    {
        return Hostel::with(['rooms.roomType', 'rooms.assignments.student.user'])
            ->findOrFail($hostelId);
    }

    /**
     * Get room types.
     * 
     * @param bool $activeOnly
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRoomTypes(bool $activeOnly = true)
    {
        $query = HostelRoomType::query();
        
        if ($activeOnly) {
            $query->where('is_active', true);
        }
        
        return $query->orderBy('name')->get();
    }

    /**
     * Get available rooms.
     * 
     * @param int|null $hostelId
     * @param int|null $roomTypeId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAvailableRooms(?int $hostelId = null, ?int $roomTypeId = null)
    {
        $query = HostelRoom::with(['hostel', 'roomType'])
            ->where('is_active', true)
            ->whereRaw('current_occupancy < capacity');
        
        if ($hostelId) {
            $query->where('hostel_id', $hostelId);
        }
        
        if ($roomTypeId) {
            $query->where('room_type_id', $roomTypeId);
        }
        
        return $query->orderBy('room_number')->get();
    }

    /**
     * Get room occupants.
     * 
     * @param int $roomId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRoomOccupants(int $roomId)
    {
        return HostelAssignment::with(['student.user', 'student.schoolClass', 'student.section'])
            ->where('room_id', $roomId)
            ->whereNull('checkout_date')
            ->get();
    }

    /**
     * Get student's hostel assignment.
     * 
     * @param int $studentId
     * @param int|null $sessionId
     * @return HostelAssignment|null
     */
    public function getStudentAssignment(int $studentId, ?int $sessionId = null): ?HostelAssignment
    {
        $query = HostelAssignment::with(['room.hostel', 'room.roomType'])
            ->where('student_id', $studentId)
            ->whereNull('checkout_date');
        
        if ($sessionId) {
            $query->where('academic_session_id', $sessionId);
        }
        
        return $query->first();
    }

    /**
     * Get hostel statistics.
     * 
     * @return array
     */
    public function getStatistics(): array
    {
        $totalHostels = Hostel::where('is_active', true)->count();
        $totalRooms = HostelRoom::where('is_active', true)->count();
        $totalCapacity = HostelRoom::where('is_active', true)->sum('capacity');
        $totalOccupancy = HostelRoom::where('is_active', true)->sum('current_occupancy');
        $availableRooms = HostelRoom::where('is_active', true)
            ->whereRaw('current_occupancy < capacity')
            ->count();
        
        return [
            'total_hostels' => $totalHostels,
            'total_rooms' => $totalRooms,
            'total_capacity' => $totalCapacity,
            'total_occupancy' => $totalOccupancy,
            'available_rooms' => $availableRooms,
            'occupancy_percentage' => $totalCapacity > 0 ? round(($totalOccupancy / $totalCapacity) * 100, 2) : 0,
        ];
    }

    /**
     * Update hostel capacity based on rooms.
     * 
     * @param int $hostelId
     * @return void
     */
    private function updateHostelCapacity(int $hostelId): void
    {
        $totalCapacity = HostelRoom::where('hostel_id', $hostelId)
            ->where('is_active', true)
            ->sum('capacity');
        
        Hostel::where('id', $hostelId)->update(['capacity' => $totalCapacity]);
    }

    /**
     * Generate hostel report data.
     * 
     * @param int|null $hostelId
     * @param int|null $sessionId
     * @return array
     */
    public function getReportData(?int $hostelId = null, ?int $sessionId = null): array
    {
        $query = HostelAssignment::with(['student.user', 'student.schoolClass', 'student.section', 'room.hostel', 'room.roomType'])
            ->whereNull('checkout_date');
        
        if ($hostelId) {
            $query->whereHas('room', function ($q) use ($hostelId) {
                $q->where('hostel_id', $hostelId);
            });
        }
        
        if ($sessionId) {
            $query->where('academic_session_id', $sessionId);
        }
        
        $assignments = $query->get();
        
        return $assignments->map(function ($assignment) {
            return [
                'student_name' => $assignment->student->user->full_name ?? '',
                'admission_number' => $assignment->student->admission_number,
                'class' => $assignment->student->schoolClass->name ?? '',
                'section' => $assignment->student->section->name ?? '',
                'hostel' => $assignment->room->hostel->name ?? '',
                'room' => $assignment->room->room_number ?? '',
                'room_type' => $assignment->room->roomType->name ?? '',
                'checkin_date' => $assignment->checkin_date?->format('Y-m-d'),
                'monthly_fee' => $assignment->monthly_fee,
            ];
        })->toArray();
    }

    /**
     * Upload hostel image.
     * 
     * Prompt 412: Implement Hostel Media Uploads
     * 
     * @param Hostel $hostel
     * @param UploadedFile $file
     * @return array Upload result with path and URL
     */
    public function uploadHostelImage(Hostel $hostel, UploadedFile $file): array
    {
        // Validate the file
        $validation = $this->fileUploadService->validate($file, 'hostel_image');
        if (!$validation['valid']) {
            throw new \Exception(implode(', ', $validation['errors']));
        }

        // Delete old image if exists
        if ($hostel->image) {
            $this->fileUploadService->delete($hostel->image, 'public_uploads');
        }

        // Upload new image
        $result = $this->fileUploadService->uploadPublic(
            $file,
            'hostel/buildings',
            ['prefix' => "hostel_{$hostel->id}"]
        );

        // Update hostel record
        $hostel->update(['image' => $result['path']]);

        return $result;
    }

    /**
     * Upload room image.
     * 
     * Prompt 412: Implement Hostel Media Uploads
     * 
     * @param HostelRoom $room
     * @param UploadedFile $file
     * @return array Upload result with path and URL
     */
    public function uploadRoomImage(HostelRoom $room, UploadedFile $file): array
    {
        // Validate the file
        $validation = $this->fileUploadService->validate($file, 'room_image');
        if (!$validation['valid']) {
            throw new \Exception(implode(', ', $validation['errors']));
        }

        // Delete old image if exists
        if ($room->image) {
            $this->fileUploadService->delete($room->image, 'public_uploads');
        }

        // Upload new image
        $result = $this->fileUploadService->uploadPublic(
            $file,
            'hostel/rooms',
            ['prefix' => "room_{$room->id}"]
        );

        // Update room record
        $room->update(['image' => $result['path']]);

        return $result;
    }

    /**
     * Create hostel with image.
     * 
     * Prompt 412: Implement Hostel Media Uploads
     * 
     * @param array $data
     * @param UploadedFile|null $image
     * @return Hostel
     */
    public function createHostelWithImage(array $data, ?UploadedFile $image = null): Hostel
    {
        return DB::transaction(function () use ($data, $image) {
            $hostel = $this->createHostel($data);

            if ($image instanceof UploadedFile) {
                $this->uploadHostelImage($hostel, $image);
            }

            return $hostel->fresh();
        });
    }

    /**
     * Create room with image.
     * 
     * Prompt 412: Implement Hostel Media Uploads
     * 
     * @param array $data
     * @param UploadedFile|null $image
     * @return HostelRoom
     */
    public function createRoomWithImage(array $data, ?UploadedFile $image = null): HostelRoom
    {
        return DB::transaction(function () use ($data, $image) {
            $room = $this->createRoom($data);

            if ($image instanceof UploadedFile) {
                $this->uploadRoomImage($room, $image);
            }

            return $room->fresh();
        });
    }

    /**
     * Delete hostel image.
     * 
     * Prompt 412: Implement Hostel Media Uploads
     * 
     * @param Hostel $hostel
     * @return bool
     */
    public function deleteHostelImage(Hostel $hostel): bool
    {
        if (!$hostel->image) {
            return false;
        }

        // Delete file from storage
        $this->fileUploadService->delete($hostel->image, 'public_uploads');

        // Update hostel record
        $hostel->update(['image' => null]);

        return true;
    }

    /**
     * Delete room image.
     * 
     * Prompt 412: Implement Hostel Media Uploads
     * 
     * @param HostelRoom $room
     * @return bool
     */
    public function deleteRoomImage(HostelRoom $room): bool
    {
        if (!$room->image) {
            return false;
        }

        // Delete file from storage
        $this->fileUploadService->delete($room->image, 'public_uploads');

        // Update room record
        $room->update(['image' => null]);

        return true;
    }

    /**
     * Upload multiple hostel gallery images.
     * 
     * Prompt 412: Implement Hostel Media Uploads
     * 
     * @param Hostel $hostel
     * @param array $files Array of UploadedFile objects
     * @return array Array of upload results
     */
    public function uploadHostelGallery(Hostel $hostel, array $files): array
    {
        $results = [];
        $gallery = $hostel->gallery ?? [];

        foreach ($files as $file) {
            if (!($file instanceof UploadedFile)) {
                continue;
            }

            // Validate the file
            $validation = $this->fileUploadService->validate($file, 'hostel_image');
            if (!$validation['valid']) {
                continue;
            }

            // Upload image
            $result = $this->fileUploadService->uploadPublic(
                $file,
                'hostel/gallery',
                ['prefix' => "hostel_{$hostel->id}"]
            );

            $gallery[] = [
                'path' => $result['path'],
                'original_name' => $result['original_name'],
                'uploaded_at' => now()->toISOString(),
            ];

            $results[] = $result;
        }

        // Update hostel gallery
        $hostel->update(['gallery' => $gallery]);

        return $results;
    }

    /**
     * Delete hostel gallery image.
     * 
     * Prompt 412: Implement Hostel Media Uploads
     * 
     * @param Hostel $hostel
     * @param int $index Gallery image index
     * @return bool
     */
    public function deleteHostelGalleryImage(Hostel $hostel, int $index): bool
    {
        $gallery = $hostel->gallery ?? [];

        if (!isset($gallery[$index])) {
            return false;
        }

        // Delete file from storage
        $this->fileUploadService->delete($gallery[$index]['path'], 'public_uploads');

        // Remove from gallery array
        array_splice($gallery, $index, 1);
        $hostel->update(['gallery' => $gallery]);

        return true;
    }
}
