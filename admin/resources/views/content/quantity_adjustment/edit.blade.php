@extends('layouts/layoutMaster')

@section('title', 'Edit Adjustment')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/quill/typography.scss', 'resources/assets/vendor/libs/quill/katex.scss', 'resources/assets/vendor/libs/quill/editor.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/quill/quill.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js'])
@endsection

@section('page-script')
    <style>
        /* Product search dropdown styling */
        #product-search-results { border: 0 !important; }
        #product-search-results .list-group-item { color: #000 !important; border: 0 !important; }
        #product-search-results .list-group-item:hover,
        #product-search-results .list-group-item.active { color: #fff !important; background-color: var(--bs-primary) !important; }
    </style>
    <script>
        $(document).ready(function() {
            // Initialize flatpickr for date & time
            $('.flatpickr').flatpickr({
                enableTime: true,
                dateFormat: 'd/m/Y H:i',
                time_24hr: true,
                allowInput: true
            });

            // Product search textbox
            const MAX_RESULTS = 20;
            const $productSearch = $('#product-search');
            const $productResults = $('#product-search-results');
            const $productSpinner = $('#product-search-spinner');
            let activeIndex = -1;
            let searchTimeout = null;
            let currentQuery = '';
            let currentRequest = null;

            function resetResults() {
                if (currentRequest && currentRequest.readyState !== 4) {
                    currentRequest.abort();
                }
                currentRequest = null;
                activeIndex = -1;
                $productResults.empty().hide();
            }

            function setActiveItem(index) {
                const $items = $productResults.find('.list-group-item');
                $items.removeClass('active');
                if (index >= 0 && index < $items.length) {
                    $items.eq(index).addClass('active');
                    activeIndex = index;
                }
            }

            function renderResults(items) {
                $productResults.empty();
                if (!items.length) {
                    activeIndex = -1;
                    $productResults
                        .append('<div class="list-group-item bg-white text-muted">No results found</div>')
                        .show();
                    return;
                }

                items.forEach(function(item) {
                    const $button = $('<button type="button" class="list-group-item list-group-item-action bg-white text-dark"></button>')
                        .text(item.text)
                        .data('productId', item.id)
                        .data('productText', item.text);
                    $productResults.append($button);
                });

                $productResults.show();
                setActiveItem(0);
            }

            function fetchProducts(query) {
                if (currentRequest) {
                    currentRequest.abort();
                }

                currentRequest = $.ajax({
                    url: baseUrl + 'quantity-adjustment/search/ajax',
                    dataType: 'json',
                    data: {
                        q: query,
                        limit: MAX_RESULTS
                    }
                }).done(function(data) {
                    if (currentQuery !== query) {
                        return;
                    }
                    const results = data && data.results ? data.results.slice(0, MAX_RESULTS) : [];
                    renderResults(results);
                }).fail(function(xhr, status) {
                    if (status !== 'abort') {
                        resetResults();
                    }
                }).always(function() {
                    currentRequest = null;
                    $productSpinner.hide();
                });
            }

            $productSearch.on('input', function() {
                const query = $(this).val().trim();
                currentQuery = query;
                clearTimeout(searchTimeout);

                if (!query) {
                    resetResults();
                    return;
                }

                searchTimeout = setTimeout(function() {
                    $productSpinner.show();
                    fetchProducts(query);
                }, 200);
            });

            $productSearch.on('keydown', function(e) {
                if (!$productResults.is(':visible')) {
                    return;
                }

                const $items = $productResults.find('.list-group-item');
                if (!$items.length) {
                    return;
                }

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    const nextIndex = activeIndex >= $items.length - 1 ? 0 : activeIndex + 1;
                    setActiveItem(nextIndex);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    const prevIndex = activeIndex <= 0 ? $items.length - 1 : activeIndex - 1;
                    setActiveItem(prevIndex);
                } else if (e.key === 'Enter') {
                    if (activeIndex >= 0) {
                        e.preventDefault();
                        $items.eq(activeIndex).trigger('click');
                    }
                } else if (e.key === 'Escape') {
                    resetResults();
                }
            });

            $productResults.on('mousedown', '.list-group-item', function(e) {
                e.preventDefault();
            });

            $productResults.on('mouseenter', '.list-group-item', function() {
                setActiveItem($(this).index());
            });

            $productResults.on('click', '.list-group-item', function() {
                if (!$(this).data('productId')) {
                    return; // Non-selectable (e.g., No results found)
                }
                const productId = $(this).data('productId');
                const productText = $(this).data('productText');
                addProductToTable(productId, productText);
                $productSearch.val('').trigger('focus');
                resetResults();
            });

            $(document).on('click', function(e) {
                if (!$(e.target).closest('#product-search-container').length) {
                    resetResults();
                }
            });

            setTimeout(function() {
                $productSearch.trigger('focus');
            }, 0);

            // Calculate total quantity
            function calculateTotal() {
                var total = 0;
                $('#products-table tbody tr:not(.total-row)').each(function() {
                    var qty = parseFloat($(this).find('.quantity-input').val()) || 0;
                    total += qty;
                });
                $('.total-quantity').text(total.toFixed(2));
            }

            // Add product row
            function addProductToTable(productId, productText, type = 'addition', quantity = 1) {
                // Check if product already exists in the table
                var existingRow = $('#products-table tbody tr[data-product-id="' + productId + '"]:not(.total-row)');
                
                if (existingRow.length > 0) {
                    // Product exists, increment quantity
                    var quantityInput = existingRow.find('.quantity-input');
                    var currentQty = parseFloat(quantityInput.val()) || 0;
                    var newQty = currentQty + quantity;
                    quantityInput.val(newQty);
                    calculateTotal();
                    return;
                }
                
                // Product doesn't exist, add new row
                var row = '<tr data-product-id="' + productId + '">' +
                    '<td>' + productText + '</td>' +
                    '<td>' +
                        '<select class="form-select form-select-sm type-select" name="products[' + productId + '][type]" required>' +
                            '<option value="addition"' + (type === 'addition' ? ' selected' : '') + '>Addition</option>' +
                            '<option value="subtraction"' + (type === 'subtraction' ? ' selected' : '') + '>Subtraction</option>' +
                        '</select>' +
                        '<input type="hidden" name="products[' + productId + '][product_id]" value="' + productId + '">' +
                    '</td>' +
                    '<td><input type="text" onkeypress="return /^[0-9]+$/.test(event.key)" class="form-control form-control-sm quantity-input" name="products[' + productId + '][quantity]" value="' + quantity + '" autocomplete="off"></td>' +
                    '<td><a href="javascript:;" title="Remove" class="remove-product"><i class="icon-base ti tabler-x"></i></a></td>' +
                    '</tr>';
                
                $('#products-table tbody').prepend(row);
                calculateTotal();
            }

            // Load existing products
            @if(isset($adjustment) && $adjustment->items)
                @foreach($adjustment->items as $item)
                    addProductToTable(
                        {{ $item->product_id }},
                        '{{ $item->product->name }} ({{ $item->product->sku }})',
                        '{{ $item->type }}',
                        {{ $item->quantity }}
                    );
                @endforeach
            @endif

            // Remove product row
            $(document).on('click', '.remove-product', function() {
                $(this).closest('tr').remove();
                calculateTotal();
            });

            // Update total on quantity change
            $(document).on('input', '.quantity-input', function() {
                calculateTotal();
            });

            // Initialize Quill editor for note
            const noteEditor = document.querySelector('#note-editor');
            var quill;
            if (noteEditor) {
                quill = new Quill(noteEditor, {
                    modules: {
                        toolbar: '.comment-toolbar'
                    },
                    placeholder: 'Add note...',
                    theme: 'snow'
                });

                // Set initial content
                @if(isset($adjustment) && $adjustment->note)
                    quill.root.innerHTML = {!! json_encode($adjustment->note) !!};
                @endif

                // Update hidden input on editor change
                quill.on('text-change', function() {
                    let content = quill.root.innerHTML;
                    if (content === '<p><br></p>') {
                        content = ''; // Treat as empty
                    }
                    $('#note').val(content);
                });
            }

            // FormValidation for date and products
            const editAdjustmentForm = document.getElementById('editAdjustmentForm');
            if (editAdjustmentForm) {
                const fv = FormValidation.formValidation(editAdjustmentForm, {
                    fields: {
                        date: {
                            validators: {
                                notEmpty: {
                                    message: 'Date is required'
                                },
                                callback: {
                                    message: 'Enter a valid date in dd/mm/yyyy hh:mm format',
                                    callback: function(input) {
                                        const value = (input.value || '').trim();
                                        if (!value) return false;
                                        const re = /^\d{2}\/\d{2}\/\d{4}\s+\d{2}:\d{2}$/;
                                        if (!re.test(value)) return false;
                                        try {
                                            const parsed = flatpickr.parseDate(value, 'd/m/Y H:i');
                                            return parsed instanceof Date && !isNaN(parsed.getTime());
                                        } catch (e) {
                                            return false;
                                        }
                                    }
                                }
                            }
                        },
                        products: {
                            selector: '#products-table',
                            validators: {
                                callback: {
                                    message: 'Please add at least one product',
                                    callback: function() {
                                        return $('#products-table tbody tr:not(.total-row)').length > 0;
                                    }
                                }
                            }
                        }
                    },
                    plugins: {
                        trigger: new FormValidation.plugins.Trigger(),
                        bootstrap5: new FormValidation.plugins.Bootstrap5({
                            eleValidClass: 'is-valid',
                            rowSelector: function(field, ele) {
                                if (field === 'products') {
                                    return '.card-body';
                                }
                                return '.form-control-validation';
                            }
                        }),
                        submitButton: new FormValidation.plugins.SubmitButton(),
                        autoFocus: new FormValidation.plugins.AutoFocus()
                    }
                });

                // Revalidate products on changes
                $(document).on('click', '.remove-product', function() {
                    fv.revalidateField('products');
                });
                $(document).on('click', '#product-search-results .list-group-item, #product-search-results button', function() {
                    fv.revalidateField('products');
                });
                const tbody = document.querySelector('#products-table tbody');
                if (tbody) {
                    const observer = new MutationObserver(function() {
                        fv.revalidateField('products');
                    });
                    observer.observe(tbody, { childList: true, subtree: false });
                }

                // Submit form after successful validation
                fv.on('core.form.valid', function () {
                    editAdjustmentForm.submit();
                });
            }
        });
    </script>
@endsection

@section('content')
    <div class="app-ecommerce">
        <!-- Edit Adjustment -->
        <form id="editAdjustmentForm" method="POST" action="{{ route('quantity_adjustment.update') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id" value="{{ $adjustment->id }}">

            <div style="background: var(--bs-body-bg);"
                class="py-5 px-2 card-header sticky-element d-flex justify-content-sm-between align-items-sm-center flex-column flex-sm-row">
                <div class="d-flex flex-column justify-content-center">
                    <h4 class="mb-1">Edit Adjustment @if($adjustment->reference_no)(#QA{{ $adjustment->reference_no }})@endif</h4>
                    <p class="mb-0">Please fill in the information below. The field labels marked with * are required input fields.</p>
                </div>
                <div class="d-flex align-content-center flex-wrap gap-4">
                    <div class="d-flex gap-4">
                        <a href="{{ route('quantity_adjustment.list') }}" class="btn btn-secondary">Cancel</a>
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
                                <div class="col-md-3 mb-4 form-control-validation">
                                    <label class="form-label" for="date">Date <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control flatpickr" id="date" name="date" 
                                        value="{{ old('date', $adjustment->date->format('d/m/Y H:i')) }}" required>
                                    @error('date')
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="col-md-3 mb-4 form-control-validation">
                                    <label class="form-label" for="document">Attach Document</label>
                                    @if($adjustment->document)
                                        <div class="mb-2">
                                            <a href="{{ asset('storage/' . $adjustment->document) }}" target="_blank" class="btn btn-sm btn-label-primary">
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
                                            <th>Product Name (Product Code)</th>
                                            <th>Type</th>
                                            <th>Quantity</th>
                                            <th style="width: 50px;"><i class="icon-base ti tabler-trash"></i></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="total-row">
                                            <td colspan="2" class="text-end fw-bold">Total</td>
                                            <td class="fw-bold total-quantity">0.00</td>
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
                            <input type="hidden" name="note" id="note" value="{{ old('note', $adjustment->note) }}">
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

