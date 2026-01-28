(function () {
    function run() {
        // GSAP optioneel: alleen uitvoeren als aanwezig
        if (!window.gsap) return;

        const tiles = document.querySelectorAll('.gda-collage .gda-reveal');
        if (!tiles.length) return;

        // Start staat
        tiles.forEach(t => {
            t.style.opacity = '0';
            t.style.transform = 'translateY(14px)';
        });

        // Als ScrollTrigger bestaat: nice on-scroll
        if (window.ScrollTrigger && window.gsap.registerPlugin) {
            window.gsap.registerPlugin(window.ScrollTrigger);

            tiles.forEach((tile) => {
                window.gsap.to(tile, {
                    opacity: 1,
                    y: 0,
                    duration: 0.6,
                    ease: 'power2.out',
                    scrollTrigger: {
                        trigger: tile,
                        start: 'top 90%',
                    }
                });
            });

            return;
        }

        // Zonder ScrollTrigger: simple fade in
        window.gsap.to(tiles, {
            opacity: 1,
            y: 0,
            duration: 0.6,
            ease: 'power2.out',
            stagger: 0.06
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', run);
    } else {
        run();
    }
})();
