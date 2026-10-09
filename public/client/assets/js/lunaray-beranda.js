    /* Captured before Swiper() runs, since loop mode clones slide DOM nodes
       afterward and swiper.activeIndex/swiper.slides get unreliable with
       those clones — realIndex against this fixed, clone-free array is not. */
    var heroSlideDarkFlags = Array.prototype.map.call(
        document.querySelectorAll('.lunaray-hero-swiper .swiper-slide'),
        function (slide) { return slide.getAttribute('data-bg') === 'dark'; }
    );

    var lunarayHeroSwiper = new Swiper('.lunaray-hero-swiper', {
        slidesPerView: 1,
        loop: true,
        speed: 700,
        effect: 'fade',
        fadeEffect: {
            crossFade: true
        },
        autoplay: {
            delay: 6000,
            disableOnInteraction: false,
        },
        pagination: {
            el: '.lunaray-hero-pagination',
            clickable: true,
        },
        breakpoints: {
            0: { autoHeight: true },
            993: { autoHeight: false },
        },
        on: {
            init: syncHeaderWithSlide,
            slideChangeTransitionStart: syncHeaderWithSlide,
        },
    });

    function syncHeaderWithSlide(swiper) {
        var header = document.getElementById('site-header');
        if (!header) return;
        var isDark = !!heroSlideDarkFlags[swiper.realIndex];
        header.classList.toggle('header-on-dark', isDark);
        if (window.updateLunarayHeaderLogo) window.updateLunarayHeaderLogo();
    }

    (function () {
        var card = document.getElementById('ailunaCard');
        var avatar = document.getElementById('ailunaAvatarWrap');
        if (!card || !avatar) return;

        var stacked = window.matchMedia('(max-width: 992px)');

        function syncAvatarHeight() {
            if (stacked.matches) {
                avatar.style.height = '';
                return;
            }
            var cardHeight = card.getBoundingClientRect().height;
            if (cardHeight > 0) {
                avatar.style.height = cardHeight + 'px';
            }
        }

        syncAvatarHeight();
        window.addEventListener('load', syncAvatarHeight);
        window.addEventListener('resize', syncAvatarHeight);

        if (window.ResizeObserver) {
            new ResizeObserver(syncAvatarHeight).observe(card);
        }
    })();

    (function () {
        var wrap = document.getElementById('ailunaAvatarWrap');
        var canvas = document.getElementById('ailunaAvatarCanvas');
        if (!wrap || !canvas || !canvas.getContext) return;

        if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        var ctx = canvas.getContext('2d');
        var dpr = Math.min(window.devicePixelRatio || 1, 2);

        var revealImg = new Image();
        revealImg.src = "/assets/img/lunaray/content/ailuna.webp";

        var coverCanvas = document.createElement('canvas');
        var coverCtx = coverCanvas.getContext('2d');
        var brushCanvas = document.createElement('canvas');
        var brushCtx = brushCanvas.getContext('2d');

        var brushRadius = 90;
        var decay = 0.02;
        var idle = 0;
        var idleLimit = 100;
        var points = [];
        var last = null;

        function drawCover() {
            if (!revealImg.complete || !revealImg.naturalWidth) return;
            var cw = coverCanvas.width, ch = coverCanvas.height;
            coverCtx.clearRect(0, 0, cw, ch);
            var iw = revealImg.naturalWidth, ih = revealImg.naturalHeight;
            var scale = Math.min(cw / iw, ch / ih);
            var dw = iw * scale, dh = ih * scale;
            var dx = (cw - dw) / 2, dy = (ch - dh) / 2;
            coverCtx.drawImage(revealImg, dx, dy, dw, dh);
        }

        function sizeCanvases() {
            var rect = wrap.getBoundingClientRect();

            canvas.width = Math.max(1, Math.round(rect.width * dpr));
            canvas.height = Math.max(1, Math.round(rect.height * dpr));
            canvas.style.width = rect.width + 'px';
            canvas.style.height = rect.height + 'px';

            coverCanvas.width = canvas.width;
            coverCanvas.height = canvas.height;
            drawCover();

            var diameter = Math.ceil(brushRadius * dpr * 2);
            brushCanvas.width = diameter;
            brushCanvas.height = diameter;
        }

        function stamp(x, y) {
            var r = brushRadius * dpr;
            var d = brushCanvas.width;

            brushCtx.clearRect(0, 0, d, d);
            brushCtx.globalCompositeOperation = 'source-over';
            var grad = brushCtx.createRadialGradient(d / 2, d / 2, 0, d / 2, d / 2, d / 2);
            grad.addColorStop(0, 'rgba(255,255,255,1)');
            grad.addColorStop(0.55, 'rgba(255,255,255,.82)');
            grad.addColorStop(1, 'rgba(255,255,255,0)');
            brushCtx.fillStyle = grad;
            brushCtx.fillRect(0, 0, d, d);

            brushCtx.globalCompositeOperation = 'source-in';
            brushCtx.drawImage(coverCanvas, x - r, y - r, d, d, 0, 0, d, d);

            ctx.globalCompositeOperation = 'source-over';
            ctx.drawImage(brushCanvas, x - r, y - r);
        }

        function onPointerMove(e) {
            var rect = wrap.getBoundingClientRect();
            var margin = brushRadius;
            if (e.clientX < rect.left - margin || e.clientX > rect.right + margin ||
                e.clientY < rect.top - margin || e.clientY > rect.bottom + margin) {
                last = null;
                return;
            }

            var p = { x: (e.clientX - rect.left) * dpr, y: (e.clientY - rect.top) * dpr };

            if (last) {
                var dx = p.x - last.x, dy = p.y - last.y;
                var dist = Math.sqrt(dx * dx + dy * dy);
                var step = Math.max(brushRadius * dpr * 0.3, 1);
                var n = Math.min(Math.ceil(dist / step), 60);
                for (var i = 1; i <= n; i++) {
                    points.push({ x: last.x + (dx * i) / n, y: last.y + (dy * i) / n });
                }
            } else {
                points.push(p);
            }
            last = p;
        }

        window.addEventListener('pointermove', onPointerMove, { passive: true });

        function tick() {
            if (points.length) {
                idle = 0;
            } else {
                idle++;
            }

            if (idle <= idleLimit) {
                var fade = points.length ? decay : Math.min(decay + idle * 0.004, 0.5);
                ctx.globalCompositeOperation = 'destination-out';
                ctx.fillStyle = 'rgba(0,0,0,' + fade + ')';
                ctx.fillRect(0, 0, canvas.width, canvas.height);

                while (points.length) {
                    var pt = points.shift();
                    stamp(pt.x, pt.y);
                }
            } else if (idle === idleLimit + 1) {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            }

            requestAnimationFrame(tick);
        }

        if (revealImg.complete) {
            sizeCanvases();
        } else {
            revealImg.onload = sizeCanvases;
        }

        window.addEventListener('resize', sizeCanvases);
        if (window.ResizeObserver) {
            new ResizeObserver(sizeCanvases).observe(wrap);
        }

        requestAnimationFrame(tick);
    })();

    (function () {
        var orbit = document.getElementById('pathOrbit');
        var panelCard = document.getElementById('pathPanel');
        if (!orbit || !panelCard) return;

        var orbs = orbit.querySelectorAll('.path-orb');
        var panels = panelCard.querySelectorAll('.path-panel');
        var accordions = orbit.querySelectorAll('.path-accordion');
        var isMobile = window.matchMedia('(max-width: 992px)');

        orbs.forEach(function (orb) {
            orb.addEventListener('click', function () {
                var key = orb.getAttribute('data-path');
                // On mobile, tapping the already-open chip closes it instead
                // of forcing something to always stay expanded.
                var closing = isMobile.matches && orb.classList.contains('is-active');

                orbs.forEach(function (o) {
                    var active = !closing && o === orb;
                    o.classList.toggle('is-active', active);
                    o.setAttribute('aria-expanded', active ? 'true' : 'false');
                });
                panels.forEach(function (p) {
                    p.classList.toggle('is-active', !closing && p.getAttribute('data-path') === key);
                });
                accordions.forEach(function (a) {
                    a.classList.toggle('is-open', !closing && a.getAttribute('data-path') === key);
                });

                if (!closing && isMobile.matches) {
                    var item = orb.closest('.path-item');
                    if (item) {
                        setTimeout(function () {
                            item.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        }, 80);
                    }
                }
            });
        });
    })();

    (function () {
        var items = document.querySelectorAll('.faq-item');

        items.forEach(function (item) {
            var button = item.querySelector('.faq-question');
            if (!button) return;

            button.addEventListener('click', function () {
                var opening = !item.classList.contains('is-open');

                items.forEach(function (other) {
                    var shouldOpen = opening && other === item;
                    other.classList.toggle('is-open', shouldOpen);
                    var otherButton = other.querySelector('.faq-question');
                    if (otherButton) otherButton.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
                });
            });
        });
    })();

