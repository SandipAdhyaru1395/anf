@extends('layouts/layoutMaster')

@section('title', 'Currencies Settings')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss','resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss','resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss','resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss','resources/assets/vendor/libs/select2/select2.scss','resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js','resources/assets/vendor/libs/select2/select2.js','resources/assets/vendor/libs/@form-validation/popular.js','resources/assets/vendor/libs/@form-validation/bootstrap5.js','resources/assets/vendor/libs/@form-validation/auto-focus.js','resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')
@vite('resources/assets/js/settings-currency.js')
<script>
  document.addEventListener('DOMContentLoaded', function(){
    @if ($errors->hasBag('addCurrencyModal') && $errors->getBag('addCurrencyModal')->any())
      $('#modalAddCurrency').modal('show');
    @endif
    @if ($errors->hasBag('editCurrencyModal') && $errors->getBag('editCurrencyModal')->any())
      $('#ajaxEditCurrencyModal').modal('show');
    @endif
  });
</script>
@endsection

@section('content')
<div class="row g-6">
  @include('content/settings/sidebar')
  <div class="col-12 col-lg-12 pt-6 pt-lg-0">
    <div class="tab-content p-0">
      <div class="tab-pane fade show active" id="currencies" role="tabpanel">
        <div class="card mb-6">
          <div class="card-body">
            <div class="row text-end mb-2">
              <div class="col">
                <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#modalAddCurrency">Add Currency</button>
              </div>
            </div>
            <div class="card-datatable">
              <table class="datatables-currencies table">
                <thead class="border-top">
                  <tr>
                    <th>Currency Code</th>
                    <th>Currency Name</th>
                    <th>Symbol</th>
                    <th>Exchange Rate</th>
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
@include('_partials._modals.modal-add-currency')
@include('_partials._modals.modal-edit-currency')
@endsection
