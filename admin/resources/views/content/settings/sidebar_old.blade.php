<!-- Sidebar Navigation -->
<div class="col-12 col-lg-3">
  <div class="sidebar-wrapper d-flex flex-column p-3 shadow-sm rounded">
    <h5 class="mb-4 text-uppercase fw-bold">Settings</h5>

    <div class="sidebar-scrollable flex-grow-1">
      <ul class="nav flex-column sidebar-menu">

        <!-- General Links -->
        <li class="nav-item mb-1">
          <a class="nav-link {{ request()->routeIs('settings.general') ? 'active' : '' }}"
            href="{{ route('settings.general') }}">
            <i class="menu-icon icon-base ti tabler-building-store me-2"></i> General
          </a>
        </li>
        <li class="nav-item mb-1">
          <a class="nav-link {{ request()->routeIs('settings.banner') ? 'active' : '' }}"
            href="{{ route('settings.banner') }}">
            <i class="menu-icon icon-base ti tabler-photo me-2"></i> Banner
          </a>
        </li>
        <li class="nav-item mb-1">
          <a class="nav-link {{ request()->routeIs('settings.maintenance') ? 'active' : '' }}"
            href="{{ route('settings.maintenance') }}">
            <i class="menu-icon icon-base ti tabler-tools me-2"></i> Maintenance
          </a>
        </li>
        <li class="nav-item mb-1">
          <a class="nav-link {{ request()->routeIs('settings.deliveryMethod') ? 'active' : '' }}"
            href="{{ route('settings.deliveryMethod') }}">
            <i class="menu-icon icon-base ti tabler-truck-delivery me-2"></i> Delivery Methods
          </a>
        </li>

        <!-- Customer Options -->
        @php
          $groupRoutes = [
            'settings.customerGroup',
            'settings.customerGroup.*',
            'settings.priceList',
            'settings.priceList.*',
          ];

          $customerActive = request()->routeIs($groupRoutes);
        @endphp

        <li class="nav-item mb-1">
          <a class="nav-link d-flex justify-content-between align-items-center {{ $customerActive ? '' : 'collapsed' }}"
            data-bs-toggle="collapse" href="#customerGroup" role="button"
            aria-expanded="{{ $customerActive ? 'true' : 'false' }}" aria-controls="customerGroup">

            <span>
              <i class="menu-icon icon-base ti tabler-users me-2"></i>
              Groups & Price Lists
            </span>

            <i class="menu-icon icon-base ti tabler-chevron-down"></i>
          </a>

          <ul class="collapse nav flex-column sidebar-child {{ $customerActive ? 'show' : '' }}" id="customerGroup">

            <li class="nav-item mb-1">
              <a class="nav-link {{ request()->routeIs('settings.customerGroup*') ? 'active' : '' }}"
                href="{{ route('settings.customerGroup') }}">
                Customer Groups
              </a>
            </li>

            <li class="nav-item mb-1">
              <a class="nav-link {{ request()->routeIs('settings.priceList*') ? 'active' : '' }}"
                href="{{ route('settings.priceList') }}">
                Price Lists
              </a>
            </li>

          </ul>
        </li>


        <!-- Tax & Units -->
        @php
          $taxActive = request()->routeIs('settings.vatMethod') || request()->routeIs('settings.unit');
        @endphp
        <li class="nav-item mb-1">
          <a class="nav-link d-flex justify-content-between align-items-center {{ $taxActive ? '' : 'collapsed' }}"
            data-bs-toggle="collapse" href="#taxUnitGroup" role="button"
            aria-expanded="{{ $taxActive ? 'true' : 'false' }}" aria-controls="taxUnitGroup">
            <span><i class="menu-icon icon-base ti tabler-scale me-2"></i> Tax & Units</span>
            <i class="menu-icon icon-base ti tabler-chevron-down"></i>
          </a>
          <ul class="collapse nav flex-column sidebar-child {{ $taxActive ? 'show' : '' }}" id="taxUnitGroup">
            <li class="nav-item mb-1">
              <a class="nav-link {{ request()->routeIs('settings.vatMethod') ? 'active' : '' }}"
                href="{{ route('settings.vatMethod') }}">
                VAT Methods
              </a>
            </li>
            <li class="nav-item mb-1">
              <a class="nav-link {{ request()->routeIs('settings.unit') ? 'active' : '' }}"
                href="{{ route('settings.unit') }}">
                Units
              </a>
            </li>
          </ul>
        </li>

      </ul>
    </div>
  </div>
</div>

<style>
  /* Sidebar wrapper */
  .sidebar-wrapper {
    background-color: #fff;
    border-radius: 0.5rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
  }

  /* Sidebar scrollable */
  .sidebar-scrollable {
    overflow-y: auto;
    max-height: 75vh;
    height: auto;
    padding-right: 0.25rem;
  }

  /* Custom scrollbar */
  .sidebar-scrollable::-webkit-scrollbar {
    width: 6px;
  }

  .sidebar-scrollable::-webkit-scrollbar-thumb {
    background-color: rgba(0, 0, 0, 0.2);
    border-radius: 3px;
  }

  .sidebar-scrollable::-webkit-scrollbar-track {
    background: transparent;
  }

  /* Menu links */
  .sidebar-menu .nav-link {
    color: #495057;
    font-weight: 500;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    transition: all 0.2s;
  }

  .sidebar-menu .nav-link:hover {
    background-color: #f1f3f5;
    color: #212529;
  }

  .sidebar-menu .nav-link.active {
    background-color: var(--logo-color);
    color: #f8f9fa;
    font-weight: 600;
  }

  /* Child links */
  .sidebar-menu .sidebar-child {
    padding-left: 0 !important;
    margin-left: 0 !important;
  }

  .sidebar-menu .sidebar-child .nav-link {
    padding-left: 4.25rem;
    display: flex;
    align-items: center;
  }

  .sidebar-menu .sidebar-child .nav-link:hover {
    background-color: #f8f9fa;
  }

  .sidebar-menu .sidebar-child .nav-link.active {
    background-color: var(--logo-color);
    color: #f8f9fa;
  }

  /* Icons */
  .sidebar-menu i.menu-icon {
    font-size: 1.1rem;
  }

  /* Override Bootstrap hover/focus for active links */
  .sidebar-menu .nav-link.active,
  .sidebar-menu .nav-link.active:hover,
  .sidebar-menu .nav-link.active:focus {
    color: #f8f9fa !important;
    background-color: var(--logo-color) !important;
  }

  /* Collapsible arrows */
  .nav-link[data-bs-toggle="collapse"] i.tabler-chevron-down {
    transition: transform 0.3s ease;
  }

  .nav-link[data-bs-toggle="collapse"].collapsed i.tabler-chevron-down {
    transform: rotate(0deg);
  }

  .nav-link[data-bs-toggle="collapse"]:not(.collapsed) i.tabler-chevron-down {
    transform: rotate(180deg);
  }
</style>