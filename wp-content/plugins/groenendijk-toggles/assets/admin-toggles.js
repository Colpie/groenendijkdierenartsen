jQuery(function ($) {
    const $input = $("#gda-ug-gallery-ids");
    const $preview = $("#gda-ug-gallery-preview");
    const $pick = $("#gda-ug-gallery-pick");
    const $addOne = $("#gda-ug-gallery-addone");
    const $clear = $("#gda-ug-gallery-clear");

    function getIds() {
        const raw = ($input.val() || "").trim();
        if (!raw) return [];
        return raw.split(",").map(v => parseInt(v, 10)).filter(Boolean);
    }

    function setIds(ids) {
        const clean = Array.from(new Set(ids.map(n => parseInt(n, 10)).filter(Boolean)));
        $input.val(clean.join(","));
        $clear.toggle(clean.length > 0);
        return clean;
    }

    function addThumb(id, url) {
        const $thumb = $(`
      <div class="gda-ug-thumb" data-id="${id}" style="position:relative;">
        <img src="${url}" style="width:110px;height:auto;border:1px solid #ddd;border-radius:6px;display:block;" />
        <button type="button" class="button gda-ug-thumb-remove"
          style="position:absolute;top:6px;right:6px;line-height:1;padding:2px 6px;" title="Verwijderen">×</button>
      </div>
    `);
        $preview.append($thumb);
    }

    function render(ids) {
        $preview.empty();
        if (!ids.length) {
            $clear.hide();
            return;
        }

        let remaining = ids.length;
        const map = {};

        ids.forEach(function (id) {
            const att = wp.media.attachment(id);
            att.fetch().then(function () {
                map[id] = att.toJSON();
                remaining--;
                if (remaining === 0) {
                    ids.forEach(function (id2) {
                        const a = map[id2];
                        const url =
                            (a && a.sizes && a.sizes.thumbnail && a.sizes.thumbnail.url) ||
                            (a && a.url) ||
                            "";
                        if (url) addThumb(id2, url);
                    });
                    $clear.show();
                }
            });
        });
    }

    // 1) Kies afbeeldingen (multi) = vervangt selectie (zoals WP standaard)
    $pick.on("click", function (e) {
        e.preventDefault();

        const frame = wp.media({
            title: "Kies afbeeldingen",
            button: { text: "Gebruik geselecteerde afbeeldingen" },
            multiple: true,
            library: { type: "image" },
        });

        frame.on("select", function () {
            const selection = frame.state().get("selection");
            const ids = [];
            selection.each(function (item) {
                const a = item.toJSON();
                if (a && a.id) ids.push(a.id);
            });

            const clean = setIds(ids);
            render(clean);
        });

        frame.open();
    });

    // 2) Voeg 1 foto toe (single) = append zonder dat je ooit alles opnieuw moet selecteren ✅
    $addOne.on("click", function (e) {
        e.preventDefault();

        const frame = wp.media({
            title: "Voeg 1 foto toe",
            button: { text: "Voeg toe" },
            multiple: false,
            library: { type: "image" },
        });

        frame.on("select", function () {
            const a = frame.state().get("selection").first().toJSON();
            if (!a || !a.id) return;

            const current = getIds();
            current.push(a.id);

            const clean = setIds(current);

            // snelle preview append (zonder alles te herladen)
            const url =
                (a.sizes && a.sizes.thumbnail && a.sizes.thumbnail.url) ||
                (a.sizes && a.sizes.medium && a.sizes.medium.url) ||
                a.url;

            // alleen append als hij nog niet bestond
            if ($preview.find('.gda-ug-thumb[data-id="' + a.id + '"]').length === 0) {
                addThumb(a.id, url);
            }

            $clear.show();
        });

        frame.open();
    });

    // remove 1 thumb
    $preview.on("click", ".gda-ug-thumb-remove", function (e) {
        e.preventDefault();
        const $thumb = $(this).closest(".gda-ug-thumb");
        const id = parseInt($thumb.data("id"), 10);

        const ids = getIds().filter(x => x !== id);
        setIds(ids);
        $thumb.remove();

        if (!ids.length) $clear.hide();
    });

    // clear all
    $clear.on("click", function (e) {
        e.preventDefault();
        setIds([]);
        $preview.empty();
        $clear.hide();
    });
});