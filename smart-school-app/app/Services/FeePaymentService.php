<?php

namespace App\Services;

use App\Models\FeesTransaction;
use App\Models\FeesAllotment;
use App\Models\PaymentProof;
use App\Models\Student;
use App\Services\FileUploadService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Carbon\Carbon;

/**
 * Fee Payment Service
 * 
 * Prompt 330: Create Fee Payment Service
 * Prompt 407: Implement Fee Payment Proof Upload
 * 
 * Isolates payment recording and ledger updates. Stores transactions,
 * updates balances, writes accounting ledger entries, and handles
 * payment proof uploads.
 */
class FeePaymentService
{
    protected FileUploadService $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Record a fee payment.
     * 
     * @param int $studentId
     * @param int $feesAllotmentId
     * @param float $amount
     * @param string $paymentMethod
     * @param int|null $receivedBy
     * @param array $additionalData
     * @return FeesTransaction
     */
    public function recordPayment(
        int $studentId,
        int $feesAllotmentId,
        float $amount,
        string $paymentMethod,
        ?int $receivedBy = null,
        array $additionalData = []
    ): FeesTransaction {
        return DB::transaction(function () use ($studentId, $feesAllotmentId, $amount, $paymentMethod, $receivedBy, $additionalData) {
            $allotment = FeesAllotment::findOrFail($feesAllotmentId);
            
            // Generate transaction ID
            $transactionId = $this->generateTransactionId();
            
            // Create transaction record
            $transaction = FeesTransaction::create([
                'student_id' => $studentId,
                'fees_allotment_id' => $feesAllotmentId,
                'transaction_id' => $transactionId,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'payment_status' => 'completed',
                'payment_date' => now(),
                'transaction_date' => now(),
                'reference_number' => $additionalData['reference_number'] ?? null,
                'bank_name' => $additionalData['bank_name'] ?? null,
                'cheque_number' => $additionalData['cheque_number'] ?? null,
                'remarks' => $additionalData['remarks'] ?? null,
                'received_by' => $receivedBy,
            ]);
            
            // Update allotment
            $this->updateAllotmentBalance($allotment, $amount);
            
            return $transaction;
        });
    }

    /**
     * Record a partial payment.
     * 
     * @param int $studentId
     * @param int $feesAllotmentId
     * @param float $amount
     * @param string $paymentMethod
     * @param int|null $receivedBy
     * @param array $additionalData
     * @return FeesTransaction
     */
    public function recordPartialPayment(
        int $studentId,
        int $feesAllotmentId,
        float $amount,
        string $paymentMethod,
        ?int $receivedBy = null,
        array $additionalData = []
    ): FeesTransaction {
        return $this->recordPayment($studentId, $feesAllotmentId, $amount, $paymentMethod, $receivedBy, $additionalData);
    }

    /**
     * Record a bulk payment for multiple allotments.
     * 
     * @param int $studentId
     * @param array $payments Array of ['allotment_id' => amount]
     * @param string $paymentMethod
     * @param int|null $receivedBy
     * @param array $additionalData
     * @return array
     */
    public function recordBulkPayment(
        int $studentId,
        array $payments,
        string $paymentMethod,
        ?int $receivedBy = null,
        array $additionalData = []
    ): array {
        $transactions = [];
        
        DB::transaction(function () use ($studentId, $payments, $paymentMethod, $receivedBy, $additionalData, &$transactions) {
            foreach ($payments as $allotmentId => $amount) {
                if ($amount > 0) {
                    $transactions[] = $this->recordPayment(
                        $studentId,
                        $allotmentId,
                        $amount,
                        $paymentMethod,
                        $receivedBy,
                        $additionalData
                    );
                }
            }
        });
        
        return $transactions;
    }

    /**
     * Process a refund.
     * 
     * @param int $transactionId
     * @param float|null $amount
     * @param string|null $reason
     * @param int|null $processedBy
     * @return FeesTransaction
     */
    public function processRefund(
        int $transactionId,
        ?float $amount = null,
        ?string $reason = null,
        ?int $processedBy = null
    ): FeesTransaction {
        return DB::transaction(function () use ($transactionId, $amount, $reason, $processedBy) {
            $originalTransaction = FeesTransaction::findOrFail($transactionId);
            $refundAmount = $amount ?? $originalTransaction->amount;
            
            // Create refund transaction
            $refundTransaction = FeesTransaction::create([
                'student_id' => $originalTransaction->student_id,
                'fees_allotment_id' => $originalTransaction->fees_allotment_id,
                'transaction_id' => $this->generateTransactionId('REF'),
                'amount' => -$refundAmount,
                'payment_method' => $originalTransaction->payment_method,
                'payment_status' => 'refunded',
                'payment_date' => now(),
                'transaction_date' => now(),
                'reference_number' => $originalTransaction->transaction_id,
                'remarks' => $reason ?? 'Refund for transaction ' . $originalTransaction->transaction_id,
                'received_by' => $processedBy,
            ]);
            
            // Update original transaction status
            $originalTransaction->update(['payment_status' => 'refunded']);
            
            // Update allotment balance
            $allotment = FeesAllotment::find($originalTransaction->fees_allotment_id);
            if ($allotment) {
                $this->updateAllotmentBalance($allotment, -$refundAmount);
            }
            
            return $refundTransaction;
        });
    }

    /**
     * Cancel a pending transaction.
     * 
     * @param int $transactionId
     * @param string|null $reason
     * @return FeesTransaction
     */
    public function cancelTransaction(int $transactionId, ?string $reason = null): FeesTransaction
    {
        $transaction = FeesTransaction::findOrFail($transactionId);
        
        if ($transaction->payment_status !== 'pending') {
            throw new \Exception('Only pending transactions can be cancelled.');
        }
        
        $transaction->update([
            'payment_status' => 'failed',
            'remarks' => $reason ?? 'Transaction cancelled',
        ]);
        
        return $transaction;
    }

    /**
     * Get payment history for a student.
     * 
     * @param int $studentId
     * @param int|null $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getStudentPaymentHistory(int $studentId, ?int $limit = null)
    {
        $query = FeesTransaction::with(['feesAllotment.feesMaster.feesType', 'receivedBy'])
            ->where('student_id', $studentId)
            ->orderBy('payment_date', 'desc');
        
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->get();
    }

    /**
     * Get transactions for a date range.
     * 
     * @param string $startDate
     * @param string $endDate
     * @param string|null $paymentMethod
     * @param string|null $status
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTransactionsByDateRange(
        string $startDate,
        string $endDate,
        ?string $paymentMethod = null,
        ?string $status = null
    ) {
        $query = FeesTransaction::with(['student.user', 'feesAllotment.feesMaster.feesType', 'receivedBy'])
            ->whereBetween('payment_date', [$startDate, $endDate]);
        
        if ($paymentMethod) {
            $query->where('payment_method', $paymentMethod);
        }
        
        if ($status) {
            $query->where('payment_status', $status);
        }
        
        return $query->orderBy('payment_date', 'desc')->get();
    }

    /**
     * Get daily collection summary.
     * 
     * @param string|null $date
     * @return array
     */
    public function getDailyCollectionSummary(?string $date = null): array
    {
        $date = $date ?? now()->format('Y-m-d');
        
        $transactions = FeesTransaction::whereDate('payment_date', $date)
            ->where('payment_status', 'completed')
            ->get();
        
        $byMethod = $transactions->groupBy('payment_method')->map(function ($group) {
            return [
                'count' => $group->count(),
                'total' => $group->sum('amount'),
            ];
        });
        
        return [
            'date' => $date,
            'total_transactions' => $transactions->count(),
            'total_amount' => $transactions->sum('amount'),
            'by_payment_method' => $byMethod,
        ];
    }

    /**
     * Get monthly collection summary.
     * 
     * @param int $month
     * @param int $year
     * @return array
     */
    public function getMonthlyCollectionSummary(int $month, int $year): array
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        
        $transactions = FeesTransaction::whereBetween('payment_date', [$startDate, $endDate])
            ->where('payment_status', 'completed')
            ->get();
        
        $dailyCollection = [];
        for ($day = 1; $day <= $endDate->day; $day++) {
            $date = Carbon::create($year, $month, $day)->format('Y-m-d');
            $dayTransactions = $transactions->filter(function ($t) use ($date) {
                return $t->payment_date->format('Y-m-d') === $date;
            });
            
            $dailyCollection[$date] = [
                'count' => $dayTransactions->count(),
                'total' => $dayTransactions->sum('amount'),
            ];
        }
        
        return [
            'month' => $month,
            'year' => $year,
            'total_transactions' => $transactions->count(),
            'total_amount' => $transactions->sum('amount'),
            'daily_collection' => $dailyCollection,
        ];
    }

    /**
     * Generate receipt data for a transaction.
     * 
     * @param int $transactionId
     * @return array
     */
    public function generateReceipt(int $transactionId): array
    {
        $transaction = FeesTransaction::with([
            'student.user',
            'student.schoolClass',
            'student.section',
            'feesAllotment.feesMaster.feesType',
            'receivedBy',
        ])->findOrFail($transactionId);
        
        return [
            'receipt_number' => $transaction->transaction_id,
            'date' => $transaction->payment_date->format('Y-m-d'),
            'student' => [
                'name' => $transaction->student->user->full_name ?? '',
                'admission_number' => $transaction->student->admission_number,
                'class' => $transaction->student->schoolClass->name ?? '',
                'section' => $transaction->student->section->name ?? '',
            ],
            'fee_type' => $transaction->feesAllotment->feesMaster->feesType->name ?? '',
            'amount' => $transaction->amount,
            'payment_method' => $transaction->payment_method,
            'reference_number' => $transaction->reference_number,
            'received_by' => $transaction->receivedBy->name ?? '',
            'remarks' => $transaction->remarks,
        ];
    }

    /**
     * Update allotment balance after payment.
     * 
     * @param FeesAllotment $allotment
     * @param float $amount
     * @return void
     */
    private function updateAllotmentBalance(FeesAllotment $allotment, float $amount): void
    {
        $newPaidAmount = $allotment->paid_amount + $amount;
        $newBalance = $allotment->net_amount - $newPaidAmount;
        
        $status = 'unpaid';
        if ($newBalance <= 0) {
            $status = 'paid';
            $newBalance = 0;
        } elseif ($newPaidAmount > 0) {
            $status = 'partial';
        }
        
        $allotment->update([
            'paid_amount' => $newPaidAmount,
            'balance' => $newBalance,
            'status' => $status,
        ]);
    }

    /**
     * Generate unique transaction ID.
     * 
     * @param string $prefix
     * @return string
     */
    private function generateTransactionId(string $prefix = 'TXN'): string
    {
        return $prefix . date('Ymd') . strtoupper(Str::random(6));
    }

    /**
     * Get payment statistics.
     * 
     * @param int|null $sessionId
     * @return array
     */
    public function getStatistics(?int $sessionId = null): array
    {
        $query = FeesTransaction::where('payment_status', 'completed');
        
        if ($sessionId) {
            $query->whereHas('feesAllotment.feesMaster', function ($q) use ($sessionId) {
                $q->where('academic_session_id', $sessionId);
            });
        }
        
        $totalCollected = $query->sum('amount');
        $totalTransactions = $query->count();
        $todayCollection = (clone $query)->whereDate('payment_date', now())->sum('amount');
        $monthCollection = (clone $query)->whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount');
        
        return [
            'total_collected' => $totalCollected,
            'total_transactions' => $totalTransactions,
            'today_collection' => $todayCollection,
            'month_collection' => $monthCollection,
        ];
    }

    /**
     * Upload payment proof for a transaction.
     * 
     * Prompt 407: Implement Fee Payment Proof Upload
     * 
     * @param FeesTransaction $transaction
     * @param UploadedFile $file
     * @param string|null $description
     * @return PaymentProof
     */
    public function uploadPaymentProof(
        FeesTransaction $transaction,
        UploadedFile $file,
        ?string $description = null
    ): PaymentProof {
        // Validate the file
        $validation = $this->fileUploadService->validate($file, 'payment_proof');
        if (!$validation['valid']) {
            throw new \Exception(implode(', ', $validation['errors']));
        }

        // Upload file using FileUploadService
        $result = $this->fileUploadService->uploadPaymentProof($file, $transaction->id);

        // Create payment proof record
        return PaymentProof::create([
            'fees_transaction_id' => $transaction->id,
            'file_path' => $result['path'],
            'original_name' => $result['original_name'],
            'mime_type' => $result['mime_type'],
            'size' => $result['size'],
            'disk' => $result['disk'],
            'description' => $description,
            'uploaded_by' => auth()->id(),
        ]);
    }

    /**
     * Record payment with proof upload.
     * 
     * Prompt 407: Implement Fee Payment Proof Upload
     * 
     * @param int $studentId
     * @param int $feesAllotmentId
     * @param float $amount
     * @param string $paymentMethod
     * @param UploadedFile|null $proofFile
     * @param int|null $receivedBy
     * @param array $additionalData
     * @return FeesTransaction
     */
    public function recordPaymentWithProof(
        int $studentId,
        int $feesAllotmentId,
        float $amount,
        string $paymentMethod,
        ?UploadedFile $proofFile = null,
        ?int $receivedBy = null,
        array $additionalData = []
    ): FeesTransaction {
        return DB::transaction(function () use ($studentId, $feesAllotmentId, $amount, $paymentMethod, $proofFile, $receivedBy, $additionalData) {
            // Record the payment
            $transaction = $this->recordPayment(
                $studentId,
                $feesAllotmentId,
                $amount,
                $paymentMethod,
                $receivedBy,
                $additionalData
            );

            // Upload proof if provided
            if ($proofFile instanceof UploadedFile) {
                $this->uploadPaymentProof($transaction, $proofFile, $additionalData['proof_description'] ?? null);
            }

            return $transaction->load('paymentProofs');
        });
    }

    /**
     * Delete a payment proof.
     * 
     * Prompt 407: Implement Fee Payment Proof Upload
     * 
     * @param PaymentProof $proof
     * @return bool
     */
    public function deletePaymentProof(PaymentProof $proof): bool
    {
        // Delete file from storage
        $this->fileUploadService->delete($proof->file_path, $proof->disk ?? 'private_uploads');

        // Delete record
        return $proof->delete();
    }

    /**
     * Replace a payment proof.
     * 
     * Prompt 407: Implement Fee Payment Proof Upload
     * 
     * @param PaymentProof $proof
     * @param UploadedFile $file
     * @return PaymentProof
     */
    public function replacePaymentProof(PaymentProof $proof, UploadedFile $file): PaymentProof
    {
        // Validate the file
        $validation = $this->fileUploadService->validate($file, 'payment_proof');
        if (!$validation['valid']) {
            throw new \Exception(implode(', ', $validation['errors']));
        }

        // Replace file using FileUploadService
        $result = $this->fileUploadService->replace(
            $file,
            $proof->file_path,
            'payments/proofs',
            'private_uploads'
        );

        // Update proof record
        $proof->update([
            'file_path' => $result['path'],
            'original_name' => $result['original_name'],
            'mime_type' => $result['mime_type'],
            'size' => $result['size'],
        ]);

        return $proof->fresh();
    }

    /**
     * Get payment proofs for a transaction.
     * 
     * Prompt 407: Implement Fee Payment Proof Upload
     * 
     * @param FeesTransaction $transaction
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPaymentProofs(FeesTransaction $transaction)
    {
        return PaymentProof::where('fees_transaction_id', $transaction->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Verify a payment proof.
     * 
     * Prompt 407: Implement Fee Payment Proof Upload
     * 
     * @param PaymentProof $proof
     * @param int $verifiedBy
     * @return PaymentProof
     */
    public function verifyPaymentProof(PaymentProof $proof, int $verifiedBy): PaymentProof
    {
        $proof->update([
            'is_verified' => true,
            'verified_by' => $verifiedBy,
            'verified_at' => now(),
        ]);

        return $proof->fresh();
    }

    /**
     * Get unverified payment proofs.
     * 
     * Prompt 407: Implement Fee Payment Proof Upload
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUnverifiedProofs()
    {
        return PaymentProof::with(['feesTransaction.student.user'])
            ->where('is_verified', false)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
