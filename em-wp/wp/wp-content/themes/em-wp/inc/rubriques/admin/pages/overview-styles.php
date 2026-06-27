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
    .em-v4-collapse__body .em-v4-appearance,
    .em-v4-collapse__body .em-v4-layout { border:0; background:transparent; padding:0; margin:0 0 10px; border-radius:0; }
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

    /* Lay-out : colonnes + alignement */
    .em-v4-layout { display:flex; flex-wrap:wrap; align-items:flex-end; gap:18px; margin:0 0 12px; padding:10px 12px; background:#fff; border:1px solid #dcdcde; border-radius:6px; }
    .em-v4-layout__count { display:flex; flex-direction:column; gap:3px; font-size:11px; text-transform:uppercase; letter-spacing:.04em; color:#6b7280; }
    .em-v4-aligns { display:flex; flex-wrap:wrap; gap:12px; flex:1 1 auto; }
    .em-v4-align { display:flex; flex-direction:column; gap:3px; }
    .em-v4-align__label { font-size:11px; text-transform:uppercase; letter-spacing:.03em; color:#6b7280; }
    .em-v4-align__group { display:inline-flex; border:1px solid #c3c4c7; border-radius:5px; overflow:hidden; background:#fff; }
    .em-v4-align__btn { display:inline-flex; align-items:center; justify-content:center; width:30px; height:30px; padding:0; border:0; border-left:1px solid #e2e4e7; background:#fff; color:#50575e; cursor:pointer; }
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
    .em-v4-row__remove { margin-left:auto; border:0; background:transparent; color:#b32d2e; font-size:16px; line-height:1; cursor:pointer; }
    .em-v4-row__cols { display:flex; gap:10px; padding:10px 12px; flex:1 1 auto; min-width:0; }
    .em-v4-col { flex:1 1 0; min-width:0; background:#fff; border:1px solid #dcdcde; border-radius:6px; display:flex; flex-direction:column; }
    .em-v4-col__head { font-size:11px; text-transform:uppercase; letter-spacing:.04em; color:#6b7280; padding:5px 8px; border-bottom:1px solid #f0f0f1; background:#f8fafc; border-radius:6px 6px 0 0; }
    .em-v4-col__drop { min-height:40px; padding:8px; display:flex; flex-direction:column; gap:6px; }

    .em-v4-chip { display:flex; align-items:center; gap:6px; background:#f8fafc; border:1px solid #dcdcde; border-radius:5px; padding:5px 6px; }
    .em-v4-chip.is-dragging { opacity:.5; border-style:dashed; }
    .em-v4-chip__drag { cursor:grab; color:#9ca3af; flex:0 0 auto; }
    .em-v4-chip__type { font-size:9px; text-transform:uppercase; letter-spacing:.03em; color:#6b7280; background:#eef2f7; padding:1px 5px; border-radius:3px; flex:0 0 auto; }
    .em-v4-chip__fields { display:flex; flex-direction:column; gap:4px; flex:1 1 auto; min-width:0; }
    .em-v4-chip__label { font-size:11px; color:#6b7280; }
    .em-v4-chip__value { font-weight:500; width:100%; }
    .em-v4-chip__media { display:flex; align-items:center; gap:6px; }
    .em-v4-chip__focal { position:relative; display:inline-block; line-height:0; cursor:crosshair; }
    .em-v4-chip__thumb { width:64px; height:48px; object-fit:contain; background:#f0f0f1; border-radius:4px; border:1px solid #dcdcde; }
    .em-v4-chip__focaldot { position:absolute; width:10px; height:10px; margin:-5px 0 0 -5px; border:2px solid #fff; border-radius:50%; background:#2271b1; box-shadow:0 0 0 1px #2271b1; pointer-events:none; }
    .em-v4-chip__pick { flex:0 0 auto; }
    .em-v4-chip__size { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
    .em-v4-chip__sizelabel { display:inline-flex; align-items:center; gap:6px; font-size:11px; color:#6b7280; }
    .em-v4-chip__sizelabel input[type="range"] { width:120px; }
    .em-v4-chip__wout { min-width:38px; font-variant-numeric:tabular-nums; color:#1d2327; }
    .em-v4-chip__h { width:96px; }
    .em-v4-chip__remove { border:0; background:transparent; color:#b32d2e; font-size:16px; line-height:1; cursor:pointer; padding:0 2px; flex:0 0 auto; }
    .em-v4-chip__toggle { border:0; background:transparent; color:#6b7280; cursor:pointer; padding:0 2px; flex:0 0 auto; display:inline-flex; align-items:center; }
    .em-v4-chip__toggle:hover { color:#1d2327; }
    .em-v4-chip__toggle .dashicons { font-size:18px; width:18px; height:18px; }
    .em-v4-chip.is-hidden { opacity:.55; background:#f1f1f3; }
    .em-v4-chip.is-hidden .em-v4-chip__type::after { content:" (masqué)"; color:#b32d2e; font-weight:600; }
    .em-v4-chip.is-hidden .em-v4-chip__toggle { color:#b32d2e; }

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
    .em-v4-preview { margin:0; padding:10px 12px; background:#fff; border:1px solid #c3c4c7; border-radius:6px; box-shadow:0 2px 6px rgba(0,0,0,.06); }
    .em-v4-savebar:not([hidden]) + .em-v4-preview { border-top-left-radius:0; border-top-right-radius:0; }
    .em-v4-preview__label { font-size:11px; text-transform:uppercase; letter-spacing:.04em; color:#6b7280; margin-bottom:4px; }
    .em-v4-preview__frame, .em-v4-livepreview { border:1px dashed #cbd5e1; border-radius:6px; overflow:hidden; }
    .em-v4-livepreview:empty::before { content:"…"; display:block; padding:18px; color:#9ca3af; text-align:center; }

    /* Rendu d'un item (lignes/colonnes) */
    .em-rubrique { background:var(--em-rubrique-bg,#0f172a); color:var(--em-rubrique-text,#e2e8f0); padding:var(--em-rubrique-pt,18px) var(--em-rubrique-pr,20px) var(--em-rubrique-pb,18px) var(--em-rubrique-pl,20px); font-family:var(--em-rubrique-font,inherit); }
    .em-rubrique__row { display:flex; gap:16px; align-items:center; }
    .em-rubrique__row + .em-rubrique__row { margin-top:10px; }
    .em-rubrique__col { flex:1 1 0; min-width:0; }
    .em-rubrique__col--left { text-align:left; }
    .em-rubrique__col--center { text-align:center; }
    .em-rubrique__col--right { text-align:right; }
    .em-rubrique__col--justify { text-align:justify; }
    .em-rubrique__sep { width:100%; border:0; border-top:1px solid currentColor; opacity:.25; margin:8px 0; }
    .em-rubrique__spacer { display:block; height:12px; }
    .em-rubrique__arrow { display:inline-block; font-size:18px; line-height:1; }
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
    .em-rubrique__icon { font-size:26px; line-height:26px; margin:0 7px; vertical-align:middle; }
    .em-rubrique__link--media .em-rubrique__icon { margin:0 7px; color:inherit; }
    .em-rubrique__chip { display:inline-block; font-size:12px; opacity:.8; border:1px solid currentColor; border-radius:3px; padding:0 6px; margin:2px; }
    .em-rubrique__swatch { display:inline-block; width:18px; height:18px; border-radius:3px; border:1px solid rgba(255,255,255,.4); vertical-align:middle; margin:2px; }
</style>
<?php
