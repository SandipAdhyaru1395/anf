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
                $hasActiveChild = \App\Helpers\Helpers::hasActiveChild($menu);
            @endphp

            <li class="menu-item
                {{ $isActive ? 'active' : '' }}
                {{ !$isActive && $hasActiveChild ? 'open' : '' }}
            ">
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

    <style>
        /* Ensure no browser bullets/dots appear for sidebar lists */
        #layout-menu .menu-inner,
        #layout-menu .menu-sub,
        #layout-menu .menu-inner ul {
            list-style: none !important;
            padding-left: 0;
            margin-left: 0;
        }

        #layout-menu .menu-item {
            list-style: none !important;
        }

        /* Remove list markers and any template bullet pseudo-elements */
        #layout-menu .menu-item::marker {
            content: '' !important;
        }

        #layout-menu .menu-item::before {
            content: none !important;
        }

        /* Hide template's submenu bullet indicators */
        #layout-menu .menu-sub .menu-link::before,
        #layout-menu .menu-inner .menu-link::before {
            display: none !important;
            content: none !important;
            background: none !important;
            box-shadow: none !important;
        }
    </style>

</aside>
