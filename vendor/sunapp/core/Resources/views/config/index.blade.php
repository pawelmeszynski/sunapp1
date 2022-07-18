<div id="app" class="sg-application">
    <div class="app-content">
        <div id="chart" class="sg-application" >
            <div class="app-content">
                <section class="row flexbox-container">
                    <div class="col-xl-12 col-11 d-flex justify-content-center">
                        <div class="card bg-authentication rounded-0 mb-0">
                            <div class="row m-0">
                                <div class="col-lg-12 col-12 p-0">
                                    <div class="card-header pb-1">
                                        <div class="card-title">
                                            <h4 class="mb-0">@lang('core::user.verify_2fa_google')</h4>
                                        </div>
                                    </div>
                                    <div class="card-content">
                                        @if(Session::has('res2fa'))
                                            <div class="alert alert-warning">
                                                {!! Session::get('res2fa') !!}
                                            </div>
                                        @endif
                                    
                                        <table class="table table-border">
                                            <tbody>
                                                <tr>
                                                    <td>@lang('core::config.global_2fa')</td>
                                                    <td>
                                                        <a href="{{route('SunApp::config.2fa.global_enable', ['status'=> ($globalStatus2fa == true) ? false : true])}}" class="btn btn-primary">@lang(($globalStatus2fa == true) ? 'core::user.button_disable' : 'core::user.button_enable' )</a>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>@lang('core::config.generate_for_all_2fa')</td>
                                                    <td><a href="{{route('SunApp::config.2fa.generate_all')}}" class="btn btn-warning">@lang('core::config.generate')</a></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
