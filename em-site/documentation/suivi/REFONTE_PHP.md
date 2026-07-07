# Rapport d'audit refonte PHP/structure

Date (Paris) : 2026-07-05 17:11:19
PÃ©rimÃ¨tre scannÃ© : em-site/wp/wp-content/themes/em-site

## Mise Ã  jour structurelle (Paris) : 2026-07-07 12:56:24

- Migration de composants BO partagÃ©s vers `inc/admin/shared/components/` avec sous-dossier dÃ©diÃ© par composant.
- Composants migrÃ©s et recÃ¢blÃ©s : `color-picker`, `scotchs-control`, `style-panel`, `hub-cards`, `hub-breadcrumb`.
- Renommage explicite du builder Rubriques : fichiers `chip*`, `structure*`, `preview`, `save`, `script*` renommÃ©s en `builder-...`.
- RecÃ¢blage des includes et loaders Rubriques aprÃ¨s renommage pour Ã©viter toute rupture.
- Correctif de mutualisation Scotchs : neutralisation des variations de layout contextuelles via classes dÃ©diÃ©es de composant (`em-v4-scotchs-control__color`, `em-v4-scotchs-control__check`).

## Mise Ã  jour structurelle (Paris) : 2026-07-07 21:54:16

- Purge des prÃ©fixes `emv4` restante dans le builder Rubriques et le composant Scotchs mutualisÃ©.
- Distinction nette des helpers top-bar admin/front pour supprimer le fatal `Cannot redeclare` sur le rendu admin.
- Validation finale: `emv4` ne ressort plus dans le thÃ¨me actif aprÃ¨s grep global.

## Mise Ã  jour structurelle (Paris) : 2026-07-07 14:12:20

- Migration des assets partagÃ©s admin vers `assets/admin/shared/*` avec recÃ¢blage des enqueues cÃ´tÃ© `inc/admin`.
- Migration des assets partagÃ©s front vers `assets/front/shared/*` avec recÃ¢blage des enqueues cÃ´tÃ© `inc/core` et pages Rubriques.
- Purge des chemins legacy `assets/front/css/rubriques-v4/*` et suppression du dossier fantÃ´me.
- Purge du namespace gate legacy `ellene` dans les points d'entrÃ©e admin.
- Correctif de rÃ©gression des accÃ¨s admin: rÃ¨gles rÃ©tablies avec comptes rÃ©els `admin-ellene` et `admin-tyson`.
- Hotfix imports CSS admin cassÃ©s: correction des chemins dans `hub-cards.css` et `module-common.css`.

## 1) MÃ©thode de scan (calcul rÃ©el des lignes)

- Scan rÃ©cursif rÃ©el sur le dossier complet du thÃ¨me.
- Comptage de lignes effectuÃ© fichier par fichier via lecture rÃ©elle du contenu.
- Extensions incluses pour ce rapport : .php, .js, .css, .html, .md.

## 2) RÃ©sumÃ© chiffrÃ© global

- Fichiers scannÃ©s : 482
- Lignes totales : 66 365
- Fichiers > 300 lignes : 38

RÃ©partition par extension :

- .php : 332 fichiers, 40 703 lignes, 29 fichiers > 300
- .js : 72 fichiers, 11 402 lignes, 5 fichiers > 300
- .css : 76 fichiers, 11 765 lignes, 3 fichiers > 300
- .html : 2 fichiers, 2 495 lignes, 1 fichier > 300

## 3) Fichiers > 300 lignes (liste complÃ¨te)

1. visual-links-builder/visual-links-builder.html â€” 2278
2. visual-links-builder/admin/assets/builder.js â€” 2147
3. assets/admin/js/template/wizard/wizard-guide.js â€” 1281
4. assets/admin/js/template/wizard/wizard-draft.js â€” 1030
5. inc/rubriques/admin/builder/script/part-01.php â€” 896
6. inc/admin/client-access.php â€” 783
7. assets/admin/js/template/wizard/wizard-wireframe.js â€” 729
8. visual-links-builder/styles/builder.css â€” 664
9. visual-links-builder/admin/assets/builder.css â€” 664
10. inc/admin/shared/landing-preview.php â€” 628
11. inc/rubriques/core/storage.php â€” 609
12. inc/admin/shared/style-panel.php â€” 594
13. inc/rubriques/admin/builder/preview.php â€” 570
14. inc/admin/pages/rubriques/header-section.php â€” 535
15. inc/rubriques/admin/pages/overview-styles.php â€” 516
16. inc/rubriques/core/field-types/media.php â€” 492
17. inc/shared/rubrique-order.php â€” 489
18. inc/shared/template/registry.php â€” 480
19. inc/admin/template/pages/create-page.php â€” 439
20. inc/rubriques/admin/builder/rows-script.php â€” 430
21. inc/admin/pages/rubriques/header-section-assets.php â€” 417
22. inc/rubriques/admin/pages/overview.php â€” 401
23. inc/rubriques/admin/builder/chip-script.php â€” 401
24. inc/admin/shared/variant-hub.php â€” 393
25. inc/admin/shared/register-module-saves.php â€” 392
26. inc/admin/shared/onboarding.php â€” 375
27. inc/rubriques/admin/builder/structure-rows.php â€” 370
28. inc/admin/shared/menu/accordion.php â€” 368
29. inc/admin/template/pages/list.php â€” 364
30. assets/admin/js/pages/rubriques/template-skeleton.js â€” 345
31. inc/shared/template/active.php â€” 343
32. inc/admin/pages/rubriques/skeleton-preview.php â€” 324
33. inc/rubriques/core/registry.php â€” 321
34. inc/shared/template/plan.php â€” 318
35. inc/admin/pages/dashboard/components.php â€” 309
36. inc/admin/template/banner.php â€” 304
37. inc/admin/template/wizard/config-data.php â€” 302
38. assets/front/css/rubriques-v4/render.css â€” 302

Constat : le point Â« trop de fichiers > 300 lignes Â» est confirmÃ©.

## 4) Nommage non intuitif (part01/part02/etc.)

### 4.1 Fichiers au format part-XX

Total dÃ©tectÃ© : 15

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

### 4.2 Fichiers index.php gÃ©nÃ©riques

Total dÃ©tectÃ© : 18

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

Constat : la remarque sur le nommage non intuitif est Ã©galement confirmÃ©e.

## 5) Duplication du guard ABSPATH

Question posÃ©e : Â« pourquoi encore plein de fichiers avec `<?php if (!defined('ABSPATH')) { exit; }` ? utile ou oubli de purge ? Â»

Mesure rÃ©elle :

- Occurrences exactes du pattern compact : 30 fichiers
- Occurrences variantes du guard ABSPATH (formes diffÃ©rentes) : 276 fichiers

Lecture technique :

- Ce n'est pas un oubli de purge au sens "code mort" : c'est une protection WordPress standard contre l'accÃ¨s direct Ã  des fichiers PHP.
- La rÃ©pÃ©tition est normale dans beaucoup de codebases WP, surtout sur des fichiers chargeables directement.
- En revanche, dans une architecture dÃ©jÃ  totalement contrÃ´lÃ©e par bootstrap/loader, on peut rÃ©duire certaines rÃ©pÃ©titions (mais cela demande une dÃ©cision d'architecture et des tests de non-rÃ©gression).

Conclusion sur ce point : pattern utile globalement, mais marge de rationalisation possible.

## 6) Dossiers vides (scan complet)

Total dÃ©tectÃ© : 12

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

Constat : la remarque sur les dossiers vides est confirmÃ©e (dont inc/admin/fields).

## 7) SynthÃ¨se de l'audit

- Point 1 (fichiers > 300) : confirmÃ©, volume significatif (38 fichiers).
- Point 2 (noms non intuitifs) : confirmÃ© (part-XX + index.php gÃ©nÃ©riques nombreux).
- Point 3 (guards ABSPATH dupliquÃ©s) : confirmÃ© en volume, mais techniquement justifiÃ©s dans la plupart des cas.
- Point 4 (dossiers vides) : confirmÃ© (12 dossiers).
- Point 5 (scan complet rÃ©el des lignes) : rÃ©alisÃ© et consolidÃ© dans ce rapport.

## 8) Actions proposÃ©es (non exÃ©cutÃ©es)

Ce rapport ne dÃ©clenche aucune action corrective (conforme Ã  la demande Â« aucune action pour le moment Â»).

Si validation ultÃ©rieure :

1. Prioriser les 10 plus gros fichiers > 300 (impact maximal rapide).
2. Plan de renommage explicite des part-XX/index.php ambiguÃ«s.

Points temporairement mis en pause :

- Revue ABSPATH fichier par fichier avec politique unique (conserver/simplifier).
- Purge contrÃ´lÃ©e des dossiers vides avec vÃ©rification Git/CI.

## Mise à jour structurelle (Paris) : 2026-07-07 22:08:43

- Suppression du fichier de migration one-shot inc/core/legacy-option-prefix-migration.php.
- Débranchement du require correspondant dans inc/bootstrap.php.
- Contrôle de cohérence effectué: plus aucune référence au migrateur ni au préfixe legacy em_wp dans les fichiers PHP du thème.

## Mise à jour structurelle (Paris) : 2026-07-07 23:11:23

- Correctif de robustesse côté admin Rubriques: neutralisation du moteur global `module-form-dirty` sur la page Rubriques pour éviter les soumissions hors périmètre vers `admin-post.php`.
- Ajout d'un garde-fou serveur sur `admin-post.php` quand `action` est vide: redirection contrôlée vers l'admin avec indicateur d'erreur au lieu d'un écran blanc.
- Suppression du BOM UTF-8 dans 3 fichiers PHP sensibles (sortie prématurée possible avant doctype) :
	- `inc/rubriques/admin/pages/overview-styles.php`
	- `inc/rubriques/admin/builder/builder-preview-script.php`
	- `inc/front/modules/slider/render.php`
