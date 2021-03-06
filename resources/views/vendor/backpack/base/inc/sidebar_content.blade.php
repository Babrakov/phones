<!-- This file is used to store sidebar items, starting with Backpack\Base 0.9.0 -->
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i> {{ trans('backpack::base.dashboard') }}</a></li>

{{--@role('admin')--}}
@if(backpack_user()->hasRole('admin'))
<!-- Users, Roles Permissions -->
<li class="nav-item nav-dropdown">
    <a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-group"></i> {{ trans('backpack::permissionmanager.auth') }}</a>
    <ul class="nav-dropdown-items">
        <li class="nav-item"><a class="nav-link" href="{{ backpack_url('user') }}"><i class="nav-icon la la-user"></i> <span>{{ trans('backpack::permissionmanager.users') }}</span></a></li>
        <li class="nav-item"><a class="nav-link" href="{{ backpack_url('role') }}"><i class="nav-icon la la-group"></i> <span>{{ trans('backpack::permissionmanager.roles') }}</span></a></li>
        <li class="nav-item"><a class="nav-link" href="{{ backpack_url('permission') }}"><i class="nav-icon la la-key"></i> <span>{{ trans('backpack::permissionmanager.permissions') }}</span></a></li>
    </ul>
</li>
@endif
{{--@endrole--}}

{{--<li class='nav-item'><a class='nav-link' href='{{ backpack_url('tag') }}'><i class='nav-icon la la-question'></i> Tags</a></li>--}}

<li class='nav-item'><a class='nav-link' href='{{ backpack_url('phone') }}'><i class='nav-icon la la-phone'></i> Телефоны</a></li>
{{--{{  dd(backpack_user()->hasRole('user')) }}--}}
{{--@if(backpack_user()->hasRole('admin'))--}}
<li class="nav-item nav-dropdown">
    <a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-folder-open"></i> Справочники</a>
    <ul class="nav-dropdown-items">
        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('sex') }}'><i class='nav-icon la la-venus-mars'></i> Полы</a></li>
        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('source') }}'><i class='nav-icon la la-map-marker'></i> Источники</a></li>
        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('region') }}'><i class='nav-icon la la-map'></i> Регионы</a></li>
        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('town') }}'><i class='nav-icon la la-city'></i> Нас. пункты</a></li>
    </ul>
</li>
{{--@endif--}}
