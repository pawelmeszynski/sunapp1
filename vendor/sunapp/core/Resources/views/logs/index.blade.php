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
                                    <div @click="getItems('all', searchPhrase, 'click')"
                                         class="list-group-item list-group-item-action border-0"
                                         :class="{ active: this.itemsFilter === 'all' }">
                                        <i class="font-medium-5 feather icon-align-left mr-50"></i>
                                        @lang('core::actions.all') <span class="badge badge-primary badge-pill float-right">@{{ itemsTotal }}</span>
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
                                            <input type="text" class="form-control" v-model="searchPhrase" @keyup.enter="getItems(itemsFilter, searchPhrase)" placeholder="@lang('core::actions.search')..." disabled>
                                            <div class="form-control-position search-icon" @click="getItems(itemsFilter, searchPhrase)">
                                                <i class="feather icon-search"></i>
                                            </div>
                                            <div id="clear-search" @click="clearSearchPhrase">
                                                &times;
                                            </div>
                                        </fieldset>
                                    </div>
                                    <div class="app-action">
                                        <div class="action-left">
                                        </div>
                                        <div class="action-right">
                                            <ul class="list-inline m-0">
                                                <li class="list-inline-item">
                                                    <a class="dt-refresh-action has-tooltip" href="#" @click.prevent="getItems('refresh', searchPhrase)">
                                                        <span class="tooltip-text">@lang('core::actions.refresh_list')</span>
                                                        <span class="action-icon"><i class="feather icon-refresh-ccw"></i></span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
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
                                            <ol class="users-list-wrapper media-list p-0" v-show="items.length">
                                                <li v-for="(item, index) in items" :key="item.id" class="list-item media" :data-id="item.id" @dblclick.prevent="showElement(item.links.show)">
                                                    <div class="media-left pr-50">
                                                    </div>
                                                    <div class="media-body">
                                                        <div class="user-details">
                                                            <div class="mail-items">
                                                                <h5 class="list-group-item-heading text-bold-600 mb-25">
                                                                    <span class="form-group-translation">
                                                                        <span class="text-muted">#@{{ item.id }}</span>
                                                                        <span>@{{ item.attributes.name + ' [' + item.attributes.size + ']' }}</span>
                                                                    </span>
                                                                </h5>
                                                                <span class="list-group-item-text text-truncate">
                                                                    @lang('core::fields.last_update'): @{{ item.attributes.updated_at }}
                                                                </span>
                                                            </div>
                                                            <div class="mail-meta-item">
                                                                <div class="float-right">
                                                                    <div class="float-right todo-item-action d-flex">
                                                                        <a v-if="item.links.show" :href="item.links.show" class="show-element has-tooltip has-tooltip--down" @click.prevent="showElement(item.links.show)">
                                                                            <span class="tooltip-text">@lang('core::actions.show')</span>
                                                                            <i class="m-25 feather icon-eye"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="mail-message">
                                                            <p class="list-group-item-text truncate mb-0">
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="clearfix"></div>
                                                </li>
                                            </ol>
                                        </div>
                                        <div v-show="nextPageUrl && !isLoading" class="get-next-items__wrapper">
                                            <button class="btn btn-primary btn-sm pull-left waves-effect waves-light"
                                                    @click.prevent="getNextItems(nextPageUrl)">
                                                @lang('core::actions.load_next_items')
                                            </button>
                                        </div>
                                        <div class="placeholders placeholders--domains" v-show="nextItemsLoading">
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
                                            </div>
                                        </div>
                                        <div class="no-results" v-show="!isLoading && !items.length">
                                            <h5 style="display: none;" :class="{ 'd-block' : !items.length }">@lang('core::messages.no_items')</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @include('core::logs.log')
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
@theme_asset('roles-vue', '../modules/core/assets/js/logs.js', ['base-vue'])
