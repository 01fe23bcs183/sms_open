<?php

namespace App\Services;

use App\Models\FeesType;
use App\Models\FeesGroup;
use App\Models\FeesMaster;
use App\Models\FeesDiscount;
use App\Models\FeesAllotment;
use App\Models\FeesFine;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Fees Service
 * 
 * Prompt 329: Create Fees Service
 * 
 * Centralizes fee calculation and collection rules. Manages fee groups,
 * discounts, fines, and dues. Generates invoices and updates fee statuses.
 */
class FeesService
{
    /**
     * Create a fee type.
     * 
     * @param array $data
     * @return FeesType
     */
    public function createFeeType(array $data): FeesType
    {
        return FeesType::create([
            'name' => $data['name'],
            'code' => $data['code'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * Create a fee group.
     * 
     * @param array $data
     * @return FeesGroup
     */
    public function createFeeGroup(array $data): FeesGroup
    {
        return FeesGroup::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * Create a fee master (fee configuration for class/section).
     * 
     * @param array $data
     * @return FeesMaster
     */
    public function createFeeMaster(array $data): FeesMaster
    {
        return FeesMaster::create([
            'fees_type_id' => $data['fees_type_id'],
            'fees_group_id' => $data['fees_group_id'] ?? null,
            'academic_session_id' => $data['academic_session_id'],
            'class_id' => $data['class_id'],
            'section_id' => $data['section_id'] ?? null,
            'amount' => $data['amount'],
            'due_date' => $data['due_date'],
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * Create a fee discount.
     * 
     * @param array $data
     * @return FeesDiscount
     */
    public function createDiscount(array $data): FeesDiscount
    {
        return FeesDiscount::create([
            'name' => $data['name'],
            'code' => $data['code'] ?? null,
            'discount_type' => $data['discount_type'], // 'percentage' or 'fixed'
            'discount_value' => $data['discount_value'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * Create a fee fine rule.
     * 
     * @param array $data
     * @return FeesFine
     */
    public function createFine(array $data): FeesFine
    {
        return FeesFine::create([
            'fees_type_id' => $data['fees_type_id'],
            'fine_type' => $data['fine_type'], // 'daily', 'weekly', 'monthly', 'one_time'
            'fine_amount' => $data['fine_amount'],
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * Allot fees to a student.
     * 
     * @param int $studentId
     * @param int $feesMasterId
     * @param int|null $discountId
     * @return FeesAllotment
     */
    public function allotFees(int $studentId, int $feesMasterId, ?int $discountId = null): FeesAllotment
    {
        $feesMaster = FeesMaster::findOrFail($feesMasterId);
        $discount = $discountId ? FeesDiscount::find($discountId) : null;
        
        $amount = $feesMaster->amount;
        $discountAmount = 0;
        
        if ($discount) {
            if ($discount->discount_type === 'percentage') {
                $discountAmount = ($amount * $discount->discount_value) / 100;
            } else {
                $discountAmount = $discount->discount_value;
            }
        }
        
        $netAmount = $amount - $discountAmount;
        
        return FeesAllotment::create([
            'student_id' => $studentId,
            'fees_master_id' => $feesMasterId,
            'fees_discount_id' => $discountId,
            'amount' => $amount,
            'discount_amount' => $discountAmount,
            'net_amount' => $netAmount,
            'paid_amount' => 0,
            'balance' => $netAmount,
            'due_date' => $feesMaster->due_date,
            'status' => 'unpaid',
        ]);
    }

    /**
     * Allot fees to all students in a class/section.
     * 
     * @param int $feesMasterId
     * @return int Number of allotments created
     */
    public function allotFeesToClass(int $feesMasterId): int
    {
        $feesMaster = FeesMaster::findOrFail($feesMasterId);
        
        $query = Student::where('class_id', $feesMaster->class_id)
            ->where('is_active', true);
        
        if ($feesMaster->section_id) {
            $query->where('section_id', $feesMaster->section_id);
        }
        
        $students = $query->get();
        $count = 0;
        
        DB::transaction(function () use ($students, $feesMasterId, &$count) {
            foreach ($students as $student) {
                // Check if already allotted
                $exists = FeesAllotment::where('student_id', $student->id)
                    ->where('fees_master_id', $feesMasterId)
                    ->exists();
                
                if (!$exists) {
                    $this->allotFees($student->id, $feesMasterId);
                    $count++;
                }
            }
        });
        
        return $count;
    }

    /**
     * Calculate total fees for a student.
     * 
     * @param int $studentId
     * @param int|null $sessionId
     * @return array
     */
    public function calculateStudentFees(int $studentId, ?int $sessionId = null): array
    {
        $query = FeesAllotment::with(['feesMaster.feesType', 'feesDiscount'])
            ->where('student_id', $studentId);
        
        if ($sessionId) {
            $query->whereHas('feesMaster', function ($q) use ($sessionId) {
                $q->where('academic_session_id', $sessionId);
            });
        }
        
        $allotments = $query->get();
        
        $totalAmount = 0;
        $totalDiscount = 0;
        $totalPaid = 0;
        $totalFine = 0;
        $totalBalance = 0;
        
        $fees = [];
        
        foreach ($allotments as $allotment) {
            $fine = $this->calculateFine($allotment);
            
            $fees[] = [
                'allotment_id' => $allotment->id,
                'fee_type' => $allotment->feesMaster->feesType->name ?? '',
                'amount' => $allotment->amount,
                'discount' => $allotment->discount_amount,
                'net_amount' => $allotment->net_amount,
                'paid' => $allotment->paid_amount,
                'fine' => $fine,
                'balance' => $allotment->balance + $fine,
                'due_date' => $allotment->due_date?->format('Y-m-d'),
                'status' => $allotment->status,
            ];
            
            $totalAmount += $allotment->amount;
            $totalDiscount += $allotment->discount_amount;
            $totalPaid += $allotment->paid_amount;
            $totalFine += $fine;
            $totalBalance += $allotment->balance;
        }
        
        return [
            'fees' => $fees,
            'summary' => [
                'total_amount' => $totalAmount,
                'total_discount' => $totalDiscount,
                'total_paid' => $totalPaid,
                'total_fine' => $totalFine,
                'total_balance' => $totalBalance + $totalFine,
            ],
        ];
    }

    /**
     * Calculate fine for an allotment.
     * 
     * @param FeesAllotment $allotment
     * @return float
     */
    public function calculateFine(FeesAllotment $allotment): float
    {
        if ($allotment->status === 'paid' || !$allotment->due_date) {
            return 0;
        }
        
        $dueDate = Carbon::parse($allotment->due_date);
        $today = now();
        
        if ($today->lte($dueDate)) {
            return 0;
        }
        
        $daysOverdue = $dueDate->diffInDays($today);
        
        // Get fine rule for this fee type
        $fineRule = FeesFine::where('fees_type_id', $allotment->feesMaster->fees_type_id)
            ->where('is_active', true)
            ->first();
        
        if (!$fineRule) {
            return 0;
        }
        
        $fine = 0;
        
        switch ($fineRule->fine_type) {
            case 'daily':
                $fine = $daysOverdue * $fineRule->fine_amount;
                break;
            case 'weekly':
                $weeksOverdue = ceil($daysOverdue / 7);
                $fine = $weeksOverdue * $fineRule->fine_amount;
                break;
            case 'monthly':
                $monthsOverdue = ceil($daysOverdue / 30);
                $fine = $monthsOverdue * $fineRule->fine_amount;
                break;
            case 'one_time':
                $fine = $fineRule->fine_amount;
                break;
        }
        
        return $fine;
    }

    /**
     * Get fee types.
     * 
     * @param bool $activeOnly
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFeeTypes(bool $activeOnly = true)
    {
        $query = FeesType::query();
        
        if ($activeOnly) {
            $query->where('is_active', true);
        }
        
        return $query->orderBy('name')->get();
    }

    /**
     * Get fee groups.
     * 
     * @param bool $activeOnly
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFeeGroups(bool $activeOnly = true)
    {
        $query = FeesGroup::query();
        
        if ($activeOnly) {
            $query->where('is_active', true);
        }
        
        return $query->orderBy('name')->get();
    }

    /**
     * Get fee masters for a class.
     * 
     * @param int $classId
     * @param int|null $sectionId
     * @param int|null $sessionId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFeeMasters(int $classId, ?int $sectionId = null, ?int $sessionId = null)
    {
        $query = FeesMaster::with(['feesType', 'feesGroup'])
            ->where('class_id', $classId)
            ->where('is_active', true);
        
        if ($sectionId) {
            $query->where(function ($q) use ($sectionId) {
                $q->where('section_id', $sectionId)
                  ->orWhereNull('section_id');
            });
        }
        
        if ($sessionId) {
            $query->where('academic_session_id', $sessionId);
        }
        
        return $query->get();
    }

    /**
     * Get discounts.
     * 
     * @param bool $activeOnly
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getDiscounts(bool $activeOnly = true)
    {
        $query = FeesDiscount::query();
        
        if ($activeOnly) {
            $query->where('is_active', true);
        }
        
        return $query->orderBy('name')->get();
    }

    /**
     * Get students with pending fees.
     * 
     * @param int|null $classId
     * @param int|null $sectionId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getStudentsWithPendingFees(?int $classId = null, ?int $sectionId = null)
    {
        $query = FeesAllotment::with(['student.user', 'student.schoolClass', 'student.section', 'feesMaster.feesType'])
            ->where('status', '!=', 'paid')
            ->where('balance', '>', 0);
        
        if ($classId) {
            $query->whereHas('student', function ($q) use ($classId) {
                $q->where('class_id', $classId);
            });
        }
        
        if ($sectionId) {
            $query->whereHas('student', function ($q) use ($sectionId) {
                $q->where('section_id', $sectionId);
            });
        }
        
        return $query->get();
    }

    /**
     * Get fee statistics.
     * 
     * @param int|null $sessionId
     * @return array
     */
    public function getStatistics(?int $sessionId = null): array
    {
        $query = FeesAllotment::query();
        
        if ($sessionId) {
            $query->whereHas('feesMaster', function ($q) use ($sessionId) {
                $q->where('academic_session_id', $sessionId);
            });
        }
        
        $totalAmount = (clone $query)->sum('net_amount');
        $totalPaid = (clone $query)->sum('paid_amount');
        $totalPending = $totalAmount - $totalPaid;
        $paidCount = (clone $query)->where('status', 'paid')->count();
        $pendingCount = (clone $query)->where('status', '!=', 'paid')->count();
        
        return [
            'total_amount' => $totalAmount,
            'total_paid' => $totalPaid,
            'total_pending' => $totalPending,
            'paid_count' => $paidCount,
            'pending_count' => $pendingCount,
            'collection_percentage' => $totalAmount > 0 ? round(($totalPaid / $totalAmount) * 100, 2) : 0,
        ];
    }
}
