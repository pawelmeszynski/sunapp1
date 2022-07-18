@extends('partials.card')
@section('content')
    @lang('auth.logged_in')

    @if (app()->isProduction())
    <script src="@asset('js/vue.js')"></script>
    @else
        <script src="@asset('js/vue-dev.js')"></script>
    @endif
        <script src="@asset('js/axios.min.js')"></script>

    @theme_asset('base-vue', '../assets/js/vue-base.js', ['app'])
    @theme_asset('dashboard', '../modules/core/assets/js/dashboard.js', ['base-vue'])
@endsection
