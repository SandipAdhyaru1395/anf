@extends('layouts/layoutMaster')

@section('title', 'Planufac ERP')

@section('content')
  <div class="row">
    <div class="col-12 col-lg-10">
      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Planufac ERP</h5>
        </div>
        <div class="card-body">
          <form class="row mb-0" id="planufac-erp-form" action="{{ route('settings.planufacErp.update') }}" method="POST">
            @csrf

            <div class="col-12 col-xxl-8 mb-3">
              <label for="planufac_base_url" class="form-label">Base URL</label>
              <input type="text" class="form-control" id="planufac_base_url" name="planufac_base_url"
                value="{{ old('planufac_base_url', $setting['planufac_base_url'] ?? 'https://sandbox.planufac.com') }}"
                placeholder="https://sandbox.planufac.com" autocomplete="off">
              @error('planufac_base_url')
                <div class="text-danger small mt-1">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-12 col-xxl-8 mb-3">
              <label for="planufac_email" class="form-label">Email</label>
              <input type="email" class="form-control" id="planufac_email" name="planufac_email"
                value="{{ old('planufac_email', $setting['planufac_email'] ?? '') }}"
                placeholder="you@example.com" autocomplete="off">
              @error('planufac_email')
                <div class="text-danger small mt-1">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-12 col-xxl-8 mb-3">
              <label for="planufac_password" class="form-label">Password</label>
              <div class="input-group">
                <input type="password" class="form-control" id="planufac_password" name="planufac_password"
                  value=""
                  placeholder="{{ !empty($setting['planufac_password']) ? 'Saved (leave blank to keep unchanged)' : 'Enter password' }}"
                  autocomplete="new-password">
                <button class="btn btn-outline-secondary" type="button" id="toggle-planufac-password">
                  <i class="menu-icon icon-base ti tabler-eye-off" id="planufac-password-icon"></i>
                </button>
              </div>
              <div class="text-muted small mt-1">
                Leave blank to keep the current password.
              </div>
              @error('planufac_password')
                <div class="text-danger small mt-1">{{ $message }}</div>
              @enderror
            </div>

            <div class="d-flex mt-4">
              <button type="submit" class="btn btn-primary me-2">Save</button>
            </div>
          </form>

          <script>
            document.addEventListener('DOMContentLoaded', function () {
              var toggleBtn = document.getElementById('toggle-planufac-password');
              var pwdInput = document.getElementById('planufac_password');
              var icon = document.getElementById('planufac-password-icon');

              if (toggleBtn && pwdInput) {
                toggleBtn.addEventListener('click', function () {
                  var isPassword = pwdInput.type === 'password';
                  pwdInput.type = isPassword ? 'text' : 'password';
                  if (icon) {
                    icon.classList.toggle('tabler-eye-off');
                    icon.classList.toggle('tabler-eye');
                  }
                });
              }
            });
          </script>
        </div>
      </div>
    </div>
  </div>
@endsection

