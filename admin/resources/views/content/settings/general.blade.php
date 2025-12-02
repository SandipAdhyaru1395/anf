@extends('layouts/layoutMaster')

@section('title', 'General Settings')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/dropzone/dropzone.scss','resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/dropzone/dropzone.js','resources/assets/vendor/libs/@form-validation/popular.js','resources/assets/vendor/libs/@form-validation/bootstrap5.js','resources/assets/vendor/libs/@form-validation/auto-focus.js'])
@endsection

@section('page-script')
@vite('resources/assets/js/settings-general.js')
@endsection

@section('content')
<div class="row g-6">
  @include('content/settings/sidebar')

  <!-- Options -->
  <div class="col-12 col-lg-9 pt-6 pt-lg-0">
    <div class="tab-content p-0">
      <!-- Store Details Tab -->
      <div class="tab-pane fade show active" id="general" role="tabpanel">
        <form id="generalSettingsForm" action="{{ route(name: 'settings.general.update') }}" method="post" enctype="multipart/form-data" id="generalForm">
          @csrf
          <div class="card mb-6">
            <div class="card-body">
              <div class="row mb-6 g-6">
                <div class="col-12 form-control-validation">
                  <label class="form-label mb-1" for="company-title">Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="company-title" placeholder="Company Title"
                    name="companyTitle" value="{{ $setting['company_title'] ?? '' }}"/>
                    @error('companyTitle')
                      <span class="text-danger text-center mb-5" role="alert">
                          <strong>{{ $message }}</strong>
                      </span>
                    @enderror
                </div>
                <div class="col-12 form-control-validation">
                  <label class="form-label mb-1" for="company-name">Company Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="company-name" placeholder="Company Name"
                    name="companyName" value="{{ $setting['company_name'] ?? '' }}"/>
                  @error('companyName')
                  <span class="text-danger text-center mb-5" role="alert">
                      <strong>{{ $message }}</strong>
                  </span>
                  @enderror
                </div>
                <div class="col-12">
                  <label class="form-label mb-1" for="company-address">Address</label>
                  <input type="text" class="form-control" id="company-address" placeholder="Company Address"
                    name="companyAddress" value="{{ $setting['company_address'] ?? '' }}"/>
                  @error('companyAddress')
                  <span class="text-danger text-center mb-5" role="alert">
                      <strong>{{ $message }}</strong>
                  </span>
                  @enderror
                </div>
                <div class="col-12 col-md-6 form-control-validation">
                  <label class="form-label mb-1" for="company-email">Email</label>
                  <input type="email" class="form-control" id="company-email" placeholder="info@example.com"
                    name="companyEmail" value="{{ $setting['company_email'] ?? '' }}"/>
                  @error('companyEmail')
                  <span class="text-danger text-center mb-5" role="alert">
                      <strong>{{ $message }}</strong>
                  </span>
                  @enderror
                </div>
                <div class="col-12 col-md-6 form-control-validation">
                  <label class="form-label mb-1" for="company-phone">Phone</label>
                  <input type="text" class="form-control" id="company-phone" placeholder="+911234567890"
                    name="companyPhone" maxlength="10" onkeypress="return /^[0-9.]+$/.test(event.key)" value="{{ $setting['company_phone'] ?? '' }}"/>
                    @error('companyPhone')
                      <span class="text-danger text-center mb-5" role="alert">
                          <strong>{{ $message }}</strong>
                      </span>
                    @enderror
                </div>

                <div class="col-12 col-md-6 form-control-validation">
                  <label class="form-label mb-1" for="session-timeout">Session Timeout (minutes)</label>
                  <input type="text" onkeypress="return /^[0-9.]+$/.test(event.key)" class="form-control" id="session-timeout" placeholder="60"
                    name="sessionTimeout" value="{{ $setting['session_timeout'] ?? '' }}"/>
                  @error('sessionTimeout')
                    <span class="text-danger text-center mb-5" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                  @enderror
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label mb-1" for="currency-symbol">Currency Symbol</label>
                  <input type="text" class="form-control" id="currency-symbol" placeholder="£"
                    name="currencySymbol" value="{{ $setting['currency_symbol'] ?? '' }}"/>
                  @error('currencySymbol')
                  <span class="text-danger text-center mb-5" role="alert">
                      <strong>{{ $message }}</strong>
                  </span>
                  @enderror
                </div>

                <div class="col-12">
                  <h5 class="mb-3 mt-4">Bank Account Details</h5>
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label mb-1" for="account-name">Account Name</label>
                  <input type="text" class="form-control" id="account-name" placeholder="Account Name"
                    name="accountName" value="{{ $setting['account_name'] ?? '' }}"/>
                  @error('accountName')
                  <span class="text-danger text-center mb-5" role="alert">
                      <strong>{{ $message }}</strong>
                  </span>
                  @enderror
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label mb-1" for="bank">Bank</label>
                  <input type="text" class="form-control" id="bank" placeholder="Bank Name"
                    name="bank" value="{{ $setting['bank'] ?? '' }}"/>
                  @error('bank')
                  <span class="text-danger text-center mb-5" role="alert">
                      <strong>{{ $message }}</strong>
                  </span>
                  @enderror
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label mb-1" for="sort-code">Sort Code</label>
                  <input type="text" class="form-control" id="sort-code" placeholder="00-00-00"
                    name="sortCode" value="{{ $setting['sort_code'] ?? '' }}"/>
                  @error('sortCode')
                  <span class="text-danger text-center mb-5" role="alert">
                      <strong>{{ $message }}</strong>
                  </span>
                  @enderror
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label mb-1" for="account-no">Account No</label>
                  <input type="text" class="form-control" id="account-no" placeholder="Account Number"
                    name="accountNo" value="{{ $setting['account_no'] ?? '' }}"/>
                  @error('accountNo')
                  <span class="text-danger text-center mb-5" role="alert">
                      <strong>{{ $message }}</strong>
                  </span>
                  @enderror
                </div>

                <div class="col-12">
                  <label class="form-label mb-1" for="company-logo">Logo</label>
                  <!-- Media -->
                  <div class="card">
                      <img class="align-self-center pt-5" height="200px" width="300px" src="{{ isset($setting['company_logo']) ? asset('storage/'.$setting['company_logo']) : '' }}" alt="Company Logo" />
                      <div class="card-body form-control-validation">
                          <input type="file" name="companyLogo" id="companyLogo" hidden>
                          <div class="dropzone needsclick p-0" id="dropzone-basic">
                              <div class="dz-message needsclick">
                                  <p class="h4 needsclick pt-3 mb-2">Drag and drop logo here</p>
                                  <p class="h6 text-body-secondary d-block fw-normal mb-2">or</p>
                                  <span class="needsclick btn btn-sm btn-label-primary" id="btnBrowse">Browse
                                      image</span>
                              </div>
                          </div>
                      </div>
                      @error('companyLogo')
                      <span class="text-danger text-center mb-5" role="alert">
                          <strong>{{ $message }}</strong>
                      </span>
                      @enderror
                  </div>
                  <!-- /Media -->
                </div>
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-end gap-4">
            <button type="reset" class="btn btn-label-secondary">Discard</button>
            <button class="btn btn-primary" type="submit">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <!-- /Options-->
</div>

@endsection
