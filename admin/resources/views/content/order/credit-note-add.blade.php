@extends('layouts/layoutMaster')

@section('title', 'Add Credit Note')

@section('vendor-style')
    @vite([
        'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
        'resources/assets/vendor/libs/@form-validation/form-validation.scss'
    ])
@endsection

@section('vendor-script')
    @vite([
        'resources/assets/vendor/libs/flatpickr/flatpickr.js',
        'resources/assets/vendor/libs/@form-validation/popular.js',
        'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
        'resources/assets/vendor/libs/@form-validation/auto-focus.js'
    ])
@endsection

@section('page-script')
    <style>
        #products-table th:nth-child(1),
        #products-table td:nth-child(1) {
            width: auto;
            min-width: 200px;
        }
        
        #products-table th:nth-child(2),
        #products-table td:nth-child(2) {
            width: 120px;
            min-width: 120px;
            text-align: right;
        }
        
        #products-table th:nth-child(3),
        #products-table td:nth-child(3) {
            width: 100px;
            min-width: 100px;
            text-align: right;
        }
        
        #products-table th:nth-child(4),
        #products-table td:nth-child(4) {
            width: 100px;
            min-width: 100px;
            text-align: right;
        }
        
        #products-table .form-control-sm {
            width: 100%;
        }
        
        #products-table .quantity-display {
            /* padding: 0.375rem 0.75rem;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem; */
        }
    </style>
    <script>
        window.currencySymbol = @json($setting['currency_symbol'] ?? '');
    </script>
    @vite('resources/assets/js/credit-note-add.js')
@endsection

@section('content')
    <div class="app-ecommerce">
        <!-- Credit Note Form -->
        <form id="creditNoteForm" method="POST" action="{{ route('credit-note.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="order_id" value="{{ $order->id }}">

            <div style="background: var(--bs-body-bg);"
                class="py-5 px-2 card-header sticky-element d-flex justify-content-sm-between align-items-sm-center flex-column flex-sm-row">
                <div class="d-flex flex-column justify-content-center">
                    <h4 class="mb-1">Add Credit Note (Order #{{ ($order->type ?? 'SO') . $order->order_number }})</h4>
                    <p class="mb-0">Please fill in the returned quantities below. The field labels marked with * are required input fields.</p>
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
                                <div class="col-md-4 align-content-end">
                                    <label class="form-label fs-6">Customer: <strong>{{ $order->customer->name ?? ($order->customer->company_name ?? 'N/A') }}</strong></label>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fs-6">Order Date: <strong>{{ $order->order_date ? \Carbon\Carbon::parse($order->order_date)->format('d/m/Y H:i') : 'N/A' }}</strong></label>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fs-6">Order Number: <strong>#{{ ($order->type ?? 'SO') . $order->order_number }}</strong></label>
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
                            <div class="table-responsive">
                                <table id="products-table" class="table table-bordered">
                                    <thead class="table-primary">
                                        <tr>
                                            <th width="40%">Product Name (Product Code)</th>
                                            <th width="20%">Sale Price</th>
                                            <th width="15%">Order Qty</th>
                                            <th width="15%">Returned Qty <span class="text-danger">*</span></th>
                                            <th width="10%">Sub Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(isset($order) && $order->items && $order->items->count() > 0)
                                            @foreach($order->items as $item)
                                                @if($item->product)
                                                    <tr data-product-id="{{ $item->product_id }}" data-order-qty="{{ $item->quantity }}">
                                                        <td>
                                                            {{ $item->product->name ?? 'Product #' . $item->product_id }} ({{ $item->product->sku ?? 'N/A' }})
                                                            <input type="hidden" name="products[{{ $item->product_id }}][product_id]" value="{{ $item->product_id }}">
                                                        </td>
                                                        <td class="text-end">
                                                            <span class="quantity-display">{{ ($setting['currency_symbol'] ?? '') . number_format((float)($item->unit_price ?? 0), 2, '.', '') }}</span>
                                                            <input type="hidden" name="products[{{ $item->product_id }}][unit_price]" value="{{ $item->unit_price ?? 0 }}">
                                                        </td>
                                                        <td class="text-end">
                                                            <span class="quantity-display">{{ number_format((float)$item->quantity, 0, '.', '') }}</span>
                                                            <input type="hidden" name="products[{{ $item->product_id }}][order_quantity]" value="{{ $item->quantity }}">
                                                        </td>
                                                        <td class="text-end">
                                                            <input type="text" 
                                                                   onkeypress="return /^[0-9]+$/.test(event.key)" 
                                                                   class="form-control form-control-sm returned-input" 
                                                                   name="products[{{ $item->product_id }}][returned_quantity]" 
                                                                   value="0" 
                                                                   data-order-qty="{{ $item->quantity }}"
                                                                   data-product-id="{{ $item->product_id }}"
                                                                   autocomplete="off"
                                                                   required>
                                                            <small class="text-danger error-message" id="error-{{ $item->product_id }}" style="display: none;"></small>
                                                        </td>
                                                        <td class="text-end subtotal-cell">0.00</td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        @endif
                                        <tr class="total-row">
                                            <td class="text-end fw-bold" colspan="3">Total</td>
                                            <td class="fw-bold total-returned">0</td>
                                            <td class="fw-bold total-amount">0.00</td>
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
                </div>
            </div>
        </form>
    </div>
@endsection

