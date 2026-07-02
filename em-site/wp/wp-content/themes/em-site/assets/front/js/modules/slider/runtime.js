(function () {
    if (window.emSiteSliderRuntimeFactory) {
        return;
    }

    function updateAudioButtonState(state, video) {
        if (!state.audioBtn) {
            return;
        }

        if (!video) {
            state.audioBtn.classList.add('is-hidden');
            return;
        }

        state.audioBtn.classList.remove('is-hidden');

        if (video.muted) {
            state.audioBtn.classList.add('is-muted');
            state.audioBtn.classList.remove('is-live');
            state.audioBtn.setAttribute('aria-label', 'Activer le son');
            state.audioBtn.setAttribute('aria-pressed', 'false');
            return;
        }

        state.audioBtn.classList.add('is-live');
        state.audioBtn.classList.remove('is-muted');
        state.audioBtn.setAttribute('aria-label', 'Couper le son');
        state.audioBtn.setAttribute('aria-pressed', 'true');
    }

    function syncIframePlayback(state, activeIndex) {
        const activeSlide = state.slides[activeIndex];
        const activeIframe = activeSlide ? activeSlide.querySelector('iframe') : null;
        if (activeIframe && activeIframe.contentWindow) {
            activeIframe.contentWindow.postMessage(JSON.stringify({ event: 'command', func: 'playVideo', args: [] }), '*');
            activeIframe.contentWindow.postMessage(JSON.stringify({ event: 'command', func: 'unMute', args: [] }), '*');
        }

        state.slides.forEach(function (slide, i) {
            if (i === activeIndex) {
                return;
            }

            const iframe = slide.querySelector('iframe');
            if (iframe && iframe.contentWindow) {
                iframe.contentWindow.postMessage(JSON.stringify({ event: 'command', func: 'pauseVideo', args: [] }), '*');
            }
        });
    }

    function syncVideoPlayback(state, activeIndex, api) {
        const media = window.emSiteSliderMedia || {};
        const markVideoUnplayable = media.markVideoUnplayable || function () {};
        const activeVideo = api.getActiveVideo(state, activeIndex);

        state.slides.forEach(function (slide, i) {
            const video = slide.querySelector('video.em-slider__tiktok-video');
            if (!video) {
                return;
            }

            if (i !== activeIndex) {
                video.pause();
                video.currentTime = 0;
                video.muted = true;
                return;
            }

            if (video.dataset.emVideoUnplayable === '1') {
                return;
            }

            video.currentTime = 0;
            video.muted = false;
            video.volume = 1;

            const playPromise = video.play();
            if (!playPromise || typeof playPromise.catch !== 'function') {
                return;
            }

            playPromise.catch(function (err) {
                if (video.error || (err && (err.name === 'NotSupportedError' || err.name === 'AbortError'))) {
                    markVideoUnplayable(video, 'playback-error');
                    updateAudioButtonState(state, null);
                    return;
                }

                video.muted = true;
                updateAudioButtonState(state, video);

                const retryPromise = video.play();
                if (!retryPromise || typeof retryPromise.catch !== 'function') {
                    return;
                }

                retryPromise.catch(function (retryErr) {
                    if (video.error || (retryErr && (retryErr.name === 'NotSupportedError' || retryErr.name === 'AbortError'))) {
                        markVideoUnplayable(video, 'retry-playback-error');
                        updateAudioButtonState(state, null);
                    }
                });
            });
        });

        updateAudioButtonState(state, activeVideo);
        syncIframePlayback(state, activeIndex);
    }

    function bindControls(state, api, goToSlide) {
        if (state.dots.length > 0 && state.slides.length > 1) {
            state.dots.forEach(function (dot) {
                dot.addEventListener('click', function () {
                    const target = Number(dot.getAttribute('data-slide-to'));
                    if (Number.isFinite(target)) {
                        goToSlide(target);
                    }
                });
            });
        }

        if (state.prevButton) {
            state.prevButton.addEventListener('click', function () {
                goToSlide((state.currentIndex - 1 + state.slides.length) % state.slides.length);
            });
        }

        if (state.nextButton) {
            state.nextButton.addEventListener('click', function () {
                goToSlide((state.currentIndex + 1) % state.slides.length);
            });
        }

        if (state.audioBtn) {
            state.audioBtn.addEventListener('click', function () {
                const activeVideo = api.getActiveVideo(state, state.currentIndex);
                if (!activeVideo) {
                    return;
                }

                activeVideo.muted = !activeVideo.muted;
                updateAudioButtonState(state, activeVideo);

                if (!activeVideo.paused) {
                    const playPromise = activeVideo.play();
                    if (playPromise && typeof playPromise.catch === 'function') {
                        playPromise.catch(function () {});
                    }
                }
            });
        }

        state.slides.forEach(function (slide) {
            const video = slide.querySelector('video.em-slider__tiktok-video');
            if (!video || video.dataset.emSliderEndedBound === '1') {
                return;
            }

            video.dataset.emSliderEndedBound = '1';
            video.addEventListener('ended', function () {
                const slideIndex = state.slides.indexOf(slide);
                if (slideIndex !== state.currentIndex || state.slides.length <= 1) {
                    return;
                }

                goToSlide((state.currentIndex + 1) % state.slides.length);
            });
        });

        ['pointerdown', 'keydown', 'touchstart'].forEach(function (evt) {
            document.addEventListener(evt, function unlockActiveSound() {
                if (state.soundUnlocked) {
                    return;
                }
                state.soundUnlocked = true;

                const activeVideo = api.getActiveVideo(state, state.currentIndex);
                if (activeVideo) {
                    activeVideo.muted = false;
                    activeVideo.volume = 1;
                    const playPromise = activeVideo.play();
                    if (playPromise && typeof playPromise.catch === 'function') {
                        playPromise.catch(function () {});
                    }
                    updateAudioButtonState(state, activeVideo);
                }

                const activeSlide = state.slides[state.currentIndex];
                const iframe = activeSlide ? activeSlide.querySelector('iframe') : null;
                if (iframe && iframe.contentWindow) {
                    iframe.contentWindow.postMessage(JSON.stringify({ event: 'command', func: 'unMute', args: [] }), '*');
                    iframe.contentWindow.postMessage(JSON.stringify({ event: 'command', func: 'setVolume', args: [100] }), '*');
                }
            }, { once: true });
        });
    }

    window.emSiteSliderRuntimeFactory = function (state, api) {
        function goToSlide(index) {
            state.slides.forEach(function (slide, i) {
                slide.classList.toggle('is-active', i === index);
            });

            state.dots.forEach(function (dot, i) {
                dot.classList.toggle('is-active', i === index);
            });

            state.currentIndex = index;
            syncVideoPlayback(state, index, api);

            if (api.shouldUseTimedAutoplay(state, index)) {
                api.stopAutoPlay(state);
                state.autoPlayTimer = setTimeout(function () {
                    goToSlide((state.currentIndex + 1) % state.slides.length);
                }, api.getSlideDelay(state, state.currentIndex));
                return;
            }

            api.stopAutoPlay(state);
        }

        return {
            goToSlide: goToSlide,
            bindControls: function () {
                bindControls(state, api, goToSlide);
            }
        };
    };
})();
