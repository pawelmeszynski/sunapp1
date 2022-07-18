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
                                            <input type="text" class="form-control" v-model="searchPhrase" @keyup.enter="getItems(itemsFilter, searchPhrase)" placeholder="@lang('core::actions.search')...">
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
                                            <div v-show="items.length" class="vs-checkbox-con selectAll" @change="selectAll($event)">
                                                <input type="checkbox" id="selectAllCheckbox">
                                                <span class="vs-checkbox">
                                                    <span class="vs-checkbox--check">
                                                        <i class="vs-icon feather icon-minus"></i>
                                                    </span>
                                                </span>
                                                <span>@lang('core::actions.select_all')</span>
                                            </div>
                                        </div>
                                        <div class="action-right">
                                            <ul class="list-inline m-0">
                                                <li id="list-inline-item--remove" class="list-inline-item hidden">
                                                    <a href="#" class="action-icon remove-all has-tooltip" @click.prevent="removeAll">
                                                        <span class="tooltip-text">@lang('core::actions.remove_selected')</span>
                                                        <i class="feather icon-trash"></i>
                                                    </a>
                                                </li>
                                                <li id="list-inline-item--restore" class="list-inline-item hidden">
                                                    <a href="#" class="action-icon restore-all has-tooltip" @click.prevent="restoreAll">
                                                        <span class="tooltip-text">@lang('core::actions.restore_selected')</span>
                                                        <i class="feather icon-arrow-left"></i>
                                                    </a>
                                                </li>
                                                <li v-if="userCanCreate" class="list-inline-item hidden">
                                                    <a href="#" @click.prevent="createElement('click')" class="has-tooltip">
                                                        <span class="tooltip-text">@lang('core::actions.new_item')</span>
                                                        <span class="action-icon"><i class="feather icon-plus"></i></span>
                                                    </a>
                                                </li>
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
                                        <div :class="{'dd dd-main' : itemsFilter === 'all' && searchPhrase === ''}" data-token="{{ csrf_token() }}">
                                            <ol class="users-list-wrapper media-list p-0 dd-list" v-show="items.length">
                                                <li v-for="(item, index) in items" :key="item.id" class="list-item media dd-item" :data-id="item.id" :data-parent="item.attributes.parent_id" ref="nested">
                                                    <div class="media-left pr-50">
                                                        <div class="user-action">
                                                            <div class="vs-checkbox-con">
                                                                <input type="checkbox" class="item-checkbox" @change="selectOne">
                                                                <span class="vs-checkbox vs-checkbox-sm">
                                                                <span class="vs-checkbox--check">
                                                                    <i class="vs-icon feather icon-check"></i>
                                                                </span>
                                                            </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="media-body">
                                                        <div class="user-details">
                                                            <div class="mail-items">
                                                                <h5 class="list-group-item-heading text-bold-600 mb-25">
                                                                     <span class="form-group-translation">
                                                                        <span class="text-muted">#@{{ item.id }}</span>
                                                                        <span>@lang('core::security.ip_address'): @{{ item.attributes.ip_address }}</span>
                                                                         <span><template v-if="item.attributes.active == 1">@lang('core::security.lock_active'): Tak</template> <template v-else>@lang('core::security.lock_active'): Nie</template></span>
                                                                     </span>
                                                                </h5>
                                                                <span class="list-group-item-text text-truncate"><b>@lang('core::security.blocked_from'):</b> @{{ item.attributes.blocked_from }}
                                                                    <br/><b>@lang('core::security.blocked_to'):</b> @{{ item.attributes.blocked_to }}
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

                                                    <template v-if="itemsFilter === 'all' && index !== items.length - 1">
                                                        <ol class="dd-list" v-if="items[index+1].attributes.parent_id === item.id"></ol>
                                                    </template>
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

                            @include('core::locks.form')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="sidenav-overlay"></div>
    <div class="drag-target"></div>
</div>

{{--<script src="@asset('../app-assets/vendors/js/vue/vue.js')"></script>--}}
<script src="@asset('js/vue-dev.js')"></script>
<script src="@asset('js/axios.min.js')"></script>

@theme_asset('base-vue', '../assets/js/vue-base.js', ['app'])
@theme_asset('roles-vue', '../modules/core/assets/js/locks.js', ['base-vue'])
