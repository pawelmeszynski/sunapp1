@if(Menu::get('AppNavBar'))
<nav class="header-navbar navbar-expand-lg navbar navbar-with-menu navbar-light navbar-shadow fixed-top">
    <div class="navbar-wrapper">
        <div class="navbar-container content">
            <div class="navbar-collapse" id="navbar-mobile">
                <div class="mr-auto float-left bookmark-wrapper d-flex align-items-center">
                    <ul class="nav navbar-nav">
                        <li class="nav-item mobile-menu d-xl-none mr-auto"><a class="nav-link nav-menu-main menu-toggle hidden-xs" href="#"><i class="ficon feather icon-menu"></i></a></li>
                    </ul>
                </div>

                <ul class="nav navbar-nav float-right navbar-langs">
                    {{--<li class="nav-item align-text-center d-none d-sm-flex a-ic"><a href="{{ route('SunApp::core.cache.clear') }}" class="sg-btn waves-effect waves-light">Usuń cache</a></li>--}}
                    @foreach(Menu::get('AppNavBar')->sortBy('order')->roots() as $menu)
                        <li @lm_attrs($menu) class="dropdown nav-item d-flex a-ic" @lm_endattrs>
                            @if($menu->link)
                            <a @lm_attrs($menu->link) @if($menu->hasChildren()) class="dropdown-toggle nav-link" href="#" data-toggle="dropdown" @else class="nav-link" href="{{ $menu->url() }}"  @endif @lm_endattrs>
                                <i class="@if(!\Str::contains($menu->icon, 'flag-icon')) ficon align-text-bottom @endif {{ $menu->icon }}"></i>
                                <span class="menu-title" data-i18n="">{{ $menu->title }}</span>
                                @if ($menu->badge!='')
                                    <span class="{{ $menu->badgeClass!='' ? $menu->badgeClass.' test' : 'badge badge-pill badge-primary float-right notTest' }} ">{{$menu->badge}}</span>
                                @endif
                                {!! $menu->afterHTML  !!}
                            </a>
                            @if($menu->hasChildren())
                                <div class="dropdown-menu dropdown-menu-right">
                                @partial('navbar-submenu', ['items' => $menu->children()])
                                </div>
                            @endif
                            @else
                                {!! $menu->title !!}
                            @endif

                        </li>
                        @if($menu->divider)
                            <li{!! Lavary\Menu\Builder::attributes($menu->divider) !!}></li>
                        @endif
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</nav>
<!-- END: Header-->
@endif
