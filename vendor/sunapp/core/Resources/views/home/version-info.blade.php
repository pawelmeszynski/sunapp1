@if($dbType)
    <p>@lang('core::info.base_type'): {{$dbType}}</p>
@endif
@if($dbVersion)
    <p>@lang('core::info.base_version'): {{$dbVersion}}</p>
@endif
@if($serverVersion)
    <p>@lang('core::info.server_version'): {{$serverVersion}}</p>
@endif
@if(count($requiredExtensions))
    <p>@lang('core::info.installed_php_extensions'):</p>
    <ul>
        @foreach($requiredExtensions as $extension)
            <li @if(!extension_loaded(str_replace('ext-', '', $extension))) style="color: red" @endif>{{ $extension }}</li>
        @endforeach
    </ul>
@endif
@if($installedModules)
    <p>@lang('core::info.installed_modules'):</p>
    <ul>
        @foreach ($installedModules as $module)
            <li>{{$module}}</li>
        @endforeach
    </ul>
@endif
