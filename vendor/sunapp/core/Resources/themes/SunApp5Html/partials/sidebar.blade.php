@if(Menu::get('AppSideBar'))
    <div class="main-menu menu-fixed menu-dark menu-accordion menu-shadow @if($menu_cookie) expanded @endif" data-scroll-to-active="true">
        <div class="navbar-header">
            <ul class="nav navbar-nav flex-row">
                <li class="nav-item mr-auto"><a class="navbar-brand" href="{{route('SunApp::home')}}">
                        <div class="brand-logo"></div>
                    </a></li>
                <li class="nav-item nav-toggle"><a class="nav-link modern-nav-toggle pr-0" data-toggle="collapse"><i class="feather icon-x d-block d-xl-none font-medium-4 primary toggle-icon"></i><i class="toggle-icon feather icon-disc font-medium-4 d-none d-xl-block primary" data-ticon="icon-disc"></i></a></li>
            </ul>
        </div>
        <div class="shadow-bottom"></div>
        <div class="main-menu-content">

            <ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation">
                @foreach(Menu::get('AppSideBar')->sortBy('order')->roots() as $menu)
                    <li @lm_attrs($menu) class="nav-item" @lm_endattrs>
                        <a href="{{ $menu->url() }}@if($menu->add_timestamp)?ts={{time()}} @endif">
                            <i class="{{ $menu->icon }}"></i>
                            <span class="menu-title" data-i18n="">{{ $menu->title }}</span>
                            @if ($menu->badge!='')
                                <span class="{{ $menu->badgeClass!='' ? $menu->badgeClass.' test' : 'badge badge-pill badge-primary float-right notTest' }} ">{{$menu->badge}}</span>
                            @endif
                            {!! $menu->afterHTML  !!}
                        </a>
                        @if($menu->hasChildren())
                            @partial('sidebar-submenu', ['items' => $menu->children()])
                        @endif
                    </li>
                    @if($menu->divider)
                        <li{!! Lavary\Menu\Builder::attributes($menu->divider) !!} class="navigation-header"></li>
                    @endif
                @endforeach
            </ul>
            @if(auth()->user()->superadmin)
                <div class="sidebar--system_info" onclick="openModal('{{route('SunApp::core.get.version-data')}}','core::info.server_info')">
                    <p>PHP: {{phpversion()}} &nbsp;
                        SunApp: {{sunapp_version()}} &nbsp;
                        @lang('core::info.processess'): {{process_queues()}}</p>
                </div>
            @endif
        </div>
    </div>
    <!-- END: Main Menu-->
@endif
