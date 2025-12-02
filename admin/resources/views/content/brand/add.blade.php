@extends('layouts/layoutMaster')

@section('title', 'Add Brand')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/quill/typography.scss', 'resources/assets/vendor/libs/quill/katex.scss', 'resources/assets/vendor/libs/quill/editor.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/dropzone/dropzone.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/tagify/tagify.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/dropzone/dropzone.js','resources/assets/vendor/libs/tagify/tagify.js',
        'resources/assets/vendor/libs/select2/select2.js','resources/assets/vendor/libs/@form-validation/popular.js','resources/assets/vendor/libs/@form-validation/bootstrap5.js','resources/assets/vendor/libs/@form-validation/auto-focus.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/brand-add.js'])
@endsection

@section('content')
    <div class="app-ecommerce">
        <!-- Add Brand -->
        <form id="addBrandForm" method="POST" action="{{ route('brand.create') }}" enctype="multipart/form-data">
            @csrf

            <div
                style="background: var(--bs-body-bg);" class="py-5 px-2 card-header sticky-element d-flex justify-content-sm-between align-items-sm-center flex-column flex-sm-row">
                <div class="d-flex flex-column justify-content-center">
                    <h4 class="mb-1">Add a new Brand</h4>
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
                                    value="{{ old('brandTitle') }}" autocomplete="off" />
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
                            <div class="mb-4 form-control-validation">
                                <label class="form-label" for="brandImageUrl">Image URL <span class="text-danger">*</span></label>
                                <input type="url" class="form-control" id="brandImageUrl"
                                    placeholder="https://example.com/image.jpg" name="brandImageUrl" 
                                    aria-label="Brand Image URL" value="{{ old('brandImageUrl') }}" 
                                    autocomplete="off" />
                                <div class="form-text">Enter a full image URL or upload an image file below (at least one is required)</div>
                                @error('brandImageUrl')
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Or Upload Image <span class="text-danger">*</span></label>
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
                                        <option value="1" @selected(old('brandStatus') == '1')>Active</option> 
                                        <option value="0" @selected(old('brandStatus') == '0')>Inactive</option>
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
                                            @include('_partials.category_option', ['category' => $category,'prefix' => ''])
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
                                    aria-label="Brand Tags" value="" />
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