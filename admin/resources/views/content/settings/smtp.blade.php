@extends('layouts/layoutMaster')

@section('title', 'SMTP')

@section('content')
  <div class="row g-6">
    @include('content.settings.sidebar')

    <div class="col-12 col-lg-12 pt-6 pt-lg-0">
      <div class="card mb-6">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">SMTP</h5>
        </div>
        <div class="card-body">
          <p class="text-muted small mb-4">
            Outbound mail uses only these credentials (stored in the database). There is no fallback to <code>.env</code> <code>MAIL_*</code> values.
            Until all required fields are saved and valid, messages are not sent via SMTP (mailer stays disabled).
          </p>

          <form action="{{ route('settings.smtp.update') }}" method="POST" autocomplete="off">
            @csrf

            <div class="row g-4">
              <div class="col-12 col-md-6">
                <label class="form-label" for="smtp_host">SMTP host <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="smtp_host" name="smtp_host"
                  value="{{ old('smtp_host', $setting['smtp_host'] ?? '') }}"
                  placeholder="smtp.example.com" required>
                @error('smtp_host')
                  <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-12 col-md-6">
                <label class="form-label" for="smtp_port">Port <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="smtp_port" name="smtp_port"
                  value="{{ old('smtp_port', $setting['smtp_port'] ?? '587') }}"
                  min="1" max="65535" required>
                @error('smtp_port')
                  <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-12 col-md-6">
                <label class="form-label" for="smtp_username">Username <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="smtp_username" name="smtp_username"
                  value="{{ old('smtp_username', $setting['smtp_username'] ?? '') }}" autocomplete="username" required>
                @error('smtp_username')
                  <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-12 col-md-6">
                <label class="form-label" for="smtp_password">Password <span class="text-danger">*</span></label>
                <div class="input-group">
                  <input type="password" class="form-control" id="smtp_password" name="smtp_password" value=""
                    placeholder="{{ !empty($setting['smtp_password']) ? 'Saved (leave blank to keep unchanged)' : 'SMTP password' }}"
                    autocomplete="new-password" {{ empty($setting['smtp_password'] ?? null) ? 'required' : '' }}>
                  <button class="btn btn-outline-secondary" type="button" id="toggle-smtp-password" aria-label="Show password">
                    <i class="menu-icon icon-base ti tabler-eye-off" id="smtp-password-icon"></i>
                  </button>
                </div>
                <div class="text-muted small mt-1">Stored encrypted. Leave blank to keep the current password when editing.</div>
                @error('smtp_password')
                  <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-12 col-md-6">
                <label class="form-label" for="smtp_encryption">Encryption</label>
                <select class="form-select" id="smtp_encryption" name="smtp_encryption">
                  @php $enc = old('smtp_encryption', $setting['smtp_encryption'] ?? ''); @endphp
                  <option value="" {{ $enc === '' ? 'selected' : '' }}>None</option>
                  <option value="tls" {{ $enc === 'tls' ? 'selected' : '' }}>TLS</option>
                  <option value="ssl" {{ $enc === 'ssl' ? 'selected' : '' }}>SSL</option>
                </select>
                @error('smtp_encryption')
                  <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-12">
                <hr class="my-2">
                <h6 class="mb-3">Default “From” for outbound mail</h6>
              </div>

              <div class="col-12 col-md-6">
                <label class="form-label" for="mail_from_address">From email <span class="text-danger">*</span></label>
                <input type="email" class="form-control" id="mail_from_address" name="mail_from_address"
                  value="{{ old('mail_from_address', $setting['mail_from_address'] ?? '') }}"
                  placeholder="noreply@example.com" required>
                @error('mail_from_address')
                  <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-12 col-md-6">
                <label class="form-label" for="mail_from_name">From name</label>
                <input type="text" class="form-control" id="mail_from_name" name="mail_from_name"
                  value="{{ old('mail_from_name', $setting['mail_from_name'] ?? '') }}"
                  placeholder="Company name">
                @error('mail_from_name')
                  <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <div class="mt-4">
              <button type="submit" class="btn btn-primary">Save</button>
            </div>
          </form>

          <script>
            document.addEventListener('DOMContentLoaded', function () {
              var btn = document.getElementById('toggle-smtp-password');
              var input = document.getElementById('smtp_password');
              var icon = document.getElementById('smtp-password-icon');
              if (btn && input) {
                btn.addEventListener('click', function () {
                  var isPwd = input.type === 'password';
                  input.type = isPwd ? 'text' : 'password';
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
