# Rapport d'audit refonte PHP/structure

Date (Paris) : 2026-07-05 17:11:19
P?rim?tre scann? : em-site/wp/wp-content/themes/em-site

## Mise ? jour structurelle (Paris) : 2026-07-14 14:10:33

- Ajout d'un socle central de gestion d'ic?nes partag? : `inc/shared/icons/icons.php`.
- Ajout d'une liste d'ic?nes active persist?e : `inc/shared/icons/dashicons-list.txt`.
- Ajout d'une page admin de pilotage des ic?nes : `inc/shared/icons/dashicons-preview.php`.
- Ajout du point d'entr?e menu d?di? pour l'acc?s Param?tres : `inc/admin/shared/menu/dashicons-manager.php`.
- Rec?blage des rendus admin/modules/rubriques vers les helpers partag?s (`em_site_site_icon`, `em_site_rubrique_icon`) pour ?liminer les hardcodes et respecter le fallback global.
- Fiabilisation s?curit?/enregistrement de la page manager : nonce WordPress + validation fallback robuste.

## Mise ? jour structurelle (Paris) : 2026-07-07 12:56:24

- Migration de composants BO partag?s vers `inc/admin/shared/components/` avec sous-dossier d?di? par composant.
- Composants migr?s et rec?bl?s : `color-picker`, `scotchs-control`, `style-panel`, `hub-cards`, `hub-breadcrumb`.
- Renommage explicite du builder Rubriques : fichiers `chip*`, `structure*`, `preview`, `save`, `script*` renomm?s en `builder-...`.
- Rec?blage des includes et loaders Rubriques apr?s renommage pour ?viter toute rupture.
- Correctif de mutualisation Scotchs : neutralisation des variations de layout contextuelles via classes d?di?es de composant (`em-v4-scotchs-control__color`, `em-v4-scotchs-control__check`).

## Mise ? jour structurelle (Paris) : 2026-07-07 21:54:16

- Purge des pr?fixes `emv4` restante dans le builder Rubriques et le composant Scotchs mutualis?.
- Distinction nette des helpers top-bar admin/front pour supprimer le fatal `Cannot redeclare` sur le rendu admin.
- Validation finale: `emv4` ne ressort plus dans le th?me actif apr?s grep global.

## Mise ? jour structurelle (Paris) : 2026-07-07 14:12:20

- Migration des assets partag?s admin vers `assets/admin/shared/*` avec rec?blage des enqueues c?t? `inc/admin`.
- Migration des assets partag?s front vers `assets/front/shared/*` avec rec?blage des enqueues c?t? `inc/core` et pages Rubriques.
- Purge des chemins legacy `assets/front/css/rubriques-v4/*` et suppression du dossier fant?me.
- Purge du namespace gate legacy `ellene` dans les points d'entr?e admin.
- Correctif de r?gression des acc?s admin: r?gles r?tablies avec comptes r?els `admin-ellene` et `admin-tyson`.
- Hotfix imports CSS admin cass?s: correction des chemins dans `hub-cards.css` et `module-common.css`.

## 1) M?thode de scan (calcul r?el des lignes)

- Scan r?cursif r?el sur le dossier complet du th?me.
- Comptage de lignes effectu? fichier par fichier via lecture r?elle du contenu.
- Extensions incluses pour ce rapport : .php, .js, .css, .html, .md.

## 2) R?sum? chiffr? global

- Fichiers scann?s : 482
- Lignes totales : 66 365
- Fichiers > 300 lignes : 38

R?partition par extension :

- .php : 332 fichiers, 40 703 lignes, 29 fichiers > 300
- .js : 72 fichiers, 11 402 lignes, 5 fichiers > 300
- .css : 76 fichiers, 11 765 lignes, 3 fichiers > 300
- .html : 2 fichiers, 2 495 lignes, 1 fichier > 300

## 3) Fichiers > 300 lignes (liste compl?te)

1. visual-links-builder/visual-links-builder.html ? 2278
2. visual-links-builder/admin/assets/builder.js ? 2147
3. assets/admin/js/template/wizard/wizard-guide.js ? 1281
4. assets/admin/js/template/wizard/wizard-draft.js ? 1030
5. inc/rubriques/admin/builder/script/part-01.php ? 896
6. inc/admin/client-access.php ? 783
7. assets/admin/js/template/wizard/wizard-wireframe.js ? 729
8. visual-links-builder/styles/builder.css ? 664
9. visual-links-builder/admin/assets/builder.css ? 664
10. inc/admin/shared/landing-preview.php ? 628
11. inc/rubriques/core/storage.php ? 609
12. inc/admin/shared/style-panel.php ? 594
13. inc/rubriques/admin/builder/preview.php ? 570
14. inc/admin/pages/rubriques/header-section.php ? 535
15. inc/rubriques/admin/pages/overview-styles.php ? 516
16. inc/rubriques/core/field-types/media.php ? 492
17. inc/shared/rubrique-order.php ? 489
18. inc/shared/template/registry.php ? 480
19. inc/admin/template/pages/create-page.php ? 439
20. inc/rubriques/admin/builder/rows-script.php ? 430
21. inc/admin/pages/rubriques/header-section-assets.php ? 417
22. inc/rubriques/admin/pages/overview.php ? 401
23. inc/rubriques/admin/builder/chip-script.php ? 401
24. inc/admin/shared/variant-hub.php ? 393
25. inc/admin/shared/register-module-saves.php ? 392
26. inc/admin/shared/onboarding.php ? 375
27. inc/rubriques/admin/builder/structure-rows.php ? 370
28. inc/admin/shared/menu/accordion.php ? 368
29. inc/admin/template/pages/list.php ? 364
30. assets/admin/js/pages/rubriques/template-skeleton.js ? 345
31. inc/shared/template/active.php ? 343
32. inc/admin/pages/rubriques/skeleton-preview.php ? 324
33. inc/rubriques/core/registry.php ? 321
34. inc/shared/template/plan.php ? 318
35. inc/admin/pages/dashboard/components.php ? 309
36. inc/admin/template/banner.php ? 304
37. inc/admin/template/wizard/config-data.php ? 302
38. assets/front/css/rubriques-v4/render.css ? 302

Constat : le point ? trop de fichiers > 300 lignes ? est confirm?.

## 4) Nommage non intuitif (part01/part02/etc.)

### 4.1 Fichiers au format part-XX

Total d?tect? : 15

1. inc/admin/pages/rubriques/definitions/part-01.php
2. inc/admin/pages/rubriques/definitions/part-02.php
3. inc/admin/pages/rubriques/definitions/part-03.php
4. inc/admin/pages/rubriques/definitions/part-04.php
5. inc/admin/pages/rubriques/definitions/part-05.php
6. inc/admin/shared/hub-cards/part-01.php
7. inc/admin/shared/hub-cards/part-02.php
8. inc/admin/shared/hub-cards/part-03.php
9. inc/admin/shared/hub-cards/part-04.php
10. inc/admin/shared/hub-cards/part-05.php
11. inc/admin/shared/menu/layout/part-01.php
12. inc/admin/shared/menu/layout/part-02.php
13. inc/admin/shared/menu/layout/part-03.php
14. inc/admin/shared/menu/layout/part-04.php
15. inc/rubriques/admin/builder/script/part-01.php

### 4.2 Fichiers index.php g?n?riques

Total d?tect? : 18

1. inc/admin/modules/about/index.php
2. inc/admin/modules/contact/index.php
3. inc/admin/modules/header/index.php
4. inc/admin/pages/sections/index.php
5. inc/admin/pages/templates/index.php
6. inc/domain/resolver/header/index.php
7. inc/domain/resolver/sections/index.php
8. inc/domain/sections/header/index.php
9. inc/domain/sections/instances/index.php
10. inc/domain/sections/items/index.php
11. inc/domain/sections/registry/index.php
12. inc/domain/sections/slide/index.php
13. inc/domain/sections/visibility/index.php
14. inc/domain/templates/active/index.php
15. inc/domain/templates/registry/index.php
16. inc/domain/templates/skeleton/index.php
17. index.php
18. visual-links-builder/admin/index.php

Constat : la remarque sur le nommage non intuitif est ?galement confirm?e.

## 5) Duplication du guard ABSPATH

Question pos?e : ? pourquoi encore plein de fichiers avec `<?php if (!defined('ABSPATH')) { exit; }` ? utile ou oubli de purge ? ?

Mesure r?elle :

- Occurrences exactes du pattern compact : 30 fichiers
- Occurrences variantes du guard ABSPATH (formes diff?rentes) : 276 fichiers

Lecture technique :

- Ce n'est pas un oubli de purge au sens "code mort" : c'est une protection WordPress standard contre l'acc?s direct ? des fichiers PHP.
- La r?p?tition est normale dans beaucoup de codebases WP, surtout sur des fichiers chargeables directement.
- En revanche, dans une architecture d?j? totalement contr?l?e par bootstrap/loader, on peut r?duire certaines r?p?titions (mais cela demande une d?cision d'architecture et des tests de non-r?gression).

Conclusion sur ce point : pattern utile globalement, mais marge de rationalisation possible.

## 6) Dossiers vides (scan complet)

Total d?tect? : 12

1. assets/admin/js/modules/about
2. assets/admin/js/modules/contact
3. assets/admin/js/modules/cta
4. assets/admin/js/modules/footer
5. assets/admin/js/modules/social
6. assets/admin/js/modules/stream
7. assets/admin/js/modules/video
8. assets/front/images/mayami
9. inc/admin/fields
10. inc/admin/validation
11. visual-links-builder/exports-html/Mayami-EPK/Template-Email/img
12. visual-links-builder/exports-html/Mayami-EPK/Template-HTML

Constat : la remarque sur les dossiers vides est confirm?e (dont inc/admin/fields).

## 7) Synth?se de l'audit

- Point 1 (fichiers > 300) : confirm?, volume significatif (38 fichiers).
- Point 2 (noms non intuitifs) : confirm? (part-XX + index.php g?n?riques nombreux).
- Point 3 (guards ABSPATH dupliqu?s) : confirm? en volume, mais techniquement justifi?s dans la plupart des cas.
- Point 4 (dossiers vides) : confirm? (12 dossiers).
- Point 5 (scan complet r?el des lignes) : r?alis? et consolid? dans ce rapport.

## 8) Actions propos?es (non ex?cut?es)

Ce rapport ne d?clenche aucune action corrective (conforme ? la demande ? aucune action pour le moment ?).

Si validation ult?rieure :

1. Prioriser les 10 plus gros fichiers > 300 (impact maximal rapide).
2. Plan de renommage explicite des part-XX/index.php ambigu?s.

Points temporairement mis en pause :

- Revue ABSPATH fichier par fichier avec politique unique (conserver/simplifier).
- Purge contr?l?e des dossiers vides avec v?rification Git/CI.

## Mise ? jour structurelle (Paris) : 2026-07-07 22:08:43

- Suppression du fichier de migration one-shot inc/core/legacy-option-prefix-migration.php.
- D?branchement du require correspondant dans inc/bootstrap.php.
- Contr?le de coh?rence effectu?: plus aucune r?f?rence au migrateur ni au pr?fixe legacy em_wp dans les fichiers PHP du th?me.

## Mise ? jour structurelle (Paris) : 2026-07-07 23:11:23

- Correctif de robustesse c?t? admin Rubriques: neutralisation du moteur global `module-form-dirty` sur la page Rubriques pour ?viter les soumissions hors p?rim?tre vers `admin-post.php`.
- Ajout d'un garde-fou serveur sur `admin-post.php` quand `action` est vide: redirection contr?l?e vers l'admin avec indicateur d'erreur au lieu d'un ?cran blanc.
- Suppression du BOM UTF-8 dans 3 fichiers PHP sensibles (sortie pr?matur?e possible avant doctype) :
	- `inc/rubriques/admin/pages/overview-styles.php`
	- `inc/rubriques/admin/builder/builder-preview-script.php`
	- `inc/front/modules/slider/render.php`
