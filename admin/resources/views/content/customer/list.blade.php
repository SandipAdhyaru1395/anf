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

@section('page-script')
@vite(['resources/assets/js/customer-list.js','resources/assets/js/modal-add-customer.js'])
<script>

   @if ($errors->add->any())
          document.addEventListener("DOMContentLoaded", function () {
              // let offcanvasCustomerAdd = new bootstrap.Offcanvas(document.getElementById('offcanvasCustomerAdd'));
              // offcanvasCustomerAdd.show();
              let addCustomerModal = new bootstrap.Modal(document.getElementById('addCustomerModal'));
              addCustomerModal.show();
          });
    @endif
    

</script>
@endsection

@section('content')
<!-- customers List Table -->
<div class="card">
  <div class="card-datatable table-responsive">
    <table class="datatables-customers table border-top">
      <thead>
        <tr>
          <th></th>
          <th></th>
          <th>Customer</th>
          <th>Phone</th>
          <th class="text-nowrap">Credit Balance</th>
          <th>Order</th>
          <th class="text-nowrap">Total Spent</th>
          <th class="text-nowrap text-center">Actions</th>
        </tr>
      </thead>
    </table>
  </div>
  <!-- Offcanvas to add new customer -->
  @include('_partials/_modals/modal-add-customer')
</div>
@endsection