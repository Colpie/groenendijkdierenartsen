(function ($) {
    $(document).ready(function () {

        $('.check-list ul').each(function () {
            $(this).children('li').each(function (i) {
                $(this)
                    .addClass('wow fadeInRight')
                    .css('animation-delay', (i * 0.1) + 's');
            });
        });

        $(document).on('show.bs.collapse', '.panel-collapse', function () {
            $(this).find('.toggle-content ul li').css({
                opacity: 0,
                visibility: 'hidden',
                animation: 'none',
                'animation-delay': ''
            });
        });

        $(document).on('shown.bs.collapse', '.panel-collapse', function () {
            $(this).find('.toggle-content ul').each(function () {
                $(this).find('li').each(function (i) {
                    var $li = $(this);

                    $li.css({
                        opacity: 0,
                        visibility: 'hidden',
                        animation: 'none',
                        'animation-delay': ''
                    });

                    $li[0].offsetHeight;

                    $li.css({
                        visibility: 'visible',
                        'animation-name': 'fadeInRight',
                        'animation-duration': '0.6s',
                        'animation-fill-mode': 'forwards',
                        'animation-timing-function': 'ease',
                        'animation-delay': (i * 0.1) + 's'
                    });
                });
            });
        });

        $(document).on('hide.bs.collapse', '.panel-collapse', function () {
            $(this).find('.toggle-content ul li').css({
                opacity: 0,
                visibility: 'hidden',
                animation: 'none',
                'animation-delay': ''
            });
        });

        $('img[title]').hover(function () {
            $(this).data('title', $(this).attr('title')).removeAttr('title');
        }, function () {
            var originalTitle = $(this).data('title');
            if (originalTitle) {
                $(this).attr('title', originalTitle);
            }
        });

        // Default swiper
        (function () {

            function setVisibleSlides(swiper) {
                const slides = swiper.slides;
                const activeIndex = swiper.activeIndex;
                const visibleCount = swiper.params.slidesPerView;

                // reset
                slides.forEach(slide => {
                    slide.classList.remove('is-visible');
                });

                // active + volgende zichtbare slides
                for (let i = 0; i < visibleCount; i++) {
                    const slide = slides[activeIndex + i];
                    if (slide) {
                        slide.classList.add('is-visible');
                    }
                }
            }

            function wrapSwiperNavigation(carousel) {
                const prev = carousel.querySelector('.awb-swiper-button-prev');
                const next = carousel.querySelector('.awb-swiper-button-next');

                if (!prev || !next) return;

                // voorkom dubbel wrappen
                if (prev.parentElement.classList.contains('custom-navigation')) return;

                const wrapper = document.createElement('div');
                wrapper.className = 'custom-navigation';

                prev.parentNode.insertBefore(wrapper, prev);
                wrapper.appendChild(prev);
                wrapper.appendChild(next);
            }

            function overrideAvadaCarousel() {
                document
                    .querySelectorAll(".fusion-image-carousel .awb-swiper-carousel")
                    .forEach(function (carousel) {

                        const isFullWidth = !!carousel.closest(".fusion-image-carousel.full-width-swiper");

                        if (carousel.swiper) {
                            carousel.swiper.destroy(true, true);
                        }

                        const swiper = new Swiper(carousel, {
                            // ✅ full-width altijd 1 slide
                            // ✅ andere carousels jouw default 3 (met breakpoints 1/2/3)
                            slidesPerView: isFullWidth ? 1 : 3,

                            spaceBetween: 13,
                            speed: 500,
                            loop: true,
                            autoHeight: true,

                            // ✅ fade alleen voor full-width
                            effect: isFullWidth ? "fade" : "slide",
                            fadeEffect: isFullWidth ? {crossFade: true} : undefined,

                            // enkel nodig voor parallax
                            watchSlidesProgress: isFullWidth,

                            navigation: {
                                nextEl: carousel.querySelector(".awb-swiper-button-next"),
                                prevEl: carousel.querySelector(".awb-swiper-button-prev"),
                            },

                            breakpoints: isFullWidth
                                ? {
                                    0: {slidesPerView: 1},
                                    768: {slidesPerView: 1},
                                    1025: {slidesPerView: 1},
                                }
                                : {
                                    0: {slidesPerView: 1},
                                    768: {slidesPerView: 2},
                                    1025: {slidesPerView: 3},
                                },

                            on: {
                                init() {
                                    setVisibleSlides(this);
                                    wrapSwiperNavigation(carousel);
                                },
                                slideChange() {
                                    setVisibleSlides(this);
                                },
                                resize() {
                                    setVisibleSlides(this);
                                }
                            }
                        });

                    });
            }

            window.addEventListener('load', function () {
                if (typeof window.initFullWidthSwiperScrollParallax === 'function') {
                    window.initFullWidthSwiperScrollParallax();
                }
            });

            window.addEventListener('load', function () {
                setTimeout(overrideAvadaCarousel, 300);
            });

        })();

        // Paralax image
        jQuery(function ($) {
            if (!window.gsap || !window.ScrollTrigger) return;

            gsap.registerPlugin(ScrollTrigger);

            $(".parallax-image-column").each(function () {
                const column = this;
                const img = column.querySelector("img");

                if (!img) return;

                // startpositie
                gsap.set(img, {yPercent: -10});

                gsap.to(img, {
                    yPercent: 10,
                    ease: "none",
                    scrollTrigger: {
                        trigger: column,
                        start: "top bottom",
                        end: "bottom top",
                        scrub: true,
                        invalidateOnRefresh: true,
                    }
                });
            });

            // Avada / images
            ScrollTrigger.refresh(true);
            $(window).on("load", function () {
                ScrollTrigger.refresh(true);
            });
        });

        // BrSwap
        (function initBrSwap() {

            function setupBrSwap($els, breakpoint, ns) {
                if (!$els || !$els.length) return;

                // Bewaar per element de originele HTML 1x
                $els.each(function () {
                    var $el = $(this);
                    if (!$el.data('brswapOriginal')) {
                        $el.data('brswapOriginal', $el.html());
                    }
                });

                function stripBr(html) {
                    return (html || '').replace(/<br\s*\/?>/gi, ' ');
                }

                function update() {
                    var isSmall = $(window).width() < breakpoint;

                    $els.each(function () {
                        var $el = $(this);
                        var originalHTML = $el.data('brswapOriginal') || $el.html();
                        var noBrHTML = stripBr(originalHTML);

                        if (isSmall) {
                            if ($el.html() !== noBrHTML) $el.html(noBrHTML);
                        } else {
                            if ($el.html() !== originalHTML) $el.html(originalHTML);
                        }
                    });
                }

                update();

                // unieke namespace per swap + debounce
                var resizeTimer;
                $(window).off('resize.' + ns).on('resize.' + ns, function () {
                    clearTimeout(resizeTimer);
                    resizeTimer = setTimeout(update, 150);
                });
            }

            // jouw 2 cases
            setupBrSwap($('.br-swap p'), 640, 'brSwapH2');
            setupBrSwap($('.banner-row .fusion-title-heading'), 640, 'brSwapBannerP');

        })();

        // Fade in up columns
        jQuery(function ($) {
            if (!window.gsap || !window.ScrollTrigger) return;

            gsap.registerPlugin(ScrollTrigger);

            const $items = $(".usp-column, .contact-column");
            if (!$items.length) return;

            // Pak liever een container die ALLE usp-columns bevat
            // (parent() is vaak ok, maar als Avada wrappers gebruikt kan closest beter zijn)
            const $wrap = $items.first().closest(".fusion-row, .fusion-builder-row, .fusion-fullwidth, .fusion-content-boxes").first();
            const triggerEl = $wrap.length ? $wrap[0] : $items.first().parent()[0];

            // Start state
            gsap.set($items, {autoAlpha: 0, y: 30});

            // Timeline met stagger
            const tl = gsap.timeline({paused: true});
            tl.to($items.toArray(), {
                autoAlpha: 1,
                y: 0,
                duration: 0.6,
                ease: "power3.out",
                stagger: 0.20,
                overwrite: true,
                // ❌ clearProps weg laten, anders reverse “doet niets”
                // clearProps: "transform",
            });

            ScrollTrigger.create({
                trigger: triggerEl,
                start: "top 85%",
                end: "bottom 60%",
                invalidateOnRefresh: true,
                // markers: true,

                onEnter: () => tl.play(),
                onLeaveBack: () => tl.reverse(),
            });

            // Avada layout/images => refresh
            ScrollTrigger.refresh(true);
            $(window).on("load", function () {
                ScrollTrigger.refresh(true);
            });
        });

        // Moving image
        (function () {

            function findScroller(el) {
                let p = el.parentElement;
                while (p && p !== document.body) {
                    const s = getComputedStyle(p);
                    const canScroll =
                        (s.overflowY === "auto" || s.overflowY === "scroll") &&
                        p.scrollHeight > p.clientHeight + 5;
                    if (canScroll) return p;
                    p = p.parentElement;
                }
                return window;
            }

            let parallaxSTs = [];
            let parallaxMM = null;
            let mobileFloatTweens = [];

            function killAll() {
                // kill desktop STs
                parallaxSTs.forEach(st => {
                    try {
                        st.kill(true);
                    } catch (e) {
                    }
                });
                parallaxSTs = [];

                // kill mobile floats
                mobileFloatTweens.forEach(tw => {
                    try {
                        tw.kill();
                    } catch (e) {
                    }
                });
                mobileFloatTweens = [];

                // reset transforms
                const cols = gsap.utils.toArray(".full-height-image-column, .moving-image");
                if (cols.length) gsap.set(cols, {clearProps: "transform"});
            }

            function buildDesktopParallax() {
                const cols = gsap.utils.toArray(".full-height-image-column, .moving-image");
                if (!cols.length) return;

                const amount = 180;

                cols.forEach((col) => {
                    const scroller = findScroller(col);

                    const tween = gsap.fromTo(
                        col,
                        {y: amount / 2},
                        {
                            y: -amount / 2,
                            ease: "none",
                            overwrite: true,
                            scrollTrigger: {
                                trigger: col,
                                scroller: scroller,
                                start: "top bottom",
                                end: "bottom top",
                                scrub: true,
                                invalidateOnRefresh: true,
                            }
                        }
                    );

                    if (tween.scrollTrigger) parallaxSTs.push(tween.scrollTrigger);
                });

                ScrollTrigger.refresh(true);
            }

            function buildMobileSubtleFloat() {
                const cols = gsap.utils.toArray(".full-height-image-column, .moving-image");
                if (!cols.length) return;

                cols.forEach((col, i) => {
                    // heel subtiel + wat variatie zodat het niet “synchroon” beweegt
                    const base = 20;                 // px (subtiel)
                    const variance = (i % 3) * 1.5; // kleine variatie
                    const yAmount = base + variance;

                    const dur = 3.6 + (i % 4) * 0.35; // variatie in timing

                    // start state
                    gsap.set(col, {willChange: "transform"});

                    const tw = gsap.to(col, {
                        y: -yAmount,
                        // rotation: 0.15, // 👈 optioneel, meestal niet nodig
                        duration: dur,
                        ease: "sine.inOut",
                        yoyo: true,
                        repeat: -1,
                        overwrite: true
                    });

                    mobileFloatTweens.push(tw);
                });
            }

            window.initColumnFloatParallax = function initColumnFloatParallax() {
                if (!window.gsap || !window.ScrollTrigger) return;

                gsap.registerPlugin(ScrollTrigger);

                if (parallaxMM) {
                    try {
                        parallaxMM.kill();
                    } catch (e) {
                    }
                    parallaxMM = null;
                }

                parallaxMM = gsap.matchMedia();

                // Desktop: scroll-parallax
                parallaxMM.add("(min-width: 1200px)", () => {
                    killAll();
                    buildDesktopParallax();

                    const onRefresh = () => {
                        killAll();
                        buildDesktopParallax();
                    };
                    ScrollTrigger.addEventListener("refreshInit", onRefresh);

                    return () => {
                        ScrollTrigger.removeEventListener("refreshInit", onRefresh);
                        killAll();
                    };
                });

                // Mobile: subtiele float (geen ScrollTrigger)
                parallaxMM.add("(max-width: 1199px)", () => {
                    killAll();
                    buildMobileSubtleFloat();

                    return () => {
                        killAll();
                    };
                });
            };

            jQuery(window).on("load", function () {
                setTimeout(() => window.initColumnFloatParallax(), 500);
            });

            // debounce resize -> refresh (voor desktop ST)
            let rT = null;
            window.addEventListener("resize", function () {
                clearTimeout(rT);
                rT = setTimeout(function () {
                    if (window.ScrollTrigger) ScrollTrigger.refresh(true);
                }, 200);
            });

        })();

        // Bigger text
        // document.querySelectorAll('.bigger-text').forEach(el => {
        //     // Bewaar originele structuur
        //     const originalHTML = el.innerHTML;
        //     const temp = document.createElement('div');
        //     temp.innerHTML = originalHTML;
        //
        //     // Split text nodes in WORD spans, behoud tags zoals <strong> en <br>
        //     function splitWords(node) {
        //         // Text node
        //         if (node.nodeType === 3) {
        //             const text = node.textContent;
        //             const frag = document.createDocumentFragment();
        //
        //             // Split op spaties, maar behoud de spaties in output
        //             // (zodat layout identiek blijft)
        //             const parts = text.split(/(\s+)/);
        //
        //             parts.forEach(part => {
        //                 if (!part) return;
        //
        //                 // Whitespace -> gewoon tekst terugplaatsen
        //                 if (/^\s+$/.test(part)) {
        //                     frag.appendChild(document.createTextNode(part));
        //                 } else {
        //                     const span = document.createElement('span');
        //                     span.className = 'word';
        //                     span.textContent = part;
        //                     frag.appendChild(span);
        //                 }
        //             });
        //
        //             return frag;
        //         }
        //
        //         // Element node (bv strong, p, br)
        //         if (node.nodeType === 1) {
        //             const clone = node.cloneNode(false);
        //             node.childNodes.forEach(child => clone.appendChild(splitWords(child)));
        //             return clone;
        //         }
        //
        //         return document.createTextNode('');
        //     }
        //
        //     // Rebuild content
        //     el.innerHTML = '';
        //     temp.childNodes.forEach(n => el.appendChild(splitWords(n)));
        //
        //     const words = el.querySelectorAll('.word');
        //
        //     gsap.fromTo(
        //         words,
        //         {
        //             y: 4
        //         },
        //         {
        //             y: 0,
        //             ease: 'none',      // perfect voor scrub
        //             stagger: 0.12,
        //             scrollTrigger: {
        //                 trigger: el,
        //                 start: 'top 85%',
        //                 end: 'top 35%',
        //                 scrub: true
        //                 // markers: true
        //             }
        //         }
        //     );
        // });

        // Reviews Swiper
        $(".reviews-swiper").each(function () {
            const $el = $(this);

            // voorkom dubbele init
            if ($el.data("swiper-initialized")) return;
            $el.data("swiper-initialized", true);

            const swiper = new Swiper(this, {
                loop: true,
                slidesPerView: 1,
                spaceBetween: 0,

                effect: "fade",
                fadeEffect: {crossFade: true},

                autoplay: {
                    delay: 7000,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: false,
                },

                navigation: {
                    nextEl: $el.find(".swiper-button-next")[0],
                    prevEl: $el.find(".swiper-button-prev")[0],
                },

                on: {
                    init: function () {
                        updateReviewHeader(this);
                        runReviewSlideAnim(this);
                    },
                    slideChangeTransitionStart: function () {
                        updateReviewHeader(this);
                        runReviewSlideAnim(this);
                    },
                },
            });

            /**
             * 🔄 UPDATE HEADER IMAGE (bovenste kolom)
             * Zoekt [reviews_header_image] binnen dezelfde Avada row
             */
            function updateReviewHeader(sw) {
                if (!sw || !sw.slides) return;

                // 1) zoek actieve slide
                let slide = sw.slides[sw.activeIndex];
                let headerUrl = slide ? slide.getAttribute("data-header") : "";

                // 2) fallback: zoek eerste slide met header (bij loop/duplicates)
                if (!headerUrl) {
                    for (let i = 0; i < sw.slides.length; i++) {
                        const url = sw.slides[i].getAttribute("data-header");
                        if (url) {
                            headerUrl = url;
                            break;
                        }
                    }
                }

                if (!headerUrl) return;

                // 3) zoek header IMG in dezelfde Avada sectie
                const $row = $el.closest(
                    ".fusion-fullwidth, .fusion-builder-row, .fusion-row"
                );

                let $img = $row.find("img[data-reviews-header='1']").first();

                // ultieme fallback: globaal
                if (!$img.length) {
                    $img = $("img[data-reviews-header='1']").first();
                }

                if (!$img.length) return;

                // 4) swap alleen als nodig
                if ($img.attr("src") !== headerUrl) {
                    $img.attr("src", headerUrl);
                    $img.removeAttr("srcset").removeAttr("sizes");

                    // subtiele fade
                    if (window.gsap) {
                        gsap.killTweensOf($img[0]);
                        gsap.fromTo(
                            $img[0],
                            {autoAlpha: 0},
                            {
                                autoAlpha: 1,
                                duration: 0.5,
                                ease: "power2.out",
                                overwrite: true,
                            }
                        );
                    }
                }
            }

            /**
             * 🎬 JOUW BESTAANDE SLIDE-ANIMATIES
             */
            function runReviewSlideAnim(sw) {
                if (!window.gsap) return;

                const slide = sw.slides[sw.activeIndex];
                if (!slide) return;

                const contentTargets = slide.querySelectorAll(
                    "h1,h2,h3,h4,p,.fusion-title,.fusion-text,.review-content,.review-author,.review-stars"
                );

                const img = slide.querySelector("img");

                gsap.killTweensOf(contentTargets);
                if (img) gsap.killTweensOf(img);

                gsap.fromTo(
                    contentTargets,
                    {autoAlpha: 0, y: 25},
                    {
                        autoAlpha: 1,
                        y: 0,
                        duration: 0.7,
                        ease: "power3.out",
                        stagger: 0.06,
                        overwrite: true,
                        delay: 0.05,
                    }
                );

                if (img) {
                    gsap.fromTo(
                        img,
                        {scale: 1.5},
                        {
                            scale: 1.0,
                            duration: 1.2,
                            ease: "power2.out",
                            overwrite: true,
                        }
                    );
                }
            }
        });

        // Popup
        jQuery(function ($) {
            const $popup = $('.ae-popup');

            // Click toggle → overal
            $(document).on('click', '.ae-popup-icon', function (e) {
                e.preventDefault();
                $(this).closest('.ae-popup').toggleClass('active');
            });

            // ⏱️ Timers enkel op frontpage
            if ($('body').hasClass('home')) {

                // Auto open after 3 seconds
                setTimeout(function () {
                    $popup.addClass('active');
                }, 3000);

                // Auto close after 7 seconds
                setTimeout(function () {
                    $popup.removeClass('active');
                }, 7000);

            }
        });

        // Fixed header
        (function () {
            const header = document.querySelector('#boxed-wrapper header');
            if (!header) return;

            const fusionHeader = header.querySelector('.fusion-header');
            const row = header.querySelector('.fusion-row');
            const logoImg = header.querySelector('.logo-column img');
            const portal = header.querySelector('.client-portal');

            if (!logoImg) return;

            const TRIGGER_Y = 180;
            const mq = window.matchMedia('(min-width: 1200px)');

            // ✅ Vul deze in
            const DEFAULT_LOGO_URL = logoImg.getAttribute('src'); // pakt huidige logo
            const STICKY_LOGO_URL = '/wp-content/themes/Avada-Child/assets/images/logo_fixed.png';

            // Bewaar ook srcset/sizes zodat je die kan terugzetten
            const DEFAULT_SRCSET = logoImg.getAttribute('srcset');
            const DEFAULT_SIZES = logoImg.getAttribute('sizes');

            let placeholder = null;
            let isFixed = false;

            // Timeline
            const tl = gsap.timeline({
                paused: true,
                defaults: {ease: 'power2.out', duration: 0.25}
            });

            if (fusionHeader) tl.to(fusionHeader, {paddingTop: 8, paddingBottom: 8}, 0);
            if (row) tl.to(row, {paddingTop: 0, paddingBottom: 0}, 0);
            if (logoImg) tl.to(logoImg, {scale: 0.82, transformOrigin: 'left center'}, 0);
            if (portal) tl.to(portal, {scale: 0.92, transformOrigin: 'right center'}, 0);

            function ensurePlaceholder() {
                if (placeholder) return;
                placeholder = document.createElement('div');
                placeholder.className = 'fusion-header-placeholder';
                placeholder.style.display = 'none';
                header.parentNode.insertBefore(placeholder, header.nextSibling);
            }

            function updatePlaceholderHeight() {
                if (!placeholder) return;
                placeholder.style.height = header.offsetHeight + 'px';
            }

            // ✅ logo swap helpers
            function setStickyLogo() {
                // srcset kan conflict geven met svg/png swap -> weghalen voor zekerheid
                logoImg.setAttribute('src', STICKY_LOGO_URL);
                logoImg.removeAttribute('srcset');
                logoImg.removeAttribute('sizes');
                logoImg.classList.add('is-sticky-logo');
            }

            function setDefaultLogo() {
                logoImg.setAttribute('src', DEFAULT_LOGO_URL);
                if (DEFAULT_SRCSET) logoImg.setAttribute('srcset', DEFAULT_SRCSET);
                else logoImg.removeAttribute('srcset');

                if (DEFAULT_SIZES) logoImg.setAttribute('sizes', DEFAULT_SIZES);
                else logoImg.removeAttribute('sizes');

                logoImg.classList.remove('is-sticky-logo');
            }

            function resetToNormal() {
                isFixed = false;
                header.classList.remove('is-fixed');
                if (placeholder) placeholder.style.display = 'none';

                // logo terug normaal
                setDefaultLogo();

                tl.pause(0);
                gsap.set([fusionHeader, row, logoImg, portal].filter(Boolean), {clearProps: 'transform,padding'});
            }

            function setFixed(state) {
                if (state === isFixed) return;
                isFixed = state;

                if (state) {
                    ensurePlaceholder();
                    updatePlaceholderHeight();
                    placeholder.style.display = 'block';
                    header.classList.add('is-fixed');

                    // ✅ logo sticky
                    setStickyLogo();

                    tl.play();
                } else {
                    header.classList.remove('is-fixed');
                    if (placeholder) placeholder.style.display = 'none';

                    // ✅ logo terug
                    setDefaultLogo();

                    tl.reverse();
                }
            }

            function onScroll() {
                if (!mq.matches) return;
                setFixed(window.scrollY > TRIGGER_Y);
            }

            function enableDesktop() {
                ensurePlaceholder();
                updatePlaceholderHeight();
                window.addEventListener('resize', updatePlaceholderHeight);
                window.addEventListener('scroll', onScroll, {passive: true});
                onScroll();
            }

            function disableDesktop() {
                window.removeEventListener('resize', updatePlaceholderHeight);
                window.removeEventListener('scroll', onScroll);
                resetToNormal();
            }

            function handleMQChange() {
                if (mq.matches) enableDesktop();
                else disableDesktop();
            }

            handleMQChange();

            if (typeof mq.addEventListener === 'function') mq.addEventListener('change', handleMQChange);
            else mq.addListener(handleMQChange);
        })();

        // Mobile menu
        // Mobile menu
        $(function () {
            let menuOpen = false;

            const $menuBg = $('.fusion-flyout-menu-bg');
            const $menu = $('.fusion-flyout-menu');
            const $toggle = $('.fusion-flyout-menu-toggle');
            const $body = $('body');

            function getMenuItems() {
                return $('.fusion-flyout-menu .fusion-menu > li');
            }

            function buildMenuCarets() {
                $('.fusion-flyout-menu .fusion-menu .menu-item-has-children > a').each(function () {
                    const $link = $(this);

                    if (!$link.find('.custom-caret').length) {
                        $link.append(
                            '<span class="fusion-caret custom-caret">' +
                            '<i class="fusion-dropdown-indicator" aria-hidden="true"></i>' +
                            '</span>'
                        );
                    }
                });
            }

            function resetSubmenus() {
                $('.fusion-flyout-menu .sub-menu').removeClass('open').css({
                    height: 0,
                    opacity: 0,
                    overflow: 'hidden'
                });

                $('.fusion-flyout-menu .custom-caret').removeClass('rotate');
            }

            function openSubmenu($caret) {
                const $li = $caret.closest('li');
                const $submenu = $li.children('.sub-menu').first();

                if (!$submenu.length) return;

                $submenu.addClass('open');

                gsap.killTweensOf($submenu[0]);

                gsap.set($submenu, {
                    height: 'auto',
                    opacity: 1,
                    overflow: 'hidden'
                });

                const targetHeight = $submenu.outerHeight();

                gsap.fromTo($submenu[0],
                    {
                        height: 0,
                        opacity: 0
                    },
                    {
                        height: targetHeight,
                        opacity: 1,
                        duration: 0.4,
                        ease: 'power2.out',
                        onComplete: function () {
                            $submenu.css({
                                height: 'auto',
                                overflow: ''
                            });
                        }
                    }
                );

                $caret.addClass('rotate');
            }

            function closeSubmenu($caret) {
                const $li = $caret.closest('li');
                const $submenu = $li.children('.sub-menu').first();

                if (!$submenu.length) return;

                gsap.killTweensOf($submenu[0]);

                gsap.set($submenu, {
                    height: $submenu.outerHeight(),
                    opacity: 1,
                    overflow: 'hidden'
                });

                gsap.to($submenu[0], {
                    height: 0,
                    opacity: 0,
                    duration: 0.35,
                    ease: 'power2.out',
                    onComplete: function () {
                        $submenu.removeClass('open').css({
                            height: 0,
                            opacity: 0,
                            overflow: 'hidden'
                        });
                    }
                });

                $caret.removeClass('rotate');
            }

            function openMenu() {
                menuOpen = true;

                $('.fusion-header').removeAttr('style');
                $('.fusion-flyout-mobile-menu-icons').addClass('change');

                buildMenuCarets();
                resetSubmenus();

                const $menuItems = getMenuItems();

                gsap.killTweensOf([$menuBg[0], $menu[0]]);
                gsap.killTweensOf($menuItems.toArray());

                gsap.set($menuBg, {
                    y: '100%',
                    autoAlpha: 0
                });

                gsap.set($menu, {
                    y: 50,
                    autoAlpha: 0
                });

                gsap.set($menuItems.toArray(), {
                    opacity: 0,
                    y: 30
                });

                gsap.to($menuBg, {
                    y: '0%',
                    autoAlpha: 1,
                    duration: 0.7,
                    ease: 'power3.out'
                });

                gsap.to($menu, {
                    y: 0,
                    autoAlpha: 1,
                    duration: 0.6,
                    delay: 0.2,
                    ease: 'power3.out'
                });

                gsap.to($menuItems.toArray(), {
                    opacity: 1,
                    y: 0,
                    duration: 0.6,
                    delay: 0.3,
                    stagger: 0.05,
                    ease: 'power3.out'
                });

                $body.addClass('flyout-menu-open');
            }

            function closeMenu() {
                menuOpen = false;

                $('.fusion-flyout-mobile-menu-icons').removeClass('change');

                const $menuItems = getMenuItems();

                resetSubmenus();

                gsap.killTweensOf([$menuBg[0], $menu[0]]);
                gsap.killTweensOf($menuItems.toArray());

                gsap.to($menuItems.get().reverse(), {
                    opacity: 0,
                    y: 30,
                    duration: 0.4,
                    stagger: 0.04,
                    ease: 'power2.in',
                    onComplete: function () {
                        gsap.to($menu, {
                            y: 50,
                            autoAlpha: 0,
                            duration: 0.45,
                            ease: 'power3.inOut',
                            onComplete: function () {
                                gsap.to($menuBg, {
                                    y: '100%',
                                    autoAlpha: 0,
                                    duration: 0.55,
                                    ease: 'power3.inOut'
                                });
                            }
                        });
                    }
                });

                $body.removeClass('flyout-menu-open');
            }

            function toggleMenu(e) {
                e.preventDefault();

                if (!menuOpen) {
                    openMenu();
                } else {
                    closeMenu();
                }
            }

            $toggle.on('click', toggleMenu);

            $(document).on('click', '.fusion-flyout-menu .custom-caret', function (e) {
                e.preventDefault();
                e.stopPropagation();

                const $caret = $(this);
                const $submenu = $caret.closest('li').children('.sub-menu').first();

                if (!$submenu.length) return;

                if ($submenu.hasClass('open')) {
                    closeSubmenu($caret);
                } else {
                    openSubmenu($caret);
                }
            });

            $(window).on('resize', function () {
                if (window.innerWidth >= 1200 && menuOpen) {
                    closeMenu();
                }
            });
        });

        function setServiceImageHeight() {
            $('.fusion-builder-row').has('.service-content-column, .full-height-service-image').each(function () {
                var $row = $(this);
                var $contentColumn = $row.find('.service-content-column').first();
                var $image = $row.find('.full-height-service-image').first();

                if (!$contentColumn.length || !$image.length) return;

                var contentHeight = $contentColumn.outerHeight();
                $image.height(contentHeight);
            });
        }

        // Run on load
        setServiceImageHeight();

        // Run again after images load (belangrijk bij Avada)
        $(window).on('load', function () {
            setServiceImageHeight();
        });

        let serviceResizeTimer = null;

        $(window).on('resize', function () {
            clearTimeout(serviceResizeTimer);
            serviceResizeTimer = setTimeout(function () {
                setServiceImageHeight();
            }, 150);
        });

        $(document).on('click', '.latest-news-wrapper .latest-news__loadmore', function (e) {
            e.preventDefault();

            const $btn = $(this);
            const $wrap = $btn.closest('.latest-news-wrapper');
            const $list = $wrap.find('.latest-news');

            if ($btn.data('loading')) return;

            let page = parseInt($wrap.attr('data-page'), 10) || 1;
            const maxPages = parseInt($wrap.attr('data-max-pages'), 10) || 1;

            // volgende pagina
            page++;

            if (page > maxPages) {
                $btn.closest('.latest-news__actions').remove();
                return;
            }

            $btn.data('loading', true);
            $btn.addClass('is-loading');

            const originalText = $btn.find('.fusion-button-text').text();
            $btn.find('.fusion-button-text').text('Laden…');

            $.ajax({
                url: gdaNews.ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'gda_load_more_news',
                    nonce: $wrap.attr('data-nonce'),
                    page: page,
                    per_page: $wrap.attr('data-per-page'),
                    excerpt: $wrap.attr('data-excerpt'),
                    cat: $wrap.attr('data-cat')
                }
            }).done(function (res) {

                if (res && res.success && res.data && res.data.html) {
                    $list.append(res.data.html);
                    $wrap.attr('data-page', page);
                }

                // Hide button if no more
                if (!res || !res.success || !res.data || res.data.has_more === false || page >= maxPages) {
                    $btn.closest('.latest-news__actions').remove();
                }

            }).fail(function () {
                // fallback tekst terug
                $btn.find('.fusion-button-text').text('Probeer opnieuw');
            }).always(function () {
                $btn.data('loading', false);
                $btn.removeClass('is-loading');

                if ($btn.length && $.contains(document, $btn[0])) {
                    $btn.find('.fusion-button-text').text(originalText);
                }
            });
        });


        // Form submit
        document.addEventListener('wpcf7submit', function () {
            $('.loading-spinner').hide();
        });

        $('.wpcf7-submit').on('click', function () {
            const $form = $('.contact-form');

            if (!$form.find('.loading-spinner').length) {
                $form.prepend('<div class="loading-spinner"><img src="/wp-content/themes/Avada-Child/assets/images/Spinner.gif" alt=""></div>');
            } else {
                $form.find('.loading-spinner').show();
            }
        });

        // *********************************************************************************

        // Animations

        $('.delay').each(function (i) {
            $(this).css('animation-delay', ((i + 1) * 0.2) + 's');
        });

        // Animation Callup (always on bottom of this script !!!!)
        WOW.prototype.addBox = function (element) {
            this.boxes.push(element);
        };

        // Init WOW.js and get instance
        var wow = new WOW();
        wow.init();

        // Attach scrollSpy to .wow elements for detect view exit events,
        // then reset elements and add again for animation
        $('.wow').on('scrollSpy:exit', function () {
            $(this).css({
                'visibility': 'hidden',
                'animation-name': 'none'
            }).removeClass('animated');
            wow.addBox(this);
        }).scrollSpy();

        // *********************************************************************************

    });

})(jQuery);
