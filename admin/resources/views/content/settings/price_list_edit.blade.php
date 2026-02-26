@extends('layouts/layoutMaster')

@section('title', 'Edit Price List')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')
    @vite('resources/assets/js/settings-priceList.js')
@endsection

@section('content')
    <div class="row g-6">

        @include('content/settings/sidebar')

        <div class="col-12 col-lg-12 pt-6 pt-lg-0">
            <div class="tab-content p-0">
                <div class="tab-pane fade show active">

                    <div class="card mb-6">
                        <div class="card-body">

                            <h5 class="card-title">Edit Price List</h5>

                            <form action="{{ route('settings.priceList.update') }}" method="POST" id="editPriceListForm">
                                @csrf
                                <input type="hidden" id="id" name="id" value="{{ $priceList->id }}">
                                <div class="row g-4">
                                    {{-- Price List Name --}}
                                    <div class="col-md-6 mb-3 form-control-validation">
                                        <label class="form-label">
                                            Price List Name <span class="text-danger">*</span>
                                        </label>

                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                            name="name" value="{{ old('name', $priceList->name) }}" autocomplete="off">

                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row g-4">
                                    {{-- Conversion Rate --}}
                                    <div class="col-md-6 mb-4 form-control-validation">
                                        <label class="form-label">
                                            Conversion Rate (%) <span class="text-danger">*</span><br>
                                            <small class="form-text">
                                                Set individual prices on each item, or use the conversion rate to automatically calculate prices
                                            </small>
                                        </label>
                                        <input type="text" onkeypress="return /^[0-9.]+$/.test(event.key)"
                                            class="form-control @error('conversion_rate') is-invalid @enderror"
                                            name="conversion_rate" value="{{ old('conversion_rate', $priceList->conversion_rate) }}"
                                            autocomplete="off">

                                        @error('conversion_rate')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Price List Type --}}
                                <div class="mb-4 form-control-validation">
                                    <label class="form-label mb-2">
                                        Price List Type <span class="text-danger">*</span>
                                    </label>

                                    <!-- Wholesale Prices -->
                                    <div class="form-check">
                                        <input class="form-check-input @error('price_list_type') is-invalid @enderror"
                                            type="radio" name="price_list_type" id="type_wholesale" value="0"
                                            {{ old('price_list_type', $priceList->price_list_type) == 0 ? 'checked' : '' }}>

                                        <label class="form-check-label" for="type_wholesale">
                                            Wholesale Prices<br>
                                            <small class="form-text">
                                                Set custom wholesale prices and use the account-wide retail prices (RRP)
                                            </small>
                                        </label>
                                    </div>

                                    <!-- Wholesale and Retail Prices -->
                                    <div class="form-check mt-2">
                                        <input class="form-check-input @error('price_list_type') is-invalid @enderror"
                                            type="radio" name="price_list_type" id="type_wholesale_retail" value="1"
                                            {{ old('price_list_type',$priceList->price_list_type) == 1 ? 'checked' : '' }}>

                                        <label class="form-check-label" for="type_wholesale_retail">
                                            Wholesale and Retail Prices<br>
                                            <small class="form-text">
                                                Set custom wholesale prices and retail prices
                                            </small>
                                        </label>
                                    </div>

                                    @error('price_list_type')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>




                                <div class="d-flex justify-content-end gap-4">
                                    <button type="reset" class="btn btn-label-secondary">
                                        Discard
                                    </button>

                                    <button type="submit" class="btn btn-primary">
                                        Save Changes
                                    </button>
                                </div>

                            </form>

                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
@endsection
