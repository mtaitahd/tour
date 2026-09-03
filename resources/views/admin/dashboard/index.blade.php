@extends('admin.layouts.app')
@section('title', 'Dashboard')

@section('content')
    {{-- Page Heading --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h4 class="mb-0 text-gray-800">
            <i class="fas fa-tachometer-alt" style="color:var(--primary); margin-right:8px;"></i>
            Welcome, {{ auth()->user()->name ?? 'Admin' }}!
        </h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Dashboard</li>
        </ol>
    </div>

    <section class="section dashboard">
        {{-- Stat Cards --}}
        <div class="row mb-4">
            {{-- Total Tours Card --}}
            <div class="col-xl-4 col-md-6 mb-4">
                <a href="{{ route('admin.tour-packages.index') }}"
                   style="text-decoration:none"
                   {{ auth()->user()->canAccess('tours') ? '' : 'data-perm-denied data-perm-label="Tours & Packages"' }}>
                    <div class="card h-100" style="cursor:pointer; transition: transform .15s, box-shadow .15s;">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1">Total Tours</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalTours ?? 0 }}</div>
                                    <div class="mt-2 mb-0 text-muted text-xs">
                                        <span class="text-success mr-2"><i class="fas fa-arrow-up"></i> +{{ $newToursThisMonth ?? 0 }}</span>
                                        <span>this month</span>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-hiking fa-2x text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            {{-- Inquiries Card --}}
            <div class="col-xl-4 col-md-6 mb-4">
                <a href="{{ route('admin.inquiries.index') }}"
                   style="text-decoration:none"
                   {{ auth()->user()->canAccess('inquiries') ? '' : 'data-perm-denied data-perm-label="Bookings / Inquiries"' }}>
                    <div class="card h-100" style="cursor:pointer; transition: transform .15s, box-shadow .15s;">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1">Inquiries This Month</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalInquiriesThisMonth ?? 0 }}</div>
                                    <div class="mt-2 mb-0 text-muted text-xs">
                                        <span class="text-success mr-2"><i class="fas fa-arrow-up"></i> +{{ $newInquiriesToday ?? 0 }}</span>
                                        <span>today</span>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-envelope-open-text fa-2x text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            {{-- Revenue Card --}}
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-uppercase mb-1">Revenue This Month</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($revenueThisMonth ?? 0, 0) }}</div>
                                <div class="mt-2 mb-0 text-muted text-xs">
                                    <span class="text-success mr-2"><i class="fas fa-arrow-up"></i> +{{ $revenueGrowthPercent ?? 0 }}%</span>
                                    <span>vs last month</span>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-dollar-sign fa-2x text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Chart Row --}}
        <div class="row">
            {{-- Inquiries Trend Chart --}}
            <div class="col-xl-8 col-lg-7 mb-4">
                <div class="card">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Inquiries Trend (Monthly Totals)</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-area" style="height:300px">
                            <canvas id="inquiriesAreaChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tours by Destination Chart --}}
            <div class="col-xl-4 col-lg-5 mb-4">
                <div class="card">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Tours by Destination</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-pie pt-4 pb-2" style="height:250px">
                            <canvas id="destinationPieChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@php
    $pieMap = [];
    foreach (\App\Models\TourPackage::with('destinations')->get() as $tp) {
        if ($tp->destinations->count()) {
            foreach ($tp->destinations as $dest) {
                $pieMap[$dest->name] = ($pieMap[$dest->name] ?? 0) + 1;
            }
        } else {
            $pieMap['General'] = ($pieMap['General'] ?? 0) + 1;
        }
    }
    $pieLabels = array_keys($pieMap);
    $pieData   = array_values($pieMap);
@endphp

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
<script>
    $(document).ready(function () {
        Chart.defaults.global.defaultFontFamily = 'Nunito, -apple-system, system-ui, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
        Chart.defaults.global.defaultFontColor = '#94A3B8';

        // Inquiries Trend (area)
        var areaCtx = document.getElementById("inquiriesAreaChart");
        new Chart(areaCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($inquiriesTrendDates ?? []) !!},
                datasets: [{
                    label: "Inquiries",
                    fill: true,
                    lineTension: 0.3,
                    backgroundColor: "rgba(37, 99, 235, 0.5)",
                    borderColor: "rgba(37, 99, 235, 1)",
                    pointRadius: 3,
                    pointBackgroundColor: "rgba(37, 99, 235, 1)",
                    pointBorderColor: "rgba(37, 99, 235, 1)",
                    pointHoverRadius: 3,
                    pointHoverBackgroundColor: "rgba(37, 99, 235, 1)",
                    pointHoverBorderColor: "rgba(37, 99, 235, 1)",
                    pointHitRadius: 10,
                    pointBorderWidth: 2,
                    data: {!! json_encode($inquiriesTrendData ?? []) !!}
                }]
            },
            options: {
                maintainAspectRatio: false,
                layout: { padding: { left: 10, right: 25, top: 25, bottom: 0 } },
                scales: {
                    xAxes: [{ gridLines: { display: false, drawBorder: false }, ticks: { maxTicksLimit: 7 } }],
                    yAxes: [{ ticks: { maxTicksLimit: 5, padding: 10 }, gridLines: { color: "rgb(226, 232, 240)", zeroLineColor: "rgb(226, 232, 240)", drawBorder: false, borderDash: [2], zeroLineBorderDash: [2] } }]
                },
                legend: { display: false },
                tooltips: {
                    backgroundColor: "rgb(255,255,255)", bodyFontColor: "#94A3B8", titleMarginBottom: 10, titleFontColor: '#475569',
                    titleFontSize: 14, borderColor: '#E2E8F0', borderWidth: 1, xPadding: 15, yPadding: 15, displayColors: false,
                    intersect: false, mode: 'index', caretPadding: 10
                }
            }
        });

        // Tours by Destination (doughnut)
        var pieLabels = @json($pieLabels);
        var pieData = @json($pieData);
        var pieCtx = document.getElementById("destinationPieChart");
        new Chart(pieCtx, {
            type: 'doughnut',
            data: {
                labels: pieLabels,
                datasets: [{
                    data: pieData,
                    backgroundColor: ["#2563EB", "#10B981", "#38BDF8", "#F59E0B", "#F43F5E", "#8B5CF6", "#94A3B8"],
                    hoverBackgroundColor: ["#1D4ED8", "#059669", "#0EA5E9", "#D97706", "#E11D48", "#7C3AED", "#64748B"],
                    hoverBorderColor: "rgba(226, 232, 240, 1)"
                }]
            },
            options: {
                maintainAspectRatio: false,
                tooltips: { backgroundColor: "rgb(255,255,255)", bodyFontColor: "#94A3B8", borderColor: '#E2E8F0', borderWidth: 1, xPadding: 15, yPadding: 15, displayColors: false, caretPadding: 10 },
                legend: { display: false },
                cutoutPercentage: 80
            }
        });
    });
</script>
@endpush
