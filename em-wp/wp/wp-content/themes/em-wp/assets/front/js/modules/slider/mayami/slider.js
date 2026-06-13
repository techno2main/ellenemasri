(function () {
    function initSlider(root) {
        const slides = Array.from(root.querySelectorAll('.em-slider__slide'));
        const dots = Array.from(root.querySelectorAll('.em-slider__dot'));
        const prev = root.querySelector('.em-slider__nav--prev');
        const next = root.querySelector('.em-slider__nav--next');
        const muteBtn = root.querySelector('.em-slider__mute');
        const playBtn = root.querySelector('.em-slider__play');

        if (slides.length <= 1) {
            const onlySlide = slides[0];
            if (onlySlide) {
                const media = onlySlide.querySelector('video');
                if (media) {
                    media.muted = false;
                    media.volume = 1;
                    media.play().catch(function () {});
                }
            }
            return;
        }

        let activeIndex = 0;
        let timer = null;
        let isSoundOn = true;

        function getSlideDelay(index) {
            const slide = slides[index];
            if (!slide) {
                return 5000;
            }

            const delay = Number(slide.getAttribute('data-delay') || '5000');
            return Number.isFinite(delay) && delay > 0 ? delay : 5000;
        }

        function stopSlideMedia(slide) {
            if (!slide) {
                return;
            }

            const video = slide.querySelector('video');
            if (video) {
                video.pause();
            }

            const iframe = slide.querySelector('iframe');
            if (iframe && iframe.contentWindow) {
                iframe.contentWindow.postMessage(JSON.stringify({ event: 'command', func: 'pauseVideo', args: [] }), '*');
            }
        }

        function playSlideMedia(slide) {
            if (!slide) {
                return;
            }

            const video = slide.querySelector('video');
            if (video) {
                video.muted = !isSoundOn;
                video.volume = 1;
                video.currentTime = 0;
                video.play().catch(function () {});
            }

            const iframe = slide.querySelector('iframe');
            if (iframe && iframe.contentWindow) {
                iframe.contentWindow.postMessage(JSON.stringify({ event: 'command', func: 'playVideo', args: [] }), '*');
                iframe.contentWindow.postMessage(JSON.stringify({ event: 'command', func: isSoundOn ? 'unMute' : 'mute', args: [] }), '*');
            }
        }

        function scheduleNext() {
            window.clearTimeout(timer);
            timer = window.setTimeout(function () {
                render(activeIndex + 1);
            }, getSlideDelay(activeIndex));
        }

        function refreshSoundUi() {
            if (!muteBtn) {
                return;
            }

            muteBtn.setAttribute('aria-pressed', isSoundOn ? 'true' : 'false');
            muteBtn.setAttribute('aria-label', isSoundOn ? 'Couper le son' : 'Activer le son');
            muteBtn.textContent = isSoundOn ? '🔊' : '🔇';
        }

        function render(index) {
            const oldSlide = slides[activeIndex];
            activeIndex = (index + slides.length) % slides.length;

            slides.forEach(function (slide, idx) {
                slide.classList.toggle('is-active', idx === activeIndex);
            });

            dots.forEach(function (dot, idx) {
                dot.classList.toggle('is-active', idx === activeIndex);
            });

            stopSlideMedia(oldSlide);
            playSlideMedia(slides[activeIndex]);
            scheduleNext();
        }

        if (prev) {
            prev.addEventListener('click', function () {
                render(activeIndex - 1);
            });
        }

        if (next) {
            next.addEventListener('click', function () {
                render(activeIndex + 1);
            });
        }

        dots.forEach(function (dot) {
            dot.addEventListener('click', function () {
                const target = Number(dot.getAttribute('data-slide-to'));
                if (Number.isFinite(target)) {
                    render(target);
                }
            });
        });

        if (muteBtn) {
            muteBtn.addEventListener('click', function () {
                isSoundOn = !isSoundOn;
                refreshSoundUi();
                playSlideMedia(slides[activeIndex]);
            });
        }

        if (playBtn) {
            playBtn.addEventListener('click', function () {
                render(activeIndex + 1);
            });
        }

        refreshSoundUi();
        playSlideMedia(slides[0]);
        scheduleNext();
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.em-slider--mayami[data-em-slider]').forEach(initSlider);
    });
})();
