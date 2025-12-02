<!-- Navigation -->
  <div class="col-12 col-lg-3">
    <div class="d-flex justify-content-between flex-column mb-4 mb-md-0">
      <h5 class="mb-4">Settings</h5>
      <ul class="nav nav-align-left nav-pills flex-column">
        <li class="nav-item mb-1">
          <a class="nav-link {{ request()->routeIs('settings.general') ? 'active' : '' }}" href="{{ route('settings.general') }}">
            <i class="icon-base ti tabler-building-store icon-sm me-1_5"></i>
            <span class="align-middle">General</span>
          </a>
        </li>
        <li class="nav-item mb-1">
          <a class="nav-link {{ request()->routeIs('settings.banner') ? 'active' : '' }}" href="{{ route('settings.banner') }}">
            <i class="icon-base ti tabler-photo icon-sm me-1_5"></i>
            <span class="align-middle">Banner</span>
          </a>
        </li>
        <li class="nav-item mb-1">
          <a class="nav-link {{ request()->routeIs('settings.maintenance') ? 'active' : '' }}" href="{{ route('settings.maintenance') }}">
            <i class="icon-base ti tabler-tools icon-sm me-1_5"></i>
            <span class="align-middle">Maintenance</span>
          </a>
        </li>
        <li class="nav-item mb-1">
          <a class="nav-link {{ request()->routeIs('settings.theme') ? 'active' : '' }}" href="{{ route('settings.theme') }}">
            <i class="icon-base ti tabler-pencil icon-sm me-1_5"></i>
            <span class="align-middle">Theme</span>
          </a>
        </li>
        <li class="nav-item mb-1">
          <a class="nav-link {{ request()->routeIs('settings.deliveryMethod') ? 'active' : '' }}" href="{{ route('settings.deliveryMethod') }}">
            <i class="icon-base ti tabler-truck-delivery icon-sm me-1_5"></i>
            <span class="align-middle">Delivery Methods</span>
          </a>
        </li>
        <li class="nav-item mb-1">
          <a class="nav-link {{ request()->routeIs('settings.vatMethod') ? 'active' : '' }}" href="{{ route('settings.vatMethod') }}">
            <i class="icon-base ti tabler-percentage icon-sm me-1_5"></i>
            <span class="align-middle">VAT Methods</span>
          </a>
        </li>
        <li class="nav-item mb-1">
          <a class="nav-link {{ request()->routeIs('settings.unit') ? 'active' : '' }}" href="{{ route('settings.unit') }}">
            <i class="icon-base ti tabler-scale icon-sm me-1_5"></i>
            <span class="align-middle">Units</span>
          </a>
        </li>
      </ul>
    </div>
  </div>
  <!-- /Navigation -->