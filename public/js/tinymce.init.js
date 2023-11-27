function initTinyMce(textAreaName)
{
    tinymce.init({
        selector: "textarea[name=" + textAreaName + "]",
        plugins: [
            "advlist autolink lists link image charmap print preview anchor",
            "searchreplace visualblocks code fullscreen",
            "insertdatetime media table contextmenu paste"
        ],
        toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image",
        entities: "160,nbsp",
        entity_encoding: "named",
    });
}