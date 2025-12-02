<aside id="layout-menu" class="layout-menu menu-vertical menu">


    <div class="app-brand demo">
        <a href="{{ url('/') }}" class="app-brand-link">
            <span class="app-brand-logo demo">@include('_partials.macros')</span>
            <span class="app-brand-text demo menu-text fw-bold ms-3">{{ config('variables.templateName') }}</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="icon-base ti menu-toggle-icon d-none d-xl-block"></i>
            <i class="icon-base ti tabler-x d-block d-xl-none"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        @foreach ($sidebarMenuData as $menu)
            @php
                $isActive = \App\Helpers\Helpers::isMenuActive($menu);
            @endphp

            <li class="menu-item {{ $isActive ? 'active open' : '' }}">
                <a href="{{ isset($menu['url']) ? url($menu['url']) : 'javascript:void(0);' }}"
                    class="{{ !empty($menu['children']) ? 'menu-link menu-toggle' : 'menu-link' }}">
                    @isset($menu['icon'])
                        <i class="{{ $menu['icon'] }}"></i>
                    @endisset
                    <div>{{ $menu['name'] ?? '' }}</div>
                </a>

                @if (!empty($menu['children']))
                    @include('layouts.sections.menu.submenu', ['menu' => $menu['children']])
                @endif
            </li>
        @endforeach

    </ul>

</aside>
