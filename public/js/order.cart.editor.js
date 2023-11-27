/*  _____ _______         _                      _
 * |_   _|__   __|       | |                    | |
 *   | |    | |_ __   ___| |___      _____  _ __| | __  ___ ____
 *   | |    | | '_ \ / _ \ __\ \ /\ / / _ \| '__| |/ / / __|_  /
 *  _| |_   | | | | |  __/ |_ \ V  V / (_) | |  |   < | (__ / /
 * |_____|  |_|_| |_|\___|\__| \_/\_/ \___/|_|  |_|\_(_)___/___|
 *                                _
 *              ___ ___ ___ _____|_|_ _ _____
 *             | . |  _| -_|     | | | |     |
 *             |  _|_| |___|_|_|_|_|___|_|_|_|
 *             |_|                          _ _ _        LICENCE
 *        ___ ___    ___    ___ ___ ___ ___| | |_|___ ___
 *       |   | . |  |___|  |  _| -_|_ -| -_| | | |   | . |
 *       |_|_|___|         |_| |___|___|___|_|_|_|_|_|_  |
 *                                                   |___|
 *
 * IT ZPRAVODAJSTVÍ  <>  PROGRAMOVÁNÍ  <>  HW A SW  <>  KOMUNITA
 *
 * Tento zdrojový kód je součástí výukových seriálů na
 * IT sociální síti WWW.ITNETWORK.CZ
 *
 * Kód spadá pod licenci prémiového obsahu s omezeným
 * přeprodáváním a vznikl díky podpoře našich členů. Je určen
 * pouze pro osobní užití a nesmí být šířen. Může být použit
 * v jednom uzavřeném komerčním projektu, pro širší využití je
 * dostupná licence Premium commercial. Více informací na
 * http://www.itnetwork.cz/licence
 */

/**
 * Vyvolá dialog ke změně údajů zákazníka (v iframe)
 */
function changeBuyer(e) {
    e.preventDefault();
    $('#buyer-form').dialog('open', {position: [0, 0]});
    // Skrytí odesílacího tlačítka v iframe
    $("#buyer-form-frame").contents().find("#odeslat").hide();
}

/**
 * Obsluha vnějšího tlačítka, které kliká na skryté odesílací tlačítko v iframe
 */
function changePerson(e) {
    e.preventDefault();
    // Přivěšení události na přenačtení rámu (tedy odeslání formuláře)
    $("#buyer-form-frame").on('load', function (e) {
        const orderId = $("#order-items-table").data("order-id");
        // Formulář přesměroval na úvod, byl tedy korektně odeslán
        if ($("#buyer-form-frame")[0].contentWindow.location.href.indexOf("uvod") !== -1) {
            $("#buyer-form-frame")[0].src = "api/EshopModule-Products-OrderManagement/edit-person/" + orderId;
            $("#buyer-form").dialog('close');
            $.get("api/EshopModule-Products-OrderManagement/buyer-detail/" + orderId, function (data) {
                $("#buyer-detail").html(data); // Obnovení detailu zákazníka na objednávce
            }).fail(function () {
                alert("Při stahování změn ze serveru došlo k chybě. Aktualizujte stránku.")
            });
        }
    });
    // Kliknutí na tlačítko v rámu
    $("#buyer-form-frame").contents().find("#odeslat").click();
}

/**
 * Aktualizuje souhrnné informace o ceně objednávky
 * @param summary Data se souhrnnou cenou objednávky
 */
function changeOrderSummary(summary) {
    $.each(summary, function (index, value) {
        $("#order-summary-total")[0].querySelector("." + index).textContent = value;
    });
}

/**
 * Propagace změny v položce objednávky na server + přepočítání celkové ceny objednávky
 */
function changeOrderItem() {
    const quantity = $(this).prev().val();
    const productId = $(this).parent().parent().data("order-item-id");
    const orderId = $("#order-items-table").data("order-id");
    const self = this;
    if (quantity <= 0) {
        $(this).next().click();
        return;
    }
    $.get("api/EshopModule-Products-OrderManagement/edit-product/" + orderId + "/" + productId + "/" + quantity, function (data) {
        changeOrderSummary(data.summary);
        const product = data.product;
        $.each(product, function (index, value) {
            if (index !== "quantity")
                $(self).parent().parent().find("." + index).text(value);
        });
    }).fail(function (data) {
        alert("Při nahrávání změn na server došlo k chybě." + data.reponseText)
    });
}

/**
 * Odstraní položku z objednávky
 */
function removeOrderItem() {
    const productId = $(this).parent().parent().data("order-item-id");
    const orderId = $("#order-items-table").data("order-id");
    const self = this;
    // Nastavíme počet na 0 a server ji z objednávky vymaže
    $.get("api/EshopModule-Products-OrderManagement/edit-product/" + orderId + "/" + productId + "/0", function (data) {
        $(self).parent().parent().remove();
        changeOrderSummary(data.summary);
    }).fail(function (e) {
        alert("Při nahrávání změn na server došlo k chybě.")
    })
}

/**
 * Přidání položky do objednávky
 */
function addOrderItem(e) {
    const form = $("#add-product-form");
    $.post("api/EshopModule-Products-OrderManagement/add-product", form.serialize()).done(function (data) {
        // Duplikuje skrytý řádek
        const line = $(".order-item-template").clone();
        line.removeClass("order-item-template");
        line.data("order-item-id", data.product.product_id);

        $.each(data.product, function (index, value) {
            if (index !== "quantity") {
                line.find('.' + index).text(value);
            } else {
                line.find(".order-item-quantity").val(value);
                line.find(".order-item-change").click(changeOrderItem);
                line.find(".order-item-remove").click(removeOrderItem);
            }
        });
        line.data("order-item-id", data.product.product_id);

        $("#order-items-table > tbody").append(line);
        changeOrderSummary(data.summary);
    });
    e.preventDefault();
}

$(document).ready(function () {
    $('#change-buyer-button').click(changeBuyer);
    $('#buyer-form').dialog({width: 600, height: 500, autoOpen: false, position: [0, 0]});
    $("#buyer-form-submit").click(changePerson);

    const btnsItemChange = $(".order-item-change");
    btnsItemChange.click(changeOrderItem);
    const btnsItemRemove = $(".order-item-remove");
    btnsItemRemove.click(removeOrderItem);
    $("#add-product-form > .form-buttons > input").click(addOrderItem);
});