<?php

namespace App\Services;

use App\Models\TransportRoute;
use App\Models\TransportRouteStop;
use App\Models\TransportVehicle;
use App\Models\TransportStudent;
use App\Models\Student;
use App\Services\FileUploadService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;

/**
 * Transport Service
 * 
 * Prompt 332: Create Transport Service
 * Prompt 412: Implement Transport Media Uploads
 * 
 * Manages transport routes and allocations. Assigns students to routes
 * and vehicles. Validates capacity, handles route fees, and manages
 * vehicle document uploads.
 */
class TransportService
{
    protected FileUploadService $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Create a transport route.
     * 
     * @param array $data
     * @return TransportRoute
     */
    public function createRoute(array $data): TransportRoute
    {
        return DB::transaction(function () use ($data) {
            $route = TransportRoute::create([
                'route_number' => $data['route_number'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'start_point' => $data['start_point'] ?? null,
                'end_point' => $data['end_point'] ?? null,
                'distance' => $data['distance'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);
            
            // Create stops if provided
            if (isset($data['stops']) && is_array($data['stops'])) {
                foreach ($data['stops'] as $index => $stopData) {
                    $this->addStop($route->id, array_merge($stopData, ['stop_order' => $index + 1]));
                }
            }
            
            return $route->load('stops');
        });
    }

    /**
     * Update a route.
     * 
     * @param TransportRoute $route
     * @param array $data
     * @return TransportRoute
     */
    public function updateRoute(TransportRoute $route, array $data): TransportRoute
    {
        $route->update($data);
        return $route->fresh();
    }

    /**
     * Delete a route.
     * 
     * @param TransportRoute $route
     * @return bool
     * @throws \Exception
     */
    public function deleteRoute(TransportRoute $route): bool
    {
        // Check if route has assigned students
        if (TransportStudent::where('route_id', $route->id)->exists()) {
            throw new \Exception('Cannot delete route with assigned students.');
        }
        
        return DB::transaction(function () use ($route) {
            // Delete stops
            $route->stops()->delete();
            
            // Unassign vehicles
            TransportVehicle::where('route_id', $route->id)->update(['route_id' => null]);
            
            return $route->delete();
        });
    }

    /**
     * Add a stop to a route.
     * 
     * @param int $routeId
     * @param array $data
     * @return TransportRouteStop
     */
    public function addStop(int $routeId, array $data): TransportRouteStop
    {
        $maxOrder = TransportRouteStop::where('route_id', $routeId)->max('stop_order') ?? 0;
        
        return TransportRouteStop::create([
            'route_id' => $routeId,
            'stop_name' => $data['stop_name'],
            'stop_order' => $data['stop_order'] ?? ($maxOrder + 1),
            'stop_time' => $data['stop_time'] ?? null,
            'fare' => $data['fare'] ?? 0,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
        ]);
    }

    /**
     * Update a stop.
     * 
     * @param TransportRouteStop $stop
     * @param array $data
     * @return TransportRouteStop
     */
    public function updateStop(TransportRouteStop $stop, array $data): TransportRouteStop
    {
        $stop->update($data);
        return $stop->fresh();
    }

    /**
     * Delete a stop.
     * 
     * @param TransportRouteStop $stop
     * @return bool
     * @throws \Exception
     */
    public function deleteStop(TransportRouteStop $stop): bool
    {
        // Check if stop has assigned students
        if (TransportStudent::where('stop_id', $stop->id)->exists()) {
            throw new \Exception('Cannot delete stop with assigned students.');
        }
        
        return $stop->delete();
    }

    /**
     * Create a vehicle.
     * 
     * @param array $data
     * @return TransportVehicle
     */
    public function createVehicle(array $data): TransportVehicle
    {
        return TransportVehicle::create([
            'vehicle_number' => $data['vehicle_number'],
            'vehicle_type' => $data['vehicle_type'] ?? 'bus',
            'capacity' => $data['capacity'],
            'driver_name' => $data['driver_name'] ?? null,
            'driver_phone' => $data['driver_phone'] ?? null,
            'driver_license' => $data['driver_license'] ?? null,
            'route_id' => $data['route_id'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * Update a vehicle.
     * 
     * @param TransportVehicle $vehicle
     * @param array $data
     * @return TransportVehicle
     */
    public function updateVehicle(TransportVehicle $vehicle, array $data): TransportVehicle
    {
        $vehicle->update($data);
        return $vehicle->fresh();
    }

    /**
     * Assign vehicle to a route.
     * 
     * @param int $vehicleId
     * @param int $routeId
     * @return TransportVehicle
     */
    public function assignVehicleToRoute(int $vehicleId, int $routeId): TransportVehicle
    {
        $vehicle = TransportVehicle::findOrFail($vehicleId);
        $vehicle->update(['route_id' => $routeId]);
        return $vehicle->fresh();
    }

    /**
     * Assign student to transport.
     * 
     * @param int $studentId
     * @param int $routeId
     * @param int $stopId
     * @param int $academicSessionId
     * @param int|null $vehicleId
     * @param float|null $transportFees
     * @return TransportStudent
     * @throws \Exception
     */
    public function assignStudent(
        int $studentId,
        int $routeId,
        int $stopId,
        int $academicSessionId,
        ?int $vehicleId = null,
        ?float $transportFees = null
    ): TransportStudent {
        // Check if student is already assigned for this session
        $existing = TransportStudent::where('student_id', $studentId)
            ->where('academic_session_id', $academicSessionId)
            ->first();
        
        if ($existing) {
            throw new \Exception('Student is already assigned to transport for this session.');
        }
        
        // Get fare from stop if not provided
        if ($transportFees === null) {
            $stop = TransportRouteStop::findOrFail($stopId);
            $transportFees = $stop->fare;
        }
        
        // Check vehicle capacity if vehicle is specified
        if ($vehicleId) {
            $this->checkVehicleCapacity($vehicleId);
        }
        
        return TransportStudent::create([
            'student_id' => $studentId,
            'route_id' => $routeId,
            'stop_id' => $stopId,
            'vehicle_id' => $vehicleId,
            'academic_session_id' => $academicSessionId,
            'transport_fees' => $transportFees,
        ]);
    }

    /**
     * Update student transport assignment.
     * 
     * @param int $transportStudentId
     * @param array $data
     * @return TransportStudent
     */
    public function updateStudentAssignment(int $transportStudentId, array $data): TransportStudent
    {
        $assignment = TransportStudent::findOrFail($transportStudentId);
        
        // Check vehicle capacity if changing vehicle
        if (isset($data['vehicle_id']) && $data['vehicle_id'] !== $assignment->vehicle_id) {
            $this->checkVehicleCapacity($data['vehicle_id']);
        }
        
        $assignment->update($data);
        return $assignment->fresh();
    }

    /**
     * Remove student from transport.
     * 
     * @param int $transportStudentId
     * @return bool
     */
    public function removeStudent(int $transportStudentId): bool
    {
        return TransportStudent::findOrFail($transportStudentId)->delete();
    }

    /**
     * Check vehicle capacity.
     * 
     * @param int $vehicleId
     * @throws \Exception
     */
    private function checkVehicleCapacity(int $vehicleId): void
    {
        $vehicle = TransportVehicle::findOrFail($vehicleId);
        $currentCount = TransportStudent::where('vehicle_id', $vehicleId)->count();
        
        if ($currentCount >= $vehicle->capacity) {
            throw new \Exception("Vehicle has reached maximum capacity ({$vehicle->capacity}).");
        }
    }

    /**
     * Get all routes.
     * 
     * @param bool $activeOnly
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRoutes(bool $activeOnly = true)
    {
        $query = TransportRoute::with(['stops', 'vehicles']);
        
        if ($activeOnly) {
            $query->where('is_active', true);
        }
        
        return $query->orderBy('route_number')->get();
    }

    /**
     * Get route with details.
     * 
     * @param int $routeId
     * @return TransportRoute
     */
    public function getRouteDetails(int $routeId): TransportRoute
    {
        return TransportRoute::with(['stops' => function ($q) {
            $q->orderBy('stop_order');
        }, 'vehicles', 'students.student.user'])
            ->findOrFail($routeId);
    }

    /**
     * Get all vehicles.
     * 
     * @param bool $activeOnly
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getVehicles(bool $activeOnly = true)
    {
        $query = TransportVehicle::with('route');
        
        if ($activeOnly) {
            $query->where('is_active', true);
        }
        
        return $query->orderBy('vehicle_number')->get();
    }

    /**
     * Get students on a route.
     * 
     * @param int $routeId
     * @param int|null $sessionId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRouteStudents(int $routeId, ?int $sessionId = null)
    {
        $query = TransportStudent::with(['student.user', 'student.schoolClass', 'student.section', 'stop', 'vehicle'])
            ->where('route_id', $routeId);
        
        if ($sessionId) {
            $query->where('academic_session_id', $sessionId);
        }
        
        return $query->get();
    }

    /**
     * Get students on a vehicle.
     * 
     * @param int $vehicleId
     * @param int|null $sessionId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getVehicleStudents(int $vehicleId, ?int $sessionId = null)
    {
        $query = TransportStudent::with(['student.user', 'student.schoolClass', 'student.section', 'stop', 'route'])
            ->where('vehicle_id', $vehicleId);
        
        if ($sessionId) {
            $query->where('academic_session_id', $sessionId);
        }
        
        return $query->get();
    }

    /**
     * Get student's transport assignment.
     * 
     * @param int $studentId
     * @param int|null $sessionId
     * @return TransportStudent|null
     */
    public function getStudentTransport(int $studentId, ?int $sessionId = null): ?TransportStudent
    {
        $query = TransportStudent::with(['route', 'stop', 'vehicle'])
            ->where('student_id', $studentId);
        
        if ($sessionId) {
            $query->where('academic_session_id', $sessionId);
        }
        
        return $query->first();
    }

    /**
     * Get transport statistics.
     * 
     * @return array
     */
    public function getStatistics(): array
    {
        $totalRoutes = TransportRoute::where('is_active', true)->count();
        $totalVehicles = TransportVehicle::where('is_active', true)->count();
        $totalCapacity = TransportVehicle::where('is_active', true)->sum('capacity');
        $totalStudents = TransportStudent::count();
        $totalStops = TransportRouteStop::count();
        
        return [
            'total_routes' => $totalRoutes,
            'total_vehicles' => $totalVehicles,
            'total_capacity' => $totalCapacity,
            'total_students' => $totalStudents,
            'total_stops' => $totalStops,
            'capacity_utilization' => $totalCapacity > 0 ? round(($totalStudents / $totalCapacity) * 100, 2) : 0,
        ];
    }

    /**
     * Generate transport report data.
     * 
     * @param int|null $routeId
     * @param int|null $sessionId
     * @return array
     */
    public function getReportData(?int $routeId = null, ?int $sessionId = null): array
    {
        $query = TransportStudent::with(['student.user', 'student.schoolClass', 'student.section', 'route', 'stop', 'vehicle']);
        
        if ($routeId) {
            $query->where('route_id', $routeId);
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
                'route' => $assignment->route->name ?? '',
                'stop' => $assignment->stop->stop_name ?? '',
                'vehicle' => $assignment->vehicle->vehicle_number ?? '',
                'fees' => $assignment->transport_fees,
            ];
        })->toArray();
    }

    /**
     * Upload vehicle document.
     * 
     * Prompt 412: Implement Transport Media Uploads
     * 
     * @param TransportVehicle $vehicle
     * @param UploadedFile $file
     * @param string $documentType
     * @return array Upload result with path and URL
     */
    public function uploadVehicleDocument(
        TransportVehicle $vehicle,
        UploadedFile $file,
        string $documentType = 'general'
    ): array {
        // Validate the file
        $validation = $this->fileUploadService->validate($file, 'vehicle_document');
        if (!$validation['valid']) {
            throw new \Exception(implode(', ', $validation['errors']));
        }

        // Upload document using FileUploadService
        $result = $this->fileUploadService->uploadVehicleDocument($file, $vehicle->id);

        // Store document info in vehicle's documents JSON field
        $documents = $vehicle->documents ?? [];
        $documents[$documentType] = [
            'path' => $result['path'],
            'original_name' => $result['original_name'],
            'mime_type' => $result['mime_type'],
            'size' => $result['size'],
            'uploaded_at' => now()->toISOString(),
        ];
        $vehicle->update(['documents' => $documents]);

        return $result;
    }

    /**
     * Upload vehicle image.
     * 
     * Prompt 412: Implement Transport Media Uploads
     * 
     * @param TransportVehicle $vehicle
     * @param UploadedFile $file
     * @return array Upload result with path and URL
     */
    public function uploadVehicleImage(TransportVehicle $vehicle, UploadedFile $file): array
    {
        // Validate the file
        $validation = $this->fileUploadService->validate($file, 'vehicle_image');
        if (!$validation['valid']) {
            throw new \Exception(implode(', ', $validation['errors']));
        }

        // Delete old image if exists
        if ($vehicle->image) {
            $this->fileUploadService->delete($vehicle->image, 'public_uploads');
        }

        // Upload new image
        $result = $this->fileUploadService->uploadPublic(
            $file,
            'transport/vehicles',
            ['prefix' => "vehicle_{$vehicle->id}"]
        );

        // Update vehicle record
        $vehicle->update(['image' => $result['path']]);

        return $result;
    }

    /**
     * Delete vehicle document.
     * 
     * Prompt 412: Implement Transport Media Uploads
     * 
     * @param TransportVehicle $vehicle
     * @param string $documentType
     * @return bool
     */
    public function deleteVehicleDocument(TransportVehicle $vehicle, string $documentType): bool
    {
        $documents = $vehicle->documents ?? [];
        
        if (!isset($documents[$documentType])) {
            return false;
        }

        // Delete file from storage
        $this->fileUploadService->delete($documents[$documentType]['path'], 'private_uploads');

        // Remove from documents array
        unset($documents[$documentType]);
        $vehicle->update(['documents' => $documents]);

        return true;
    }

    /**
     * Get vehicle documents.
     * 
     * Prompt 412: Implement Transport Media Uploads
     * 
     * @param TransportVehicle $vehicle
     * @return array
     */
    public function getVehicleDocuments(TransportVehicle $vehicle): array
    {
        return $vehicle->documents ?? [];
    }

    /**
     * Create vehicle with image.
     * 
     * Prompt 412: Implement Transport Media Uploads
     * 
     * @param array $data
     * @param UploadedFile|null $image
     * @return TransportVehicle
     */
    public function createVehicleWithImage(array $data, ?UploadedFile $image = null): TransportVehicle
    {
        return DB::transaction(function () use ($data, $image) {
            $vehicle = $this->createVehicle($data);

            if ($image instanceof UploadedFile) {
                $this->uploadVehicleImage($vehicle, $image);
            }

            return $vehicle->fresh();
        });
    }
}
