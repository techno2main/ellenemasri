(function () {
    if (window.emSiteInitSlider) {
        return;
    }

    function createSliderState(root) {
        return {
            root: root,
            slides: Array.from(root.querySelectorAll('.em-slider__slide')),
            dots: Array.from(root.querySelectorAll('.em-slider__dot')),
            prevButton: root.querySelector('.em-slider__nav--prev'),
            nextButton: root.querySelector('.em-slider__nav--next'),
            audioBtn: root.querySelector('.em-slider__audio-btn'),
            currentIndex: 0,
            autoPlayTimer: null,
            soundUnlocked: false,
            defaultDelay: 5000
        };
    }

    function getSlideDelay(state, index) {
        const slide = state.slides[index];
        if (!slide) {
            return state.defaultDelay;
        }

        const rawDelay = parseInt(slide.getAttribute('data-delay') || '', 10);
        if (Number.isNaN(rawDelay) || rawDelay < 1000) {
            return state.defaultDelay;
        }

        return rawDelay;
    }

    function getActiveVideo(state, index) {
        const slide = state.slides[index];
        return slide ? slide.querySelector('video.em-slider__tiktok-video') : null;
    }

    function stopAutoPlay(state) {
        if (state.autoPlayTimer) {
            clearTimeout(state.autoPlayTimer);
            state.autoPlayTimer = null;
        }
    }

    function isTimedTikTokEmbedSlide(state, index) {
        const slide = state.slides[index];
        if (!slide) {
            return false;
        }

        return slide.getAttribute('data-type') === 'tiktok' && !slide.querySelector('video.em-slider__tiktok-video');
    }

    function shouldUseTimedAutoplay(state, index) {
        const slide = state.slides[index];
        if (!slide) {
            return true;
        }

        if (slide.getAttribute('data-type') === 'video') {
            return false;
        }

        if (slide.getAttribute('data-type') === 'tiktok') {
            return isTimedTikTokEmbedSlide(state, index);
        }

        return true;
    }

    window.emSiteInitSlider = function (root) {
        if (!root || root.dataset.emSliderInit === '1') {
            return;
        }

        const state = createSliderState(root);
        if (state.slides.length === 0) {
            return;
        }

        root.dataset.emSliderInit = '1';

        if (window.emSiteSliderMedia && typeof window.emSiteSliderMedia.sanitizeBlockedVideos === 'function') {
            window.emSiteSliderMedia.sanitizeBlockedVideos(state.slides);
        }

        if (typeof window.emSiteSliderRuntimeFactory !== 'function') {
            return;
        }

        const runtime = window.emSiteSliderRuntimeFactory(state, {
            getSlideDelay: getSlideDelay,
            getActiveVideo: getActiveVideo,
            stopAutoPlay: stopAutoPlay,
            shouldUseTimedAutoplay: shouldUseTimedAutoplay
        });

        runtime.bindControls();
        runtime.goToSlide(0);
    };
})();
