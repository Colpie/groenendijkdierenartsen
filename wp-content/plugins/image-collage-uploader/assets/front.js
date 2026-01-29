(function () {
    function initGdaCollageFade() {
        if (!window.gsap || !window.ScrollTrigger) return;

        gsap.registerPlugin(window.ScrollTrigger);

        const tiles = document.querySelectorAll(".gda-collage .gda-tile");
        if (!tiles.length) return;

        tiles.forEach((tile) => {
            // Random maar veilig (geen transforms)
            const delay = gsap.utils.random(0, 0.35);
            const duration = gsap.utils.random(0.45, 0.9);

            // Start state: alleen opacity
            gsap.set(tile, {
                autoAlpha: 0,
                willChange: "opacity",
            });

            gsap.to(tile, {
                autoAlpha: 1,
                duration: duration,
                delay: delay,
                ease: "power2.out",
                clearProps: "will-change",
                scrollTrigger: {
                    trigger: tile,
                    start: "top 85%",
                    end: "top 40%",                 // kleine “range” zodat reverse logisch voelt
                    toggleActions: "play none play reverse",
                    // of als je het wat "strakker" wilt:
                    // toggleActions: "play reverse play reverse",
                    // scrub: false,
                    once: false,
                },
            });
        });
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initGdaCollageFade);
    } else {
        initGdaCollageFade();
    }
})();
