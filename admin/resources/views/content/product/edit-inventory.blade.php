@extends('layouts/layoutMaster')

@section('title', 'Edit Product - Inventory')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/product-edit-inventory.js'])
@endsection

@section('content')
    <div class="app-ecommerce">
        <form id="editProductInventoryForm" method="POST" action="{{ route('product.update.inventory') }}">
            @csrf
            <input type="hidden" name="id" value="{{ $product->id }}">
            <div style="background: var(--bs-body-bg);"
                class="py-5 px-2 card-header sticky-element d-flex justify-content-sm-between align-items-sm-center flex-column flex-sm-row">
                <div class="d-flex flex-column justify-content-center">
                    <h4 class="mb-1">Edit Product - Inventory</h4>
                </div>
                <div class="d-flex align-content-center flex-wrap gap-4">
                    <div class="d-flex gap-4">
                        <a href="{{ route('product.list') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </div>
            </div>

            <ul class="nav nav-tabs mb-4 px-2" role="tablist">
                <li class="nav-item">
                    <a href="{{ route('product.edit', $product->id) }}" class="nav-link">Details</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('product.edit.pricing', $product->id) }}" class="nav-link">Pricing</a>
                </li>
                <li class="nav-item">
                    <span class="nav-link active">Inventory</span>
                </li>
            </ul>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    @php
                        $onHand = (int) ($product->onhand_qty ?? $product->available_qty ?? 0);
                        $ordered = (int) ($product->ordered_qty ?? 0);
                        $available = max(0, $onHand - $ordered);
                    @endphp
                    <input type="hidden" id="inventory-ordered-hidden" value="{{ $ordered }}">

                    <div class="row g-4">
                        <div class="col-12">
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 40%;">Product</th>
                                            <th style="width: 20%;" class="text-center">
                                                On Hand
                                                <span
                                                    class="ms-1 d-inline-flex align-items-center justify-content-center fw-semibold"
                                                    style="cursor: help; font-size: 0.7rem; width: 16px; height: 16px; border-radius: 50%; background-color: #4b6edb; color: #fff;"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-placement="top"
                                                    title="The amount physically in stock. Available is the amount in stock not allocated to orders."
                                                >
                                                    ?
                                                </span>
                                            </th>
                                            <th style="width: 20%;" class="text-center">Available</th>
                                            <th style="width: 20%;" class="text-center">
                                                Backorder?
                                                <span
                                                    class="ms-1 d-inline-flex align-items-center justify-content-center fw-semibold"
                                                    style="cursor: help; font-size: 0.7rem; width: 16px; height: 16px; border-radius: 50%; background-color: #4b6edb; color: #fff;"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-placement="top"
                                                    title="Tick this box to allow orders when the item is out of stock."
                                                >
                                                    ?
                                                </span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $product->name }}</div>
                                                <div class="text-muted small">SKU: {{ $product->sku }}</div>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-inline-flex align-items-center gap-2">
                                                    <input
                                                        type="text"
                                                        class="form-control form-control-sm text-center"
                                                        id="inventory-onhand"
                                                        name="quantity"
                                                        onkeypress="return /^[0-9]+$/.test(event.key)"
                                                        value="{{ old('quantity', $onHand) }}"
                                                        autocomplete="off"
                                                        style="max-width: 90px;"
                                                    />
                                                </div>
                                                @error('quantity')
                                                    <span class="text-danger small d-block mt-1" role="alert"><strong>{{ $message }}</strong></span>
                                                @enderror
                                            </td>
                                            <td class="text-center">
                                                <span id="inventory-available"
                                                      class="badge bg-label-success px-3 py-2">
                                                    {{ $available }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check d-inline-flex align-items-center justify-content-center">
                                                    <input class="form-check-input me-2" type="checkbox"
                                                           id="inventory-backorder"
                                                           name="allow_out_of_stock"
                                                           value="1"
                                                           @checked(old('allow_out_of_stock', $product->allow_out_of_stock ?? false))>
                                                    <label class="form-check-label small text-muted"
                                                           for="inventory-backorder">
                                                        Allow when out of stock
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

