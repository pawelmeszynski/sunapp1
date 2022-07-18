<div class="sg-app-details show items-ordering" data-category-id="{{$item->id}}" data-token="{{ csrf_token() }}">
    <div ref="form_wrapper">
        <div class="sg-detail-header">
            <div class="sg-header-left d-flex align-items-center mb-1">
                <span class="go-back mr-1" @click.prevent="clearForm('fromOrdering')"><i
                        class="feather icon-arrow-left font-medium-4"></i></span>
                <h3>
                    <span class="text-muted">#{{$item->id}}</span>
                    <span>{{$item->name}} - @lang('core::actions.items_order')</span>
                </h3>
            </div>
        </div>
        <div class="sg-scroll-area">
            <div class="row">
                <div class="col-12">
                    <div class="form-body">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="ordering-tab" data-toggle="tab" href="#ordering"
                                   aria-selected="true" aria-controls="related"
                                   role="tab">@lang('core::actions.items_order')</a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane active" id="ordering" aria-labelledby="ordering-tab" role="tabpanel">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="sg-user-list list-group">
                                            <div class="dd dd-ordering">
                                                <ol class="dd-list table">
                                                    @foreach($categoriables->get() as $categoriable)
                                                        <li class="dd-item file_element dd-item-ordering">
                                                            <div class="dd-handle-wrapper">
                                                                <div
                                                                    class="dd-handle has-tooltip has-tooltip--down has-tooltip--left"
                                                                    >
                                                                    <i class="feather icon-maximize-2"></i>
                                                                    <span
                                                                        class="tooltip-text">@lang('core::actions.move')</span>
                                                                </div>
                                                            </div>
                                                            <div class="media-list-info">
                                                                <div class="media-list-cell">
                                                                    <h5 class="list-group-item-heading text-bold-600 mb-0">
                                                                        <span class="form-group-translation">
                                                                            <span class="text-muted">#{{ $categoriable->id }}</span>
                                                                            <span>{{ $categoriable->name }}</span>
                                                                        </span>
                                                                    </h5>
                                                                </div>
                                                            </div>
                                                            <input type="hidden" name="items_ordering[]"
                                                                   value="{{ $categoriable->id }}">
                                                            <div class="clearfix"></div>
                                                        </li>
                                                    @endforeach
                                                </ol>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <div class="sidenav-overlay"></div>
    <div class="drag-target"></div>
    </div>
</div>
