@extends('layouts/layoutMaster')

@section('title', 'Banner Settings')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/dropzone/dropzone.scss','resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/dropzone/dropzone.js','resources/assets/vendor/libs/@form-validation/popular.js','resources/assets/vendor/libs/@form-validation/bootstrap5.js','resources/assets/vendor/libs/@form-validation/auto-focus.js'])
@endsection

@section('page-script')
@vite('resources/assets/js/settings-banner.js')
@endsection

@section('content')
<div class="row g-6">
  @include('content/settings/sidebar')

  <!-- Options -->
  <div class="col-12 col-lg-9 pt-6 pt-lg-0">
    <div class="tab-content p-0">
      <!-- Banner Tab -->
      <div class="tab-pane fade show active" id="banner" role="tabpanel">
        <form id="bannerSettingsForm" action="{{ route('settings.banner.update') }}" method="post" enctype="multipart/form-data">
          @csrf
          <div class="card mb-6">
            <div class="card-body">
              <div class="row mb-6 g-6">
                <div class="col-12">
                  <label class="form-label mb-1" for="banner-image">Banner Image <span class="text-danger">*</span></label>
                  <!-- Media -->
                  <div class="card">
                      <img class="align-self-center pt-5" height="200px" width="300px" src="{{ isset($setting['banner']) ? asset('storage/'.$setting['banner']) : '' }}" alt="Banner Image" data-original-src="{{ isset($setting['banner']) ? asset('storage/'.$setting['banner']) : '' }}" />
                      <div class="card-body form-control-validation">
                          <input type="file" name="bannerImage" id="bannerImage" hidden>
                          <div class="dropzone needsclick p-0" id="dropzone-banner">
                              <div class="dz-message needsclick">
                                  <p class="h4 needsclick pt-3 mb-2">Drag and drop banner image here</p>
                                  <p class="h6 text-body-secondary d-block fw-normal mb-2">or</p>
                                  <span class="needsclick btn btn-sm btn-label-primary" id="btnBrowseBanner">Browse
                                      image</span>
                              </div>
                          </div>
                      </div>
                      @error('bannerImage')
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
