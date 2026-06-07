

        const imageUpload = document.getElementById('imageUpload');

        const uploadZone = document.getElementById('uploadZone');

        const uploadFromMediaBtn = document.getElementById('uploadFromMediaBtn');

        const replaceImageFileBtn = document.getElementById('replaceImageFileBtn');

        const replaceImageMediaBtn = document.getElementById('replaceImageMediaBtn');

        const canvasWrapper = document.getElementById('canvasWrapper');

        const imageCanvas = document.getElementById('imageCanvas');

        const overlayContainer = document.getElementById('overlayContainer');

        const zonesList = document.getElementById('zonesList');

        const previewBtn = document.getElementById('previewBtn');

        const clearAllBtn = document.getElementById('clearAllBtn');

        const saveDraftBtn = document.getElementById('saveDraftBtn');

        const draftNameInput = document.getElementById('draftNameInput');

        const pdfHeaderActive = document.getElementById('pdfHeaderActive');

        const pdfHeaderInactive = document.getElementById('pdfHeaderInactive');

        const pdfCtaTextInput = document.getElementById('pdfCtaTextInput');

        const pdfUrlInput = document.getElementById('pdfUrlInput');

        const draftStatus = document.getElementById('draftStatus');

        const exportQuickActions = document.getElementById('exportQuickActions');

        const exportQuickUrl = document.getElementById('exportQuickUrl');

        const copyExportQuickBtn = document.getElementById('copyExportQuickBtn');

        const downloadExportQuickBtn = document.getElementById('downloadExportQuickBtn');

        const downloadExportCodeQuickBtn = document.getElementById('downloadExportCodeQuickBtn');

        const zoneCount = document.getElementById('zoneCount');

        const linkedCount = document.getElementById('linkedCount');

        const addZoneBtn = document.getElementById('addZoneBtn');

        const confirmModal = document.getElementById('confirmModal');

        const confirmModalTitle = document.getElementById('confirmModalTitle');

        const confirmModalMessage = document.getElementById('confirmModalMessage');

        const confirmModalCancel = document.getElementById('confirmModalCancel');

        const confirmModalConfirm = document.getElementById('confirmModalConfirm');

        const exportResultModal = document.getElementById('exportResultModal');

        const exportResultUrl = document.getElementById('exportResultUrl');

        const copyExportUrlBtn = document.getElementById('copyExportUrlBtn');

        const downloadExportHtmlBtn = document.getElementById('downloadExportHtmlBtn');

        const downloadExportCodeBtn = document.getElementById('downloadExportCodeBtn');

        const closeExportResultModalBtn = document.getElementById('closeExportResultModalBtn');

        const pdfInlineAlert = document.getElementById('pdfInlineAlert');

        const pdfFieldsWrap = document.getElementById('pdfFieldsWrap');



        const queryParams = new URLSearchParams(window.location.search);

        function toSameOriginUrl(rawUrl) {
            const value = String(rawUrl || '').trim();
            if (!value) {
                return '';
            }

            try {
                const parsed = new URL(value, window.location.origin);
                if (parsed.origin === window.location.origin) {
                    return parsed.toString();
                }

                return parsed.pathname + parsed.search + parsed.hash;
            } catch (error) {
                return value;
            }
        }

        const wpAjaxUrl = toSameOriginUrl(queryParams.get('wp_ajax_url') || '') || '/wp-admin/admin-ajax.php';

        const wpNonce = queryParams.get('wp_nonce') || '';



        let zones = [];

        let isDrawing = false;

        let startX, startY;

        let currentRect = null;

        let imageUrl = '';

        let activeZoneId = null;

        let currentDraftId = queryParams.get('visual_links_draft_id') || queryParams.get('epk_draft_id') || '';

        let lastSavedSignature = '';

        let lastSavedPreviewGateSignature = '';

        let isResizing = false;

        let resizeState = null;

        let isDragging = false;

        let dragState = null;

        let suppressDragUntil = 0;

        let isAddZoneMode = false;

        let mediaFrame = null;

        let lastExportedHtmlDocument = '';

        let lastExportedFilename = 'visual-links.html';

        let lastExportedPublicUrl = '';

        let lastTemplateEmailHtmlDocument = '';

        let lastTemplateEmailFilename = '';

        let lastTemplateEmailPublicUrl = '';

        let lastTemplateEmailExportUpdatedAt = '';

        let lastTemplateHtmlHtmlDocument = '';

        let lastTemplateHtmlFilename = '';

        let lastTemplateHtmlPublicUrl = '';

        let lastTemplateHtmlExportUpdatedAt = '';

        let lastDraftUpdatedAt = '';

        let copyFeedbackTimer = null;



        const MIN_ZONE_SIZE = 12;

        const DRAG_START_THRESHOLD = 4;



        function getZoneById(zoneId) {

            return zones.find(z => String(z.id) === String(zoneId));

        }



        function setAddZoneMode(enabled) {

            isAddZoneMode = !!enabled;

            addZoneBtn.classList.toggle('is-active', isAddZoneMode);

            addZoneBtn.setAttribute('aria-pressed', isAddZoneMode ? 'true' : 'false');

            canvasWrapper.classList.toggle('is-add-zone-mode', isAddZoneMode);

        }



        function askDeleteConfirmation(message) {

            return new Promise((resolve) => {

                confirmModalTitle.textContent = 'Confirmation';

                confirmModalMessage.textContent = message;

                confirmModal.classList.add('is-open');

                confirmModal.setAttribute('aria-hidden', 'false');



                const onCancel = () => {

                    cleanup();

                    resolve(false);

                };



                const onConfirm = () => {

                    cleanup();

                    resolve(true);

                };



                const onBackdrop = (event) => {

                    if (event.target === confirmModal) {

                        cleanup();

                        resolve(false);

                    }

                };



                const onKeyDown = (event) => {

                    if (event.key === 'Escape') {

                        cleanup();

                        resolve(false);

                    }

                };



                function cleanup() {

                    confirmModal.classList.remove('is-open');

                    confirmModal.setAttribute('aria-hidden', 'true');

                    confirmModalCancel.removeEventListener('click', onCancel);

                    confirmModalConfirm.removeEventListener('click', onConfirm);

                    confirmModal.removeEventListener('click', onBackdrop);

                    window.removeEventListener('keydown', onKeyDown);

                }



                confirmModalCancel.addEventListener('click', onCancel);

                confirmModalConfirm.addEventListener('click', onConfirm);

                confirmModal.addEventListener('click', onBackdrop);

                window.addEventListener('keydown', onKeyDown);

                confirmModalCancel.focus();

            });

        }



        function closeExportResultModal() {

            exportResultModal.classList.remove('is-open');

            exportResultModal.setAttribute('aria-hidden', 'true');

        }



        function setExportQuickActionsVisible(visible) {

            exportQuickActions.classList.toggle('is-visible', !!visible);

        }



        function clearExportResultState() {

            lastExportedPublicUrl = '';

            lastExportedHtmlDocument = '';

            lastExportedFilename = 'visual-links.html';

            lastTemplateEmailHtmlDocument = '';

            lastTemplateEmailFilename = '';

            lastTemplateEmailPublicUrl = '';

            lastTemplateEmailExportUpdatedAt = '';

            lastTemplateHtmlHtmlDocument = '';

            lastTemplateHtmlFilename = '';

            lastTemplateHtmlPublicUrl = '';

            lastTemplateHtmlExportUpdatedAt = '';

            exportQuickUrl.value = '';

            copyExportQuickBtn.disabled = true;

            setExportDownloadButtonsEnabled(false);

            setExportQuickActionsVisible(false);

        }



        function setExportDownloadButtonsEnabled(enabled) {

            const isEnabled = !!enabled;

            downloadExportQuickBtn.disabled = !isEnabled;

            downloadExportCodeQuickBtn.disabled = !isEnabled;

            downloadExportHtmlBtn.disabled = !isEnabled;

            downloadExportCodeBtn.disabled = !isEnabled;

        }



        function applyExportResult(url, filename, htmlDocument) {

            lastExportedPublicUrl = String(url || '').trim();

            lastExportedFilename = String(filename || 'visual-links.html').trim() || 'visual-links.html';

            lastExportedHtmlDocument = String(htmlDocument || '');



            exportQuickUrl.value = lastExportedPublicUrl || 'URL non disponible';

            copyExportQuickBtn.disabled = !lastExportedPublicUrl;

            setExportDownloadButtonsEnabled(!!lastExportedPublicUrl);

            setExportQuickActionsVisible(true);

        }



        function applyTemplateEmailExportResult(url, filename, htmlDocument) {

            lastTemplateEmailPublicUrl = String(url || '').trim();

            lastTemplateEmailFilename = String(filename || '').trim();

            lastTemplateEmailHtmlDocument = String(htmlDocument || '');

            applyExportResult(lastTemplateEmailPublicUrl, lastTemplateEmailFilename || 'template-email.html', lastTemplateEmailHtmlDocument);

        }



        function applyTemplateHtmlExportResult(url, filename, htmlDocument) {

            lastTemplateHtmlPublicUrl = String(url || '').trim();

            lastTemplateHtmlFilename = String(filename || '').trim();

            lastTemplateHtmlHtmlDocument = String(htmlDocument || '');

            applyExportResult(lastTemplateHtmlPublicUrl, lastTemplateHtmlFilename || 'visual-links.html', lastTemplateHtmlHtmlDocument);

        }



        function showExportResultModal(url, filename, htmlDocument) {

            const cleanUrl = String(url || '').trim();

            applyExportResult(cleanUrl, filename, htmlDocument);



            exportResultUrl.value = cleanUrl || 'URL non disponible';

            copyExportUrlBtn.disabled = !cleanUrl;



            exportResultModal.classList.add('is-open');

            exportResultModal.setAttribute('aria-hidden', 'false');

            closeExportResultModalBtn.focus();

        }



        async function copyExportUrlToClipboard() {

            const urlValue = String(exportResultUrl.value || '').trim();

            if (!urlValue || urlValue === 'URL non disponible') {

                return;

            }



            try {

                if (navigator.clipboard && navigator.clipboard.writeText) {

                    await navigator.clipboard.writeText(urlValue);

                } else {

                    exportResultUrl.focus();

                    exportResultUrl.select();

                    document.execCommand('copy');

                }

                if (copyFeedbackTimer) {

                    window.clearTimeout(copyFeedbackTimer);

                }

                copyExportUrlBtn.textContent = 'CopiÃ©';

                copyExportUrlBtn.disabled = true;

                copyFeedbackTimer = window.setTimeout(() => {

                    copyExportUrlBtn.textContent = 'Copier l\'URL';

                    copyExportUrlBtn.disabled = false;

                    copyFeedbackTimer = null;

                }, 1500);

                setDraftStatus('URL export copiÃ©e dans le presse-papiers.');

            } catch (error) {

                setDraftStatus('Impossible de copier automatiquement l\'URL. Copiez-la manuellement.', true);

            }

        }



        async function copyQuickExportUrlToClipboard() {

            const urlValue = String(exportQuickUrl.value || '').trim();

            if (!urlValue || urlValue === 'URL non disponible') {

                return;

            }



            try {

                if (navigator.clipboard && navigator.clipboard.writeText) {

                    await navigator.clipboard.writeText(urlValue);

                } else {

                    exportQuickUrl.focus();

                    exportQuickUrl.select();

                    document.execCommand('copy');

                }

                if (copyFeedbackTimer) {

                    window.clearTimeout(copyFeedbackTimer);

                }

                copyExportQuickBtn.textContent = 'CopiÃ©';

                copyExportQuickBtn.disabled = true;

                copyFeedbackTimer = window.setTimeout(() => {

                    copyExportQuickBtn.textContent = 'Copier l\'URL';

                    copyExportQuickBtn.disabled = false;

                    copyFeedbackTimer = null;

                }, 1500);

                setDraftStatus('URL export copiÃ©e dans le presse-papiers.');

            } catch (error) {

                setDraftStatus('Impossible de copier automatiquement l\'URL. Copiez-la manuellement.', true);

            }

        }



        async function ensureLastExportedHtmlDocument() {

            const cachedHtml = String(lastExportedHtmlDocument || '');

            if (cachedHtml) {

                return cachedHtml;

            }



            const exportUrl = toSameOriginUrl(lastExportedPublicUrl);

            if (!exportUrl || exportUrl === 'URL non disponible') {

                return '';

            }



            try {

                const response = await fetch(exportUrl, {

                    method: 'GET',

                    credentials: 'same-origin'

                });



                if (!response.ok) {

                    return '';

                }



                const html = await response.text();

                lastExportedHtmlDocument = String(html || '');

                return lastExportedHtmlDocument;

            } catch (error) {

                return '';

            }

        }



        async function ensureTemplateEmailHtmlDocument() {

            const cachedHtml = String(lastTemplateEmailHtmlDocument || '');

            if (cachedHtml) {

                return cachedHtml;

            }



            const exportUrl = toSameOriginUrl(lastTemplateEmailPublicUrl);

            if (!exportUrl || exportUrl === 'URL non disponible') {

                return '';

            }



            try {

                const response = await fetch(exportUrl, {

                    method: 'GET',

                    credentials: 'same-origin'

                });



                if (!response.ok) {

                    return '';

                }



                const html = await response.text();

                lastTemplateEmailHtmlDocument = String(html || '');

                return lastTemplateEmailHtmlDocument;

            } catch (error) {

                return '';

            }

        }



        async function downloadLastExportHtml() {

            const html = await ensureLastExportedHtmlDocument();

            if (!html) {

                setDraftStatus('Aucun contenu HTML Ã  tÃ©lÃ©charger.', true);

                return;

            }



            const blob = new Blob([html], { type: 'text/html;charset=utf-8' });

            const objectUrl = URL.createObjectURL(blob);

            const downloadLink = document.createElement('a');

            downloadLink.href = objectUrl;

            downloadLink.download = lastExportedFilename || 'visual-links.html';

            document.body.appendChild(downloadLink);

            downloadLink.click();

            document.body.removeChild(downloadLink);

            URL.revokeObjectURL(objectUrl);

        }



        async function downloadLastExportHtmlCode() {

            const html = await ensureTemplateEmailHtmlDocument() || await ensureLastExportedHtmlDocument();

            if (!html) {

                setDraftStatus('Aucun code HTML Ã  exporter.', true);

                return;

            }



            const normalizedHtml = html

                .replace(/\r\n/g, '\n')

                .replace(/\r/g, '\n')

                .split('\n')

                .map((line) => line.replace(/[ \t]+$/g, ''))

                .join('\n')

                .trim();



            const preferredFilename = String(lastTemplateEmailFilename || lastExportedFilename || 'visual-links.html');

            const baseFilename = preferredFilename.replace(/\.html?$/i, '');

            const codeFilename = (baseFilename || 'visual-links') + '-code.txt';



            const blob = new Blob([normalizedHtml], { type: 'text/plain;charset=utf-8' });

            const objectUrl = URL.createObjectURL(blob);

            const downloadLink = document.createElement('a');

            downloadLink.href = objectUrl;

            downloadLink.download = codeFilename;

            document.body.appendChild(downloadLink);

            downloadLink.click();

            document.body.removeChild(downloadLink);

            URL.revokeObjectURL(objectUrl);



            setDraftStatus('Fichier code HTML copiable tÃ©lÃ©chargÃ©.');

        }



        function getCanvasDimensions() {

            const rect = imageCanvas.getBoundingClientRect();

            const width = Math.round(rect.width || imageCanvas.clientWidth || imageCanvas.width || 0);

            const height = Math.round(rect.height || imageCanvas.clientHeight || imageCanvas.height || 0);

            return { width, height };

        }



        function waitForImageReady() {

            return new Promise((resolve) => {

                if (imageCanvas.complete && imageCanvas.naturalWidth > 0) {

                    resolve();

                    return;

                }



                const onLoad = () => {

                    imageCanvas.removeEventListener('load', onLoad);

                    imageCanvas.removeEventListener('error', onError);

                    resolve();

                };



                const onError = () => {

                    imageCanvas.removeEventListener('load', onLoad);

                    imageCanvas.removeEventListener('error', onError);

                    resolve();

                };



                imageCanvas.addEventListener('load', onLoad);

                imageCanvas.addEventListener('error', onError);

            });

        }



        function cloneZones() {

            return zones.map((zone) => ({

                id: zone.id,

                x: Number(zone.x) || 0,

                y: Number(zone.y) || 0,

                width: Number(zone.width) || 0,

                height: Number(zone.height) || 0,

                hrefType: zone.hrefType === 'anchor' ? 'anchor' : 'url',

                hrefValue: zone.hrefValue || ''

            }));

        }



        function scaleZonesForNewImage(baseZones, sourceDims, targetDims) {

            const sx = sourceDims.width > 0 ? (targetDims.width / sourceDims.width) : 1;

            const sy = sourceDims.height > 0 ? (targetDims.height / sourceDims.height) : 1;



            return baseZones.map((zone) => {

                const x = Math.max(0, Math.round((Number(zone.x) || 0) * sx));

                const y = Math.max(0, Math.round((Number(zone.y) || 0) * sy));

                const width = Math.max(1, Math.round((Number(zone.width) || 0) * sx));

                const height = Math.max(1, Math.round((Number(zone.height) || 0) * sy));



                const maxWidth = Math.max(1, targetDims.width - x);

                const maxHeight = Math.max(1, targetDims.height - y);



                return {

                    id: zone.id,

                    x,

                    y,

                    width: Math.min(width, maxWidth),

                    height: Math.min(height, maxHeight),

                    hrefType: zone.hrefType,

                    hrefValue: zone.hrefValue || ''

                };

            }).filter((zone) => zone.width > 0 && zone.height > 0);

        }



        async function setImageSource(source, options = {}) {

            const preserveZones = !!options.preserveZones;

            const beforeDims = getCanvasDimensions();

            const previousZones = preserveZones ? cloneZones() : [];



            imageUrl = source;

            imageCanvas.src = source;

            uploadZone.style.display = 'none';

            canvasWrapper.style.display = 'inline-block';

            setAddZoneMode(false);



            await waitForImageReady();



            if (!(imageCanvas.naturalWidth > 0 && imageCanvas.naturalHeight > 0)) {

                setDraftStatus('Impossible de charger l\'image sÃ©lectionnÃ©e.', true);

                return;

            }



            const afterDims = getCanvasDimensions();

            if (preserveZones && previousZones.length > 0) {

                zones = scaleZonesForNewImage(previousZones, beforeDims, afterDims);

                renderZoneOverlays();

                updateZonesList();

                markDraftDirty();

                setDraftStatus('Image remplacÃ©e. Zones conservÃ©es et recalÃ©es.');

                return;

            }



            zones = [];

            activeZoneId = null;

            overlayContainer.innerHTML = '';

            updateZonesList();

            markDraftDirty();

            setDraftStatus('Image chargÃ©e. Ajoutez vos zones puis enregistrez le visuel.');

        }



        function loadImage(file, options = {}) {

            if (!file) {

                return;

            }



            const reader = new FileReader();

            reader.onload = async (e) => {

                const src = e && e.target ? e.target.result : '';

                if (!src) {

                    setDraftStatus('Impossible de lire le fichier image.', true);

                    return;

                }



                await setImageSource(src, options);

            };

            reader.readAsDataURL(file);

        }



        function getWordPressMediaApi() {

            try {

                if (window.parent && window.parent !== window && window.parent.wp && window.parent.wp.media) {

                    return window.parent.wp.media;

                }

            } catch (error) {

                // Access to parent can fail due to browser policies.

            }



            try {

                if (window.top && window.top.wp && window.top.wp.media) {

                    return window.top.wp.media;

                }

            } catch (error) {

                // Access to top can fail due to browser policies.

            }



            if (window.wp && window.wp.media) {

                return window.wp.media;

            }



            return null;

        }



        function openMediaLibrary(options = {}) {

            const mediaApi = getWordPressMediaApi();

            if (!mediaApi) {

                setDraftStatus('BibliothÃ¨que mÃ©dia indisponible sur cette page.', true);

                return;

            }



            if (!mediaFrame) {

                mediaFrame = mediaApi({

                    title: 'Choisir un visuel',

                    button: {

                        text: 'Utiliser ce visuel'

                    },

                    library: {

                        type: 'image'

                    },

                    multiple: false

                });



                mediaFrame.on('select', async () => {

                    const selection = mediaFrame.state().get('selection');

                    const attachment = selection && selection.first ? selection.first().toJSON() : null;

                    const selectedUrl = attachment && attachment.url ? attachment.url : '';



                    if (!selectedUrl) {

                        setDraftStatus('Aucune image valide sÃ©lectionnÃ©e.', true);

                        return;

                    }



                    await setImageSource(selectedUrl, {

                        preserveZones: zones.length > 0

                    });

                });

            }



            mediaFrame.open();

        }



        function normalizePdfUrl(rawValue) {

            const raw = String(rawValue || '').trim();

            if (!raw) {

                return '';

            }



            if (/^https?:\/\//i.test(raw)) {

                return raw;

            }



            if (/^\/\//.test(raw)) {

                return window.location.protocol + raw;

            }



            if (/^\//.test(raw)) {

                return window.location.origin + raw;

            }



            if (/^wp-content\//i.test(raw)) {

                return window.location.origin + '/' + raw;

            }



            return raw;

        }



        function normalizePdfCtaText(rawValue) {

            const fallback = 'DOWNLOAD THIS MAYAMI ONESHEET - CLICKABLE IN PDF';

            const value = String(rawValue || '').trim().replace(/\s+/g, ' ');

            return value || fallback;

        }



        function isPdfHeaderEnabled() {

            return !!(pdfHeaderActive && pdfHeaderActive.checked);

        }



        function serializeDraftPayload() {

            const dims = getCanvasDimensions();

            return {

                imageUrl: imageUrl || '',

                pdfHeaderEnabled: isPdfHeaderEnabled(),

                pdfCtaText: normalizePdfCtaText(pdfCtaTextInput.value),

                pdfUrl: normalizePdfUrl(pdfUrlInput.value),

                canvasWidth: dims.width,

                canvasHeight: dims.height,

                zones: zones.map(zone => ({

                    id: zone.id,

                    x: Number(zone.x) || 0,

                    y: Number(zone.y) || 0,

                    width: Number(zone.width) || 0,

                    height: Number(zone.height) || 0,

                    hrefType: zone.hrefType === 'anchor' ? 'anchor' : 'url',

                    hrefValue: zone.hrefValue || ''

                }))

            };

        }



        function getCurrentSignature() {

            return JSON.stringify(serializeDraftPayload());

        }



        function getCurrentPreviewGateSignature() {

            const payload = serializeDraftPayload();

            const gatePayload = {

                imageUrl: payload.imageUrl || '',

                canvasWidth: Number(payload.canvasWidth) || 0,

                canvasHeight: Number(payload.canvasHeight) || 0,

                zones: Array.isArray(payload.zones) ? payload.zones : []

            };

            return JSON.stringify(gatePayload);

        }



        function setDraftStatus(text, isError = false) {

            draftStatus.textContent = text;

            draftStatus.style.color = isError ? '#b91c1c' : '#475569';

        }



        function setPdfInlineAlertVisible(visible, message) {

            if (!pdfInlineAlert) {

                return;

            }

            if (typeof message === 'string' && message.trim() !== '') {

                pdfInlineAlert.textContent = message.trim();

            }

            pdfInlineAlert.classList.toggle('is-open', !!visible);

        }



        function syncPdfFieldsVisibility() {

            if (!pdfFieldsWrap) {

                return;

            }

            pdfFieldsWrap.style.display = isPdfHeaderEnabled() ? '' : 'none';

        }



        function updateGenerateButtonState() {

            const hasSavedServerState = !!currentDraftId && !!lastSavedPreviewGateSignature && getCurrentPreviewGateSignature() === lastSavedPreviewGateSignature;

            const hasZones = zones.length > 0;

            const canRun = hasSavedServerState && hasZones;

            previewBtn.disabled = !canRun;

        }



        function markDraftDirty(preserveExportState = false) {

            if (!preserveExportState) {

                clearExportResultState();

            }

            if (!currentDraftId) {

                setDraftStatus('Visuel non sauvegardÃ© en base.');

            } else {

                setDraftStatus('Modifications non sauvegardÃ©es. Enregistrez le visuel.');

            }

            updateGenerateButtonState();

        }



        function renderZoneOverlays() {

            overlayContainer.innerHTML = '';

            zones.forEach(zone => {

                const rect = document.createElement('div');

                rect.className = 'rectangle-overlay';

                rect.style.left = zone.x + 'px';

                rect.style.top = zone.y + 'px';

                rect.style.width = zone.width + 'px';

                rect.style.height = zone.height + 'px';

                rect.dataset.zoneId = zone.id;



                rect.addEventListener('mousedown', (event) => {

                    event.stopPropagation();

                    if (event.button !== 0) {

                        return;

                    }

                    event.preventDefault();

                    setActiveZone(zone.id);

                    startDragZone(zone.id, event.clientX, event.clientY);

                });



                rect.addEventListener('click', (event) => {

                    event.stopPropagation();

                    setActiveZone(zone.id);

                });



                const handle = document.createElement('div');

                handle.className = 'zone-resize-handle';

                handle.dataset.zoneId = zone.id;

                handle.addEventListener('mousedown', (event) => {

                    if (event.button !== 0) {

                        return;

                    }

                    event.preventDefault();

                    event.stopPropagation();

                    startResizeZone(zone.id, event.clientX, event.clientY);

                });



                rect.appendChild(handle);

                zone.element = rect;

                overlayContainer.appendChild(rect);

            });



            if (activeZoneId) {

                setActiveZone(activeZoneId);

            }

        }



        async function saveDraftToDatabase() {

            if (!wpAjaxUrl || !wpNonce) {

                setDraftStatus('Configuration AJAX manquante. Impossible d\'enregistrer.', true);

                return;

            }



            const draftName = (draftNameInput.value || '').trim();

            if (!draftName) {

                setDraftStatus('Veuillez renseigner un nom de visuel.', true);

                draftNameInput.focus();

                return;

            }



            const payload = serializeDraftPayload();

            const formData = new FormData();

            formData.append('action', 'mayami_save_visual_links_draft');

            formData.append('nonce', wpNonce);

            formData.append('name', draftName);

            formData.append('draft_id', currentDraftId || '');

            formData.append('payload', JSON.stringify(payload));



            saveDraftBtn.disabled = true;

            setDraftStatus('Enregistrement en base...');



            try {

                const response = await fetch(wpAjaxUrl, {

                    method: 'POST',

                    credentials: 'same-origin',

                    body: formData

                });

                const result = await response.json();



                if (!result || !result.success) {

                    const message = result && result.data && result.data.message ? result.data.message : 'Erreur de sauvegarde du visuel.';

                    setDraftStatus(message, true);

                    return;

                }



                currentDraftId = result.data.id;

                lastSavedSignature = getCurrentSignature();

                lastSavedPreviewGateSignature = getCurrentPreviewGateSignature();

                setDraftStatus('Visuel enregistrÃ© en base.');

                setPdfInlineAlertVisible(false);

                updateGenerateButtonState();

            } catch (error) {

                setDraftStatus('Impossible d\'enregistrer le visuel (erreur rÃ©seau).', true);

            } finally {

                saveDraftBtn.disabled = false;

            }

        }



        async function loadDraftFromDatabase() {

            if (!currentDraftId || !wpAjaxUrl || !wpNonce) {

                updateGenerateButtonState();

                return;

            }



            setDraftStatus('Chargement du visuel...');



            try {

                const url = new URL(wpAjaxUrl, window.location.href);

                url.searchParams.set('action', 'mayami_get_visual_links_draft');

                url.searchParams.set('nonce', wpNonce);

                url.searchParams.set('draft_id', currentDraftId);



                const response = await fetch(url.toString(), {

                    method: 'GET',

                    credentials: 'same-origin'

                });

                const result = await response.json();



                if (!result || !result.success || !result.data) {

                    setDraftStatus('Visuel introuvable ou inaccessible.', true);

                    return;

                }



                const payload = result.data.payload || {};

                imageUrl = payload.imageUrl || '';

                const savedPdfHeaderEnabled = payload.pdfHeaderEnabled === true;

                pdfHeaderActive.checked = !!savedPdfHeaderEnabled;

                pdfHeaderInactive.checked = !savedPdfHeaderEnabled;

                syncPdfFieldsVisibility();

                setPdfInlineAlertVisible(false);

                pdfCtaTextInput.value = normalizePdfCtaText(payload.pdfCtaText || '');

                pdfUrlInput.value = normalizePdfUrl(payload.pdfUrl || '');

                const rawZones = Array.isArray(payload.zones) ? payload.zones : [];



                if (imageUrl) {

                    imageCanvas.src = imageUrl;

                    uploadZone.style.display = 'none';

                    canvasWrapper.style.display = 'inline-block';

                    await waitForImageReady();

                } else {

                    uploadZone.style.display = '';

                    canvasWrapper.style.display = 'none';

                }



                const currentDims = getCanvasDimensions();

                const savedWidth = Number(payload.canvasWidth) || 0;

                const savedHeight = Number(payload.canvasHeight) || 0;



                // SÃ©curitÃ© anti-dÃ©calage: certains anciens drafts ont des canvasWidth/Height

                // incohÃ©rents avec les coordonnÃ©es stockÃ©es. Dans ce cas, on Ã©vite de

                // re-multiplier les coordonnÃ©es si elles sont dÃ©jÃ  dans l'espace affichÃ©.

                const rawMaxX = rawZones.reduce((max, zone) => {

                    const x = Number(zone.x) || 0;

                    const w = Number(zone.width) || 0;

                    return Math.max(max, x + w);

                }, 0);

                const rawMaxY = rawZones.reduce((max, zone) => {

                    const y = Number(zone.y) || 0;

                    const h = Number(zone.height) || 0;

                    return Math.max(max, y + h);

                }, 0);



                const hasValidSavedDims = savedWidth > 0 && savedHeight > 0 && currentDims.width > 0 && currentDims.height > 0;

                const savedMuchLargerThanCurrent = hasValidSavedDims

                    && (savedWidth > currentDims.width * 1.8 || savedHeight > currentDims.height * 1.8);

                const zonesAlreadyFitCurrentCanvas = currentDims.width > 0 && currentDims.height > 0

                    && rawMaxX <= (currentDims.width * 1.05)

                    && rawMaxY <= (currentDims.height * 1.05);



                let ratioX = 1;

                let ratioY = 1;

                if (hasValidSavedDims && !(savedMuchLargerThanCurrent && zonesAlreadyFitCurrentCanvas)) {

                    ratioX = currentDims.width / savedWidth;

                    ratioY = currentDims.height / savedHeight;

                }



                zones = rawZones.map(zone => {

                    const x = Math.max(0, Math.round((Number(zone.x) || 0) * ratioX));

                    const y = Math.max(0, Math.round((Number(zone.y) || 0) * ratioY));

                    const width = Math.max(0, Math.round((Number(zone.width) || 0) * ratioX));

                    const height = Math.max(0, Math.round((Number(zone.height) || 0) * ratioY));



                    return {

                        id: zone.id || Date.now() + Math.random(),

                        x,

                        y,

                        width,

                        height,

                        hrefType: zone.hrefType === 'anchor' ? 'anchor' : 'url',

                        hrefValue: zone.hrefValue || ''

                    };

                }).filter(zone => zone.width > 0 && zone.height > 0);



                draftNameInput.value = result.data.name || '';

                lastDraftUpdatedAt = String(result.data.updatedAt || '').trim();

                renderZoneOverlays();

                updateZonesList();

                setAddZoneMode(false);

                lastSavedSignature = getCurrentSignature();

                lastSavedPreviewGateSignature = getCurrentPreviewGateSignature();

                setDraftStatus('Visuel chargÃ© depuis la base.');

                updateGenerateButtonState();



                // Restore quick export actions if a previous export URL was persisted.

                const restoredTemplateEmailExportUrl = String(result.data.template_email_export_url || '').trim();

                const restoredTemplateEmailExportFilename = String(result.data.template_email_export_filename || '').trim();

                const restoredTemplateEmailExportUpdatedAt = String(result.data.template_email_export_updated_at || '').trim();

                const restoredTemplateHtmlExportUrl = String(result.data.template_html_export_url || '').trim();

                const restoredTemplateHtmlExportFilename = String(result.data.template_html_export_filename || '').trim();

                const restoredTemplateHtmlExportUpdatedAt = String(result.data.template_html_export_updated_at || '').trim();

                if (restoredTemplateEmailExportUrl) {

                    lastTemplateEmailPublicUrl = restoredTemplateEmailExportUrl;

                    lastTemplateEmailFilename = restoredTemplateEmailExportFilename || 'template-email.html';

                    lastTemplateEmailExportUpdatedAt = restoredTemplateEmailExportUpdatedAt;

                }

                if (restoredTemplateHtmlExportUrl) {

                    lastTemplateHtmlPublicUrl = restoredTemplateHtmlExportUrl;

                    lastTemplateHtmlFilename = restoredTemplateHtmlExportFilename || 'visual-links.html';

                    lastTemplateHtmlExportUpdatedAt = restoredTemplateHtmlExportUpdatedAt;

                }

                const restoredExportUrl = restoredTemplateEmailExportUrl || restoredTemplateHtmlExportUrl || String(result.data.export_url || '').trim();

                const restoredExportFilename = restoredTemplateEmailExportFilename || restoredTemplateHtmlExportFilename || String(result.data.export_filename || '').trim();

                if (restoredExportUrl) {

                    applyExportResult(restoredExportUrl, restoredExportFilename || 'visual-links.html', '');



                    // Export considÃ©rÃ© Ã  jour uniquement si Template-HTML et Template-Email

                    // existent tous les deux et sont plus rÃ©cents que le visuel sauvegardÃ©.

                    const hasBothExports = !!(restoredTemplateEmailExportUrl && restoredTemplateHtmlExportUrl);

                    const htmlFresh = !!(restoredTemplateHtmlExportUrl && restoredTemplateHtmlExportUpdatedAt && lastDraftUpdatedAt && restoredTemplateHtmlExportUpdatedAt >= lastDraftUpdatedAt);

                    const emailFresh = !!(restoredTemplateEmailExportUrl && restoredTemplateEmailExportUpdatedAt && lastDraftUpdatedAt && restoredTemplateEmailExportUpdatedAt >= lastDraftUpdatedAt);

                    const exportsFresh = hasBothExports && htmlFresh && emailFresh;



                    if (!exportsFresh) {

                        setExportDownloadButtonsEnabled(false);

                        setDraftStatus('Visuel chargÃ© depuis la base.');

                    }

                } else {

                    setExportDownloadButtonsEnabled(false);

                }

            } catch (error) {

                setDraftStatus('Impossible de charger le visuel.', true);

            }

        }



        // Upload handlers

        uploadZone.addEventListener('click', (event) => {

            if (event.target === uploadFromMediaBtn) {

                return;

            }

            imageUpload.click();

        });



        uploadFromMediaBtn.addEventListener('click', (event) => {

            event.preventDefault();

            event.stopPropagation();

            openMediaLibrary({ preserveZones: false });

        });



        replaceImageFileBtn.addEventListener('click', () => {

            imageUpload.click();

        });



        replaceImageMediaBtn.addEventListener('click', () => {

            openMediaLibrary({ preserveZones: zones.length > 0 });

        });

        

        uploadZone.addEventListener('dragover', (e) => {

            e.preventDefault();

            uploadZone.style.borderColor = '#667eea';

        });

        

        uploadZone.addEventListener('dragleave', () => {

            uploadZone.style.borderColor = '#ccc';

        });

        

        uploadZone.addEventListener('drop', (e) => {

            e.preventDefault();

            uploadZone.style.borderColor = '#ccc';

            const file = e.dataTransfer.files[0];

            if (file && file.type.startsWith('image/')) {

                loadImage(file, { preserveZones: zones.length > 0 });

            }

        });



        imageUpload.addEventListener('change', (e) => {

            const file = e.target.files[0];

            if (file) {

                loadImage(file, { preserveZones: zones.length > 0 });

            }

            imageUpload.value = '';

        });



        // Drawing zones

        function getCanvasPoint(clientX, clientY) {

            const rect = imageCanvas.getBoundingClientRect();

            return {

                x: Math.max(0, Math.min(rect.width, clientX - rect.left)),

                y: Math.max(0, Math.min(rect.height, clientY - rect.top)),

                rect

            };

        }



        function stopDrawing(clientX, clientY) {

            if (!isDrawing) {

                return;

            }



            isDrawing = false;



            if (!currentRect) {

                return;

            }



            const point = getCanvasPoint(clientX, clientY);

            const currentX = point.x;

            const currentY = point.y;

            const width = Math.abs(currentX - startX);

            const height = Math.abs(currentY - startY);



            if (width > 10 && height > 10) {

                const left = Math.min(startX, currentX);

                const top = Math.min(startY, currentY);



                const zone = {

                    id: Date.now(),

                    x: Math.round(left),

                    y: Math.round(top),

                    width: Math.round(width),

                    height: Math.round(height),

                    hrefType: 'url',

                    hrefValue: '',

                    element: currentRect

                };



                zones.push(zone);

                currentRect.dataset.zoneId = zone.id;



                currentRect.addEventListener('click', () => {

                    setActiveZone(zone.id);

                });



                updateZonesList();

                markDraftDirty();

                setAddZoneMode(false);

            } else {

                currentRect.remove();

            }



            currentRect = null;

        }



        function startResizeZone(zoneId, clientX, clientY) {

            const zone = getZoneById(zoneId);

            if (!zone) {

                return;

            }



            const point = getCanvasPoint(clientX, clientY);

            isResizing = true;

            resizeState = {

                zoneId: zone.id,

                startX: point.x,

                startY: point.y,

                originX: Number(zone.x) || 0,

                originY: Number(zone.y) || 0,

                originWidth: Number(zone.width) || 0,

                originHeight: Number(zone.height) || 0,

            };



            setActiveZone(zone.id);

            document.body.style.userSelect = 'none';

        }



        function startDragZone(zoneId, clientX, clientY) {

            if (isResizing) {

                return;

            }



            if (Date.now() < suppressDragUntil) {

                return;

            }



            const zone = getZoneById(zoneId);

            if (!zone) {

                return;

            }



            const point = getCanvasPoint(clientX, clientY);

            isDragging = true;

            dragState = {

                zoneId: zone.id,

                startPointerX: point.x,

                startPointerY: point.y,

                pointerOffsetX: point.x - (Number(zone.x) || 0),

                pointerOffsetY: point.y - (Number(zone.y) || 0),

                hasMoved: false,

            };



            setActiveZone(zone.id);

            document.body.style.userSelect = 'none';

        }



        function stopResize() {

            if (!isResizing) {

                return;

            }



            isResizing = false;

            resizeState = null;

            document.body.style.userSelect = '';

            updateZonesList();

            markDraftDirty();

        }



        function stopDrag() {

            if (!isDragging) {

                return;

            }



            const hasMoved = !!(dragState && dragState.hasMoved);



            isDragging = false;

            dragState = null;

            document.body.style.userSelect = '';



            if (hasMoved) {

                updateZonesList();

                markDraftDirty();

            }

        }



        zonesList.addEventListener('mousedown', (event) => {

            const interactive = event.target.closest('.zone-item, .zone-select, .zone-input, .zone-delete');

            if (interactive) {

                suppressDragUntil = Date.now() + 250;

            }

        });



        addZoneBtn.addEventListener('click', () => {

            setAddZoneMode(!isAddZoneMode);

        });



        imageCanvas.addEventListener('mousedown', (e) => {

            if (e.button !== 0) {

                return;

            }



            if (!isAddZoneMode) {

                return;

            }



            if (isResizing) {

                return;

            }



            if (isDragging) {

                return;

            }



            const point = getCanvasPoint(e.clientX, e.clientY);

            startX = point.x;

            startY = point.y;

            isDrawing = true;



            currentRect = document.createElement('div');

            currentRect.className = 'rectangle-overlay';

            currentRect.style.left = startX + 'px';

            currentRect.style.top = startY + 'px';

            currentRect.style.width = '0px';

            currentRect.style.height = '0px';

            overlayContainer.appendChild(currentRect);

        });



        window.addEventListener('mousemove', (e) => {

            if (isDragging && dragState) {

                const zone = getZoneById(dragState.zoneId);

                if (!zone) {

                    return;

                }



                const point = getCanvasPoint(e.clientX, e.clientY);

                const pointerDx = Math.abs(point.x - dragState.startPointerX);

                const pointerDy = Math.abs(point.y - dragState.startPointerY);

                if (!dragState.hasMoved && pointerDx < DRAG_START_THRESHOLD && pointerDy < DRAG_START_THRESHOLD) {

                    return;

                }

                dragState.hasMoved = true;



                const maxX = Math.max(0, imageCanvas.clientWidth - (Number(zone.width) || 0));

                const maxY = Math.max(0, imageCanvas.clientHeight - (Number(zone.height) || 0));



                zone.x = Math.max(0, Math.min(maxX, Math.round(point.x - dragState.pointerOffsetX)));

                zone.y = Math.max(0, Math.min(maxY, Math.round(point.y - dragState.pointerOffsetY)));



                if (zone.element) {

                    zone.element.style.left = zone.x + 'px';

                    zone.element.style.top = zone.y + 'px';

                }

                return;

            }



            if (isResizing && resizeState) {

                const zone = getZoneById(resizeState.zoneId);

                if (!zone) {

                    return;

                }



                const point = getCanvasPoint(e.clientX, e.clientY);

                const dx = point.x - resizeState.startX;

                const dy = point.y - resizeState.startY;

                const maxWidth = Math.max(MIN_ZONE_SIZE, imageCanvas.clientWidth - resizeState.originX);

                const maxHeight = Math.max(MIN_ZONE_SIZE, imageCanvas.clientHeight - resizeState.originY);



                zone.width = Math.min(maxWidth, Math.max(MIN_ZONE_SIZE, Math.round(resizeState.originWidth + dx)));

                zone.height = Math.min(maxHeight, Math.max(MIN_ZONE_SIZE, Math.round(resizeState.originHeight + dy)));



                if (zone.element) {

                    zone.element.style.width = zone.width + 'px';

                    zone.element.style.height = zone.height + 'px';

                }

                return;

            }



            if (!isDrawing || !currentRect) {

                return;

            }



            const point = getCanvasPoint(e.clientX, e.clientY);

            const currentX = point.x;

            const currentY = point.y;



            const width = Math.abs(currentX - startX);

            const height = Math.abs(currentY - startY);

            const left = Math.min(startX, currentX);

            const top = Math.min(startY, currentY);



            currentRect.style.left = left + 'px';

            currentRect.style.top = top + 'px';

            currentRect.style.width = width + 'px';

            currentRect.style.height = height + 'px';

        });



        window.addEventListener('mouseup', (e) => {

            if (isDragging) {

                stopDrag();

                return;

            }



            if (isResizing) {

                stopResize();

                return;

            }

            stopDrawing(e.clientX, e.clientY);

        });



        window.addEventListener('blur', () => {

            if (currentRect) {

                currentRect.remove();

                currentRect = null;

            }

            isDrawing = false;

            stopDrag();

            stopResize();

        });



        function setActiveZone(zoneId) {

            activeZoneId = zoneId;

            document.querySelectorAll('.rectangle-overlay').forEach(el => {

                el.classList.remove('active');

            });

            document.querySelectorAll('.zone-item').forEach(el => {

                el.classList.remove('active');

            });

            

            const zone = getZoneById(zoneId);

            if (zone) {

                if (zone.element) {

                    zone.element.classList.add('active');

                }

                const zoneItem = Array.from(document.querySelectorAll('.zone-item')).find((el) => {

                    return String(el.dataset.zoneId) === String(zone.id);

                });

                if (zoneItem) {

                    zoneItem.classList.add('active');

                }

            }

        }



        function updateZonesList() {

            if (zones.length === 0) {

                zonesList.innerHTML = '<p style="color: #999; text-align: center; padding: 20px; font-size: 13px;">Aucune zone crÃ©Ã©e.<br>Dessinez sur l\'image pour commencer.</p>';

            } else {

                zonesList.innerHTML = zones.map((zone, index) => `

                    <div class="zone-item" data-zone-id="${zone.id}">

                        <div class="zone-header">

                            <span class="zone-title">Zone ${index + 1}</span>

                            <button class="zone-delete" onclick="deleteZone(${zone.id})">âœ•</button>

                        </div>

                        <div class="zone-coords">

                            ðŸ“ x:${zone.x}, y:${zone.y}, w:${zone.width}, h:${zone.height}

                        </div>

                        <div class="zone-controls">

                            <select 

                                class="zone-select"

                                onchange="updateZoneType(${zone.id}, this.value)"

                                onclick="setActiveZone(${zone.id})"

                            >

                                <option value="url" ${zone.hrefType === 'url' ? 'selected' : ''}>Lien</option>

                                <option value="anchor" ${zone.hrefType === 'anchor' ? 'selected' : ''}>Ancre</option>

                            </select>

                            <input 

                                type="text"

                                class="zone-input" 

                                placeholder="${zone.hrefType === 'anchor' ? '#section' : 'https://exemple.com'}" 

                                value="${escapeHtml(zone.hrefValue)}"

                                onchange="updateZoneHref(${zone.id}, this.value)"

                                onclick="setActiveZone(${zone.id})"

                            >

                        </div>

                    </div>

                `).join('');



                Array.from(zonesList.querySelectorAll('.zone-item')).forEach((item) => {

                    item.addEventListener('click', (event) => {

                        const interactive = event.target.closest('.zone-delete, .zone-select, .zone-input');

                        if (interactive) {

                            return;

                        }

                        setActiveZone(item.dataset.zoneId || '');

                    });

                });

            }

            

            updateStats();



            if (activeZoneId !== null) {

                setActiveZone(activeZoneId);

            }

        }



        function updateStats() {

            zoneCount.textContent = zones.length;

            linkedCount.textContent = zones.filter(z => getZoneHref(z)).length;

        }



        function updateZoneType(zoneId, hrefType) {

            const zone = getZoneById(zoneId);

            if (zone) {

                zone.hrefType = hrefType;

                updateZonesList();

                setActiveZone(zoneId);

                markDraftDirty();

            }

        }



        function updateZoneHref(zoneId, hrefValue) {

            const zone = getZoneById(zoneId);

            if (zone) {

                zone.hrefValue = hrefValue;

                updateStats();

                markDraftDirty();

            }

        }



        function getZoneHref(zone) {

            const value = (zone.hrefValue || '').trim();

            if (!value) {

                return '';

            }



            if (zone.hrefType === 'anchor') {

                return value.startsWith('#') ? value : `#${value}`;

            }



            return value;

        }



        function escapeAttr(value) {

            return String(value)

                .replace(/&/g, '&amp;')

                .replace(/"/g, '&quot;')

                .replace(/</g, '&lt;')

                .replace(/>/g, '&gt;');

        }



        function getAnchorIdFromHref(href) {

            const cleanHref = String(href || '').trim();

            if (!cleanHref.startsWith('#')) {

                return '';

            }

            return cleanHref.substring(1).replace(/[^a-zA-Z0-9_-]/g, '');

        }



        async function exportHtmlFromBuilder(htmlDocument, draftName, draftId, exportSubdir, exportBucket) {

            if (!wpAjaxUrl || !wpNonce) {

                return { success: false, message: 'Configuration AJAX manquante.' };

            }



            const formData = new FormData();

            formData.append('action', 'mayami_export_visual_links_html');

            formData.append('nonce', wpNonce);

            formData.append('draft_id', draftId || '');

            formData.append('draft_name', draftName || 'visual-links');

            formData.append('html', htmlDocument || '');

            formData.append('export_subdir', exportSubdir || '');

            formData.append('export_bucket', exportBucket || '');



            try {

                const response = await fetch(wpAjaxUrl, {

                    method: 'POST',

                    credentials: 'same-origin',

                    body: formData

                });



                const responseText = await response.text();

                let result = null;

                try {

                    result = JSON.parse(responseText);

                } catch (parseError) {

                    return {

                        success: false,

                        message: 'RÃ©ponse export invalide (' + response.status + ').'

                    };

                }



                if (!response.ok) {

                    return {

                        success: false,

                        message: (result && result.data && result.data.message) ? result.data.message : ('Erreur HTTP ' + response.status + ' pendant l\'export.')

                    };

                }



                if (!result || !result.success) {

                    return {

                        success: false,

                        message: (result && result.data && result.data.message) ? result.data.message : 'Export impossible.'

                    };

                }



                return {

                    success: true,

                    filename: (result.data && result.data.filename) ? result.data.filename : ((draftName || 'visual-links') + '.html'),

                    path: (result.data && result.data.path) ? result.data.path : '',

                    url: (result.data && result.data.url) ? result.data.url : ''

                };

            } catch (error) {

                return { success: false, message: 'Erreur rÃ©seau pendant l\'export.' };

            }

        }



        window.mayamiExportVisualLinksHtmlFromOpener = async function(payload) {

            const data = payload || {};

            return exportHtmlFromBuilder(

                data.htmlDocument || '',

                data.draftName || '',

                data.draftId || '',

                data.exportSubdir || '',

                data.exportBucket || ''

            );

        };



        // Legacy alias kept for compatibility with previously generated preview windows.

        window.mayamiExportEpkHtmlFromOpener = window.mayamiExportVisualLinksHtmlFromOpener;



        function buildMapPreviewData() {

            if (!imageUrl) {

                throw new Error('Aucune image disponible pour la prÃ©visualisation.');

            }



            if (zones.length === 0) {

                throw new Error('Veuillez crÃ©er au moins une zone cliquable.');

            }



            const imgWidth = imageCanvas.naturalWidth;

            const imgHeight = imageCanvas.naturalHeight;

            const displayDims = getCanvasDimensions();

            const editorWidth = Math.max(1, Number(displayDims.width) || imageCanvas.clientWidth || imageCanvas.width || imgWidth);

            const editorHeight = Math.max(1, Number(displayDims.height) || imageCanvas.clientHeight || imageCanvas.height || imgHeight);

            const scaleX = imgWidth / editorWidth;

            const scaleY = imgHeight / editorHeight;

            const mapName = 'imageMap' + Date.now();



            const areasMarkup = zones.map(zone => {

                const x1 = Math.round(zone.x);

                const y1 = Math.round(zone.y);

                const x2 = Math.round(zone.x + zone.width);

                const y2 = Math.round(zone.y + zone.height);

                const href = getZoneHref(zone) || '#';

                const target = href.startsWith('#') ? '_self' : '_blank';

                const rel = href.startsWith('#') ? '' : ' rel="noopener noreferrer"';



                return `<area shape="rect" coords="${x1},${y1},${x2},${y2}" href="${escapeAttr(href)}" target="${target}"${rel} alt="">`;

            }).join('\n        ');



            const anchorIds = zones

                .map(zone => getAnchorIdFromHref(getZoneHref(zone)))

                .filter(Boolean)

                .filter((id, index, arr) => arr.indexOf(id) === index);



            const anchorsMarkup = anchorIds.map((id, index) => {

                return `<section id="${escapeAttr(id)}" class="anchor-section"><h2>Section #${escapeHtml(id)}</h2><p>Ancre de dÃ©monstration ${index + 1}. Le clic sur la zone ancre vous amÃ¨ne ici.</p></section>`;

            }).join('');



            const scriptCloseTag = '</scr' + 'ipt>';

            const htmlDocument = `<!DOCTYPE html>

<html lang="fr">

<head>

  <meta charset="UTF-8">

  <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>VISUAL LINKS BUILDER (VLB)</title>

  <link rel="stylesheet" href="styles/builder.css">

</head>

<body>

  <main class="wrapper">

    <div class="img-wrap">

      <img id="mapImg" src="${escapeAttr(imageUrl)}" usemap="#${escapeAttr(mapName)}" alt="Visual link layout">

    </div>

    <map name="${escapeAttr(mapName)}">

      ${areasMarkup}

    </map>

  </main>

  ${anchorsMarkup}

  <script>

    (function () {

      var img = document.getElementById('mapImg');

      var map = document.querySelector('map[name="${escapeAttr(mapName)}"]');

      if (!img || !map) return;

      var areas = Array.from(map.querySelectorAll('area'));

      var origCoords = areas.map(function (a) { return (a.getAttribute('coords') || '').split(',').map(Number); });

      var naturalW = ${editorWidth};

      function rescale() {

        var displayW = img.offsetWidth;

        if (!displayW) return;

        var ratio = displayW / naturalW;

        areas.forEach(function (area, i) {

          var c = origCoords[i];

          if (c && c.length === 4) {

            area.setAttribute('coords', [

              Math.round(c[0] * ratio),

              Math.round(c[1] * ratio),

              Math.round(c[2] * ratio),

              Math.round(c[3] * ratio)

            ].join(','));

          }

        });

      }

      if (img.complete) rescale(); else img.addEventListener('load', rescale);

      window.addEventListener('resize', rescale);

    })();

    ${scriptCloseTag}

</body>

</html>`;



            return {

                htmlDocument,

                imgWidth,

                imgHeight,

                editorWidth,

                editorHeight,

                mapName,

                areasMarkup,

                anchorsMarkup,

                scaleX,

                scaleY

            };

        }



        /**

         * Build clean export HTML template WITH PDF header (for template-html export).

         * This is the proper template users want when exporting "Template HTML".

         */

        function buildExportTemplateHtml(previewData) {

            const previewPdfText = normalizePdfCtaText(pdfCtaTextInput ? pdfCtaTextInput.value : '');

            const previewPdfUrl = normalizePdfUrl(pdfUrlInput ? pdfUrlInput.value : '');

            const previewPdfEnabled = isPdfHeaderEnabled();

            const previewPdfHasUrl = /^https?:\/\//i.test(previewPdfUrl);

            const previewPdfNotice = (previewPdfEnabled && previewPdfHasUrl)

                ? `<a href="${escapeAttr(previewPdfUrl)}" target="_blank" rel="noopener noreferrer" class="pdf-cta-preview__text">${escapeHtml(previewPdfText)}</a>`

                : `<span class="pdf-cta-preview__text">${escapeHtml(previewPdfText)}</span>`;

            const previewPdfClass = previewPdfEnabled ? 'active' : 'inactive';

            const scriptCloseTag = '</scr' + 'ipt>';



            return `<!DOCTYPE html>

<html lang="fr">

<head>

  <meta charset="UTF-8">

  <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>VISUAL LINKS BUILDER (VLB)</title>

  <style>

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif; background: #f5f5f5; }

    .wrapper { max-width: 1200px; margin: 0 auto; padding: 20px; }

    .pdf-cta-preview { display: flex; align-items: center; justify-content: center; gap: 8px; margin: 0 auto 16px; text-align: center; font-family: Arial, sans-serif; font-size: 14px; line-height: 1.35; font-weight: 700; letter-spacing: 0.2px; }

    .pdf-cta-preview__label { display: inline-block; border: 1px solid currentColor; border-radius: 4px; padding: 2px 6px; font-size: 11px; line-height: 1; font-weight: 800; text-transform: uppercase; }

    .pdf-cta-preview__text { color: inherit; text-decoration: none; }

    .pdf-cta-preview.active { color: #dc2626; }

    .pdf-cta-preview.active .pdf-cta-preview__text { text-decoration: underline; }

    .pdf-cta-preview.inactive { color: #94a3b8; }

    .img-card { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 2px 12px rgba(0,0,0,0.1); }

    .img-wrap { position: relative; }

    #mapImg { display: block; width: 100%; height: auto; border-radius: 8px; }

  </style>

</head>

<body>

  <main class="wrapper">

    <div class="pdf-cta-preview ${previewPdfClass}">

      <span class="pdf-cta-preview__label">PDF</span>

      ${previewPdfNotice}

    </div>

    <div class="img-card">

      <div class="img-wrap">

        <img id="mapImg" src="${escapeAttr(imageUrl)}" usemap="#${escapeAttr(previewData.mapName)}" alt="Visual link layout">

      </div>

      <map name="${escapeAttr(previewData.mapName)}">

        ${previewData.areasMarkup}

      </map>

    </div>

  </main>

  ${previewData.anchorsMarkup}

  <script>

    (function () {

      var img = document.getElementById('mapImg');

      var map = document.querySelector('map[name="${escapeAttr(previewData.mapName)}"]');

      if (!img || !map) return;

      var areas = Array.from(map.querySelectorAll('area'));

      var origCoords = areas.map(function (a) { return (a.getAttribute('coords') || '').split(',').map(Number); });

      var naturalW = ${previewData.editorWidth};

      function rescale() {

        var displayW = img.offsetWidth;

        if (!displayW) return;

        var ratio = displayW / naturalW;

        areas.forEach(function (area, i) {

          var c = origCoords[i];

          if (c && c.length === 4) {

            area.setAttribute('coords', [

              Math.round(c[0] * ratio),

              Math.round(c[1] * ratio),

              Math.round(c[2] * ratio),

              Math.round(c[3] * ratio)

            ].join(','));

          }

        });

      }

      if (img.complete) rescale(); else img.addEventListener('load', rescale);

      window.addEventListener('resize', rescale);

    })();

    ${scriptCloseTag}

</body>

</html>`;

        }



        function buildExternalPreviewHtml(previewData) {

            let builderUrl = 'admin.php?page=mayami_visual_links_builder';

            try {

                const ajaxEndpoint = new URL(wpAjaxUrl, window.location.href);

                const adminUrl = new URL('admin.php', ajaxEndpoint);

                adminUrl.searchParams.set('page', 'mayami_visual_links_builder');

                if (currentDraftId) {

                    adminUrl.searchParams.set('draft_id', currentDraftId);

                }

                builderUrl = adminUrl.toString();

            } catch (error) {

                if (currentDraftId) {

                    builderUrl += '&draft_id=' + encodeURIComponent(currentDraftId);

                }

            }

            const builderUrlAttr = escapeAttr(builderUrl);

            const previewPdfText = normalizePdfCtaText(pdfCtaTextInput ? pdfCtaTextInput.value : '');

            const previewPdfUrl = normalizePdfUrl(pdfUrlInput ? pdfUrlInput.value : '');

            const previewPdfEnabled = isPdfHeaderEnabled();

            const previewPdfHasUrl = /^https?:\/\//i.test(previewPdfUrl);

            const previewPdfNotice = (previewPdfEnabled && previewPdfHasUrl)

                ? `<a href="${escapeAttr(previewPdfUrl)}" target="_blank" rel="noopener noreferrer" class="pdf-cta-preview__text">${escapeHtml(previewPdfText)}</a>`

                : `<span class="pdf-cta-preview__text">${escapeHtml(previewPdfText)}</span>`;

            const previewPdfClass = previewPdfEnabled ? 'active' : 'inactive';



            return `<!DOCTYPE html>

<html lang="fr">

<head>

  <meta charset="UTF-8">

  <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Visual Link Preview</title>

  <link rel="stylesheet" href="styles/builder.css">

</head>

<body>

  <div class="topbar">

    <div class="topbar-title">PrÃ©visualisation visuelle</div>

    <div class="topbar-actions">

      <button class="preview-btn export-template" id="exportTemplateBtn">â¬‡ Exporter Template</button>

            <a class="preview-btn modify" id="modifyBtn" href="${builderUrlAttr}" style="text-decoration:none;display:inline-block;">Modifier</a>

    </div>

  </div>

  <div class="status" id="statusBox"></div>

  <div class="preview-wrap">

        <div class="pdf-cta-preview ${previewPdfClass}">

            <span class="pdf-cta-preview__label">PDF</span>

            ${previewPdfNotice}

        </div>

    <div class="preview-card">

            <img id="previewImage" src="${escapeAttr(imageUrl)}" width="${previewData.editorWidth}" height="${previewData.editorHeight}" usemap="#${escapeAttr(previewData.mapName)}" alt="Visual link preview" class="preview-image">

            <map id="previewMap" name="${escapeAttr(previewData.mapName)}">

        ${previewData.areasMarkup}

      </map>

    </div>

  </div>

  ${previewData.anchorsMarkup}

</body>

</html>`;

        }



        function openExternalPreview(previewData) {

            const previewWindow = window.open('', '_blank');

            if (!previewWindow) {

                alert('Impossible d\'ouvrir la prÃ©visualisation. Autorisez les popups pour ce site.');

                return;

            }

            previewWindow.document.open();

            previewWindow.document.write(buildExternalPreviewHtml(previewData));

            previewWindow.document.close();



            const statusBox = previewWindow.document.getElementById('statusBox');

            const exportTemplateBtn = previewWindow.document.getElementById('exportTemplateBtn');

            const previewImage = previewWindow.document.getElementById('previewImage');

            const previewMap = previewWindow.document.getElementById('previewMap');

            const mapAreas = Array.from(previewMap ? previewMap.querySelectorAll('area') : []);



            mapAreas.forEach((area) => {

                area.dataset.originalCoords = area.getAttribute('coords') || '';

            });



            function showPreviewStatus(message, isError) {

                if (!statusBox) {

                    return;

                }

                statusBox.textContent = message;

                statusBox.style.display = 'block';

                statusBox.classList.toggle('error', !!isError);

            }



            function resizePreviewMap() {

                if (!previewImage || !mapAreas.length || !previewData.editorWidth || !previewData.editorHeight) {

                    return;

                }



                const scaleX = previewImage.clientWidth / previewData.editorWidth;

                const scaleY = previewImage.clientHeight / previewData.editorHeight;



                mapAreas.forEach((area) => {

                    const original = (area.dataset.originalCoords || '').split(',').map((v) => Number(v.trim()));

                    if (original.length !== 4 || original.some((v) => Number.isNaN(v))) {

                        return;

                    }

                    const scaled = [

                        Math.round(original[0] * scaleX),

                        Math.round(original[1] * scaleY),

                        Math.round(original[2] * scaleX),

                        Math.round(original[3] * scaleY)

                    ];

                    area.setAttribute('coords', scaled.join(','));

                });

            }



            async function handlePreviewExport() {

                if (!exportTemplateBtn) {

                    return;

                }



                exportTemplateBtn.disabled = true;

                showPreviewStatus('Purge des anciens exports en cours...', false);



                const draftName = (draftNameInput.value || '').trim();

                const draftId   = currentDraftId || '';

                let exportTemplateHtml = buildExportTemplateHtml(previewData);



                try {

                    // 1. Purger les deux buckets avant de regÃ©nÃ©rer

                    const purgeHtml = await purgeExportBucket(draftName, 'template-html');

                    if (!purgeHtml.success) {

                        showPreviewStatus('Purge Template HTML impossible: ' + purgeHtml.message, true);

                        exportTemplateBtn.disabled = false;

                        return;

                    }



                    const purgeEmail = await purgeExportBucket(draftName, 'template-email');

                    if (!purgeEmail.success) {

                        showPreviewStatus('Purge Template E-Mail impossible: ' + purgeEmail.message, true);

                        exportTemplateBtn.disabled = false;

                        return;

                    }



                    // 2. Export Template-HTML (carte interactive responsive avec PDF header)

                    showPreviewStatus('Export Template HTML en cours...', false);

                    const htmlResult = await exportHtmlFromBuilder(

                        exportTemplateHtml,

                        draftName,

                        draftId,

                        '',

                        'template-html'

                    );



                    if (!htmlResult || !htmlResult.success) {

                        showPreviewStatus((htmlResult && htmlResult.message) ? htmlResult.message : 'Export Template HTML impossible.', true);

                        exportTemplateBtn.disabled = false;

                        return;

                    }



                    applyTemplateHtmlExportResult(htmlResult.url || '', htmlResult.filename || '', exportTemplateHtml);



                    // 3. Export Template-Email (slices + HTML + txt auto-sauvegardÃ© cÃ´tÃ© serveur)

                    showPreviewStatus('GÃ©nÃ©ration Template E-Mail en cours (dÃ©coupe image)...', false);

                    const emailResult = await generateEmailTemplateFromPreviewData(previewData);



                    if (!emailResult || !emailResult.success) {

                        showPreviewStatus((emailResult && emailResult.message) ? emailResult.message : 'Export Template E-Mail impossible.', true);

                        exportTemplateBtn.disabled = false;

                        return;

                    }



                    // 4. Fermer la preview et afficher la popup de rÃ©sultat dans la fenÃªtre d'Ã©dition

                    showPreviewStatus('Exports terminÃ©s avec succÃ¨s !', false);

                    setTimeout(() => {

                        previewWindow.close();

                        // Afficher la popup dans l'Ã©diteur avec les infos du template email (prioritÃ©)

                        const finalUrl      = lastTemplateEmailPublicUrl  || htmlResult.url      || '';

                        const finalFilename = lastTemplateEmailFilename   || htmlResult.filename || '';

                        const finalDoc      = lastTemplateEmailHtmlDocument || exportTemplateHtml;

                        showExportResultModal(finalUrl, finalFilename, finalDoc);

                    }, 400);



                } catch (error) {

                    showPreviewStatus((error && error.message) ? error.message : 'Erreur pendant l\'export.', true);

                    exportTemplateBtn.disabled = false;

                }

            }



            async function handlePreviewEmailTemplate() {

                // ConservÃ© pour compatibilitÃ© â€” redirige vers le flow unifiÃ©

                return handlePreviewExport();

            }



            if (previewImage) {

                if (previewImage.complete) {

                    resizePreviewMap();

                } else {

                    previewImage.addEventListener('load', resizePreviewMap, { once: true });

                }

            }



            previewWindow.addEventListener('resize', resizePreviewMap);

            if (exportTemplateBtn) {

                exportTemplateBtn.addEventListener('click', handlePreviewExport);

            }

            previewWindow.focus();

        }



        function escapeHtml(value) {

            return String(value)

                .replace(/&/g, '&amp;')

                .replace(/"/g, '&quot;')

                .replace(/</g, '&lt;')

                .replace(/>/g, '&gt;');

        }



        /**

         * Purge tous les fichiers d'un bucket d'export cÃ´tÃ© serveur avant rÃ©gÃ©nÃ©ration.

         * @param {string} draftName

         * @param {string} bucket - 'template-html' ou 'template-email'

         * @returns {Promise<{success:boolean,message:string,purged?:number}>}

         */

        async function purgeExportBucket(draftName, bucket) {

            if (!wpAjaxUrl || !wpNonce) {

                return { success: false, message: 'Configuration AJAX manquante.' };

            }

            const formData = new FormData();

            formData.append('action', 'mayami_purge_visual_export_bucket');

            formData.append('nonce', wpNonce);

            formData.append('draft_name', draftName);

            formData.append('export_bucket', bucket);

            try {

                const response = await fetch(wpAjaxUrl, { method: 'POST', credentials: 'same-origin', body: formData });

                const result = await response.json();

                if (!response.ok || !result || !result.success) {

                    return {

                        success: false,

                        message: (result && result.data && result.data.message)

                            ? result.data.message

                            : ('HTTP ' + response.status)

                    };

                }



                return {

                    success: true,

                    message: 'OK',

                    purged: Number(result.data && result.data.purged ? result.data.purged : 0)

                };

            } catch (e) {

                return { success: false, message: 'Erreur rÃ©seau pendant la purge.' };

            }

        }



        async function deleteZone(zoneId) {

            const confirmed = await askDeleteConfirmation('Supprimer cette zone ?');

            if (!confirmed) {

                return;

            }



            const zone = getZoneById(zoneId);

            if (zone) {

                zone.element.remove();

                zones = zones.filter(z => String(z.id) !== String(zoneId));

                if (String(activeZoneId) === String(zoneId)) {

                    activeZoneId = null;

                }

                updateZonesList();

                markDraftDirty();

            }

        }



        clearAllBtn.addEventListener('click', async () => {

            const confirmed = await askDeleteConfirmation('Supprimer toutes les zones ?');

            if (!confirmed) {

                return;

            }



            zones.forEach(zone => zone.element.remove());

            zones = [];

            updateZonesList();

            markDraftDirty();

        });



        function downloadTextFile(content, filename, mimeType) {

            const blob = new Blob([content], { type: mimeType || 'text/plain;charset=utf-8' });

            const downloadUrl = URL.createObjectURL(blob);

            const link = document.createElement('a');

            link.href = downloadUrl;

            link.download = filename;

            document.body.appendChild(link);

            link.click();

            document.body.removeChild(link);

            URL.revokeObjectURL(downloadUrl);

        }



        function createSliceBlob(slice) {

            return new Promise((resolve, reject) => {

                if (!slice || slice.width <= 0 || slice.height <= 0) {

                    reject(new Error('Slice invalide.'));

                    return;

                }



                const canvas = document.createElement('canvas');

                canvas.width = slice.width;

                canvas.height = slice.height;



                const ctx = canvas.getContext('2d');

                if (!ctx) {

                    reject(new Error('Contexte canvas indisponible.'));

                    return;

                }



                ctx.drawImage(

                    imageCanvas,

                    slice.x,

                    slice.y,

                    slice.width,

                    slice.height,

                    0,

                    0,

                    slice.width,

                    slice.height

                );



                canvas.toBlob((blob) => {

                    if (!blob) {

                        reject(new Error('Impossible de gÃ©nÃ©rer le blob image.'));

                        return;

                    }

                    resolve(blob);

                }, 'image/jpeg', 0.92);

            });

        }



        async function uploadEmailSlice(blob, filename, exportSubdir, exportBucket) {

            if (!wpAjaxUrl || !wpNonce) {

                return { success: false, message: 'Configuration AJAX manquante.' };

            }



            const visualName = (draftNameInput.value || '').trim() || 'visual-links';

            const formData = new FormData();

            formData.append('action', 'mayami_upload_visual_links_slice');

            formData.append('nonce', wpNonce);

            formData.append('draft_name', visualName);

            formData.append('filename', filename);

            formData.append('export_subdir', exportSubdir || '');

            formData.append('export_bucket', exportBucket || '');

            formData.append('slice_file', blob, filename);



            try {

                const response = await fetch(wpAjaxUrl, {

                    method: 'POST',

                    credentials: 'same-origin',

                    body: formData

                });



                const responseText = await response.text();

                let result = null;

                try {

                    result = JSON.parse(responseText);

                } catch (parseError) {

                    const rawText = (responseText || '').trim();

                    const suffix = rawText ? (' DÃ©tail serveur: ' + rawText.slice(0, 180)) : '';

                    return { success: false, message: 'RÃ©ponse upload slice invalide (' + response.status + ').' + suffix };

                }



                if (!response.ok || !result || !result.success) {

                    const fallbackDetail = (typeof result === 'string' || typeof result === 'number')

                        ? String(result)

                        : ((responseText || '').trim().slice(0, 180));

                    return {

                        success: false,

                        message: (result && result.data && result.data.message)

                            ? result.data.message

                            : ('Upload slice impossible (HTTP ' + response.status + ')' + (fallbackDetail ? (': ' + fallbackDetail) : '.'))

                    };

                }



                return {

                    success: true,

                    filename: result.data && result.data.filename ? result.data.filename : filename,

                    url: result.data && result.data.url ? result.data.url : ''

                };

            } catch (error) {

                return { success: false, message: 'Erreur rÃ©seau pendant upload slice.' };

            }

        }



        function buildSlicedEmailTemplateHtmlDocument(emailWidth, uploadedSlices) {

            const title = escapeHtml((draftNameInput.value || '').trim() || 'Template e-mail');

            const tableHtml = generateEmailTableHTML(uploadedSlices, emailWidth, emailWidth, (slice) => slice.url || '');

            const pdfUrl = normalizePdfUrl(pdfUrlInput.value);

            const pdfCtaText = normalizePdfCtaText(pdfCtaTextInput.value);

            const pdfHeaderEnabled = isPdfHeaderEnabled();

            const hasPdfUrl = /^https?:\/\//i.test(pdfUrl);

            const pdfBannerHtml = (pdfHeaderEnabled && hasPdfUrl)

                ? `

    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;width:100%;margin:0;padding:0;">

        <tr>

            <td align="center" style="padding:0 0 12px;margin:0;">

                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="${emailWidth}" style="border-collapse:collapse;width:${emailWidth}px;max-width:${emailWidth}px;margin:0 auto;">

                    <tr>

                        <td align="center" style="padding:0;margin:0;font-family:Arial,sans-serif;font-size:14px;line-height:1.4;">

                            <a href="${escapeAttr(pdfUrl)}" target="_blank" rel="noopener noreferrer" style="color:#dc2626;text-decoration:underline;font-weight:800;letter-spacing:0.2px;white-space:nowrap;">

                                <span style="display:inline-block;font-size:11px;line-height:1;padding:2px 5px;border:1px solid #dc2626;border-radius:3px;color:#dc2626;vertical-align:middle;">PDF</span>

                                <span style="display:inline-block;vertical-align:middle;margin-left:6px;">${escapeHtml(pdfCtaText)}</span>

                            </a>

                        </td>

                    </tr>

                </table>

            </td>

        </tr>

    </table>`

                : '';



            return `<!DOCTYPE html>

<html lang="fr">

<head>

  <meta charset="UTF-8">

  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>${title}</title>

</head>

<body style="margin:0;padding:0;background:#ffffff;">

${pdfBannerHtml}

  <div style="margin:0 auto;max-width:${emailWidth}px;">

${tableHtml}

  </div>

</body>

</html>`;

        }



        function buildScaledEmailSlicesFromNaturalSlices(naturalSlices, sourceWidth, sourceHeight, targetWidth, targetHeight) {

            const safeSourceWidth = Math.max(1, Number(sourceWidth) || 1);

            const safeSourceHeight = Math.max(1, Number(sourceHeight) || 1);

            const safeTargetWidth = Math.max(1, Number(targetWidth) || 1);

            const safeTargetHeight = Math.max(1, Number(targetHeight) || 1);

            const ratioX = safeTargetWidth / safeSourceWidth;

            const ratioY = safeTargetHeight / safeSourceHeight;



            const scaled = naturalSlices.map((slice) => ({

                ...slice,

                x: Math.round((Number(slice.x) || 0) * ratioX),

                y: Math.round((Number(slice.y) || 0) * ratioY),

                width: Math.max(1, Math.round((Number(slice.width) || 0) * ratioX)),

                height: Math.max(1, Math.round((Number(slice.height) || 0) * ratioY))

            }));



            const rows = {};

            scaled.forEach((slice) => {

                if (!rows[slice.row]) rows[slice.row] = [];

                rows[slice.row].push(slice);

            });



            Object.keys(rows).forEach((rowKey) => {

                const rowSlices = rows[rowKey].sort((a, b) => a.x - b.x);

                let consumed = 0;

                rowSlices.forEach((slice, index) => {

                    if (index === rowSlices.length - 1) {

                        slice.width = Math.max(1, safeTargetWidth - consumed);

                    }

                    consumed += slice.width;

                });

            });



            const byCols = {};

            scaled.forEach((slice) => {

                if (!byCols[slice.col]) byCols[slice.col] = [];

                byCols[slice.col].push(slice);

            });



            Object.keys(byCols).forEach((colKey) => {

                const colSlices = byCols[colKey].sort((a, b) => a.y - b.y);

                let consumed = 0;

                colSlices.forEach((slice, index) => {

                    if (index === colSlices.length - 1) {

                        slice.height = Math.max(1, safeTargetHeight - consumed);

                    }

                    consumed += slice.height;

                });

            });



            return scaled;

        }



        function createScaledBaseCanvas(targetWidth, targetHeight) {

            const canvas = document.createElement('canvas');

            canvas.width = Math.max(1, Number(targetWidth) || 1);

            canvas.height = Math.max(1, Number(targetHeight) || 1);

            const ctx = canvas.getContext('2d');

            if (!ctx) {

                return null;

            }

            ctx.imageSmoothingEnabled = true;

            ctx.imageSmoothingQuality = 'high';

            ctx.drawImage(imageCanvas, 0, 0, canvas.width, canvas.height);

            return canvas;

        }



        async function createSliceBlobFromSource(slice, sourceCanvas, outputType = 'image/png', outputQuality) {

            return new Promise((resolve, reject) => {

                if (!slice || !sourceCanvas || slice.width <= 0 || slice.height <= 0) {

                    reject(new Error('Slice invalide.'));

                    return;

                }



                const canvas = document.createElement('canvas');

                canvas.width = slice.width;

                canvas.height = slice.height;

                const ctx = canvas.getContext('2d');

                if (!ctx) {

                    reject(new Error('Contexte canvas indisponible.'));

                    return;

                }



                ctx.drawImage(

                    sourceCanvas,

                    slice.x,

                    slice.y,

                    slice.width,

                    slice.height,

                    0,

                    0,

                    slice.width,

                    slice.height

                );



                canvas.toBlob((blob) => {

                    if (!blob) {

                        reject(new Error('Impossible de gÃ©nÃ©rer le blob image.'));

                        return;

                    }

                    resolve(blob);

                }, outputType, outputQuality);

            });

        }



        async function generateEmailTemplateFromPreviewData(previewData) {

            const naturalSlices = calculateImageSlices(previewData.scaleX, previewData.scaleY);

            if (!naturalSlices.length) {

                throw new Error('Aucune slice Ã  gÃ©nÃ©rer.');

            }



            const visualName = (draftNameInput.value || '').trim() || 'visual-links';

            const emailWidth = Math.min(Math.max(1, Number(previewData.imgWidth) || 600), 600);

            const emailHeight = Math.max(1, Math.round((Number(previewData.imgHeight) || 1) * (emailWidth / Math.max(1, Number(previewData.imgWidth) || 1))));

            const scaledSlices = buildScaledEmailSlicesFromNaturalSlices(

                naturalSlices,

                previewData.imgWidth,

                previewData.imgHeight,

                emailWidth,

                emailHeight

            );



            const baseCanvas = createScaledBaseCanvas(emailWidth, emailHeight);

            if (!baseCanvas) {

                throw new Error('Impossible de prÃ©parer l\'image email.');

            }



            const slicesExportSubdir = visualName + '/Template-Email/img';

            const draftBaseName = (draftNameInput.value || '').trim() || 'template-email';

            const safeBaseName = draftBaseName

                .replace(/[\\\/:*?"<>|]+/g, '')

                .replace(/\s+/g, '-')

                .toLowerCase();

            const exportPrefix = (safeBaseName || 'template-email') + '-' + Date.now();

            const uploadedSlices = [];



            setDraftStatus('GÃ©nÃ©ration template e-mail cliquable en cours...');

            for (let i = 0; i < scaledSlices.length; i++) {

                const slice = scaledSlices[i];

                const filename = exportPrefix + '-slice-' + String(i + 1).padStart(3, '0') + '.png';

                const blob = await createSliceBlobFromSource(slice, baseCanvas, 'image/png');

                const upload = await uploadEmailSlice(blob, filename, slicesExportSubdir, 'template-email');

                if (!upload.success || !upload.url) {

                    throw new Error(upload.message || ('Ã‰chec upload slice ' + (i + 1) + '.'));

                }

                uploadedSlices.push({ ...slice, url: upload.url, filename: upload.filename || filename });

            }



            const templateHtml = buildSlicedEmailTemplateHtmlDocument(emailWidth, uploadedSlices);

            const localFilename = (safeBaseName || 'template-email') + '-email-template.html';

            const templateEmailSubdir = visualName + '/Template-Email';

            const exportResult = await exportHtmlFromBuilder(templateHtml, visualName, currentDraftId || '', templateEmailSubdir, 'template-email');

            if (exportResult && exportResult.success) {

                applyTemplateEmailExportResult(exportResult.url || '', exportResult.filename || localFilename, templateHtml);

                setDraftStatus('Template e-mail cliquable gÃ©nÃ©rÃ© (liens conservÃ©s) + HTML + TXT exportÃ©s dans Template-Email.');

                return {

                    success: true,

                    filename: exportResult.filename || localFilename,

                    url: exportResult.url || ''

                };

            }



            setDraftStatus('Export template e-mail impossible: ' + ((exportResult && exportResult.message) ? exportResult.message : 'erreur inconnue'), true);

            return {

                success: false,

                message: (exportResult && exportResult.message) ? exportResult.message : 'Export serveur impossible.'

            };

        }



        previewBtn.addEventListener('click', () => {

            if (previewBtn.disabled) {

                setDraftStatus('Enregistrez d\'abord le visuel en base avant la preview.');

                return;

            }



            try {

                const previewData = buildMapPreviewData();

                openExternalPreview(previewData);

            } catch (error) {

                alert(error && error.message ? error.message : 'Impossible d\'ouvrir la preview.');

            }

        });



        copyExportUrlBtn.addEventListener('click', () => {

            copyExportUrlToClipboard();

        });



        copyExportQuickBtn.addEventListener('click', () => {

            copyQuickExportUrlToClipboard();

        });



        downloadExportHtmlBtn.addEventListener('click', () => {

            downloadLastExportHtml();

        });



        downloadExportCodeBtn.addEventListener('click', () => {

            downloadLastExportHtmlCode();

        });



        downloadExportQuickBtn.addEventListener('click', () => {

            downloadLastExportHtml();

        });



        downloadExportCodeQuickBtn.addEventListener('click', () => {

            downloadLastExportHtmlCode();

        });



        closeExportResultModalBtn.addEventListener('click', () => {

            closeExportResultModal();

        });



        exportResultModal.addEventListener('click', (event) => {

            if (event.target === exportResultModal) {

                closeExportResultModal();

            }

        });



        function calculateImageSlices(scaleX, scaleY) {

            // CrÃ©er une grille de dÃ©coupe basÃ©e sur les zones

            const xPoints = new Set([0]);

            const yPoints = new Set([0]);

            

            // Ajouter les bords de chaque zone

            zones.forEach(zone => {

                const x1 = Math.round(zone.x * scaleX);

                const y1 = Math.round(zone.y * scaleY);

                const x2 = Math.round((zone.x + zone.width) * scaleX);

                const y2 = Math.round((zone.y + zone.height) * scaleY);

                

                xPoints.add(x1);

                xPoints.add(x2);

                yPoints.add(y1);

                yPoints.add(y2);

            });

            

            const imgWidth = imageCanvas.naturalWidth;

            const imgHeight = imageCanvas.naturalHeight;

            xPoints.add(imgWidth);

            yPoints.add(imgHeight);

            

            const xArray = Array.from(xPoints).sort((a, b) => a - b);

            const yArray = Array.from(yPoints).sort((a, b) => a - b);

            

            // CrÃ©er les morceaux

            const slices = [];

            let sliceIndex = 0;

            

            for (let i = 0; i < yArray.length - 1; i++) {

                for (let j = 0; j < xArray.length - 1; j++) {

                    const x = xArray[j];

                    const y = yArray[i];

                    const width = xArray[j + 1] - x;

                    const height = yArray[i + 1] - y;

                    

                    // Trouver si ce morceau est dans une zone cliquable

                    const matchingZone = zones.find(zone => {

                        const zx1 = Math.round(zone.x * scaleX);

                        const zy1 = Math.round(zone.y * scaleY);

                        const zx2 = Math.round((zone.x + zone.width) * scaleX);

                        const zy2 = Math.round((zone.y + zone.height) * scaleY);

                        const href = getZoneHref(zone);

                        

                        return x >= zx1 && y >= zy1 && 

                               x + width <= zx2 && 

                               y + height <= zy2 &&

                               href;

                    });

                    

                    slices.push({

                        index: ++sliceIndex,

                        x, y, width, height,

                        href: matchingZone ? getZoneHref(matchingZone) : null,

                        row: i,

                        col: j

                    });

                }

            }

            

            return slices;

        }

        

        function generateEmailTableHTML(slices, imgWidth, targetWidth, getSliceSrc) {

            // Grouper par rangÃ©e

            const rows = {};

            slices.forEach(slice => {

                if (!rows[slice.row]) rows[slice.row] = [];

                rows[slice.row].push(slice);

            });

            const safeImgWidth = Math.max(1, Number(imgWidth) || 1);

            const safeTargetWidth = Math.max(1, Number(targetWidth) || safeImgWidth);

            const displayScale = safeTargetWidth / safeImgWidth;

            

            let html = `<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="${safeTargetWidth}" style="border-collapse:collapse;border-spacing:0;mso-table-lspace:0pt;mso-table-rspace:0pt;width:${safeTargetWidth}px;max-width:${safeTargetWidth}px;margin:0 auto;table-layout:fixed;">

  <tbody>`;

            

            Object.keys(rows).sort((a, b) => Number(a) - Number(b)).forEach(rowKey => {

                const rowSlices = rows[rowKey].slice().sort((a, b) => Number(a.x) - Number(b.x));

                let consumedWidth = 0;

                html += `\n    <tr>`;

                rowSlices.forEach((slice, sliceIndex) => {

                    const resolvedSrc = typeof getSliceSrc === 'function' ? getSliceSrc(slice) : '';

                    let displayWidth = Math.max(1, Math.round((Number(slice.width) || 0) * displayScale));

                    const remainingWidth = safeTargetWidth - consumedWidth;

                    if (sliceIndex === rowSlices.length - 1) {

                        displayWidth = Math.max(1, remainingWidth);

                    }

                    consumedWidth += displayWidth;

                    const displayHeight = Math.max(1, Math.round((Number(slice.height) || 0) * displayScale));

                    const imgTag = `<img src="${escapeAttr(resolvedSrc || '')}" width="${displayWidth}" height="${displayHeight}" style="display:block;border:0;margin:0;padding:0;width:${displayWidth}px;height:${displayHeight}px;" alt="">`;

                    

                    if (slice.href) {

                        html += `\n      <td width="${displayWidth}" style="padding:0;margin:0;line-height:0;font-size:0;width:${displayWidth}px;"><a href="${slice.href}" style="display:block;border:0;text-decoration:none;line-height:0;font-size:0;">${imgTag}</a></td>`;

                    } else {

                        html += `\n      <td width="${displayWidth}" style="padding:0;margin:0;line-height:0;font-size:0;width:${displayWidth}px;">${imgTag}</td>`;

                    }

                });

                html += `\n    </tr>`;

            });

            

            html += `\n  </tbody>

</table>`;

            

            return html;

        }



        saveDraftBtn.addEventListener('click', () => {

            saveDraftToDatabase();

        });



        draftNameInput.addEventListener('input', () => {

            markDraftDirty();

        });



        pdfUrlInput.addEventListener('input', () => {

            markDraftDirty(true);

        });



        pdfCtaTextInput.addEventListener('input', () => {

            markDraftDirty(true);

        });



        pdfHeaderActive.addEventListener('change', () => {

            markDraftDirty(true);

            syncPdfFieldsVisibility();

            if (pdfHeaderActive.checked) {

                setPdfInlineAlertVisible(true, 'Mode PDF activÃ©. Cliquez sur SAUVEGARDER.');

            }

        });



        pdfHeaderInactive.addEventListener('change', () => {

            markDraftDirty(true);

            syncPdfFieldsVisibility();

            if (pdfHeaderInactive.checked) {

                setPdfInlineAlertVisible(true, 'Mode PDF dÃ©sactivÃ©. Cliquez sur SAUVEGARDER.');

            }

        });



        pdfUrlInput.addEventListener('blur', () => {

            const normalized = normalizePdfUrl(pdfUrlInput.value);

            if (normalized !== (pdfUrlInput.value || '').trim()) {

                pdfUrlInput.value = normalized;

                markDraftDirty(true);

            }

        });



        syncPdfFieldsVisibility();

        updateGenerateButtonState();

        loadDraftFromDatabase();

    