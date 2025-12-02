@extends('layouts/layoutMaster')

@section('title', 'Order Details')

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
        #product-search-results .list-group-item.disabled,
        #product-search-results .list-group-item:disabled { 
            opacity: 0.6 !important; 
            cursor: not-allowed !important; 
            pointer-events: none !important;
        }
        #product-search-results .list-group-item.disabled:hover,
        #product-search-results .list-group-item:disabled:hover { 
            background-color: transparent !important; 
            color: #000 !important;
        }

         /* Products table column widths */
         #products-table th:nth-child(1),
        #products-table td:nth-child(1) {
            width: auto;
            min-width: 200px;
        }
        
        #products-table th:nth-child(2),
        #products-table td:nth-child(2) {
            width: 100px;
            min-width: 100px;
            
        }
        
        #products-table th:nth-child(3),
        #products-table td:nth-child(3) {
            width: 80px;
            min-width: 80px;
            text-align: right;
        }
        
        #products-table th:nth-child(4),
        #products-table td:nth-child(4) {
            width: 100px;
            min-width: 100px;
            text-align: right;
        }
        
        #products-table th:nth-child(5),
        #products-table td:nth-child(5) {
            width: 100px;
            min-width: 100px;
            text-align: right;
        }
        
        #products-table tr th:nth-child(6),
        #products-table tr td:nth-child(6) {
            width: 50px;
            min-width: 50px;
            text-align: center;
          
        }
        
        #products-table .form-control-sm {
            width: 100%;
        }
    </style>
    @vite('resources/assets/js/order-details.js')
    <script>
        // Load existing products after the main script has loaded
        $(document).ready(function() {
            // Initialize select2 for address dropdown
            const $addressSelect = $('#address_id');
            if ($addressSelect.length) {
                $addressSelect.select2({
                    placeholder: 'Select Address',
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $('#editOrderForm')
                });
            }

            // Wait for order-details.js to fully initialize
            function loadProducts() {
                if (typeof window.addProductToTable === 'function') {
                    @if(isset($order) && $order->items && $order->items->count() > 0)
                        @foreach($order->items as $item)
                            @if($item->product)
                                window.addProductToTable(
                                    {{ $item->product_id }},
                                    '{{ addslashes($item->product->name ?? 'Product #' . $item->product_id) }} ({{ addslashes($item->product->sku ?? 'N/A') }})',
                                    {{ number_format((float)$item->quantity, 0, '.', '') }},
                                    {{ number_format((float)($item->unit_price ?? 0), 2, '.', '') }},
                                    {{ number_format((float)($item->unit_vat ?? ($item->product->vat_amount ?? 0)), 2, '.', '') }}
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
        <!-- Order Details -->
        <form id="editOrderForm" method="POST" action="{{ route('order.update') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id" value="{{ $order->id }}">

            <div style="background: var(--bs-body-bg);"
                class="py-5 px-2 card-header sticky-element d-flex justify-content-sm-between align-items-sm-center flex-column flex-sm-row">
                <div class="d-flex flex-column justify-content-center">
                    <h4 class="mb-1">Order Details (#{{ ($order->type ?? 'SO') . $order->order_number }})</h4>
                    <p class="mb-0">Please fill in the information below. The field labels marked with * are required input fields.</p>
                </div>
                <div class="d-flex align-content-center flex-wrap gap-4">
                    <div class="d-flex gap-4">
                        <a href="{{ route('order.list') }}" class="btn btn-secondary">Cancel</a>
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
                                    <div class="col-md-4 mb-4 align-content-end">
                                        <label class="form-label fs-5">Customer: <strong>{{ $order->customer->name ?? ($order->customer->company_name ?? 'N/A') }}</strong></label>
    
                                    </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-4 form-control-validation">
                                    <label class="form-label" for="date">Date <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control flatpickr" id="date" name="date" 
                                        value="{{ old('date', $order->order_date ? \Carbon\Carbon::parse($order->order_date)->format('d/m/Y H:i') : '') }}">
                                    @error('date')
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                 <div class="col-md-4 mb-4 form-control-validation">
                                     <label class="form-label" for="shipping_charge">Shipping Charge</label>
                                     <input type="text" class="form-control" id="shipping_charge" name="shipping_charge" 
                                         value="{{ old('shipping_charge', number_format($order->delivery_charge ?? 0, 2, '.', '')) }}" placeholder="0.00" 
                                         onkeypress="return /^[0-9.]+$/.test(event.key)" autocomplete="off">
                                     @error('shipping_charge')
                                         <span class="text-danger" role="alert">
                                             <strong>{{ $message }}</strong>
                                         </span>
                                     @enderror
                                 </div>
                                 <div class="col-md-4 mb-4 form-control-validation">
                                    <label class="form-label" for="address_id">Address <span class="text-danger">*</span></label>
                                    <select class="form-select" id="address_id" name="address_id">
                                        <option value="">Select Address</option>
                                        @if($order->customer && $order->customer->branches)
                                            @foreach($order->customer->branches as $branch)
                                                @php
                                                    $addressText = $branch->name . ' - ' . $branch->address_line1;
                                                    if ($branch->address_line2) {
                                                        $addressText .= ', ' . $branch->address_line2;
                                                    }
                                                    $addressText .= ', ' . $branch->city;
                                                    if ($branch->zip_code) {
                                                        $addressText .= ' ' . $branch->zip_code;
                                                    }
                                                    if ($branch->country) {
                                                        $addressText .= ', ' . $branch->country;
                                                    }
                                                    $isSelected = old('address_id', ($order->branch_name == $branch->name) || 
                                                                  ($order->address_line1 == $branch->address_line1 && 
                                                                   $order->city == $branch->city) ? $branch->id : '') == $branch->id;
                                                @endphp
                                                <option value="{{ $branch->id }}" {{ $isSelected ? 'selected' : '' }}>
                                                    {{ $addressText }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    @error('address_id')
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
                                            <th width="30%">Product Name (Product Code)</th>
                                            <th width="12%">Sale Price</th>
                                            <th width="12%">Quantity</th>
                                            <th width="12%">VAT</th>
                                            <th width="12%">Sub Total</th>
                                            <th style="width: 50px;"><i class="icon-base ti tabler-trash"></i></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="total-row">
                                            <td class="text-end fw-bold" colspan="3">Total</td>
                                            <td class="text-end fw-bold total-vat">0.00</td>
                                            <td class="text-end fw-bold total-amount">0.00</td>
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
                            @php
                                $productErrors = [];
                                // Collect all errors with keys starting with 'products.'
                                foreach ($errors->getMessages() as $key => $messages) {
                                    if (strpos($key, 'products.') === 0) {
                                        foreach ($messages as $message) {
                                            if (!in_array($message, $productErrors)) {
                                                $productErrors[] = $message;
                                            }
                                        }
                                    }
                                }
                            @endphp
                            @if(!empty($productErrors))
                                <div class="text-danger">
                                    @foreach($productErrors as $error)
                                        <div><strong>{{ $error }}</strong></div>
                                    @endforeach
                                </div>
                            @endif
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
                            <input type="hidden" name="delivery_note" id="note" value="{{ old('delivery_note', $order->delivery_note ?? '') }}">
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
