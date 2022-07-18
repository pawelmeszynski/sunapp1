@php
    $assets = app('theme')->asset();
    // Vendor Styles -->
    $assets->themePath(false)->add('fonts',
        'https://fonts.googleapis.com/css?family=Montserrat:300,400,500,600');
    $assets->themePath()->add('vendors', '../app-assets/vendors/css/vendors.min.css');
    $assets->themePath()->add('prism', '../app-assets/vendors/css/ui/prism.min.css', ['vendors']);

    // Theme Styles -->
    $assets->themePath()->add('bootstrap','../app-assets/css/bootstrap.min.css',['vendors']);
    $assets->themePath()->add('bootstrap-extended','../app-assets/css/bootstrap-extended.min.css',['bootstrap']);
    $assets->themePath()->add('colors','../app-assets/css/colors.min.css',['bootstrap-extended']);
    $assets->themePath()->add('components','../app-assets/css/components.min.css',['colors']);

    $assets->themePath()->add('nestable','../app-assets/vendors/css/nestable/jquery.nestable.min.css',['bootstrap']);
    $assets->themePath()->add('sweet-alert','../app-assets/vendors/css/extensions/sweetalert2.min.css',['bootstrap']);
    $assets->themePath()->add('toastr','../app-assets/vendors/css/extensions/toastr.css',['bootstrap']);
    $assets->themePath()->add('select2','../app-assets/vendors/css/forms/select/select2.min.css',['bootstrap']);
    $assets->themePath()->add('daterangepickercss','../app-assets/vendors/css/daterangepicker/daterangepicker.min.css',['bootstrap']);
    $assets->themePath()->add('selectizecss','../app-assets/vendors/css/selectize/selectize.css',['bootstrap']);
    $assets->themePath()->add('jquery-treegrid','../app-assets/vendors/css/tables/treegrid/jquery.treegrid.css',['bootstrap']);
    $assets->themePath()->add('bootstrap-table','../app-assets/vendors/css/tables/bootstrap-table/bootstrap-table.min.css',['jquery-treegrid']);

    $assets->themePath()->add('vue-treeselect','../app-assets/vendors/css/treeselect/vue-treeselect.min.css',['bootstrap']);

    // Page Styles -->
    $assets->themePath()->add('form-validation','../app-assets/css/plugins/forms/validation/form-validation.css',['components']);
    $assets->themePath()->add('menu','../app-assets/css/core/menu/menu-types/vertical-menu.min.css',['form-validation']);

    $assets->themePath()->add('module','css/module.css',['menu']);
    $assets->themePath()->add('main','css/main.css',['module']);
@endphp
