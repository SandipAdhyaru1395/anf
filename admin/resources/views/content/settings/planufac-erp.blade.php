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

            <hr class="my-4">

            <h6 class="mb-3">Custom order webhook</h6>
            <p class="text-muted small mb-3">
              Used when a mobile/API order is placed: a signed payload is queued to Planufac (<code>/webhooks/customorderapp/{identifier}</code>).
            </p>

            <div class="col-12 col-xxl-8 mb-3">
              <label for="planufac_client_identifier" class="form-label">Client identifier</label>
              <input type="text" class="form-control" id="planufac_client_identifier" name="planufac_client_identifier"
                value="{{ old('planufac_client_identifier', $setting['planufac_client_identifier'] ?? '') }}"
                placeholder="e.g. 87" autocomplete="off">
              <div class="text-muted small mt-1">Path segment from your Planufac webhook URL (same as Postman <code>.../customorderapp/87</code>).</div>
              @error('planufac_client_identifier')
                <div class="text-danger small mt-1">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-12 col-xxl-8 mb-3">
              <label for="planufac_webhook_secret" class="form-label">Webhook secret (HMAC)</label>
              <div class="input-group">
                <input type="password" class="form-control" id="planufac_webhook_secret" name="planufac_webhook_secret"
                  value=""
                  placeholder="{{ !empty($setting['planufac_webhook_secret']) ? 'Saved (leave blank to keep unchanged)' : 'Enter webhook secret' }}"
                  autocomplete="new-password">
                <button class="btn btn-outline-secondary" type="button" id="toggle-planufac-webhook-secret">
                  <i class="menu-icon icon-base ti tabler-eye-off" id="planufac-webhook-secret-icon"></i>
                </button>
              </div>
              <div class="text-muted small mt-1">
                Same secret as Postman <code>webhook_secret</code> (HMAC of the raw JSON body). Leave blank to keep the current value.
              </div>
              @error('planufac_webhook_secret')
                <div class="text-danger small mt-1">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-12 col-xxl-8 mb-3">
              <label for="planufac_webhook_header" class="form-label">Webhook signature header</label>
              <input type="text" class="form-control" id="planufac_webhook_header" name="planufac_webhook_header"
                value="{{ old('planufac_webhook_header', $setting['planufac_webhook_header'] ?? 'X-CustomOrderApp-Signature') }}"
                placeholder="X-CustomOrderApp-Signature" autocomplete="off">
              <div class="text-muted small mt-1">HTTP header name for the Base64 HMAC signature (e.g. <code>X-CustomOrderApp-Signature</code>).</div>
              @error('planufac_webhook_header')
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

              var whToggle = document.getElementById('toggle-planufac-webhook-secret');
              var whInput = document.getElementById('planufac_webhook_secret');
              var whIcon = document.getElementById('planufac-webhook-secret-icon');
              if (whToggle && whInput) {
                whToggle.addEventListener('click', function () {
                  var isPwd = whInput.type === 'password';
                  whInput.type = isPwd ? 'text' : 'password';
                  if (whIcon) {
                    whIcon.classList.toggle('tabler-eye-off');
                    whIcon.classList.toggle('tabler-eye');
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

