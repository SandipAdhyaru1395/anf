@extends('layouts/layoutMaster')

@section('title', 'Edit Product')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/quill/typography.scss', 'resources/assets/vendor/libs/quill/katex.scss', 'resources/assets/vendor/libs/quill/editor.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/dropzone/dropzone.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/tagify/tagify.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/quill/quill.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/dropzone/dropzone.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/product-edit.js'])
    <script>
        $(document).ready(function() {

            $('#stepPlus').click(function() {

                var step = $('#step').val();

                if (step > 0) {
                    step = parseInt(step) + 1;
                    $('#step').val(step);
                } else {
                    $('#step').val(1);
                }
            });

            $('#stepMinus').click(function() {
                var step = $('#step').val();

                if (step > 1) {
                    step = parseInt(step) - 1;
                    $('#step').val(step);
                } else {
                    $('#step').val(1);
                }
            });
            $('#step').on('paste', function(e) {
                e.preventDefault();
            });
        });


        function updateVatAmount() {

            var price = parseFloat($('#ecommerce-product-price').val()) || 0;
            var $selected = $('#vat_method_id option:selected');

            if (!$selected.val()) {
                $('#vat_amount').val('');
                $('#vat_method_type').val('');
                $('#vat_method_name').val('');
                $('#vat_amount_display').text(currencySymbol + '0.00');
                return;
            }

            var type = $selected.data('type');
            var name = $selected.data('name');
            var amount = parseFloat($selected.data('amount'));
            var vat = 0;

            if (type === 'Percentage') {
                vat = price * amount / 100;
            } else {
                vat = amount;
            }

            $('#vat_amount').val(vat.toFixed(2));
            $('#vat_method_type').val(type);
            $('#vat_method_name').val(name);
            $('#vat_amount_display').text(
                currencySymbol + vat.toFixed(2)
            );
        }

        $(document).ready(function() {
            $('#vat_method_id').on('change', updateVatAmount);
            $('#ecommerce-product-price').on('input', updateVatAmount);
            updateVatAmount(); // Run initially in case fields are pre-filled
            
            // Handle image URL preview with fallback
            const defaultImagePath = '{{ asset('public/public/assets/img/default_product.png') }}';
            const $imageUrlInput = $('#productImageUrl');
            const $imagePreview = $('#imagePreview');
            const $imagePreviewContainer = $('#imagePreviewContainer');
            const $existingImageContainer = $('#existingImageContainer');
            const originalImageUrl = $imageUrlInput.val().trim();
            
            // Don't show preview initially if URL matches existing product image
            // Only show preview if user changes the URL
            $imageUrlInput.on('input blur', function() {
                const currentUrl = $(this).val().trim();
                if (currentUrl && currentUrl !== originalImageUrl) {
                    // Show preview for new/changed URL
                    $imagePreview.attr('src', currentUrl);
                    $imagePreviewContainer.show();
                    // Hide existing image when showing preview
                    if ($existingImageContainer.length) {
                        $existingImageContainer.hide();
                    }
                } else if (currentUrl === originalImageUrl) {
                    // Show existing image if URL matches original
                    $imagePreviewContainer.hide();
                    if ($existingImageContainer.length) {
                        $existingImageContainer.show();
                    }
                } else {
                    // URL is empty
                    $imagePreviewContainer.hide();
                    if ($existingImageContainer.length) {
                        $existingImageContainer.show();
                    }
                }
            });
        });
    </script>
@endsection

@section('content')
    <div class="app-ecommerce">
        <!-- Add Product -->
        <form id="editProductForm" method="POST" action="{{ route('product.update') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id" value="{{ $product->id }}">
            <div style="background: var(--bs-body-bg);"
                class="py-5 px-2 card-header sticky-element d-flex justify-content-sm-between align-items-sm-center flex-column flex-sm-row">
                <div class="d-flex flex-column justify-content-center">
                    <h4 class="mb-1">Edit Product</h4>
                </div>
                <div class="d-flex align-content-center flex-wrap gap-4">
                    <div class="d-flex gap-4">
                        <a href="{{ route('product.list') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- First column-->
                <div class="col-12 col-lg-8">
                    <!-- Product Information -->
                    <div class="card mb-6">
                        <div class="card-header">
                            <h5 class="card-tile mb-0">Product information</h5>
                        </div>
                        <div class="card-body">

                            <div class="mb-6 form-control-validation">
                                <label class="form-label" for="ecommerce-product-name">Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="ecommerce-product-name"
                                    placeholder="Product title" name="productTitle" aria-label="Product title"
                                    value="{{ $product->name }}" autocomplete="off" />
                                @error('productTitle')
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="row mb-6">
                                <div class="col form-control-validation"><label class="form-label" for="ecommerce-product-sku">Product Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="ecommerce-product-sku" placeholder="Product Code"
                                        name="productSku" aria-label="Product Code" value="{{ $product->sku }}"
                                        autocomplete="off" />
                                    @error('productSku')
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="col form-control-validation"><label class="form-label" for="ecommerce-product-unit-sku">Product Unit Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="ecommerce-product-unit-sku" placeholder="Product Unit Code"
                                        name="productUnitSku" aria-label="Product Unit Code" value="{{ $product->product_unit_sku }}"
                                        autocomplete="off" />
                                    @error('productUnitSku')
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                            </div>
                            <div class="row">
                                <!-- Base Price -->
                                <div class="col-lg-4 mb-5 form-control-validation">
                                    <label class="form-label" for="ecommerce-product-price">Selling Price <span
                                            class="text-danger">*</span></label>
                                    <input type="text" onkeypress="return /^[0-9.]+$/.test(event.key)"
                                        class="form-control" id="ecommerce-product-price" placeholder="Selling Price"
                                        name="productPrice" aria-label="Product price" value="{{ $product->price }}"
                                        autocomplete="off" />
                                    @error('productPrice')
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <!-- Cost Price -->
                                <div class="col-lg-4 mb-5 form-control-validation">
                                    <label class="form-label" for="cost-price">Cost Price </label>
                                    <input type="text" onkeypress="return /^[0-9.]+$/.test(event.key)"
                                        class="form-control" id="cost-price" placeholder="Cost Price" name="costPrice"
                                        aria-label="Cost price" value="{{ $product->cost_price }}" autocomplete="off" />
                                </div>
                                <!-- Wallet Credit -->
                                <div class="col-lg-4 mb-5 form-control-validation">
                                    <label class="form-label" for="wallet-credit">Wallet Credit</label>
                                    <input type="text" onkeypress="return /^[0-9.]+$/.test(event.key)"
                                        class="form-control" id="wallet-credit" placeholder="Credit" name="walletCredit"
                                        aria-label="Wallet Credit" value="{{ $product->wallet_credit }}"
                                        autocomplete="off" />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-4 mb-5 form-control-validation">
                                    <label class="form-label" for="weight">Weight (Kg)</label>
                                    <input type="text" onkeypress="return /^[0-9.]+$/.test(event.key)" class="form-control" id="weight" placeholder="Weight" name="weight" value="{{ $product->weight }}" autocomplete="off" />
                                    @error('weight')
                                        <span class="text-danger" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                                <div class="col-lg-4 mb-5 form-control-validation">
                                    <label class="form-label" for="rrp">RRP</label>
                                    <input type="text" onkeypress="return /^[0-9.]+$/.test(event.key)" class="form-control" id="rrp" placeholder="RRP" name="rrp" value="{{ $product->rrp }}" autocomplete="off" />
                                    @error('rrp')
                                        <span class="text-danger" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                                <div class="col-lg-4 mb-5 form-control-validation">
                                    <label class="form-label" for="expiry_date">Expiry Date</label>
                                    <input type="text" class="form-control flatpickr" id="expiry_date" name="expiry_date" placeholder="dd/mm/yyyy" value="{{ $product->expiry_date ? \Carbon\Carbon::parse($product->expiry_date)->format('d/m/Y') : '' }}" readonly />
                                    @error('expiry_date')
                                        <span class="text-danger" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-6">
                                <div class="col-md-4 mb-5 form-control-validation"><label class="form-label"
                                        for="quantity">Quantity</label>
                                    <input type="text" class="form-control" id="quantity" placeholder="Enter quantity"
                                        name="quantity" onkeypress="return /^[0-9]+$/.test(event.key)"
                                        aria-label="Product Quantity" value="{{ $product->stock_quantity }}"
                                        autocomplete="off" />
                                </div>
                                <div class="col-md-4 mb-5 form-control-validation">
                                    <label class="form-label" for="unit_id">Product Unit</label>
                                    <select name="unit_id" id="unit_id" class="form-select select2">
                                        <option value="">Select Unit</option>
                                        @foreach ($units as $unit)
                                            <option value="{{ $unit->id }}" @selected($product->unit_id == $unit->id)>
                                                {{ $unit->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 mb-5 form-control-validation">
                                    <label class="form-label" for="vat_method_id">VAT Method</label>
                                    <select name="vat_method_id" id="vat_method_id" class="form-select select2">
                                        <option value="">Select VAT Method</option>
                                        @foreach ($vatMethods as $vat)
                                            <option value="{{ $vat->id }}" data-type="{{ $vat->type }}"
                                                data-name="{{ $vat->name }}" data-amount="{{ $vat->amount }}"
                                                @if ($product->vat_method_id == $vat->id) selected @endif>
                                                {{ $vat->name }}
                                                ({{ $vat->type == 'Percentage' ? $vat->amount . '%' : $currencySymbol . number_format($vat->amount, 2) }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="vat_method_name" id="vat_method_name">
                                    <input type="hidden" name="vat_method_type" id="vat_method_type">
                                    <input type="hidden" name="vat_amount" id="vat_amount">
                                    <div class="form-text mt-1">Calculated VAT: <span id="vat_amount_display">0.00</span>
                                    </div>
                                </div>
                                <div class="col-md-4 col-8 mx-auto mb-5 form-control-validation"><label class="form-label"
                                        for="step_quantity" class="form-label">Step Quantity</label>
                                    <div class="position-relative d-flex gap-2 align-items-center w-75">
                                        <button style="width:25px; left:10;"
                                            class="position-absolute btn btn-sm btn-danger h-75" type="button"
                                            id="stepMinus">
                                            <i class="flex-shrink-0 ti tabler-minus"></i>
                                        </button>
                                        <input type="text" name="step" id="step"
                                            onkeypress="return /^[0-9]+$/.test(event.key)"
                                            class="form-control text-center" value="{{ $product->step_quantity ?? 1 }}"
                                            autocomplete="off">
                                        <button style="width:25px; right:10;"
                                            class="position-absolute btn btn-sm btn-success h-75" type="button"
                                            id="stepPlus">
                                            <i class="flex-shrink-0 ti tabler-plus"></i>
                                        </button>
                                    </div>
                                    @error('step')
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <!-- Description -->
                            <div>
                                <label class="mb-1">Description (Optional)</label>
                                <div class="form-control p-0">
                                    <div class="comment-toolbar border-0 border-bottom">
                                        <div class="d-flex justify-content-start">
                                            <span class="ql-formats me-0">
                                                <button class="ql-bold"></button>
                                                <button class="ql-italic"></button>
                                                <button class="ql-underline"></button>
                                                <button class="ql-list" value="ordered"></button>
                                                <button class="ql-list" value="bullet"></button>
                                                <button class="ql-link"></button>
                                                <button class="ql-image"></button>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="comment-editor border-0 pb-6" id="product-description">
                                        {!! $product->description !!}</div>
                                </div>
                                <input type="hidden" name="productDescription" id="productDescription"
                                    value="{{ $product->description }}">
                            </div>
                        </div>
                    </div>
                    <!-- /Product Information -->
                    <!-- Media -->
                    <div class="card mb-6">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Product Image</h5>
                        </div>
                        <div class="card-body">
                            <div id="imagePreviewContainer" class="mb-4 text-center" style="display: none;">
                                <img id="imagePreview" class="align-self-center" height="300px" width="400px"
                                    alt="Product Image Preview" 
                                    onerror="this.onerror=null; this.src='{{ asset('assets/img/default_product.png') }}';" />
                            </div>
                            @if($product->image_url)
                                <div id="existingImageContainer" class="mb-4 text-center">
                                    <img class="align-self-center" height="300px" width="400px"
                                        src="{{ $product->image_url }}" alt="Current Product Image" 
                                        onerror="this.onerror=null; this.src='{{ asset('assets/img/default_product.png') }}';" />
                                </div>
                            @endif
                            <div class="mb-4 form-control-validation">
                                <label class="form-label" for="productImageUrl">Image URL</label>
                                <input type="url" class="form-control" id="productImageUrl"
                                    placeholder="https://example.com/image.jpg" name="productImageUrl" 
                                    aria-label="Product Image URL" value="{{ old('productImageUrl', $product->image_url) }}" 
                                    autocomplete="off" />
                                <div class="form-text">Enter a full image URL or upload an image file below (at least one is required if no existing image)</div>
                                @error('productImageUrl')
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Or Upload Image</label>
                            </div>
                            <div class="form-control-validation">
                                <input type="file" name="productImage" id="productImage" hidden>
                                <div class="dropzone needsclick p-0" id="dropzone-basic">
                                    <div class="dz-message needsclick">
                                        <p class="h4 needsclick pt-3 mb-2">Drag and drop your image here</p>
                                        <p class="h6 text-body-secondary d-block fw-normal mb-2">or</p>
                                        <span class="needsclick btn btn-sm btn-label-primary" id="btnBrowse">Browse
                                            image</span>
                                    </div>
                                </div>
                            </div>
                            @error('productImage')
                                <span class="text-danger text-center mb-5" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <!-- /Media -->
                </div>
                <!-- /Second column -->

                <!-- Second column -->
                <div class="col-12 col-lg-4">
                    <!-- Organize Card -->
                    <div class="card mb-6">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Product Label</h5>
                        </div>
                        <div class="card-body">
                            <!-- Status -->
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="mb-6 col ecommerce-select2-dropdown form-control-validation">
                                    <label class="form-label mb-5" for="productStatus">
                                        <span>Status <span class="text-danger">*</span></span>
                                    </label>
                                    <select class="form-select select2" name="productStatus" id="productStatus">
                                        <option value="1" @selected($product->is_active == '1')>Active</option>
                                        <option value="0" @selected($product->is_active == '0')>Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-6 form-control-validation">
                                <label class="form-label" for="brand_id">Brand <span class="text-danger">*</span></label>
                                <select class="form-control select2" name="brands[]" multiple>
                                    @forelse ($brands as $brand)
                                        <option value="{{ $brand->id }}" @selected(in_array($brand->id, $productBrands))>
                                            {{ $brand->name }}</option>
                                    @empty
                                    @endforelse
                                </select>
                                @error('brands')
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <!-- /Organize Card -->
                </div>
                <!-- /Second column -->
            </div>
        </form>
    </div>

@endsection

@push('scripts')
    <script>
        var vatMethodsJS = @json($vatMethods);
        var currencySymbol = @json($currencySymbol);

        function updateVatAmount() {
            var price = parseFloat(document.getElementById('ecommerce-product-price').value) || 0;
            var select = document.getElementById('vat_method_id');
            var selected = select.options[select.selectedIndex];
            if (!selected || !selected.value) {
                document.getElementById('vat_amount').value = '';
                document.getElementById('vat_method_type').value = '';
                document.getElementById('vat_method_name').value = '';
                document.getElementById('vat_amount_display').innerText = '0.00';
                return;
            }
            var type = selected.getAttribute('data-type');
            var name = selected.getAttribute('data-name');
            var amount = parseFloat(selected.getAttribute('data-amount'));
            var vat = 0;
            if (type === 'Percentage') {
                vat = price * amount / 100;
            } else {
                vat = amount;
            }
            document.getElementById('vat_amount').value = vat.toFixed(2);
            document.getElementById('vat_method_type').value = type;
            document.getElementById('vat_method_name').value = name;
            document.getElementById('vat_amount_display').innerText = vat.toFixed(2) + (type === 'Percentage' ? '%' : ' ' +
                currencySymbol);
        }
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('vat_method_id').addEventListener('change', updateVatAmount);
            document.getElementById('ecommerce-product-price').addEventListener('input', updateVatAmount);
            // Pre-fill on load if product already has VAT
            let preType = "{{ $product->vat_method_type ?? '' }}";
            let preName = "{{ $product->vat_method_name ?? '' }}";
            let preVat = parseFloat("{{ $product->vat_amount ?? 0 }}");
            if (preType && preName) {
                document.getElementById('vat_method_type').value = preType;
                document.getElementById('vat_method_name').value = preName;
                document.getElementById('vat_amount').value = preVat;
                document.getElementById('vat_amount_display').innerText = preVat.toFixed(2) + (preType ===
                    'Percentage' ? '%' : ' ' + currencySymbol);
            }
            // Always trigger update, even for initial blank or browser-prefill situations
            updateVatAmount();
        });
    </script>
@endpush
