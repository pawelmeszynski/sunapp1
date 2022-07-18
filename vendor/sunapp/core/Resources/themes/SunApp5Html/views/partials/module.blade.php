<div class="content-area-wrapper">
@hasSection('sidebar')
<div class="sidebar-left">
    <div class="sidebar">
        <div class="sidebar-content sg-app-sidebar d-flex">
                        <span class="sidebar-close-icon">
                            <i class="feather icon-x"></i>
                        </span>
            <div class="sg-app-menu">

                <div class="sidebar-menu-list">
                    @yield('sidebar')
                </div>
            </div>
        </div>
    </div>
</div>
@endif
<div class="@hasSection('sidebar') content-right @else content-right content-full @endif">
    <div class="content-wrapper">
        @yield('content')
    </div>
</div>
</div>
