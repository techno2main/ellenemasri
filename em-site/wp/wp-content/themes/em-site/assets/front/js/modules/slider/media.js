(function () {
    if (window.emSiteSliderMedia) {
        return;
    }

    let uploadsBasePath = null;

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
        video.removeAttribute('src');
        video.load();

        const slide = video.closest('.em-slider__slide');
        if (slide) {
            slide.setAttribute('data-video-unplayable', 'true');
        }
    }

    function sanitizeBlockedVideos(slides) {
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

    window.emSiteSliderMedia = {
        markVideoUnplayable: markVideoUnplayable,
        sanitizeBlockedVideos: sanitizeBlockedVideos
    };
})();
