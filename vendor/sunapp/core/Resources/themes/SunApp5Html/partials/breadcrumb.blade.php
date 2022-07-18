<div class="content-header row">
    <div class="content-header-left col-12 mb-2">
        <div class="row breadcrumbs-top">
            <div class="col-12">
                @php
                    $name = '';
                    $route = request()->route();
                    if($route) {
                        $route_name = explode('.',$route->getName());
                        if(\Lang::has('core::actions.'.end($route_name))) $name = trans('core::actions.'.end($route_name));
                    }
                @endphp
                <h2 class="content-header-title float-left mb-0">@get('module.title',$name)</h2>
                <div class="breadcrumb-wrapper col-12">
                    @if(Menu::get('AppSideBar') && Menu::get('AppSideBar')->active() && $breadcrumbs = Menu::get('AppSideBar')->crumbMenu()->all())
                        <ol class="breadcrumb">
                            {{-- this will load breadcrumbs dynamically from controller --}}
                            @foreach ($breadcrumbs as $breadcrumb)
                                <li class="breadcrumb-item">
                                    @if(isset($breadcrumb->link))
                                        <a href="{{ $breadcrumb->url() }}">
                                    @endif
                                        {{ $breadcrumb->title }}
                                    @if(isset($breadcrumb->link))
                                        </a>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>
