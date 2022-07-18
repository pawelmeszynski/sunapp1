<div class="sg-app-details">
    <div ref="form_wrapper">
        <div class="sg-detail-header">
            <div class="sg-header-left d-flex align-items-center mb-1">
                <span class="go-back mr-1" @click="hideCard"><i class="feather icon-arrow-left font-medium-4"></i></span>
                <h3 v-if="elementToShow">
                    <span class="text-muted">#@{{ elementToShow.id }}</span>
                    <span>@{{ elementToShow.attributes.ip_address }}</span>
                </h3>
                <h3 v-else>@lang('core::actions.new_item')</h3>
            </div>
            <div class="sg-header-right mb-1 ml-2 pl-1">
            </div>
        </div>
        <div class="sg-scroll-area">
            <div class="row">
                <div class="col-12">
                    {!! form_start($form,['id'=>'form-data','novalidate'=>'novalidate','method'=>'POST']) !!}
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="info-tab" data-toggle="tab" href="#info"
                               aria-controls="info" role="tab"
                               aria-selected="true">@lang('core::actions.info')</a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="info" aria-labelledby="info-tab" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6 col-12">
                                    {!! form_field($form,'active') !!}
                                </div>
                                <div class="col-md-6 col-12">
                                    {!! form_field($form,'blocked') !!}
                                </div>
                                <div class="col-md-6 col-12">
                                    {!! form_field($form,'ip_address') !!}
                                </div>
                                <div class="col-md-6 col-12">
                                    {!! form_field($form,'created_at') !!}
                                </div>
                                <div class="col-md-6 col-12">
                                    {!! form_field($form,'blocked_from') !!}
                                </div>
                                <div class="col-md-6 col-12">
                                    {!! form_field($form,'blocked_to') !!}
                                </div>
                            </div>

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
