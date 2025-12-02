@extends('layouts/layoutMaster')

@section('title', 'Dashboard')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/apex-charts/apex-charts.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/apex-charts/apexcharts.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/app-ecommerce-dashboard.js'])
@endsection

@section('content')
    <div class="row g-6">
        <!-- View sales -->
        <div class="col-xl-4">
            <div class="card">
                <div class="d-flex align-items-end row">
                    <div class="col-7">
                        <div class="card-body text-nowrap">
                            <h5 class="card-title mb-0">Congratulations John! 🎉</h5>
                            <p class="mb-2">Best seller of the month</p>
                            <h4 class="text-primary mb-1">{{ $setting['currency_symbol'] ?? ''}}48.9k</h4>
                            <a href="javascript:;" class="btn btn-primary">View Sales</a>
                        </div>
                    </div>
                    <div class="col-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <img src="{{ asset('assets/img/illustrations/card-advance-sale.png') }}" height="140"
                                alt="view sales" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- View sales -->

        <!-- Statistics -->
        <div class="col-xl-8 col-md-12">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title mb-0">Statistics</h5>
                    <small class="text-body-secondary">Updated 1 month ago</small>
                </div>
                <div class="card-body d-flex align-items-end">
                    <div class="w-100">
                        <div class="row gy-3">
                            <div class="col-md-3 col-6">
                                <div class="d-flex align-items-center">
                                    <div class="badge rounded bg-label-primary me-4 p-2">
                                        <i class="icon-base ti tabler-chart-pie-2 icon-lg"></i>
                                    </div>
                                    <div class="card-info">
                                        <h5 class="mb-0">230k</h5>
                                        <small>Sales</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="d-flex align-items-center">
                                    <div class="badge rounded bg-label-info me-4 p-2"><i
                                            class="icon-base ti tabler-users icon-lg"></i>
                                    </div>
                                    <div class="card-info">
                                        <h5 class="mb-0">8.549k</h5>
                                        <small>Customers</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="d-flex align-items-center">
                                    <div class="badge rounded bg-label-danger me-4 p-2">
                                        <i class="icon-base ti tabler-shopping-cart icon-lg"></i>
                                    </div>
                                    <div class="card-info">
                                        <h5 class="mb-0">1.423k</h5>
                                        <small>Products</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="d-flex align-items-center">
                                    <div class="badge rounded bg-label-success me-4 p-2">
                                        <i class="icon-base ti tabler-currency-dollar icon-lg"></i>
                                    </div>
                                    <div class="card-info">
                                        <h5 class="mb-0">{{ $setting['currency_symbol'] ?? ''}}9745</h5>
                                        <small>Revenue</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--/ Statistics -->

        <div class="col-xxl-4 col-12">
            <div class="row g-6">
                <!-- Profit last month -->
                <div class="col-xl-6 col-sm-6">
                    <div class="card h-100">
                        <div class="card-header pb-0">
                            <h5 class="card-title mb-1">Profit</h5>
                            <p class="card-subtitle">Last Month</p>
                        </div>
                        <div class="card-body">
                            <div id="profitLastMonth"></div>
                            <div class="d-flex justify-content-between align-items-center mt-3 gap-3">
                                <h4 class="mb-0">624k</h4>
                                <small class="text-success">+8.24%</small>
                            </div>
                        </div>
                    </div>
                </div>
                <!--/ Profit last month -->

                <!-- Expenses -->
                <div class="col-xl-6 col-sm-6">
                    <div class="card h-100">
                        <div class="card-header pb-2">
                            <h5 class="card-title mb-1">82.5k</h5>
                            <p class="card-subtitle">Expenses</p>
                        </div>
                        <div class="card-body">
                            <div id="expensesChart"></div>
                            <div class="mt-3 text-center">
                                <small class="text-body-secondary mt-3">{{ $setting['currency_symbol'] ?? ''}}21k Expenses more than last month</small>
                            </div>
                        </div>
                    </div>
                </div>
                <!--/ Expenses -->

                <!-- Generated Leads -->
                <div class="col-xl-12">
                    <div class="card h-100">
                        <div class="card-body d-flex justify-content-between">
                            <div class="d-flex flex-column">
                                <div class="card-title mb-auto">
                                    <h5 class="mb-0 text-nowrap">Generated Leads</h5>
                                    <p class="mb-0">Monthly Report</p>
                                </div>
                                <div class="chart-statistics">
                                    <h3 class="card-title mb-0">4,350</h3>
                                    <p class="text-success text-nowrap mb-0"><i
                                            class="icon-base ti tabler-chevron-up me-1"></i> 15.8%</p>
                                </div>
                            </div>
                            <div id="generatedLeadsChart"></div>
                        </div>
                    </div>
                </div>
                <!--/ Generated Leads -->
            </div>
        </div>

        <!-- Revenue Report -->
        <div class="col-xxl-8">
            <div class="card h-100">
                <div class="card-body p-0">
                    <div class="row row-bordered g-0">
                        <div class="col-md-8 position-relative p-6">
                            <div class="card-header d-inline-block p-0 text-wrap position-absolute">
                                <h5 class="m-0 card-title">Revenue Report</h5>
                            </div>
                            <div id="totalRevenueChart" class="mt-n1"></div>
                        </div>
                        <div class="col-md-4 p-4">
                            <div class="text-center mt-5">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-label-primary dropdown-toggle" type="button"
                                        id="budgetId" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <script>
                                            document.write(new Date().getFullYear());
                                        </script>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="budgetId">
                                        <a class="dropdown-item prev-year1" href="javascript:void(0);">
                                            <script>
                                                document.write(new Date().getFullYear() - 1);
                                            </script>
                                        </a>
                                        <a class="dropdown-item prev-year2" href="javascript:void(0);">
                                            <script>
                                                document.write(new Date().getFullYear() - 2);
                                            </script>
                                        </a>
                                        <a class="dropdown-item prev-year3" href="javascript:void(0);">
                                            <script>
                                                document.write(new Date().getFullYear() - 3);
                                            </script>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <h3 class="text-center pt-8 mb-0">{{ $setting['currency_symbol'] ?? ''}}25,825</h3>
                            <p class="mb-8 text-center"><span class="fw-medium text-heading">Budget: </span>56,800</p>
                            <div class="px-3">
                                <div id="budgetChart"></div>
                            </div>
                            <div class="text-center mt-8">
                                <button type="button" class="btn btn-primary">Increase Button</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--/ Revenue Report -->
    </div>
@endsection
