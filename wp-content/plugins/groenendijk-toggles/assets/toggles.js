jQuery(function ($) {
    const hasGSAP = !!window.gsap;

    function closeToggle($toggle) {
        const $btn = $toggle.find(".gda-toggle__header").first();
        const $panel = $toggle.find(".gda-toggle__panel").first();

        $btn.attr("aria-expanded", "false");
        $toggle.removeClass("is-open");

        if (!hasGSAP) {
            $panel.prop("hidden", true);
            return;
        }

        // Zorg dat we kunnen animeren (panel moet zichtbaar zijn om height te meten)
        $panel.prop("hidden", false);

        // Kill bestaande animaties op panel
        gsap.killTweensOf($panel[0]);

        // Animate close
        gsap.to($panel[0], {
            height: 0,
            opacity: 0,
            y: -6,
            duration: 0.28,
            ease: "power2.out",
            onComplete: () => {
                $panel.prop("hidden", true);
                gsap.set($panel[0], { clearProps: "height,opacity,transform" });
            },
        });
    }

    function openToggle($toggle) {
        const $btn = $toggle.find(".gda-toggle__header").first();
        const $panel = $toggle.find(".gda-toggle__panel").first();

        $btn.attr("aria-expanded", "true");
        $toggle.addClass("is-open");

        if (!hasGSAP) {
            $panel.prop("hidden", false);
            return;
        }

        // panel moet zichtbaar zijn
        $panel.prop("hidden", false);

        // Kill bestaande animaties
        gsap.killTweensOf($panel[0]);

        // Start: height 0 -> auto (via scrollHeight)
        gsap.set($panel[0], { height: 0, opacity: 0, y: -6 });

        const targetH = $panel[0].scrollHeight;

        gsap.to($panel[0], {
            height: targetH,
            opacity: 1,
            y: 0,
            duration: 0.35,
            ease: "power2.out",
            onComplete: () => {
                // height terug op auto (clearProps) zodat content responsive blijft
                gsap.set($panel[0], { clearProps: "height" });
            },
        });
    }

    // Init: zorg dat gesloten panels netjes “ready” zijn
    $(".gda-toggle").each(function () {
        const $toggle = $(this);
        const $btn = $toggle.find(".gda-toggle__header").first();
        const $panel = $toggle.find(".gda-toggle__panel").first();

        // standaard dicht (tenzij je later een "default open" wil)
        $btn.attr("aria-expanded", "false");
        $toggle.removeClass("is-open");
        $panel.prop("hidden", true);

        if (hasGSAP) {
            gsap.set($panel[0], { opacity: 1, y: 0, clearProps: "all" });
        }
    });

    // Click handler
    $(document).on("click", ".gda-toggle__header", function () {
        const $btn = $(this);
        const $toggle = $btn.closest(".gda-toggle");
        const isOpen = $btn.attr("aria-expanded") === "true";

        // Accordion: sluit andere open toggles
        $toggle
            .siblings(".gda-toggle.is-open")
            .each(function () {
                closeToggle($(this));
            });

        if (isOpen) {
            closeToggle($toggle);
        } else {
            openToggle($toggle);
        }
    });

    // Optional: als je met anchors werkt (#id), open die toggle automatisch
    // + smooth scroll (zonder gedoe met sticky header offsets)
    function openFromHash() {
        const hash = window.location.hash;
        if (!hash || hash.length < 2) return;

        const id = hash.substring(1);
        const $target = $("#" + CSS.escape(id) + ".gda-toggle");

        if (!$target.length) return;

        // open target + sluit rest
        $target
            .siblings(".gda-toggle.is-open")
            .each(function () {
                closeToggle($(this));
            });

        openToggle($target);

        // scroll naar header (niet naar panel)
        const $header = $target.find(".gda-toggle__header").first();
        if ($header.length) {
            setTimeout(() => {
                $header[0].scrollIntoView({ behavior: "smooth", block: "start" });
            }, 50);
        }
    }

    // bij load + bij hash change
    openFromHash();
    $(window).on("hashchange", openFromHash);
});
