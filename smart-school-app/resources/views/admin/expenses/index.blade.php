{{-- Expenses List View --}}
{{-- Prompt 258: Expenses listing page with search, filter, and CRUD operations --}}

@extends('layouts.app')

@section('title', 'Expenses')

@section('content')
<div x-data="expensesManager()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Expenses</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Accounting</a></li>
                    <li class="breadcrumb-item active">Expenses</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('expense-categories.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-folder me-1"></i> Categories
            </a>
            <button type="button" class="btn btn-outline-success" @click="exportExpenses()">
                <i class="bi bi-download me-1"></i> Export
            </button>
            <a href="{{ route('expenses.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Add Expense
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <x-alert type="success" :dismissible="true">
            {{ session('success') }}
        </x-alert>
    @endif

    @if(session('error'))
        <x-alert type="danger" :dismissible="true">
            {{ session('error') }}
        </x-alert>
    @endif

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-danger bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-currency-dollar fs-3 text-danger mb-2 d-block"></i>
                    <h3 class="mb-0 text-danger">{{ number_format($stats['total'] ?? 0, 2) }}</h3>
                    <small class="text-muted">Total Expenses</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-warning bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-calendar-month fs-3 text-warning mb-2 d-block"></i>
                    <h3 class="mb-0 text-warning">{{ number_format($stats['this_month'] ?? 0, 2) }}</h3>
                    <small class="text-muted">This Month</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-info bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-calendar-year fs-3 text-info mb-2 d-block"></i>
                    <h3 class="mb-0 text-info">{{ number_format($stats['this_year'] ?? 0, 2) }}</h3>
                    <small class="text-muted">This Year</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-secondary bg-opacity-10 h-100">
                <div class="card-body text-center py-3">
                    <i class="bi bi-receipt fs-3 text-secondary mb-2 d-block"></i>
                    <h3 class="mb-0">{{ $stats['count'] ?? count($expenses ?? []) }}</h3>
                    <small class="text-muted">Total Records</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <x-card class="mb-4">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label small text-muted">Search</label>
                <div class="input-group">
                    <span class="input-group-text bg-light">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input 
                        type="text" 
                        class="form-control" 
                        placeholder="Search by title, description..."
                        x-model="filters.search"
                    >
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Category</label>
                <select class="form-select" x-model="filters.category">
                    <option value="">All Categories</option>
                    @foreach($categories ?? [] as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Date From</label>
                <input type="date" class="form-control" x-model="filters.dateFrom">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Date To</label>
                <input type="date" class="form-control" x-model="filters.dateTo">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Payment Method</label>
                <select class="form-select" x-model="filters.paymentMethod">
                    <option value="">All Methods</option>
                    <option value="cash">Cash</option>
                    <option value="cheque">Cheque</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="online">Online</option>
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-outline-secondary w-100" @click="resetFilters()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
    </x-card>

    <!-- Expenses Table -->
    <x-card :noPadding="true">
        <x-slot name="header">
            <div class="d-flex align-items-center justify-content-between w-100">
                <span>
                    <i class="bi bi-list-ul me-2"></i>
                    Expenses
                    <span class="badge bg-danger ms-2">{{ count($expenses ?? []) }}</span>
                </span>
                <div class="d-flex align-items-center gap-2">
                    <select class="form-select form-select-sm" style="width: auto;" x-model="perPage">
                        <option value="10">10 per page</option>
                        <option value="25">25 per page</option>
                        <option value="50">50 per page</option>
                        <option value="100">100 per page</option>
                    </select>
                </div>
            </div>
        </x-slot>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>
                            <input type="checkbox" class="form-check-input" @change="toggleSelectAll($event)">
                        </th>
                        <th>Date</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th class="text-end">Amount</th>
                        <th>Payment Method</th>
                        <th>Reference</th>
                        <th class="text-center">Attachment</th>
                        <th>Created By</th>
                        <th style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses ?? [] as $expense)
                        <tr x-show="matchesFilters({{ json_encode([
                            'title' => strtolower($expense->title ?? ''),
                            'description' => strtolower($expense->description ?? ''),
                            'category_id' => $expense->category_id ?? '',
                            'expense_date' => $expense->expense_date ?? '',
                            'payment_method' => $expense->payment_method ?? ''
                        ]) }})">
                            <td>
                                <input type="checkbox" class="form-check-input" value="{{ $expense->id }}" x-model="selectedExpenses">
                            </td>
                            <td>
                                <span class="text-nowrap">
                                    {{ isset($expense->expense_date) ? \Carbon\Carbon::parse($expense->expense_date)->format('d M Y') : 'N/A' }}
                                </span>
                            </td>
                            <td>
                                <div>
                                    <span class="fw-medium">{{ $expense->title ?? 'Untitled' }}</span>
                                    @if($expense->description)
                                        <br>
                                        <small class="text-muted">{{ Str::limit($expense->description, 40) }}</small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-danger bg-opacity-10 text-danger">
                                    {{ $expense->category->name ?? 'Uncategorized' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <span class="fw-medium text-danger">
                                    {{ number_format($expense->amount ?? 0, 2) }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $methodIcons = [
                                        'cash' => 'bi-cash text-success',
                                        'cheque' => 'bi-file-text text-primary',
                                        'bank_transfer' => 'bi-bank text-info',
                                        'online' => 'bi-globe text-warning'
                                    ];
                                @endphp
                                <span>
                                    <i class="bi {{ $methodIcons[$expense->payment_method ?? 'cash'] ?? 'bi-cash' }} me-1"></i>
                                    {{ ucfirst(str_replace('_', ' ', $expense->payment_method ?? 'Cash')) }}
                                </span>
                            </td>
                            <td>
                                @if($expense->reference_number)
                                    <code class="bg-light px-2 py-1 rounded">{{ $expense->reference_number }}</code>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($expense->attachment)
                                    <a href="{{ Storage::url($expense->attachment) }}" target="_blank" class="text-primary">
                                        <i class="bi bi-paperclip"></i>
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">{{ $expense->createdBy->name ?? 'System' }}</small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary" title="View" @click="viewExpense({{ $expense->id }})">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <a href="{{ route('expenses.edit', $expense->id) }}" class="btn btn-outline-secondary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" title="Delete" @click="confirmDelete({{ $expense->id }})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-receipt fs-1 d-block mb-2"></i>
                                    <p class="mb-2">No expenses found</p>
                                    <a href="{{ route('expenses.create') }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus-lg me-1"></i> Add Expense
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($expenses ?? []) > 0)
                <tfoot class="table-light">
                    <tr>
                        <td colspan="4" class="text-end fw-medium">Total:</td>
                        <td class="text-end fw-bold text-danger">
                            {{ number_format(collect($expenses ?? [])->sum('amount'), 2) }}
                        </td>
                        <td colspan="5"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        @if(isset($expenses) && $expenses instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <x-slot name="footer">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Showing {{ $expenses->firstItem() ?? 0 }} to {{ $expenses->lastItem() ?? 0 }} of {{ $expenses->total() }} entries
                </div>
                {{ $expenses->links() }}
            </div>
        </x-slot>
        @endif
    </x-card>

    <!-- Bulk Actions -->
    <div class="mt-3" x-show="selectedExpenses.length > 0">
        <div class="d-flex align-items-center gap-2">
            <span class="text-muted"><span x-text="selectedExpenses.length"></span> selected</span>
            <button type="button" class="btn btn-outline-danger btn-sm" @click="bulkDelete()">
                <i class="bi bi-trash me-1"></i> Delete Selected
            </button>
            <button type="button" class="btn btn-outline-success btn-sm" @click="exportSelected()">
                <i class="bi bi-download me-1"></i> Export Selected
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" @click="selectedExpenses = []">
                Clear Selection
            </button>
        </div>
    </div>

    <!-- View Expense Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-receipt me-2"></i>
                        Expense Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div x-show="selectedExpense">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="text-muted" style="width: 120px;">Title:</td>
                                <td class="fw-medium" x-text="selectedExpense?.title"></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Category:</td>
                                <td x-text="selectedExpense?.category"></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Amount:</td>
                                <td class="fw-bold text-danger" x-text="'$' + parseFloat(selectedExpense?.amount || 0).toFixed(2)"></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Date:</td>
                                <td x-text="selectedExpense?.date"></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Payment:</td>
                                <td x-text="selectedExpense?.payment_method"></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Reference:</td>
                                <td x-text="selectedExpense?.reference || 'N/A'"></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Created By:</td>
                                <td x-text="selectedExpense?.created_by"></td>
                            </tr>
                        </table>
                        <div x-show="selectedExpense?.description" class="border-top pt-3 mt-3">
                            <label class="text-muted small">Description:</label>
                            <p class="mb-0" x-text="selectedExpense?.description"></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a :href="'/admin/expenses/' + selectedExpense?.id + '/edit'" class="btn btn-primary">
                        <i class="bi bi-pencil me-1"></i> Edit
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this expense? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form :action="deleteUrl" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function expensesManager() {
    return {
        filters: {
            search: '',
            category: '',
            dateFrom: '',
            dateTo: '',
            paymentMethod: ''
        },
        selectedExpenses: [],
        selectedExpense: null,
        deleteUrl: '',
        perPage: 25,
        
        expenses: @json($expenses ?? []),
        
        matchesFilters(expense) {
            // Search filter
            if (this.filters.search) {
                const search = this.filters.search.toLowerCase();
                if (!expense.title.includes(search) && !expense.description.includes(search)) {
                    return false;
                }
            }
            
            // Category filter
            if (this.filters.category && expense.category_id != this.filters.category) {
                return false;
            }
            
            // Date filters
            if (this.filters.dateFrom && expense.expense_date) {
                if (expense.expense_date < this.filters.dateFrom) return false;
            }
            
            if (this.filters.dateTo && expense.expense_date) {
                if (expense.expense_date > this.filters.dateTo) return false;
            }
            
            // Payment method filter
            if (this.filters.paymentMethod && expense.payment_method !== this.filters.paymentMethod) {
                return false;
            }
            
            return true;
        },
        
        resetFilters() {
            this.filters = {
                search: '',
                category: '',
                dateFrom: '',
                dateTo: '',
                paymentMethod: ''
            };
        },
        
        toggleSelectAll(event) {
            if (event.target.checked) {
                this.selectedExpenses = Array.from(document.querySelectorAll('tbody input[type="checkbox"]')).map(cb => cb.value);
            } else {
                this.selectedExpenses = [];
            }
        },
        
        viewExpense(expenseId) {
            const expenses = Array.isArray(this.expenses) ? this.expenses : (this.expenses.data || []);
            const expense = expenses.find(e => e.id === expenseId);
            
            if (expense) {
                this.selectedExpense = {
                    id: expense.id,
                    title: expense.title,
                    category: expense.category?.name || 'Uncategorized',
                    amount: expense.amount,
                    date: expense.expense_date,
                    payment_method: expense.payment_method?.replace('_', ' '),
                    reference: expense.reference_number,
                    description: expense.description,
                    created_by: expense.created_by?.name || 'System'
                };
                
                const modal = new bootstrap.Modal(document.getElementById('viewModal'));
                modal.show();
            }
        },
        
        confirmDelete(expenseId) {
            this.deleteUrl = `/admin/expenses/${expenseId}`;
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        },
        
        bulkDelete() {
            if (confirm(`Are you sure you want to delete ${this.selectedExpenses.length} expenses?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("expenses.bulk-delete") }}';
                form.innerHTML = `
                    @csrf
                    @method('DELETE')
                    ${this.selectedExpenses.map(id => `<input type="hidden" name="ids[]" value="${id}">`).join('')}
                `;
                document.body.appendChild(form);
                form.submit();
            }
        },
        
        exportExpenses() {
            const params = new URLSearchParams(this.filters);
            window.location.href = `{{ route('expenses.export') }}?${params.toString()}`;
        },
        
        exportSelected() {
            const params = new URLSearchParams();
            this.selectedExpenses.forEach(id => params.append('ids[]', id));
            window.location.href = `{{ route('expenses.export') }}?${params.toString()}`;
        }
    };
}
</script>
@endpush
