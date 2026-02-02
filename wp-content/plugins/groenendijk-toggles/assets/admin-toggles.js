jQuery(function ($) {
    const $wrap = $("#gda-toggle-items-wrap");
    const $add = $("#gda-add-item");
    const tpl = $("#tmpl-gda-toggle-item-row").html();

    function nextIndex() {
        // tel huidige rijen, gebruik dat als index
        return $wrap.find(".gda-toggle-item-row").length;
    }

    $add.on("click", function () {
        const idx = nextIndex();
        const html = tpl.replaceAll("{{INDEX}}", idx);
        $wrap.append(html);
    });

    $wrap.on("click", ".gda-remove-item", function () {
        $(this).closest(".gda-toggle-item-row").remove();

        // herindexeer names zodat WP netjes een array krijgt
        $wrap.find(".gda-toggle-item-row").each(function (i) {
            const $input = $(this).find('input[type="text"]');
            const name = $input.attr("name");
            if (!name) return;

            // gda_toggle_items[3][text] => gda_toggle_items[i][text]
            $input.attr("name", "gda_toggle_items[" + i + "][text]");
        });
    });
});
