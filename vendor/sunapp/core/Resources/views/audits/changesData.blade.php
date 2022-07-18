@php
    $audit = new $audit();
    $excludes_fields = config('config.excluded_history_element_fields');
@endphp
@foreach ($changes as $key => $item)
    @php
        if (in_array($key, $excludes_fields)) {
            if (is_string($key)) {
                echo $key . '<br>';
            }
            continue;
        }
    @endphp
    @if ($audit->isJson($item))
        @for ($i = 0; $i < $tabs; $i++)
            &nbsp&nbsp&nbsp&nbsp
        @endfor
        {{$key}} => [ <br>
        @if (!$tabs)
            @php
                $tabs = 1;
            @endphp
        @endif
        @if($function == 'new')
            {{html_entity_decode($audit->getNewValueAttribute($item, $tabs))}}
        @elseif($function == 'old')
            {{html_entity_decode($audit->getOldValueAttribute($item, $tabs))}}
        @endif
        @for ($i = 0; $i < $tabs; $i++)
            &nbsp&nbsp&nbsp&nbsp
        @endfor
        ]<br>
    @else
        @for ($i = 0; $i < $tabs; $i++)
            &nbsp&nbsp&nbsp&nbsp
        @endfor
        @php
            $content = html_entity_decode($item);
            $content = strip_tags($content);
            $content = str_replace(["\\n", "\\r", "\\t"], '', $content);
        @endphp
        {{$key}} => {{$content}}<br>
    @endif
@endforeach