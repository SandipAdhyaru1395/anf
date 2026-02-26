@extends('layouts/layoutMaster')

@section('title', 'VAT Settings')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss','resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss','resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss','resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss','resources/assets/vendor/libs/select2/select2.scss','resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js','resources/assets/vendor/libs/select2/select2.js','resources/assets/vendor/libs/@form-validation/popular.js','resources/assets/vendor/libs/@form-validation/bootstrap5.js','resources/assets/vendor/libs/@form-validation/auto-focus.js','resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')
@vite('resources/assets/js/settings-vatMethod.js')
<script>
  document.addEventListener('DOMContentLoaded', function(){
    @if ($errors->addVatModal->any())
      let addVatModal = new bootstrap.Offcanvas(document.getElementById('offcanvasAddVATMethod'));
      addVatModal.show();
    @endif
    @if ($errors->editVatModal->any())
      $('#ajaxEditVATMethodModal').modal('show');
    @endif
  });
</script>
@endsection

@section('content')
<div class="row g-6">
  @include('content/settings/sidebar')
  <!-- Options -->
  <div class="col-12 col-lg-12 pt-6 pt-lg-0">
    <div class="tab-content p-0">
      <!-- VAT Methods Tab -->
      <div class="tab-pane fade show active" id="vat" role="tabpanel">
        <div id="vatMethodSettingsForm">
          <div class="card mb-6">
            <div class="card-body">
              <div class="row text-end mb-2">
                <div class="col">
                    <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddVATMethod">Add VAT</button>
                </div>
              </div>
              <div class="card-datatable">
                <table class="datatables-vatMethods table">
                  <thead class="border-top">
                    <tr>
                      <th>Name</th>
                      <th>VAT</th>
                      <th>Status</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- /Options-->
</div>
<!-- Placeholders for partials: implement these next step -->
@include('_partials._offcanvas.offcanvas-add-vat-method')
@include('_partials._modals.modal-ajax-edit-vat-method')
@endsection
