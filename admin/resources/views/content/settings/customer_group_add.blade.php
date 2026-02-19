@extends('layouts/layoutMaster')

@section('title', 'Add Customer Group')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')
    <style>
        /* Modern table styling */
        #categories_tree table td {
            vertical-align: middle;
        }

        .form-check {
            margin-bottom: 0.25rem;
        }

        .child-category+label {
            cursor: pointer;
        }

        /* Make child categories vertical */
        .child-categories-vertical .form-check {
            display: block;
            margin-bottom: 0.25rem;
        }
    </style>
    @vite('resources/assets/js/settings-customerGroup.js')
@endsection

@section('content')
    <div class="row g-6">
        @include('content/settings/sidebar') <!-- Keep sidebar as is -->

        <!-- Main Content -->
        <div class="col-12 col-lg-9 pt-6 pt-lg-0">
            <div class="tab-content p-0">
                <div class="tab-pane fade show active" id="general" role="tabpanel">
                    <div class="card mb-6">
                        <div class="card-body">
                            <h5 class="card-title">Add Customer Group</h5>
                            <form action="{{ route('settings.customerGroup.store') }}" method="POST"
                                id="addCustomerGroupForm">
                                @csrf
                                <!-- Customer Group Name -->
                                <div class="mb-3 form-control-validation">
                                    <label for="name" class="form-label">Customer Group Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        autocomplete="off">
                                </div>

                                <!-- Restrict Categories -->
                                <div class="mb-3 form-control-validation">
                                    <label class="form-label">Restrict Categories? <span class="text-danger">*</span></label>
                                    <select class="form-select" id="restrict_categories" name="restrict_categories">
                                        <option value="0">No</option>
                                        <option value="1">Yes</option>
                                    </select>
                                </div>

                                <!-- Categories Table (shown only if 'Yes') -->
                                <div class="mb-3" id="categories_tree" style="display:none;">
                                    <label class="form-label">Select Categories</label>
                                    <table class="table table-bordered table-striped align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width:40%;">Parent Category</th>
                                                <th>Child Categories / Brands</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($categories as $category)
                                                <tr>
                                                    <!-- Parent Category -->
                                                    <td>
                                                        <div class="form-check">
                                                            <input class="form-check-input parent-category" type="checkbox"
                                                                value="{{ $category->id }}"
                                                                id="category_{{ $category->id }}">
                                                            <label class="form-check-label fw-semibold"
                                                                for="category_{{ $category->id }}">
                                                                {{ $category->name }}
                                                            </label>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @if ($category->children->count())
                                                            {{-- Show Child Categories --}}
                                                            <div class="child-categories-vertical">
                                                                @foreach ($category->children as $child)
                                                                    <div class="form-check mb-1">
                                                                        <input class="form-check-input child-category"
                                                                            type="checkbox" name="categories[]"
                                                                            value="{{ $child->id }}"
                                                                            id="category_{{ $child->id }}">
                                                                        <label class="form-check-label"
                                                                            for="category_{{ $child->id }}">
                                                                            {{ $child->name }}
                                                                        </label>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @elseif($category->brands->count())
                                                            {{-- Show Brands if No Child Categories --}}
                                                            <div class="child-categories-vertical">
                                                                @foreach ($category->brands as $brand)
                                                                    <div class="form-check mb-1">
                                                                        <input class="form-check-input child-category"
                                                                            type="checkbox" name="brands[]"
                                                                            value="{{ $brand->id }}"
                                                                            id="brand_{{ $brand->id }}">
                                                                        <label class="form-check-label"
                                                                            for="brand_{{ $brand->id }}">
                                                                            {{ $brand->name }}
                                                                        </label>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <span class="text-muted">No sub items available</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="d-flex justify-content-end gap-4">
                                    <button type="reset" class="btn btn-label-secondary">Discard</button>
                                    <button class="btn btn-primary" type="submit">Save Changes</button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Main Content -->
    </div>
@endsection
