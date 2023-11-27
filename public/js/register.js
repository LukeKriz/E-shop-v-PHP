/*
 * Funkce napojí div na CheckBox tak, aby se zobrazoval jen když je zaškrtnutý.
 */
function toggleFormControls(checkBoxId, divId, inverse)
{
    $("#" + checkBoxId).click(function (){
        $("#" + divId).slideToggle(500);
    });
    const checked = $("#" + checkBoxId).prop('checked');
    if ((!inverse && !checked) || (inverse && checked))
        $("#" + divId).hide();
}

$(document).ready(function () {
    toggleFormControls("create_account", "create-account-controls");
    toggleFormControls("omit_delivery_address", "delivery-address-controls", true);
});