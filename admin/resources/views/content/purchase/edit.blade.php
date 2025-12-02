@extends('layouts/layoutMaster')

@section('title', 'Edit Purchase')

@section('vendor-style')
    @vite([
        'resources/assets/vendor/libs/quill/typography.scss',
        'resources/assets/vendor/libs/quill/katex.scss',
        'resources/assets/vendor/libs/quill/editor.scss',
        'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
        'resources/assets/vendor/libs/select2/select2.scss',
        'resources/assets/vendor/libs/@form-validation/form-validation.scss'
    ])
@endsection

@section('vendor-script')
    @vite([
        'resources/assets/vendor/libs/flatpickr/flatpickr.js',
        'resources/assets/vendor/libs/quill/quill.js',
        'resources/assets/vendor/libs/select2/select2.js',
        'resources/assets/vendor/libs/@form-validation/popular.js',
        'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
        'resources/assets/vendor/libs/@form-validation/auto-focus.js'
    ])
@endsection

@section('page-script')
    <style>
        /* Product search dropdown styling */
        #product-search-results { border: 0 !important; }
        #product-search-results .list-group-item { color: #000 !important; border: 0 !important; }
        #product-search-results .list-group-item:hover,
        #product-search-results .list-group-item.active { color: #fff !important; background-color: var(--bs-primary) !important; }

         /* Products table column widths */
         #products-table th:nth-child(1),
        #products-table td:nth-child(1) {
            width: auto;
            min-width: 200px;
        }
        
        #products-table th:nth-child(2),
        #products-table td:nth-child(2) {
            width: 120px;
            min-width: 120px;
            
        }
        
        #products-table th:nth-child(3),
        #products-table td:nth-child(3) {
            width: 100px;
            min-width: 100px;
            text-align: right;
        }
        
        #products-table th:nth-child(4),
        #products-table td:nth-child(4) {
            width: 120px;
            min-width: 120px;
            text-align: right;
        }
        
        #products-table tr th:nth-child(5),
        #products-table tr td:nth-child(5) {
            width: 120px;
            min-width: 120px;
            text-align: right;
          
        }
        
        #products-table .vat-cell {
            text-align: right;
        }
        
        #products-table .form-control-sm {
            width: 100%;
        }
    </style>
    @vite('resources/assets/js/purchase-edit.js')
    <script>
        // Load existing products after the main script has loaded
        $(document).ready(function() {
            // Wait for the purchase-edit.js to fully initialize
            function loadProducts() {
                if (typeof window.addProductToTable === 'function') {
                    @if(isset($purchase) && $purchase->items && $purchase->items->count() > 0)
                        @foreach($purchase->items as $item)
                            @if($item->product)
                                window.addProductToTable(
                                    {{ $item->product_id }},
                                    '{{ addslashes($item->product->name ?? 'Product #' . $item->product_id) }} ({{ addslashes($item->product->sku ?? 'N/A') }})',
                                    {{ number_format((float)$item->quantity, 0, '.', '') }},
                                    {{ number_format((float)($item->unit_cost ?? 0), 2, '.', '') }},
                                    {{ number_format((float)($item->unit_vat ?? 0), 2, '.', '') }}
                                );
                            @endif
                        @endforeach
                    @endif
                } else {
                    // Retry after a short delay if function not yet available
                    setTimeout(loadProducts, 100);
                }
            }
            
            // Start loading products after a short delay to ensure DOM is ready
            setTimeout(loadProducts, 200);
        });
    </script>
@endsection

@section('content')
    <div class="app-ecommerce">
        <!-- Edit Purchase -->
        <form id="editPurchaseForm" method="POST" action="{{ route('purchase.update') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id" value="{{ $purchase->id }}">

            <div style="background: var(--bs-body-bg);"
                class="py-5 px-2 card-header sticky-element d-flex justify-content-sm-between align-items-sm-center flex-column flex-sm-row">
                <div class="d-flex flex-column justify-content-center">
                    <h4 class="mb-1">Edit Purchase @if($purchase->reference_no)(#PO{{ $purchase->reference_no }})@endif</h4>
                    <p class="mb-0">Please fill in the information below. The field labels marked with * are required input fields.</p>
                </div>
                <div class="d-flex align-content-center flex-wrap gap-4">
                    <div class="d-flex gap-4">
                        <a href="{{ route('purchase.list') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <!-- Basic Information -->
                    <div class="card mb-6">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-4 form-control-validation">
                                    <label class="form-label" for="date">Date <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control flatpickr" id="date" name="date" 
                                        value="{{ old('date', $purchase->date->format('d/m/Y H:i')) }}">
                                    @error('date')
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4 mb-4 form-control-validation">
                                    <label class="form-label" for="document">Attach Document</label>
                                    @if($purchase->document)
                                        <div class="mb-2">
                                            <a href="{{ asset('storage/' . $purchase->document) }}" target="_blank" class="btn btn-sm btn-label-primary">
                                                <i class="icon-base ti tabler-eye me-1"></i>View Current Document
                                            </a>
                                        </div>
                                    @endif
                                    <input type="file" class="form-control" id="document" name="document" 
                                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                    @error('document')
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-4 form-control-validation">
                                    <label class="form-label" for="supplier_id">Supplier <span class="text-danger">*</span></label>
                                    <select class="form-select" id="supplier_id" name="supplier_id">
                                        <option value="" disabled {{ old('supplier_id', optional($purchase->supplier)->id) ? '' : 'selected' }}>Select Supplier</option>
                                        @forelse($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}" {{ old('supplier_id', optional($purchase->supplier)->id) == $supplier->id ? 'selected' : '' }}>
                                                {{ $supplier->company ?? $supplier->full_name ?? 'Supplier #' . $supplier->id }}
                                            </option>
                                        @empty
                                            <option value="" disabled>No active suppliers available</option>
                                        @endforelse
                                    </select>
                                    @error('supplier_id')
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                
                                 <div class="col-md-4 mb-4 form-control-validation">
                                     <label class="form-label" for="deliver">Deliver </label>
                                     <select class="form-select" id="deliver" name="deliver">
                                        <option value="">Select Deliver</option>
                                         <option value="purchase" {{ old('deliver', $purchase->deliver ?? 'purchase') == 'purchase' ? 'selected' : '' }}>Purchase</option>
                                         <option value="delivery_note" {{ old('deliver', $purchase->deliver ?? 'purchase') == 'delivery_note' ? 'selected' : '' }}>Delivery Note</option>
                                     </select>
                                     @error('deliver')
                                         <span class="text-danger" role="alert">
                                             <strong>{{ $message }}</strong>
                                         </span>
                                     @enderror
                                 </div>
                                 <div class="col-md-4 mb-4 form-control-validation">
                                     <label class="form-label" for="shipping_charge">Shipping Charge</label>
                                     <input type="text" class="form-control" id="shipping_charge" name="shipping_charge" 
                                         value="{{ old('shipping_charge', number_format($purchase->shipping_charge ?? 0, 2, '.', '')) }}" placeholder="0.00" 
                                         onkeypress="return /^[0-9.]+$/.test(event.key)" autocomplete="off">
                                     @error('shipping_charge')
                                         <span class="text-danger" role="alert">
                                             <strong>{{ $message }}</strong>
                                         </span>
                                     @enderror
                                 </div>
                                
                            </div>
                        </div>
                    </div>

                    <!-- Products Section -->
                    <div class="card mb-6">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Products <span class="text-danger">*</span></h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-4" id="product-search-container">
                                <label class="form-label" for="product-search">Search Product</label>
                                <div class="position-relative">
                                    <input type="text" id="product-search" class="form-control pe-5" placeholder="Please add products to order list" autocomplete="off" autofocus>
                                    <div id="product-search-spinner" class="position-absolute top-50 end-0 translate-middle-y me-3" style="display: none;">
                                        <div class="spinner-border spinner-border-sm text-primary" role="status" aria-hidden="true"></div>
                                    </div>
                                    <div id="product-search-results" class="list-group position-absolute w-100 shadow-sm bg-white" style="z-index: 1050; display: none; max-height: 280px; overflow-y: auto;"></div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table id="products-table" class="table table-bordered">
                                    <thead class="table-primary">
                                        <tr>
                                            <th width="35%">Product Name (Product Code)</th>
                                            <th width="15%">Cost Price</th>
                                            <th width="12%">Quantity</th>
                                            <th width="12%">VAT</th>
                                            <th width="15%">Sub Total</th>
                                            <th style="width: 50px;"><i class="icon-base ti tabler-trash"></i></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="total-row">
                                            <td class="text-end fw-bold" colspan="2">Total</td>
                                            <td class="fw-bold total-quantity">0.00</td>
                                            <td></td>
                                            <td class="fw-bold total-amount">0.00</td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            @error('products')
                                <span class="text-danger" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <!-- Note Section -->
                    <div class="card mb-6">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Note</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-control p-0">
                                <div class="comment-toolbar border-0 border-bottom">
                                    <div class="d-flex justify-content-start">
                                        <span class="ql-formats me-0">
                                            <button class="ql-bold"></button>
                                            <button class="ql-italic"></button>
                                            <button class="ql-underline"></button>
                                            <button class="ql-strike"></button>
                                            <button class="ql-list" value="ordered"></button>
                                            <button class="ql-list" value="bullet"></button>
                                            <button class="ql-align" value=""></button>
                                            <button class="ql-align" value="center"></button>
                                            <button class="ql-align" value="right"></button>
                                            <button class="ql-align" value="justify"></button>
                                            <button class="ql-link"></button>
                                            <button class="ql-image"></button>
                                            <button class="ql-code-block"></button>
                                            <button class="ql-clean"></button>
                                        </span>
                                    </div>
                                </div>
                                <div id="note-editor" class="comment-editor border-0 pb-6" style="min-height: 200px;">
                                </div>
                            </div>
                            <input type="hidden" name="note" id="note" value="{{ old('note', $purchase->note) }}">
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

