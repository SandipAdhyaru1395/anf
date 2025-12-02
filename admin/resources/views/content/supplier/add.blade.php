@extends('layouts/layoutMaster')

@section('title', 'Supplier Add')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/supplier-add.js'])
@endsection

@section('content')
    <div class="app-ecommerce">
        <!-- Add Supplier -->
        <form id="addSupplierForm" method="POST" action="{{ route('supplier.create') }}">
            @csrf

            <div style="background: var(--bs-body-bg);"
                class="py-5 px-2 card-header sticky-element d-flex justify-content-sm-between align-items-sm-center flex-column flex-sm-row">
                <div class="d-flex flex-column justify-content-center">
                    <h4 class="mb-1">Add a new Supplier</h4>
                    <p class="mb-0">Manage your supplier information</p>
                </div>
                <div class="d-flex align-content-center flex-wrap gap-4">
                    <div class="d-flex gap-4">
                        <a href="{{ route('supplier.list') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- First column-->
                <div class="col-12">
                    <!-- Supplier Information -->
                    <div class="card mb-6">
                        <div class="card-header">
                            <h5 class="card-tile mb-0">Supplier information</h5>
                        </div>
                        <div class="card-body">
                            
                            <div class="row">
                            <div class="col-12 col-md-6 col-lg-4 mb-2 form-control-validation">
                                <label class="form-label" for="company">Company <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="company" placeholder="Company name" name="company" aria-label="Company name" value="{{ old('company') }}" autocomplete="off" />
                                @error('company')
                                    <span class="text-danger" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                            <div class="col-12 col-md-6 col-lg-4 mb-2 form-control-validation">
                                <label class="form-label" for="full_name">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="full_name" placeholder="Full Name" name="full_name" aria-label="Full Name" value="{{ old('full_name') }}" autocomplete="off" />
                                @error('full_name')
                                    <span class="text-danger" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                            <div class="col-12 col-md-6 col-lg-4 mb-2 form-control-validation">
                                <label class="form-label" for="email">Email Address</label>
                                <input type="email" class="form-control" id="email"
                                    placeholder="Email Address" name="email" aria-label="Email Address"
                                    value="{{ old('email') }}" autocomplete="off" />
                                @error('email')
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            

                            <div class="col-12 col-md-6 col-lg-4 mb-2 form-control-validation">
                                <label class="form-label" for="phone">Phone</label>
                                <input type="text" class="form-control" id="phone"
                                    placeholder="Phone" name="phone" aria-label="Phone"
                                    onkeypress="return /^[0-9]+$/.test(event.key)"
                                    value="{{ old('phone') }}" autocomplete="off" maxlength="10" />
                                @error('phone')
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-12 col-md-6 col-lg-4 mb-2 form-control-validation">
                                <label class="form-label" for="vat_number">VAT Number</label>
                                <input type="text" class="form-control" id="vat_number" placeholder="VAT Number" name="vat_number" aria-label="VAT Number" value="{{ old('vat_number') }}" autocomplete="off" />
                                @error('vat_number')
                                    <span class="text-danger" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                            <div class="col-12 col-md-6 col-lg-4 mb-2 form-control-validation">
                                <label class="form-label mb-2" for="is_active">
                                    <span>Status <span class="text-danger">*</span></span>
                                </label>
                                <select class="form-select" name="is_active" id="is_active">
                                    <option value="1" @selected(old('is_active','1')=='1')>Active</option>
                                    <option value="0" @selected(old('is_active')=='0')>Inactive</option>
                                </select>
                            </div>
                            <div class="mb-2 form-control-validation">
                                <label class="form-label" for="address">Address</label>
                                <input type="text" class="form-control" id="address" name="address"
                                    placeholder="Address" aria-label="Address" value="{{ old('address') }}" autocomplete="off" />
                                @error('address')
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6 col-lg-4 mb-2 form-control-validation">
                                <label class="form-label" for="city">City</label>
                                <input type="text" class="form-control" id="city"
                                    placeholder="City" name="city" aria-label="City"
                                    value="{{ old('city') }}" autocomplete="off" />
                                @error('city')
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-12 col-md-6 col-lg-4 mb-2 form-control-validation">
                                <label class="form-label" for="state">State</label>
                                <input type="text" class="form-control" id="state"
                                    placeholder="State" name="state" aria-label="State"
                                    value="{{ old('state') }}" autocomplete="off" />
                                @error('state')
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-12 col-md-6 col-lg-4 mb-2 form-control-validation">
                                <label class="form-label" for="zip_code">Postcode</label>
                                <input type="text" class="form-control" id="zip_code"
                                    placeholder="Postcode" name="zip_code" aria-label="Postcode"
                                    value="{{ old('zip_code') }}" autocomplete="off" />
                                @error('zip_code')
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6 col-lg-4 mb-2 form-control-validation">
                                <label class="form-label" for="country">Country</label>
                                <input type="text" class="form-control" id="country"
                                    placeholder="Country" name="country" aria-label="Country"
                                    value="{{ old('country') }}" autocomplete="off" />
                                @error('country')
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            </div>
                        </div>
                    </div>
                    <!-- /Supplier Information -->
                </div>
            </div>
        </form>
    </div>

@endsection

