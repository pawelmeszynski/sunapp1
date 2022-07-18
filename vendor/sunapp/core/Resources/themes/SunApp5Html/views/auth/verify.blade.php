@layout('auth')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">@lang('auth.verify_email')</div>

                <div class="card-body">
                    @if (session('resent'))
                        <div class="alert alert-success" role="alert">
                            @lang('auth.verify_link_sent')
                        </div>
                    @endif

                        @lang('auth.verify_email_message')
                        @lang('auth.not_receive_email'), <a href="{{ route('SunApp::verification.resend') }}">@lang('auth.click_to_resend')</a>.
                </div>
            </div>
        </div>
    </div>
</div>
