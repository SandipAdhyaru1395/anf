@extends('layouts/layoutMaster')

@section('title', 'Price Lists')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')
    @vite('resources/assets/js/settings-priceList.js')
@endsection

@section('content')
    <div class="row g-6">
        @include('content/settings/sidebar')

        <!-- Options -->
        <div class="col-12 col-lg-12 pt-6 pt-lg-0">
            <div class="tab-content p-0">
                <!-- Store Details Tab -->
                <div class="tab-pane fade show active" id="general" role="tabpanel">
                    <div id="priceListForm">
                        <div class="card mb-6">
                            <div class="card-body">
                                <h5 class="card-title">Price Lists</h5>
                                <div class="row text-end mb-2">
                                    <div class="col">
                                        <a class="btn btn-primary" href="{{ route('settings.priceList.add') }}">Add</a>
                                    </div>
                                </div>
                                <div class="card-datatable">
                                    <table class="datatables-priceList table">
                                        <thead class="border-top">
                                            <tr>
                                                <th style="display:none;">ID</th>
                                                <th>Name</th>
                                                <th>Price List Type</th>
                                                <th>Customers</th>
                                                <th class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                    </table>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Options-->
    </div>

@endsection