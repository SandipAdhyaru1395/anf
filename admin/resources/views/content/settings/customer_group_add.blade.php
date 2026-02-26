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

        /* Column-per-level: each level gets its own column */
        #categories_tree .category-level-cell {
            vertical-align: middle;
            border-right: 1px solid var(--bs-table-border-color);
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
        @include('content/settings/sidebar') <!-- Keep sidebar as is -->

        <!-- Main Content -->
        <div class="col-12 col-lg-12 pt-6 pt-lg-0">
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
                                    <table class="table table-bordered align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                @for ($level = 1; $level <= $maxDepth; $level++)
                                                    <th class="category-level-cell">
                                                        {{ $level === 1 ? 'Category' : ($level === 2 ? 'Sub Category / Brands' : 'Sub Category ' . $level) }}
                                                    </th>
                                                @endfor
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if ($maxDepth >= 2 && $groupedByRoot->isNotEmpty())
                                            @foreach ($groupedByRoot as $group)
                                                @php
                                                    $root = $group['root'];
                                                    $paths = $group['paths'];
                                                    $pathCount = $paths->count();
                                                    $hasChildren = $paths->contains(fn ($p) => count($p['path']) > 1);
                                                    $isLeafGroup = !$hasChildren;
                                                    $leafBrands = $isLeafGroup ? $paths->first()['brands'] : collect();
                                                    $hasBrands = $leafBrands->isNotEmpty();
                                                    $groupRowId = 'g' . $loop->iteration;
                                                @endphp
                                                @foreach ($paths as $pathRow)
                                                    @php
                                                        $path = $pathRow['path'];
                                                        $pathLen = count($path);
                                                        $isLeafRow = $pathLen === 1;
                                                        $brands = $isLeafRow ? $pathRow['brands'] : collect();
                                                        $rowId = $groupRowId . '_p' . $loop->iteration;
                                                    @endphp
                                                    <tr data-row-id="{{ $rowId }}" data-group-id="{{ $groupRowId }}" data-leaf-row="{{ $isLeafRow ? '1' : '0' }}">
                                                        @if ($loop->first)
                                                            <td rowspan="{{ $pathCount }}" class="category-level-cell" style="vertical-align: top;">
                                                                <div class="form-check">
                                                                    @if ($hasChildren)
                                                                        <input class="form-check-input category-level-checkbox" type="checkbox"
                                                                            name="categories[]" value="{{ $root->id }}"
                                                                            id="cat_{{ $groupRowId }}_root" data-group-id="{{ $groupRowId }}" data-level="0">
                                                                    @elseif($hasBrands)
                                                                        <input class="form-check-input category-level-checkbox" type="checkbox"
                                                                            id="cat_{{ $groupRowId }}_root" data-has-brands-only="1" data-group-id="{{ $groupRowId }}" data-level="0">
                                                                    @else
                                                                        <input class="form-check-input category-level-checkbox" type="checkbox"
                                                                            name="categories[]" value="{{ $root->id }}"
                                                                            id="cat_{{ $groupRowId }}_root" data-group-id="{{ $groupRowId }}" data-level="0">
                                                                    @endif
                                                                    <label class="form-check-label text-body fw-semibold" for="cat_{{ $groupRowId }}_root">{{ $root->name }}</label>
                                                                </div>
                                                            </td>
                                                        @endif
                                                        @for ($level = 1; $level < $maxDepth; $level++)
                                                            @php
                                                                $prevPath = $loop->index > 0 ? $paths[$loop->index - 1]['path'] : null;
                                                                $hasCatAtLevel = isset($path[$level]);
                                                                $sameAsPrev = $prevPath && $hasCatAtLevel && isset($prevPath[$level]) && $prevPath[$level]->id === $path[$level]->id;
                                                                $outputCell = !$sameAsPrev;
                                                                $rowspan = 1;
                                                                if ($outputCell && $hasCatAtLevel && !$isLeafRow) {
                                                                    for ($i = $loop->index + 1; $i < $paths->count(); $i++) {
                                                                        $p = $paths[$i]['path'] ?? [];
                                                                        if (isset($p[$level]) && $p[$level]->id === $path[$level]->id) { $rowspan++; } else { break; }
                                                                    }
                                                                }
                                                            @endphp
                                                            @if ($outputCell)
                                                                <td @if ($hasCatAtLevel && !$isLeafRow && $rowspan > 1) rowspan="{{ $rowspan }}" @endif class="category-level-cell" @if ($rowspan > 1) style="vertical-align: top;" @endif>
                                                                    @if ($isLeafRow)
                                                                        @if ($level === 1)
                                                                            @if ($brands->isNotEmpty())
                                                                                <div class="child-categories-vertical">
                                                                                    @foreach ($brands as $brand)
                                                                                        <div class="form-check mb-1">
                                                                                            <input class="form-check-input brand-checkbox" type="checkbox" name="brands[]" value="{{ $brand->id }}"
                                                                                                id="brand_{{ $rowId }}_{{ $brand->id }}">
                                                                                            <label class="form-check-label" for="brand_{{ $rowId }}_{{ $brand->id }}">{{ $brand->name }}</label>
                                                                                        </div>
                                                                                    @endforeach
                                                                                </div>
                                                                            @else
                                                                                <span class="text-muted">No sub items available</span>
                                                                            @endif
                                                                        @endif
                                                                    @else
                                                                        @if ($hasCatAtLevel)
                                                                            @php
                                                                                $cat = $path[$level];
                                                                                $ancestorIds = collect($path)->take($level)->pluck('id')->implode(',');
                                                                            @endphp
                                                                            <div class="form-check">
                                                                                <input class="form-check-input category-level-checkbox" type="checkbox"
                                                                                    name="categories[]" value="{{ $cat->id }}"
                                                                                    id="cat_{{ $groupRowId }}_l{{ $level }}_{{ $cat->id }}"
                                                                                    data-group-id="{{ $groupRowId }}" data-level="{{ $level }}" data-ancestor-ids="{{ $ancestorIds }}">
                                                                                <label class="form-check-label text-body" for="cat_{{ $groupRowId }}_l{{ $level }}_{{ $cat->id }}">{{ $cat->name }}</label>
                                                                            </div>
                                                                        @endif
                                                                    @endif
                                                                </td>
                                                            @endif
                                                        @endfor
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="{{ $maxDepth }}" class="text-muted">No categories available.</td>
                                                </tr>
                                            @endif
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
