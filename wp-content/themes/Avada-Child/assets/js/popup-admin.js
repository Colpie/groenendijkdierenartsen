jQuery(function ($) {
    let frame;

    $(document).on("click", "#ae-popup-upload", function (e) {
        e.preventDefault();

        if (frame) {
            frame.open();
            return;
        }

        frame = wp.media({
            title: "Kies afbeelding",
            button: { text: "Gebruik deze" },
            multiple: false,
            library: { type: ["image"] },
        });

        frame.on("select", function () {
            const att = frame.state().get("selection").first().toJSON();
            $("#ae_popup_icon_id").val(att.id);
            $("#ae-popup-icon-preview").attr("src", att.url).show();
            $("#ae-popup-remove").show();
        });

        frame.open();
    });

    $(document).on("click", "#ae-popup-remove", function (e) {
        e.preventDefault();
        $("#ae_popup_icon_id").val("");
        $("#ae-popup-icon-preview").attr("src", "").hide();
        $("#ae-popup-remove").hide();
    });
});
