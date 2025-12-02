@php
use Illuminate\Support\Facades\Route;
@endphp

<ul class="menu-sub">
  @if (isset($menu))
    @foreach ($menu as $submenu)

    {{-- active menu method --}}
    @php
      $activeClass = null;
      $currentRouteName =  Route::currentRouteName();
   
      if ($currentRouteName === $submenu['slug']) {
          $activeClass = 'active';
      }
      
    @endphp
      <li class="menu-item {{$activeClass}}">
        <a href="{{ isset($submenu['url']) ? url($submenu['url']) : 'javascript:void(0)' }}" class="{{ (!empty($submenu['children'])) ? 'menu-link menu-toggle' : 'menu-link' }}" @if (isset($submenu->target) and !empty($submenu->target)) target="_blank" @endif>
          @if (isset($submenu['icon']))
          <i class="{{ $submenu['icon'] }}"></i>
          @endif
          <div>{{ isset($submenu['name']) ? $submenu['name'] : '' }}</div>
        </a>

        {{-- submenu --}}
        @if (!empty($submenu['children']))
          @include('layouts.sections.menu.submenu',['menu' => $submenu['children']])
        @endif
      </li>
    @endforeach
  @endif
</ul>
