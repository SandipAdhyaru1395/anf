@extends('layouts/layoutMaster')

@section('title', 'Customer - Overview')

@section('vendor-style')
  @vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
    'resources/assets/vendor/libs/select2/select2.scss',
    'resources/assets/vendor/libs/@form-validation/form-validation.scss'
  ])
@endsection

@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
    'resources/assets/vendor/libs/select2/select2.js',
    'resources/assets/vendor/libs/@form-validation/popular.js',
    'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
    'resources/assets/vendor/libs/@form-validation/auto-focus.js',
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'
  ])
@endsection

@section('page-script')
  @vite([
    'resources/assets/js/customer-detail.js',
    'resources/assets/js/customer-overview.js',
    'resources/assets/js/modal-edit-customer.js'
  ])
  <script>
    @if ($errors->editCustomer->any())
      document.addEventListener("DOMContentLoaded", function () {
        // let offcanvasCustomerEdit = new bootstrap.Offcanvas(document.getElementById('offcanvasCustomerEdit'));
        // offcanvasCustomerEdit.show();
        let editCustomerModal = new bootstrap.Modal(document.getElementById('editCustomerModal'));
        editCustomerModal.show();
      });
    @endif
  </script>
@endsection

@section('content')
  @include('content.customer.header')

  <div class="row">
    @include('content.customer.sidebar')

    <!-- Customer Content -->
    <div class="col-xl-8 col-lg-7 col-md-7 order-0 order-md-1">
      <!-- Customer Pills -->
      <div class="nav-align-top">
        <ul class="nav nav-pills flex-column flex-md-row mb-6 row-gap-2 flex-wrap">
          <li class="nav-item">
            <a class="nav-link active" href="javascript:void(0);"><i
                class="icon-base ti tabler-user icon-sm me-1_5"></i>Overview</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="{{ url('customer/' . $customer->id . '/security') }}"><i
                class="icon-base ti tabler-lock icon-sm me-1_5"></i>Security</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="{{ url('customer/' . $customer->id . '/branches') }}"><i
                class="icon-base ti tabler-map-pin icon-sm me-1_5"></i>Branches</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="{{ url('customer/' . $customer->id . '/notifications') }}"><i
                class="icon-base ti tabler-bell icon-sm me-1_5"></i>Notifications</a>
          </li>
        </ul>
      </div>
      <!--/ Customer Pills -->

      <!-- / Customer cards -->
      <div class="row text-nowrap">
        <div class="col-md-6 mb-6">
          <div class="card h-100">
            <div class="card-body">
              <div class="card-icon mb-2">
                <div class="avatar">
                  <div class="avatar-initial rounded bg-label-primary">
                    <i class="icon-base ti tabler-currency-dollar icon-lg"></i>
                  </div>
                </div>
              </div>
              <div class="card-info">
                <h5 class="card-title mb-2">Account Balance</h5>
                <div class="d-flex align-items-baseline gap-1">
                  <h5 class="text-primary mb-0">
                    {{ $currencySymbol }}{{ number_format($customer->credit_balance ?? 0, 2) }}</h5>
                  <p class="mb-0">Credit Left</p>
                </div>
                <p class="mb-0 text-truncate">Account balance for next purchase</p>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-6 mb-6">
          <div class="card">
            <div class="card-body">
              <div class="card-icon mb-2">
                <div class="avatar">
                  <div class="avatar-initial rounded bg-label-warning">
                    <i class="icon-base ti tabler-star icon-lg"></i>
                  </div>
                </div>
              </div>
              <div class="card-info">
                <h5 class="card-title mb-2">Wishlist</h5>
                <div class="d-flex align-items-baseline gap-1">
                  <h5 class="text-warning mb-0">15</h5>
                  <p class="mb-0">Items in wishlist</p>
                </div>
                <p class="mb-0 text-truncate">Receive notification when items go on sale</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- / customer cards -->

      <!-- Invoice table -->
      <div class="card mb-6">
        <div class="table-responsive mb-4">
          <table class="table datatables-customer-order" data-customer-id="{{ $customer->id ?? '' }}">
            <thead>
              <tr>
                <th></th>
                <th>Order</th>
                <th>Date</th>
                <th>Payment</th>
                <th>Status</th>
                <th>Spent</th>
                <th class="text-md-center">Actions</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
      <!-- /Invoice table -->
    </div>
    <!--/ Customer Content -->
  </div>
@endsection