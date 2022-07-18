<div class="sg-app-details">
    <div ref="form_wrapper">
        <div class="sg-detail-header">
            <div class="sg-header-left d-flex align-items-center mb-1">
                <span class="go-back mr-1" @click="hideCard"><i class="feather icon-arrow-left font-medium-4"></i></span>
                <h3 v-if="elementToShow">
                    <span class="text-muted">#@{{ elementToShow.id }}</span>
                    <span class="form-group-translation">
                        <span>
                            @{{ elementToShow.attributes.name }}
                        </span>
                    </span>
                </h3>
                <h3 v-else>@lang('core::actions.new_item')</h3>
            </div>
            <div class="sg-header-right mb-1 ml-2 pl-1">
                <ul class="list-inline m-0">
                    <template v-if="elementToShow">
                        <li v-if="elementToShow.links.update && elementToShow.attributes.deleted_at">
                            <a :href="elementToShow.links.update" @click.prevent="restoreElement($event)" class="has-tooltip">
                                <span class="tooltip-text">@lang('core::actions.restore')</span>
                                <i class="m-25 feather icon-arrow-left font-medium-5"></i>
                            </a>
                            <form :action="elementToShow.links.update" method="post" class="form-restore">
                                <input name="_method" type="hidden" value="PATCH">
                                {{csrf_field()}}
                            </form>
                        </li>
                        <li v-if="!itemPreview" class="list-inline-item">
                            <a href="#" class="save-element has-tooltip" @click.prevent="saveElement(elementToShow.links.update, true, true)">
                                <span class="tooltip-text">@lang('core::actions.save_and_leave')</span>
                                <span class="action-icon"><i class="feather icon-save font-medium-5"></i><i class="feather icon-arrow-left font-small-2"></i></span>
                            </a>
                            <a href="#" class="save-element has-tooltip" @click.prevent="saveElement(elementToShow.links.update, true)">
                                <span class="tooltip-text">@lang('core::actions.save_and_new')</span>
                                <span class="action-icon"><i class="feather icon-save font-medium-5"></i><i class="feather icon-plus-circle font-small-2"></i></span>
                            </a>
                            <a href="#" class="save-element has-tooltip" @click.prevent="saveElement(elementToShow.links.update)">
                                <span class="tooltip-text">@lang('core::actions.save')</span>
                                <span class="action-icon"><i class="feather icon-save font-medium-5"></i></span>
                            </a>
                        </li>
                    </template>
                    <template v-else>
                        <li v-if="!itemPreview && storeUrl" class="list-inline-item">
                            <a href="#" class="save-element has-tooltip" @click.prevent="saveElement(storeUrl, true, true)">
                                <span class="tooltip-text">@lang('core::actions.create_and_leave')</span>
                                <i class="feather icon-save font-medium-5"></i><i class="feather icon-arrow-left font-small-2"></i>
                            </a>
                            <a href="#" class="save-element has-tooltip" @click.prevent="saveElement(storeUrl, true)">
                                <span class="tooltip-text">@lang('core::actions.create_and_new')</span>
                                <i class="feather icon-save font-medium-5"></i><i class="feather icon-plus-circle font-small-2"></i>
                            </a>
                            <a href="#" class="save-element has-tooltip" @click.prevent="saveElement(storeUrl)">
                                <span class="tooltip-text">@lang('core::actions.create')</span>
                                <i class="feather icon-save font-medium-5"></i>
                            </a>
                        </li>
                        <li v-else-if="!itemPreview && !storeUrl" class="list-inline-item">
                            <a href="#" class="save-element has-tooltip" @click.prevent="saveElement('actionUrl', true, true)">
                                <span class="tooltip-text">@lang('core::actions.create_and_leave')</span>
                                <i class="feather icon-save font-medium-5"></i><i class="feather icon-arrow-left font-small-2"></i>
                            </a>
                            <a href="#" class="save-element has-tooltip" @click.prevent="saveElement('actionUrl', true)">
                                <span class="tooltip-text">@lang('core::actions.create_and_new')</span>
                                <i class="feather icon-save font-medium-5"></i><i class="feather icon-plus-circle font-small-2"></i>
                            </a>
                            <a href="#" class="save-element has-tooltip" @click.prevent="saveElement('actionUrl')">
                                <span class="tooltip-text">@lang('core::actions.create')</span>
                                <i class="feather icon-save font-medium-5"></i>
                            </a>
                        </li>
                    </template>

                    <template v-if="elementToShow">
                        <li v-if="elementToShow.links.edit && !elementToShow.attributes.deleted_at && !itemEdition" class="list-inline-item">
                            <a :href="elementToShow.links.edit" class="edit-element has-tooltip" @click.prevent="editElement(elementToShow.links.edit)">
                                <span class="tooltip-text">@lang('core::actions.edit')</span>
                                <span class="action-icon"><i class="feather icon-edit-2 font-medium-5"></i></span>
                            </a>
                        </li>
                        <li v-if="elementToShow.links.destroy && !defaultItemEdition" class="list-inline-item">
                            <a :href="elementToShow.links.destroy" class="destroy-element has-tooltip" @click.prevent="removeElement($event)">
                                <span class="tooltip-text">@lang('core::actions.remove')</span>
                                <i class="feather icon-trash font-medium-5"></i>
                            </a>
                            <form :action="elementToShow.links.destroy" method="post" class="form-delete">
                                <input name="_method" type="hidden" value="DELETE">
                                {{csrf_field()}}
                            </form>
                        </li>
                    </template>
                </ul>
            </div>
        </div>
        <div class="sg-scroll-area">
            <div class="row">
                <div class="col-12">
                    {!! form_start($form,['id'=>'form-data','novalidate'=>'novalidate','method'=>'POST']) !!}
                    <input type="hidden" v-if="itemEdition" name="_method" value="PATCH">
                    <div class="form-body">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="info-tab" data-toggle="tab" href="#info"
                                   aria-controls="info" role="tab"
                                   aria-selected="true">@lang('cms::pages.info')</a>
                            </li>
                            @include('core::extra-fields.tab')
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane active" id="info" aria-labelledby="info-tab" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-4 col-12">
                                        {!! form_field($form,'ip_address_mask') !!}
                                        {!! form_field($form,'w_2fa') !!}
                                    </div>
                                </div>
                            </div>
                            @include('core::extra-fields.content')
                        </div>
                    </div>
                    {!! form_end($form) !!}
                </div>
            </div>
        </div>
    </div>
    <div class="sidenav-overlay"></div>
    <div class="drag-target"></div>
</div>
