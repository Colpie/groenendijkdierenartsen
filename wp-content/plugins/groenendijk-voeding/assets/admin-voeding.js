jQuery(function ($) {
    function bindField(i) {
        const $pick = $("#gda-voeding-pick-" + i);
        const $remove = $("#gda-voeding-remove-" + i);
        const $input = $("#gda-voeding-image-" + i);
        const $preview = $("#gda-voeding-preview-" + i);

        let frame = null;

        $pick.on("click", function (e) {
            e.preventDefault();

            if (frame) {
                frame.open();
                return;
            }

            frame = wp.media({
                title: "Kies afbeelding " + i,
                button: { text: "Gebruik deze afbeelding" },
                multiple: false,
                library: { type: "image" },
            });

            frame.on("select", function () {
                const attachment = frame.state().get("selection").first().toJSON();
                if (!attachment || !attachment.id) return;

                $input.val(attachment.id);

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

    bindField(1);
    bindField(2);
});