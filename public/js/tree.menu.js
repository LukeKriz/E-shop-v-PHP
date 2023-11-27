/**
 * Interaktivní stromové menu
 * Základ z http://cssdeck.com/labs/twitter-bootstrap-plain-collapsible-tree-menu
 */
$(document).ready(function () {
    // Kliknutí na kategorii - otevření podmenu a uložení otevřené položky
    $('nav.menu-tree label.tree-toggler').click(function () {
        window.sessionStorage.setItem('menu_opened', $(this).data('path').substr(1));
        $(this).parent().children('ul.tree').toggle(300);
    });

    // Kliknutí na položku - uložení otevřené položky
    $('nav.menu-tree a').click(function () {
        window.sessionStorage.setItem('menu_opened', $(this).data('path').substr(1));
    });

    // Kliknutí na všechny položky, díky čemuž je menu v počátečním stavu zavřené
    $('nav.menu-tree label.tree-toggler').parent().children('ul.tree').toggle(0);

    /**
     * Pokusí se načíst posledně otevřenou položku a rozbalit menu tam, kde uživatel naposledy skončil
     */
    let item = window.sessionStorage.getItem('menu_opened');
    if (item) {
        item = item.split('/');
        for (let i = 0; i < item.length; i++) {
            $("nav.menu-tree label[data-path$='/" + item[i] + "']").parent().children('ul.tree').show();
        }
    }
});