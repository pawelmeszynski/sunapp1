<div class="sg-app-details">
    <div ref="form_wrapper">
        <div class="sg-detail-header">
            <div class="sg-header-left d-flex align-items-center mb-1">
                <h3 v-if="elementToShow">
                    <span class="text-muted">#@{{ elementToShow.id }}</span>
                    <span class="form-group-translation">
                        <span>
                            @{{ elementToShow.attributes.name }} (@{{ elementToShow.attributes.email }})
                        </span>
                    </span>
                </h3>
            </div>
            <div class="sg-header-right mb-1 ml-2 pl-1">
                <ul class="list-inline m-0">
                    <template v-if="elementToShow">
                        <li class="list-inline-item">
                            <a href="#" class="save-element has-tooltip" @click.prevent="saveElement('{{ route('SunApp::core.my-account.update', $item) }}')">
                                <span class="tooltip-text">@lang('core::actions.save')</span>
                                <span class="action-icon"><i class="feather icon-save font-medium-5"></i></span>
                            </a>
                        </li>
                    </template>
                </ul>
            </div>
        </div>
        <div class="sg-scroll-area">
            <div class="row">
                <div class="col-12">
                    @include('core::users.fields')
                </div>
            </div>
        </div>
    </div>
    <div class="sidenav-overlay"></div>
    <div class="drag-target"></div>
</div>
