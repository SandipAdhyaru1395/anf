@extends('layouts/layoutMaster')

@section('title', 'Supplier List')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss','resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
'resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',])
@endsection

@section('page-script')
@vite(['resources/assets/js/supplier-list.js'])
<script>
  function deleteSupplier(id) {
      if (id) {
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
            window.location.href = baseUrl + 'supplier/delete/' + id;
          }
        });
      }
    }
</script>
@endsection

@section('content')
<!-- Supplier List Widget -->
<div class="card mb-6">
  <div class="card-widget-separator-wrapper">
    <div class="card-body card-widget-separator">
      <div class="row gy-4 gy-sm-1">
        <div class="col-sm-6 col-lg-4">
          <div class="d-flex justify-content-between align-items-start card-widget-1 border-end pb-4 pb-sm-0">
            <div>
              <p class="mb-1">Total Suppliers</p>
              <h4 class="mb-1">{{ $total_suppliers_count }}</h4>
            </div>
            <span class="avatar me-sm-6">
              <span class="avatar-initial rounded"><i
                  class="icon-base ti tabler-building-factory-2 icon-28px text-heading"></i></span>
            </span>
          </div>
          <hr class="d-none d-sm-block d-lg-none me-6" />
        </div>
        <div class="col-sm-6 col-lg-4">
          <div class="d-flex justify-content-between align-items-start card-widget-2 border-end pb-4 pb-sm-0">
            <div>
              <p class="mb-1">Active Suppliers</p>
              <h4 class="mb-1">{{ $active_suppliers_count }}</h4>
            </div>
            <span class="avatar p-2 me-lg-6">
              <span class="avatar-initial rounded"><i
                  class="icon-base ti tabler-checks icon-28px text-heading"></i></span>
            </span>
          </div>
          <hr class="d-none d-sm-block d-lg-none" />
        </div>
        <div class="col-sm-6 col-lg-4">
          <div class="d-flex justify-content-between align-items-start border-end pb-4 pb-sm-0 card-widget-3">
            <div>
              <p class="mb-1">Inactive Suppliers</p>
              <h4 class="mb-1">{{ $inactive_suppliers_count }}</h4>
            </div>
            <span class="avatar p-2 me-sm-6">
              <span class="avatar-initial rounded"><i
                  class="icon-base ti tabler-ban icon-28px text-heading"></i></span>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Supplier List Table -->
<div class="card">
  <div class="card-datatable">
    <table class="datatables-suppliers table">
      <thead class="border-top">
        <tr>
          <th></th>
          <th></th>
          <th>Company</th>
          <th>Full Name</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Status</th>
          <th>actions</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

@endsection

