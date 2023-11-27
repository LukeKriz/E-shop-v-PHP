/**
 * Odstraňování náhledů AJAXem ve správě produktů
 */
$(document).ready(function () {
    $("#product-images-administration a").click(function (e) {
        e.preventDefault();

        if (confirm('Opravdu si přejete odstranit vybraný náhled?')) {
            const url = $(this).attr('href');
            const self = this;

            $.get(url, function (data) {
                // Posun indexy za odstraněným obrázkem o 1 dozadu
                const oldUrls = $(self).parent().nextAll().children("a");
                for (let i = 0; i < $(oldUrls).length; i++) {
                    const splitPath = $(oldUrls[i]).attr('href').split("/");
                    splitPath[splitPath.length - 1]--;
                    $(oldUrls[i]).attr('href', splitPath.join("/"));
                }
                $(self).parent().remove();
            }).fail(function (data) {
                alert("Při odstraňování obrázku došlo k chybě." + data.reponseText);
            });
        }
    });
});