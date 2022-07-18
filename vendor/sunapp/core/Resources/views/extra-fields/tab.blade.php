@if(method_exists($item, 'extraFields'))
    @if($item->extraFields()->count())
        <li class="nav-item">
            <a class="{{ $classList ?? 'nav-link' }}" id="extraFields-tab" data-toggle="tab" href="#extraFields"
               aria-controls="extraFields" role="tab">@lang('core::module.extra_fields')</a>
        </li>
    @endif
@endif
