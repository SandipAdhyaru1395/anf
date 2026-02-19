<!-- Edit Shipping Address Modal -->
<div class="modal fade" id="editCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-simple modal-add-new-address">
        <div class="modal-content">
            <div class="modal-body">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="text-center mb-6">
                    <h4 class="address-title mb-2">Edit Customer</h4>
                </div>
                <form method="post" action="{{ route('customer.update') }}" class="customer-add pt-0"
                    id="editCustomerForm">
                    @csrf
                    <input type="hidden" name="id" value="{{ $customer->id }}">
                    <div class="row mb-5">
                        <div class="col-md-6">
                            <div class="mb-6 form-control-validation">
                                <label class="form-label" for="company-name">Company Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" autocomplete="off" id="company-name" class="form-control"
                                    placeholder="Enter company name" aria-label="Enter company name" name="companyName"
                                    value="{{ old('companyName') ?? $customer->company_name }}" />
                                @error('companyName', 'editCustomer')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-6 form-control-validation">
                                <label class="form-label" for="email">Email <span
                                        class="text-danger">*</span></label>
                                <input type="text" autocomplete="off" id="email" class="form-control"
                                    placeholder="Enter email" aria-label="Enter email" name="email"
                                    value="{{ old('email') ?? $customer->email }}" />
                                @error('email', 'editCustomer')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-6 form-control-validation">
                                <label class="form-label" for="mobile">Mobile <span
                                        class="text-danger">*</span></label>
                                <input type="text" maxlength="10" autocomplete="off"
                                    onkeypress="return /[0-9]/i.test(event.key)" id="mobile" class="form-control"
                                    placeholder="Enter mobile no" aria-label="Enter mobile no" name="mobile"
                                    value="{{ old('mobile') ?? $customer->phone }}" />
                                @error('mobile', 'editCustomer')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-6 form-control-validation form-password-toggle">
                                <label class="form-label" for="password">Password </label>
                                <div class="input-group input-group-merge">
                                    <input type="password" autocomplete="off" class="form-control" id="password"
                                        placeholder="Enter password" name="password" aria-label="Enter password" />
                                    <span class="input-group-text cursor-pointer"><i
                                            class="icon-base ti tabler-eye-off"></i></span>
                                </div>
                                @error('password', 'editCustomer')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-6 form-control-validation form-password-toggle">
                                <label class="form-label" for="confirmPassword">Confirm Password</label>
                                <div class="input-group input-group-merge">
                                    <input type="password" autocomplete="off" id="confirmPassword" class="form-control"
                                        placeholder="Enter confirm password" aria-label="Enter confirm password"
                                        name="confirmPassword" />
                                    <span class="input-group-text cursor-pointer"><i
                                            class="icon-base ti tabler-eye-off"></i></span>
                                </div>
                            </div>
                            <div class="mb-6 form-control-validation">
                                <label class="form-label" for="status">Status <span
                                        class="text-danger">*</span></label>
                                <select class="form-select select2" id="status" name="status">
                                    <option value="active" @selected(old('status') == 'active') @selected(old('status') == '') @selected($customer->is_active == '1')>
                                        Active</option>
                                    <option value="inactive" @selected(old('status') == 'inactive') @selected($customer->is_active == '0')>Inactive</option>
                                </select>
                                @error('status', 'editCustomer')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-6">
                                <label class="form-label" for="customer_group_id">Customer Group</label>
                                <select class="form-select select2" id="customer_group_id" name="customer_group_id">
                                    @if ($customer_groups?->isNotEmpty())
                                        <option value="" selected>Select customer group</option>
                                    @endif
                                    @forelse($customer_groups as $customerGroup)
                                        <option value="{{ $customerGroup->id }}" @selected(old('customer_group_id') == $customerGroup->id) @selected($customer->customer_group_id == $customerGroup->id)>{{ $customerGroup->name }}</option>
                                    @empty
                                        <option value="">No customer group found</option>
                                    @endforelse
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 border-start">
                            <div class="mb-6 form-control-validation">
                                <label class="form-label" for="address-line1">Address Line 1 <span
                                        class="text-danger">*</span></label>
                                <input type="text" autocomplete="off" id="address-line1" class="form-control"
                                    placeholder="Enter address" aria-label="Enter address" name="addressLine1"
                                    value="{{ old('addressLine1') ?? $customer->company_address_line1 }}" />
                                @error('addressLine1', 'editCustomer')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-6">
                                <label class="form-label" for="address-line2">Address Line 2</label>
                                <input type="text" autocomplete="off" id="address-line2" class="form-control"
                                    placeholder="Enter address" aria-label="Enter address" name="addressLine2"
                                    value="{{ old('addressLine2') ?? $customer->company_address_line2 }}" />
                            </div>
                            <div class="mb-6 form-control-validation">
                                <label class="form-label" for="city">City <span
                                        class="text-danger">*</span></label>
                                <input type="text" autocomplete="off" id="city" class="form-control"
                                    placeholder="Enter city" aria-label="Enter city" name="city"
                                    value="{{ old('city') ?? $customer->company_city }}" />
                                @error('city', 'editCustomer')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-6">
                                <label class="form-label" for="country">Country</label>
                                <input type="text" autocomplete="off" id="country" class="form-control"
                                    placeholder="Enter country" aria-label="Enter country" name="country"
                                    value="{{ old('country') ?? $customer->company_country }}" />
                            </div>
                            <div class="mb-6 form-control-validation">
                                <label class="form-label" for="zip_code">Postcode <span
                                        class="text-danger">*</span></label>
                                <input type="text" autocomplete="off" id="zip_code" class="form-control"
                                    placeholder="Enter postcode" aria-label="Enter postcode" name="zip_code"
                                    value="{{ old('zip_code') ?? $customer->company_zip_code }}" />
                                @error('zip_code', 'editCustomer')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-6">
                                <label class="form-label" for="rep_id">Sales Person</label>
                                <select id="rep_id" name="rep_id" class="form-control select2">
                                    @if($sales_persons->isNotEmpty())
                                        <option value="">Select sales person</option>
                                        @foreach ($sales_persons as $sales_person)
                                            <option value="{{ $sales_person->id }}" 
                                            @selected( $sales_person->id == $customer->rep_id)    
                                            >
                                                {{ $sales_person->name }} (
                                                {{ $sales_person->email }} )
                                            </option>
                                        @endforeach
                                    @else
                                        <option value="">No sales person found</option>
                                    @endforelse
                                </select>
                                @error('rep_id', 'editCustomer')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col text-center">
                            <button type="submit" class="btn btn-primary me-sm-4 data-submit">Save</button>
                            <button type="reset" class="btn btn-label-danger"
                                data-bs-dismiss="modal">Discard</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--/ Add New Address Modal -->
