@extends('layouts/layoutMaster')

@section('title', 'Edit Customer Group')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')
    <style>
        #categories_tree table td {
            vertical-align: middle;
        }

        .form-check {
            margin-bottom: 0.25rem;
        }

        .child-category+label {
            cursor: pointer;
        }

        .child-categories-vertical .form-check {
            display: block;
            margin-bottom: 0.25rem;
        }
    </style>
    @vite('resources/assets/js/settings-customerGroup.js')
@endsection

@section('content')
    <div class="row g-6">
        @include('content/settings/sidebar')

        <div class="col-12 col-lg-9 pt-6 pt-lg-0">
            <div class="tab-content p-0">
                <div class="tab-pane fade show active" id="general" role="tabpanel">
                    <div class="card mb-6">
                        <div class="card-body">
                            <h5 class="card-title">Edit Customer Group</h5>
                            <form action="{{ route('settings.customerGroup.update', $customerGroup->id) }}" method="POST"
                                id="editCustomerGroupForm">
                                @csrf

                                <input type="hidden" name="id" value="{{ $customerGroup->id }}">

                                <!-- Customer Group Name -->
                                <div class="mb-3 form-control-validation">
                                    <label for="name" class="form-label">Customer Group Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        value="{{ old('name', $customerGroup->name) }}" autocomplete="off">
                                </div>

                                <!-- Restrict Categories -->
                                <div class="mb-3 form-control-validation">
                                    <label class="form-label">Restrict Categories? <span class="text-danger">*</span></label>
                                    <select class="form-select" id="restrict_categories" name="restrict_categories">
                                        <option value="0"
                                            {{ $customerGroup->restrict_categories == 0 ? 'selected' : '' }}>No</option>
                                        <option value="1"
                                            {{ $customerGroup->restrict_categories == 1 ? 'selected' : '' }}>Yes</option>
                                    </select>
                                </div>

                                <!-- Categories Table -->
                                <div class="mb-3" id="categories_tree"
                                    style="{{ $customerGroup->restrict_categories ? 'display:block;' : 'display:none;' }}">
                                    <label class="form-label">Select Categories</label>
                                    <table class="table table-bordered table-striped align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width:40%;">Parent Category</th>
                                                <th>Child Categories / Brands</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                            @php
                                                $selectedCategoryIds = $customerGroup->categories
                                                    ->pluck('id')
                                                    ->toArray();
                                                $selectedBrandIds = $customerGroup->brands->pluck('id')->toArray();
                                            @endphp

                                            @foreach ($categories as $category)
                                                <tr>

                                                    {{-- ================= Parent Category ================= --}}
                                                    <td>
                                                        @php
                                                            $childIds = $category->children->pluck('id')->toArray();
                                                            $brandIds = $category->brands->pluck('id')->toArray();

                                                            // Parent checked if:
                                                            // 1) Any child category selected
                                                            // 2) Any brand selected
                                                            $isParentChecked =
                                                                count(
                                                                    array_intersect($childIds, $selectedCategoryIds),
                                                                ) > 0 ||
                                                                count(array_intersect($brandIds, $selectedBrandIds)) >
                                                                    0;
                                                        @endphp

                                                        <div class="form-check">
                                                            <input class="form-check-input parent-category" type="checkbox"
                                                                id="parent_{{ $category->id }}"
                                                                {{ $isParentChecked ? 'checked' : '' }}>

                                                            <label class="form-check-label fw-semibold"
                                                                for="parent_{{ $category->id }}">
                                                                {{ $category->name }}
                                                            </label>
                                                        </div>
                                                    </td>


                                                    {{-- ================= Child / Brand Section ================= --}}
                                                    <td>

                                                        {{-- If child categories exist --}}
                                                        @if ($category->children->count())
                                                            <div class="child-categories-vertical">
                                                                @foreach ($category->children as $child)
                                                                    <div class="form-check mb-1">
                                                                        <input class="form-check-input child-category"
                                                                            type="checkbox" name="categories[]"
                                                                            value="{{ $child->id }}"
                                                                            id="category_{{ $child->id }}"
                                                                            {{ in_array($child->id, $selectedCategoryIds) ? 'checked' : '' }}>

                                                                        <label class="form-check-label"
                                                                            for="category_{{ $child->id }}">
                                                                            {{ $child->name }}
                                                                        </label>
                                                                    </div>
                                                                @endforeach
                                                            </div>

                                                            {{-- If no child → show brands --}}
                                                        @elseif($category->brands->count())
                                                            <div class="brands-vertical">
                                                                @foreach ($category->brands as $brand)
                                                                    <div class="form-check mb-1">
                                                                        <input class="form-check-input brand-checkbox"
                                                                            type="checkbox" name="brands[]"
                                                                            value="{{ $brand->id }}"
                                                                            id="brand_{{ $brand->id }}"
                                                                            {{ in_array($brand->id, $selectedBrandIds) ? 'checked' : '' }}>

                                                                        <label class="form-check-label"
                                                                            for="brand_{{ $brand->id }}">
                                                                            {{ $brand->name }}
                                                                        </label>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <span class="text-muted">No child categories or brands</span>
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
    </div>
@endsection
