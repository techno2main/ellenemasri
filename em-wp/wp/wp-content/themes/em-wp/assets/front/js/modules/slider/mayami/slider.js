window.emWpInitMayamiSlider = function (root) {
    if (!root || root.dataset.emSliderInit === '1') {
        return;
    }

    root.dataset.emSliderInit = '1';

    const slides = Array.from(root.querySelectorAll('.em-slider__slide'));
    const dots = Array.from(root.querySelectorAll('.em-slider__dot'));
    const prevButton = root.querySelector('.em-slider__nav--prev');
    const nextButton = root.querySelector('.em-slider__nav--next');
    const audioBtn = root.querySelector('.em-slider__audio-btn');

    if (slides.length === 0) {
        return;
    }

    let currentIndex = 0;
    let autoPlayTimer = null;
    const DEFAULT_AUTOPLAY_DELAY = 5000;
    let uploadsBasePath = null;

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

    function isPrivateOrLocalHostname(hostname) {
        const host = (hostname || '').toLowerCase();
        if (!host) {
            return false;
        }

        if (host === 'localhost' || host === '127.0.0.1') {
            return true;
        }

        if (
            host.endsWith('.local')
            || host.endsWith('.lan')
            || host.endsWith('.home')
            || host.endsWith('.home.arpa')
        ) {
            return true;
        }

        if (/^10\./.test(host) || /^192\.168\./.test(host) || /^169\.254\./.test(host)) {
            return true;
        }

        return /^172\.(1[6-9]|2[0-9]|3[0-1])\./.test(host);
    }

    function isBlockedMediaUrl(rawUrl) {
        if (!rawUrl) {
            return false;
        }

        let parsed;
        try {
            parsed = new URL(rawUrl, window.location.href);
        } catch (e) {
            return false;
        }

        // Local/dev same-origin media must stay allowed (ex: http://localhost:8190/...).
        if (parsed.origin === window.location.origin) {
            return false;
        }

        if (isPrivateOrLocalHostname(parsed.hostname)) {
            return true;
        }

        return window.location.protocol === 'https:' && parsed.protocol === 'http:';
    }

    function detectUploadsBasePath() {
        if (uploadsBasePath !== null) {
            return uploadsBasePath;
        }

        const hintSelectors = [
            'img[src*="/wp/wp-content/uploads/"]',
            'source[src*="/wp/wp-content/uploads/"]',
            'a[href*="/wp/wp-content/uploads/"]'
        ];

        const hasWpSubdir = hintSelectors.some(function (sel) {
            return !!document.querySelector(sel);
        });

        uploadsBasePath = hasWpSubdir ? '/wp/wp-content' : '/wp-content';
        return uploadsBasePath;
    }

    function normalizeMediaUrl(rawUrl) {
        if (!rawUrl) {
            return rawUrl;
        }

        let parsed;
        try {
            parsed = new URL(rawUrl, window.location.href);
        } catch (e) {
            return rawUrl;
        }

        if (!isBlockedMediaUrl(parsed.href)) {
            return parsed.href;
        }

        let nextPath = parsed.pathname || '';
        if (nextPath.startsWith('/wp-content/')) {
            const basePath = detectUploadsBasePath();
            if (basePath === '/wp/wp-content') {
                nextPath = '/wp' + nextPath;
            }
        }

        return window.location.origin + nextPath + (parsed.search || '') + (parsed.hash || '');
    }

    function markVideoUnplayable(video, reason) {
        if (!video || video.dataset.emVideoUnplayable === '1') {
            return;
        }

        video.dataset.emVideoUnplayable = '1';
        video.dataset.emVideoUnplayableReason = reason || 'blocked';
        video.pause();
        video.muted = true;
        video.setAttribute('preload', 'none');

        // Remove blocked URL to prevent repeated network retries and console spam.
        video.removeAttribute('src');
        video.load();

        const slide = video.closest('.em-slider__slide');
        if (slide) {
            slide.setAttribute('data-video-unplayable', 'true');
        }
    }

    function sanitizeBlockedVideos() {
        slides.forEach(function (slide) {
            const image = slide.querySelector('img');
            if (image) {
                const imageSrc = image.getAttribute('src') || '';
                const normalizedImageSrc = normalizeMediaUrl(imageSrc);
                if (normalizedImageSrc && normalizedImageSrc !== imageSrc) {
                    image.setAttribute('src', normalizedImageSrc);
                }
            }

            const video = slide.querySelector('video.em-slider__tiktok-video');
            if (!video) {
                return;
            }

            const src = video.getAttribute('src') || '';
            const normalizedSrc = normalizeMediaUrl(src);
            if (normalizedSrc && normalizedSrc !== src) {
                video.setAttribute('src', normalizedSrc);
            }

            const poster = video.getAttribute('poster') || '';
            const normalizedPoster = normalizeMediaUrl(poster);
            if (normalizedPoster && normalizedPoster !== poster) {
                video.setAttribute('poster', normalizedPoster);
            }

            if (isBlockedMediaUrl(video.getAttribute('src') || '')) {
                markVideoUnplayable(video, 'mixed-content-local-network');
            }
        });
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
                if (video.dataset.emVideoUnplayable === '1') {
                    return;
                }

                video.currentTime = 0;
                video.muted = false;
                video.volume = 1;

                const playPromise = video.play();
                if (playPromise && typeof playPromise.catch === 'function') {
                    playPromise.catch(function (err) {
                        if (video.error || (err && (err.name === 'NotSupportedError' || err.name === 'AbortError'))) {
                            markVideoUnplayable(video, 'playback-error');
                            updateAudioButtonState(null);
                            return;
                        }

                        video.muted = true;
                        updateAudioButtonState(video);

                        const retryPromise = video.play();
                        if (retryPromise && typeof retryPromise.catch === 'function') {
                            retryPromise.catch(function (retryErr) {
                                if (video.error || (retryErr && (retryErr.name === 'NotSupportedError' || retryErr.name === 'AbortError'))) {
                                    markVideoUnplayable(video, 'retry-playback-error');
                                    updateAudioButtonState(null);
                                }
                            });
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

    // Les navigateurs bloquent la lecture audible tant qu'aucune interaction
    // utilisateur n'a eu lieu : ├á l'init la vid├®o retombe en muet. On r├®tablit le
    // son d├¿s le premier geste (clic, touche, toucher) sur la page, comme attendu.
    let soundUnlocked = false;
    function unlockActiveSound() {
        if (soundUnlocked) {
            return;
        }
        soundUnlocked = true;

        const activeVideo = getActiveVideo(currentIndex);
        if (activeVideo) {
            activeVideo.muted = false;
            activeVideo.volume = 1;
            const playPromise = activeVideo.play();
            if (playPromise && typeof playPromise.catch === 'function') {
                playPromise.catch(function () {});
            }
            updateAudioButtonState(activeVideo);
        }

        const activeSlide = slides[currentIndex];
        const iframe = activeSlide ? activeSlide.querySelector('iframe') : null;
        if (iframe && iframe.contentWindow) {
            iframe.contentWindow.postMessage(JSON.stringify({ event: 'command', func: 'unMute', args: [] }), '*');
            iframe.contentWindow.postMessage(JSON.stringify({ event: 'command', func: 'setVolume', args: [100] }), '*');
        }
    }

    ['pointerdown', 'keydown', 'touchstart'].forEach(function (evt) {
        document.addEventListener(evt, unlockActiveSound, { once: true });
    });

    sanitizeBlockedVideos();
    initVideoEndedHandlers();
    syncActiveVideoPlayback(currentIndex);

    if (slides.length > 1) {
        startAutoPlay();
    }
};
