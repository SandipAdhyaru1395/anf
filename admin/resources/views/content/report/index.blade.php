@extends('layouts/layoutMaster')

@section('title', 'Reports')

@section('content')
<div class="row g-4">
  <!-- Sales Report Card -->
  <div class="col-xl-3 col-md-6">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div>
            <h5 class="card-title mb-0">Sales Report</h5>
            <p class="text-muted mb-0">View sales analytics</p>
          </div>
          <div class="avatar">
            <span class="avatar-initial bg-label-primary rounded">
              <i class="icon-base ti tabler-chart-line icon-lg"></i>
            </span>
          </div>
        </div>
        <a href="{{ route('report.sales') }}" class="btn btn-primary w-100">View Report</a>
      </div>
    </div>
  </div>
  <!-- Sales Report Card -->

  <!-- VAT Report Card -->
  <div class="col-xl-3 col-md-6">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div>
            <h5 class="card-title mb-0">Net VAT Report</h5>
            <p class="text-muted mb-0">View VAT details</p>
          </div>
          <div class="avatar">
            <span class="avatar-initial bg-label-info rounded">
              <i class="icon-base ti tabler-receipt icon-lg"></i>
            </span>
          </div>
        </div>
        <a href="{{ route('report.net-vat') }}" class="btn btn-info w-100">View Report</a>
      </div>
    </div>
  </div>
  <!-- VAT Report Card -->

  <!-- Daily Sales Report Card -->
  <div class="col-xl-3 col-md-6">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div>
            <h5 class="card-title mb-0">Daily Sales Report</h5>
            <p class="text-muted mb-0">View daily sales data</p>
          </div>
          <div class="avatar">
            <span class="avatar-initial bg-label-success rounded">
              <i class="icon-base ti tabler-calendar-event icon-lg"></i>
            </span>
          </div>
        </div>
        <a href="{{ route('report.daily-sales') }}" class="btn btn-success w-100">View Report</a>
      </div>
    </div>
  </div>
  <!-- Daily Sales Report Card -->

  <!-- Monthly Sales Report Card -->
  <div class="col-xl-3 col-md-6">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div>
            <h5 class="card-title mb-0">Monthly Sales Report</h5>
            <p class="text-muted mb-0">View monthly sales data</p>
          </div>
          <div class="avatar">
            <span class="avatar-initial bg-label-warning rounded">
              <i class="icon-base ti tabler-chart-bar icon-lg"></i>
            </span>
          </div>
        </div>
        <a href="{{ route('report.monthly-sales') }}" class="btn btn-warning w-100">View Report</a>
      </div>
    </div>
  </div>
  <!-- Monthly Sales Report Card -->
</div>
@endsection

