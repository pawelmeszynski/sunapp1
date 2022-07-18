{{-- For submenu --}}
@foreach($items as $submenu)
    <a @lm_attrs($submenu->link) class="dropdown-item" @lm_endattrs href="{{ $submenu->url() }}"><i
            class="{{ $submenu->icon }}"></i> {{ $submenu->title }}{!! $submenu->afterHTML  !!}</a>

    @if ($submenu->hasChildren())
        <div class="dropdown-menu dropdown-menu-right">
            @partial('navbar-submenu', ['items' => $submenu->children()])
        </div>
    @endif

    @if($submenu->divider)
        <div class="dropdown-divider" {!! Lavary\Menu\Builder::attributes($submenu->divider) !!}></div>
    @endif

@endforeach
