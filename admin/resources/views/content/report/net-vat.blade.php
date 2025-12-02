@extends('layouts/layoutMaster')

@section('title', 'Net VAT Report')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/flatpickr/flatpickr.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/flatpickr/flatpickr.js'])
@endsection

@section('page-script')
@vite(['resources/assets/js/net-vat-report.js'])
@endsection

@section('content')
<div style="background: var(--bs-body-bg);"
  class="py-5 px-2 card-header sticky-element d-flex justify-content-sm-between align-items-sm-center flex-column flex-sm-row">
  <div class="d-flex flex-column justify-content-center">
    <h4 class="mb-1">Net VAT Report</h4>
    <p class="text-muted mb-0">Please view the VAT report and you can select the date range to customized the report.</p>
  </div>
  <div class="d-flex align-content-center flex-wrap gap-4">
    <div class="d-flex gap-4 align-items-center">
      <!-- Date Range Picker -->
      <div class="dropdown">
        <button class="btn btn-label-primary dropdown-toggle" type="button" id="date-range-dropdown" data-bs-toggle="dropdown" aria-expanded="false">
          <span id="date-range-display">{{ $startDate }} - {{ $endDate }}</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end" id="date-range-menu" aria-labelledby="date-range-dropdown" style="min-width: 250px;">
          <li><a class="dropdown-item" href="#" data-range="today">Today</a></li>
          <li><a class="dropdown-item" href="#" data-range="yesterday">Yesterday</a></li>
          <li><a class="dropdown-item" href="#" data-range="last-7-days">Last 7 Days</a></li>
          <li><a class="dropdown-item" href="#" data-range="last-30-days">Last 30 Days</a></li>
          <li><a class="dropdown-item" href="#" data-range="this-month">This Month</a></li>
          <li><a class="dropdown-item" href="#" data-range="last-month">Last Month</a></li>
          <li><hr class="dropdown-divider"></li>
          <li>
            <a class="dropdown-item" href="#" id="custom-range-option" data-range="custom">
              <span class="text-primary">Custom Range</span>
            </a>
            <div id="custom-range-panel" style="display: none; padding: 12px;">
              <div class="mb-2">
                <label class="form-label small">FROM</label>
                <input type="text" id="custom-from-date" class="form-control form-control-sm" placeholder="dd/mm/yyyy hh:mm" autocomplete="off">
              </div>
              <div class="mb-3">
                <label class="form-label small">TO</label>
                <input type="text" id="custom-to-date" class="form-control form-control-sm" placeholder="dd/mm/yyyy hh:mm" autocomplete="off">
              </div>
              <div class="d-flex gap-2">
                <button type="button" id="apply-date-range" class="btn btn-success btn-sm flex-fill">Apply</button>
                <button type="button" id="cancel-date-range" class="btn btn-label-secondary btn-sm flex-fill">Cancel</button>
              </div>
            </div>
          </li>
        </ul>
      </div>
      <a href="{{ route('report.index') }}" class="btn btn-secondary">Back To Reports</a>
    </div>
  </div>
</div>

<div class="row g-4">
  <!-- Net VAT To Pay Card -->
  <div class="col-xl-6 col-md-6">
    <div class="card vat-card" id="net-vat-card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div>
            <h5 class="card-title mb-0">Net VAT To Pay</h5>
            <p class="text-muted mb-0 vat-breakdown">
              Loading...
            </p>
          </div>
          <div class="avatar">
            <span class="avatar-initial bg-label-primary rounded">
              <i class="icon-base ti tabler-receipt icon-lg"></i>
            </span>
          </div>
        </div>
        <h2 class="mb-0 vat-value">Loading...</h2>
      </div>
    </div>
  </div>
  <!-- Net VAT To Pay Card -->

  <!-- Purchase VAT Card -->
  <div class="col-xl-6 col-md-6">
    <div class="card vat-card" id="purchase-vat-card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div>
            <h5 class="card-title mb-0">Purchase VAT</h5>
          </div>
          <div class="avatar">
            <span class="avatar-initial bg-label-info rounded">
              <i class="icon-base ti tabler-shopping-cart icon-lg"></i>
            </span>
          </div>
        </div>
        <h2 class="mb-0 vat-value">Loading...</h2>
      </div>
    </div>
  </div>
  <!-- Purchase VAT Card -->
</div>
@endsection

