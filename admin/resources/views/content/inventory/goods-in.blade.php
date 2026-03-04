@extends('layouts/layoutMaster')

@section('title', 'Goods In')

@section('vendor-style')
  @vite([
    'resources/assets/vendor/libs/select2/select2.scss',
  ])
@endsection

@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/select2/select2.js',
  ])
@endsection

@section('page-script')
  @vite(['resources/assets/js/inventory-goods-in.js'])
@endsection

@section('content')
  <style>
    /* Prevent horizontal scroll on Goods In page when Select2 opens */
    body {
      overflow-x: hidden;
    }
    .goods-in-page .select2-container {
      max-width: 100% !important;
    }
    .goods-in-page .select2-dropdown {
      max-width: 100% !important;
    }
  </style>
  <div class="app-ecommerce goods-in-page">
    <form method="POST" action="{{ route('inventory.goods_in.update') }}">
      @csrf
      <div class="card">
        <div
          class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
          <div>
            <h5 class="mb-1">Goods In</h5>
          </div>
          <button type="submit" class="btn btn-primary">
            Submit
          </button>
        </div>

        <div class="card-body">
          <div class="row mb-4">
            <div class="col-md-8">
              <label for="goods_in_product" class="form-label">Product</label>
              <select id="goods_in_product" class="form-select select2" style="width: 100%;">
                <option value="">Search by name or SKU</option>
              </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
              <button type="button" id="goods_in_add" class="btn btn-outline-secondary w-100">
                Add
              </button>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-sm align-middle" id="goods_in_table">
              <thead class="table-light">
                <tr>
                  <th style="width: 40%;">Product</th>
                  <th style="width: 20%;">SKU</th>
                  <th style="width: 20%;" class="text-end">On Hand</th>
                  <th style="width: 20%;" class="text-end">Available</th>
                </tr>
              </thead>
              <tbody>
                {{-- Rows will be added dynamically via JS --}}
              </tbody>
            </table>
          </div>
          <div class="mt-4 mb-0 text-center">
            <p id="goods_in_empty" class="text-muted small mb-0">
              No products added yet.
            </p>
          </div>
        </div>
      </div>
    </form>
  </div>
@endsection

