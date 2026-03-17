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
        <style>
            .js-add-volume-discount .vd-group-name-hover:hover {
                text-decoration: underline;
            }
            .js-add-volume-discount[data-vd-group-name=""]:hover {
                text-decoration: underline;
            }
            .js-add-volume-discount .vd-prefix {
                color: #607565;
            }
            .vd-break-qty {
                color: #000 !important;
            }
            .vd-break-disc {
                color: #6c757d !important;
            }
            .vd-header-main {
                color: #000 !important;
            }
        </style>
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
                <li class="nav-item">
                    <a href="{{ route('product.edit.inventory', $product->id) }}" class="nav-link">Inventory</a>
                </li>
            </ul>

            <div class="row">
                <div class="col-12">
                    <!-- Default Prices -->
                    @php
                        $defaultVolume = $volumeDiscountsByList['default'] ?? null;
                        $defaultBreaks = $defaultVolume && $defaultVolume->group ? $defaultVolume->group->breaks->sortBy('from_quantity') : collect();
                        $defaultOverride = $volumeDiscountOverridePricesByList['default'] ?? [];
                    @endphp
                    <div class="card mb-4 js-price-list-card" data-pricelist-id="default">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-0">
                                <h5 class="card-title mb-0 text-body fw-bold" style="font-size: 1rem;">Default Prices</h5>
                                <a href="#"
                                   class="text-primary js-add-volume-discount"
                                   data-pricelist-id="default"
                                   data-pricelist-label="Default Prices"
                                   data-vd-group-name="{{ $defaultVolume && $defaultVolume->group ? $defaultVolume->group->name : '' }}"
                                   data-vd-breaks='@json($defaultBreaks->map(fn($b) => ["id" => $b->id, "from_quantity" => $b->from_quantity, "discount_percentage" => $b->discount_percentage])->values())'>
                                    @if($defaultVolume && $defaultVolume->group)
                                        <small class="vd-prefix">Volume Discount:</small>
                                        <span class="vd-group-name-hover">{{ $defaultVolume->group->name }}</span>
                                    @else
                                        Add Volume Discount
                                    @endif
                                </a>
                            </div>
                            <div class="border-top border-secondary mt-3 pt-3" style="border-color: #d9dee3 !important;">
                                <table class="table table-borderless border mb-0 align-middle">
                                    <thead style="background-color: var(--bs-body-bg);">
                                        <tr>
                                            <th class="border-0 border-bottom p-2 text-body fw-normal" style="width: auto;"></th>
                                            <th class="border-0 border-bottom p-2 fw-normal text-center small vd-header-main" style="width: 150px; font-size: 0.8rem;">Unit Price {{ $currencySymbol }}</th>
                                            <th class="border-0 border-bottom p-2 fw-normal text-center small vd-header-main" style="width: 150px; font-size: 0.8rem;">RRP {{ $currencySymbol }}</th>
                                            @foreach ($defaultBreaks as $break)
                                                <th class="border-0 border-bottom p-2 fw-normal text-center small vd-col-header" style="width: 150px; font-size: 0.8rem;">
                                                    <span class="vd-break-qty">{{ $break->from_quantity }}+</span>
                                                    <span class="vd-break-disc">(-{{ rtrim(rtrim(number_format($break->discount_percentage, 2, '.', ''), '0'), '.') }}%)</span>
                                                </th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="border-0 p-2 text-body align-middle">{{ $product->sku }}</td>
                                            <td class="border-0 p-2 text-end" style="width: 150px;">
                                                <div class="form-control-validation mb-0">
                                                    <input type="text" onkeypress="return /^[0-9.]+$/.test(event.key)"
                                                        class="form-control form-control-sm text-end" id="productPrice" placeholder="0"
                                                        name="productPrice" value="{{ old('productPrice', $product->price) }}" autocomplete="off" />
                                                    @error('productPrice')
                                                        <span class="text-danger small d-block mt-1" role="alert"><strong>{{ $message }}</strong></span>
                                                    @enderror
                                                </div>
                                            </td>
                                            <td class="border-0 p-2 text-end" style="width: 150px;">
                                                <div class="form-control-validation mb-0">
                                                    <input type="text" onkeypress="return /^[0-9.]+$/.test(event.key)"
                                                        class="form-control form-control-sm text-end" id="rrp" placeholder="0" name="rrp"
                                                        value="{{ old('rrp', $product->rrp) }}" autocomplete="off" />
                                                    @error('rrp')
                                                        <span class="text-danger small d-block mt-1" role="alert"><strong>{{ $message }}</strong></span>
                                                    @enderror
                                                </div>
                                            </td>
                                            @foreach ($defaultBreaks as $break)
                                                <td class="border-0 p-2 text-end align-middle vd-col-cell" style="width: 150px;">
                                                    <input
                                                        type="text"
                                                        class="form-control form-control-sm text-end"
                                                        onkeypress="return /^[0-9.]+$/.test(event.key)"
                                                        name="volume_discount_price[default][{{ $break->id }}]"
                                                        value="{{ old('volume_discount_price.default.'.$break->id, $defaultOverride[$break->id] ?? '') }}"
                                                        placeholder=""
                                                        autocomplete="off"
                                                    />
                                                </td>
                                            @endforeach
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
                        $plVolume = $volumeDiscountsByList[$pl->id] ?? null;
                        $plBreaks = $plVolume && $plVolume->group ? $plVolume->group->breaks->sortBy('from_quantity') : collect();
                        $plOverride = $volumeDiscountOverridePricesByList[$pl->id] ?? [];
                    @endphp
                    <div class="card mb-4 js-price-list-card" data-pricelist-id="{{ $pl->id }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-0">
                                <h5 class="card-title mb-0 text-body fw-bold" style="font-size: 1rem;">{{ $pl->name }}</h5>
                                <a href="#"
                                   class="text-primary js-add-volume-discount"
                                   data-pricelist-id="{{ $pl->id }}"
                                   data-pricelist-label="{{ $pl->name }}"
                                   data-vd-group-name="{{ $plVolume && $plVolume->group ? $plVolume->group->name : '' }}"
                                   data-vd-breaks='@json($plBreaks->map(fn($b) => ["id" => $b->id, "from_quantity" => $b->from_quantity, "discount_percentage" => $b->discount_percentage])->values())'>
                                    @if($plVolume && $plVolume->group)
                                        <small class="vd-prefix">Volume Discount:</small>
                                        <span class="vd-group-name-hover">{{ $plVolume->group->name }}</span>
                                    @else
                                        Add Volume Discount
                                    @endif
                                </a>
                            </div>
                            <p class="text-body-secondary small mb-0 mt-1">Prices will be set to <span class="fw-semibold text-primary">{{ $pct }}% </span> of the default price unless specified below.</p>
                            <div class="border-top border-secondary mt-3 pt-3" style="border-color: #d9dee3 !important;">
                                @if($pl->price_list_type == 0)
                                    {{-- Type 0: Unit Price only --}}
                                    <table class="table table-borderless border mb-0 align-middle">
                                        <thead style="background-color: var(--bs-body-bg);">
                                            <tr>
                                                <th class="border-0 border-bottom p-2 text-body fw-normal" style="width: auto;"></th>
                                                <th class="border-0 border-bottom p-2 fw-normal text-center small vd-header-main" style="width: 150px; font-size: 0.8rem;">Unit Price {{ $currencySymbol }}</th>
                                                @foreach ($plBreaks as $break)
                                                    <th class="border-0 border-bottom p-2 fw-normal text-center small vd-col-header" style="width: 150px; font-size: 0.8rem;">
                                                        <span class="vd-break-qty">{{ $break->from_quantity }}+</span>
                                                        <span class="vd-break-disc">(-{{ rtrim(rtrim(number_format($break->discount_percentage, 2, '.', ''), '0'), '.') }}%)</span>
                                                    </th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="border-0 p-2 text-body align-middle">{{ $product->sku }}</td>
                                                <td class="border-0 p-2 text-end" style="width: 150px;">
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
                                                @foreach ($plBreaks as $break)
                                                    <td class="border-0 p-2 text-end align-middle vd-col-cell" style="width: 150px;">
                                                        <input
                                                            type="text"
                                                            class="form-control form-control-sm text-end"
                                                            onkeypress="return /^[0-9.]+$/.test(event.key)"
                                                            name="volume_discount_price[{{ $pl->id }}][{{ $break->id }}]"
                                                            value="{{ old('volume_discount_price.'.$pl->id.'.'.$break->id, $plOverride[$break->id] ?? '') }}"
                                                            placeholder=""
                                                            autocomplete="off"
                                                        />
                                                    </td>
                                                @endforeach
                                            </tr>
                                        </tbody>
                                    </table>
                                @else
                                    {{-- Type 1: Unit Price + RRP --}}
                                    <table class="table table-borderless border mb-0 align-middle">
                                        <thead style="background-color: var(--bs-body-bg);">
                                            <tr>
                                                <th class="border-0 border-bottom p-2 text-body fw-normal" style="width: auto;"></th>
                                                <th class="border-0 border-bottom p-2 fw-normal text-center small vd-header-main" style="width: 150px; font-size: 0.8rem;">Unit Price {{ $currencySymbol }}</th>
                                                <th class="border-0 border-bottom p-2 fw-normal text-center small vd-header-main" style="width: 150px; font-size: 0.8rem;">RRP {{ $currencySymbol }}</th>
                                                @foreach ($plBreaks as $break)
                                                    <th class="border-0 border-bottom p-2 fw-normal text-center small vd-col-header" style="width: 150px; font-size: 0.8rem;">
                                                        <span class="vd-break-qty">{{ $break->from_quantity }}+</span>
                                                        <span class="vd-break-disc">(-{{ rtrim(rtrim(number_format($break->discount_percentage, 2, '.', ''), '0'), '.') }}%)</span>
                                                    </th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="border-0 p-2 text-body align-middle">{{ $product->sku }}</td>
                                                <td class="border-0 p-2 text-end" style="width: 150px;">
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
                                                <td class="border-0 p-2 text-end" style="width: 150px;">
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
                                                @foreach ($plBreaks as $break)
                                                    <td class="border-0 p-2 text-end align-middle vd-col-cell" style="width: 150px;">
                                                        <input
                                                            type="text"
                                                            class="form-control form-control-sm text-end"
                                                            onkeypress="return /^[0-9.]+$/.test(event.key)"
                                                            name="volume_discount_price[{{ $pl->id }}][{{ $break->id }}]"
                                                            value="{{ old('volume_discount_price.'.$pl->id.'.'.$break->id, $plOverride[$break->id] ?? '') }}"
                                                            placeholder=""
                                                            autocomplete="off"
                                                        />
                                                    </td>
                                                @endforeach
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

            <div class="modal fade" id="volumeDiscountModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Select a volume discount group</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="volumeDiscountPriceListId" value="">

                            <div id="volumeDiscountSelectView">
                                <div id="vdGroupsList"></div>
                            </div>

                            <div id="volumeDiscountCreateView" class="d-none">
                                <div class="alert alert-info d-flex align-items-center py-2 px-3 mb-3" id="vdUsageInfo" style="font-size: 0.8rem;">
                                    <i class="menu-icon ti tabler-info-circle me-2"></i>
                                    <span id="vdUsageInfoText" class="text-dark">
                                        This discount group usage will be shown here. Any changes made will affect all products using it.
                                    </span>
                                </div>

                                <div class="mb-7">
                                    <label for="vdGroupName" class="form-label mb-1">Name</label>
                                    <input type="text" class="form-control" id="vdGroupName" placeholder="Discount group name" autocomplete="off">
                                    <small class="text-body-secondary d-inline-block mt-1">
                                        Enter a name for this discount group so it can be re-used by other products.
                                    </small>
                                </div>

                                <div class="mb-3">
                                    <h6 class="mb-2">Quantity Price Breaks</h6>
                                    <div class="table-responsive">
                                        <table class="table align-middle table-bordered" id="vdQuantityTable">
                                            <thead>
                                                <tr>
                                                    <th style="width: 40%;">From Quantity</th>
                                                    <th style="width: 60%;">Discount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <input type="text" class="form-control form-control-sm vd-from-qty" placeholder="0">
                                                    </td>
                                                    <td class="pe-0">
                                                        <div class="d-flex align-items-center">
                                                            <input type="text" class="form-control form-control-sm me-2 vd-discount" placeholder="0">
                                                            <span class="me-2">%</span>
                                                            <button type="button" class="btn btn-sm vd-remove-row"><i class="menu-icon ti tabler-trash"></i></button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="vdAddRowBtn">
                                        Add a quantity break
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-danger" id="volumeDiscountRemoveBtn">
                                Remove Discount
                            </button>
                            <button type="button" class="btn btn-primary" id="volumeDiscountNewGroupBtn">
                                New Discount Group
                            </button>

                            <button type="button" class="btn btn-outline-secondary d-none" id="volumeDiscountBackBtn">
                                Back
                            </button>
                            <button type="button" class="btn btn-primary d-none" id="volumeDiscountSaveSelectBtn">
                                Save and Select
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
