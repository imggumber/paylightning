jQuery(document).ready(function ($) {
    jQuery('#speed-transactions-table').DataTable({
        "paging": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "pageLength": 10
    });
});