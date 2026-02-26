<!-- Responsive Settings Navigation -->
<div class="col-12 mb-4">
  <div class="settings-wrapper shadow-sm rounded p-3 bg-white">

    <!-- DESKTOP MENU -->
    <ul class="nav nav-pills d-none d-md-flex flex-row gap-2">

      <!-- General -->
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('settings.general') ? 'active' : '' }}"
           href="{{ route('settings.general') }}">
          <i class="ti tabler-building-store me-1"></i> General
        </a>
      </li>

      <!-- Banner -->
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('settings.banner') ? 'active' : '' }}"
           href="{{ route('settings.banner') }}">
          <i class="ti tabler-photo me-1"></i> Banner
        </a>
      </li>

      <!-- Maintenance -->
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('settings.maintenance') ? 'active' : '' }}"
           href="{{ route('settings.maintenance') }}">
          <i class="ti tabler-tools me-1"></i> Maintenance
        </a>
      </li>

      <!-- Delivery -->
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('settings.deliveryMethod') ? 'active' : '' }}"
           href="{{ route('settings.deliveryMethod') }}">
          <i class="ti tabler-truck-delivery me-1"></i> Delivery
        </a>
      </li>

      @php
        $groupRoutes = [
          'settings.customerGroup',
          'settings.customerGroup.*',
          'settings.priceList',
          'settings.priceList.*',
        ];
        $customerActive = request()->routeIs($groupRoutes);
      @endphp

      <!-- Desktop Dropdown - Customer -->
      <li class="nav-item dropdown position-relative">
        <a class="nav-link dropdown-toggle {{ $customerActive ? 'active' : '' }}"
           data-bs-toggle="dropdown"
           aria-expanded="false">
          <i class="ti tabler-users me-1"></i> Customer Groups & Price Lists
        </a>

        <ul class="dropdown-menu shadow-sm w-100">
          <li>
            <a class="dropdown-item {{ request()->routeIs('settings.customerGroup*') ? 'active' : '' }}"
               href="{{ route('settings.customerGroup') }}">
              Customer Groups
            </a>
          </li>
          <li>
            <a class="dropdown-item {{ request()->routeIs('settings.priceList*') ? 'active' : '' }}"
               href="{{ route('settings.priceList') }}">
              Price Lists
            </a>
          </li>
        </ul>
      </li>

      @php
        $taxActive = request()->routeIs('settings.vatMethod') || request()->routeIs('settings.unit') || request()->routeIs('settings.currency');
      @endphp

      <!-- Desktop Dropdown - Tax & Currency -->
      <li class="nav-item dropdown position-relative">
        <a class="nav-link dropdown-toggle {{ $taxActive ? 'active' : '' }}"
           data-bs-toggle="dropdown"
           aria-expanded="false">
          <i class="ti tabler-scale me-1"></i> VAT Methods & Units
        </a>

        <ul class="dropdown-menu shadow-sm w-100">
          <li>
            <a class="dropdown-item {{ request()->routeIs('settings.vatMethod') ? 'active' : '' }}"
               href="{{ route('settings.vatMethod') }}">
              VAT Methods
            </a>
          </li>
          <li>
            <a class="dropdown-item {{ request()->routeIs('settings.unit') ? 'active' : '' }}"
               href="{{ route('settings.unit') }}">
              Units
            </a>
          </li>
          <li>
            <a class="dropdown-item {{ request()->routeIs('settings.currency') ? 'active' : '' }}"
               href="{{ route('settings.currency') }}">
              Currencies
            </a>
          </li>
        </ul>
      </li>

    </ul>


    <!-- MOBILE MENU -->
    <ul class="nav flex-column d-md-none sidebar-mobile-menu mt-2">

      <li class="nav-item mb-1">
        <a class="nav-link {{ request()->routeIs('settings.general') ? 'active' : '' }}"
           href="{{ route('settings.general') }}">
          General
        </a>
      </li>

      <li class="nav-item mb-1">
        <a class="nav-link {{ request()->routeIs('settings.banner') ? 'active' : '' }}"
           href="{{ route('settings.banner') }}">
          Banner
        </a>
      </li>

      <li class="nav-item mb-1">
        <a class="nav-link {{ request()->routeIs('settings.maintenance') ? 'active' : '' }}"
           href="{{ route('settings.maintenance') }}">
          Maintenance
        </a>
      </li>

      <li class="nav-item mb-1">
        <a class="nav-link {{ request()->routeIs('settings.deliveryMethod') ? 'active' : '' }}"
           href="{{ route('settings.deliveryMethod') }}">
          Delivery
        </a>
      </li>

      <!-- Mobile Collapse Groups -->
      <li class="nav-item mb-1">
        <a class="nav-link d-flex justify-content-between align-items-center collapsed"
           data-bs-toggle="collapse"
           href="#mobileGroupMenu"
           aria-expanded="false">
          Customer Groups & Price Lists
          <i class="ti tabler-chevron-down"></i>
        </a>

        <ul class="collapse nav flex-column ps-3" id="mobileGroupMenu">
          <li>
            <a class="nav-link {{ request()->routeIs('settings.customerGroup*') ? 'active' : '' }}"
               href="{{ route('settings.customerGroup') }}">
              Customer Groups
            </a>
          </li>
          <li>
            <a class="nav-link {{ request()->routeIs('settings.priceList*') ? 'active' : '' }}"
               href="{{ route('settings.priceList') }}">
              Price Lists
            </a>
          </li>
        </ul>
      </li>

      <!-- Mobile Collapse Tax -->
      <li class="nav-item mb-1">
        <a class="nav-link d-flex justify-content-between align-items-center collapsed"
           data-bs-toggle="collapse"
           href="#mobileTaxMenu"
           aria-expanded="false">
          Tax
          <i class="ti tabler-chevron-down"></i>
        </a>

        <ul class="collapse nav flex-column ps-3" id="mobileTaxMenu">
          <li>
            <a class="nav-link {{ request()->routeIs('settings.vatMethod') ? 'active' : '' }}"
               href="{{ route('settings.vatMethod') }}">
              VAT Methods
            </a>
          </li>
          <li>
            <a class="nav-link {{ request()->routeIs('settings.unit') ? 'active' : '' }}"
               href="{{ route('settings.unit') }}">
              Units
            </a>
          </li>
          <li>
            <a class="nav-link {{ request()->routeIs('settings.currency') ? 'active' : '' }}"
               href="{{ route('settings.currency') }}">
              Currencies
            </a>
          </li>
        </ul>
      </li>

    </ul>

  </div>
</div>

<style>
/* Active Link */
.settings-wrapper .nav-link.active {
  background-color: var(--logo-color);
  color: #fff !important;
  border-radius: 6px;
}

/* Desktop dropdown full width fix */
.settings-wrapper .nav-item.dropdown {
  position: relative;
}

.settings-wrapper .dropdown-menu {
  min-width: 100% !important;
  left: 0;
  right: 0;
}

/* Mobile arrow rotation */
.sidebar-mobile-menu .nav-link[aria-expanded="true"] i {
  transform: rotate(180deg);
  transition: transform 0.3s ease;
}
</style>