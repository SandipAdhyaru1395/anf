@extends('layouts/layoutMaster')

@section('title', 'Order List')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/quill/typography.scss',
'resources/assets/vendor/libs/quill/katex.scss',
'resources/assets/vendor/libs/quill/editor.scss',
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
'resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/flatpickr/flatpickr.js',
'resources/assets/vendor/libs/quill/quill.js',
'resources/assets/vendor/libs/@form-validation/popular.js',
'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
'resources/assets/vendor/libs/@form-validation/auto-focus.js',
'resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('page-script')
@vite(['resources/assets/js/order-list.js', 'resources/assets/js/payment-add.js', 'resources/assets/js/payment-view.js'])
@if(session('order_add_clear_storage'))
<script>
  (function() {
    try {
      if (window && window.localStorage) {
        localStorage.removeItem('order_add_form_v1');
      }
    } catch (e) {
      // ignore
    }
  })();
</script>
@endif
@endsection

@section('page-style')
<style>
  .order-list-header {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 0;
  }
  .order-list-header .page-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #566a7f;
    margin: 0;
  }
  .order-list-actions {
    display: flex;
    align-items: center;
    gap: 0;
    flex-wrap: wrap;
  }
  .order-list-actions a {
    color: #696cff;
    text-decoration: none;
    font-weight: 500;
    padding: 0 0.75rem;
    border-right: 1px solid #d9dee3;
  }
  .order-list-actions a:last-child {
    border-right: none;
    padding-right: 0;
  }
  .order-list-actions a:hover {
    color: #5f61e6;
  }
  .order-list-actions .dropdown .dropdown-toggle {
    color: #696cff;
    text-decoration: none;
    font-weight: 500;
    padding: 0 0.75rem;
  }
  .order-list-actions .dropdown .dropdown-toggle:hover {
    color: #5f61e6;
  }
  /* Export dropdown items: taller hit area (same as customer list) */
  .order-list-actions .dropdown-menu .dropdown-item {
    padding-top: 0.75rem;
    padding-bottom: 0.75rem;
    line-height: 1.2;
  }
  .order-list-filter {
    /* same structure + sizing as customer-list-filter */
    padding: 1.05rem 1.25rem;
    border-radius: 0.375rem;
    display: flex;
    flex-wrap: wrap;
    align-items: stretch;
    gap: 0.5rem;
    min-height: 64px;
    margin-bottom: 1rem;
  }
  .order-list-filter .filter-show,
  .order-list-filter .btn-go,
  .order-list-filter .form-select,
  .order-list-filter .form-control {
    height: 44px;
  }
  .order-list-filter .form-control,
  .order-list-filter .form-select {
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
  }
  .order-list-filter .btn-go {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0 1rem;
    line-height: 1;
  }
  .order-list-filter .filter-show {
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  .order-list-filter .filter-show label {
    margin: 0;
    font-weight: 500;
    color: #566a7f;
    white-space: nowrap;
  }
  /* Show dropdown: remove background and make it wider */
  #filter-show {
    min-width: 220px !important;
    width: 220px;
    background-color: transparent !important;
    box-shadow: none;
  }
  /* Slightly wider search input */
  #order-search-input {
    min-width: 320px;
  }
  .order-list-filter .form-select,
  .order-list-filter .form-control {
    border-radius: 0.375rem;
    border-color: #d9dee3;
  }
  .order-list-filter .btn-go {
    background-color: #e7e7ff;
    color: #566a7f;
    border: none;
    padding: 0.47rem 1rem;
    border-radius: 0.375rem;
    font-weight: 500;
  }
  .order-list-filter .btn-go:hover {
    background-color: #ddddf7;
    color: #566a7f;
  }

  .datatables-order {
    font-size: 0.8125rem;
  }
  .datatables-order thead th {
    padding: 0.4rem 0.6rem;
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
    background-color: #f5f5f9;
    color: #566a7f;
    border-bottom: 1px solid #d9dee3;
  }
  .datatables-order tbody td {
    padding: 0.4rem 0.6rem;
    font-size: 0.8125rem;
    vertical-align: middle;
  }
  .datatables-order tbody tr:hover {
    background-color: #f5f5f9;
  }
  .datatables-order .badge {
    font-size: 0.6875rem;
    padding: 0.2rem 0.4rem;
    line-height: 1.2;
  }
  .datatables-order .btn {
    padding: 0.2rem 0.4rem;
    font-size: 0.75rem;
  }
  .datatables-order .control {
    width: 25px !important;
    padding: 0.4rem 0.15rem !important;
    min-width: 25px !important;
  }
  .datatables-order .dt-checkboxes {
    width: 16px;
    height: 16px;
    margin: 0;
  }
  .datatables-order .text-nowrap {
    white-space: nowrap;
  }
  .card-datatable {
    padding: 0.75rem;
  }
  .datatables-order thead th:nth-child(7),
  .datatables-order thead th:nth-child(8),
  .datatables-order thead th:nth-child(9) {
    width: 80px;
    min-width: 80px;
    text-align: center;
    line-height: 1.2;
  }
  .datatables-order thead th:nth-child(8) {
    width: 90px;
    min-width: 90px;
  }
  .datatables-order tbody td:nth-child(7),
  .datatables-order tbody td:nth-child(8),
  .datatables-order tbody td:nth-child(9) {
    text-align: center;
    width: 80px;
  }
  .datatables-order tbody td:nth-child(8) {
    width: 90px;
  }
  .datatables-order tbody tr {
    cursor: pointer;
  }
  .datatables-order tbody tr td:first-child,
  .datatables-order tbody tr td:last-child {
    cursor: default;
  }
  /* Override global odd/even row colors - make all rows white by default */
  .datatables-order tbody tr:nth-child(odd),
  .datatables-order tbody tr:nth-child(even),
  table.dataTable.datatables-order tbody tr:nth-child(odd),
  table.dataTable.datatables-order tbody tr:nth-child(even) {
    background-color: #ffffff !important;
  }
  .datatables-order tbody tr:nth-child(odd) td,
  .datatables-order tbody tr:nth-child(even) td,
  table.dataTable.datatables-order tbody tr:nth-child(odd) td,
  table.dataTable.datatables-order tbody tr:nth-child(even) td {
    background-color: #ffffff !important;
  }
  /* EST order row background colors - override default white */
  .datatables-order tbody tr.est-order-unpaid,
  table.dataTable.datatables-order tbody tr.est-order-unpaid {
    background-color: #ffe6e6 !important;
  }
  .datatables-order tbody tr.est-order-paid,
  table.dataTable.datatables-order tbody tr.est-order-paid {
    background-color: #e6ffe6 !important;
  }
  /* Ensure all cells in EST rows have the background */
  .datatables-order tbody tr.est-order-unpaid td,
  table.dataTable.datatables-order tbody tr.est-order-unpaid td {
    background-color: #ffe6e6 !important;
  }
  .datatables-order tbody tr.est-order-paid td,
  table.dataTable.datatables-order tbody tr.est-order-paid td {
    background-color: #e6ffe6 !important;
  }
</style>
@endsection

@section('content')
<!-- Order List Widget -->

<!-- Order List Table -->
<div class="card">
  <div class="card-body">
    <div class="order-list-header mb-4">
      <h5 class="page-title">Orders</h5>
      <div class="order-list-actions">
        <a href="{{ url('order/add') }}">New Order</a>
        <div class="dropdown d-inline-block">
          <a href="javascript:void(0);" class="dropdown-toggle" id="order-export-trigger" data-bs-toggle="dropdown" aria-expanded="false">Export</a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="order-export-trigger">
            <li><a class="dropdown-item order-export-action" href="javascript:void(0);" data-export="print">Print</a></li>
            <li><a class="dropdown-item order-export-action" href="javascript:void(0);" data-export="csv">CSV</a></li>
            <li><a class="dropdown-item order-export-action" href="javascript:void(0);" data-export="excel">Excel</a></li>
            <li><a class="dropdown-item order-export-action" href="javascript:void(0);" data-export="pdf">PDF</a></li>
            <li><a class="dropdown-item order-export-action" href="javascript:void(0);" data-export="copy">Copy</a></li>
          </ul>
        </div>
      </div>
    </div>
    <div id="order-export-buttons-placeholder" class="d-none"></div>

    <div class="order-list-filter">
      <div class="filter-show">
        <label for="filter-show">Show</label>
        <select class="form-select form-select-sm" id="filter-show" style="width: auto; min-width: 160px;">
          <option value="">All</option>
          <option value="except_cancelled">All except cancelled</option>
          <option value="cancelled">Cancelled only</option>
        </select>
      </div>
      <div class="d-flex align-items-center flex-grow-1 flex-sm-grow-0" style="min-width: 200px;">
        <input type="search" class="form-control form-control-sm" id="order-search-input" placeholder="Search orders" aria-label="Search orders">
      </div>
      <button type="button" class="btn btn-sm btn-go" id="order-search-go">Go</button>
    </div>

    <div class="card-datatable table-responsive">
      <table class="datatables-order table border-top">
        <thead>
          <tr>
            <th>Order No</th>
            <th>Customer</th>
            <th>Order Date</th>
            <th>Grand Total</th>
            <th>Paid</th>
            <th>Invoice</th>
            <th>Sale<br>Status</th>
            <th>Payment<br>Status</th>
            <th>actions</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</div>

@include('_partials._modals.modal-add-payment')
@include('_partials._modals.modal-view-payments')

@endsection
