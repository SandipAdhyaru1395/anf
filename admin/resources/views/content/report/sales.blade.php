@extends('layouts/layoutMaster')

@section('title', 'Sales Report')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
'resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
'resources/assets/vendor/libs/flatpickr/flatpickr.js',
'resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('page-script')
@vite(['resources/assets/js/sales-report.js'])
@endsection

@section('content')
<div style="background: var(--bs-body-bg);"
                class="py-5 px-2 card-header sticky-element d-flex justify-content-sm-between align-items-sm-center flex-column flex-sm-row">
                <div class="d-flex flex-column justify-content-center">
                    <h4 class="mb-1">Sales Order Report</h4>
                </div>
                <div class="d-flex align-content-center flex-wrap gap-4">
                    <div class="d-flex gap-4">
                        <a href="{{ route('report.index') }}" class="btn btn-secondary">Back To Reports</a>
                    </div>
                </div>
            </div>
<!-- Sales Report Table -->
<div class="card">
  <!-- Filters -->
  <div class="card-header border-bottom">
    
    <div class="row g-3">
      <div class="col-md-3">
        <label class="form-label">Customer</label>
        <select id="filter-customer" class="form-select select2">
          <option value="">All Customers</option>
          @foreach($customers as $customer)
            <option value="{{ $customer->id }}">{{ $customer->company_name ?? $customer->email }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label">Start Date</label>
        <input type="text" id="filter-start-date" class="form-control flatpickr" placeholder="Select start date" autocomplete="off">
      </div>
      <div class="col-md-2">
        <label class="form-label">End Date</label>
        <input type="text" id="filter-end-date" class="form-control flatpickr" placeholder="Select end date" autocomplete="off">
      </div>
      <div class="col-md-2">
        <label class="form-label">Payment Status</label>
        <select id="filter-payment-status" class="form-select">
          <option value="">All Status</option>
          <option value="Due">Due</option>
          <option value="Partial">Partial</option>
          <option value="Paid">Paid</option>
        </select>
      </div>
      <div class="col-md-3 d-flex align-items-end">
        <button type="button" id="btn-clear-filters" class="btn btn-label-secondary w-100">Clear Filters</button>
      </div>
    </div>
  </div>
  <div class="card-datatable table-responsive">
    <table class="datatables-sales-report table border-top">
      <thead>
        <tr>
          <th>Date</th>
          <th>Invoice No</th>
          <th>Customer</th>
          <th>Gross Total</th>
          <th>VAT</th>
          <th>Net Total</th>
          <th>Paid</th>
          <th>Balance</th>
          <th>Payment Status</th>
        </tr>
      </thead>
      <tfoot>
        <tr>
          <th colspan="3" class="text-end fw-bold"></th>
          <th id="total-subtotal" class="fw-bold"></th>
          <th id="total-vat" class="fw-bold"></th>
          <th id="total-amount" class="fw-bold"></th>
          <th id="total-paid" class="fw-bold"></th>
          <th id="total-balance" class="fw-bold"></th>
          <th></th>
        </tr>
      </tfoot>
    </table>
  </div>
</div>
@endsection

