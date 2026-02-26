@extends('layouts/layoutMaster')

@section('title', 'Edit Product - Pricing')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/product-edit-pricing.js'])
@endsection

@section('content')
    <div class="app-ecommerce">
        <form id="editProductPricingForm" method="POST" action="{{ route('product.update.pricing') }}">
            @csrf
            <input type="hidden" name="id" value="{{ $product->id }}">
            <div style="background: var(--bs-body-bg);"
                class="py-5 px-2 card-header sticky-element d-flex justify-content-sm-between align-items-sm-center flex-column flex-sm-row">
                <div class="d-flex flex-column justify-content-center">
                    <h4 class="mb-1">Edit Product - Pricing</h4>
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
                    <span class="nav-link active">Pricing</span>
                </li>
            </ul>

            <div class="row">
                <div class="col-12">
                    <!-- Default Prices -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-0">
                                <h5 class="card-title mb-0 text-body fw-bold" style="font-size: 1rem;">Default Prices</h5>
                                <a href="#" class="text-primary text-decoration-underline">Add Volume Discount</a>
                            </div>
                            <div class="border-top border-secondary mt-3 pt-3" style="border-color: #d9dee3 !important;">
                                <table class="table table-borderless border mb-0 align-middle">
                                    <thead style="background-color: var(--bs-body-bg);">
                                        <tr>
                                            <th class="border-0 border-bottom p-3 text-body fw-normal" style="width: auto;"></th>
                                            <th class="border-0 border-bottom p-3 text-secondary fw-normal text-end small" style="width: 200px;">Unit Price {{ $currencySymbol }}</th>
                                            <th class="border-0 border-bottom p-3 text-secondary fw-normal text-end small" style="width: 200px;">RRP {{ $currencySymbol }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="border-0 p-2 text-body align-middle">{{ $product->sku }}</td>
                                            <td class="border-0 p-2 text-end" style="width: 200px;">
                                                <div class="form-control-validation mb-0">
                                                    <input type="text" onkeypress="return /^[0-9.]+$/.test(event.key)"
                                                        class="form-control form-control-sm text-end" id="productPrice" placeholder="0"
                                                        name="productPrice" value="{{ old('productPrice', $product->price) }}" autocomplete="off" />
                                                    @error('productPrice')
                                                        <span class="text-danger small d-block mt-1" role="alert"><strong>{{ $message }}</strong></span>
                                                    @enderror
                                                </div>
                                            </td>
                                            <td class="border-0 p-2 text-end" style="width: 200px;">
                                                <div class="form-control-validation mb-0">
                                                    <input type="text" onkeypress="return /^[0-9.]+$/.test(event.key)"
                                                        class="form-control form-control-sm text-end" id="rrp" placeholder="0" name="rrp"
                                                        value="{{ old('rrp', $product->rrp) }}" autocomplete="off" />
                                                    @error('rrp')
                                                        <span class="text-danger small d-block mt-1" role="alert"><strong>{{ $message }}</strong></span>
                                                    @enderror
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    @forelse ($priceLists ?? [] as $pl)
                    @php
                        $pivot = $productPriceByList[$pl->id] ?? null;
                        $defaultUnit = $pivot ? ($pivot->unit_price ?? '') : '';
                        $defaultRrp = $pivot ? ($pivot->rrp ?? '') : '';
                        $pct = isset($pl->conversion_rate) ? (float) $pl->conversion_rate : 100;
                    @endphp
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-0">
                                <h5 class="card-title mb-0 text-body fw-bold" style="font-size: 1rem;">{{ $pl->name }}</h5>
                                <a href="#" class="text-primary text-decoration-underline">Add Volume Discount</a>
                            </div>
                            <p class="text-body-secondary small mb-0 mt-1">Prices will be set to <span class="fw-semibold text-primary">{{ $pct }}% </span> of the default price unless specified below.</p>
                            <div class="border-top border-secondary mt-3 pt-3" style="border-color: #d9dee3 !important;">
                                @if($pl->price_list_type == 0)
                                    {{-- Type 0: Unit Price only --}}
                                    <table class="table table-borderless border mb-0 align-middle">
                                        <thead style="background-color: var(--bs-body-bg);">
                                            <tr>
                                                <th class="border-0 border-bottom p-3 text-body fw-normal" style="width: auto;"></th>
                                                <th class="border-0 border-bottom p-3 text-secondary fw-normal text-end small" style="width: 200px;">Unit Price {{ $currencySymbol }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="border-0 p-2 text-body align-middle">{{ $product->sku }}</td>
                                                <td class="border-0 p-2 text-end" style="width: 200px;">
                                                    <div class="form-control-validation mb-0">
                                                        <input type="text" onkeypress="return /^[0-9.]+$/.test(event.key)"
                                                            class="form-control form-control-sm text-end" name="price_list[{{ $pl->id }}][unit_price]"
                                                            placeholder=""
                                                            value="{{ old('price_list.'.$pl->id.'.unit_price', $defaultUnit) }}" autocomplete="off" />
                                                        @error('price_list.'.$pl->id.'.unit_price')
                                                            <span class="text-danger small d-block mt-1" role="alert"><strong>{{ $message }}</strong></span>
                                                        @enderror
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                @else
                                    {{-- Type 1: Unit Price + RRP --}}
                                    <table class="table table-borderless border mb-0 align-middle">
                                        <thead style="background-color: var(--bs-body-bg);">
                                            <tr>
                                                <th class="border-0 border-bottom p-3 text-body fw-normal" style="width: auto;"></th>
                                                <th class="border-0 border-bottom p-3 text-secondary fw-normal text-end small" style="width: 200px;">Unit Price {{ $currencySymbol }}</th>
                                                <th class="border-0 border-bottom p-3 text-secondary fw-normal text-end small" style="width: 200px;">RRP {{ $currencySymbol }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="border-0 p-2 text-body align-middle">{{ $product->sku }}</td>
                                                <td class="border-0 p-2 text-end" style="width: 200px;">
                                                    <div class="form-control-validation mb-0">
                                                        <input type="text" onkeypress="return /^[0-9.]+$/.test(event.key)"
                                                            class="form-control form-control-sm text-end" name="price_list[{{ $pl->id }}][unit_price]"
                                                            placeholder=""
                                                            value="{{ old('price_list.'.$pl->id.'.unit_price', $defaultUnit) }}" autocomplete="off" />
                                                        @error('price_list.'.$pl->id.'.unit_price')
                                                            <span class="text-danger small d-block mt-1" role="alert"><strong>{{ $message }}</strong></span>
                                                        @enderror
                                                    </div>
                                                </td>
                                                <td class="border-0 p-2 text-end" style="width: 200px;">
                                                    <div class="form-control-validation mb-0">
                                                        <input type="text" onkeypress="return /^[0-9.]+$/.test(event.key)"
                                                            class="form-control form-control-sm text-end" name="price_list[{{ $pl->id }}][rrp]"
                                                            placeholder=""
                                                            value="{{ old('price_list.'.$pl->id.'.rrp', $defaultRrp) }}" autocomplete="off" />
                                                        @error('price_list.'.$pl->id.'.rrp')
                                                            <span class="text-danger small d-block mt-1" role="alert"><strong>{{ $message }}</strong></span>
                                                        @enderror
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-body-secondary small">No price lists configured. Add price lists in Settings to set list-specific prices.</p>
                    @endforelse
                </div>
            </div>
        </form>
    </div>
@endsection
