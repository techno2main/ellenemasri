<?php
/**
 * Styles inline de la page Rubriques EM-SITE (require depuis overview.php).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<style>
    .em-site-overview h2 { margin-top: 26px; text-transform: uppercase; }

   .em-site-overview {
      --em-site-overview-accent: #9e2f3f;
      --em-site-overview-accent-strong: #751820;
      --em-site-overview-surface: #fffdfb;
      --em-site-overview-surface-soft: #fff7f4;
      --em-site-overview-border: #eadfda;
      --em-site-overview-border-strong: #dfc5c9;
      --em-site-overview-text-soft: #6f5a5d;
   }

   .em-site-overview__focus-bar,
   .em-site-overview__summary,
   .em-site-overview .em-site-cards,
   .em-site-overview .em-site-createtype {
      animation: em-site-overview-fade .26s ease;
   }

   .em-site-overview__focus-bar {
      display: none;
      position: sticky;
      top: 32px;
      z-index: 20;
      align-items: center;
      justify-content: space-between;
      gap: 18px;
      margin: 16px 0 18px;
      padding: 10px 14px;
      background: rgba(255, 251, 248, .92);
      border: 1px solid var(--em-site-overview-border);
      border-radius: 14px;
      backdrop-filter: blur(8px);
   }

   .em-site-overview.is-focus-mode .em-site-overview__focus-bar { display: flex; }

   .em-site-overview__focus-back {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      color: var(--em-site-overview-accent-strong);
      text-decoration: none;
      font-size: 12px;
      font-weight: 600;
      letter-spacing: .02em;
   }

   .em-site-overview__focus-back:hover,
   .em-site-overview__focus-back:focus { color: var(--em-site-overview-accent); }

   .em-site-overview__focus-titlewrap {
      display: flex;
      flex-direction: column;
      align-items: flex-end;
      gap: 4px;
   }

   .em-site-overview__focus-kicker {
      color: var(--em-site-overview-text-soft);
      font-size: 11px;
      font-weight: 600;
      letter-spacing: .08em;
      text-transform: uppercase;
   }

   .em-site-overview__focus-title {
      min-height: 1.3em;
      font-size: 16px;
      color: #2b2022;
   }

   .em-site-overview__summary {
      display: flex;
      flex-direction: column;
      gap: 20px;
      margin: 18px 0 18px;
      padding: 26px;
      background: linear-gradient(180deg, #fffdfb 0%, #fff8f5 100%);
      border: 1px solid var(--em-site-overview-border);
      border-radius: 22px;
      box-shadow: 0 20px 48px -36px rgba(98, 29, 41, .38);
   }

   .em-site-overview.is-focus-mode .em-site-overview__summary,
   .em-site-overview.is-focus-mode .em-site-createtype { display: none; }
   .em-site-overview:not(.is-focus-mode) .em-site-cards { display: none; }

   .em-site-overview__summary-head {
      display: flex;
      align-items: flex-start;
      justify-content: flex-start;
      gap: 24px;
   }

   .em-site-overview__eyebrow {
      margin: 0 0 8px;
      color: var(--em-site-overview-accent-strong);
      font-size: 11px;
      font-weight: 700;
      letter-spacing: .1em;
      text-transform: uppercase;
   }

   .em-site-overview__title {
      margin: 0;
      font-size: 20px;
      line-height: 1.2;
      letter-spacing: -.01em;
      text-transform: none;
      color: #24191b;
   }

   .em-site-overview__lead {
      max-width: 420px;
      margin: 2px 0 0;
      color: var(--em-site-overview-text-soft);
      font-size: 13px;
      line-height: 1.65;
   }

   .em-site-overview__directory {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 14px;
   }

   .em-site-overview__directory-link {
      display: grid;
      grid-template-columns: minmax(0, 1fr) 50px;
      align-items: start;
      column-gap: 12px;
      min-height: 92px;
      padding: 16px 18px;
      background: linear-gradient(180deg, #ffffff 0%, #fffaf7 100%);
      border: 1px solid #4f080e;
      border-radius: 18px;
      color: inherit;
      text-decoration: none;
      box-shadow: 0 14px 30px -24px rgba(94, 25, 37, .28), 0 2px 8px rgba(94, 25, 37, .06);
      transition: transform .24s cubic-bezier(.22, 1, .36, 1), border-color .22s ease, box-shadow .24s cubic-bezier(.22, 1, .36, 1), background-color .22s ease;
   }

   .em-site-overview__directory-link--create {
      cursor: pointer;
      text-align: left;
      width: 100%;
   }

   .em-site-overview__directory-link--create.is-active {
      border-color: color-mix(in srgb, var(--em-site-overview-accent) 32%, #fff);
      box-shadow: 0 0 0 1px color-mix(in srgb, var(--em-site-overview-accent) 22%, #fff), 0 18px 36px -24px rgba(117, 24, 32, .34), 0 6px 16px rgba(117, 24, 32, .08);
   }

   .em-site-overview__directory-link:hover,
   .em-site-overview__directory-link:focus {
      transform: translateY(-4px) scale(1.012);
      border-color: #4f080e;
      box-shadow: 0 26px 42px -28px rgba(94, 25, 37, .34), 0 10px 20px rgba(94, 25, 37, .08);
      background: #7d484d;
   }

   .em-site-overview__directory-link.is-active {
      border-color: color-mix(in srgb, var(--em-site-overview-accent) 32%, #fff);
      box-shadow: 0 0 0 1px color-mix(in srgb, var(--em-site-overview-accent) 22%, #fff), 0 18px 36px -24px rgba(117, 24, 32, .34), 0 6px 16px rgba(117, 24, 32, .08);
   }

   .em-site-overview__directory-icon {
      color: var(--em-site-overview-accent-strong);
      font-size: 20px;
      width: 20px;
      height: 20px;
   }

   .em-site-overview__directory-heading {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      min-width: 0;
   }

   .em-site-overview__directory-topline {
      display: flex;
      align-items: center;
      justify-content: flex-start;
      gap: 10px;
      min-height: 22px;
      min-width: 0;
   }

   .em-site-overview__directory-content {
      display: flex;
      flex-direction: column;
      gap: 8px;
      min-width: 0;
   }

   .em-site-overview__directory-label {
      font-size: 15px;
      line-height: 1.35;
      color: #2b2022;
   }

   .em-site-overview__directory-label--create {
      text-transform: uppercase;
      letter-spacing: .04em;
   }

   .em-site-overview__directory-link:hover .em-site-overview__directory-icon,
   .em-site-overview__directory-link:hover .em-site-overview__directory-label,
   .em-site-overview__directory-link:focus .em-site-overview__directory-icon,
   .em-site-overview__directory-link:focus .em-site-overview__directory-label {
      color: #fff;
   }

   .em-site-overview__directory-meta {
      display: flex;
      flex-direction: column;
      gap: 5px;
      align-items: start;
      width: 100%;
   }

   .em-site-overview__directory-pill {
      display: inline-flex;
      align-items: center;
      position: relative;
      min-height: 18px;
      max-width: 100%;
      padding: 0 0 0 12px;
      border-radius: 0;
      background: transparent;
      border: 0;
      color: #6e2a34;
      font-size: 10px;
      font-weight: 700;
      letter-spacing: .01em;
      line-height: 1.3;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
   }

   .em-site-overview__directory-pill::before {
      content: '›';
      position: absolute;
      left: 0;
      top: 50%;
      transform: translateY(-52%);
      color: #b8757f;
      font-size: 11px;
      line-height: 1;
   }

   .em-site-overview__directory-pill.is-empty {
      padding-left: 0;
      color: #8f6870;
   }

   .em-site-overview__directory-pill.is-empty::before {
      content: none;
   }

   .em-site-overview__directory-link:hover .em-site-overview__directory-pill,
   .em-site-overview__directory-link:focus .em-site-overview__directory-pill {
      color: #fff;
   }

   .em-site-overview__directory-link:hover .em-site-overview__directory-pill.is-empty,
   .em-site-overview__directory-link:focus .em-site-overview__directory-pill.is-empty {
      color: rgba(255, 255, 255, .84);
   }

   .em-site-overview__directory-link:hover .em-site-overview__directory-pill::before,
   .em-site-overview__directory-link:focus .em-site-overview__directory-pill::before {
      color: rgba(255, 255, 255, .82);
   }

   .em-site-overview__directory-arrow {
      flex: 0 0 auto;
      color: #b8757f;
      font-size: 16px;
      width: 16px;
      height: 16px;
      transition: transform .24s cubic-bezier(.22, 1, .36, 1), color .18s ease;
   }

   .em-site-overview__directory-rail {
      display: flex;
      flex-direction: column;
      align-items: flex-end;
      gap: 9px;
      width: 45px;
      min-height: 22px;
   }

   .em-site-overview__directory-map {
      display: flex;
      flex-direction: column;
      gap: 4px;
      margin-top: 2px;
      opacity: .9;
      width: 100%;
      align-items: flex-end;
   }

   .em-site-overview__directory-map-slot {
      width: 50px;
      height: 5px;
      border-radius: 1px;
      background: rgba(79, 8, 14, .24);
      transition: background-color .2s ease, transform .2s ease;
   }

   .em-site-overview__directory-map-slot.is-current {
      background: #7d484d;
      transform: scaleX(1.28);
      transform-origin: right center;
   }

   .em-site-overview__directory-link:hover .em-site-overview__directory-arrow,
   .em-site-overview__directory-link:focus .em-site-overview__directory-arrow {
      transform: translateX(3px);
      color: #fff;
   }

   .em-site-overview__directory-link:hover .em-site-overview__directory-map-slot,
   .em-site-overview__directory-link:focus .em-site-overview__directory-map-slot {
      background: rgba(255, 255, 255, .34);
   }

   .em-site-overview__directory-link:hover .em-site-overview__directory-map-slot.is-current,
   .em-site-overview__directory-link:focus .em-site-overview__directory-map-slot.is-current {
      background: #fff;
   }

   .em-site-overview.is-focus-mode .em-site-cards {
      display: block;
      margin-top: 0;
   }

   .em-site-overview__summary .em-site-createtype {
      margin-top: 14px;
      border-radius: 18px;
      border-color: #4f080e;
      background: linear-gradient(180deg, #fffdfb 0%, #fff7f4 100%);
   }

   .em-site-collapse { background:#fff; border:1px solid #dcdcde; border-radius:6px; margin:0 0 10px; }
    /* Item repliÃ© : neutre (cf. .em-site-item plus bas). Item dÃ©pliÃ© : mis en
       avant avec la teinte de sa section Â« Apparence Â» (--em-site-item-bg posÃ©e
       en JS). --item-open-bg factorise un fond clair dÃ©rivÃ© via color-mix. */
   .em-site-item {
      border-color:#4f080e;
      border-radius:18px;
      background:linear-gradient(180deg, #ffffff 0%, #fffaf7 100%);
      box-shadow:0 14px 30px -24px rgba(94, 25, 37, .24), 0 2px 8px rgba(94, 25, 37, .05);
      transition:transform .24s cubic-bezier(.22, 1, .36, 1), border-color .22s ease, box-shadow .24s cubic-bezier(.22, 1, .36, 1), background-color .22s ease, color .18s ease;
   }
   .em-site-item:not([open]):hover,
   .em-site-item:not([open]):focus-within {
      transform:translateY(-4px) scale(1.012);
      border-color:#4f080e;
      background:#7d484d;
      box-shadow:0 26px 42px -28px rgba(94, 25, 37, .34), 0 10px 20px rgba(94, 25, 37, .08);
   }
    .em-site-item[open] {
        --item-open-bg: color-mix(in srgb, var(--em-site-item-bg, #2271b1) 7%, #fff);
        border-width:2px; border-color:var(--em-site-item-bg, #2271b1);
        background:var(--item-open-bg);
        box-shadow:0 6px 18px -10px color-mix(in srgb, var(--em-site-item-bg, #2271b1) 50%, transparent);
    }
   .em-site-item[open] > .em-site-collapse__summary {
      border-radius:18px 18px 0 0;
   }
    .em-site-collapse__summary { list-style:none; cursor:pointer; display:flex; align-items:center; gap:8px; padding:12px 14px; user-select:none; }
    .em-site-collapse__summary::-webkit-details-marker { display:none; }
    .em-site-collapse__chevron { width:0; height:0; border-left:6px solid #6b7280; border-top:5px solid transparent; border-bottom:5px solid transparent; transition:transform .15s ease; flex:0 0 auto; }
    .em-site-collapse[open] > .em-site-collapse__summary > .em-site-collapse__chevron { transform:rotate(90deg); }
    .em-site-collapse__summary code { background:#f0f0f1; padding:1px 6px; border-radius:3px; font-size:12px; }
    .em-site-collapse__body { padding:0 14px 14px; border-top:1px solid #f0f0f1; }

   .em-site-card { border-color:var(--em-site-overview-border); border-radius:18px; transition:border-color .18s ease, box-shadow .18s ease, background-color .18s ease, transform .18s ease; }
   .em-site-card > .em-site-card__head {
      font-size:15px;
      display:grid;
      grid-template-columns: 18px 10px 18px 18px minmax(100px, 135px) 22px 22px 46px minmax(260px, 340px) 24px;
      align-items:center;
      column-gap:10px;
      justify-content:start;
   }
    /* Rubrique fermÃ©e : lÃ©ger feedback au survol, reste neutre */
   .em-site-card:not([open]):hover { border-color:var(--em-site-overview-border-strong); }
    /* Rubrique ouverte : encadrement accentuÃ© (anneau couleur marque), fond
       lÃ©gÃ¨rement teintÃ© et ombre discrÃ¨te pour crÃ©er une hiÃ©rarchie visuelle.
       L'anneau via box-shadow Ã©vite tout dÃ©calage de mise en page. */
   .em-site-card[open] { border-color:var(--em-site-overview-accent-strong); background:var(--em-site-overview-surface); box-shadow:0 0 0 1px var(--em-site-overview-accent-strong), 0 14px 34px -22px rgba(78,8,14,.30); }
   .em-site-card[open] > .em-site-card__head { background:var(--em-site-overview-surface-soft); border-radius:18px 18px 0 0; }
   /* HiÃ©rarchie Rubriques : les rubriques standards sont dÃ©calÃ©es Ã  droite,
      TOP-BAR/FOOTER restent alignÃ©es Ã  gauche (rubriques spÃ©ciales). */
   .em-site-card { margin-left:14px; }
   .em-site-card--header-linked {
      margin-left:44px;
      border-left:3px solid #d7b2b7;
   }
   .em-site-card--header-linked > .em-site-card__head { padding-left:10px; }
   .em-site-card--fixed-single { margin-left:0; border-left:3px solid #751820; }
   .em-site-card--fixed-single > .em-site-card__head { padding-left:10px; }
   .em-site-item, .em-site-step, .em-site-create { background:#fffefd; }
    /* Ligne Â« Nouvelle Section Â» : 2 lignes empilÃ©es (crÃ©er / dupliquer) */
      .em-site-card__additem { display:inline-flex; align-items:center; justify-content:center; gap:6px; margin-left:0; width:100%; min-width:260px; max-width:340px; height:30px; padding:0 12px; border:1px dashed var(--em-site-overview-border-strong); border-radius:999px; background:#fff; color:var(--em-site-overview-accent-strong); cursor:pointer; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; line-height:1; }
      .em-site-card__additem:hover { border-color:var(--em-site-overview-accent); background:#fff7f4; color:var(--em-site-overview-accent); }
         .em-site-card__additem.is-active,
         .em-site-card__additem[aria-expanded="true"] { border-style:solid; border-color:var(--em-site-overview-accent-strong); background:var(--em-site-overview-accent-strong); color:#fff; box-shadow:0 0 0 1px var(--em-site-overview-accent-strong); }
      .em-site-card__additem .dashicons { font-size:14px; width:14px; height:14px; }
      .em-site-card__additem span:last-child { white-space:nowrap; }
      .em-site-card:not([open]) .em-site-card__additem { display:none; }
      .em-site-card.em-site-card--item-open .em-site-card__additem { display:none; }
      .em-site-create[hidden] { display:none; }
      .em-site-create:not([hidden]):not(.em-site-createtype),
      .em-site-create.em-site-createtype:not([hidden]) { border-color:var(--em-site-overview-accent-strong); box-shadow:0 0 0 1px var(--em-site-overview-accent-strong), 0 8px 20px -12px rgba(78,8,14,.28); background:#fff8f8; }
      .em-site-create:not([hidden]):not(.em-site-createtype) .em-site-collapse__body,
      .em-site-create.em-site-createtype:not([hidden]) .em-site-collapse__body { border-top-color:#ead6d8; }
    .em-site-create__options { display:flex; flex-direction:column; gap:12px; padding:14px; }
    .em-site-create__row { display:flex; flex-wrap:wrap; align-items:center; gap:10px; margin:0; }
    .em-site-create__row .button { display:inline-flex; align-items:center; gap:6px; height:34px; line-height:1; }
    .em-site-create__row .button .dashicons { font-size:16px; width:16px; height:16px; line-height:1; }
    .em-site-create__row input.regular-text, .em-site-create__select { height:34px; box-sizing:border-box; margin:0; }
    .em-site-create__name { min-width:240px; }
    .em-site-create__label { flex:0 0 220px; display:inline-flex; align-items:center; gap:6px; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:.04em; color:#1d2327; cursor:pointer; }
    .em-site-create__label .dashicons { font-size:16px; width:16px; height:16px; color:#2271b1; flex:0 0 auto; }
    .em-site-create__radio { margin:0 2px 0 0; flex:0 0 auto; }
    .em-site-create__select { max-width:100%; }
    .em-site-create__row.is-off { opacity:.5; }
    .em-site-create__row.is-off .em-site-create__label { cursor:pointer; }
    /* SÃ©lecteur d'icÃ´ne (Nouvelle Rubrique) */
    .em-site-iconpick { display:inline-flex; flex-wrap:wrap; gap:4px; align-items:center; }
    .em-site-iconpick__opt { position:relative; display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; border:1px solid #dcdcde; border-radius:5px; cursor:pointer; color:#50575e; background:#fff; }
    .em-site-iconpick__opt input { position:absolute; opacity:0; width:0; height:0; }
    .em-site-iconpick__opt:hover { border-color:#2271b1; color:#2271b1; }
    .em-site-iconpick__opt:has(input:checked) { border-color:#2271b1; background:#2271b1; color:#fff; box-shadow:0 0 0 1px #2271b1; }
    .em-site-iconpick__opt .dashicons { font-size:18px; width:18px; height:18px; }

    .em-site-badge { background:#111827; color:#fff; border-radius:3px; padding:1px 8px; font-size:11px; text-transform:uppercase; letter-spacing:.04em; }
    .em-site-badge--default { background:#2271b1; }
   .em-site-card__drag { grid-column:1; }
   .em-site-card > .em-site-card__head > .em-site-collapse__chevron { grid-column:2; }
    .em-site-card__icon { color:#4e080e; }
   .em-site-card__icon { grid-column:3; }
   .em-site-card__name { min-width:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
   .em-site-card__name { grid-column:5; }
   .em-site-card__nameinput { width:100%; max-width:none; text-transform:uppercase; font-weight:700; min-width:0; margin:0; }
   .em-site-card__nameinput { grid-column:5; }
   .em-site-card__count { display:inline-flex; align-items:center; justify-content:center; min-width:22px; height:22px; padding:0 8px; margin-left:0; justify-self:center; grid-column:8; background:#fff; color:var(--em-site-overview-accent-strong); border:1px solid var(--em-site-overview-border-strong); border-radius:999px; font-size:11px; font-weight:700; line-height:1; }
    /* RÃ©ordonnancement des rubriques (glisser-dÃ©poser) */
    .em-site-card__drag { color:#a7aaae; cursor:grab; font-size:18px; width:18px; height:18px; flex:0 0 auto; }
    .em-site-card__drag:hover { color:#646970; }
    .em-site-card__drag:active { cursor:grabbing; }
   .em-site-card--not-reorderable .em-site-card__drag { cursor:default; opacity:.35; }
   .em-site-card--not-reorderable .em-site-card__drag:hover { color:#a7aaae; }
    .em-site-card.is-dragging { opacity:.6; outline:2px dashed #2271b1; outline-offset:2px; }
    /* Renommage d'une rubrique (crayon + champ inline) */
   .em-site-card__edit { background:none; border:0; cursor:pointer; color:#2271b1; padding:0 2px; margin-left:0; display:inline-flex; align-items:center; justify-content:center; }
   .em-site-card__edit { grid-column:4; }
    .em-site-card__edit:hover { color:#135e96; }
    .em-site-card__edit .dashicons { font-size:16px; width:16px; height:16px; }
   .em-site-card__confirm { grid-column:6; margin-left:0; }
   .em-site-card__cancel { grid-column:7; margin-left:0; }
   .em-site-card__additem { grid-column:9; justify-self:start; }
   .em-site-card__delete { grid-column:10; justify-self:end; width:22px; height:22px; padding:0; border:0; background:transparent; color:#b32d2e; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; }
   .em-site-card__delete:hover { color:#8a2424; }
   .em-site-card__delete .dashicons { font-size:18px; width:18px; height:18px; }
   .em-site-card:not([open]) .em-site-card__delete { display:none; }

    .em-site-item__title { display:inline-flex; align-items:center; gap:6px; }
    .em-site-item__prefix, .em-site-item__name { text-transform:uppercase; font-weight:600; }
   .em-site-item:not([open]):hover .em-site-collapse__chevron,
   .em-site-item:not([open]):focus-within .em-site-collapse__chevron,
   .em-site-item:not([open]):hover .em-site-item__title,
   .em-site-item:not([open]):focus-within .em-site-item__title,
   .em-site-item:not([open]):hover .em-site-item__prefix,
   .em-site-item:not([open]):focus-within .em-site-item__prefix,
   .em-site-item:not([open]):hover .em-site-item__name,
   .em-site-item:not([open]):focus-within .em-site-item__name,
   .em-site-item:not([open]):hover .em-site-item__edit,
   .em-site-item:not([open]):focus-within .em-site-item__edit,
   .em-site-item:not([open]):hover .em-site-item__delete,
   .em-site-item:not([open]):focus-within .em-site-item__delete,
   .em-site-item:not([open]):hover .em-site-item__preview .dashicons,
   .em-site-item:not([open]):focus-within .em-site-item__preview .dashicons {
      color:#fff;
   }
   .em-site-item:not([open]):hover .em-site-item__anchor,
   .em-site-item:not([open]):focus-within .em-site-item__anchor,
   .em-site-item:not([open]):hover .em-site-item__slug,
   .em-site-item:not([open]):focus-within .em-site-item__slug {
      background:rgba(255,255,255,.07);
      border-color:rgba(255,255,255,.18);
      color:rgba(255,255,255,.86);
   }
   .em-site-item:not([open]):hover .em-site-item__anchor-hash,
   .em-site-item:not([open]):focus-within .em-site-item__anchor-hash,
   .em-site-item:not([open]):hover .em-site-item__slug-label,
   .em-site-item:not([open]):focus-within .em-site-item__slug-label,
   .em-site-item:not([open]):hover .em-site-item__slug-value,
   .em-site-item:not([open]):focus-within .em-site-item__slug-value,
   .em-site-item:not([open]):hover .em-site-item__anchor input.em-site-item__anchorinput,
   .em-site-item:not([open]):focus-within .em-site-item__anchor input.em-site-item__anchorinput {
      color:rgba(255,255,255,.86);
   }
    .em-site-item__edit { background:none; border:0; cursor:pointer; color:#2271b1; padding:0 2px; display:inline-flex; align-items:center; }
    .em-site-item__edit:hover { color:#135e96; }
    .em-site-item__edit .dashicons { font-size:18px; width:18px; height:18px; }
    .em-site-item__delete { background:none !important; border:0 !important; box-shadow:none !important; outline:0; cursor:pointer; color:#b32d2e; padding:0 2px; margin-left:6px; display:inline-flex; align-items:center; }
    .em-site-item__delete:hover, .em-site-item__delete:focus, .em-site-item__delete:active { background:none !important; box-shadow:none !important; color:#8a2424; }
    .em-site-item__delete .dashicons { font-size:18px; width:18px; height:18px; }
    /* Champ #ancre : mini chip inline unifiÃ©. Tout le rendu visuel (fond,
       bordure, focus) est portÃ© par le wrapper ; l'input interne est rendu
       totalement neutre/transparent. SpÃ©cificitÃ© renforcÃ©e pour neutraliser le
       style natif des inputs admin WordPress (.wp-core-ui input[type=text]). */
    .em-site-item__anchor { display:inline-flex; align-items:center; gap:4px; margin-left:8px; padding:0 9px; height:24px; line-height:1; background:#eef1f4; border:1px solid transparent; border-radius:7px; transition:background-color .15s ease, border-color .15s ease, box-shadow .15s ease; }
    .em-site-item__anchor:hover { background:#e7ebef; }
    .em-site-item__anchor:focus-within { background:#fff; border-color:#c7ced6; box-shadow:0 0 0 1px rgba(120,134,150,.25); }
    .em-site-item__anchor-hash { color:#9aa4b1; font-weight:600; font-size:12px; line-height:1; flex:0 0 auto; }
    .em-site-item__anchor input.em-site-item__anchorinput,
    .em-site-item__anchor input.em-site-item__anchorinput:focus,
    .em-site-item__anchor input.em-site-item__anchorinput:hover {
        width:92px; height:22px; min-height:0; margin:0; padding:0; box-sizing:border-box;
        font-size:12px; line-height:22px; color:#1d2327;
        border:0; border-radius:0; background:transparent; outline:0; box-shadow:none; appearance:none;
    }
    .em-site-item__anchorinput::placeholder { color:#aeb6bf; opacity:1; }
   .em-site-item__slug { display:inline-flex; align-items:center; gap:5px; margin-left:6px; padding:0 8px; height:24px; border:1px solid #e5e8ec; border-radius:7px; background:#f6f8fa; color:#7c8794; font-size:11px; line-height:1; }
   .em-site-item__slug-label { text-transform:uppercase; letter-spacing:.04em; font-weight:600; opacity:.8; }
   .em-site-item__slug-value { color:#66717e; font-family:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; font-size:11px; }
    .em-site-item__nameinput { text-transform:uppercase; font-weight:600; min-width:200px; }
    /* Boutons valider / annuler du renommage inline (items + rubriques) */
    .em-site-item__confirm, .em-site-item__cancel, .em-site-card__confirm, .em-site-card__cancel { background:#fff; border:1px solid #c3c4c7; border-radius:4px; cursor:pointer; padding:2px 4px; margin-left:4px; display:inline-flex; align-items:center; line-height:1; }
    .em-site-item__confirm, .em-site-card__confirm { color:#1d7b34; }
    .em-site-item__confirm:hover, .em-site-card__confirm:hover { border-color:#1d7b34; background:#f0faf2; }
    .em-site-item__cancel, .em-site-card__cancel { color:#646970; }
    .em-site-item__cancel:hover, .em-site-card__cancel:hover { border-color:#b32d2e; color:#b32d2e; background:#fcf0f0; }
    .em-site-item__confirm .dashicons, .em-site-item__cancel .dashicons, .em-site-card__confirm .dashicons, .em-site-card__cancel .dashicons { font-size:16px; width:16px; height:16px; }
    .em-site-item__confirm[hidden], .em-site-item__cancel[hidden], .em-site-card__confirm[hidden], .em-site-card__cancel[hidden] { display:none !important; }

    /* Apparence (couleurs globales) */
    .em-site-appearance { display:flex; flex-direction:column; gap:10px; margin:0 0 14px; padding:10px 12px; background:#fff; border:1px solid #dcdcde; border-radius:6px; }
    .em-site-builder__section > .em-site-collapse__summary strong { font-size:13px; text-transform:uppercase; letter-spacing:.04em; }
    .em-site-collapse__body .em-site-appearance { border:0; background:transparent; padding:0; margin:0 0 10px; border-radius:0; }

    /* Bloc interne ouvert (Apparence / Contenu / Ligne N) : encadrement net pour
       repÃ©rer la partie de l'item en cours d'Ã©dition. On reprend la teinte de
       l'item (--em-site-item-bg) pour rester cohÃ©rent ; le corps reste blanc afin
       de ressortir sur le fond teintÃ© de l'item ouvert. Bloc fermÃ© = neutre. */
    .em-site-builder__section, .em-site-row { transition:border-color .18s ease, box-shadow .18s ease, background-color .18s ease; }
    .em-site-builder__section[open], .em-site-row[open] {
        border-color:color-mix(in srgb, var(--em-site-item-bg, #2271b1) 60%, #c3c4c7);
        box-shadow:0 0 0 1px color-mix(in srgb, var(--em-site-item-bg, #2271b1) 32%, transparent),
                   0 6px 16px -10px color-mix(in srgb, var(--em-site-item-bg, #2271b1) 45%, transparent);
    }
    .em-site-builder__section[open] > .em-site-collapse__summary,
    .em-site-row[open] > .em-site-row__summary {
        background:color-mix(in srgb, var(--em-site-item-bg, #2271b1) 10%, #fff);
        border-radius:6px 6px 0 0;
    }
    .em-site-appearance__line { display:flex; flex-wrap:wrap; gap:18px; align-items:center; }
    .em-site-appearance__title { font-size:11px; text-transform:uppercase; letter-spacing:.04em; color:#6b7280; min-width:90px; }
    .em-site-appearance__item { display:flex; align-items:center; gap:10px; }
    .em-site-appearance__label { font-size:13px; color:#374151; }
    .em-site-appearance__toggle, .em-site-appearance__num, .em-site-appearance__font { display:flex; align-items:center; gap:6px; }
    .em-site-appearance__num-input { width:72px; }
    /* Pastilles de couleur carrÃ©es (au lieu de rondes) dans l'Apparence */
    .em-site-appearance .em-site-admin-color-trigger__swatch { border-radius:4px; }
    /* Espacements liables (haut/bas, gauche/droite) */
    .em-site-appearance__group { display:inline-flex; align-items:center; gap:8px; }
    .em-site-appearance__chain { display:inline-flex; align-items:center; justify-content:center; width:26px; height:26px; padding:0; border:1px solid #dcdcde; border-radius:5px; background:#fff; color:#6b7280; cursor:pointer; }
    .em-site-appearance__chain:hover { border-color:#2271b1; color:#2271b1; }
    .em-site-appearance__chain[aria-pressed="true"] { border-color:#2271b1; background:#2271b1; color:#fff; }
    .em-site-appearance__chain .dashicons { font-size:16px; width:16px; height:16px; }
    .em-site-appearance__font-input { max-width:220px; }
    .em-site-appearance__bgpos-input { max-width:220px; }
    /* Image de fond (sÃ©lecteur mÃ©dia) */
    .em-site-appearance__bgmedia { display:inline-flex; align-items:center; gap:8px; }
    .em-site-appearance__bgthumb { width:46px; height:32px; object-fit:cover; border-radius:4px; border:1px solid #dcdcde; background:#f0f0f1; }
    .em-site-appearance__bgclear { display:inline-flex; align-items:center; justify-content:center; width:22px; height:22px; padding:0; border:0; background:transparent; color:#b32d2e; font-size:18px; line-height:1; cursor:pointer; border-radius:4px; }
    .em-site-appearance__bgclear:hover { background:#fbeaea; }
    .em-site-appearance__bgopacity-input { width:90px; }
    .em-site-appearance__bgopacity-out { font-size:12px; color:#6b7280; min-width:34px; }

    /* En-tÃªte de ligne : colonnes + alignement par colonne */
    /* Colonnes + alignement : visibles uniquement quand la ligne est ouverte, Ã  cÃ´tÃ© du libellÃ©. */
   .em-site-row__layout { display:flex; flex-wrap:nowrap; align-items:center; gap:8px; margin-left:14px; }
   .em-site-row:not([open]) > .em-site-row__summary .em-site-row__layout { display:none; }
   .em-site-rowcols-label { display:inline-flex; flex-direction:row; align-items:center; gap:6px; font-size:11px; color:#6b7280; }
    .em-site-rowcols { height:28px; min-height:28px; line-height:1; padding:0 18px 0 4px; margin:0; font-size:12px; vertical-align:middle; border:0; box-shadow:none; background-color:transparent; }
    .em-site-rowcols:focus { border:0; box-shadow:none; outline:none; }
    .em-site-align { display:flex; flex-direction:row; align-items:center; }
    .em-site-align__label { display:none; }
    .em-site-align__group { display:inline-flex; border:1px solid #c3c4c7; border-radius:5px; overflow:hidden; background:#fff; }
    /* RepliÃ© : on n'affiche que l'alignement choisi ; ouvert : les 4 options. */
    .em-site-align__group:not(.is-open) .em-site-align__btn:not(.is-active) { display:none; }
    .em-site-align__group:not(.is-open) .em-site-align__btn.is-active { border-left:0; }
    .em-site-align__btn { display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; padding:0; border:0; border-left:1px solid #e2e4e7; background:#fff; color:#50575e; cursor:pointer; }
    .em-site-align__btn:first-of-type { border-left:0; }
    .em-site-align__btn:hover { background:#f0f0f1; color:#1d2327; }
    .em-site-align__btn.is-active { background:#2271b1; color:#fff; }
    /* Onglet repliÃ© : l'icÃ´ne d'alignement affichÃ©e est en noir (pas en bleu). */
    .em-site-align__group:not(.is-open) .em-site-align__btn.is-active { background:#fff; color:#1d2327; }
    .em-site-align__btn .dashicons { font-size:18px; width:18px; height:18px; }

    /* Mini-carte de la grille (Ã  cÃ´tÃ© de Â« Contenu Â») â€” large & plate (â‰ˆ section front) */
    .em-site-gridmap { display:inline-flex; flex-direction:column; gap:3px; margin-left:12px; padding:4px; width:142px; box-sizing:border-box; border:1px solid #c3c4c7; border-radius:5px; background:#f6f7f8; vertical-align:middle; }
    .em-site-gridmap:empty { display:none; }
    .em-site-gridmap__row { display:flex; gap:3px; }
    .em-site-gridmap__cell { flex:1 1 0; min-width:0; height:9px; border-radius:2px; background:#aab2bd; cursor:pointer; transition:background .12s ease; }
    .em-site-gridmap__cell:hover { background:#7b8593; }
    .em-site-gridmap__cell.is-active { background:#155a9c; }
    /* Å’il Â« toute la section Â» Ã  droite de la carte */
    .em-site-gridmap__eye { margin-left:8px; padding:0 2px; border:0; background:transparent; color:#2271b1; cursor:pointer; display:inline-flex; align-items:center; vertical-align:middle; }
    .em-site-gridmap__eye:hover { color:#135e96; }
    .em-site-gridmap__eye[aria-pressed="true"] { color:#135e96; }
    .em-site-gridmap__eye .dashicons { font-size:18px; width:18px; height:18px; }

    /* Bulle d'aperÃ§u d'une cellule de la carte (survol) */
    .em-site-gridmap__pop { position:absolute; z-index:100000; display:none; max-width:380px; min-width:180px; padding:8px; background:#fff; border:1px solid #c3c4c7; border-radius:8px; box-shadow:0 8px 28px rgba(16,24,40,.20); pointer-events:none; }
   .em-site-gridmap__pop-title { margin:0 0 6px; padding:0 2px; font-size:11px; line-height:1.25; color:#111; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .em-site-gridmap__pop .em-rubrique--preview { padding:0; font-size:12px; }

    /* Vignette d'aperÃ§u RÃ‰DUIT, INTÃ‰GRÃ‰E Ã  la ligne Â« Contenu Â» (Ã  droite de l'Å“il), temps rÃ©el */
    .em-site-miniprev { display:inline-block; margin-left:8px; vertical-align:middle; background:#fff; border:1px solid #c3c4c7; border-radius:6px; box-shadow:0 1px 4px rgba(16,24,40,.12); overflow:hidden; }
    .em-site-miniprev[hidden] { display:none; }
   .em-site-miniprev { cursor:default; }
    /* AperÃ§u de la PARTIE en Ã©dition (colonne de la ligne ouverte), Ã  droite du
       total : mÃªme gabarit, bordure pointillÃ©e pour signaler le focus colonne. */
    .em-site-partprev { border-style:dashed; border-color:#b9c0c9; }
    /* Loupe : popover flottant agrandissant l'aperÃ§u survolÃ© (total ou partie). */
    .em-site-miniprev__zoom { position:absolute; z-index:100001; display:none; overflow:hidden; background:#fff; border:1px solid #c3c4c7; border-radius:8px; box-shadow:0 12px 34px rgba(16,24,40,.26); pointer-events:none; }
    .em-site-miniprev__stage { overflow:hidden; width:100%; height:100%; }
    .em-site-miniprev__stage .em-site-livepreview { border:0; margin:0; }
    .em-site-gridmap__pop-empty { font-size:12px; color:#6b7280; padding:4px 6px; font-style:italic; }
    .em-rubrique .em-site-gridmap__pop-empty { color:inherit; opacity:.7; text-align:center; }

    /* Item Â« MasquÃ© Â» signalÃ© dans l'aperÃ§u (au lieu d'Ãªtre omis) */
    .em-rubrique__masked { display:inline-flex; align-items:center; gap:4px; padding:2px 8px; margin:2px; border:1px dashed currentColor; border-radius:999px; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.04em; opacity:.55; }
    .em-rubrique__masked .dashicons { font-size:14px; width:14px; height:14px; }

    /* Builder : lignes Ã— colonnes */
    .em-site-rows { counter-reset: emrow; display:flex; flex-direction:column; gap:10px; margin:8px 0; }
    .em-site-row { counter-increment: emrow; background:#fff; border:1px solid #dcdcde; border-radius:6px; }
   .em-site-row__summary { list-style:none; cursor:pointer; display:grid; grid-template-columns:auto auto minmax(230px, 320px) minmax(420px, 1fr) auto auto; align-items:center; column-gap:8px; padding:8px 12px; user-select:none; }
    .em-site-row__summary::-webkit-details-marker { display:none; }
    .em-site-row[open] > .em-site-row__summary { border-bottom:1px solid #f0f0f1; }
    .em-site-row[open] > .em-site-row__summary > .em-site-collapse__chevron { transform:rotate(90deg); }
    .em-site-row__label { font-size:11px; text-transform:uppercase; letter-spacing:.03em; color:#6b7280; }
   .em-site-row__label::before { content:"L" counter(emrow); }
   .em-site-row__left { display:inline-flex; align-items:center; min-width:0; }
   .em-site-row__right { display:inline-flex; align-items:center; min-width:0; }
   .em-site-row__title { display:inline-flex; align-items:center; gap:4px; margin-left:4px; padding:0 6px; height:24px; line-height:1; background:#eef1f4; border:1px solid transparent; border-radius:7px; transition:background-color .15s ease, border-color .15s ease, box-shadow .15s ease; }
   .em-site-row__title:hover { background:#e7ebef; }
   .em-site-row__title:focus-within { background:#fff; border-color:#c7ced6; box-shadow:0 0 0 1px rgba(120,134,150,.25); }
   .em-site-row__titleprefix { color:#9aa4b1; font-weight:700; font-size:12px; line-height:1; flex:0 0 auto; }
   .em-site-row__titletxt { max-width:150px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-size:12px; color:#1d2327; }
   .em-site-row__titletxt[data-empty="1"] { color:#9aa4b1; }
   .em-site-row__titleedit { width:18px; height:18px; padding:0; border:0; background:transparent; color:#7b8796; border-radius:4px; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; }
   .em-site-row__titleedit:hover { color:#2271b1; background:#eef2f7; }
   .em-site-row__titleedit .dashicons { width:14px; height:14px; font-size:14px; line-height:14px; }
   .em-site-row__title[data-editing="1"] .em-site-row__titletxt { display:none; }
   .em-site-row__title[data-editing="1"] .em-site-row__titleedit { display:none; }
   .em-site-row__titleinput,
   .em-site-row__titleinput:focus,
   .em-site-row__titleinput:hover { width:120px; height:22px; min-height:0; margin:0; padding:0; box-sizing:border-box; font-size:12px; line-height:22px; color:#1d2327; border:0; border-radius:0; background:transparent; outline:0; box-shadow:none; appearance:none; }
   .em-site-row__titleinput::placeholder { color:#aeb6bf; opacity:1; }
   .em-site-row__colcount { display:inline-flex; align-items:center; gap:2px; margin-left:0; font-size:11px; color:#8a929e; font-variant-numeric:tabular-nums; }
   .em-site-row__colsnum { white-space:nowrap; }
   .em-site-row__colnames { display:inline-flex; align-items:center; gap:0; margin-left:8px; min-width:0; flex-wrap:wrap; }
   .em-site-row__colname { border:0; background:transparent; color:#4b5563; font-size:11px; line-height:1.2; cursor:pointer; padding:0 2px; border-radius:3px; max-width:none; white-space:normal; overflow:visible; text-overflow:clip; font-weight:400; }
   .em-site-row__colname + .em-site-row__colname::before { content:"+"; color:#9aa4b1; margin-right:5px; }
   .em-site-row__colname:hover { color:#2271b1; background:#eef2f7; }
   .em-site-row__colname.is-active { color:#111; font-weight:600; }
    .em-site-row__colcount .dashicons { font-size:15px; width:15px; height:15px; }
    .em-site-row[open] > .em-site-row__summary > .em-site-row__colcount { display:none; }
    .em-site-row__drag { cursor:grab; color:#aab1bd; flex:0 0 auto; }
    .em-site-row__drag:hover { color:#1d2327; }
    .em-site-row.is-dragging { opacity:.5; outline:2px dashed #2271b1; }
   .em-site-row__add { margin-left:0; border:0; background:transparent; color:#2271b1; cursor:pointer; padding:0; border-radius:4px; display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; align-self:center; }
    .em-site-row__add:hover { background:#eef2f7; }
    .em-site-row__add .dashicons { font-size:18px; width:18px; height:18px; }
    .em-site-row__remove { border:0; background:transparent; color:#b32d2e; font-size:16px; line-height:1; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; width:24px; height:28px; align-self:center; }
    /* Corps de ligne : onglets de colonnes + panneau actif en pleine largeur. */
    .em-site-row__body { padding:10px 12px; }
    .em-site-col-tabs { display:flex; flex-wrap:wrap; align-items:flex-end; gap:4px; border-bottom:1px solid #e2e4e7; margin-bottom:10px; }
    .em-site-col-tab { display:inline-flex; align-items:center; gap:6px; padding:5px 8px; border:1px solid transparent; border-bottom:0; border-radius:6px 6px 0 0; background:transparent; cursor:pointer; margin-bottom:-1px; }
    .em-site-col-tab:not(.is-active):hover { background:#f0f0f1; }
    .em-site-col-tab.is-active { background:#fff; border-color:#dcdcde; box-shadow:inset 0 3px 0 #751820; }
    .em-site-col-tab__name { font-size:11px; text-transform:uppercase; letter-spacing:.04em; color:#6b7280; white-space:nowrap; }
    .em-site-col-tab.is-active .em-site-col-tab__name { color:#751820; font-weight:700; }
   .em-site-col-tab[data-editing="1"] .em-site-col-tab__name { display:none; }
   .em-site-col-tab[data-editing="1"] .em-site-row__titleedit { display:none; }
    .em-site-col-tab .em-site-align { margin:0; }
    .em-site-col-tab__del { border:0; background:transparent; color:#b32d2e; font-size:15px; line-height:1; cursor:pointer; padding:0 2px; border-radius:3px; display:inline-flex; align-items:center; }
    .em-site-col-tab__del:hover { background:#fbeaea; }
    .em-site-col-tab__move-group { display:inline-flex; align-items:center; gap:1px; }
    .em-site-col-tab__move { border:0; background:transparent; color:#646970; cursor:pointer; padding:1px; border-radius:3px; display:inline-flex; align-items:center; justify-content:center; }
    .em-site-col-tab__move .dashicons { font-size:14px; width:14px; height:14px; line-height:14px; }
    .em-site-col-tab__move:hover:not(:disabled) { background:#eef0f2; color:#751820; }
    .em-site-col-tab__move:disabled { opacity:.3; cursor:default; }
    /* Une seule colonne : dÃ©placement inutile. */
    .em-site-col-tab:first-of-type:last-of-type .em-site-col-tab__move-group { display:none; }
    /* Une seule colonne : pas de croix (on ne peut pas supprimer la derniÃ¨re). */
    .em-site-col-tab:first-of-type:last-of-type .em-site-col-tab__del { display:none; }
    .em-site-col-tab__add { align-self:center; display:inline-flex; align-items:center; gap:4px; border:1px dashed #c3c4c7; background:#fff; color:#2271b1; height:28px; padding:0 10px; border-radius:5px; cursor:pointer; font-size:12px; margin-bottom:3px; flex:0 0 auto; white-space:nowrap; }
    .em-site-col-tab__add .dashicons { font-size:16px; width:16px; height:16px; }
    .em-site-col-tab__add:hover { background:#f0f6fc; border-color:#2271b1; }
    /* Pictos de colonnes : un picto = une colonne (ligne ouverte et fermÃ©e). */
    .em-site-colpips { display:inline-flex; align-items:center; gap:3px; }
    .em-site-colpip { width:4px; height:12px; background:#8a929e; border-radius:1px; }
    .em-site-col-panels { min-width:0; }
    .em-site-col { min-width:0; }
    .em-site-col:not(.is-active) { display:none; }
    .em-site-col.is-active { display:flex; flex-direction:column; }
    .em-site-col__drop { min-height:40px; padding:0 0 8px; display:flex; flex-direction:column; gap:6px; }

    .em-site-chip { display:flex; align-items:center; gap:8px; background:#fff; border:1px solid #dcdcde; border-radius:7px; padding:8px 9px; box-shadow:0 1px 2px rgba(16,24,40,.04); }
    .em-site-chip.is-dragging { opacity:.5; border-style:dashed; }
    .em-site-chip--decor { align-items:center; }
    .em-site-chip--decor .em-site-chip__url { flex:1 1 auto; min-width:0; }
    .em-site-chip__drag { cursor:grab; color:#aab1bd; flex:0 0 auto; }
    .em-site-chip__type { display:inline-flex; align-items:center; gap:4px; font-size:9px; font-weight:600; text-transform:uppercase; letter-spacing:.04em; color:#475569; background:#eef2f7; padding:3px 6px; border-radius:4px; flex:0 0 auto; }
    .em-site-chip__typeicon { font-size:13px; width:13px; height:13px; line-height:13px; color:#2271b1; }
    .em-site-chip__media--av { display:inline-flex; align-items:center; gap:6px; flex-wrap:wrap; }
    .em-site-chip__medianame { font-size:11px; color:#50575e; max-width:160px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .em-site-chip__slider { display:flex; align-items:center; gap:6px; flex-wrap:wrap; }
    .em-site-chip__slides { display:flex; gap:4px; flex-wrap:wrap; }
    .em-site-chip__slide { position:relative; width:40px; height:40px; border-radius:4px; overflow:hidden; border:1px solid #dcdcde; }
    .em-site-chip__slide img { width:100%; height:100%; object-fit:cover; display:block; }
    .em-site-chip__slide-del { position:absolute; top:0; right:0; border:0; background:rgba(0,0,0,.6); color:#fff; cursor:pointer; line-height:1; padding:0 3px; border-bottom-left-radius:4px; }
    /* Ã‰diteur de slides riche (champ Slider EM-SITE) : prend toute la largeur de la chip. */
    .em-site-slides { flex:1 1 100%; min-width:0; display:flex; flex-direction:column; gap:8px; background:#f8fafc; border:1px solid #eef0f3; border-radius:6px; padding:8px; }
   .em-site-slides__section-title { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#6b7280; }
   .em-site-slides__opts { display:flex; flex-wrap:wrap; align-items:center; gap:10px 14px; }
   .em-site-slides__opts--row1 { flex-wrap:nowrap; align-items:flex-end; padding-bottom:6px; border-bottom:1px solid #e9edf2; overflow-x:auto; }
   .em-site-slides__opts--row2 { padding-top:2px; align-items:center; }
   .em-site-slides__titlegroup { display:flex; flex:0 0 320px; min-width:0; align-items:center; gap:12px; flex-wrap:nowrap; }
   .em-site-slides__group-label { display:inline-flex; align-items:center; min-height:28px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#6b7280; }
    .em-site-slides__opt { display:inline-flex; align-items:center; gap:6px; font-size:11px; color:#475569; }
    .em-site-slides__opt > span { font-weight:600; }
    .em-site-slides__opt--check { gap:4px; }
   .em-site-slides__opt--title { flex:0 1 190px; min-width:180px; display:flex; flex-direction:column; align-items:flex-start; gap:4px; }
   .em-site-slides__opt--title > span { font-size:13px; font-weight:600; text-transform:none; letter-spacing:0; color:#1d2327; }
   .em-site-slides__title { min-height:28px; width:190px; max-width:190px; min-width:0; user-select:text; -webkit-user-select:text; }
   .em-site-slides__opts--row1 .em-site-slides__opt--check { white-space:nowrap; flex:0 0 auto; align-self:flex-end; padding-bottom:1px; }
   .em-site-slides__opts--row1 .em-site-slides__colorfield { flex:0 0 auto; }
   .em-site-slides__opts--row1 .em-site-slides__opt--check-tapes { margin-left:4px; }
   .em-site-scotchs-control__check { display:inline-flex; align-items:center; gap:4px; white-space:nowrap; flex:0 0 auto; align-self:flex-end; padding-bottom:1px; }
   .em-site-slides__slides-label { margin-top:2px; }
   .em-site-slides__opts--row2 .em-site-admin-color-field { flex:0 0 auto; }
   .em-site-slides__colorfield { margin:0; }
   .em-site-slides__colorfield .em-site-admin-color-field-row__label { min-width:0; }
   .em-site-slides__colorfield .em-site-admin-color-trigger { min-height:28px; }
   .em-site-slides__colorfield .em-site-admin-color-trigger__swatch { border-radius:4px; }
   .em-site-scotchs-control__color { margin:0; flex:0 0 auto; }
   .em-site-scotchs-control__color .em-site-admin-color-field-row__label { min-width:0; }
   .em-site-scotchs-control__color .em-site-admin-color-trigger { min-height:28px; }
   .em-site-scotchs-control__color .em-site-admin-color-trigger__swatch { border-radius:4px; }
    .em-site-slides__list { display:flex; flex-direction:column; gap:6px; }
    .em-site-slide { display:flex; align-items:center; gap:6px; flex-wrap:wrap; background:#fff; border:1px solid #e3e7ec; border-radius:6px; padding:6px 8px; }
    .em-site-slide.is-hidden { opacity:.55; }
    .em-site-slide__move { display:inline-flex; flex-direction:column; gap:1px; flex:0 0 auto; }
    .em-site-slide__move button { border:0; background:#eef2f7; color:#475569; cursor:pointer; line-height:1; padding:1px 5px; border-radius:3px; font-size:9px; }
    .em-site-slide__move button:hover { background:#dde6f1; }
    .em-site-slide__type { flex:0 0 auto; min-height:28px; }
      .em-site-slide__name { flex:1 1 180px; min-width:180px; min-height:28px; }
    .em-site-slide__videourl, .em-site-slide__tiktokurl { flex:0 1 200px; min-width:120px; min-height:28px; }
    .em-site-slide__duration { width:56px; min-height:28px; }
    .em-site-slide__media { display:inline-flex; align-items:center; gap:6px; flex:0 0 auto; }
    .em-site-slide__thumb { width:40px; height:40px; object-fit:cover; border-radius:4px; border:1px solid #dcdcde; background:#f0f0f1; }
    .em-site-slide__eye, .em-site-slide__del { border:0; background:none; cursor:pointer; line-height:1; flex:0 0 auto; }
    .em-site-slide__eye { color:#475569; }
    .em-site-slide__del { color:#b32d2e; font-size:18px; }
    /* Affichage conditionnel des contrÃ´les selon le type de slide. */
    .em-site-slide .em-site-slide__videourl, .em-site-slide .em-site-slide__tiktokurl, .em-site-slide .em-site-slide__media--ttvid { display:none; }
    .em-site-slide[data-type="image"] .em-site-slide__media--image { display:inline-flex; }
    .em-site-slide[data-type="video"] .em-site-slide__media--image { display:none; }
    .em-site-slide[data-type="video"] .em-site-slide__videourl { display:inline-flex; }
    .em-site-slide[data-type="tiktok"] .em-site-slide__tiktokurl, .em-site-slide[data-type="tiktok"] .em-site-slide__media--ttvid, .em-site-slide[data-type="tiktok"] .em-site-slide__media--image { display:inline-flex; }
    .em-site-slides__add { align-self:flex-start; }
    /* AperÃ§u temps rÃ©el du slider : placeholder de slide sans mÃ©dia (le reste du
       look vient de la CSS front partagÃ©e chargÃ©e sur la page builder). */
    .em-site-livepreview .em-slider--shared, .em-site-miniprev .em-slider--shared { margin:0 auto; }
    .em-slider--shared .em-slider__ph { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; color:#fff; font-size:22px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; opacity:.8; }
    /* Tous les champs du contenu sur UNE seule ligne (saisie + lien + style).
       Les saisies ne s'Ã©tirent pas (flex-grow:0) : largeurs compactes, le vide
       restant pousse les actions (Å“il/croix) Ã  droite. */
    .em-site-chip__fields { display:flex; flex-direction:row; flex-wrap:wrap; align-items:center; gap:6px; flex:1 1 auto; min-width:0; }
    .em-site-chip__fields > .em-site-chip__label { font-size:11px; font-weight:600; color:#475569; background:#f8fafc; flex:0 1 190px; min-width:70px; width:auto; }
    .em-site-chip__fields .em-site-chip__value, .em-site-chip__fields .em-site-chip__titext, .em-site-chip__fields .em-site-chip__titext2 { font-weight:500; flex:0 1 190px; min-width:60px; width:auto; }
    .em-site-chip__fields .em-site-chip__tlink { flex:0 1 120px; min-width:60px; width:auto; }
    .em-site-chip__fields input[type="text"], .em-site-chip__fields input[type="url"], .em-site-chip__fields input[type="number"], .em-site-chip__fields select { min-height:28px; }
   .em-site-chip__rich { display:flex; flex-direction:column; gap:6px; flex:1 1 100%; min-width:220px; }
   .em-site-chip__richbar { display:flex; align-items:center; gap:4px; flex-wrap:wrap; }
   .em-site-chip__richbar .em-site-richbtn { min-width:30px; padding:0 6px; font-weight:600; display:inline-flex; align-items:center; justify-content:center; color:#111 !important; }
   .em-site-chip__richbar .em-site-richbtn[data-cmd="justifyLeft"],
   .em-site-chip__richbar .em-site-richbtn[data-cmd="justifyCenter"],
   .em-site-chip__richbar .em-site-richbtn[data-cmd="justifyRight"],
   .em-site-chip__richbar .em-site-richbtn[data-cmd="justifyFull"] { min-width:28px; font-size:11px; }
   .em-site-chip__richbar .em-site-richbtn[data-cmd="justifyLeft"] .dashicons,
   .em-site-chip__richbar .em-site-richbtn[data-cmd="justifyCenter"] .dashicons,
   .em-site-chip__richbar .em-site-richbtn[data-cmd="justifyRight"] .dashicons,
   .em-site-chip__richbar .em-site-richbtn[data-cmd="justifyFull"] .dashicons { width:14px; height:14px; font-size:14px; line-height:14px; color:#111; }
   .em-site-chip__richbar .em-site-richbtn[data-action="link"] .dashicons,
   .em-site-chip__richbar .em-site-richbtn[data-cmd="unlink"] .dashicons { width:14px; height:14px; font-size:14px; line-height:14px; color:#111; }
   .em-site-chip__richcolor { display:inline-flex; align-items:center; }
   .em-site-chip__richcolor .em-site-admin-color-field-row { margin:0; }
   .em-site-chip__richcolor .em-site-admin-color-trigger { min-height:28px; }
   .em-site-chip__richedit { min-height:90px; max-height:260px; overflow:auto; border:1px solid #d0d4dc; border-radius:6px; background:#fff; padding:8px; line-height:1.45; user-select:text; -webkit-user-select:text; cursor:text; }
   .em-site-chip__richedit:focus { outline:2px solid #2271b1; outline-offset:0; border-color:#2271b1; }
   .em-site-chip__richedit:empty::before { content:attr(data-placeholder); color:#8c8f94; }
   .em-site-chip__richedit b, .em-site-chip__richedit strong { font-weight:700 !important; }
   .em-site-chip__richedit i, .em-site-chip__richedit em { font-style:italic !important; }
   .em-site-chip__richedit a, .em-site-chip__richedit a:visited, .em-site-chip__richedit a:hover, .em-site-chip__richedit a:focus { color:inherit !important; text-decoration:none !important; }
   .em-site-richdialog { position:fixed; inset:0; z-index:100000; display:flex; align-items:center; justify-content:center; }
   .em-site-richdialog[hidden] { display:none; }
   .em-site-richdialog__backdrop { position:absolute; inset:0; background:rgba(18,23,30,.55); }
   .em-site-richdialog__panel { position:relative; width:min(460px, calc(100vw - 32px)); background:#fff; border:1px solid #d0d4dc; border-radius:10px; box-shadow:0 18px 48px rgba(0,0,0,.24); padding:16px; display:flex; flex-direction:column; gap:8px; }
   .em-site-richdialog__title { margin:0; font-size:15px; line-height:1.3; color:#1d2327; }
   .em-site-richdialog__label { font-size:12px; color:#4b5563; }
   .em-site-richdialog__input { min-height:36px; border:1px solid #c3c4c7; border-radius:6px; padding:0 10px; font-size:13px; }
   .em-site-richdialog__actions { display:flex; justify-content:flex-end; gap:8px; margin-top:4px; }
   .em-site-chip__talign { width:150px; max-width:180px; font-size:12px; flex:0 0 auto; }
    .em-site-chip__media { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
    .em-site-chip__focal { position:relative; display:inline-block; line-height:0; cursor:crosshair; }
    .em-site-chip__thumb { width:64px; height:48px; object-fit:contain; background:#f0f0f1; border-radius:4px; border:1px solid #dcdcde; }
    .em-site-chip__focaldot { position:absolute; width:10px; height:10px; margin:-5px 0 0 -5px; border:2px solid #fff; border-radius:50%; background:#2271b1; box-shadow:0 0 0 1px #2271b1; pointer-events:none; }
    .em-site-chip__pick { flex:0 0 auto; }
    .em-site-chip__size { display:flex; align-items:center; gap:12px; flex-wrap:wrap; padding:6px 8px; background:#f8fafc; border:1px solid #eef0f3; border-radius:6px; }
    .em-site-chip__sizelabel { display:inline-flex; align-items:center; gap:6px; font-size:11px; color:#6b7280; }
    .em-site-chip__sizelabel input[type="range"] { width:120px; }
    .em-site-chip__wout { min-width:38px; font-variant-numeric:tabular-nums; color:#1d2327; }
    .em-site-chip__h { width:96px; }
    .em-site-chip__height { width:120px; }

    /* RÃ©glages de style propres au champ texte : groupe compact, sur la mÃªme ligne. */
    .em-site-chip__tstyle { display:inline-flex; align-items:center; gap:6px; flex-wrap:nowrap; flex:0 0 auto; padding:4px 6px; background:#f8fafc; border:1px solid #eef0f3; border-radius:6px; }
    .em-site-chip__tstyle::before { content:"Aa"; font-weight:700; font-size:11px; color:#94a3b8; line-height:1; }
    .em-site-chip__tsize { width:64px; flex:0 0 auto; }
    .em-site-chip__tfont { width:120px; max-width:140px; font-size:12px; flex:0 0 auto; }
    .em-site-chip__tstyle .em-site-admin-color-field-row { margin:0; }
    .em-site-chip__btncolor { display:inline-flex; align-items:center; gap:5px; flex:0 0 auto; padding:3px 6px; background:#f8fafc; border:1px solid #eef0f3; border-radius:6px; }
    .em-site-chip__btncolor-label { font-size:11px; font-weight:700; color:#94a3b8; line-height:1; }
    .em-site-chip__btncolor .em-site-admin-color-field-row { margin:0; }
    .em-site-chip__btnmargin, .em-site-chip__badgeopt { display:inline-flex; align-items:center; gap:5px; flex:0 0 auto; padding:3px 6px; background:#f8fafc; border:1px solid #eef0f3; border-radius:6px; }
    .em-site-chip__btnmargin > span, .em-site-chip__badgeopt > span { font-size:11px; font-weight:700; color:#94a3b8; line-height:1; }
    .em-site-chip__btnmargin input[type="number"], .em-site-chip__badgeopt input[type="number"] { width:56px; font-size:12px; }
    .em-site-chip__badgeopt select { font-size:12px; max-width:140px; }
    .em-site-chip__badgeopt input[type="number"] { width:48px; }

    .em-site-chip__actions { display:flex; align-items:center; gap:2px; flex:0 0 auto; margin-top:0; }
    .em-site-chip__remove { border:0; background:transparent; color:#b32d2e; font-size:18px; line-height:1; cursor:pointer; padding:0 4px; border-radius:4px; flex:0 0 auto; display:inline-flex; align-items:center; justify-content:center; height:24px; }
    .em-site-chip__remove:hover { background:#fbeaea; }
    .em-site-chip__toggle { border:0; background:transparent; color:#6b7280; cursor:pointer; padding:0 4px; border-radius:4px; flex:0 0 auto; display:inline-flex; align-items:center; justify-content:center; height:24px; }
    .em-site-chip__toggle:hover { color:#1d2327; background:#eef2f7; }
    .em-site-chip__toggle .dashicons { font-size:18px; width:18px; height:18px; }
    .em-site-chip.is-hidden { opacity:.55; background:#f1f1f3; }
    .em-site-chip.is-hidden .em-site-chip__type::after { content:" (masquÃ©)"; color:#b32d2e; font-weight:600; }
    .em-site-chip.is-hidden .em-site-chip__toggle { color:#b32d2e; }

    /* Bloc Plateforme / RÃ©seau : tous les champs compacts sur UNE ligne (titre,
       rÃ©seau, lien, pseudo) â€” mÃªmes bases flex que les autres champs, l'espace
       restant pousse l'Å“il/la croix Ã  droite. */
    .em-site-chip__fields .em-site-chip__platform { flex:0 1 150px; min-width:90px; max-width:100%; width:auto; }
    .em-site-chip__fields .em-site-chip__ptitle { flex:0 1 130px; min-width:70px; width:auto; }
    .em-site-chip__fields .em-site-chip__paccount { flex:0 1 150px; min-width:70px; width:auto; }
    .em-site-chip__fields .em-site-chip__url { flex:0 1 160px; min-width:70px; width:auto; }

    .em-site-chip__tt-part { display:inline-flex; align-items:center; gap:6px; flex-wrap:nowrap; flex:1 1 240px; min-width:0; }
    .em-site-chip__check { display:inline-flex; align-items:center; gap:4px; font-size:12px; color:#50575e; white-space:nowrap; }
    .em-site-chip__vthumb { flex:0 0 auto; }
    .em-site-chip__ti-image { display:flex; align-items:center; gap:6px; flex-wrap:wrap; }

    /* Ajout d'un champ dans une cellule */
    .em-site-celladd { padding:0; }
    .em-site-celladd__btn { width:100%; border:1px dashed #cbd5e1; background:#fff; color:#2271b1; border-radius:5px; padding:5px; cursor:pointer; font-size:12px; display:flex; align-items:center; justify-content:center; gap:4px; }
    .em-site-celladd__btn:hover { background:#f0f6fc; }
    .em-site-celladd__form[hidden] { display:none; }
   .em-site-celladd__form:not([hidden]) { display:flex; gap:6px; align-items:center; flex-wrap:nowrap; margin-top:6px; padding:6px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; position:relative; overflow:visible; }
   .em-site-celladd__picker { position:relative; flex:0 0 260px; width:260px; max-width:260px; min-width:0; }
   .em-site-celladd__pickbtn { width:100%; min-height:32px; border:1px solid #c3c4c7; border-radius:6px; background:#fff; display:flex; align-items:center; gap:8px; padding:0 8px; color:#111827; cursor:pointer; }
   .em-site-celladd__pickbtn:hover { border-color:#8fa7c0; background:#fdfefe; }
   .em-site-celladd__pickicon { color:#0f172a; }
   .em-site-celladd__picklabel { flex:1 1 auto; min-width:0; text-align:left; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-size:12px; }
   .em-site-celladd__pickarrow { color:#64748b; }
   .em-site-celladd__menu { position:absolute; top:calc(100% + 6px); left:0; width:min(360px, 60vw); max-height:300px; overflow:auto; background:#fff; border:1px solid #cbd5e1; border-radius:8px; box-shadow:0 10px 28px rgba(2,6,23,.16); z-index:35; padding:6px; }
   .em-site-celladd__group + .em-site-celladd__group { margin-top:6px; padding-top:6px; border-top:1px solid #eef2f7; }
   .em-site-celladd__gtitle { font-size:11px; font-weight:700; color:#475569; text-transform:uppercase; letter-spacing:.03em; margin:0 2px 4px; }
   .em-site-celladd__glist { display:flex; flex-direction:column; gap:2px; }
   .em-site-celladd__opt { border:0; background:#fff; border-radius:6px; min-height:28px; padding:4px 6px; display:flex; align-items:center; gap:7px; text-align:left; cursor:pointer; color:#0f172a; }
   .em-site-celladd__opt:hover { background:#eef6ff; }
   .em-site-celladd__opticon { color:#334155; }
   .em-site-celladd__optlabel { font-size:12px; line-height:1.25; }
   .em-site-celladd__type[hidden] { display:none !important; }
   .em-site-celladd__confirm { min-width:44px; }
    .em-site-celladd__cancel { border:0; background:transparent; color:#6b7280; font-size:16px; cursor:pointer; }

    .em-site-builder__actions { display:flex; gap:8px; align-items:center; margin-top:12px; }

   .em-site-release-tools { display:inline-flex; align-items:center; gap:8px; flex-wrap:wrap; }
   .em-site-release-tools__hint { font-size:11px; color:#6b7280; }

   .em-site-builder[data-item-type="release"] .em-site-chip--release-intro,
   .em-site-builder[data-item-type="release"] .em-site-chip--release-title,
   .em-site-builder[data-item-type="release"] .em-site-chip--release-credit {
      display:flex;
      align-items:center;
      gap:8px;
      padding:6px 8px;
      border-radius:8px;
   }

   .em-site-builder[data-item-type="release"] .em-site-chip--release-intro { background:#fffef6; border-color:#eadfb0; }
   .em-site-builder[data-item-type="release"] .em-site-chip--release-title { background:#f5f7ff; border-color:#d8def4; }
   .em-site-builder[data-item-type="release"] .em-site-chip--release-credit { background:#f8fafc; border-color:#d7e0ea; }
   .em-site-builder[data-item-type="release"] .em-site-chip--release-credit-sep { background:#fbfdff; border-color:#e2e8f0; }

   .em-site-builder[data-item-type="release"] .em-site-chip--release-intro .em-site-chip__type,
   .em-site-builder[data-item-type="release"] .em-site-chip--release-title .em-site-chip__type,
   .em-site-builder[data-item-type="release"] .em-site-chip--release-credit .em-site-chip__type {
      min-width:110px;
      max-width:110px;
      overflow:hidden;
      text-overflow:ellipsis;
      white-space:nowrap;
   }

   .em-site-builder[data-item-type="release"] .em-site-chip--release-intro .em-site-chip__fields,
   .em-site-builder[data-item-type="release"] .em-site-chip--release-title .em-site-chip__fields,
   .em-site-builder[data-item-type="release"] .em-site-chip--release-credit .em-site-chip__fields {
      display:flex;
      align-items:center;
      gap:8px;
      flex:1 1 auto;
      min-width:0;
      overflow-x:auto;
      padding-bottom:2px;
   }

   .em-site-builder[data-item-type="release"] .em-site-chip--release-title .em-site-chip__tt-part,
   .em-site-builder[data-item-type="release"] .em-site-chip--release-credit .em-site-chip__tt-part {
      display:flex;
      align-items:center;
      gap:8px;
      flex:0 0 auto;
   }

   .em-site-builder[data-item-type="release"] .em-site-chip--release-title .em-site-chip__titext,
   .em-site-builder[data-item-type="release"] .em-site-chip--release-credit .em-site-chip__titext {
      width:180px;
   }

   .em-site-builder[data-item-type="release"] .em-site-chip--release-title .em-site-chip__titext2,
   .em-site-builder[data-item-type="release"] .em-site-chip--release-credit .em-site-chip__titext2 {
      width:220px;
   }

   .em-site-builder[data-item-type="release"] .em-site-chip--release-intro .em-site-chip__actions,
   .em-site-builder[data-item-type="release"] .em-site-chip--release-title .em-site-chip__actions,
   .em-site-builder[data-item-type="release"] .em-site-chip--release-credit .em-site-chip__actions {
      margin-left:auto;
      flex:0 0 auto;
   }

   .em-site-builder[data-item-type="release"] .em-site-chip--release-intro .em-site-chip__tlink,
   .em-site-builder[data-item-type="release"] .em-site-chip--release-credit .em-site-chip__tlink,
   .em-site-builder[data-item-type="release"] .em-site-chip--release-credit .em-site-chip__tlink2,
   .em-site-builder[data-item-type="release"] .em-site-chip--release-intro .em-site-chip__tsize,
   .em-site-builder[data-item-type="release"] .em-site-chip--release-intro .em-site-chip__tfont,
   .em-site-builder[data-item-type="release"] .em-site-chip--release-intro .em-site-chip__talign,
   .em-site-builder[data-item-type="release"] .em-site-chip--release-credit .em-site-chip__tsize,
   .em-site-builder[data-item-type="release"] .em-site-chip--release-credit .em-site-chip__tfont,
   .em-site-builder[data-item-type="release"] .em-site-chip--release-credit .em-site-chip__talign {
      display:none !important;
   }

    .em-site-sticky { position:sticky; top:32px; z-index:20; margin:0 0 14px; }
    .em-site-savebar { display:flex; align-items:center; justify-content:flex-start; gap:12px; margin:0; padding:8px 0; background:transparent; border:0; box-shadow:none; }
    .em-site-savebar[hidden] { display:none; }
    /* Boutons de la savebar : mÃªmes styles que le modal de confirmation (bordeaux, pas de bleu) */
    .em-site-savebar__btn.button-primary { border-color:#4e080e !important; background:linear-gradient(180deg,#751820 0%,#4e080e 100%) !important; color:#fff !important; text-shadow:none !important; box-shadow:0 1px 0 rgba(255,255,255,.18) inset, 0 2px 8px rgba(78,8,14,.28) !important; }
    .em-site-savebar__btn.button-primary:hover, .em-site-savebar__btn.button-primary:focus { border-color:#3d060b !important; background:linear-gradient(180deg,#651620 0%,#3d060b 100%) !important; color:#fff !important; box-shadow:0 1px 0 rgba(255,255,255,.14) inset, 0 4px 12px rgba(78,8,14,.34) !important; }
   /* Exception: bouton "+ Nouvelle Rubrique" neutre au chargement, accent seulement en interaction/ouverture. */
   .em-site-savebar__btn.em-site-rubriques-admin__add-rubrique-toggle.button-primary,
   .em-site-savebar__btn.em-rubriques-admin__add-rubrique-toggle.button-primary {
      border-color:transparent !important;
      background:transparent !important;
      color:#1d2327 !important;
      box-shadow:none !important;
   }
   .em-site-savebar__btn.em-site-rubriques-admin__add-rubrique-toggle.button-primary:hover,
   .em-site-savebar__btn.em-site-rubriques-admin__add-rubrique-toggle.button-primary:focus,
   .em-site-savebar__btn.em-rubriques-admin__add-rubrique-toggle.button-primary:hover,
   .em-site-savebar__btn.em-rubriques-admin__add-rubrique-toggle.button-primary:focus {
      border-color:#751820 !important;
      background:#fff !important;
      color:#751820 !important;
      box-shadow:none !important;
   }
   .em-site-savebar__btn.em-site-rubriques-admin__add-rubrique-toggle.button-primary[aria-expanded="true"],
   .em-site-savebar__btn.em-rubriques-admin__add-rubrique-toggle.button-primary[aria-expanded="true"] {
      border-color:#751820 !important;
      background:#751820 !important;
      color:#fff !important;
      box-shadow:none !important;
   }
    .em-site-savebar__revert { display:inline-flex; align-items:center; height:30px; padding:0 12px; border:1px solid #c3c4c7; border-radius:3px; background:#fff; color:#50575e; font-size:13px; font-weight:500; line-height:1; cursor:pointer; box-shadow:0 1px 0 rgba(255,255,255,.9) inset; }
    .em-site-savebar__revert:hover, .em-site-savebar__revert:focus { border-color:#751820; color:#751820; background:#fafafa; }
    /* ContrÃ´les d'aperÃ§u sur la ligne du nom de la rubrique (Å“il + nouvel onglet) */
    .em-site-item__preview { display:inline-flex; align-items:center; gap:2px; margin-left:8px; }
    .em-site-preview__toggle, .em-site-preview__popout { border:0; background:transparent; color:#2271b1; cursor:pointer; padding:3px 5px; border-radius:4px; display:inline-flex; align-items:center; }
    .em-site-preview__toggle:hover, .em-site-preview__popout:hover { background:#eef2f7; }
    .em-site-preview__toggle[aria-pressed="true"] { color:#1d2327; background:#eef2f7; }
    .em-site-preview__toggle .dashicons { font-size:18px; width:18px; height:18px; }
    .em-site-preview__popout .dashicons { font-size:16px; width:16px; height:16px; }
    .em-site-preview__frame, .em-site-livepreview { border:1px dashed #cbd5e1; border-radius:6px; overflow:hidden; }
    .em-site-livepreview { margin-top:8px; }
    .em-site-livepreview[hidden] { display:none; }
    .em-site-livepreview:empty::before { content:"â€¦"; display:block; padding:18px; color:#9ca3af; text-align:center; }

    /* NOTE : le rendu .em-rubriqueâ€¦ (base, grille, champs, mÃ©dias, composants)
       n'est plus dupliquÃ© ici. Il est inlinÃ© en fin de fichier depuis la
      source admin locale rubriques-preview/ via em_site_admin_rubriques_preview_css(). */
    /* HEADER composite (.em-header-shellâ€¦) : mÃªme source admin locale. */
    .em-site-chip--decor { align-items:center; }
    .em-site-chip--decor .em-site-chip__type { font-weight:600; color:#1d2327; text-transform:none; }

   /* === RENDU RUBRIQUES ADMIN (source unique : assets/admin/css/rubriques-preview/*) === */
<?php echo function_exists('em_site_admin_rubriques_preview_css') ? em_site_admin_rubriques_preview_css() : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
   @keyframes em-site-overview-fade {
      from {
         opacity: 0;
         transform: translateY(8px);
      }
      to {
         opacity: 1;
         transform: translateY(0);
      }
   }

   @media (max-width: 960px) {
      .em-site-overview__summary-head,
      .em-site-overview__focus-bar {
         flex-direction: column;
         align-items: flex-start;
      }

      .em-site-overview__focus-titlewrap {
         align-items: flex-start;
      }
   }
</style>
<?php

