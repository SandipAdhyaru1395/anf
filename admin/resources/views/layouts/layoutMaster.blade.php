@isset($pageConfigs)
  {!! Helper::updatePageConfig($pageConfigs) !!}
@endisset

@if(isset($sidebarMenuData))
  @include('layouts.contentNavbarLayout')
@else
  @include('layouts.blankLayout')
@endif
