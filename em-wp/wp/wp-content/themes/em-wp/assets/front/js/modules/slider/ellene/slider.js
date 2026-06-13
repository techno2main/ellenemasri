window.emWpInitElleneSlider = function (root) {
    if (!root || root.dataset.emSliderInit === '1') {
        return;
    }

    root.dataset.emSliderInit = '1';

    const slides = Array.from(root.querySelectorAll('.em-slider__slide'));
    const dots = Array.from(root.querySelectorAll('.em-slider__dot'));
    const prevButton = root.querySelector('.em-slider__nav--prev');
    const nextButton = root.querySelector('.em-slider__nav--next');
    const audioBtn = root.querySelector('.em-slider__audio-btn');
    const playBtn = root.querySelector('.em-slider__play');

    if (slides.length === 0) {
        return;
    }

    let currentIndex = 0;
    let autoPlayTimer = null;
    const DEFAULT_AUTOPLAY_DELAY = 5000;

    function getSlideDelay(index) {
        const slide = slides[index];
        if (!slide) {
            return DEFAULT_AUTOPLAY_DELAY;
        }

        const rawDelay = parseInt(slide.getAttribute('data-delay') || '', 10);
        if (Number.isNaN(rawDelay) || rawDelay < 1000) {
            return DEFAULT_AUTOPLAY_DELAY;
        }

        return rawDelay;
    }

    function getActiveVideo(index) {
        const slide = slides[index];
        return slide ? slide.querySelector('video.em-slider__tiktok-video') : null;
    }

    function stopAutoPlay() {
        if (autoPlayTimer) {
            clearTimeout(autoPlayTimer);
            autoPlayTimer = null;
        }
    }

    function isTimedTikTokEmbedSlide(index) {
        const slide = slides[index];
        if (!slide) {
            return false;
        }

        return slide.getAttribute('data-type') === 'tiktok' && !slide.querySelector('video.em-slider__tiktok-video');
    }

    function shouldUseTimedAutoplay(index) {
        const slide = slides[index];
        if (!slide) {
            return true;
        }

        if (slide.getAttribute('data-type') === 'video') {
            return false;
        }

        if (slide.getAttribute('data-type') === 'tiktok') {
            return isTimedTikTokEmbedSlide(index);
        }

        return true;
    }

    function startAutoPlay() {
        if (slides.length <= 1 || !shouldUseTimedAutoplay(currentIndex)) {
            stopAutoPlay();
            return;
        }

        stopAutoPlay();
        autoPlayTimer = setTimeout(function () {
            goToSlide((currentIndex + 1) % slides.length);
            startAutoPlay();
        }, getSlideDelay(currentIndex));
    }

    function updateAudioButtonState(video) {
        if (!audioBtn) {
            return;
        }

        if (!video) {
            audioBtn.classList.add('is-hidden');
            return;
        }

        audioBtn.classList.remove('is-hidden');

        if (video.muted) {
            audioBtn.classList.add('is-muted');
            audioBtn.classList.remove('is-live');
            audioBtn.setAttribute('aria-label', 'Activer le son');
            audioBtn.setAttribute('aria-pressed', 'false');
        } else {
            audioBtn.classList.add('is-live');
            audioBtn.classList.remove('is-muted');
            audioBtn.setAttribute('aria-label', 'Couper le son');
            audioBtn.setAttribute('aria-pressed', 'true');
        }
    }

    function syncActiveVideoPlayback(index) {
        const activeVideo = getActiveVideo(index);

        slides.forEach(function (slide, i) {
            const video = slide.querySelector('video.em-slider__tiktok-video');
            if (!video) {
                return;
            }

            if (i === index) {
                video.currentTime = 0;
                video.muted = false;
                video.volume = 1;

                const playPromise = video.play();
                if (playPromise && typeof playPromise.catch === 'function') {
                    playPromise.catch(function () {
                        video.muted = true;
                        updateAudioButtonState(video);

                        const retryPromise = video.play();
                        if (retryPromise && typeof retryPromise.catch === 'function') {
                            retryPromise.catch(function () {});
                        }
                    });
                }

                return;
            }

            video.pause();
            video.currentTime = 0;
            video.muted = true;
        });

        updateAudioButtonState(activeVideo);

        const activeSlide = slides[index];
        const iframe = activeSlide ? activeSlide.querySelector('iframe') : null;
        if (iframe && iframe.contentWindow) {
            iframe.contentWindow.postMessage(JSON.stringify({ event: 'command', func: 'playVideo', args: [] }), '*');
            iframe.contentWindow.postMessage(JSON.stringify({ event: 'command', func: 'unMute', args: [] }), '*');
        }

        slides.forEach(function (slide, i) {
            if (i === index) {
                return;
            }

            const iframeSlide = slide.querySelector('iframe');
            if (iframeSlide && iframeSlide.contentWindow) {
                iframeSlide.contentWindow.postMessage(JSON.stringify({ event: 'command', func: 'pauseVideo', args: [] }), '*');
            }
        });
    }

    function goToSlide(index) {
        slides.forEach(function (slide, i) {
            slide.classList.toggle('is-active', i === index);
        });

        dots.forEach(function (dot, i) {
            dot.classList.toggle('is-active', i === index);
        });

        currentIndex = index;
        syncActiveVideoPlayback(index);

        if (shouldUseTimedAutoplay(index)) {
            startAutoPlay();
        } else {
            stopAutoPlay();
        }
    }

    function initVideoEndedHandlers() {
        slides.forEach(function (slide) {
            const video = slide.querySelector('video.em-slider__tiktok-video');
            if (!video || video.dataset.emSliderEndedBound === '1') {
                return;
            }

            video.dataset.emSliderEndedBound = '1';
            video.addEventListener('ended', function () {
                const slideIndex = slides.indexOf(slide);
                if (slideIndex !== currentIndex || slides.length <= 1) {
                    return;
                }

                goToSlide((currentIndex + 1) % slides.length);
            });
        });
    }

    if (dots.length > 0 && slides.length > 1) {
        dots.forEach(function (dot) {
            dot.addEventListener('click', function () {
                const target = Number(dot.getAttribute('data-slide-to'));
                if (Number.isFinite(target)) {
                    goToSlide(target);
                }
            });
        });
    }

    if (prevButton) {
        prevButton.addEventListener('click', function () {
            goToSlide((currentIndex - 1 + slides.length) % slides.length);
        });
    }

    if (nextButton) {
        nextButton.addEventListener('click', function () {
            goToSlide((currentIndex + 1) % slides.length);
        });
    }

    if (audioBtn) {
        audioBtn.addEventListener('click', function () {
            const activeVideo = getActiveVideo(currentIndex);
            if (!activeVideo) {
                return;
            }

            activeVideo.muted = !activeVideo.muted;
            updateAudioButtonState(activeVideo);

            if (!activeVideo.paused) {
                const playPromise = activeVideo.play();
                if (playPromise && typeof playPromise.catch === 'function') {
                    playPromise.catch(function () {});
                }
            }
        });
    }

    if (playBtn) {
        playBtn.addEventListener('click', function () {
            goToSlide((currentIndex + 1) % slides.length);
        });
    }

    initVideoEndedHandlers();
    syncActiveVideoPlayback(currentIndex);

    if (slides.length > 1) {
        startAutoPlay();
    }
};
