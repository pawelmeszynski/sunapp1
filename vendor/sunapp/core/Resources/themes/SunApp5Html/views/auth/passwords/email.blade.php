@layout('auth')

<section class="row flexbox-container">
    <div class="col-xl-7 col-10 d-flex justify-content-center">
        <div class="card bg-authentication rounded-0 mb-0 w-100">
            <div class="row m-0">
                <div class="col-lg-6 d-lg-block d-none text-center align-self-center p-0">
                    <img src="@asset('../app-assets/images/pages/reset-password.png')" alt="branding logo">
                </div>
                <div class="col-lg-6 col-12 p-0">
                    <div class="card rounded-0 mb-0 px-2">
                        <div class="card-header pb-1">
                            <div class="card-title">
                                <h4 class="mb-0">@lang('auth.reset_password')</h4>
                            </div>
                        </div>
                        {{--ToDo::Ostylować komunikat--}}
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif
                        <p class="px-2">@lang('auth.reset_password_message')</p>
                        <div class="card-content">
                            <div class="card-body pt-1">
                                <form method="POST" action="{{ route('SunApp::password.email') }}">
                                    @csrf
                                    <fieldset class="form-label-group">
                                        <input type="text" class="form-control @error('email') is-invalid @enderror" id="user-email" placeholder="@lang('auth.email')" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                                        <label for="user-email">@lang('auth.email')</label>
                                        @error('email')
                                        <span class="invalid-feedback" role="alert">
                                        {{ $message }}
                                        </span>
                                        @enderror
                                    </fieldset>

                                    <div class="row pt-2">
                                        <div class="col-12 col-md-6 mb-1">
                                            <a href="{{ route('SunApp::login') }}" class="btn btn-outline-primary btn-block px-0">@lang('auth.back_to_login')</a>
                                        </div>
                                        <div class="col-12 col-md-6 mb-1">
                                            <button type="submit" class="btn btn-primary btn-block px-0">@lang('auth.send_password_link')</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{--<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Reset Password') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('SunApp::password.email') }}">
                        @csrf

                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('E-Mail Address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Send Password Reset Link') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>--}}
