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
    .em-v4-collapse__summary { list-style:none; cursor:pointer; display:flex; align-items:center; gap:8px; padding:12px 14px; user-select:none; }
    .em-v4-collapse__summary::-webkit-details-marker { display:none; }
    .em-v4-collapse__chevron { width:0; height:0; border-left:6px solid #6b7280; border-top:5px solid transparent; border-bottom:5px solid transparent; transition:transform .15s ease; flex:0 0 auto; }
    .em-v4-collapse[open] > .em-v4-collapse__summary > .em-v4-collapse__chevron { transform:rotate(90deg); }
    .em-v4-collapse__summary code { background:#f0f0f1; padding:1px 6px; border-radius:3px; font-size:12px; }
    .em-v4-collapse__body { padding:0 14px 14px; border-top:1px solid #f0f0f1; }

    .em-v4-card { border-color:#c3c4c7; }
    .em-v4-card > .em-v4-card__head { font-size:15px; }
    .em-v4-item, .em-v4-step, .em-v4-create { background:#fbfbfc; }

    .em-v4-badge { background:#111827; color:#fff; border-radius:3px; padding:1px 8px; font-size:11px; text-transform:uppercase; letter-spacing:.04em; }
    .em-v4-badge--default { background:#2271b1; }

    .em-v4-item__title { display:inline-flex; align-items:center; gap:6px; }
    .em-v4-item__prefix, .em-v4-item__name { text-transform:uppercase; font-weight:600; }
    .em-v4-item__edit { background:none; border:0; cursor:pointer; color:#2271b1; padding:0 2px; display:inline-flex; align-items:center; }
    .em-v4-item__edit:hover { color:#135e96; }
    .em-v4-item__edit .dashicons { font-size:18px; width:18px; height:18px; }
    .em-v4-item__nameinput { text-transform:uppercase; font-weight:600; min-width:200px; }

    /* Apparence (couleurs globales) */
    .em-v4-appearance { display:flex; flex-direction:column; gap:10px; margin:0 0 14px; padding:10px 12px; background:#fff; border:1px solid #dcdcde; border-radius:6px; }
    .em-v4-builder__section > .em-v4-collapse__summary strong { font-size:13px; text-transform:uppercase; letter-spacing:.04em; }
    .em-v4-collapse__body .em-v4-appearance { border:0; background:transparent; padding:0; margin:0 0 10px; border-radius:0; }
    .em-v4-builder__save { margin-top:0; }
    .em-v4-appearance__line { display:flex; flex-wrap:wrap; gap:18px; align-items:center; }
    .em-v4-appearance__title { font-size:11px; text-transform:uppercase; letter-spacing:.04em; color:#6b7280; min-width:90px; }
    .em-v4-appearance__item { display:flex; align-items:center; gap:10px; }
    .em-v4-appearance__label { font-size:13px; color:#374151; }
    .em-v4-appearance__toggle, .em-v4-appearance__num, .em-v4-appearance__font { display:flex; align-items:center; gap:6px; }
    .em-v4-appearance__num-input { width:72px; }
    .em-v4-appearance__font-input { max-width:220px; }
    .em-v4-appearance__preview { display:flex; align-items:center; gap:8px; }
    .em-v4-appearance__preview-label { font-size:11px; text-transform:uppercase; letter-spacing:.04em; color:#6b7280; }
    .em-v4-appearance__preview-box { display:inline-flex; align-items:center; gap:10px; padding:6px 12px; border-radius:5px; border:1px solid #dcdcde; background:#0f172a; }
    .em-v4-appearance__preview-box .ap-text { color:#e2e8f0; font-size:13px; }
    .em-v4-appearance__preview-box .ap-link { color:var(--ap-link,#38bdf8); font-size:13px; text-decoration:underline; cursor:pointer; }
    .em-v4-appearance__preview-box .ap-link.is-visited { color:var(--ap-link-visited,var(--ap-link,#38bdf8)); }
    .em-v4-appearance__preview-box .ap-link:hover { color:var(--ap-link-hover,var(--ap-link,#38bdf8)); }

    /* En-tête de ligne : colonnes + alignement par colonne */
    /* Colonnes + alignement : visibles uniquement quand la ligne est ouverte, à côté du libellé. */
    .em-v4-row__layout { display:flex; flex-wrap:nowrap; align-items:center; gap:8px; margin-left:14px; }
    .em-v4-row:not([open]) > .em-v4-row__summary > .em-v4-row__layout { display:none; }
    .em-v4-rowcols-label { display:flex; flex-direction:row; align-items:center; gap:5px; font-size:10px; text-transform:uppercase; letter-spacing:.04em; color:#6b7280; }
    .em-v4-rowcols { height:28px; min-height:28px; line-height:1; padding:0 18px 0 4px; margin:0; font-size:12px; vertical-align:middle; border:0; box-shadow:none; background-color:transparent; }
    .em-v4-rowcols:focus { border:0; box-shadow:none; outline:none; }
    .em-v4-row__aligns { display:flex; flex-wrap:nowrap; align-items:center; gap:6px; }
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
    .em-v4-align__btn .dashicons { font-size:18px; width:18px; height:18px; }

    /* Builder : lignes × colonnes */
    .em-v4-rows { counter-reset: emrow; display:flex; flex-direction:column; gap:10px; margin:8px 0; }
    .em-v4-row { counter-increment: emrow; background:#fff; border:1px solid #dcdcde; border-radius:6px; }
    .em-v4-row__summary { list-style:none; cursor:pointer; display:flex; align-items:center; gap:8px; padding:8px 12px; user-select:none; }
    .em-v4-row__summary::-webkit-details-marker { display:none; }
    .em-v4-row[open] > .em-v4-row__summary { border-bottom:1px solid #f0f0f1; }
    .em-v4-row[open] > .em-v4-row__summary > .em-v4-collapse__chevron { transform:rotate(90deg); }
    .em-v4-row__label { font-size:11px; text-transform:uppercase; letter-spacing:.03em; color:#6b7280; }
    .em-v4-row__label::before { content:"Ligne " counter(emrow); }
    .em-v4-row__drag { cursor:grab; color:#aab1bd; flex:0 0 auto; }
    .em-v4-row__drag:hover { color:#1d2327; }
    .em-v4-row.is-dragging { opacity:.5; outline:2px dashed #2271b1; }
    .em-v4-row__add { margin-left:auto; border:0; background:transparent; color:#2271b1; cursor:pointer; padding:0; border-radius:4px; display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; align-self:center; }
    .em-v4-row__add:hover { background:#eef2f7; }
    .em-v4-row__add .dashicons { font-size:18px; width:18px; height:18px; }
    .em-v4-row__remove { border:0; background:transparent; color:#b32d2e; font-size:16px; line-height:1; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; width:24px; height:28px; align-self:center; }
    .em-v4-row__cols { display:grid; gap:10px; padding:10px 12px; grid-auto-flow:column; grid-auto-columns:minmax(0,1fr); min-width:0; }
    .em-v4-col { min-width:0; background:#fff; border:1px solid #dcdcde; border-radius:6px; display:flex; flex-direction:column; }
    .em-v4-col__head { font-size:11px; text-transform:uppercase; letter-spacing:.04em; color:#6b7280; padding:5px 8px; border-bottom:1px solid #f0f0f1; background:#f8fafc; border-radius:6px 6px 0 0; }
    .em-v4-col__drop { min-height:40px; padding:8px; display:flex; flex-direction:column; gap:6px; }

    .em-v4-chip { display:flex; align-items:flex-start; gap:8px; background:#fff; border:1px solid #dcdcde; border-radius:7px; padding:8px 9px; box-shadow:0 1px 2px rgba(16,24,40,.04); }
    .em-v4-chip.is-dragging { opacity:.5; border-style:dashed; }
    .em-v4-chip--decor { align-items:center; }
    .em-v4-chip--decor .em-v4-chip__drag, .em-v4-chip--decor .em-v4-chip__type, .em-v4-chip--decor .em-v4-chip__actions { margin-top:0; }
    .em-v4-chip--decor .em-v4-chip__url { flex:1 1 auto; min-width:0; }
    .em-v4-chip__drag { cursor:grab; color:#aab1bd; flex:0 0 auto; margin-top:3px; }
    .em-v4-chip__type { display:inline-flex; align-items:center; gap:4px; font-size:9px; font-weight:600; text-transform:uppercase; letter-spacing:.04em; color:#475569; background:#eef2f7; padding:3px 6px; border-radius:4px; flex:0 0 auto; margin-top:1px; }
    .em-v4-chip__typeicon { font-size:13px; width:13px; height:13px; line-height:13px; color:#2271b1; }
    .em-v4-chip__media--av { display:inline-flex; align-items:center; gap:6px; flex-wrap:wrap; }
    .em-v4-chip__medianame { font-size:11px; color:#50575e; max-width:160px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .em-v4-chip__slider { display:flex; align-items:center; gap:6px; flex-wrap:wrap; }
    .em-v4-chip__slides { display:flex; gap:4px; flex-wrap:wrap; }
    .em-v4-chip__slide { position:relative; width:40px; height:40px; border-radius:4px; overflow:hidden; border:1px solid #dcdcde; }
    .em-v4-chip__slide img { width:100%; height:100%; object-fit:cover; display:block; }
    .em-v4-chip__slide-del { position:absolute; top:0; right:0; border:0; background:rgba(0,0,0,.6); color:#fff; cursor:pointer; line-height:1; padding:0 3px; border-bottom-left-radius:4px; }
    .em-v4-chip__fields { display:flex; flex-direction:column; gap:6px; flex:1 1 auto; min-width:0; }
    .em-v4-chip__label { font-size:11px; font-weight:600; color:#475569; background:#f8fafc; }
    .em-v4-chip__value { font-weight:500; width:100%; }
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

    /* Réglages de style propres au champ texte : groupe compact et lisible. */
    .em-v4-chip__tstyle { display:flex; align-items:center; gap:8px; flex-wrap:wrap; padding:6px 8px; background:#f8fafc; border:1px solid #eef0f3; border-radius:6px; }
    .em-v4-chip__tstyle::before { content:"Aa"; font-weight:700; font-size:11px; color:#94a3b8; line-height:1; }
    .em-v4-chip__tsize { width:62px; }
    .em-v4-chip__tfont { max-width:160px; font-size:12px; flex:1 1 120px; }
    .em-v4-chip__tstyle .em-wp-admin-color-field-row { margin:0; }

    .em-v4-chip__actions { display:flex; align-items:center; gap:2px; flex:0 0 auto; margin-top:1px; }
    .em-v4-chip__remove { border:0; background:transparent; color:#b32d2e; font-size:16px; line-height:1; cursor:pointer; padding:2px 4px; border-radius:4px; flex:0 0 auto; }
    .em-v4-chip__remove:hover { background:#fbeaea; }
    .em-v4-chip__toggle { border:0; background:transparent; color:#6b7280; cursor:pointer; padding:2px 4px; border-radius:4px; flex:0 0 auto; display:inline-flex; align-items:center; }
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
    .em-v4-chip__tt-part { display:inline-flex; align-items:center; gap:6px; flex-wrap:wrap; }
    .em-v4-chip__check { display:inline-flex; align-items:center; gap:4px; font-size:12px; color:#50575e; white-space:nowrap; }
    .em-v4-chip__vthumb { flex:0 0 auto; }
    .em-v4-chip__ti-image { display:flex; align-items:center; gap:6px; flex-wrap:wrap; }

    /* Ajout d'un champ dans une cellule */
    .em-v4-celladd { padding:0 8px 8px; }
    .em-v4-celladd__btn { width:100%; border:1px dashed #cbd5e1; background:#fff; color:#2271b1; border-radius:5px; padding:5px; cursor:pointer; font-size:12px; display:flex; align-items:center; justify-content:center; gap:4px; }
    .em-v4-celladd__btn:hover { background:#f0f6fc; }
    .em-v4-celladd__form[hidden] { display:none; }
    .em-v4-celladd__form:not([hidden]) { display:flex; gap:5px; align-items:center; flex-wrap:wrap; margin-top:6px; }
    .em-v4-celladd__label { flex:1 1 100%; }
    .em-v4-celladd__type { flex:1 1 auto; }
    .em-v4-celladd__cancel { border:0; background:transparent; color:#6b7280; font-size:16px; cursor:pointer; }

    .em-v4-builder__actions { display:flex; gap:8px; align-items:center; margin-top:12px; }

    .em-v4-item__footeractions { display:flex; flex-wrap:wrap; align-items:center; gap:16px; margin-top:12px; padding-top:10px; border-top:1px solid #f0f0f1; }
    .em-v4-dupform { display:flex; align-items:center; gap:6px; }
    .em-v4-dupform__name { text-transform:uppercase; min-width:200px; }

    .em-v4-sticky { position:sticky; top:32px; z-index:20; margin:0 0 14px; }
    .em-v4-savebar { display:flex; align-items:center; justify-content:space-between; gap:12px; margin:0; padding:8px 12px; background:#fcf9e8; border:1px solid #dba617; border-bottom:0; border-radius:6px 6px 0 0; box-shadow:0 2px 6px rgba(0,0,0,.08); }
    .em-v4-savebar[hidden] { display:none; }
    .em-v4-savebar__msg { font-size:12px; font-weight:600; color:#915b00; }
    .em-v4-preview { margin:0; padding:8px 12px; background:#fff; border:1px solid #c3c4c7; border-radius:6px; box-shadow:0 2px 6px rgba(0,0,0,.06); }
    .em-v4-savebar:not([hidden]) + .em-v4-preview { border-top-left-radius:0; border-top-right-radius:0; }
    .em-v4-preview__head { display:flex; align-items:center; gap:6px; }
    .em-v4-preview__label { font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:.04em; color:#1d2327; }
    .em-v4-preview__toggle { flex:1 1 auto; border:0; background:transparent; color:#2271b1; cursor:pointer; padding:4px 6px; border-radius:4px; display:inline-flex; align-items:center; gap:8px; text-align:left; }
    .em-v4-preview__toggle:hover { background:#eef2f7; }
    .em-v4-preview__toggle[aria-pressed="true"] { color:#1d2327; }
    .em-v4-preview__toggle .dashicons { font-size:20px; width:20px; height:20px; flex:0 0 auto; }
    .em-v4-preview__popout { flex:0 0 auto; border:0; background:transparent; color:#2271b1; cursor:pointer; padding:4px 6px; border-radius:4px; display:inline-flex; align-items:center; }
    .em-v4-preview__popout:hover { background:#eef2f7; }
    .em-v4-preview__popout .dashicons { font-size:18px; width:18px; height:18px; }
    .em-v4-preview__frame, .em-v4-livepreview { border:1px dashed #cbd5e1; border-radius:6px; overflow:hidden; }
    .em-v4-livepreview { margin-top:8px; }
    .em-v4-livepreview[hidden] { display:none; }
    .em-v4-livepreview:empty::before { content:"…"; display:block; padding:18px; color:#9ca3af; text-align:center; }

    /* Rendu d'un item (lignes/colonnes) */
    .em-rubrique { background:var(--em-rubrique-bg,#0f172a); color:var(--em-rubrique-text,#e2e8f0); padding:var(--em-rubrique-pt,18px) var(--em-rubrique-pr,20px) var(--em-rubrique-pb,18px) var(--em-rubrique-pl,20px); font-family:var(--em-rubrique-font,inherit); }
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
    .em-rubrique__chip { display:inline-block; font-size:12px; opacity:.8; border:1px solid currentColor; border-radius:3px; padding:0 6px; margin:2px; }
    .em-rubrique__swatch { display:inline-block; width:18px; height:18px; border-radius:3px; border:1px solid rgba(255,255,255,.4); vertical-align:middle; margin:2px; }
</style>
<?php
