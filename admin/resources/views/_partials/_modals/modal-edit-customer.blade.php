<!-- Edit Shipping Address Modal -->
<div class="modal fade" id="editCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-simple modal-add-new-address">
        <div class="modal-content">
            <div class="modal-body">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <form method="post" action="{{ route('customer.update') }}" class="customer-add pt-0"
                    id="editCustomerForm">
                    @csrf
                    <input type="hidden" name="id" value="{{ $customer->id }}">
                    <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-6">
                        <h4 class="address-title mb-0">Edit Customer</h4>
                        <div class="d-flex align-items-center gap-2 ms-auto">
                            <label class="form-label mb-0 text-nowrap" for="pay_later_edit">Pay later</label>
                            <div class="form-check form-switch m-0">
                                <input type="checkbox" name="pay_later" value="1" class="form-check-input"
                                    id="pay_later_edit"
                                    @checked(old('pay_later', $customer->pay_later))>
                            </div>
                        </div>
                    </div>
                    @error('pay_later', 'editCustomer')
                        <div class="text-danger small mb-4">{{ $message }}</div>
                    @enderror
                    <div class="row mb-5">
                        <div class="col-12 mb-2">
                            <h5 class="mb-2">1. Business Address</h5>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-6 form-control-validation">
                                <label class="form-label" for="company-name">Company Name <span class="text-danger">*</span></label>
                                <input type="text" autocomplete="off" id="company-name" class="form-control"
                                    placeholder="Enter company name" aria-label="Enter company name" name="companyName"
                                    value="{{ old('companyName') ?? $customer->company_name }}" />
                                @error('companyName', 'editCustomer')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-6 form-control-validation">
                                <label class="form-label" for="address-line1">Address Line 1 <span class="text-danger">*</span></label>
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
                        </div>
                        <div class="col-md-6">
                            <div class="mb-6 form-control-validation">
                                <label class="form-label" for="city">City <span class="text-danger">*</span></label>
                                <input type="text" autocomplete="off" id="city" class="form-control"
                                    placeholder="Enter city" aria-label="Enter city" name="city"
                                    value="{{ old('city') ?? $customer->company_city }}" />
                                @error('city', 'editCustomer')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-6 form-control-validation">
                                <label class="form-label" for="zip_code">Postcode <span class="text-danger">*</span></label>
                                <input type="text" autocomplete="off" id="zip_code" class="form-control"
                                    placeholder="Enter postcode" aria-label="Enter postcode" name="zip_code"
                                    value="{{ old('zip_code') ?? $customer->company_zip_code }}" />
                                @error('zip_code', 'editCustomer')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-6">
                                <label class="form-label" for="country">Country</label>
                                <input type="text" autocomplete="off" id="country" class="form-control"
                                    placeholder="Enter country" aria-label="Enter country" name="country"
                                    value="{{ old('country') ?? $customer->company_country }}" />
                            </div>
                        </div>

                        <div class="col-12 mt-2 mb-2">
                            <h5 class="mb-2">2. Company Details</h5>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-6 form-control-validation">
                                <label class="form-label" for="mobile">Mobile <span class="text-danger">*</span></label>
                                <input type="text" maxlength="40" autocomplete="off" id="mobile" class="form-control"
                                    placeholder="10–20 letters or numbers" aria-label="Enter mobile no" name="mobile"
                                    value="{{ old('mobile') ?? $customer->phone }}"
                                    onkeypress="if (event.key.length !== 1) return true; return /^[a-zA-Z0-9]$/i.test(event.key)" />
                                @error('mobile', 'editCustomer')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-6">
                                <label class="form-label" for="vatNumber">VAT Number (if applicable)</label>
                                <input type="text" autocomplete="off" id="vatNumber" class="form-control"
                                    placeholder="Enter VAT number" name="vatNumber"
                                    value="{{ old('vatNumber') ?? $customer->vat_number }}" />
                                @error('vatNumber', 'editCustomer')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-6">
                                <label class="form-label" for="eoriNumber">EORI Number (if applicable)</label>
                                <input type="text" autocomplete="off" id="eoriNumber" class="form-control"
                                    placeholder="Enter EORI number" name="eoriNumber"
                                    value="{{ old('eoriNumber') ?? $customer->eori_number }}" />
                                @error('eoriNumber', 'editCustomer')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-6 form-control-validation">
                                <label class="form-label" for="isPartOfGroup">Are you part of a group? i.e. A symbol group or Industry Body, The FED etc.</label>
                                <select class="form-select" id="isPartOfGroup" name="isPartOfGroup">
                                    <option value="yes" @selected(old('isPartOfGroup') == 'yes') @selected(is_null(old('isPartOfGroup')) && (int) $customer->is_part_of_group === 1)>Yes</option>
                                    <option value="no" @selected(old('isPartOfGroup') == 'no') @selected(is_null(old('isPartOfGroup')) && (int) $customer->is_part_of_group === 0)>No</option>
                                </select>
                                @error('isPartOfGroup', 'editCustomer')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-6 form-control-validation">
                                <label class="form-label" for="businessType">Type of business</label>
                                <select class="form-select" id="businessType" name="businessType">
                                    @foreach (['Wholesaler', 'Distributor', 'Retailer', 'Online retailer', 'Vape shop'] as $type)
                                        <option value="{{ $type }}" @selected(old('businessType') == $type) @selected(is_null(old('businessType')) && $customer->business_type === $type)>{{ $type }}</option>
                                    @endforeach
                                </select>
                                @error('businessType', 'editCustomer')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-6 form-control-validation">
                                <label class="form-label" for="averageMonthlySpendExVat">Average monthly spend excluding VAT</label>
                                <input type="text" autocomplete="off" id="averageMonthlySpendExVat"
                                    class="form-control" placeholder="Enter amount" name="averageMonthlySpendExVat"
                                    value="{{ old('averageMonthlySpendExVat') ?? $customer->average_monthly_spend_ex_vat }}" />
                                @error('averageMonthlySpendExVat', 'editCustomer')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-6 form-control-validation">
                                <label class="form-label" for="storesServicedCount">How many stores do you have/service</label>
                                <input type="number" min="0" step="1" autocomplete="off" id="storesServicedCount"
                                    class="form-control" placeholder="Enter number of stores" name="storesServicedCount"
                                    value="{{ old('storesServicedCount') ?? $customer->stores_serviced_count }}" />
                                @error('storesServicedCount', 'editCustomer')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-12 mt-2 mb-2">
                            <h5 class="mb-2">3. User Details</h5>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-6 form-control-validation">
                                <label class="form-label" for="yourName">Your name</label>
                                <input type="text" autocomplete="off" id="yourName" class="form-control"
                                    placeholder="Enter your name" name="yourName"
                                    value="{{ old('yourName') ?? $customer->contact_person_name }}" />
                                @error('yourName', 'editCustomer')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-6 form-control-validation">
                                <label class="form-label" for="email">Email <span class="text-danger">*</span></label>
                                <input type="text" autocomplete="off" id="email" class="form-control"
                                    placeholder="Enter email" aria-label="Enter email" name="email"
                                    value="{{ old('email') ?? $customer->email }}" />
                                @error('email', 'editCustomer')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-6 form-control-validation form-password-toggle">
                                <label class="form-label" for="password">Password </label>
                                <div class="input-group input-group-merge">
                                    <input type="password" autocomplete="off" class="form-control" id="password"
                                        placeholder="Enter password" name="password" aria-label="Enter password" />
                                    <span class="input-group-text cursor-pointer"><i class="icon-base ti tabler-eye-off"></i></span>
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
                                    <span class="input-group-text cursor-pointer"><i class="icon-base ti tabler-eye-off"></i></span>
                                </div>
                            </div>
                            <div class="mb-6">
                                <label class="form-label" for="price_list_id">Price List</label>
                                <select class="form-select select2" id="price_list_id" name="price_list_id">
                                    @if ($price_lists->isNotEmpty())
                                        <option value="" selected>Select price list</option>
                                    @endif
                                    @forelse($price_lists as $price_list)
                                        <option value="{{ $price_list->id }}" @selected(old('price_list_id') == $price_list->id) @selected($customer->price_list_id == $price_list->id)>{{ $price_list->name }}</option>
                                    @empty
                                        <option value="">No price list found</option>
                                    @endforelse
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-6 form-control-validation">
                                <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
                                <select class="form-select select2" id="status" name="status">
                                    <option value="active" @selected(old('status') == 'active') @selected(old('status') == '') @selected($customer->is_active == '1')>Active</option>
                                    <option value="inactive" @selected(old('status') == 'inactive') @selected($customer->is_active == '0')>Inactive</option>
                                </select>
                                @error('status', 'editCustomer')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-6">
                                <label class="form-label" for="rep_id">Sales Person</label>
                                <select id="rep_id" name="rep_id" class="form-control select2">
                                    @if($sales_persons->isNotEmpty())
                                        <option value="">Select sales person</option>
                                        @foreach ($sales_persons as $sales_person)
                                            <option value="{{ $sales_person->id }}" @selected($sales_person->id == $customer->rep_id)>
                                                {{ $sales_person->name }} ({{ $sales_person->email }} )
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
                            <div class="mb-6">
                                <label class="form-label" for="repCode">Rep code</label>
                                <input type="text" autocomplete="off" id="repCode" class="form-control"
                                    placeholder="Optional" name="repCode"
                                    value="{{ old('repCode') ?? $customer->rep_code }}" maxlength="100" />
                                @error('repCode', 'editCustomer')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-6">
                                <label class="form-label" for="salesRepName">Sales rep name (if known)</label>
                                <input type="text" autocomplete="off" id="salesRepName" class="form-control"
                                    placeholder="Name as given at registration" name="salesRepName"
                                    value="{{ old('salesRepName') ?? $customer->sales_rep_name }}" maxlength="255" />
                                @error('salesRepName', 'editCustomer')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-6">
                                <label class="form-label" for="customer_group_id">Customer Group</label>
                                <select class="form-select select2" id="customer_group_id" name="customer_group_id">
                                    @if ($customer_groups->isNotEmpty())
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

