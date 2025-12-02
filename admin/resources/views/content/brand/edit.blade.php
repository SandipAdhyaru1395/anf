@extends('layouts/layoutMaster')

@section('title', 'Edit Brand')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/quill/typography.scss', 'resources/assets/vendor/libs/quill/katex.scss', 'resources/assets/vendor/libs/quill/editor.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/dropzone/dropzone.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/tagify/tagify.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/dropzone/dropzone.js','resources/assets/vendor/libs/tagify/tagify.js',
        'resources/assets/vendor/libs/select2/select2.js','resources/assets/vendor/libs/@form-validation/popular.js','resources/assets/vendor/libs/@form-validation/bootstrap5.js','resources/assets/vendor/libs/@form-validation/auto-focus.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/brand-edit.js'])
    <script>
        $(document).ready(function() {
            // Handle image URL preview with fallback
            const defaultImagePath = '{{ asset('public/public/assets/img/default_brand.png') }}';
            const $imageUrlInput = $('#brandImageUrl');
            const $imagePreview = $('#imagePreview');
            const $imagePreviewContainer = $('#imagePreviewContainer');
            const $existingImageContainer = $('#existingImageContainer');
            const originalImageUrl = $imageUrlInput.val().trim();
            
            // Don't show preview initially if URL matches existing brand image
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
        <!-- Edit Brand -->
        <form id="editBrandForm" method="POST" action="{{ route('brand.update') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id" value="{{ $brand->id }}">
            <div
                style="background: var(--bs-body-bg);" class="py-5 px-2 card-header sticky-element d-flex justify-content-sm-between align-items-sm-center flex-column flex-sm-row">
                <div class="d-flex flex-column justify-content-center">
                    <h4 class="mb-1">Edit Brand</h4>
                </div>
                <div class="d-flex align-content-center flex-wrap gap-4">
                    <div class="d-flex gap-4">
                        <a href="{{ route('brand.list') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- First column-->
                <div class="col-12 col-lg-8">
                    <!-- Brand Information -->
                    <div class="card mb-6">
                        <div class="card-body">
                            <div class="mb-6 form-control-validation">
                                <label class="form-label" for="brand-name">Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="brand-name"
                                    placeholder="Brand title" name="brandTitle" aria-label="Brand title"
                                    value="{{ $brand->name }}" autocomplete="off" />
                                @error('brandTitle')
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <!-- /Brand Information -->
                    <!-- Media -->
                    <div class="card mb-6">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Brand Image</h5>
                        </div>
                        <div class="card-body">
                            <div id="imagePreviewContainer" class="mb-4 text-center" style="display: none;">
                                <img id="imagePreview" class="align-self-center" height="300px" width="400px"
                                    alt="Brand Image Preview" 
                                    onerror="this.onerror=null; this.src='{{ asset('assets/img/default_brand.png') }}';" />
                            </div>
                            @if($brand->image)
                                <div id="existingImageContainer" class="mb-4 text-center">
                                    <img class="align-self-center" height="300px" width="400px"
                                        src="{{ $brand->image }}" alt="Current Brand Image" 
                                        onerror="this.onerror=null; this.src='{{ asset('assets/img/default_brand.png') }}';" />
                                </div>
                            @endif
                            <div class="mb-4 form-control-validation">
                                <label class="form-label" for="brandImageUrl">Image URL</label>
                                <input type="url" class="form-control" id="brandImageUrl"
                                    placeholder="https://example.com/image.jpg" name="brandImageUrl" 
                                    aria-label="Brand Image URL" value="{{ old('brandImageUrl', $brand->image ?? '') }}" 
                                    autocomplete="off" />
                                <div class="form-text">Enter a full image URL or upload an image file below (at least one is required if no existing image)</div>
                                @error('brandImageUrl')
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Or Upload Image</label>
                            </div>
                            <div class="form-control-validation">
                                <input type="file" name="brandImage" id="brandImage" hidden>
                                <div class="dropzone needsclick p-0" id="dropzone-basic">
                                    <div class="dz-message needsclick">
                                        <p class="h4 needsclick pt-3 mb-2">Drag and drop your image here</p>
                                        <p class="h6 text-body-secondary d-block fw-normal mb-2">or</p>
                                        <span class="needsclick btn btn-sm btn-label-primary" id="btnBrowse">Browse
                                            image</span>
                                    </div>
                                </div>
                            </div>
                            @error('brandImage')
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
                            <h5 class="card-title mb-0">Label</h5>
                        </div>
                        <div class="card-body">
                            
                            <!-- Status -->
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="mb-6 col ecommerce-select2-dropdown form-control-validation">
                                    <label class="form-label mb-5" for="brandStatus">
                                        <span>Status <span class="text-danger">*</span></span>
                                    </label>
                                    <select class="form-select select2" name="brandStatus" id="brandStatus">
                                        <option value="1" @selected($brand->is_active == '1')>Active</option> 
                                        <option value="0" @selected($brand->is_active == '0')>Inactive</option>
                                    </select>                                   
                                </div>
                            </div>
                            <!-- Category -->
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="mb-6 col ecommerce-select2-dropdown form-control-validation">
                                    <label class="form-label mb-5" for="category-org">
                                        <span>Category <span class="text-danger">*</span></span>
                                    </label>
                                    <select class="form-control select2" name="categories[]" multiple>
                                    @foreach ($categories as $category)
                                            @include('_partials.edit_category_option', ['category' => $category,'prefix' => ''])
                                        @endforeach
                                    </select>

                                    @error('categories')
                                        <span class="text-danger" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <!-- Tags -->
                            <div>
                                <label for="brand-tags" class="form-label mb-1">Tags</label>
                                <input id="brand-tags" class="form-control" name="brandTags"
                                    aria-label="Brand Tags" value="{{ $brandTags }}" />
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