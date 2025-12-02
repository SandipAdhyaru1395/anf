@extends('layouts/layoutMaster')

@section('title', 'Customer - Security')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/@form-validation/popular.js',
'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
'resources/assets/vendor/libs/@form-validation/auto-focus.js',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')
@vite(['resources/assets/js/customer-detail.js','resources/assets/js/customer-security.js',
'resources/assets/js/modal-edit-customer.js'])
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
          <a class="nav-link" href="{{ url('customer/'.$customer->id .'/overview') }}"><i
              class="icon-base ti tabler-user icon-sm me-1_5"></i>Overview</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" href="javascript:void(0);"><i
              class="icon-base ti tabler-lock icon-sm me-1_5"></i>Security</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ url('customer/'.$customer->id.'/branches') }}"><i
              class="icon-base ti tabler-map-pin icon-sm me-1_5"></i>Branches</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ url('customer/'.$customer->id.'/notifications') }}"><i
              class="icon-base ti tabler-bell icon-sm me-1_5"></i>Notifications</a>
        </li>
      </ul>
    </div>
    <!--/ Customer Pills -->
    <!-- Change Password -->
    <div class="card mb-6">
      <h5 class="card-header">Change Password</h5>
      <div class="card-body">
        <form id="formChangePassword" method="POST" action="{{ route('customer.update-password') }}">
          @csrf
          <input type="hidden" name="id" value="{{ $customer->id }}">
          <div class="row gy-4 gx-6">
            <div class="col-12 col-sm-6 form-password-toggle form-control-validation">
              <label class="form-label" for="newPassword">New Password</label>
              <div class="input-group input-group-merge">
                <input class="form-control" type="password" id="newPassword" name="newPassword"
                  placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                <span class="input-group-text cursor-pointer"><i class="icon-base ti tabler-eye icon-xs"></i></span>
              </div>
            </div>

            <div class="col-12 col-sm-6 form-password-toggle form-control-validation">
              <label class="form-label" for="confirmPassword">Confirm Password</label>
              <div class="input-group input-group-merge">
                <input class="form-control" type="password" name="confirmPassword" id="confirmPassword"
                  placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                <span class="input-group-text cursor-pointer"><i class="icon-base ti tabler-eye icon-xs"></i></span>
              </div>
            </div>
            <div>
              <button type="submit" class="btn btn-primary me-2">Change Password</button>
            </div>
          </div>
        </form>
      </div>
    </div>
    <!--/ Change Password -->
   

    <!-- Recent Devices -->
    <div class="card mb-6">
      <h5 class="card-header">Recent Devices</h5>
      <div class="table-responsive">
        <table class="table border-top table-border-bottom-0">
          <thead>
            <tr>
              <th class="text-truncate">Browser</th>
              <th class="text-truncate">Device</th>
              <th class="text-truncate">Location</th>
              <th class="text-truncate">Recent Activities</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="text-truncate text-heading"><i
                  class="mb-1 icon-base ti tabler-brand-windows icon-md text-info me-4"></i> Chrome on Windows</td>
              <td class="text-truncate">HP Spectre 360</td>
              <td class="text-truncate">Switzerland</td>
              <td class="text-truncate">10, July 2021 20:07</td>
            </tr>
            <tr>
              <td class="text-truncate text-heading"><i
                  class="mb-1 icon-base ti tabler-device-mobile icon-md text-danger me-4"></i> Chrome on iPhone</td>
              <td class="text-truncate">iPhone 12x</td>
              <td class="text-truncate">Australia</td>
              <td class="text-truncate">13, July 2021 10:10</td>
            </tr>
            <tr>
              <td class="text-truncate text-heading"><i
                  class="mb-1 icon-base ti tabler-brand-android icon-md text-success me-4"></i> Chrome on Android</td>
              <td class="text-truncate">Oneplus 9 Pro</td>
              <td class="text-truncate">Dubai</td>
              <td class="text-truncate">14, July 2021 15:15</td>
            </tr>
            <tr>
              <td class="text-truncate text-heading"><i
                  class="mb-1 icon-base ti tabler-brand-apple icon-md me-4"></i>Chrome on MacOS</td>
              <td class="text-truncate">Apple iMac</td>
              <td class="text-truncate">India</td>
              <td class="text-truncate">16, July 2021 16:17</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <!--/ Recent Devices -->
  </div>
  <!--/ Customer Content -->
</div>

<!-- Modal -->
@include('_partials/_modals/modal-enable-otp')
@include('_partials/_modals/modal-upgrade-plan')
<!-- /Modal -->
@endsection