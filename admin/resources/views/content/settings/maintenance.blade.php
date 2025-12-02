@extends('layouts/layoutMaster')

@section('title', 'Maintenance Settings')

@section('content')
<div class="row g-6">
  @include('content/settings/sidebar')

  <!-- Options -->
  <div class="col-12 col-lg-9 pt-6 pt-lg-0">
    <div class="tab-content p-0">
      <div class="tab-pane fade show active" id="maintenance" role="tabpanel">
        <form action="{{ route('settings.maintenance.update') }}" method="post">
          @csrf
          <div class="card mb-6">
            <div class="card-body">
              <div class="row mb-6 g-6">
                <div class="col-12">
                  <div class="d-flex align-items-center justify-content-between">
                    <div>
                      <h5 class="mb-1">Maintenance Mode</h5>
                      <p class="mb-0 text-body-secondary">When enabled, the application and the store front enter in maintenance mode.</p>
                    </div>
                    <div class="form-check form-switch mb-0">
                      <input class="form-check-input" type="checkbox" id="maintenanceEnabled" name="maintenanceEnabled" {{ ($setting['maintenance_mode'] ?? '0') == '1' ? 'checked' : '' }}>
                    </div>
                  </div>
                </div>
                <div class="col-12">
                  <label class="form-label" for="maintenanceSecret">Secret (optional)</label>
                  <input type="text" class="form-control" id="maintenanceSecret" name="maintenanceSecret" placeholder="e.g. admin-access" value="{{ $setting['maintenance_secret'] ?? '' }}">
                  <small class="text-body-secondary">If set, users with the secret path can access the admin panel while in maintenance.
                    @if(!empty($setting['maintenance_secret'] ?? ''))
                      Bypass URL: <code>{{ url('/') . '/' . $setting['maintenance_secret'] }}</code>
                    @endif
                  </small>
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


