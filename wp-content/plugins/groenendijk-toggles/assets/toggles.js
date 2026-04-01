jQuery(function ($) {
    const hasGSAP = !!window.gsap;

    function getAnimatedItems($panel) {
        return $panel.find(".check-list ul li, .icon-body-content ul li");
    }

    function hideAnimatedItems($panel) {
        getAnimatedItems($panel).css({
            opacity: "0",
            visibility: "hidden",
            animation: "none",
            "animation-delay": ""
        });
    }

    function clearAnimatedItems($panel) {
        getAnimatedItems($panel).css({
            opacity: "",
            visibility: "",
            animation: "",
            "animation-delay": ""
        });
    }

    function animateListItems($panel) {
        $panel.find(".check-list ul, .icon-body-content ul").each(function () {
            $(this).children("li").each(function (i) {
                const $li = $(this);

                $li.css({
                    opacity: "0",
                    visibility: "hidden",
                    animation: "none",
                    "animation-delay": ""
                });

                this.offsetHeight;

                $li.css({
                    visibility: "visible",
                    animation: "fadeInRight 0.6s ease forwards",
                    "animation-delay": (i * 0.1) + "s"
                });
            });
        });
    }

    function closeToggle($toggle) {
        const $btn = $toggle.children(".gda-toggle__header");
        const $panel = $toggle.children(".gda-toggle__panel");

        $btn.attr("aria-expanded", "false");
        $toggle.removeClass("is-open");

        hideAnimatedItems($panel);

        if (!hasGSAP) {
            $panel.attr("hidden", "hidden");
            return;
        }

        $panel.removeAttr("hidden");
        gsap.killTweensOf($panel[0]);

        gsap.to($panel[0], {
            height: 0,
            opacity: 0,
            y: -6,
            duration: 0.28,
            ease: "power2.out",
            onComplete: () => {
                $panel.attr("hidden", "hidden");
                gsap.set($panel[0], { clearProps: "height,opacity,transform,overflow" });
            }
        });
    }

    function openToggle($toggle) {
        const $btn = $toggle.children(".gda-toggle__header");
        const $panel = $toggle.children(".gda-toggle__panel");

        $btn.attr("aria-expanded", "true");
        $toggle.addClass("is-open");

        // Heel belangrijk: eerst direct verbergen vóór panel zichtbaar wordt
        hideAnimatedItems($panel);

        if (!hasGSAP) {
            $panel.removeAttr("hidden");
            animateListItems($panel);
            return;
        }

        $panel.removeAttr("hidden");
        gsap.killTweensOf($panel[0]);

        gsap.set($panel[0], {
            height: 0,
            opacity: 0,
            y: -6,
            overflow: "hidden"
        });

        const targetH = $panel[0].scrollHeight;

        gsap.to($panel[0], {
            height: targetH,
            opacity: 1,
            y: 0,
            duration: 0.35,
            ease: "power2.out",
            onComplete: () => {
                gsap.set($panel[0], { clearProps: "height,overflow" });
                animateListItems($panel);
            }
        });
    }

    $(".gda-toggle").each(function () {
        const $toggle = $(this);
        const $btn = $toggle.children(".gda-toggle__header");
        const $panel = $toggle.children(".gda-toggle__panel");

        $btn.attr("aria-expanded", "false");
        $toggle.removeClass("is-open");

        if (!$panel.is("[hidden]")) {
            $panel.attr("hidden", "hidden");
        }

        if (hasGSAP) {
            gsap.set($panel[0], { clearProps: "all" });
        }

        // Meteen bij load al hidden zetten zodat ze nooit eerst zichtbaar flashen
        hideAnimatedItems($panel);
    });

    $(document).on("click", ".gda-toggle__header", function (e) {
        e.preventDefault();

        const $toggle = $(this).closest(".gda-toggle");
        const isOpen = $toggle.hasClass("is-open");

        $toggle.siblings(".gda-toggle.is-open").each(function () {
            closeToggle($(this));
        });

        if (isOpen) {
            closeToggle($toggle);
        } else {
            openToggle($toggle);
        }
    });

    function openFromHash() {
        const hash = window.location.hash;
        if (!hash || hash.length < 2) return;
        if (typeof CSS === "undefined" || typeof CSS.escape !== "function") return;

        const id = hash.substring(1);
        const $target = $("#" + CSS.escape(id) + ".gda-toggle");

        if (!$target.length) return;

        $target.siblings(".gda-toggle.is-open").each(function () {
            closeToggle($(this));
        });

        openToggle($target);

        const $header = $target.children(".gda-toggle__header");
        if ($header.length) {
            setTimeout(() => {
                $header[0].scrollIntoView({ behavior: "smooth", block: "start" });
            }, 80);
        }
    }

    openFromHash();
    $(window).on("hashchange", openFromHash);
});