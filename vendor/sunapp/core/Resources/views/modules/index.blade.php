<div id="app" class="sg-application">
    <div class="app-content">

        <div class="content-overlay"></div>
        <div class="content-area-wrapper">
        <div v-if="installModuleStatus" class="loading-status-absolute">@{{installModuleStatusText}}</div>

            <div class="sidebar-left">
                <div class="sidebar">
                    <div class="sidebar-content sg-app-sidebar d-flex">
                    <span class="sidebar-close-icon">
                        <i class="feather icon-x"></i>
                    </span>
                        <div class="sg-app-menu">
                            <div class="sidebar-menu-list">
                                <div class="list-group list-group-messages font-medium-1">
                                    <div @click="showModulesToInstall = false;getItems('all', searchPhrase, 'click');"
                                         class="list-group-item list-group-item-action border-0"
                                         :class="{ active: (this.itemsFilter === 'all' && this.showModulesToInstall === false) }">
                                        <i class="font-medium-5 feather icon-align-left mr-50"></i>
                                        @lang('core::modules.allInstallModules') <span class="badge badge-primary badge-pill float-right">@{{ itemsTotal }}</span>
                                    </div>
                                    <div @click="getModulesToInstall()"
                                         class="list-group-item list-group-item-action border-0"
                                         :class="{ active: this.showModulesToInstall === true }">
                                        <i class="font-medium-5 feather icon-download mr-50"></i>
                                        @lang('core::modules.installModules') 
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
                            <div class="sg-app-list-wrapper">
                                <div class="sg-app-list">
                                    <div class="loading-spinner-wrapper" id="spinner" v-show="loadingCircle">
                                        <div class="loading-spinner spinner-border text-primary">
                                        </div>
                                    </div>
                                    <div class="app-fixed-search">
                                        <div class="sidebar-toggle d-block d-lg-none"><i class="feather icon-menu"></i></div>
                                        <fieldset class="form-group position-relative has-icon-left m-0">
                                            <input type="text" class="form-control" v-model="searchPhrase" @keyup.enter="getItems(itemsFilter, searchPhrase)" placeholder="@lang('core::actions.search')...">
                                            <div class="form-control-position search-icon" @click="getItems(itemsFilter, searchPhrase)">
                                                <i class="feather icon-search"></i>
                                            </div>
                                            <div id="clear-search" @click="clearSearchPhrase">
                                                &times;
                                            </div>
                                        </fieldset>
                                    </div>
                                   
                                    <div class="sg-user-list list-group">
                                    <div class="progress progress-deleting progress-bar-primary" v-show="currentProgress">
                                            <div class="progress-bar progress-bar-striped progress-bar-animated"
                                                 role="progressbar" :aria-valuenow="currentProgress"
                                                 aria-valuemin="0" aria-valuemax="100" :style="{ 'width': currentProgress+'%' }">
                                            </div>
                                        </div>
                                        <div class="placeholders placeholders--domains" v-show="isLoading">
                                            <div class="placeholder placeholder--domains">
                                                <div class="placeholder-content-wrapper">
                                                    <div class="placeholder-content">
                                                        <div class="placeholder-content_item"></div>
                                                    </div>
                                                </div>
                                                <div class="placeholder-content-wrapper">
                                                    <div class="placeholder-content">
                                                        <div class="placeholder-content_item"></div>
                                                    </div>
                                                </div>
                                                <div class="placeholder-content-wrapper">
                                                    <div class="placeholder-content">
                                                        <div class="placeholder-content_item"></div>
                                                    </div>
                                                </div>
                                                <div class="placeholder-content-wrapper">
                                                    <div class="placeholder-content">
                                                        <div class="placeholder-content_item"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <ol class="users-list-wrapper media-list p-0 dd-list" v-show="!showModulesToInstall" v-show="items.length">
                                                <li v-for="(item, index) in items" :key="item.id" class="list-item media" @dblclick.stop="itemsFilter == 'trashed' ? showElement(item.links.show) : editElement(item.links.edit)">
                                                    <div class="media-left pr-50"></div>
                                                    <div class="media-body">
                                                        <div class="user-details">
                                                            <div class="mail-items">
                                                                <h5 class="list-group-item-heading text-bold-600 mb-25">
                                                                <span class="form-group-translation">
                                                                    <span class="text-muted">#@{{ item.id }}</span>
                                                                    <span class="form-field-translation d-inline-block">
                                                                        @{{ item.attributes.name }}
                                                                    </span>
                                                                </span>
                                                                </h5>
                                                                <span class="list-group-item-text text-truncate">@lang('core::fields.last_update'): @{{ item.attributes.updated_at }}<template v-if="item.attributes.updatedBy">, @{{ item.attributes.updatedBy }}</template>
                                                                </span>
                                                            </div>
                                                            <div class="mail-meta-item">
                                                                <div class="float-right">
                                                                    <div class="float-right todo-item-action d-flex">
                                                                        <template v-if=" item.attributes.alias != 'core'">
                                                                            <a v-if="item.attributes.moduleStatus == 1" class="has-tooltip has-tooltip--down" style="color: #0f0;" :href="item.links.enable" @click.prevent="permitElement($event, 'disableModule', false, item.attributes)">
                                                                                <span class="tooltip-text">@lang('core::modules.disableModule')</span>
                                                                                <i class="m-25 feather icon-toggle-right"></i>
                                                                            </a>
                                                                            <a v-if="item.attributes.moduleStatus == 0" class="has-tooltip has-tooltip--down" style="color: #f00;" :href="item.links.enable" @click.prevent="permitElement($event, 'enableModule', true, item.attributes)">
                                                                                <span class="tooltip-text">@lang('core::modules.enableModule')</span>
                                                                                <i class="m-25 feather icon-toggle-left"></i>
                                                                            </a>
                                                                            <form :action="item.links.enable" method="post" class="form-enable-module">
                                                                                {{csrf_field()}}
                                                                                <input type="hidden" name="is_enabled" :value="item.attributes.moduleStatus">
                                                                            </form>
                                                                        </template>

                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                      
                                                    </div>
                                                    <div class="clearfix"></div>
                                                </li>
                                            </ol>
                                            <ol  class="users-list-wrapper media-list p-0 dd-list" v-show="showModulesToInstall">
                                                <li v-for="(item, index) in modulesToInstall"  class="list-item media">
                                                    <div class="media-left pr-50"></div>
                                                    <div class="media-body">
                                                        <div class="user-details">
                                                            <div class="mail-items">
                                                                <h5 class="list-group-item-heading text-bold-600 mb-25">
                                                                <span class="form-group-translation">
                                                                    <span class="text-muted">#@{{ index }}</span>
                                                                    <span class="form-field-translation d-inline-block">
                                                                        @{{ item.name }}
                                                                    </span>
                                                                </span>
                                                                </h5>
                                                                <span class="list-group-item-text text-truncate">
                                                                    <span v-if="item.installed == false">@lang('core::modules.notinstalled')</span> 
                                                                    <span v-else>
                                                                        @lang('core::modules.installed'), 
                                                                        <span v-if="item.status == true">@lang('core::modules.enabled')</span>
                                                                        <span v-else>@lang('core::modules.disabled')</span>
                                                                    </span>
                                                                </span>
                                                            </div>
                                                            <div class="mail-meta-item">
                                                                <div class="float-right">
                                                                    <div class="float-right todo-item-action d-flex">
                                                                        <template>
                                                                            <a class="has-tooltip has-tooltip--down btn btn-sm btn-success" v-if="item.installed == false" @click.prevent="installModule(true, item)">
                                                                                <span class="tooltip-text">@lang('core::modules.install')</span>
                                                                                @lang('core::modules.install')
                                                                            </a>
                                                                            <a class="has-tooltip has-tooltip--down btn btn-sm btn-danger" v-if="item.installed == true" @click.prevent="installModule(false, item)">
                                                                                <span class="tooltip-text">@lang('core::modules.uninstall')</span>
                                                                                @lang('core::modules.uninstall')
                                                                            </a>
                                                                        </template>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="clearfix"></div>
                                                </li>
                                            </ol>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @include('core::users.form')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="sidenav-overlay"></div>
    <div class="drag-target"></div>
</div>

<style>
    .loading-status-absolute {
        width: 100%;
        height: 100%;
        background: #000;
        position: absolute;
        z-index: 10000;
        opacity: 0.5;
        text-align: center;
        padding: 20% 0;
        color: #fff;
        font-weight: bold;
    }
</style>

@if (app()->isProduction())
    <script src="@asset('js/vue.js')"></script>
@else
    <script src="@asset('js/vue-dev.js')"></script>
@endif
<script src="@asset('js/axios.min.js')"></script>

@theme_asset('base-vue', '../assets/js/vue-base.js', ['app'])
@theme_asset('modules-vue', '../modules/core/assets/js/modules.js', ['base-vue'])
