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
 * Změna stavu objednávky pomocí AJAXu
 */
$(document).ready(function () {
    $.get("api/EshopModule-Products-OrderManagement/order-states", function (orderStates) {
        const lastCellsInRow = $(".change-state");
        const flexDiv = $('<div class="d-flex"></div>');
        const select = $('<select class="form-control w-auto"></select>');
        const submitButton = $('<button class="ml-1 btn btn-outline-danger"></button>');
        submitButton.attr("type", "button");
        submitButton.click(changeOrderState);
        submitButton.append('<i class="fa fa-pencil-alt"></i>');

        // Přidání selectů na jednotlivé řádky tabulky
        for (const index in orderStates) {
            if (orderStates.hasOwnProperty(index)) {
                const option = $("<option></option>");
                option.attr("value", orderStates[index]);
                option.text(index);
                select.append(option);
            }
        }
        flexDiv.append(select);
        flexDiv.append(submitButton);
        lastCellsInRow.append(flexDiv);

        // Výběr stavu objednávky v příslušných selectech
        const selects = $("td.change-state select");
        for (let i = 0; i < selects.length; i++) {
            const state = $(selects[i]).parent().parent().parent().data("order-state");
            $(selects[i]).find("option[value='" + state + "']").attr("selected", "");
        }
    })
});

/**
 * Změní stav objednávky
 */
function changeOrderState() {
    const orderId = $(this).parent().parent().parent().data("order-id");
    const state = $(this).prev().val();
    const self = this;
    $.get("api/EshopModule-Products-OrderManagement/set-state/" + orderId + "/" + state, function (data) {
        $(self).parent().parent().prev().find(".invoice-link").text(data); // Vepsání vygenerovaného čísla faktury do tabulky
        alert("Úspěšně změněno. Zákazníkovi byl odeslán email.");
    }).fail(function (data) {
        alert("Došlo k chybě. Stav nebyl změněn. Důvod: " + data.responseText);
    });
}
