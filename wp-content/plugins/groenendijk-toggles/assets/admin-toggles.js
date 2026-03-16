jQuery(function ($) {
    const $pick = $("#gda-ug-pick");
    const $remove = $("#gda-ug-remove");
    const $input = $("#gda-ug-image");
    const $preview = $("#gda-ug-preview");

    let frame = null;

    $pick.on("click", function (e) {
        e.preventDefault();

        if (frame) {
            frame.open();
            return;
        }

        frame = wp.media({
            title: "Kies afbeelding",
            button: { text: "Gebruik deze afbeelding" },
            multiple: false,
            library: { type: "image" }
        });

        frame.on("select", function () {
            const attachment = frame.state().get("selection").first().toJSON();
            if (!attachment || !attachment.id) return;

            $input.val(attachment.id);

            const url =
                (attachment.sizes && attachment.sizes.medium && attachment.sizes.medium.url) ||
                (attachment.sizes && attachment.sizes.thumbnail && attachment.sizes.thumbnail.url) ||
                attachment.url;

            $preview.attr("src", url).show();
            $remove.show();
        });

        frame.open();
    });

    $remove.on("click", function (e) {
        e.preventDefault();
        $input.val("");
        $preview.attr("src", "").hide();
        $remove.hide();
    });
});