(function ($) {
    $(document).ready(function () {

        $('img').hover(function () {
            $(this).data('title', $(this).attr('title')).removeAttr('title');
        }, function () {
            $(this).attr('title', $(this).data('title'));
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
                document.querySelectorAll('.awb-swiper-carousel').forEach(function (carousel) {

                    if (carousel.swiper) {
                        carousel.swiper.destroy(true, true);
                    }

                    const swiper = new Swiper(carousel, {
                        slidesPerView: 3,
                        spaceBetween: 13,
                        speed: 500,
                        loop: true,
                        autoHeight: true,
                        navigation: {
                            nextEl: carousel.querySelector('.awb-swiper-button-next'),
                            prevEl: carousel.querySelector('.awb-swiper-button-prev')
                        },
                        breakpoints: {
                            0: {
                                slidesPerView: 1
                            },
                            768: {
                                slidesPerView: 2
                            },
                            1025: {
                                slidesPerView: 3
                            }
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
                gsap.set(img, { yPercent: -10 });

                gsap.to(img, {
                    yPercent: 10,
                    ease: "none",
                    scrollTrigger: {
                        trigger: column,
                        start: "top bottom",
                        end: "bottom top",
                        scrub: true,
                        invalidateOnRefresh: true,
                        // markers: true,
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
            gsap.set($items, { autoAlpha: 0, y: 30 });

            // Timeline met stagger
            const tl = gsap.timeline({ paused: true });
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
                parallaxSTs.forEach(st => { try { st.kill(true); } catch (e) {} });
                parallaxSTs = [];

                // kill mobile floats
                mobileFloatTweens.forEach(tw => { try { tw.kill(); } catch (e) {} });
                mobileFloatTweens = [];

                // reset transforms
                const cols = gsap.utils.toArray(".full-height-image-column, .moving-image");
                if (cols.length) gsap.set(cols, { clearProps: "transform" });
            }

            function buildDesktopParallax() {
                const cols = gsap.utils.toArray(".full-height-image-column, .moving-image");
                if (!cols.length) return;

                const amount = 180;

                cols.forEach((col) => {
                    const scroller = findScroller(col);

                    const tween = gsap.fromTo(
                        col,
                        { y: amount / 2 },
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
                    gsap.set(col, { willChange: "transform" });

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
                    try { parallaxMM.kill(); } catch (e) {}
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

            const TRIGGER_Y = 80;
            const mq = window.matchMedia('(min-width: 1200px)');

            // ✅ Vul deze in
            const DEFAULT_LOGO_URL = logoImg.getAttribute('src'); // pakt huidige logo
            const STICKY_LOGO_URL  = '/wp-content/themes/Avada-Child/assets/images/logo_fixed.png';

            // Bewaar ook srcset/sizes zodat je die kan terugzetten
            const DEFAULT_SRCSET = logoImg.getAttribute('srcset');
            const DEFAULT_SIZES  = logoImg.getAttribute('sizes');

            let placeholder = null;
            let isFixed = false;

            // Timeline
            const tl = gsap.timeline({
                paused: true,
                defaults: { ease: 'power2.out', duration: 0.25 }
            });

            if (fusionHeader) tl.to(fusionHeader, { paddingTop: 8, paddingBottom: 8 }, 0);
            if (row) tl.to(row, { paddingTop: 0, paddingBottom: 0 }, 0);
            if (logoImg) tl.to(logoImg, { scale: 0.82, transformOrigin: 'left center' }, 0);
            if (portal) tl.to(portal, { scale: 0.92, transformOrigin: 'right center' }, 0);

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
                gsap.set([fusionHeader, row, logoImg, portal].filter(Boolean), { clearProps: 'transform,padding' });
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
                window.addEventListener('scroll', onScroll, { passive: true });
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
        $(function () {
            let menuOpen = false;

            const $menuBg = $('.fusion-flyout-menu-bg');
            const $menu = $('.fusion-flyout-menu');
            const $menuItems = $('.fusion-flyout-menu .fusion-menu > li');
            const $toggle = $('.fusion-flyout-menu-toggle');

            function resetSubmenus() {
                $('.sub-menu').removeClass('open').css({height: 0, opacity: 0});
                $('.custom-caret').removeClass('rotate');
            }

            $toggle.on('click', function (e) {
                e.preventDefault();
                $('.fusion-header').removeAttr('style');
                $('.fusion-flyout-mobile-menu-icons').toggleClass('change');
                $('.custom-caret').remove();

                if (!menuOpen) {
                    menuOpen = true;

                    // Add caret icons
                    $('.fusion-flyout-menu .fusion-menu .menu-item-has-children > a').each(function () {
                        $(this).append('<span class="fusion-caret custom-caret"><i class="fusion-dropdown-indicator" aria-hidden="true"></i></span>');
                    });

                    // Submenu toggle logic
                    $('.custom-caret').on('click', function (e) {
                        e.preventDefault();
                        const $submenu = $(this).closest('li').find('.sub-menu').first();
                        const isOpen = $submenu.hasClass('open');

                        if (isOpen) {
                            gsap.to($submenu, {height: 0, opacity: 0, duration: 0.4});
                            $submenu.removeClass('open');
                            $submenu.find('.sub-menu').removeClass('open');
                            $(this).removeClass('rotate');
                        } else {
                            $submenu.addClass('open');
                            $submenu.find('.sub-menu').addClass('open');
                            gsap.set($submenu, {height: 'auto'});
                            gsap.from($submenu, {height: 0, opacity: 0, duration: 0.4});
                            $(this).addClass('rotate');
                        }
                    });

                    // Animate background in
                    gsap.set($menuBg, {y: '100%', autoAlpha: 0});
                    gsap.to($menuBg, {y: '0%', autoAlpha: 1, duration: 0.7, ease: 'power3.out'});

                    // Animate menu in
                    gsap.set($menu, {y: 50, autoAlpha: 0});
                    gsap.to($menu, {y: 0, autoAlpha: 1, duration: 0.6, delay: 0.2, ease: 'power3.out'});

                    // Animate items
                    gsap.fromTo($menuItems, {
                        opacity: 0,
                        y: 30
                    }, {
                        opacity: 1,
                        y: 0,
                        duration: 0.6,
                        delay: 0.3,
                        stagger: 0.05,
                        ease: 'power3.out'
                    });

                } else {
                    // CLOSE SEQUENCE
                    menuOpen = false;
                    resetSubmenus();

                    // Animate menu items out
                    gsap.to($menuItems.get().reverse(), {
                        opacity: 0,
                        y: 30,
                        duration: 0.6,
                        stagger: 0.06,
                        ease: 'power2.in',
                        onComplete: function () {
                            // Then animate menu down
                            gsap.to($menu, {
                                y: 50,
                                autoAlpha: 0,
                                duration: 0.6,
                                ease: 'power3.inOut',
                                onComplete: function () {
                                    // Finally hide background
                                    gsap.to($menuBg, {
                                        y: '100%',
                                        autoAlpha: 0,
                                        duration: 0.7,
                                        ease: 'power3.inOut'
                                    });
                                }
                            });
                        }
                    });
                }
            });
        });


        // Form submit
        $('.wpcf7-submit').on('click', function () {
            $('.contact-form').prepend('<div class="loading-spinner"><img src="/wp-content/themes/Avada-Child/assets/images/Spinner.gif"> </div>');
            setTimeout(function () {
                if ($('.wpcf7-acceptance-as-validation').hasClass('sent')) {
                    // $('.wpcf7-response-output.success').remove();
                    $('.loading-spinner').hide();
                }

                if ($('.wpcf7-acceptance-as-validation').hasClass('invalid')) {
                    $('.loading-spinner').hide();
                }

            }, 5000);
        });

        $('.schade .wpcf7-submit').on('click', function () {
            setTimeout(function () {
                if ($('.wpcf7-acceptance-as-validation').hasClass('sent')) {
                    $('.fusion-alert.fusion-success .fusion-alert-content-wrapper .fusion-alert-content').text('Bedankt, uw bericht is succesvol verzonden.');
                }
            }, 5000);
        });

        // $('.fusion-flyout-menu').append('<div class="menu-cover-title">Menu</div>');
        // *********************************************************************************

        // Equal heights
        $.fn.equalHeights = function () {
            var max_height = 0;
            $(this).each(function () {
                max_height = Math.max($(this).height(), max_height);
            });
            $(this).each(function () {
                $(this).height(max_height);
            });
        };

        $('.equal-height').equalHeights();

        // Cijfers en indexen
        $('.insufeed-category').click(function (e) {
            e.preventDefault();

            $(this).siblings('.cijfers-content-container').slideToggle();

        });

        // Lord icons
        if ($('lord-icon').length) {

            $('.trigger-hover').on('mouseenter', function (e) {
                $(this).find('lord-icon').attr('trigger', 'loop');
            });

            $('.trigger-hover').on('mouseleave', function () {
                $(this).find('lord-icon').attr('trigger', '');
            });
        }

        // News clicktrough
        $('.latest-news-item .card').on('click', function () {
            var url = $(this).find('a').attr('href');
            window.location = url;
        });


        // Animations

        var t = 0.2;
        $('.delay').each(function (i) {
            $(this).css('animation-delay', t + 's');
            t = t + 0.2;
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

        new WOW().init();

        // *********************************************************************************

    });

})(jQuery);