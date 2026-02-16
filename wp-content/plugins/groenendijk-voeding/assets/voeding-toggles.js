jQuery(function ($) {

    var headerSel = ".gda-voeding-toggle__header";

    // voorkomen van dubbele binding
    $(document).off("click.gdaVoedingIso", headerSel);

    $(document).on("click.gdaVoedingIso", headerSel, function (e) {
        e.preventDefault();

        var $header = $(this);
        var $toggle = $header.closest(".gda-voeding-toggle");
        var $panel  = $toggle.find(".gda-voeding-toggle__panel").first();

        var isOpen = $header.attr("aria-expanded") === "true";

        if (isOpen) {
            $header.attr("aria-expanded", "false");
            $panel.stop(true, true).slideUp(200);
        } else {
            $header.attr("aria-expanded", "true");
            $panel.stop(true, true).slideDown(200);
        }
    });

});