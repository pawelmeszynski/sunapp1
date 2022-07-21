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
                                        <div class="sidebar-toggle d-block d-lg-none"><i class="feather icon-menu"></i>
                                        </div>
                                        <fieldset class="form-group position-relative has-icon-left m-0">
                                            <input type="text" class="form-control" v-model="searchPhrase"
                                                   @keyup.enter="getItems(itemsFilter, searchPhrase)"
                                                   placeholder="@lang('core::actions.search')...">
                                            <div class="form-control-position search-icon"
                                                 @click="getItems(itemsFilter, searchPhrase)">
                                                <i class="feather icon-search"></i>
                                            </div>
                                            <div id="clear-search" @click="clearSearchPhrase">
                                                &times;
                                            </div>
                                        </fieldset>
                                    </div>
                                    <div class="app-action">
                                        <div class="action-left">
                                            <div v-show="items.length" class="vs-checkbox-con selectAll"
                                                 @change="selectAll($event)">
                                                <input type="checkbox" id="selectAllCheckbox">
                                                <span class="vs-checkbox">
                                                    <span class="vs-checkbox--check">
                                                        <i class="vs-icon feather icon-minus"></i>
                                                    </span>
                                                </span>
                                                <span>@lang('core::actions.select_all')</span>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <div class="sg-user-list list-group">
                                    <div class="progress progress-deleting progress-bar-primary"
                                         v-show="currentProgress">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated"
                                             role="progressbar" :aria-valuenow="currentProgress"
                                             aria-valuemin="0" aria-valuemax="100"
                                             :style="{ 'width': currentProgress+'%' }">
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
                                            <li v-for="(item, index) in items" :key="item.id" class="list-item media"
                                                @dblclick.stop="itemsFilter == 'trashed' ? showElement(item.links.show) : editElement(item.links.edit)">
                                                <div class="media-left pr-50">
                                                    <div class="user-action">
                                                        <div class="vs-checkbox-con">
                                                            <input type="checkbox" class="item-checkbox"
                                                                   @change="selectOne">
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
                                                                        @{{ item.attributes.name }} (@{{ item.attributes.email }})
                                                                    </span>
                                                                    <hr>
                                                                    <span> Punkty: @{{ item.attributes.points }}</span>
                                                                </span>
                                                            </h5>
                                                        </div>
                                                        <div class="mail-meta-item">
                                                            <div class="float-right">
                                                                <div class="float-right todo-item-action d-flex">
                                                                    </template>
                                                                    <a v-if="item.links.show" :href="item.links.show"
                                                                       class="show-element has-tooltip has-tooltip--down"
                                                                       @click.prevent="showElement(item.links.show)">
                                                                        <span
                                                                            class="tooltip-text">@lang('core::actions.show')</span>
                                                                        <i class="m-25 feather icon-eye"></i>
                                                                    </a>
                                                                    <a v-if="item.links.edit && !item.attributes.deleted_at"
                                                                       :href="item.links.edit"
                                                                       class="edit-element has-tooltip has-tooltip--down"
                                                                       @click.prevent="editElement(item.links.edit)">
                                                                        <span
                                                                            class="tooltip-text">@lang('core::actions.edit')</span>
                                                                        <i class="m-25 feather icon-edit-2"></i>
                                                                    </a>
                                                                    <template
                                                                        v-if="!item.attributes.default && item.links.destroy">
                                                                        <a :href="item.links.destroy"
                                                                           @click.prevent="removeElement($event)"
                                                                           class="destroy-element has-tooltip has-tooltip--down">
                                                                            <span
                                                                                class="tooltip-text">@lang('core::actions.remove')</span>
                                                                            <i class="m-25 feather icon-trash"></i>
                                                                        </a>
                                                                        <form :action="item.links.destroy" method="post"
                                                                              class="form-delete">
                                                                            <input name="_method" type="hidden"
                                                                                   value="DELETE">
                                                                            {{csrf_field()}}
                                                                        </form>
                                                                    </template>
                                                                    {{--<template>
                                                                        <a class="has-tooltip has-tooltip--down" :href="item.links.enable2fa" @click.prevent="permitElement($event, (item.attributes.is2fa_google_enabled === true) ? 'disable2fa' : 'enable2fa')">
                                                                            <span class="tooltip-text" v-if="item.attributes.is2fa_google_enabled == true">@lang('core::user.disable2fa')</span>
                                                                            <span class="tooltip-text" v-else>@lang('core::user.enable2fa')</span>
                                                                            <i class="m-25 feather icon-archive"></i>
                                                                        </a>
                                                                        <form :action="item.links.enable2fa" method="post" class="form-restore">
                                                                            {{csrf_field()}}
                                                                            <input type="hidden" name="is_enabled" :value="item.attributes.is2fa_google_enabled">
                                                                        </form>
                                                                    </template>--}}
                                                                    <template>
                                                                        <form :action="item.links.reset2fa"
                                                                              method="post" class="form-restore">
                                                                            {{csrf_field()}}
                                                                        </form>
                                                                    </template>
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
                                    <h5 style="display: none;"
                                        :class="{ 'd-block' : !items.length }">@lang('core::messages.no_items')</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                    @include('sunbet::users.form')
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
@theme_asset('users-vue', '../modules/core/assets/js/users.js', ['module-users-vue'])
