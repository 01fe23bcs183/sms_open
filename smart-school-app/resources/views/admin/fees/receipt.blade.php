{{-- Fee Collection Receipt View --}}
{{-- Prompt 208: Printable fee receipt with school branding and payment details --}}

@extends('layouts.app')

@section('title', 'Fee Receipt')

@section('content')
<div x-data="feeReceiptManager()">
    <!-- Page Header (Hidden in Print) -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 d-print-none">
        <div>
            <h1 class="h3 mb-1">Fee Receipt</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('fee-transactions.index') }}">Transactions</a></li>
                    <li class="breadcrumb-item active">Receipt #{{ $transaction->receipt_number ?? 'N/A' }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <button type="button" class="btn btn-primary" @click="printReceipt()">
                <i class="bi bi-printer me-1"></i> Print Receipt
            </button>
            <button type="button" class="btn btn-outline-success" @click="downloadPDF()">
                <i class="bi bi-download me-1"></i> Download PDF
            </button>
            <button type="button" class="btn btn-outline-info" @click="sendEmail()">
                <i class="bi bi-envelope me-1"></i> Email Receipt
            </button>
            <a href="{{ route('fees.collect') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <!-- Receipt Container -->
    <div class="card shadow-sm" id="receipt-container">
        <div class="card-body p-4 p-md-5">
            <!-- Receipt Header -->
            <div class="row align-items-center mb-4 pb-3 border-bottom">
                <div class="col-auto">
                    <img src="{{ asset('images/logo.png') }}" alt="School Logo" class="img-fluid" style="max-height: 80px;" onerror="this.style.display='none'">
                </div>
                <div class="col text-center">
                    <h2 class="mb-1">{{ config('app.school_name', 'Smart School') }}</h2>
                    <p class="mb-0 text-muted">{{ config('app.school_address', '123 Education Street, City, State - 12345') }}</p>
                    <p class="mb-0 text-muted">
                        Phone: {{ config('app.school_phone', '+1 234 567 8900') }} | 
                        Email: {{ config('app.school_email', 'info@smartschool.com') }}
                    </p>
                </div>
                <div class="col-auto text-end">
                    <h4 class="text-primary mb-1">FEE RECEIPT</h4>
                    <p class="mb-0 fw-bold">#{{ $transaction->receipt_number ?? 'RCP-000001' }}</p>
                </div>
            </div>

            <!-- Receipt Info Row -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="bg-light rounded p-3">
                        <h6 class="text-muted mb-3">Student Information</h6>
                        <table class="table table-borderless table-sm mb-0">
                            <tr>
                                <td class="text-muted" style="width: 40%;">Name:</td>
                                <td class="fw-medium">{{ $transaction->student->name ?? 'John Doe' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Admission No:</td>
                                <td>{{ $transaction->student->admission_number ?? 'ADM001' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Class / Section:</td>
                                <td>{{ ($transaction->student->class->name ?? 'Class 10') . ' / ' . ($transaction->student->section->name ?? 'A') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Roll Number:</td>
                                <td>{{ $transaction->student->roll_number ?? '1' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="bg-light rounded p-3">
                        <h6 class="text-muted mb-3">Payment Information</h6>
                        <table class="table table-borderless table-sm mb-0">
                            <tr>
                                <td class="text-muted" style="width: 40%;">Receipt Date:</td>
                                <td class="fw-medium">{{ isset($transaction->created_at) ? $transaction->created_at->format('M d, Y') : date('M d, Y') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Payment Date:</td>
                                <td>{{ isset($transaction->payment_date) ? \Carbon\Carbon::parse($transaction->payment_date)->format('M d, Y') : date('M d, Y') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Payment Method:</td>
                                <td>
                                    <span class="badge bg-info">{{ ucfirst($transaction->payment_method ?? 'Cash') }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Academic Session:</td>
                                <td>{{ $transaction->academicSession->name ?? '2024-2025' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Fee Details Table -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Fee Type</th>
                            <th>Description</th>
                            <th class="text-end">Amount</th>
                            <th class="text-end">Discount</th>
                            <th class="text-end">Fine</th>
                            <th class="text-end">Net Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transaction->details ?? [] as $index => $detail)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="fw-medium">{{ $detail->feeType->name ?? 'N/A' }}</td>
                                <td>{{ $detail->description ?? '-' }}</td>
                                <td class="text-end">${{ number_format($detail->amount ?? 0, 2) }}</td>
                                <td class="text-end text-success">{{ $detail->discount > 0 ? '-$' . number_format($detail->discount, 2) : '-' }}</td>
                                <td class="text-end text-danger">{{ $detail->fine > 0 ? '+$' . number_format($detail->fine, 2) : '-' }}</td>
                                <td class="text-end fw-medium">${{ number_format($detail->net_amount ?? 0, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td>1</td>
                                <td class="fw-medium">Tuition Fee</td>
                                <td>Monthly tuition fee for January 2024</td>
                                <td class="text-end">$500.00</td>
                                <td class="text-end text-success">-$50.00</td>
                                <td class="text-end text-danger">-</td>
                                <td class="text-end fw-medium">$450.00</td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td class="fw-medium">Lab Fee</td>
                                <td>Science laboratory fee</td>
                                <td class="text-end">$100.00</td>
                                <td class="text-end text-success">-</td>
                                <td class="text-end text-danger">+$10.00</td>
                                <td class="text-end fw-medium">$110.00</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="table-light">
                            <td colspan="3" class="text-end fw-bold">Sub Total:</td>
                            <td class="text-end">${{ number_format($transaction->sub_total ?? 600, 2) }}</td>
                            <td class="text-end text-success">-${{ number_format($transaction->total_discount ?? 50, 2) }}</td>
                            <td class="text-end text-danger">${{ number_format($transaction->total_fine ?? 10, 2) }}</td>
                            <td class="text-end fw-bold">${{ number_format($transaction->amount ?? 560, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="6" class="text-end fw-bold border-0">Total Paid:</td>
                            <td class="text-end fw-bold text-success fs-5 border-0">${{ number_format($transaction->amount ?? 560, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Amount in Words -->
            <div class="bg-light rounded p-3 mb-4">
                <div class="row">
                    <div class="col-md-8">
                        <small class="text-muted">Amount in Words:</small>
                        <p class="mb-0 fw-medium text-capitalize" x-text="amountInWords({{ $transaction->amount ?? 560 }})">
                            Five Hundred Sixty Dollars Only
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <small class="text-muted">Payment Status:</small>
                        <p class="mb-0">
                            <span class="badge bg-success fs-6">PAID</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Additional Payment Details -->
            @if(($transaction->payment_method ?? '') === 'cheque')
                <div class="alert alert-info mb-4">
                    <div class="row">
                        <div class="col-md-4">
                            <small class="text-muted d-block">Cheque Number</small>
                            <span class="fw-medium">{{ $transaction->cheque_number ?? 'N/A' }}</span>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Bank Name</small>
                            <span class="fw-medium">{{ $transaction->bank_name ?? 'N/A' }}</span>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Cheque Date</small>
                            <span class="fw-medium">{{ $transaction->cheque_date ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            @endif

            @if(($transaction->payment_method ?? '') === 'bank_transfer' || ($transaction->payment_method ?? '') === 'online')
                <div class="alert alert-info mb-4">
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Transaction Reference</small>
                            <span class="fw-medium font-monospace">{{ $transaction->transaction_ref ?? 'TXN123456789' }}</span>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Transaction Date</small>
                            <span class="fw-medium">{{ isset($transaction->payment_date) ? \Carbon\Carbon::parse($transaction->payment_date)->format('M d, Y h:i A') : date('M d, Y h:i A') }}</span>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Notes -->
            @if($transaction->notes ?? false)
                <div class="mb-4">
                    <small class="text-muted">Notes:</small>
                    <p class="mb-0">{{ $transaction->notes }}</p>
                </div>
            @endif

            <!-- Signature Section -->
            <div class="row mt-5 pt-4">
                <div class="col-md-4 text-center">
                    <div class="border-top border-dark pt-2">
                        <small class="text-muted">Parent/Guardian Signature</small>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <div class="border-top border-dark pt-2">
                        <small class="text-muted">Accountant Signature</small>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <div class="border-top border-dark pt-2">
                        <small class="text-muted">Principal Signature</small>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-4 pt-3 border-top">
                <p class="text-muted small mb-1">This is a computer-generated receipt and does not require a physical signature.</p>
                <p class="text-muted small mb-0">For any queries, please contact the school accounts department.</p>
            </div>
        </div>
    </div>

    <!-- Duplicate Copy (for print) -->
    <div class="card shadow-sm mt-4 d-none d-print-block" id="receipt-duplicate">
        <div class="card-body p-4">
            <div class="text-center mb-3">
                <span class="badge bg-secondary">OFFICE COPY</span>
            </div>
            <!-- Same content as above but condensed for office copy -->
            <div class="row align-items-center mb-3 pb-2 border-bottom">
                <div class="col text-center">
                    <h5 class="mb-0">{{ config('app.school_name', 'Smart School') }}</h5>
                    <small class="text-muted">Receipt #{{ $transaction->receipt_number ?? 'RCP-000001' }} | {{ isset($transaction->created_at) ? $transaction->created_at->format('M d, Y') : date('M d, Y') }}</small>
                </div>
            </div>
            <div class="row small">
                <div class="col-6">
                    <strong>Student:</strong> {{ $transaction->student->name ?? 'John Doe' }}<br>
                    <strong>Adm No:</strong> {{ $transaction->student->admission_number ?? 'ADM001' }}<br>
                    <strong>Class:</strong> {{ ($transaction->student->class->name ?? 'Class 10') . ' / ' . ($transaction->student->section->name ?? 'A') }}
                </div>
                <div class="col-6 text-end">
                    <strong>Amount:</strong> ${{ number_format($transaction->amount ?? 560, 2) }}<br>
                    <strong>Method:</strong> {{ ucfirst($transaction->payment_method ?? 'Cash') }}<br>
                    <strong>Status:</strong> <span class="text-success">PAID</span>
                </div>
            </div>
            <div class="row mt-3 pt-2 border-top">
                <div class="col-6 text-center">
                    <small class="text-muted">Received By</small>
                </div>
                <div class="col-6 text-center">
                    <small class="text-muted">Accountant</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function feeReceiptManager() {
    return {
        printReceipt() {
            window.print();
        },

        downloadPDF() {
            const receiptId = '{{ $transaction->id ?? 1 }}';
            window.location.href = `/fees/receipt/${receiptId}/pdf`;
        },

        sendEmail() {
            Swal.fire({
                title: 'Send Receipt via Email',
                input: 'email',
                inputLabel: 'Email Address',
                inputValue: '{{ $transaction->student->parent_email ?? '' }}',
                inputPlaceholder: 'Enter email address',
                showCancelButton: true,
                confirmButtonText: 'Send',
                showLoaderOnConfirm: true,
                preConfirm: (email) => {
                    return fetch(`/fees/receipt/{{ $transaction->id ?? 1 }}/email`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ email })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Failed to send email');
                        }
                        return response.json();
                    })
                    .catch(error => {
                        Swal.showValidationMessage(`Error: ${error.message}`);
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire('Sent!', 'Receipt has been emailed successfully.', 'success');
                }
            });
        },

        amountInWords(amount) {
            const ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 
                         'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 
                         'Eighteen', 'Nineteen'];
            const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
            
            if (amount === 0) return 'Zero Dollars Only';
            
            const convertHundreds = (num) => {
                let str = '';
                if (num > 99) {
                    str += ones[Math.floor(num / 100)] + ' Hundred ';
                    num %= 100;
                }
                if (num > 19) {
                    str += tens[Math.floor(num / 10)] + ' ';
                    num %= 10;
                }
                if (num > 0) {
                    str += ones[num] + ' ';
                }
                return str;
            };
            
            const dollars = Math.floor(amount);
            const cents = Math.round((amount - dollars) * 100);
            
            let result = '';
            
            if (dollars >= 1000000) {
                result += convertHundreds(Math.floor(dollars / 1000000)) + 'Million ';
                dollars %= 1000000;
            }
            if (dollars >= 1000) {
                result += convertHundreds(Math.floor(dollars / 1000)) + 'Thousand ';
                dollars %= 1000;
            }
            result += convertHundreds(dollars);
            
            result += 'Dollars';
            
            if (cents > 0) {
                result += ' and ' + convertHundreds(cents) + 'Cents';
            }
            
            return result + ' Only';
        }
    }
}
</script>
@endpush

@push('styles')
<style>
@media print {
    body {
        font-size: 12px;
    }
    
    .d-print-none {
        display: none !important;
    }
    
    .d-print-block {
        display: block !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    #receipt-container {
        page-break-after: always;
    }
    
    #receipt-duplicate {
        page-break-before: always;
    }
    
    .table {
        font-size: 11px;
    }
    
    .badge {
        border: 1px solid #000;
        color: #000 !important;
        background: transparent !important;
    }
}

[dir="rtl"] .me-1 {
    margin-right: 0 !important;
    margin-left: 0.25rem !important;
}

[dir="rtl"] .text-end {
    text-align: left !important;
}
</style>
@endpush
