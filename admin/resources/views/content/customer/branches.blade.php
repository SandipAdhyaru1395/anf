@extends('layouts/layoutMaster')

@section('title', 'Customer - Branches')

@section('vendor-style')
@vite(['resources/assets/vendor/fonts/flag-icons.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/@form-validation/popular.js',
'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
'resources/assets/vendor/libs/@form-validation/auto-focus.js',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')
@vite(['resources/assets/js/customer-detail.js','resources/assets/js/modal-customer-add-branch.js','resources/assets/js/modal-customer-edit-branch.js',
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
<script>


$(document).ready(function () {

      $('#editCustomerBranch').on('show.bs.modal', function (e) {

        var id = $(e.relatedTarget).data('id');

        $.ajax({
          url: "{{ route('customer.branch.edit') }}",
          type: 'GET',
          data: { id: id },
          success: function (response) {
            if (response.success) {
                const branch = response.branch;
                
                // Populate form fields
                $('#editCustomerBranchForm input[name="id"]').val(branch.id);
                $('#editCustomerBranchForm input[name="name"]').val(branch.name || '');
                $('#editCustomerBranchForm input[name="address_line1"]').val(branch.address_line1 || '');
                $('#editCustomerBranchForm input[name="address_line2"]').val(branch.address_line2 || '');
                $('#editCustomerBranchForm input[name="city"]').val(branch.city || '');
                $('#editCustomerBranchForm input[name="state"]').val(branch.state || '');
                $('#editCustomerBranchForm input[name="zip_code"]').val(branch.zip_code || '');
                $('#editCustomerBranchForm input[name="country"]').val(branch.country || '');
                $('#editCustomerBranchForm input[name="contact_name"]').val(branch.contact_name || '');
      $('#editCustomerBranchForm input[name="is_default_delivery"]').prop('checked', !!branch.is_default_delivery);
      $('#editCustomerBranchForm input[name="is_default_billing"]').prop('checked', !!branch.is_default_billing);
            }

          }
        });
      });
    });

function deleteBranch(branchId) {
   if (branchId) {
      Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        customClass: {
          confirmButton: 'btn btn-primary me-3',
          cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
      }).then(function (result) {
        if(result.isConfirmed){
          window.location.href = baseUrl + 'customer/branch/' + branchId + '/delete';
        }
      });
    }
}


  document.addEventListener('DOMContentLoaded', function(){
    @if ($errors->addBranch->any())
      const addCustomerBranch = document.getElementById('addCustomerBranch');
      if (addCustomerBranch) new bootstrap.Modal(addCustomerBranch).show();
    @endif
    @if ($errors->editBranch->any())
      const editCustomerBranch = document.getElementById('editCustomerBranch');
      if (editCustomerBranch) new bootstrap.Modal(editCustomerBranch).show();
    @endif
  });
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
  .customer-actions a {
    color: #696cff;
    text-decoration: none;
    font-weight: 500;
    white-space: nowrap;
  }
  .customer-actions a:hover { color: #5f61e6; }
  .address-grid { margin-top: 1rem; }
  .address-card,
  .address {
    background: #fff;
    border: 1px solid #eceef1;
    border-radius: .25rem;
    padding: 1.1rem 1.1rem;
    width: 300px;
    min-height: 150px;
    position: relative;
  }
  .address-card p,
  .address p { margin: 0; line-height: 1.4; color: #566a7f; font-size: .85rem; }
  .address-card:hover,
  .address:hover { border-color: #d9dee3; }
  .address-card.is-clickable,
  .address.is-clickable { cursor: pointer; }
  .address-edit-icon {
    position: absolute;
    top: .6rem;
    right: .6rem;
    width: 28px;
    height: 28px;
    border-radius: 9999px;
    background: #f5f5f9;
    border: 1px solid #eceef1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transform: translateY(-2px);
    transition: opacity .12s ease, transform .12s ease;
  }
  .address:hover .address-edit-icon,
  .address-card:hover .address-edit-icon {
    opacity: 1;
    transform: translateY(0);
  }
</style>

<div class="customer-topbar">
  <div class="customer-breadcrumb">
    <a href="{{ route('customer.list') }}">Customers</a>
    <span class="muted">/</span>
    <a href="{{ route('customer.overview', $customer->id) }}">{{ $customerName }}</a>
    <span class="muted">/</span>
    <span>Addresses</span>
  </div>
  <div class="customer-actions">
    <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#addCustomerBranch">New Address</a>
  </div>
</div>

<div class="address-grid">
  @if(isset($branches) && $branches->count() > 0)
    <div class="d-flex flex-wrap gap-4">
      @foreach($branches as $branch)
        <div
          class="address address-card is-clickable"
          role="button"
          tabindex="0"
          data-bs-toggle="modal"
          data-bs-target="#editCustomerBranch"
          data-id="{{ $branch->id }}"
        >
          <span class="address-edit-icon" aria-hidden="true">
            <i class="icon-base ti tabler-pencil"></i>
          </span>
          <p>{{ $branch->name ?: ($branch->company_name ?? '') }}</p>
          @if(!empty($branch->contact_name)) <p class="text-muted small mb-0">Contact: {{ $branch->contact_name }}</p> @endif
          @if(!empty($customerName)) <p>{{ $customerName }}</p> @endif
          @if(!empty($branch->address_line1)) <p>{{ $branch->address_line1 }}</p> @endif
          @if(!empty($branch->address_line2)) <p>{{ $branch->address_line2 }}</p> @endif
          @if(!empty($branch->city)) <p>{{ $branch->city }}</p> @endif
          @if(!empty($branch->state)) <p>{{ $branch->state }}</p> @endif
          @if(!empty($branch->zip_code)) <p>{{ $branch->zip_code }}</p> @endif
          @if(!empty($branch->country)) <p>{{ $branch->country }}</p> @endif
        </div>
      @endforeach
    </div>
  @else
    <div class="text-muted">No addresses found.</div>
  @endif
</div>

<!-- Modal -->
@include('_partials/_modals/modal-customer-add-branch')
@include('_partials/_modals/modal-customer-edit-branch')
<!-- /Modal -->
@endsection