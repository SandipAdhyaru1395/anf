@extends('layouts/layoutMaster')

@section('title', 'Add Order')

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
    @vite('resources/assets/js/order-add.js')
@endsection

@section('content')
    <div class="app-ecommerce">
        <!-- Add Order -->
        <form id="addOrderForm" method="POST" action="{{ route('order.create') }}" enctype="multipart/form-data">
            @csrf

            <div style="background: var(--bs-body-bg);"
                class="py-5 px-2 card-header sticky-element d-flex justify-content-sm-between align-items-sm-center flex-column flex-sm-row">
                <div class="d-flex flex-column justify-content-center">
                    <h4 class="mb-1">Add Order</h4>
                    <p class="mb-0">Please fill in the information below. The field labels marked with * are required input fields.</p>
                </div>
                <div class="d-flex align-content-center flex-wrap gap-4">
                    <div class="d-flex gap-4">
                        <a href="{{ route('order.list') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save</button>
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
                                    <label class="form-label" for="customer_id">Customer <span class="text-danger">*</span></label>
                                    <select class="form-select" id="customer_id" name="customer_id">
                                        <option value="">Select Customer</option>
                                        @forelse($customers as $customer)
                                            <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                                {{ $customer->company_name ?? $customer->email ?? 'Customer #' . $customer->id }}
                                            </option>
                                        @empty
                                            <option value="" disabled>No active customers available</option>
                                        @endforelse
                                    </select>
                                    @error('customer_id')
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4 mb-4 form-control-validation">
                                    <label class="form-label" for="date">Date <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control flatpickr" id="date" name="date" 
                                        value="{{ old('date', date('d/m/Y H:i')) }}">
                                    @error('date')
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-4 form-control-validation">
                                    <label class="form-label" for="address_id">Address <span class="text-danger">*</span></label>
                                    <select class="form-select" id="address_id" name="address_id">
                                        <option value="">Select Customer First</option>
                                    </select>
                                    @if(old('address_id'))
                                        <script>
                                            window.oldAddressId = {{ old('address_id') }};
                                        </script>
                                    @endif
                                    @error('address_id')
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                
                                
                                <div class="col-md-4 mb-4 form-control-validation">
                                    <label class="form-label" for="shipping_charge">Shipping Charge</label>
                                    <input type="text" class="form-control" id="shipping_charge" name="shipping_charge" 
                                        value="{{ old('shipping_charge', '0.00') }}" placeholder="0.00" 
                                        onkeypress="return /^[0-9.]+$/.test(event.key)" autocomplete="off">
                                    @error('shipping_charge')
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-4 form-control-validation">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" id="is_est" name="is_est" value="1" {{ old('is_est') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_est">
                                            Is estimated?
                                        </label>
                                    </div>
                                    @error('is_est')
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
                                            <th width="12%" id="sale-price-header">Sale Price</th>
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
                            <input type="hidden" name="delivery_note" id="note" value="{{ old('delivery_note', '') }}">
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

