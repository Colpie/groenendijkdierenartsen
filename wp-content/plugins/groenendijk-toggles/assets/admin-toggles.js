jQuery(function ($) {
    function bindImageField(index) {
        const $pick = $("#gda-ug-pick-" + index);
        const $remove = $("#gda-ug-remove-" + index);
        const $input = $("#gda-ug-image-" + index);
        const $preview = $("#gda-ug-preview-" + index);

        let frame = null;

        $pick.on("click", function (e) {
            e.preventDefault();

            if (frame) {
                frame.open();
                return;
            }

            frame = wp.media({
                title: "Kies afbeelding " + index,
                button: { text: "Gebruik deze afbeelding" },
                multiple: false,
                library: { type: "image" },
            });

            frame.on("select", function () {
                const attachment = frame.state().get("selection").first().toJSON();
                if (!attachment || !attachment.id) return;

                $input.val(attachment.id);

                // Kies een nette preview (medium indien beschikbaar)
                const url =
                    (attachment.sizes && attachment.sizes.medium && attachment.sizes.medium.url) ||
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
    }

    bindImageField(1);
    bindImageField(2);
});