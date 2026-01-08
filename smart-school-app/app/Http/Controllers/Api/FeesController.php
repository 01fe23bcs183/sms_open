<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\HasDataTables;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Fees API Controller
 * 
 * Prompt 299: Add Server-Side Pagination, Search, and Filters
 * 
 * Provides API endpoints for fees data with pagination,
 * search, and filter support.
 */
class FeesController extends Controller
{
    use HasDataTables;

    /**
     * Get fees transactions.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function transactions(Request $request): JsonResponse
    {
        $query = DB::table('fees_transactions')
            ->join('students', 'fees_transactions.student_id', '=', 'students.id')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->leftJoin('fees_allotments', 'fees_transactions.fees_allotment_id', '=', 'fees_allotments.id')
            ->leftJoin('fees_masters', 'fees_allotments.fees_master_id', '=', 'fees_masters.id')
            ->leftJoin('fees_types', 'fees_masters.fees_type_id', '=', 'fees_types.id')
            ->leftJoin('classes', 'students.class_id', '=', 'classes.id')
            ->select([
                'fees_transactions.id',
                'fees_transactions.receipt_number',
                'fees_transactions.transaction_date',
                'fees_transactions.amount',
                'fees_transactions.discount_amount',
                'fees_transactions.fine_amount',
                'fees_transactions.total_amount',
                'fees_transactions.payment_method',
                'fees_transactions.payment_status',
                'fees_transactions.transaction_id',
                'fees_transactions.created_at',
                'students.admission_number',
                'users.name as student_name',
                'fees_types.name as fees_type',
                'classes.name as class_name',
            ])
            ->whereNull('fees_transactions.deleted_at');

        // Apply filters
        if ($request->filled('academic_session_id')) {
            $query->where('fees_transactions.academic_session_id', $request->input('academic_session_id'));
        }

        if ($request->filled('student_id')) {
            $query->where('fees_transactions.student_id', $request->input('student_id'));
        }

        if ($request->filled('payment_status')) {
            $query->where('fees_transactions.payment_status', $request->input('payment_status'));
        }

        if ($request->filled('payment_method')) {
            $query->where('fees_transactions.payment_method', $request->input('payment_method'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('fees_transactions.transaction_date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('fees_transactions.transaction_date', '<=', $request->input('date_to'));
        }

        // Check if DataTables request
        if ($request->has('draw')) {
            return $this->simpleDataTablesResponse(
                $query,
                $request,
                ['users.name', 'students.admission_number', 'fees_transactions.receipt_number'],
                ['fees_transactions.transaction_date', 'fees_transactions.total_amount', 'users.name'],
                function ($transaction) {
                    return [
                        'id' => $transaction->id,
                        'receipt_number' => $transaction->receipt_number,
                        'transaction_date' => $transaction->transaction_date,
                        'student_name' => $transaction->student_name,
                        'admission_number' => $transaction->admission_number,
                        'class' => $transaction->class_name,
                        'fees_type' => $transaction->fees_type,
                        'amount' => '₹' . number_format($transaction->amount, 2),
                        'discount' => '₹' . number_format($transaction->discount_amount ?? 0, 2),
                        'fine' => '₹' . number_format($transaction->fine_amount ?? 0, 2),
                        'total' => '₹' . number_format($transaction->total_amount, 2),
                        'payment_method' => ucfirst($transaction->payment_method),
                        'payment_status' => ucfirst($transaction->payment_status),
                    ];
                }
            );
        }

        // Standard pagination response
        $perPage = min($request->input('per_page', 15), 100);
        $transactions = $query->orderBy('fees_transactions.transaction_date', 'desc')->paginate($perPage);

        return $this->paginatedResponse(
            $transactions->items(),
            $transactions->total(),
            $transactions->currentPage(),
            $transactions->perPage(),
            'Transactions retrieved successfully'
        );
    }

    /**
     * Get student fee dues.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function dues(Request $request): JsonResponse
    {
        $studentId = $request->input('student_id');
        $sessionId = $request->input('academic_session_id');

        $query = DB::table('fees_allotments')
            ->join('students', 'fees_allotments.student_id', '=', 'students.id')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->join('fees_masters', 'fees_allotments.fees_master_id', '=', 'fees_masters.id')
            ->join('fees_types', 'fees_masters.fees_type_id', '=', 'fees_types.id')
            ->leftJoin('fees_groups', 'fees_masters.fees_group_id', '=', 'fees_groups.id')
            ->leftJoin('classes', 'students.class_id', '=', 'classes.id')
            ->select([
                'fees_allotments.id',
                'fees_allotments.student_id',
                'fees_allotments.amount as allotted_amount',
                'fees_allotments.discount_amount',
                'fees_allotments.paid_amount',
                'fees_allotments.balance_amount',
                'fees_allotments.status',
                'fees_masters.due_date',
                'fees_types.name as fees_type',
                'fees_groups.name as fees_group',
                'students.admission_number',
                'users.name as student_name',
                'classes.name as class_name',
            ])
            ->whereNull('fees_allotments.deleted_at')
            ->where('fees_allotments.balance_amount', '>', 0);

        if ($studentId) {
            $query->where('fees_allotments.student_id', $studentId);
        }

        if ($sessionId) {
            $query->where('fees_allotments.academic_session_id', $sessionId);
        }

        $dues = $query->orderBy('fees_masters.due_date')->get();

        // Calculate totals
        $totalDue = $dues->sum('balance_amount');
        $totalPaid = $dues->sum('paid_amount');
        $totalAllotted = $dues->sum('allotted_amount');

        return $this->successResponse([
            'dues' => $dues,
            'summary' => [
                'total_allotted' => '₹' . number_format($totalAllotted, 2),
                'total_paid' => '₹' . number_format($totalPaid, 2),
                'total_due' => '₹' . number_format($totalDue, 2),
                'dues_count' => $dues->count(),
            ],
        ], 'Fee dues retrieved');
    }

    /**
     * Get fees collection statistics.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function stats(Request $request): JsonResponse
    {
        $sessionId = $request->input('academic_session_id');
        $month = $request->input('month'); // Format: YYYY-MM

        $transactionQuery = DB::table('fees_transactions')
            ->whereNull('deleted_at')
            ->where('payment_status', 'completed');

        if ($sessionId) {
            $transactionQuery->where('academic_session_id', $sessionId);
        }

        if ($month) {
            $transactionQuery->whereRaw("DATE_FORMAT(transaction_date, '%Y-%m') = ?", [$month]);
        }

        // Collection by payment method
        $byPaymentMethod = (clone $transactionQuery)
            ->select('payment_method', DB::raw('SUM(total_amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get();

        // Collection by date (last 30 days or month)
        $dailyCollection = (clone $transactionQuery)
            ->select(DB::raw('DATE(transaction_date) as date'), DB::raw('SUM(total_amount) as total'))
            ->groupBy(DB::raw('DATE(transaction_date)'))
            ->orderBy('date')
            ->limit(30)
            ->get();

        // Total collection
        $totalCollection = (clone $transactionQuery)->sum('total_amount');
        $totalTransactions = (clone $transactionQuery)->count();

        // Pending dues
        $pendingDues = DB::table('fees_allotments')
            ->whereNull('deleted_at')
            ->where('balance_amount', '>', 0)
            ->when($sessionId, fn($q) => $q->where('academic_session_id', $sessionId))
            ->sum('balance_amount');

        return $this->successResponse([
            'total_collection' => '₹' . number_format($totalCollection, 2),
            'total_collection_raw' => $totalCollection,
            'total_transactions' => $totalTransactions,
            'pending_dues' => '₹' . number_format($pendingDues, 2),
            'pending_dues_raw' => $pendingDues,
            'by_payment_method' => $byPaymentMethod,
            'daily_collection' => $dailyCollection,
        ], 'Fees statistics');
    }

    /**
     * Get fee structure for a class.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function structure(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
        ]);

        $classId = $request->input('class_id');
        $sectionId = $request->input('section_id');
        $sessionId = $request->input('academic_session_id');

        $query = DB::table('fees_masters')
            ->join('fees_types', 'fees_masters.fees_type_id', '=', 'fees_types.id')
            ->leftJoin('fees_groups', 'fees_masters.fees_group_id', '=', 'fees_groups.id')
            ->select([
                'fees_masters.id',
                'fees_masters.amount',
                'fees_masters.due_date',
                'fees_masters.is_active',
                'fees_types.name as fees_type',
                'fees_types.code as fees_code',
                'fees_groups.name as fees_group',
            ])
            ->where('fees_masters.class_id', $classId)
            ->where('fees_masters.is_active', true)
            ->whereNull('fees_masters.deleted_at');

        if ($sectionId) {
            $query->where(function ($q) use ($sectionId) {
                $q->where('fees_masters.section_id', $sectionId)
                  ->orWhereNull('fees_masters.section_id');
            });
        }

        if ($sessionId) {
            $query->where('fees_masters.academic_session_id', $sessionId);
        }

        $structure = $query->orderBy('fees_masters.due_date')->get();

        $totalAmount = $structure->sum('amount');

        return $this->successResponse([
            'class_id' => $classId,
            'section_id' => $sectionId,
            'fees' => $structure,
            'total_amount' => '₹' . number_format($totalAmount, 2),
            'total_amount_raw' => $totalAmount,
        ], 'Fee structure retrieved');
    }
}
