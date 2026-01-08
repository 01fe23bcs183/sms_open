{{-- Hostel Report View --}}
{{-- Prompt 241: Hostel report page with statistics and charts --}}

@extends('layouts.app')

@section('title', 'Hostel Report')

@section('content')
<div x-data="hostelReport()">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Hostel Report</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hostels.index') }}">Hostels</a></li>
                    <li class="breadcrumb-item active">Report</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <button type="button" class="btn btn-outline-success" @click="exportReport('excel')">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
            </button>
            <button type="button" class="btn btn-outline-danger" @click="exportReport('pdf')">
                <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
            </button>
            <button type="button" class="btn btn-outline-secondary" @click="printReport">
                <i class="bi bi-printer me-1"></i> Print
            </button>
        </div>
    </div>

    <!-- Filter Section -->
    <x-card class="mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="academicSession" class="form-label">Academic Session</label>
                <select class="form-select" id="academicSession" x-model="filters.academic_session_id">
                    <option value="">All Sessions</option>
                    @foreach($academicSessions ?? [] as $session)
                        <option value="{{ $session->id }}">{{ $session->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="hostelFilter" class="form-label">Hostel</label>
                <select class="form-select" id="hostelFilter" x-model="filters.hostel_id">
                    <option value="">All Hostels</option>
                    @foreach($hostels ?? [] as $hostel)
                        <option value="{{ $hostel->id }}">{{ $hostel->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="dateFrom" class="form-label">Date From</label>
                <input type="date" class="form-control" id="dateFrom" x-model="filters.date_from">
            </div>
            <div class="col-md-3">
                <label for="dateTo" class="form-label">Date To</label>
                <input type="date" class="form-control" id="dateTo" x-model="filters.date_to">
            </div>
        </div>
        <div class="mt-3">
            <button type="button" class="btn btn-primary" @click="generateReport">
                <i class="bi bi-bar-chart me-1"></i> Generate Report
            </button>
            <button type="button" class="btn btn-outline-secondary" @click="clearFilters">
                <i class="bi bi-x-lg me-1"></i> Clear Filters
            </button>
        </div>
    </x-card>

    <!-- Summary Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-primary bg-opacity-10 h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-building fs-2 text-primary mb-2 d-block"></i>
                    <h2 class="mb-0">{{ $stats['total_hostels'] ?? 0 }}</h2>
                    <small class="text-muted">Total Hostels</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-success bg-opacity-10 h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-door-open fs-2 text-success mb-2 d-block"></i>
                    <h2 class="mb-0">{{ $stats['total_rooms'] ?? 0 }}</h2>
                    <small class="text-muted">Total Rooms</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-info bg-opacity-10 h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-people fs-2 text-info mb-2 d-block"></i>
                    <h2 class="mb-0">{{ $stats['total_students'] ?? 0 }}</h2>
                    <small class="text-muted">Total Students</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-warning bg-opacity-10 h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-currency-dollar fs-2 text-warning mb-2 d-block"></i>
                    <h2 class="mb-0">${{ number_format($stats['total_fees'] ?? 0, 2) }}</h2>
                    <small class="text-muted">Total Fees Collected</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-md-2 col-sm-4">
            <div class="card h-100">
                <div class="card-body text-center py-3">
                    <h4 class="mb-0 text-primary">{{ $stats['total_capacity'] ?? 0 }}</h4>
                    <small class="text-muted">Total Capacity</small>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4">
            <div class="card h-100">
                <div class="card-body text-center py-3">
                    <h4 class="mb-0 text-success">{{ $stats['total_occupied'] ?? 0 }}</h4>
                    <small class="text-muted">Occupied Beds</small>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4">
            <div class="card h-100">
                <div class="card-body text-center py-3">
                    <h4 class="mb-0 text-info">{{ $stats['available_beds'] ?? 0 }}</h4>
                    <small class="text-muted">Available Beds</small>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4">
            <div class="card h-100">
                <div class="card-body text-center py-3">
                    @php
                        $occupancyRate = ($stats['total_capacity'] ?? 0) > 0 
                            ? round((($stats['total_occupied'] ?? 0) / ($stats['total_capacity'] ?? 0)) * 100, 1) 
                            : 0;
                    @endphp
                    <h4 class="mb-0 text-warning">{{ $occupancyRate }}%</h4>
                    <small class="text-muted">Occupancy Rate</small>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4">
            <div class="card h-100">
                <div class="card-body text-center py-3">
                    <h4 class="mb-0 text-danger">{{ $stats['boys_hostels'] ?? 0 }}</h4>
                    <small class="text-muted">Boys Hostels</small>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4">
            <div class="card h-100">
                <div class="card-body text-center py-3">
                    <h4 class="mb-0 text-secondary">{{ $stats['girls_hostels'] ?? 0 }}</h4>
                    <small class="text-muted">Girls Hostels</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Occupancy Chart -->
        <div class="col-lg-6">
            <x-card>
                <x-slot name="header">
                    <i class="bi bi-pie-chart me-2"></i>
                    Occupancy by Hostel
                </x-slot>

                <div style="height: 300px;">
                    <canvas id="occupancyChart"></canvas>
                </div>
            </x-card>
        </div>

        <!-- Room Type Distribution -->
        <div class="col-lg-6">
            <x-card>
                <x-slot name="header">
                    <i class="bi bi-bar-chart me-2"></i>
                    Room Type Distribution
                </x-slot>

                <div style="height: 300px;">
                    <canvas id="roomTypeChart"></canvas>
                </div>
            </x-card>
        </div>

        <!-- Monthly Admissions Chart -->
        <div class="col-12">
            <x-card>
                <x-slot name="header">
                    <i class="bi bi-graph-up me-2"></i>
                    Monthly Hostel Admissions
                </x-slot>

                <div style="height: 300px;">
                    <canvas id="admissionsChart"></canvas>
                </div>
            </x-card>
        </div>
    </div>

    <!-- Hostel-wise Report Table -->
    <x-card class="mt-4" :noPadding="true">
        <x-slot name="header">
            <i class="bi bi-table me-2"></i>
            Hostel-wise Summary
        </x-slot>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Hostel</th>
                        <th>Type</th>
                        <th class="text-center">Rooms</th>
                        <th class="text-center">Capacity</th>
                        <th class="text-center">Occupied</th>
                        <th class="text-center">Available</th>
                        <th class="text-center">Occupancy %</th>
                        <th class="text-end">Total Fees</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($hostelSummary ?? [] as $summary)
                        <tr>
                            <td>
                                <a href="{{ route('hostels.show', $summary->id ?? 0) }}" class="text-decoration-none fw-medium">
                                    {{ $summary->name ?? '-' }}
                                </a>
                            </td>
                            <td>
                                <span class="badge {{ ($summary->type ?? '') == 'boys' ? 'bg-primary' : (($summary->type ?? '') == 'girls' ? 'bg-danger' : 'bg-secondary') }}">
                                    {{ ucfirst($summary->type ?? '-') }}
                                </span>
                            </td>
                            <td class="text-center">{{ $summary->rooms_count ?? 0 }}</td>
                            <td class="text-center">{{ $summary->total_capacity ?? 0 }}</td>
                            <td class="text-center">{{ $summary->total_occupied ?? 0 }}</td>
                            <td class="text-center">
                                @php
                                    $available = ($summary->total_capacity ?? 0) - ($summary->total_occupied ?? 0);
                                @endphp
                                @if($available > 0)
                                    <span class="badge bg-success">{{ $available }}</span>
                                @else
                                    <span class="badge bg-danger">Full</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @php
                                    $percent = ($summary->total_capacity ?? 0) > 0 
                                        ? round((($summary->total_occupied ?? 0) / ($summary->total_capacity ?? 0)) * 100, 1) 
                                        : 0;
                                @endphp
                                <div class="progress" style="height: 20px; width: 100px; margin: 0 auto;">
                                    <div 
                                        class="progress-bar {{ $percent >= 90 ? 'bg-danger' : ($percent >= 70 ? 'bg-warning' : 'bg-success') }}" 
                                        role="progressbar" 
                                        style="width: {{ $percent }}%"
                                    >
                                        {{ $percent }}%
                                    </div>
                                </div>
                            </td>
                            <td class="text-end fw-medium">${{ number_format($summary->total_fees ?? 0, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="bi bi-building fs-3 d-block mb-2"></i>
                                    <p class="mb-0">No hostel data available</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="table-light">
                    <tr class="fw-bold">
                        <td>Total</td>
                        <td></td>
                        <td class="text-center">{{ $stats['total_rooms'] ?? 0 }}</td>
                        <td class="text-center">{{ $stats['total_capacity'] ?? 0 }}</td>
                        <td class="text-center">{{ $stats['total_occupied'] ?? 0 }}</td>
                        <td class="text-center">{{ $stats['available_beds'] ?? 0 }}</td>
                        <td class="text-center">{{ $occupancyRate ?? 0 }}%</td>
                        <td class="text-end">${{ number_format($stats['total_fees'] ?? 0, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </x-card>

    <!-- Room Type Summary -->
    <x-card class="mt-4" :noPadding="true">
        <x-slot name="header">
            <i class="bi bi-grid me-2"></i>
            Room Type Summary
        </x-slot>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Room Type</th>
                        <th>Hostel</th>
                        <th class="text-center">Rooms</th>
                        <th class="text-center">Beds/Room</th>
                        <th class="text-center">Total Capacity</th>
                        <th class="text-center">Students</th>
                        <th class="text-end">Fees/Month</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roomTypeSummary ?? [] as $roomType)
                        <tr>
                            <td class="fw-medium">{{ $roomType->name ?? '-' }}</td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $roomType->hostel->name ?? '-' }}</span>
                            </td>
                            <td class="text-center">{{ $roomType->rooms_count ?? 0 }}</td>
                            <td class="text-center">{{ $roomType->beds_per_room ?? 0 }}</td>
                            <td class="text-center">{{ $roomType->capacity ?? 0 }}</td>
                            <td class="text-center">{{ $roomType->students_count ?? 0 }}</td>
                            <td class="text-end">${{ number_format($roomType->fees_per_month ?? 0, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="bi bi-grid fs-3 d-block mb-2"></i>
                                    <p class="mb-0">No room type data available</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
function hostelReport() {
    return {
        filters: {
            academic_session_id: '',
            hostel_id: '',
            date_from: '',
            date_to: ''
        },

        init() {
            this.initCharts();
        },

        initCharts() {
            // Occupancy Chart
            const occupancyCtx = document.getElementById('occupancyChart');
            if (occupancyCtx) {
                new Chart(occupancyCtx, {
                    type: 'doughnut',
                    data: {
                        labels: @json(collect($hostelSummary ?? [])->pluck('name')),
                        datasets: [{
                            data: @json(collect($hostelSummary ?? [])->pluck('total_occupied')),
                            backgroundColor: [
                                'rgba(54, 162, 235, 0.8)',
                                'rgba(255, 99, 132, 0.8)',
                                'rgba(255, 206, 86, 0.8)',
                                'rgba(75, 192, 192, 0.8)',
                                'rgba(153, 102, 255, 0.8)'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }

            // Room Type Chart
            const roomTypeCtx = document.getElementById('roomTypeChart');
            if (roomTypeCtx) {
                new Chart(roomTypeCtx, {
                    type: 'bar',
                    data: {
                        labels: @json(collect($roomTypeSummary ?? [])->pluck('name')),
                        datasets: [{
                            label: 'Rooms',
                            data: @json(collect($roomTypeSummary ?? [])->pluck('rooms_count')),
                            backgroundColor: 'rgba(54, 162, 235, 0.8)'
                        }, {
                            label: 'Students',
                            data: @json(collect($roomTypeSummary ?? [])->pluck('students_count')),
                            backgroundColor: 'rgba(255, 99, 132, 0.8)'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }

            // Monthly Admissions Chart
            const admissionsCtx = document.getElementById('admissionsChart');
            if (admissionsCtx) {
                new Chart(admissionsCtx, {
                    type: 'line',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                        datasets: [{
                            label: 'New Admissions',
                            data: @json($monthlyAdmissions ?? [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]),
                            borderColor: 'rgba(54, 162, 235, 1)',
                            backgroundColor: 'rgba(54, 162, 235, 0.1)',
                            fill: true,
                            tension: 0.4
                        }, {
                            label: 'Departures',
                            data: @json($monthlyDepartures ?? [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]),
                            borderColor: 'rgba(255, 99, 132, 1)',
                            backgroundColor: 'rgba(255, 99, 132, 0.1)',
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        },

        generateReport() {
            // In real implementation, this would reload the page with filters
            const params = new URLSearchParams(this.filters);
            window.location.href = `/hostels/report?${params.toString()}`;
        },

        clearFilters() {
            this.filters.academic_session_id = '';
            this.filters.hostel_id = '';
            this.filters.date_from = '';
            this.filters.date_to = '';
        },

        exportReport(format) {
            const params = new URLSearchParams(this.filters);
            params.append('format', format);
            window.location.href = `/hostels/report/export?${params.toString()}`;
        },

        printReport() {
            window.print();
        }
    }
}
</script>
@endpush

@push('styles')
<style>
@media print {
    .btn, .form-select, .form-control, nav, .breadcrumb {
        display: none !important;
    }
    
    .card {
        break-inside: avoid;
    }
}

[dir="rtl"] .me-1 {
    margin-right: 0 !important;
    margin-left: 0.25rem !important;
}

[dir="rtl"] .me-2 {
    margin-right: 0 !important;
    margin-left: 0.5rem !important;
}

[dir="rtl"] .text-end {
    text-align: start !important;
}
</style>
@endpush
