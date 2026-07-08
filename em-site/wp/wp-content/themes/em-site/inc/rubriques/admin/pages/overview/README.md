# Overview Rubriques Admin

Architecture actuelle de la page overview Rubriques.

## Entree principale

- ../../overview.php : chargeur des partiels PHP.

## Partiels PHP

- parts/menu-and-types.php : menu admin, ordre des types, enqueue des assets.
- parts/focus-directory.php : rendu du sommaire et navigation focus.
- parts/render-overview-page.php : rendu principal de la page et scripts UI.
- parts/notice-and-type-card.php : notice admin, rendu des cartes de type.
- parts/actions-create-and-css.php : actions admin et pont CSS.

## CSS admin

- assets/admin/css/rubriques-overview/fields-controls.css
- assets/admin/css/rubriques-overview/summary-directory-top.css
- assets/admin/css/rubriques-overview/summary-directory-meta-and-focus.css
- assets/admin/css/rubriques-overview/cards-and-items.css
- assets/admin/css/rubriques-overview/builder-rows.css
- assets/admin/css/rubriques-overview/module-chips.css
- assets/admin/css/rubriques-overview/savebar-and-preview.css

## Regles

- Pas de fichiers numerotes.
- Pas de suffixes artificiels -a/-b.
- Ajouter les styles dans le segment metier approprie.
