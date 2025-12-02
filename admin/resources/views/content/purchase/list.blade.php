@extends('layouts/layoutMaster')

@section('title', 'Purchases List')

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
@vite(['resources/assets/js/purchase-list.js'])
<script>
  function deletePurchase(id) {
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
            window.location.href = baseUrl + 'purchase/delete/' + id;
          }
        });
      }
    }
</script>
<style>
  /* Pointer cursor on row hover for click-to-view UX */
  .datatables-purchases tbody tr { cursor: pointer; }
  /* Keep non-clickable cells default cursor (control, checkbox, actions) */
  .datatables-purchases tbody tr td:nth-child(1),
  .datatables-purchases tbody tr td:nth-child(2),
  .datatables-purchases tbody tr td:last-child { cursor: default; }
</style>
@endsection

@section('content')
<!-- Purchases List Widget -->
<div class="card mb-6">
  <div class="card-widget-separator-wrapper">
    <div class="card-body card-widget-separator">
      <div class="row gy-4 gy-sm-1">
        <div class="col-sm-6 col-lg-4">
          <div class="d-flex justify-content-between align-items-start card-widget-1 border-end pb-4 pb-sm-0">
            <div>
              <p class="mb-1">Total Purchases</p>
              <h4 class="mb-1">{{ $total_purchases_count }}</h4>
            </div>
            <span class="avatar me-sm-6">
              <span class="avatar-initial rounded"><i
                  class="icon-base ti tabler-shopping-cart icon-28px text-heading"></i></span>
            </span>
          </div>
          <hr class="d-none d-sm-block d-lg-none me-6" />
        </div>
        <div class="col-sm-6 col-lg-4">
          <div class="d-flex justify-content-between align-items-start card-widget-2 border-end pb-4 pb-sm-0">
            <div>
              <p class="mb-1">Today's Purchases</p>
              <h4 class="mb-1">{{ $today_purchases_count }}</h4>
            </div>
            <span class="avatar p-2 me-lg-6">
              <span class="avatar-initial rounded"><i
                  class="icon-base ti tabler-calendar icon-28px text-heading"></i></span>
            </span>
          </div>
          <hr class="d-none d-sm-block d-lg-none" />
        </div>
        <div class="col-sm-6 col-lg-4">
          <div class="d-flex justify-content-between align-items-start border-end pb-4 pb-sm-0 card-widget-3">
            <div>
              <p class="mb-1">This Month</p>
              <h4 class="mb-1">{{ $this_month_purchases_count }}</h4>
            </div>
            <span class="avatar p-2 me-sm-6">
              <span class="avatar-initial rounded"><i
                  class="icon-base ti tabler-chart-bar icon-28px text-heading"></i></span>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Purchases List Table -->
<div class="card">
  <div class="card-datatable">
    <table class="datatables-purchases table">
      <thead class="border-top">
        <tr>
          <th></th>
          <th></th>
          <th>Date</th>
          <th>Reference No</th>
          <th>Supplier</th>
          <th>Total Amount</th>
          <th>Created By</th>
          <th>Note</th>
          <th>Actions</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="purchase-view-modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-simple">
    <div class="modal-content" id="purchase-view-modal-content">
      <!-- AJAX content will be injected here -->
    </div>
  </div>
  </div>

@endsection

