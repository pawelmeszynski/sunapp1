<?php app('theme')->asset()->themePath()->add('datatable-pdfmake-js', 'vendors/js/tables/datatable/pdfmake.min.js') ?>
<?php app('theme')->asset()->themePath()->add('datatable-vfs_fonts-js', 'vendors/js/tables/datatable/vfs_fonts.js') ?>
<?php app('theme')->asset()->themePath()->add('datatable-js', 'vendors/js/tables/datatable/datatables.min.js') ?>
<?php app('theme')->asset()->themePath()->add('datatable-buttons-js', 'vendors/js/tables/datatable/datatables.buttons.min.js') ?>
<?php app('theme')->asset()->themePath()->add('datatable-buttons-html5-js', 'vendors/js/tables/datatable/buttons.html5.min.js') ?>
<?php app('theme')->asset()->themePath()->add('datatable-buttons-print-js', 'vendors/js/tables/datatable/buttons.print.min.js') ?>
<?php app('theme')->asset()->themePath()->add('datatable-buttons-server-js', 'js/scripts/buttons.server-side.js') ?>
<?php app('theme')->asset()->themePath()->add('datatable-buttons-bootstrap-js', 'js/scripts/buttons.bootstrap.min.js') ?>
<?php app('theme')->asset()->themePath()->add('datatable-bootstrap4-js', 'vendors/js/tables/datatable/datatables.bootstrap4.min.js') ?>

<?php app('theme')->asset()->themePath()->add('datatable-css', 'vendors/css/tables/datatable/datatables.min.css', ['vendors']) ?>
(function(window,$){
    window.LaravelDataTables=window.LaravelDataTables||{};
    window.LaravelDataTables["%1$s"]=$("#%1$s").DataTable(%2$s);
})(window,jQuery);

