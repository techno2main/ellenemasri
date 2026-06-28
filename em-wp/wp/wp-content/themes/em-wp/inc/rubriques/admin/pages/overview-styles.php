<?php
/**
 * Styles inline de la page Rubriques V4 (require depuis overview.php).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<style>
    .em-v4-overview h2 { margin-top: 26px; text-transform: uppercase; }

    .em-v4-collapse { background:#fff; border:1px solid #dcdcde; border-radius:6px; margin:0 0 10px; }
    /* Item en cours d'édition : cadre 2px à la couleur de fond choisie */
    .em-v4-item[open] { border-width:2px; border-color:var(--em-v4-item-bg, #2271b1); }
    .em-v4-collapse__summary { list-style:none; cursor:pointer; display:flex; align-items:center; gap:8px; padding:12px 14px; user-select:none; }
    .em-v4-collapse__summary::-webkit-details-marker { display:none; }
    .em-v4-collapse__chevron { width:0; height:0; border-left:6px solid #6b7280; border-top:5px solid transparent; border-bottom:5px solid transparent; transition:transform .15s ease; flex:0 0 auto; }
    .em-v4-collapse[open] > .em-v4-collapse__summary > .em-v4-collapse__chevron { transform:rotate(90deg); }
    .em-v4-collapse__summary code { background:#f0f0f1; padding:1px 6px; border-radius:3px; font-size:12px; }
    .em-v4-collapse__body { padding:0 14px 14px; border-top:1px solid #f0f0f1; }

    .em-v4-card { border-color:#c3c4c7; }
    .em-v4-card > .em-v4-card__head { font-size:15px; }
    .em-v4-item, .em-v4-step, .em-v4-create { background:#fbfbfc; }
    /* Ligne « Nouvelle Section » : 2 lignes empilées (créer / dupliquer) */
    .em-v4-create__options { display:flex; flex-direction:column; gap:12px; padding:14px; }
    .em-v4-create__row { display:flex; flex-wrap:wrap; align-items:center; gap:10px; margin:0; }
    .em-v4-create__row .button { display:inline-flex; align-items:center; gap:6px; height:34px; line-height:1; }
    .em-v4-create__row .button .dashicons { font-size:16px; width:16px; height:16px; line-height:1; }
    .em-v4-create__row input.regular-text, .em-v4-create__select { height:34px; box-sizing:border-box; margin:0; }
    .em-v4-create__name { min-width:240px; }
    .em-v4-create__label { flex:0 0 220px; display:inline-flex; align-items:center; gap:6px; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:.04em; color:#1d2327; cursor:pointer; }
    .em-v4-create__label .dashicons { font-size:16px; width:16px; height:16px; color:#2271b1; flex:0 0 auto; }
    .em-v4-create__radio { margin:0 2px 0 0; flex:0 0 auto; }
    .em-v4-create__select { max-width:100%; }
    .em-v4-create__row.is-off { opacity:.5; }
    .em-v4-create__row.is-off .em-v4-create__label { cursor:pointer; }
    /* Sélecteur d'icône (Nouvelle Rubrique) */
    .em-v4-iconpick { display:inline-flex; flex-wrap:wrap; gap:4px; align-items:center; }
    .em-v4-iconpick__opt { position:relative; display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; border:1px solid #dcdcde; border-radius:5px; cursor:pointer; color:#50575e; background:#fff; }
    .em-v4-iconpick__opt input { position:absolute; opacity:0; width:0; height:0; }
    .em-v4-iconpick__opt:hover { border-color:#2271b1; color:#2271b1; }
    .em-v4-iconpick__opt:has(input:checked) { border-color:#2271b1; background:#2271b1; color:#fff; box-shadow:0 0 0 1px #2271b1; }
    .em-v4-iconpick__opt .dashicons { font-size:18px; width:18px; height:18px; }

    .em-v4-badge { background:#111827; color:#fff; border-radius:3px; padding:1px 8px; font-size:11px; text-transform:uppercase; letter-spacing:.04em; }
    .em-v4-badge--default { background:#2271b1; }
    .em-v4-card__icon { color:#4e080e; }
    .em-v4-card__count { display:inline-flex; align-items:center; justify-content:center; min-width:18px; height:18px; padding:0 6px; margin-left:8px; background:#f0f0f1; color:#646970; border:1px solid #dcdcde; border-radius:999px; font-size:11px; font-weight:600; line-height:1; }
    /* Réordonnancement des rubriques (glisser-déposer) */
    .em-v4-card__drag { color:#a7aaae; cursor:grab; font-size:18px; width:18px; height:18px; flex:0 0 auto; }
    .em-v4-card__drag:hover { color:#646970; }
    .em-v4-card__drag:active { cursor:grabbing; }
    .em-v4-card.is-dragging { opacity:.6; outline:2px dashed #2271b1; outline-offset:2px; }
    /* Renommage d'une rubrique (crayon + champ inline) */
    .em-v4-card__edit { background:none; border:0; cursor:pointer; color:#2271b1; padding:0 2px; margin-left:6px; display:inline-flex; align-items:center; }
    .em-v4-card__edit:hover { color:#135e96; }
    .em-v4-card__edit .dashicons { font-size:16px; width:16px; height:16px; }
    .em-v4-card__nameinput { text-transform:uppercase; font-weight:700; min-width:200px; margin:0; }

    .em-v4-item__title { display:inline-flex; align-items:center; gap:6px; }
    .em-v4-item__prefix, .em-v4-item__name { text-transform:uppercase; font-weight:600; }
    .em-v4-item__edit { background:none; border:0; cursor:pointer; color:#2271b1; padding:0 2px; display:inline-flex; align-items:center; }
    .em-v4-item__edit:hover { color:#135e96; }
    .em-v4-item__edit .dashicons { font-size:18px; width:18px; height:18px; }
    .em-v4-item__delete { background:none !important; border:0 !important; box-shadow:none !important; outline:0; cursor:pointer; color:#b32d2e; padding:0 2px; margin-left:6px; display:inline-flex; align-items:center; }
    .em-v4-item__delete:hover, .em-v4-item__delete:focus, .em-v4-item__delete:active { background:none !important; box-shadow:none !important; color:#8a2424; }
    .em-v4-item__delete .dashicons { font-size:18px; width:18px; height:18px; }
    .em-v4-item__nameinput { text-transform:uppercase; font-weight:600; min-width:200px; }
    /* Boutons valider / annuler du renommage inline (items + rubriques) */
    .em-v4-item__confirm, .em-v4-item__cancel, .em-v4-card__confirm, .em-v4-card__cancel { background:#fff; border:1px solid #c3c4c7; border-radius:4px; cursor:pointer; padding:2px 4px; margin-left:4px; display:inline-flex; align-items:center; line-height:1; }
    .em-v4-item__confirm, .em-v4-card__confirm { color:#1d7b34; }
    .em-v4-item__confirm:hover, .em-v4-card__confirm:hover { border-color:#1d7b34; background:#f0faf2; }
    .em-v4-item__cancel, .em-v4-card__cancel { color:#646970; }
    .em-v4-item__cancel:hover, .em-v4-card__cancel:hover { border-color:#b32d2e; color:#b32d2e; background:#fcf0f0; }
    .em-v4-item__confirm .dashicons, .em-v4-item__cancel .dashicons, .em-v4-card__confirm .dashicons, .em-v4-card__cancel .dashicons { font-size:16px; width:16px; height:16px; }
    .em-v4-item__confirm[hidden], .em-v4-item__cancel[hidden], .em-v4-card__confirm[hidden], .em-v4-card__cancel[hidden] { display:none !important; }

    /* Apparence (couleurs globales) */
    .em-v4-appearance { display:flex; flex-direction:column; gap:10px; margin:0 0 14px; padding:10px 12px; background:#fff; border:1px solid #dcdcde; border-radius:6px; }
    .em-v4-builder__section > .em-v4-collapse__summary strong { font-size:13px; text-transform:uppercase; letter-spacing:.04em; }
    .em-v4-collapse__body .em-v4-appearance { border:0; background:transparent; padding:0; margin:0 0 10px; border-radius:0; }
    .em-v4-appearance__line { display:flex; flex-wrap:wrap; gap:18px; align-items:center; }
    .em-v4-appearance__title { font-size:11px; text-transform:uppercase; letter-spacing:.04em; color:#6b7280; min-width:90px; }
    .em-v4-appearance__item { display:flex; align-items:center; gap:10px; }
    .em-v4-appearance__label { font-size:13px; color:#374151; }
    .em-v4-appearance__toggle, .em-v4-appearance__num, .em-v4-appearance__font { display:flex; align-items:center; gap:6px; }
    .em-v4-appearance__num-input { width:72px; }
    /* Pastilles de couleur carrées (au lieu de rondes) dans l'Apparence */
    .em-v4-appearance .em-wp-admin-color-trigger__swatch { border-radius:4px; }
    /* Espacements liables (haut/bas, gauche/droite) */
    .em-v4-appearance__group { display:inline-flex; align-items:center; gap:8px; }
    .em-v4-appearance__chain { display:inline-flex; align-items:center; justify-content:center; width:26px; height:26px; padding:0; border:1px solid #dcdcde; border-radius:5px; background:#fff; color:#6b7280; cursor:pointer; }
    .em-v4-appearance__chain:hover { border-color:#2271b1; color:#2271b1; }
    .em-v4-appearance__chain[aria-pressed="true"] { border-color:#2271b1; background:#2271b1; color:#fff; }
    .em-v4-appearance__chain .dashicons { font-size:16px; width:16px; height:16px; }
    .em-v4-appearance__font-input { max-width:220px; }
    .em-v4-appearance__bgpos-input { max-width:220px; }
    /* Image de fond (sélecteur média) */
    .em-v4-appearance__bgmedia { display:inline-flex; align-items:center; gap:8px; }
    .em-v4-appearance__bgthumb { width:46px; height:32px; object-fit:cover; border-radius:4px; border:1px solid #dcdcde; background:#f0f0f1; }
    .em-v4-appearance__bgclear { display:inline-flex; align-items:center; justify-content:center; width:22px; height:22px; padding:0; border:0; background:transparent; color:#b32d2e; font-size:18px; line-height:1; cursor:pointer; border-radius:4px; }
    .em-v4-appearance__bgclear:hover { background:#fbeaea; }
    .em-v4-appearance__bgopacity-input { width:90px; }
    .em-v4-appearance__bgopacity-out { font-size:12px; color:#6b7280; min-width:34px; }

    /* En-tête de ligne : colonnes + alignement par colonne */
    /* Colonnes + alignement : visibles uniquement quand la ligne est ouverte, à côté du libellé. */
    .em-v4-row__layout { display:flex; flex-wrap:nowrap; align-items:center; gap:8px; margin-left:14px; }
    .em-v4-row:not([open]) > .em-v4-row__summary > .em-v4-row__layout { display:none; }
    .em-v4-rowcols-label { display:flex; flex-direction:row; align-items:center; gap:5px; font-size:10px; text-transform:uppercase; letter-spacing:.04em; color:#6b7280; }
    .em-v4-rowcols { height:28px; min-height:28px; line-height:1; padding:0 18px 0 4px; margin:0; font-size:12px; vertical-align:middle; border:0; box-shadow:none; background-color:transparent; }
    .em-v4-rowcols:focus { border:0; box-shadow:none; outline:none; }
    .em-v4-align { display:flex; flex-direction:row; align-items:center; }
    .em-v4-align__label { display:none; }
    .em-v4-align__group { display:inline-flex; border:1px solid #c3c4c7; border-radius:5px; overflow:hidden; background:#fff; }
    /* Replié : on n'affiche que l'alignement choisi ; ouvert : les 4 options. */
    .em-v4-align__group:not(.is-open) .em-v4-align__btn:not(.is-active) { display:none; }
    .em-v4-align__group:not(.is-open) .em-v4-align__btn.is-active { border-left:0; }
    .em-v4-align__btn { display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; padding:0; border:0; border-left:1px solid #e2e4e7; background:#fff; color:#50575e; cursor:pointer; }
    .em-v4-align__btn:first-of-type { border-left:0; }
    .em-v4-align__btn:hover { background:#f0f0f1; color:#1d2327; }
    .em-v4-align__btn.is-active { background:#2271b1; color:#fff; }
    /* Onglet replié : l'icône d'alignement affichée est en noir (pas en bleu). */
    .em-v4-align__group:not(.is-open) .em-v4-align__btn.is-active { background:#fff; color:#1d2327; }
    .em-v4-align__btn .dashicons { font-size:18px; width:18px; height:18px; }

    /* Mini-carte de la grille (à côté de « Contenu ») — large & plate (≈ section front) */
    .em-v4-gridmap { display:inline-flex; flex-direction:column; gap:3px; margin-left:12px; padding:4px; width:142px; box-sizing:border-box; border:1px solid #c3c4c7; border-radius:5px; background:#f6f7f8; vertical-align:middle; }
    .em-v4-gridmap:empty { display:none; }
    .em-v4-gridmap__row { display:flex; gap:3px; }
    .em-v4-gridmap__cell { flex:1 1 0; min-width:0; height:9px; border-radius:2px; background:#aab2bd; cursor:pointer; transition:background .12s ease; }
    .em-v4-gridmap__cell:hover { background:#7b8593; }
    .em-v4-gridmap__cell.is-active { background:#155a9c; }
    /* Œil « toute la section » à droite de la carte */
    .em-v4-gridmap__eye { margin-left:8px; padding:0 2px; border:0; background:transparent; color:#2271b1; cursor:pointer; display:inline-flex; align-items:center; vertical-align:middle; }
    .em-v4-gridmap__eye:hover { color:#135e96; }
    .em-v4-gridmap__eye[aria-pressed="true"] { color:#135e96; }
    .em-v4-gridmap__eye .dashicons { font-size:18px; width:18px; height:18px; }

    /* Bulle d'aperçu d'une cellule de la carte (survol) */
    .em-v4-gridmap__pop { position:absolute; z-index:100000; display:none; max-width:380px; min-width:180px; padding:8px; background:#fff; border:1px solid #c3c4c7; border-radius:8px; box-shadow:0 8px 28px rgba(16,24,40,.20); pointer-events:none; }
    .em-v4-gridmap__pop .em-rubrique--preview { padding:0; font-size:12px; }

    /* Vignette d'aperçu RÉDUIT, INTÉGRÉE à la ligne « Contenu » (à droite de l'œil), temps réel */
    .em-v4-miniprev { display:inline-block; margin-left:8px; vertical-align:middle; background:#fff; border:1px solid #c3c4c7; border-radius:6px; box-shadow:0 1px 4px rgba(16,24,40,.12); overflow:hidden; }
    .em-v4-miniprev[hidden] { display:none; }
    .em-v4-miniprev__stage { overflow:hidden; width:100%; height:100%; }
    .em-v4-miniprev__stage .em-v4-livepreview { border:0; margin:0; }
    .em-v4-gridmap__pop-empty { font-size:12px; color:#6b7280; padding:4px 6px; font-style:italic; }
    .em-rubrique .em-v4-gridmap__pop-empty { color:inherit; opacity:.7; text-align:center; }

    /* Item « Masqué » signalé dans l'aperçu (au lieu d'être omis) */
    .em-rubrique__masked { display:inline-flex; align-items:center; gap:4px; padding:2px 8px; margin:2px; border:1px dashed currentColor; border-radius:999px; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.04em; opacity:.55; }
    .em-rubrique__masked .dashicons { font-size:14px; width:14px; height:14px; }

    /* Builder : lignes × colonnes */
    .em-v4-rows { counter-reset: emrow; display:flex; flex-direction:column; gap:10px; margin:8px 0; }
    .em-v4-row { counter-increment: emrow; background:#fff; border:1px solid #dcdcde; border-radius:6px; }
    .em-v4-row__summary { list-style:none; cursor:pointer; display:flex; align-items:center; gap:8px; padding:8px 12px; user-select:none; }
    .em-v4-row__summary::-webkit-details-marker { display:none; }
    .em-v4-row[open] > .em-v4-row__summary { border-bottom:1px solid #f0f0f1; }
    .em-v4-row[open] > .em-v4-row__summary > .em-v4-collapse__chevron { transform:rotate(90deg); }
    .em-v4-row__label { font-size:11px; text-transform:uppercase; letter-spacing:.03em; color:#6b7280; }
    .em-v4-row__label::before { content:"Ligne " counter(emrow); }
    .em-v4-row__colcount { display:inline-flex; align-items:center; gap:2px; margin-left:6px; font-size:11px; color:#8a929e; font-variant-numeric:tabular-nums; }
    .em-v4-row__colcount .dashicons { font-size:15px; width:15px; height:15px; }
    .em-v4-row[open] > .em-v4-row__summary > .em-v4-row__colcount { display:none; }
    .em-v4-row__drag { cursor:grab; color:#aab1bd; flex:0 0 auto; }
    .em-v4-row__drag:hover { color:#1d2327; }
    .em-v4-row.is-dragging { opacity:.5; outline:2px dashed #2271b1; }
    .em-v4-row__add { margin-left:auto; border:0; background:transparent; color:#2271b1; cursor:pointer; padding:0; border-radius:4px; display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; align-self:center; }
    .em-v4-row__add:hover { background:#eef2f7; }
    .em-v4-row__add .dashicons { font-size:18px; width:18px; height:18px; }
    .em-v4-row__remove { border:0; background:transparent; color:#b32d2e; font-size:16px; line-height:1; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; width:24px; height:28px; align-self:center; }
    /* Corps de ligne : onglets de colonnes + panneau actif en pleine largeur. */
    .em-v4-row__body { padding:10px 12px; }
    .em-v4-col-tabs { display:flex; flex-wrap:wrap; align-items:flex-end; gap:4px; border-bottom:1px solid #e2e4e7; margin-bottom:10px; }
    .em-v4-col-tab { display:inline-flex; align-items:center; gap:6px; padding:5px 8px; border:1px solid transparent; border-bottom:0; border-radius:6px 6px 0 0; background:transparent; cursor:pointer; margin-bottom:-1px; }
    .em-v4-col-tab:not(.is-active):hover { background:#f0f0f1; }
    .em-v4-col-tab.is-active { background:#fff; border-color:#dcdcde; box-shadow:inset 0 3px 0 #751820; }
    .em-v4-col-tab__name { font-size:11px; text-transform:uppercase; letter-spacing:.04em; color:#6b7280; white-space:nowrap; }
    .em-v4-col-tab.is-active .em-v4-col-tab__name { color:#751820; font-weight:700; }
    .em-v4-col-tab .em-v4-align { margin:0; }
    .em-v4-col-tab__del { border:0; background:transparent; color:#b32d2e; font-size:15px; line-height:1; cursor:pointer; padding:0 2px; border-radius:3px; display:inline-flex; align-items:center; }
    .em-v4-col-tab__del:hover { background:#fbeaea; }
    .em-v4-col-tab__move-group { display:inline-flex; align-items:center; gap:1px; }
    .em-v4-col-tab__move { border:0; background:transparent; color:#646970; cursor:pointer; padding:1px; border-radius:3px; display:inline-flex; align-items:center; justify-content:center; }
    .em-v4-col-tab__move .dashicons { font-size:14px; width:14px; height:14px; line-height:14px; }
    .em-v4-col-tab__move:hover:not(:disabled) { background:#eef0f2; color:#751820; }
    .em-v4-col-tab__move:disabled { opacity:.3; cursor:default; }
    /* Une seule colonne : déplacement inutile. */
    .em-v4-col-tab:first-of-type:last-of-type .em-v4-col-tab__move-group { display:none; }
    /* Une seule colonne : pas de croix (on ne peut pas supprimer la dernière). */
    .em-v4-col-tab:first-of-type:last-of-type .em-v4-col-tab__del { display:none; }
    .em-v4-col-tab__add { align-self:center; display:inline-flex; align-items:center; gap:4px; border:1px dashed #c3c4c7; background:#fff; color:#2271b1; height:28px; padding:0 10px; border-radius:5px; cursor:pointer; font-size:12px; margin-bottom:3px; flex:0 0 auto; white-space:nowrap; }
    .em-v4-col-tab__add .dashicons { font-size:16px; width:16px; height:16px; }
    .em-v4-col-tab__add:hover { background:#f0f6fc; border-color:#2271b1; }
    /* Pictos de colonnes : un picto = une colonne (ligne ouverte et fermée). */
    .em-v4-colpips { display:inline-flex; align-items:center; gap:3px; }
    .em-v4-colpip { width:4px; height:12px; background:#8a929e; border-radius:1px; }
    .em-v4-col-panels { min-width:0; }
    .em-v4-col { min-width:0; }
    .em-v4-col:not(.is-active) { display:none; }
    .em-v4-col.is-active { display:flex; flex-direction:column; }
    .em-v4-col__drop { min-height:40px; padding:0 0 8px; display:flex; flex-direction:column; gap:6px; }

    .em-v4-chip { display:flex; align-items:center; gap:8px; background:#fff; border:1px solid #dcdcde; border-radius:7px; padding:8px 9px; box-shadow:0 1px 2px rgba(16,24,40,.04); }
    .em-v4-chip.is-dragging { opacity:.5; border-style:dashed; }
    .em-v4-chip--decor { align-items:center; }
    .em-v4-chip--decor .em-v4-chip__url { flex:1 1 auto; min-width:0; }
    .em-v4-chip__drag { cursor:grab; color:#aab1bd; flex:0 0 auto; }
    .em-v4-chip__type { display:inline-flex; align-items:center; gap:4px; font-size:9px; font-weight:600; text-transform:uppercase; letter-spacing:.04em; color:#475569; background:#eef2f7; padding:3px 6px; border-radius:4px; flex:0 0 auto; }
    .em-v4-chip__typeicon { font-size:13px; width:13px; height:13px; line-height:13px; color:#2271b1; }
    .em-v4-chip__media--av { display:inline-flex; align-items:center; gap:6px; flex-wrap:wrap; }
    .em-v4-chip__medianame { font-size:11px; color:#50575e; max-width:160px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .em-v4-chip__slider { display:flex; align-items:center; gap:6px; flex-wrap:wrap; }
    .em-v4-chip__slides { display:flex; gap:4px; flex-wrap:wrap; }
    .em-v4-chip__slide { position:relative; width:40px; height:40px; border-radius:4px; overflow:hidden; border:1px solid #dcdcde; }
    .em-v4-chip__slide img { width:100%; height:100%; object-fit:cover; display:block; }
    .em-v4-chip__slide-del { position:absolute; top:0; right:0; border:0; background:rgba(0,0,0,.6); color:#fff; cursor:pointer; line-height:1; padding:0 3px; border-bottom-left-radius:4px; }
    /* Éditeur de slides riche (champ Slider V4) : prend toute la largeur de la chip. */
    .em-v4-slides { flex:1 1 100%; min-width:0; display:flex; flex-direction:column; gap:8px; background:#f8fafc; border:1px solid #eef0f3; border-radius:6px; padding:8px; }
    .em-v4-slides__opts { display:flex; flex-wrap:wrap; align-items:center; gap:10px; padding-bottom:6px; border-bottom:1px solid #e9edf2; }
    .em-v4-slides__opt { display:inline-flex; align-items:center; gap:6px; font-size:11px; color:#475569; }
    .em-v4-slides__opt > span { font-weight:600; }
    .em-v4-slides__opt--check { gap:4px; }
    .em-v4-slides__title { min-height:28px; min-width:180px; }
    .em-v4-slides__opt input[type="color"] { width:30px; height:24px; padding:0; border:1px solid #dcdcde; border-radius:4px; background:none; cursor:pointer; }
    .em-v4-slides__list { display:flex; flex-direction:column; gap:6px; }
    .em-v4-slide { display:flex; align-items:center; gap:6px; flex-wrap:wrap; background:#fff; border:1px solid #e3e7ec; border-radius:6px; padding:6px 8px; }
    .em-v4-slide.is-hidden { opacity:.55; }
    .em-v4-slide__move { display:inline-flex; flex-direction:column; gap:1px; flex:0 0 auto; }
    .em-v4-slide__move button { border:0; background:#eef2f7; color:#475569; cursor:pointer; line-height:1; padding:1px 5px; border-radius:3px; font-size:9px; }
    .em-v4-slide__move button:hover { background:#dde6f1; }
    .em-v4-slide__type { flex:0 0 auto; min-height:28px; }
    .em-v4-slide__name { flex:1 1 130px; min-width:80px; min-height:28px; }
    .em-v4-slide__videourl, .em-v4-slide__tiktokurl { flex:0 1 200px; min-width:120px; min-height:28px; }
    .em-v4-slide__duration { width:56px; min-height:28px; }
    .em-v4-slide__media { display:inline-flex; align-items:center; gap:6px; flex:0 0 auto; }
    .em-v4-slide__thumb { width:40px; height:40px; object-fit:cover; border-radius:4px; border:1px solid #dcdcde; background:#f0f0f1; }
    .em-v4-slide__eye, .em-v4-slide__del { border:0; background:none; cursor:pointer; line-height:1; flex:0 0 auto; }
    .em-v4-slide__eye { color:#475569; }
    .em-v4-slide__del { color:#b32d2e; font-size:18px; }
    /* Affichage conditionnel des contrôles selon le type de slide. */
    .em-v4-slide .em-v4-slide__videourl, .em-v4-slide .em-v4-slide__tiktokurl, .em-v4-slide .em-v4-slide__media--ttvid { display:none; }
    .em-v4-slide[data-type="image"] .em-v4-slide__media--image { display:inline-flex; }
    .em-v4-slide[data-type="video"] .em-v4-slide__media--image { display:none; }
    .em-v4-slide[data-type="video"] .em-v4-slide__videourl { display:inline-flex; }
    .em-v4-slide[data-type="tiktok"] .em-v4-slide__tiktokurl, .em-v4-slide[data-type="tiktok"] .em-v4-slide__media--ttvid, .em-v4-slide[data-type="tiktok"] .em-v4-slide__media--image { display:inline-flex; }
    .em-v4-slides__add { align-self:flex-start; }
    /* Aperçu temps réel du slider : placeholder de slide sans média (le reste du
       look vient de la CSS front mayami chargée sur la page builder). */
    .em-v4-livepreview .em-slider--mayami, .em-v4-miniprev .em-slider--mayami { margin:0 auto; }
    .em-slider--mayami .em-slider__ph { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; color:#fff; font-size:22px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; opacity:.8; }
    /* Tous les champs du contenu sur UNE seule ligne (saisie + lien + style).
       Les saisies ne s'étirent pas (flex-grow:0) : largeurs compactes, le vide
       restant pousse les actions (œil/croix) à droite. */
    .em-v4-chip__fields { display:flex; flex-direction:row; flex-wrap:wrap; align-items:center; gap:6px; flex:1 1 auto; min-width:0; }
    .em-v4-chip__fields > .em-v4-chip__label { font-size:11px; font-weight:600; color:#475569; background:#f8fafc; flex:0 1 190px; min-width:70px; width:auto; }
    .em-v4-chip__fields .em-v4-chip__value, .em-v4-chip__fields .em-v4-chip__titext, .em-v4-chip__fields .em-v4-chip__titext2 { font-weight:500; flex:0 1 190px; min-width:60px; width:auto; }
    .em-v4-chip__fields .em-v4-chip__tlink { flex:0 1 120px; min-width:60px; width:auto; }
    .em-v4-chip__fields input[type="text"], .em-v4-chip__fields input[type="url"], .em-v4-chip__fields input[type="number"], .em-v4-chip__fields select { min-height:28px; }
    .em-v4-chip__media { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
    .em-v4-chip__focal { position:relative; display:inline-block; line-height:0; cursor:crosshair; }
    .em-v4-chip__thumb { width:64px; height:48px; object-fit:contain; background:#f0f0f1; border-radius:4px; border:1px solid #dcdcde; }
    .em-v4-chip__focaldot { position:absolute; width:10px; height:10px; margin:-5px 0 0 -5px; border:2px solid #fff; border-radius:50%; background:#2271b1; box-shadow:0 0 0 1px #2271b1; pointer-events:none; }
    .em-v4-chip__pick { flex:0 0 auto; }
    .em-v4-chip__size { display:flex; align-items:center; gap:12px; flex-wrap:wrap; padding:6px 8px; background:#f8fafc; border:1px solid #eef0f3; border-radius:6px; }
    .em-v4-chip__sizelabel { display:inline-flex; align-items:center; gap:6px; font-size:11px; color:#6b7280; }
    .em-v4-chip__sizelabel input[type="range"] { width:120px; }
    .em-v4-chip__wout { min-width:38px; font-variant-numeric:tabular-nums; color:#1d2327; }
    .em-v4-chip__h { width:96px; }
    .em-v4-chip__height { width:120px; }

    /* Réglages de style propres au champ texte : groupe compact, sur la même ligne. */
    .em-v4-chip__tstyle { display:inline-flex; align-items:center; gap:6px; flex-wrap:nowrap; flex:0 0 auto; padding:4px 6px; background:#f8fafc; border:1px solid #eef0f3; border-radius:6px; }
    .em-v4-chip__tstyle::before { content:"Aa"; font-weight:700; font-size:11px; color:#94a3b8; line-height:1; }
    .em-v4-chip__tsize { width:64px; flex:0 0 auto; }
    .em-v4-chip__tfont { width:120px; max-width:140px; font-size:12px; flex:0 0 auto; }
    .em-v4-chip__tstyle .em-wp-admin-color-field-row { margin:0; }
    .em-v4-chip__btncolor { display:inline-flex; align-items:center; gap:5px; flex:0 0 auto; padding:3px 6px; background:#f8fafc; border:1px solid #eef0f3; border-radius:6px; }
    .em-v4-chip__btncolor-label { font-size:11px; font-weight:700; color:#94a3b8; line-height:1; }
    .em-v4-chip__btncolor .em-wp-admin-color-field-row { margin:0; }

    .em-v4-chip__actions { display:flex; align-items:center; gap:2px; flex:0 0 auto; margin-top:0; }
    .em-v4-chip__remove { border:0; background:transparent; color:#b32d2e; font-size:18px; line-height:1; cursor:pointer; padding:0 4px; border-radius:4px; flex:0 0 auto; display:inline-flex; align-items:center; justify-content:center; height:24px; }
    .em-v4-chip__remove:hover { background:#fbeaea; }
    .em-v4-chip__toggle { border:0; background:transparent; color:#6b7280; cursor:pointer; padding:0 4px; border-radius:4px; flex:0 0 auto; display:inline-flex; align-items:center; justify-content:center; height:24px; }
    .em-v4-chip__toggle:hover { color:#1d2327; background:#eef2f7; }
    .em-v4-chip__toggle .dashicons { font-size:18px; width:18px; height:18px; }
    .em-v4-chip.is-hidden { opacity:.55; background:#f1f1f3; }
    .em-v4-chip.is-hidden .em-v4-chip__type::after { content:" (masqué)"; color:#b32d2e; font-weight:600; }
    .em-v4-chip.is-hidden .em-v4-chip__toggle { color:#b32d2e; }

    /* Carte « Bloc Plateforme » (aperçu) — rendu identique à la section Stream du site. */
    .em-rubrique__platform-card { box-sizing:border-box; display:flex; align-items:center; justify-content:space-between; gap:16px; width:100%; max-width:100%; padding:20px 24px; border:2px solid #100421; border-radius:16px; background:#fff6ea; color:#100421; text-decoration:none; text-align:left; box-shadow:6px 6px 0 #100421; font-family:"Archivo Black", Arial, sans-serif; transition:transform .12s ease; }
    .em-rubrique__platform-card *, .em-rubrique__platform-card { box-sizing:border-box; }
    .em-rubrique__platform-card:hover { transform:translate(-2px,-4px); }
    .em-rubrique__platform-card-body { flex:1 1 auto; min-width:0; }
    .em-rubrique__platform-card-label { display:block; margin:0 0 4px; font-size:10px; letter-spacing:.25em; text-transform:uppercase; opacity:.7; }
    .em-rubrique__platform-card-title { display:flex; align-items:center; gap:8px; margin:0; font-size:24px; line-height:1; }
    .em-rubrique__platform-card-icon { font-size:.9em; }
    .em-rubrique__platform-card-arrow { font-size:24px; flex:0 0 auto; transition:transform .12s ease; }
    .em-rubrique__platform-card:hover .em-rubrique__platform-card-arrow { transform:translateX(4px); }
    .em-v4-chip__platform { max-width:100%; }
    .em-v4-chip__ptitle { width:100%; }

    /* Champ « Texte + Image » : côte à côte, centré verticalement (comme le titre du site). */
    .em-rubrique__textimg { display:flex; align-items:center; gap:14px; flex-wrap:wrap; }
    .em-rubrique__textimg > * { margin:0; }
    .em-rubrique__col--center .em-rubrique__textimg { justify-content:center; }
    .em-rubrique__col--right .em-rubrique__textimg { justify-content:flex-end; }
    .em-rubrique__col--justify .em-rubrique__textimg { justify-content:space-between; }
    .em-rubrique__texttext { display:flex; align-items:baseline; gap:10px; flex-wrap:wrap; }
    .em-rubrique__texttext > * { margin:0; }
    .em-rubrique__col--center .em-rubrique__texttext { justify-content:center; }
    .em-rubrique__col--right .em-rubrique__texttext { justify-content:flex-end; }
    .em-rubrique__col--justify .em-rubrique__texttext { justify-content:space-between; }
    .em-v4-chip__tt-part { display:inline-flex; align-items:center; gap:6px; flex-wrap:nowrap; flex:1 1 240px; min-width:0; }
    .em-v4-chip__check { display:inline-flex; align-items:center; gap:4px; font-size:12px; color:#50575e; white-space:nowrap; }
    .em-v4-chip__vthumb { flex:0 0 auto; }
    .em-v4-chip__ti-image { display:flex; align-items:center; gap:6px; flex-wrap:wrap; }

    /* Ajout d'un champ dans une cellule */
    .em-v4-celladd { padding:0; }
    .em-v4-celladd__btn { width:100%; border:1px dashed #cbd5e1; background:#fff; color:#2271b1; border-radius:5px; padding:5px; cursor:pointer; font-size:12px; display:flex; align-items:center; justify-content:center; gap:4px; }
    .em-v4-celladd__btn:hover { background:#f0f6fc; }
    .em-v4-celladd__form[hidden] { display:none; }
    .em-v4-celladd__form:not([hidden]) { display:flex; gap:5px; align-items:center; flex-wrap:wrap; margin-top:6px; }
    .em-v4-celladd__type { flex:1 1 auto; }
    .em-v4-celladd__cancel { border:0; background:transparent; color:#6b7280; font-size:16px; cursor:pointer; }

    .em-v4-builder__actions { display:flex; gap:8px; align-items:center; margin-top:12px; }

    .em-v4-sticky { position:sticky; top:32px; z-index:20; margin:0 0 14px; }
    .em-v4-savebar { display:flex; align-items:center; justify-content:flex-start; gap:12px; margin:0; padding:8px 0; background:transparent; border:0; box-shadow:none; }
    .em-v4-savebar[hidden] { display:none; }
    /* Boutons de la savebar : mêmes styles que le modal de confirmation (bordeaux, pas de bleu) */
    .em-v4-savebar__btn.button-primary { border-color:#4e080e !important; background:linear-gradient(180deg,#751820 0%,#4e080e 100%) !important; color:#fff !important; text-shadow:none !important; box-shadow:0 1px 0 rgba(255,255,255,.18) inset, 0 2px 8px rgba(78,8,14,.28) !important; }
    .em-v4-savebar__btn.button-primary:hover, .em-v4-savebar__btn.button-primary:focus { border-color:#3d060b !important; background:linear-gradient(180deg,#651620 0%,#3d060b 100%) !important; color:#fff !important; box-shadow:0 1px 0 rgba(255,255,255,.14) inset, 0 4px 12px rgba(78,8,14,.34) !important; }
    .em-v4-savebar__revert { display:inline-flex; align-items:center; height:30px; padding:0 12px; border:1px solid #c3c4c7; border-radius:3px; background:#fff; color:#50575e; font-size:13px; font-weight:500; line-height:1; cursor:pointer; box-shadow:0 1px 0 rgba(255,255,255,.9) inset; }
    .em-v4-savebar__revert:hover, .em-v4-savebar__revert:focus { border-color:#751820; color:#751820; background:#fafafa; }
    /* Contrôles d'aperçu sur la ligne du nom de la rubrique (œil + nouvel onglet) */
    .em-v4-item__preview { display:inline-flex; align-items:center; gap:2px; margin-left:8px; }
    .em-v4-preview__toggle, .em-v4-preview__popout { border:0; background:transparent; color:#2271b1; cursor:pointer; padding:3px 5px; border-radius:4px; display:inline-flex; align-items:center; }
    .em-v4-preview__toggle:hover, .em-v4-preview__popout:hover { background:#eef2f7; }
    .em-v4-preview__toggle[aria-pressed="true"] { color:#1d2327; background:#eef2f7; }
    .em-v4-preview__toggle .dashicons { font-size:18px; width:18px; height:18px; }
    .em-v4-preview__popout .dashicons { font-size:16px; width:16px; height:16px; }
    .em-v4-preview__frame, .em-v4-livepreview { border:1px dashed #cbd5e1; border-radius:6px; overflow:hidden; }
    .em-v4-livepreview { margin-top:8px; }
    .em-v4-livepreview[hidden] { display:none; }
    .em-v4-livepreview:empty::before { content:"…"; display:block; padding:18px; color:#9ca3af; text-align:center; }

    /* Rendu d'un item (lignes/colonnes) */
    .em-rubrique { position:relative; overflow:hidden; background-color:var(--em-rubrique-bg,#0f172a); color:var(--em-rubrique-text,#e2e8f0); padding:var(--em-rubrique-pt,18px) var(--em-rubrique-pr,20px) var(--em-rubrique-pb,18px) var(--em-rubrique-pl,20px); font-family:var(--em-rubrique-font,inherit); }
    /* Calque image de fond (séparé du contenu) : permet opacité + miroir comme sur le front. */
    .em-rubrique::before { content:""; position:absolute; inset:0; z-index:0; background-image:var(--em-rubrique-bg-image,none); background-size:var(--em-rubrique-bg-size,cover); background-repeat:var(--em-rubrique-bg-repeat,no-repeat); background-position:var(--em-rubrique-bg-position,center); opacity:var(--em-rubrique-bg-opacity,1); transform:var(--em-rubrique-bg-transform,none); pointer-events:none; }
    .em-rubrique > * { position:relative; z-index:1; }
    /* HEADER composite : le « shell » porte le fond partagé pleine largeur ;
       HERO/SLIDER posés dans une grille CENTRÉE par-dessus, sans fond propre
       (transparents). Reproduit .em-landing-header + .em-landing-hero-row du front. */
    /* Fond partagé adouci comme sur le site (filtre + lissage). */
    .em-header-shell::before { filter:brightness(1.12) saturate(0.95); }
    .em-header-shell__inner { position:relative; z-index:1; width:min(1100px,92vw); margin:0 auto; padding:44px 0 64px; display:grid; gap:28px; align-items:start; }
    .em-header-shell__inner.is-single { grid-template-columns:minmax(0,1fr) !important; }
    .em-header-shell__col { position:relative; z-index:1; min-width:0; }
    .em-header-shell__col--slider { display:flex; justify-content:center; }
    .em-header-shell__col--slider .em-slider--mayami { width:100%; max-width:430px; margin:0; }
    .em-header-shell .em-rubrique { background-color:transparent !important; padding-left:0 !important; padding-right:0 !important; }
    .em-header-shell .em-rubrique::before { display:none !important; }
    @media (max-width:960px) {
        .em-header-shell__inner, .em-header-shell__inner.is-slider-first { grid-template-columns:minmax(0,1fr) !important; }
    }
    .em-rubrique__row { display:grid; grid-template-columns:repeat(1,minmax(0,1fr)); gap:16px; align-items:center; }
    .em-rubrique__row + .em-rubrique__row { margin-top:10px; }
    .em-rubrique__col { box-sizing:border-box; min-width:0; }
    .em-rubrique__col--left { text-align:left; }
    .em-rubrique__col--center { text-align:center; }
    .em-rubrique__col--right { text-align:right; }
    .em-rubrique__col--justify { text-align:justify; }
    .em-rubrique__sep { width:100%; border:0; border-top:1px solid currentColor; opacity:.25; margin:8px 0; }
    .em-rubrique__spacer { display:block; height:12px; }
    .em-rubrique__arrow { display:inline-block; font-size:18px; line-height:1; }
    .em-rubrique__arrow + .em-rubrique__arrow,
    .em-rubrique__arrow + .em-rubrique__arrow-link,
    .em-rubrique__arrow-link + .em-rubrique__arrow,
    .em-rubrique__arrow-link + .em-rubrique__arrow-link { margin-left:var(--em-rubrique-arrow-gap,12px); }
    .em-v4-chip--decor { align-items:center; }
    .em-v4-chip--decor .em-v4-chip__type { font-weight:600; color:#1d2327; text-transform:none; }
    .em-rubrique__field { margin:0 0 4px; }
    .em-rubrique__link { color:var(--em-rubrique-link,inherit); text-decoration:var(--em-rubrique-underline,none); margin:0 8px; }
    .em-rubrique__link:not(.em-rubrique__link--media):visited { color:var(--em-rubrique-link-visited,var(--em-rubrique-link,inherit)); }
    .em-rubrique__link:hover { color:var(--em-rubrique-link-hover,var(--em-rubrique-link,inherit)); }
    .em-rubrique__link:focus, .em-rubrique__link:active, .em-rubrique__link:focus-visible { outline:none; box-shadow:none; }
    .em-rubrique__link--media { display:inline-flex; align-items:center; margin:0; text-decoration:none; color:var(--em-rubrique-link,inherit); }
    .em-rubrique__link--media:hover { color:var(--em-rubrique-link-hover,var(--em-rubrique-link,inherit)); }
    .em-rubrique__image { max-width:100%; height:auto; }
    .em-rubrique__video, .em-rubrique__audio { max-width:100%; }
    .em-rubrique__audio { width:100%; }
    .em-rubrique__video-embed { position:relative; display:inline-block; vertical-align:top; width:100%; max-width:560px; aspect-ratio:16/9; text-align:left; }
    .em-rubrique__video-embed--tiktok { max-width:325px; aspect-ratio:9/16; }
    .em-rubrique__video-embed iframe { position:absolute; inset:0; width:100%; height:100%; border:0; }
    .em-rubrique__videourl { display:inline-block; vertical-align:top; width:100%; max-width:560px; text-decoration:none; }
    .em-rubrique__video-toplay { cursor:pointer; }
    .em-rubrique__video-facade { position:relative; display:block; }
    .em-rubrique__video-poster { display:block; width:100%; height:auto; border-radius:8px; }
    .em-rubrique__video-play { position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); width:68px; height:48px; border-radius:12px; background:rgba(0,0,0,.6); transition:background .15s ease; }
    .em-rubrique__video-play::after { content:""; position:absolute; top:50%; left:50%; transform:translate(-40%,-50%); border-style:solid; border-width:11px 0 11px 18px; border-color:transparent transparent transparent #fff; }
    .em-rubrique__videourl:hover .em-rubrique__video-play { background:#ff0000; }
    .em-rubrique__col--center .em-rubrique__slider { margin-inline:auto; }
    .em-rubrique__slider { display:flex; gap:12px; overflow-x:auto; scroll-snap-type:x mandatory; padding-bottom:6px; }
    .em-rubrique__slide { flex:0 0 auto; scroll-snap-align:start; max-width:80%; }
    .em-rubrique__slide-img { display:block; max-height:320px; width:auto; border-radius:8px; }
    .em-rubrique__icon { font-size:26px; line-height:26px; margin:0 7px; vertical-align:middle; }
    .em-rubrique__link--media .em-rubrique__icon { margin:0 7px; color:inherit; }
    .em-rubrique__button { display:inline-block; padding:12px 28px; border-radius:999px; background:#111; color:#fff; font-weight:800; text-transform:uppercase; letter-spacing:.03em; text-decoration:none; border:2px solid #111; box-shadow:0 4px 0 rgba(0,0,0,.45); line-height:1.1; }
    .em-rubrique__button:hover { filter:brightness(1.06); }
    .em-rubrique__button:focus, .em-rubrique__button:active, .em-rubrique__button:focus-visible { outline:none; box-shadow:0 4px 0 rgba(0,0,0,.45); }
    .em-rubrique__chip { display:inline-block; font-size:12px; opacity:.8; border:1px solid currentColor; border-radius:3px; padding:0 6px; margin:2px; }
    .em-rubrique__swatch { display:inline-block; width:18px; height:18px; border-radius:3px; border:1px solid rgba(255,255,255,.4); vertical-align:middle; margin:2px; }
</style>
<?php
