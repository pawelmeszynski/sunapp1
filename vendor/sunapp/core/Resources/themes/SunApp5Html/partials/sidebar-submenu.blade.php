{{-- For submenu --}}
<ul class="menu-content">
    @foreach($items as $submenu)
        <li @lm_attrs($submenu) @lm_endattrs>
            <a href="{{ $submenu->url() }}@if($submenu->add_timestamp)?ts={{time()}} @endif">
                <i class="{{ $submenu->icon }}"></i>
                <span class="menu-title" data-i18n="">{{ $submenu->title }}</span>
                {!! $submenu->afterHTML  !!}
            </a>
            @if ($submenu->hasChildren())
                @partial('sidebar-submenu', ['items' => $submenu->children()])
            @endif
        </li>
        @if($submenu->divider)
            <li{!! Lavary\Menu\Builder::attributes($menu->divider) !!}></li>
        @endif
    @endforeach
</ul>
