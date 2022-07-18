<div id="app" class="sg-application">
    <div class="app-content">
        <div class="content-overlay"></div>
        <div class="content-area-wrapper">
            <div class="sidebar-left">
                <div class="sidebar">
                    <div class="sidebar-content sg-app-sidebar d-flex">
                    <span class="sidebar-close-icon">
                        <i class="feather icon-x"></i>
                    </span>
                        <div class="sg-app-menu">
                            <div class="sidebar-menu-list">
                                <div class="list-group list-group-messages font-medium-1">
                                    <div class="list-group-item list-group-item-action border-0"
                                         :class="{ active: this.itemsFilter === 'all' }">
                                        <i class="font-medium-5 feather icon-align-left mr-50"></i>
                                        @lang('core::actions.all_m') <span class="badge badge-primary badge-pill float-right">1</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-right">
                <div class="content-wrapper">
                    <div class="content-header row">
                    </div>
                    <div class="content-body">
                        <div class="app-content-overlay"></div>
                        <div class="sg-app-area">
                            @include('core::my-account.form')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="sidenav-overlay"></div>
    <div class="drag-target"></div>
</div>

@if (app()->isProduction())
    <script src="@asset('js/vue.js')"></script>
@else
    <script src="@asset('js/vue-dev.js')"></script>
@endif
<script src="@asset('js/axios.min.js')"></script>

@theme_asset('base-vue', '../assets/js/vue-base.js', ['app'])
@theme_asset('module-users-vue', '../modules/core/assets/js/module-users.js', ['base-vue'])
@theme_asset('my-account-vue', '../modules/core/assets/js/my-account.js', ['module-users-vue'])
