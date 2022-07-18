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
                                <div class="list-group list-group-messages font-medium-1" v-if="loadedData">
                                    <div @click="getItems('all', searchPhrase, 'click')"
                                         class="list-group-item list-group-item-action border-0"
                                         :class="{ active: this.itemsFilter === 'all' }">
                                        <i class="font-medium-5 feather icon-align-left mr-50"></i>
                                        @lang('core::actions.all_m') <span class="badge badge-primary badge-pill float-right">@{{ itemsTotal }}</span>
                                    </div>
                                    <div @click="getItems('trashed', searchPhrase, 'click')"
                                         class="list-group-item list-group-item-action border-0"
                                         :class="{ active: this.itemsFilter === 'trashed' }">
                                        <i class="font-medium-5 feather icon-trash mr-50"></i>
                                        @lang('core::actions.trash') <span class="badge badge-secondary badge-pill float-right">@{{ itemsTrashed }}</span>
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
                                    <div class="app-action">
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
                                                @if($item->public_access && $item->public_access->value == 1)
                                                    <span>@lang('core::access.public_access')</span>
                                                    <div class="vs-checkbox-con" title="@lang('core::access.disable_public_acces')">
                                                        <input type="checkbox" checked onchange="location.href='{{ route('SunApp::core.access-enable') }}'">
                                                        <span class="vs-checkbox vs-checkbox-sm ml-1">
                                                                <span class="vs-checkbox--check">
                                                                    <i class="vs-icon feather icon-check"></i>
                                                                </span>
                                                            </span>
                                                    </div>
                                                @else
                                                    <span>@lang('core::access.public_access')</span>
                                                    <div class="vs-checkbox-con" title="@lang('core::access.enable_public_acces')">
                                                        <input type="checkbox" onchange="location.href='{{ route('SunApp::core.access-enable') }}'">
                                                        <span class="vs-checkbox vs-checkbox-sm ml-1">
                                                                <span class="vs-checkbox--check">
                                                                    <i class="vs-icon feather icon-minus"></i>
                                                                </span>
                                                            </span>
                                                    </div>
                                                @endif
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
                                                <li v-if="userCanCreate && createUrl" class="list-inline-item">
                                                    <a :href="createUrl" @click.prevent="createElement('click')" class="has-tooltip">
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
                                        <div>
                                            <ol class="users-list-wrapper media-list p-0 dd-list" v-show="items.length">
                                                <li v-for="(item, index) in items" :key="item.id" class="list-item media" @dblclick.stop="itemsFilter == 'trashed' ? showElement(item.links.show) : editElement(item.links.edit)">
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
                                                                    <span class="form-field-translation d-inline-block">
                                                                        @{{ item.attributes.ip_address_mask }}
                                                                    </span>
                                                                </span>
                                                                </h5>
                                                                <span class="list-group-item-text text-truncate">@lang('core::fields.last_update'): @{{ item.attributes.updated_at }}<template v-if="item.attributes.updatedBy">, @{{ item.attributes.updatedBy }}</template>
                                                                </span>
                                                            </div>
                                                            <div class="mail-meta-item">
                                                                <div class="float-right">
                                                                    <div class="float-right todo-item-action d-flex">
                                                                        <template v-if="item.links.update && item.attributes.deleted_at">
                                                                            <a class="has-tooltip has-tooltip--down" :href="item.links.update" @click.prevent="restoreElement($event)">
                                                                                <span class="tooltip-text">@lang('core::actions.restore')</span>
                                                                                <i class="m-25 feather icon-arrow-left"></i>
                                                                            </a>
                                                                            <form :action="item.links.update" method="post" class="form-restore">
                                                                                <input name="_method" type="hidden" value="PATCH">
                                                                                {{csrf_field()}}
                                                                            </form>
                                                                        </template>
                                                                        <a v-if="item.links.show" :href="item.links.show" class="show-element has-tooltip has-tooltip--down" @click.prevent="showElement(item.links.show)">
                                                                            <span class="tooltip-text">@lang('core::actions.show')</span>
                                                                            <i class="m-25 feather icon-eye"></i>
                                                                        </a>
                                                                        <a v-if="item.links.edit && !item.attributes.deleted_at" :href="item.links.edit" class="edit-element has-tooltip has-tooltip--down" @click.prevent="editElement(item.links.edit)">
                                                                            <span class="tooltip-text">@lang('core::actions.edit')</span>
                                                                            <i class="m-25 feather icon-edit-2"></i>
                                                                        </a>
                                                                        <template v-if="!item.attributes.default && item.links.destroy">
                                                                            <a :href="item.links.destroy" @click.prevent="removeElement($event)" class="destroy-element has-tooltip has-tooltip--down">
                                                                                <span class="tooltip-text">@lang('core::actions.remove')</span>
                                                                                <i class="m-25 feather icon-trash"></i>
                                                                            </a>
                                                                            <form :action="item.links.destroy" method="post" class="form-delete">
                                                                                <input name="_method" type="hidden" value="DELETE">
                                                                                {{csrf_field()}}
                                                                            </form>
                                                                        </template>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="mail-message">
                                                            <p class="list-group-item-text truncate mb-0">
                                                                <template v-if="!item.attributes.deleted_at">
                                                                    <!--
                                                                    <div class="chip-wrapper d-inline-block" v-if="!item.attributes.w_2fa">
                                                                        <div class="chip chip-warning mb-0">
                                                                            <div class="chip-body">
                                                                                <span class="chip-text"> @lang('core::access.check_w_2fa')</span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    -->
                                                                    <div class="chip-wrapper d-inline-block" v-if="item.attributes.w_2fa">
                                                                        <div class="chip chip-success mb-0">
                                                                            <div class="chip-body">
                                                                                <span class="chip-text"> @lang('core::access.no_check_w_2fa')</span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </template>
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
                            @include('core::access.form')
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
@theme_asset('access-vue', '../modules/core/assets/js/access.js', ['base-vue'])
