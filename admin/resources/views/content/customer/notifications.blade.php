@extends('layouts/layoutMaster')

@section('title', 'Customer - Notifications')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/@form-validation/popular.js',
'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
'resources/assets/vendor/libs/@form-validation/auto-focus.js',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'
])

@endsection

@section('page-script')
@vite(['resources/assets/js/customer-detail.js',
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
          <a class="nav-link" href="{{ url('customer/'.$customer->id .'/security') }}"><i
              class="icon-base ti tabler-lock icon-sm me-1_5"></i>Security</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ url('customer/'.$customer->id.'/branches') }}"><i
              class="icon-base ti tabler-map-pin icon-sm me-1_5"></i>Branches</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" href="javascript:void(0);"><i
              class="icon-base ti tabler-bell icon-sm me-1_5"></i>Notifications</a>
        </li>
      </ul>
    </div>
    <!--/ Customer Pills -->
    <!-- Project table -->
    <div class="card mb-6">
      <!-- Notifications -->
      <div class="card-header">
        <h5 class="card-title mb-1">Notifications</h5>
        <span class="text-body">You will receive notification for the below selected items.</span>
      </div>
      <div>
        <div class="table-responsive border-bottom">
          <table class="table">
            <thead>
              <tr>
                <th class="text-nowrap">Type</th>
                <th class="text-nowrap text-center">Email</th>
                <th class="text-nowrap text-center">Browser</th>
                <th class="text-nowrap text-center">App</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="text-nowrap text-heading">New for you</td>
                <td>
                  <div class="form-check d-flex justify-content-center mb-0">
                    <input class="form-check-input" type="checkbox" id="defaultCheck1" checked />
                  </div>
                </td>
                <td>
                  <div class="form-check d-flex justify-content-center mb-0">
                    <input class="form-check-input" type="checkbox" id="defaultCheck2" checked />
                  </div>
                </td>
                <td>
                  <div class="form-check d-flex justify-content-center mb-0">
                    <input class="form-check-input" type="checkbox" id="defaultCheck3" checked />
                  </div>
                </td>
              </tr>
              <tr>
                <td class="text-nowrap text-heading">Account activity</td>
                <td>
                  <div class="form-check d-flex justify-content-center mb-0">
                    <input class="form-check-input" type="checkbox" id="defaultCheck4" checked />
                  </div>
                </td>
                <td>
                  <div class="form-check d-flex justify-content-center mb-0">
                    <input class="form-check-input" type="checkbox" id="defaultCheck5" checked />
                  </div>
                </td>
                <td>
                  <div class="form-check d-flex justify-content-center mb-0">
                    <input class="form-check-input" type="checkbox" id="defaultCheck6" checked />
                  </div>
                </td>
              </tr>
              <tr>
                <td class="text-nowrap text-heading">A new browser used to sign in</td>
                <td>
                  <div class="form-check d-flex justify-content-center mb-0">
                    <input class="form-check-input" type="checkbox" id="defaultCheck7" checked />
                  </div>
                </td>
                <td>
                  <div class="form-check d-flex justify-content-center mb-0">
                    <input class="form-check-input" type="checkbox" id="defaultCheck8" checked />
                  </div>
                </td>
                <td>
                  <div class="form-check d-flex justify-content-center mb-0">
                    <input class="form-check-input" type="checkbox" id="defaultCheck9" />
                  </div>
                </td>
              </tr>
              <tr class="border-transparent">
                <td class="text-nowrap text-heading">A new device is linked</td>
                <td>
                  <div class="form-check d-flex justify-content-center mb-0">
                    <input class="form-check-input" type="checkbox" id="defaultCheck10" checked />
                  </div>
                </td>
                <td>
                  <div class="form-check d-flex justify-content-center mb-0">
                    <input class="form-check-input" type="checkbox" id="defaultCheck11" />
                  </div>
                </td>
                <td>
                  <div class="form-check d-flex justify-content-center mb-0">
                    <input class="form-check-input" type="checkbox" id="defaultCheck12" />
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="card-body pt-6">
        <button type="submit" class="btn btn-primary me-3">Save changes</button>
        <button type="reset" class="btn btn-label-secondary">Discard</button>
      </div>
      <!-- /Notifications -->
    </div>
    <!-- /Project table -->
  </div>
  <!--/ Customer Content -->
</div>

<!-- Modal -->
@include('_partials/_modals/modal-upgrade-plan')
<!-- /Modal -->
@endsection