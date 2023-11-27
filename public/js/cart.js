/**
 * JavaScript k obsluze košíku
 */
$(document).ready(function () {
    // Odstranění položky v košíku
    $("#cart-management .remove-button").click(function () {
        $(this).prev().val(0);
    });
});