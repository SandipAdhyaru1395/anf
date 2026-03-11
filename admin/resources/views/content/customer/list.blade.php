@extends('layouts/layoutMaster')
@section('title', 'Customer List')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
'resources/assets/vendor/libs/@form-validation/form-validation.scss',
'resources/assets/vendor/libs/select2/select2.scss','resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
    'resources/assets/vendor/libs/select2/select2.js',
  'resources/assets/vendor/libs/@form-validation/popular.js','resources/assets/vendor/libs/@form-validation/bootstrap5.js','resources/assets/vendor/libs/@form-validation/auto-focus.js',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-style')
<style>
  .customer-list-header {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 0;
  }
  .customer-list-header .page-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #566a7f;
    margin: 0;
  }
  .customer-list-actions {
    display: flex;
    align-items: center;
    gap: 0;
    flex-wrap: wrap;
  }
  .customer-list-actions a {
    color: #696cff;
    text-decoration: none;
    font-weight: 500;
    padding: 0 0.75rem;
    border-right: 1px solid #d9dee3;
  }
  .customer-list-actions a:last-child {
    border-right: none;
    padding-right: 0;
  }
  .customer-list-actions a:hover {
    color: #5f61e6;
  }
  .customer-list-actions .dropdown .dropdown-toggle {
    color: #696cff;
    text-decoration: none;
    font-weight: 500;
    padding: 0 0.75rem;
  }
  .customer-list-actions .dropdown .dropdown-toggle:hover {
    color: #5f61e6;
  }
  /* Export dropdown items: taller hit area */
  .customer-list-actions .dropdown-menu .dropdown-item {
    padding-top: 0.75rem;
    padding-bottom: 0.75rem;
    line-height: 1.2;
  }
  .customer-list-filter {
    /* background-color: #f5f5f9; */
    padding: 1.05rem 1.25rem;
    border-radius: 0.375rem;
    display: flex;
    flex-wrap: wrap;
    align-items: stretch;
    gap: 0.5rem;
    min-height: 64px;
    margin-bottom: 1rem;
  }
  /* Make inner controls match the bar height */
  .customer-list-filter .filter-show,
  .customer-list-filter .btn-go,
  .customer-list-filter .form-select,
  .customer-list-filter .form-control {
    height: 44px;
  }
  .customer-list-filter .form-control,
  .customer-list-filter .form-select {
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
  }
  .customer-list-filter .btn-go {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0 1rem;
    line-height: 1;
  }
  .customer-list-filter .filter-show {
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  .customer-list-filter .filter-show label {
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
  #customer-search-input {
    min-width: 320px;
  }
  .customer-list-filter .form-select,
  .customer-list-filter .form-control {
    border-radius: 0.375rem;
    border-color: #d9dee3;
  }
  .customer-list-filter .btn-go {
    background-color: #e7e7ff;
    color: #566a7f;
    border: none;
    padding: 0.47rem 1rem;
    border-radius: 0.375rem;
    font-weight: 500;
  }
  .customer-list-filter .btn-go:hover {
    background-color: #ddddf7;
    color: #566a7f;
  }
  .datatables-customers thead th {
    background-color: #f5f5f9;
    font-weight: 600;
    color: #566a7f;
    border-bottom: 1px solid #d9dee3;
  }
  .datatables-customers tbody td {
    vertical-align: middle;
    /* Increase row height */
    padding-top: 0.9rem;
    padding-bottom: 0.9rem;
  }
  .datatables-customers tbody tr {
    cursor: pointer;
  }
  .datatables-customers tbody tr:hover {
    background-color: #f5f5f9;
  }
  .badge-status-active {
    background-color: #71dd37;
    color: #fff;
    padding: 0.35em 0.65em;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
  }
  .badge-status-inactive {
    background-color: #ffab00;
    color: #fff;
    padding: 0.35em 0.65em;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
  }
</style>
@endsection

@section('page-script')
@vite(['resources/assets/js/customer-list.js','resources/assets/js/modal-add-customer.js'])
<script>

   @if ($errors->add->any())
          document.addEventListener("DOMContentLoaded", function () {
              let addCustomerModal = new bootstrap.Modal(document.getElementById('addCustomerModal'));
              addCustomerModal.show();
          });
    @endif


</script>
@endsection

@section('content')
<!-- Customers List -->
<div class="card">
  <div class="card-body">
    <!-- Header: Title + Action links -->
    <div class="customer-list-header mb-4">
      <h5 class="page-title">Customers</h5>
      <div class="customer-list-actions">
        <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#addCustomerModal">New Customer</a>
        <a href="{{ route('settings.customerGroup') }}">Groups</a>
        <div class="dropdown d-inline-block">
          <a href="javascript:void(0);" class="dropdown-toggle" id="customer-export-trigger" data-bs-toggle="dropdown" aria-expanded="false">Export</a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="customer-export-trigger">
            <li><a class="dropdown-item customer-export-action" href="javascript:void(0);" data-export="print">Print</a></li>
            <li><a class="dropdown-item customer-export-action" href="javascript:void(0);" data-export="csv">CSV</a></li>
            <li><a class="dropdown-item customer-export-action" href="javascript:void(0);" data-export="excel">Excel</a></li>
            <li><a class="dropdown-item customer-export-action" href="javascript:void(0);" data-export="pdf">PDF</a></li>
            <li><a class="dropdown-item customer-export-action" href="javascript:void(0);" data-export="copy">Copy</a></li>
          </ul>
        </div>
      </div>
    </div>
    <div id="customer-export-buttons-placeholder" class="d-none"></div>

    <!-- Filter bar: Show + Search + Go -->
    <div class="customer-list-filter">
      <div class="filter-show">
        <label for="filter-show">Show</label>
        <select class="form-select form-select-sm" id="filter-show" style="width: auto; min-width: 160px;">
          <option value="">All</option>
          <option value="active">Active only</option>
          <option value="inactive">Inactive only</option>
        </select>
      </div>
      <div class="d-flex align-items-center flex-grow-1 flex-sm-grow-0" style="min-width: 200px;">
        <input type="search" class="form-control form-control-sm" id="customer-search-input" placeholder="Search customers" aria-label="Search customers">
      </div>
      <button type="button" class="btn btn-sm btn-go" id="customer-search-go">Go</button>
    </div>

    <div class="card-datatable table-responsive">
      <table class="datatables-customers table border-top">
        <thead>
          <tr>
            <th>Name</th>
            <th>Main Contact</th>
            <th>Group</th>
            <th>Last Seen</th>
            <th>Last Order</th>
            <th>Min. Spend</th>
            <th>Status</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
  @include('_partials/_modals/modal-add-customer')
</div>
@endsection
