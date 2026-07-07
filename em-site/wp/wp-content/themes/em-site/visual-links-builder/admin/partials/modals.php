<div class="confirm-modal" id="confirmModal" aria-hidden="true">
        <div class="confirm-modal__card" role="dialog" aria-modal="true" aria-labelledby="confirmModalTitle">
            <h3 class="confirm-modal__title" id="confirmModalTitle">Confirmation</h3>
            <p class="confirm-modal__message" id="confirmModalMessage">Confirmez cette action.</p>
            <div class="confirm-modal__actions">
                <button type="button" class="confirm-modal__btn cancel" id="confirmModalCancel">Annuler</button>
                <button type="button" class="confirm-modal__btn confirm" id="confirmModalConfirm">Supprimer</button>
            </div>
        </div>
    </div>

    <div class="export-modal" id="exportResultModal" aria-hidden="true">
        <div class="export-modal__card" role="dialog" aria-modal="true" aria-labelledby="exportResultTitle">
            <h3 class="export-modal__title" id="exportResultTitle">Export HTML terminé</h3>
            <p class="export-modal__message">Copiez l'URL publique ci-dessous, téléchargez le fichier HTML, ou exportez le code HTML en .txt copiable.</p>
            <div class="export-modal__url-row">
                <input type="text" id="exportResultUrl" class="export-modal__url-input" readonly>
                <button type="button" id="copyExportUrlBtn" class="export-modal__btn copy">Copier l'URL</button>
            </div>
            <div class="export-modal__actions">
                <button type="button" id="downloadExportHtmlBtn" class="export-modal__btn download">Télécharger</button>
                <button type="button" id="downloadExportCodeBtn" class="export-modal__btn code">Code HTML (.txt)</button>
                <button type="button" id="closeExportResultModalBtn" class="export-modal__btn close">Fermer</button>
            </div>
        </div>
    </div>

    