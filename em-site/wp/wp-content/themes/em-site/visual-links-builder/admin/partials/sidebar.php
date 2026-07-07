<div class="sidebar">
                <div class="draft-box">
                    <h3>💾 Visuel</h3>
                    <input type="text" id="draftNameInput" class="draft-name-input" placeholder="Nom du visuel (obligatoire)">
                </div>

                <div class="draft-box">
                    <div class="block-head">
                        <h3>📄 Mettre un lien PDF</h3>
                        <div class="radio-inline" aria-label="Etat du header PDF">
                            <label><input type="radio" name="pdfHeaderMode" id="pdfHeaderActive" value="active"> Actif</label>
                            <label><input type="radio" name="pdfHeaderMode" id="pdfHeaderInactive" value="inactive" checked> Inactif</label>
                        </div>
                    </div>
                    <div id="pdfFieldsWrap">
                        <label for="pdfCtaTextInput" class="field-label">CTA</label>
                        <input type="text" id="pdfCtaTextInput" class="draft-name-input" placeholder="Phrase d'appel à l'action (ex: Télécharger le PDF)">
                        <label for="pdfUrlInput" class="field-label">URL</label>
                        <input type="url" id="pdfUrlInput" class="draft-name-input" placeholder="Lien PDF (https://votresite.com/fichier.pdf)">
                    </div>
                    <div class="pdf-inline-alert" id="pdfInlineAlert" role="alert" aria-live="polite">
                        Le mode PDF a changé. La PREVIEW est déjà à jour. Cliquez sur SAUVEGARDER pour conserver ce réglage en base.
                    </div>
                </div>

                <div class="draft-box">
                    <button class="btn btn-secondary" id="saveDraftBtn">💾 SAUVEGARDER</button>
                </div>

                <div class="draft-box">
                    <h3>👁️ Preview</h3>
                    <p class="preview-helper">Relancer PREVIEW puis EXPORTER TEMPLATE.</p>
                    <div class="draft-status" id="draftStatus">Visuel non sauvegardé en base.</div>
                    <button class="btn btn-primary" id="previewBtn" disabled style="margin-top:8px;">
                        👁️ Preview
                    </button>
                </div>

                <div class="draft-box export-quick-actions" id="exportQuickActions">
                    <p class="export-quick-actions__title">Dernier export HTML</p>
                    <div class="export-quick-actions__row">
                        <input type="text" id="exportQuickUrl" class="export-quick-actions__url" readonly>
                        <button type="button" id="copyExportQuickBtn" class="export-quick-actions__btn copy">Copier l'URL</button>
                    </div>
                    <div class="export-quick-actions__btns-row">
                        <button type="button" id="downloadExportQuickBtn" class="export-quick-actions__btn download">Télécharger HTML</button>
                        <button type="button" id="downloadExportCodeQuickBtn" class="export-quick-actions__btn code">Télécharger Code</button>
                    </div>
                </div>

                <div class="image-source-box">
                    <h3>🖼️ Image de fond</h3>
                    <div class="image-source-actions">
                        <button type="button" class="btn btn-secondary btn-image-source" id="replaceImageFileBtn">Remplacer depuis l'ordinateur</button>
                        <button type="button" class="btn btn-secondary btn-image-source" id="replaceImageMediaBtn">Choisir dans la bibliothèque média</button>
                    </div>
                    <p class="image-source-help">Le remplacement conserve vos zones et les recale automatiquement.</p>
                </div>

                <div class="stats">
                    <div class="stat-box">
                        <div class="stat-number" id="zoneCount">0</div>
                        <div class="stat-label">Zones</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-number" id="linkedCount">0</div>
                        <div class="stat-label">Liées</div>
                    </div>
                </div>
                
                <div class="zones-heading">
                    <h2>📍 Zones cliquables</h2>
                    <button type="button" id="addZoneBtn" class="zone-add-btn" aria-pressed="false" title="Ajouter une zone">+</button>
                </div>
                
                <div id="zonesList">
                    <p style="color: #999; text-align: center; padding: 20px; font-size: 13px;">
                        Aucune zone créée.<br>Dessinez sur l'image pour commencer.
                    </p>
                </div>
                
                <div class="actions">
                    <button class="btn btn-secondary" id="clearAllBtn">
                        🗑️ Tout effacer
                    </button>
                </div>
            </div>
        </div>
    </div>

    