@php
use Illuminate\Support\Facades\Route;
@endphp

<ul class="menu-sub">
  @foreach ($menu as $submenu)
    @php
        $isActive = \App\Helpers\Helpers::isMenuActive($submenu);
    @endphp

    <li class="menu-item {{ $isActive ? 'active open' : '' }}">
        <a href="{{ isset($submenu['url']) ? url($submenu['url']) : 'javascript:void(0)' }}"
           class="{{ (!empty($submenu['children'])) ? 'menu-link menu-toggle' : 'menu-link' }}"
           @if (!empty($submenu['target'])) target="_blank" @endif>
           @isset($submenu['icon'])
           <i class="{{ $submenu['icon'] }}"></i>
           @endisset
           <div>{{ $submenu['name'] ?? '' }}</div>
        </a>

        @if (!empty($submenu['children']))
            @include('layouts.sections.menu.submenu',['menu' => $submenu['children']])
        @endif
    </li>
  @endforeach
</ul>
