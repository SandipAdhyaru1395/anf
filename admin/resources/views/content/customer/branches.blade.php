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
          <a class="nav-link active" href="javascript:void(0);"><i
              class="icon-base ti tabler-map-pin icon-sm me-1_5"></i>Branches</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ url('customer/'.$customer->id .'/notifications') }}"><i
              class="icon-base ti tabler-bell icon-sm me-1_5"></i>Notifications</a>
        </li>
      </ul>
    </div>
    <!--/ Customer Pills -->

    <!-- Branch accordion -->

    <div class="card card-action mb-6">
      <div class="card-header align-items-center py-6">
        <h5 class="card-action-title mb-0">Branches</h5>
        <div class="card-action-element">
          <button class="btn btn-sm btn-label-primary" type="button" data-bs-toggle="modal"
            data-bs-target="#addCustomerBranch">Add new branch</button>
        </div>
      </div>
      <div class="card-body">
        @if(isset($branches) && $branches->count() > 0)
          <div class="accordion accordion-flush accordion-arrow-left" id="accordionBranch">
            @foreach($branches as $index => $branch)
              <div class="accordion-item {{ $index === 0 ? 'border-bottom' : ($index === $branches->count() - 1 ? 'border-top-0' : 'border-bottom border-top-0') }}">
                <div class="accordion-header d-flex justify-content-between align-items-center flex-wrap flex-sm-nowrap"
                  id="heading{{ $branch->id }}">
                  <a class="accordion-button collapsed" data-bs-toggle="collapse"
                    data-bs-target="#branch{{ $branch->id }}" 
                    aria-expanded="false" 
                    aria-controls="heading{{ $branch->id }}"
                    role="button">
                    <span>
                      
                      <span class="mb-0">{{ $branch->address_line1 }}</span>
                    </span>
                  </a>
                  <div class="d-flex gap-4 p-6 p-sm-0 pt-0 ms-1 ms-sm-0">
                    <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#editCustomerBranch" data-id="{{ $branch->id }}">
                      <i class="icon-base ti tabler-edit text-body icon-md"></i>
                    </a>
                    <a href="javascript:void(0);" onclick="deleteBranch({{ $branch->id }})">
                      <i class="icon-base ti tabler-trash text-body icon-md"></i>
                    </a>
                  </div>
                </div>
                <div id="branch{{ $branch->id }}" 
                     class="accordion-collapse collapse" 
                     aria-labelledby="heading{{ $branch->id }}"
                     data-bs-parent="#accordionBranch">
                  <div class="accordion-body ps-6 ms-1">
                    <h6 class="mb-1">{{ $branch->name ?: '' }}</h6>
                    <p class="mb-1">{{ $branch->address_line1 }},</p>
                    @if($branch->address_line2)
                      <p class="mb-1">{{ $branch->address_line2 }},</p>
                    @endif
                    <p class="mb-1">{{ $branch->city }}, {{ $branch->state }} {{ $branch->zip_code }},</p>
                    <p class="mb-1">{{ $branch->country }}</p>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        @else
          <div class="text-center py-4">
            <p class="text-muted">No branches found. Add first branch using the button above.</p>
          </div>
        @endif
      </div>
    </div>
    <!-- Branch accordion -->
    
  </div>
  <!--/ Customer Content -->
</div>

<!-- Modal -->
@include('_partials/_modals/modal-customer-add-branch')
@include('_partials/_modals/modal-customer-edit-branch')
<!-- /Modal -->
@endsection