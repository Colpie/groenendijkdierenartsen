(function () {
    function initGdaCollageFade() {
        if (!window.gsap || !window.ScrollTrigger) return;

        gsap.registerPlugin(ScrollTrigger);

        const tiles = gsap.utils.toArray(".gda-collage .gda-tile");
        if (!tiles.length) return;

        // kill vorige batch/triggers als je re-init doet
        ScrollTrigger.getAll().forEach((st) => {
            if (st?.vars?.id === "gdaCollageBatch") st.kill();
        });
        gsap.killTweensOf(tiles);

        gsap.set(tiles, { autoAlpha: 0, willChange: "opacity" });

        ScrollTrigger.batch(tiles, {
            id: "gdaCollageBatch",

            // ✅ dit voelt meestal “juist” in Avada layouts
            start: "top 75%",
            end: "top 15%",

            // hoe lang ST “wacht” om tiles te groeperen (smoothness)
            interval: 0.10,
            batchMax: 8,

            onEnter: (batch) => {
                gsap.to(batch, {
                    autoAlpha: 1,
                    duration: 0.6,
                    ease: "power2.out",
                    stagger: 0.08,
                    overwrite: "auto",
                    clearProps: "will-change"
                });
            },

            onLeaveBack: (batch) => {
                gsap.to(batch, {
                    autoAlpha: 0,
                    duration: 0.35,
                    ease: "power1.out",
                    stagger: 0.04,
                    overwrite: "auto"
                });
            },

            invalidateOnRefresh: true,
            // markers: true,
        });

        // extra safe na images/layout
        ScrollTrigger.refresh(true);
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initGdaCollageFade);
    } else {
        initGdaCollageFade();
    }
})();
