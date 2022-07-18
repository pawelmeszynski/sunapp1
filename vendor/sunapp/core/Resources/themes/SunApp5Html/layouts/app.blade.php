<!DOCTYPE html>
<html class="loading" lang="@locale" lang-fallback="@localeFallback" data-textdirection="ltr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="keywords" content="@get('keywords')">
    <meta name="description" content="@get('description')">
    <meta name="author" content="@get('author')">
    <meta name="csrf-token" content="@csrfToken">
    <meta name="theme-assets-url" content="@asset('')">
    <meta name="base-url" content="{{url('/')}}">
    <meta name="admin-base-url" content="{{url(env('APP_PREFIX',''))}}"/>

    <title>@get('title', config('app.name', 'SunApp'))</title>

    <link rel="shortcut icon" type="image/x-icon" href="@asset('images/ico/favicon.ico')">
    <meta name="theme-color" content="#ffd800">
    <meta name="msapplication-navbutton-color" content="#ffd800">
    <meta name="apple-mobile-web-app-status-bar-style" content="#ffd800">
    <meta name="msapplication-TileColor" content="#ffd800">
    <link rel="apple-touch-icon" sizes="57x57" href="@asset('images/ico/apple-icon-57x57.png')">
    <link rel="apple-touch-icon" sizes="60x60" href="@asset('images/ico/apple-icon-60x60.png')">
    <link rel="apple-touch-icon" sizes="72x72" href="@asset('images/ico/apple-icon-72x72.png')">
    <link rel="apple-touch-icon" sizes="76x76" href="@asset('images/ico/apple-icon-76x76.png')">
    <link rel="apple-touch-icon" sizes="114x114" href="@asset('images/ico/apple-icon-114x114.png')">
    <link rel="apple-touch-icon" sizes="120x120" href="@asset('images/ico/apple-icon-120x120.png')">
    <link rel="apple-touch-icon" sizes="144x144" href="@asset('images/ico/apple-icon-144x144.png')">
    <link rel="apple-touch-icon" sizes="152x152" href="@asset('images/ico/apple-icon-152x152.png')">
    <link rel="apple-touch-icon" sizes="180x180" href="@asset('images/ico/apple-icon-180x180.png')">
    <link rel="icon" type="image/png" sizes="192x192" href="@asset('images/ico/android-icon-192x192.png')">
    <link rel="icon" type="image/png" sizes="32x32" href="@asset('images/ico/favicon-32x32.png')">
    <link rel="icon" type="image/png" sizes="96x96" href="@asset('images/ico/favicon-96x96.png')">
    <link rel="icon" type="image/png" sizes="16x16" href="@asset('images/ico/favicon-16x16.png')">
    <meta name="msapplication-TileImage" content="@asset('images/ico/ms-icon-144x144.png')">
    <link rel="manifest" href="@asset('images/ico/manifest.json')">

    {{-- Include core + vendor Styles --}}
    @partial('styles')

    {{-- Include page Style --}}
    @styles()
    @yield('styles')

</head>
<?php $menu_cookie = ((isset($_COOKIE['sunapp_menu_open']) && $_COOKIE['sunapp_menu_open'] == 1) || !isset($_COOKIE['sunapp_menu_open'])); ?>
<body
    class="vertical-layout 2-columns footer-static pace-done navbar-sticky semi-dark-layout_ vertical-menu-modern @if($menu_cookie) menu-expanded @else menu-collapsed @endif"
    data-menu="vertical-menu-modern" data-col="2-columns">
{{-- Include Sidebar --}}
@partial('sidebar', ['menu_cookie' => $menu_cookie])

<!-- BEGIN: Content-->
<div class="app-content content">
    <!-- BEGIN: Header-->
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>

    {{-- Include Navbar --}}
    @partial('navbar')

    <div class="content-wrapper pb-0">

        @partial('flash')
    </div>
    <div class="">

        @content()
    </div>


</div>
<!-- End: Content-->

<div class="sidenav-overlay"></div>
<div class="drag-target"></div>

{{-- include footer --}}
@partial('footer')

{{-- include default scripts --}}
@partial('scripts')

{{-- Include page script --}}
@scripts()
@yield('scripts')
</body>

</html>
