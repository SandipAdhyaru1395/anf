@extends('layouts/layoutMaster')

@section('title', 'Payment Gateways')

@section('content')
    <div class="row">
        <div class="col-12 col-lg-10">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">DNA Payments</h5>
                    <div class="d-flex align-items-center gap-1">
                        <span class="text-muted small me-1">Off</span>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="dna_enabled_header" name="dna_enabled"
                                form="dna-payment-gateway-form"
                                {{ ($setting['dna_payments_enabled'] ?? '0') === '1' ? 'checked' : '' }}>
                        </div>
                        <span class="text-muted small ms-0" style="margin-left:-7px;">On</span>
                    </div>
                </div>
                <div class="card-body">
                    <form class="row mb-0" id="dna-payment-gateway-form"
                        action="{{ route('settings.paymentGateways.update') }}" method="POST">
                        @csrf

                        <div class="col-12 col-lg-6 mb-3">
                            <label for="dna_mode" class="form-label">Mode</label>
                            <select class="form-select" id="dna_mode" name="dna_mode">
                                @php $mode = $setting['dna_payments_mode'] ?? 'test'; @endphp
                                <option value="test" {{ $mode === 'test' ? 'selected' : '' }}>Test</option>
                                <option value="live" {{ $mode === 'live' ? 'selected' : '' }}>Live</option>
                            </select>
                        </div>

                        <div class="col-12 col-lg-6 mb-3">
                            <label for="dna_client_id" class="form-label">Client ID</label>
                            <input type="text" class="form-control" id="dna_client_id" name="dna_client_id"
                                value="{{ old('dna_client_id', $setting['dna_payments_client_id'] ?? '') }}">
                        </div>

                        <div class="col-12 col-xxl-8 mb-3 mb-lg-5">
                            <label for="dna_client_secret" class="form-label">Client Secret</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="dna_client_secret" name="dna_client_secret"
                                    value=""
                                    placeholder="{{ !empty($setting['dna_payments_client_secret']) ? 'Saved (leave blank to keep unchanged)' : 'Enter client secret' }}"
                                    autocomplete="new-password">
                                <button class="btn btn-outline-secondary" type="button" id="toggle-dna-secret">
                                    <i class="menu-icon icon-base ti tabler-eye-off" id="dna-secret-icon"></i>
                                </button>
                            </div>
                            <div class="text-muted small mt-1">
                                Leave blank to keep the current secret.
                            </div>
                        </div>

                        <div class="col-12 col-xxl-8 mb-5">
                            <label for="dna_terminal_id" class="form-label">Terminal ID</label>
                            <input type="text" class="form-control" id="dna_terminal_id" name="dna_terminal_id"
                                value="{{ old('dna_terminal_id', $setting['dna_payments_terminal_id'] ?? '') }}">
                        </div>
                        <div class="d-flex mt-5">
                            <button type="submit" class="btn btn-primary me-2">Save</button>
                        </div>
                    </form>
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            var toggleBtn = document.getElementById('toggle-dna-secret');
                            var secretInput = document.getElementById('dna_client_secret');
                            var icon = document.getElementById('dna-secret-icon');

                            if (toggleBtn && secretInput) {
                                toggleBtn.addEventListener('click', function () {
                                    var isPassword = secretInput.type === 'password';
                                    secretInput.type = isPassword ? 'text' : 'password';
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
