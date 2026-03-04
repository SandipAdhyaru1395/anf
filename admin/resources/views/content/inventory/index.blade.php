@extends('layouts/layoutMaster')

@section('title', 'Inventory')

@section('vendor-style')
  @vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
    'resources/assets/vendor/libs/select2/select2.scss',
  ])
@endsection

@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
    'resources/assets/vendor/libs/select2/select2.js',
  ])
@endsection

@section('page-script')
  @vite(['resources/assets/js/inventory-list.js'])
@endsection

@section('content')
<style>
  /* Slightly increase inventory row height for readability */
  .datatables-inventory tbody tr td {
    padding-top: 0.9rem;
    padding-bottom: 0.9rem;
  }
</style>

<div class="card">
  <div class="card-header">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
      <h5 class="mb-0">Inventory</h5>
      <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#inventoryImportModal">
          Import
        </button>
      </div>
    </div>

    @isset($categories)
      <div class="d-flex align-items-center">
        <label class="form-label mb-0 me-3" for="inventory_category">Show</label>
        <select class="select2" id="inventory_category" name="inventoryCategory" style="width: 50%; min-width: 260px;">
          <option value="">All categories</option>
          @forelse($categories as $category)
            @include('_partials.parent_category_option', [
                'category' => $category,
                'prefix' => '',
            ])
          @empty
          @endforelse
        </select>
      </div>
    @endisset
  </div>

  <div class="card-datatable">
    <table class="datatables-inventory table table-borderless">
      <thead class="border-top">
        <tr>
          <th></th>
          <th>Product</th>
          <th>SKU</th>
          <th>On hand</th>
          <th>Ordered</th>
          <th>Available</th>
          <th></th>
        </tr>
      </thead>
    </table>
  </div>
</div>

<!-- Inventory Import Modal -->
<div class="modal fade" id="inventoryImportModal" tabindex="-1" aria-labelledby="inventoryImportModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="{{ route('inventory.import') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="inventoryImportModalLabel">Inventory Import</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="mb-4">
            Bulk update your stock levels by uploading a CSV file. The file should contain two columns:
            <strong>SKU</strong> and <strong>On Hand</strong>.
          </p>
          <p class="mb-3">
            You can download a ready-made template here:
            <a href="{{ route('inventory.import.sample') }}" class="fw-semibold">Download sample file</a>
          </p>
          <div class="mb-3">
            <label for="inventory_file" class="form-label">CSV file</label>
            <input type="file" class="form-control" id="inventory_file" name="inventory_file" accept=".csv,text/csv">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Upload</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

