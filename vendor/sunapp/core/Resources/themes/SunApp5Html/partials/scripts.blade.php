<script>
    var themeAssetsUrl = document.querySelector("meta[name='theme-assets-url']").getAttribute('content');
    var baseUrl = document.querySelector("meta[name='base-url']").getAttribute('content');
    var adminBaseUrl = document.querySelector("meta[name='admin-base-url']").getAttribute('content');
    var currentLang = "{{(session('content_lang')?session('content_lang')->code:'')}}";
</script>

@php
    $assets = app('theme')->asset();
    // Vendor Scripts
    $assets->themePath()->add('vendors','../app-assets/vendors/js/vendors.min.js');
    $assets->themePath()->add('prism','../app-assets/vendors/js/ui/prism.min.js',['vendors']);
    $assets->themePath()->add('nestable','../app-assets/vendors/js/nestable/jquery.nestable.min.js',['vendors']);
    $assets->themePath()->add('ckeditor','../app-assets/vendors/js/editors/ckeditor/ckeditor.js',['vendors']);
    $assets->themePath()->add('sweetalert2','../app-assets/vendors/js/extensions/sweetalert2.all.min.js',['vendors']);
    $assets->themePath()->add('toastr','../app-assets/vendors/js/extensions/toastr.min.js',['vendors']);
    $assets->themePath()->add('select2','../app-assets/vendors/js/forms/select/select2.min.js',['vendors']);
    $assets->themePath()->add('cookies-js','../app-assets/vendors/js/cookies/js.cookie.min.js',['vendors']);
    $assets->themePath()->add('momentjs','../app-assets/vendors/js/moment/moment.min.js',['vendors']);
    $assets->themePath()->add('daterangepickerjs','../app-assets/vendors/js/daterangepicker/daterangepicker.min.js',['vendors']);
    $assets->themePath()->add('selectizejs','../app-assets/vendors/js/selectize/selectize.js',['vendors']);
    $assets->themePath()->add('jquery-treegrid','../app-assets/vendors/js/tables/treegrid/jquery.treegrid.min.js',['vendors']);
    $assets->themePath()->add('bootstrap-table','../app-assets/vendors/js/tables/bootstrap-table/bootstrap-table.min.js',['jquery-treegrid']);
    $assets->themePath()->add('bootstrap-table-treegrid','../app-assets/vendors/js/tables/bootstrap-table/bootstrap-table-treegrid.min.js',['bootstrap-table']);

    $assets->themePath()->add('vue-treeselect','../app-assets/vendors/js/treeselect/vue-treeselect.umd.min.js',['base-vue']);
    $assets->themePath()->add('vue-treeselect-js','js/vue-treeselect.js',['base-vue']);

    // Theme Scripts
    $assets->themePath()->add('app_menu','../app-assets/js/core/app-menu.min.js',['vendors']);
    $assets->themePath()->add('app','../app-assets/js/core/app.min.js',['app_menu']);

    // Page Scripts
    $assets->themePath()->add('module','js/module.js',['app']);
    $assets->themePath()->add('main','js/main.js',['app']);
@endphp

