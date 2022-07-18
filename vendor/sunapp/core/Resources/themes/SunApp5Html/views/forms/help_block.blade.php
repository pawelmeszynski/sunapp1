@if ($options['help_block']['text'] && !$options['is_child'])
    <small {!! $options['help_block']['helpBlockAttrs'] !!}>
        {{$options['help_block']['text']}}
    </small>
@endif
