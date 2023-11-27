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
 * Hodnocení produktu pomocí hvězdiček
 */

HTMLCollection.prototype.indexOf = [].indexOf;

$(document).ready(function() {
    $("#write-review").click(showReviewForm);
    const stars = $(".review-star");
    stars.click(setStars);
    stars.mouseenter(starHover);
    stars.mouseleave(returnStarState);
    setStars.call(stars[2]);
});

/**
 * Rozsvítí hvězdy zleva až do té, na které je kurzor myši
 */
function starHover() {
    const stars = $(".review-star");
    const selectedStars = this.parentElement.children.indexOf(this) + 1;
    stars.addClass("far");
    stars.removeClass("fa");
    for (let i = 0; i < selectedStars; i++) {
        $(stars[i]).removeClass("far");
        $(stars[i]).addClass("fa");
    }
}

/**
 * Zhasne hvězdy tak, aby byl zobrazený původní (nakliknutý) stav
 */
function returnStarState() {
    const stars = $(".review-star");
    stars.addClass("far");
    stars.removeClass("fa");

    for (let i = 0; i < $("#rating")[0].value; i++) {
        $(stars[i]).removeClass("far");
        $(stars[i]).addClass("fa");
    }
}

/**
 * Uloží nakliknutou hodnotu na hvězdách do skrytého pole
 */
function setStars() {
    const selectedStars = this.parentElement.children.indexOf(this);
    $("#rating")[0].value = selectedStars + 1;

    returnStarState();
}

/**
 * Zobrazí/skryje formulář pro recenze
 */
function showReviewForm() {
    $("#review-form").slideToggle(300);
}