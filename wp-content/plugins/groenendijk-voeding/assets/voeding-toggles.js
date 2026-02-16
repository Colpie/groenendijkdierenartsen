jQuery(function ($) {

    var headerSel = ".gda-voeding-toggle__header";

    $(document).off("click.gdaVoedingIso", headerSel);

    $(document).on("click.gdaVoedingIso", headerSel, function (e) {
        e.preventDefault();

        var $header = $(this);
        var $toggle = $header.closest(".gda-voeding-toggle");
        var $panel  = $toggle.find(".gda-voeding-toggle__panel").first();

        var isOpen = $header.attr("aria-expanded") === "true";

        // ✅ SLUIT ALLES ANDERS (accordion)
        $(".gda-voeding-toggle__header").not($header).attr("aria-expanded", "false");
        $(".gda-voeding-toggle__panel").not($panel).stop(true, true).slideUp(200);

        // ✅ TOGGLE HUIDIGE
        if (isOpen) {
            $header.attr("aria-expanded", "false");
            $panel.stop(true, true).slideUp(200);
        } else {
            $header.attr("aria-expanded", "true");
            $panel.stop(true, true).slideDown(200);
        }
    });

});