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
@php
  $customerName = $customer->company_name ?: ($customer->email ?: 'Customer');
@endphp

<style>
  .customer-topbar {
    background: #fff;
    border: 1px solid #eceef1;
    border-radius: .375rem;
    padding: 1rem 1.25rem;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1rem;
  }
  .customer-breadcrumb {
    display: flex;
    align-items: center;
    gap: .5rem;
    font-size: 1rem;
    font-weight: 600;
    color: #566a7f;
  }
  .customer-breadcrumb a {
    color: #696cff;
    text-decoration: none;
    font-weight: 600;
  }
  .customer-breadcrumb a:hover {
    color: #5f61e6;
    text-decoration: underline;
  }
  .customer-breadcrumb .muted { color: #a1acb8; font-weight: 500; }
</style>

<div class="customer-topbar">
  <div class="customer-breadcrumb">
    <a href="{{ route('customer.list') }}">Customers</a>
    <span class="muted">/</span>
    <a href="{{ route('customer.overview', $customer->id) }}">{{ $customerName }}</a>
    <span class="muted">/</span>
    <span>Security</span>
  </div>
</div>

<div class="row">
  <div class="col-md-8 col-lg-6">
    <div class="card mb-6">
      <h5 class="card-header">Change Password</h5>
      <div class="card-body">
        <form id="formChangePassword" method="POST" action="{{ route('customer.update-password') }}">
          @csrf
          <input type="hidden" name="id" value="{{ $customer->id }}">
          <div class="row gy-4 gx-6">
            <div class="col-12 form-password-toggle form-control-validation">
              <label class="form-label" for="newPassword">New Password</label>
              <div class="input-group input-group-merge">
                <input class="form-control" type="password" id="newPassword" name="newPassword"
                  placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                <span class="input-group-text cursor-pointer"><i class="icon-base ti tabler-eye icon-xs"></i></span>
              </div>
            </div>

            <div class="col-12 form-password-toggle form-control-validation">
              <label class="form-label" for="confirmPassword">Confirm Password</label>
              <div class="input-group input-group-merge">
                <input class="form-control" type="password" name="confirmPassword" id="confirmPassword"
                  placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                <span class="input-group-text cursor-pointer"><i class="icon-base ti tabler-eye icon-xs"></i></span>
              </div>
            </div>
            <div class="col-12">
              <button type="submit" class="btn btn-primary me-2">Change Password</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

@endsection