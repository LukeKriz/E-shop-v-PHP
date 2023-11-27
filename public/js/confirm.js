/**
 * Přidá odkazům s data-atributem data-confirm potvrzovací dialog se zprávou v tomto atributu
 */
$(document).ready(function () {
    $("a[data-confirm]").click(function (e) {
        if (!confirm($(this).data('confirm')))
            e.preventDefault();
    });
});