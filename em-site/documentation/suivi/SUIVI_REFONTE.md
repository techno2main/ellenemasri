# Suivi Refonte em-site

## Horodatage temps r?el (Paris)
1. Fuseau de r?f?rence : Europe/Paris.
2. Format obligatoire : YYYY-MM-DD HH:mm:ss.
3. Derni?re mise ? jour : 2026-07-08 14:45:14.

## R?gles de suivi
1. Une ?tape = un objectif concret v?rifiable.
2. Chaque ?tape contient statut, date, d?cisions, preuves.
3. Pas de m?lange entre actions faites et id?es futures.
4. Un fichier d?di? par ?tape dans ce dossier.
5. Ex?cution par phases avec sous-?tapes claires.
6. Suivi en temps r?el pendant l'ex?cution.
7. Mise ? jour des documents de suivi apr?s chaque validation d'?tape.
8. Flow GitHub uniquement sur demande explicite : commits atomiques par logique m?tier, puis push sur la branche d?di?e.
9. Toute action de suivi est horodat?e en heure de Paris.
10. Mot-cl? MAJ docs : scanner tous les documents de suivi concern?s par l'avancement en temps r?el, puis les mettre ? jour.
11. Mot-cl? flow GH : lancer le process GitHub selon les r?gles actives du chantier, uniquement sur demande explicite.
12. R?GLE D'OR : aucun flow GH sans v?rification et finalisation pr?alable de la MAJ docs.
13. Tests obligatoires entre lots : aucun passage au lot/?tape suivant sans tests ex?cut?s et valid?s.

## Journal d'avancement

### 2026-07-07 21:54:16 (Paris)

- Purge des r?sidus `emv4` finalis?e sur le builder Rubriques et le composant Scotchs partag?.
- Renommage des pr?fixes d'identifiants CSS/JS pour r?tablir la coh?rence runtime entre PHP serveur et JS client.
- Correctif d'un conflit fatal de fonction sur le top-bar: s?paration explicite des helpers admin et front pour ?viter le `Cannot redeclare`.
- Revalidation effectu?e apr?s correction: la page `em-rubriques-overview` ne renvoie plus le 500 c?t? moteur PHP et le grep global `emv4` retourne 0 r?sultat dans le th?me actif.

### 2026-07-07 14:12:20 (Paris)

- Migration effective des assets partag?s admin `assets/admin/css/shared` + `assets/admin/js/shared` vers `assets/admin/shared/css` + `assets/admin/shared/js`, avec rec?blage des enqueues.
- Migration effective des assets front partag?s `assets/front/css/core` + JS core/slider vers `assets/front/shared/css` + `assets/front/shared/js`.
- Suppression du dossier fant?me `assets/front/css/rubriques-v4` et rec?blage des d?pendances front/admin vers les nouveaux chemins.
- Purge du namespace legacy gate `ellene` c?t? admin et bascule vers le namespace `client`.
- Correction de r?gression sur la logique d'acc?s par admin connect?: mapping actif r?tabli sur `admin-ellene` (restreint) et `admin-tyson` (complet).
- Hotfix CSS admin: correction des imports cass?s dans `assets/admin/shared/css/hub-cards.css` et `assets/admin/shared/css/module-common.css` pour restaurer l'alignement logo/pseudo et le rendu BO.

### 2026-07-07 12:56:24 (Paris)

- Standardisation des composants BO partag?s vers `inc/admin/shared/components/<composant>/`.
- Migration effective des composants partag?s suivants : color-picker, scotchs-control, style-panel, hub-cards, hub-breadcrumb.
- Rec?blage des `require_once` admin vers les nouveaux chemins centralis?s.
- Harmonisation du composant Scotchs pour un rendu identique entre Slider et Top-bar (ordre color picker puis case ? cocher, m?me logique de placement).
- Ajout de classes neutres de composant Scotchs (`em-v4-scotchs-control__color`, `em-v4-scotchs-control__check`) c?t? PHP, JS et CSS pour ?viter les divergences contextuelles.
- Renommage explicite des fichiers du builder Rubriques (pr?fixe `builder-...`) pour am?liorer la lisibilit? de reprise d?veloppeur.
- Rec?blage complet des includes builder apr?s renommage (bootstrap Rubriques, item render, script principal et d?pendances).
- V?rifications effectu?es : recherche de r?f?rences legacy r?siduelles, diagnostics d'erreurs sur les fichiers modifi?s (aucune erreur remont?e par l'outil de diagnostic).



### 2026-07-07 22:08:43 (Paris)

- Suppression d?finitive du migrateur legacy inc/core/legacy-option-prefix-migration.php.
- Retrait du chargement associ? dans inc/bootstrap.php.
- Validation post-suppression: aucune r?f?rence PHP restante au migrateur, aucun r?sidu em_wp dans les PHP du th?me, bootstrap sans erreur de diagnostic.

### 2026-07-07 23:11:23 (Paris)

- Debug cibl? de la panne d'enregistrement Rubriques/Slider avec atterrissage sur admin-post.php.
- Correctif JS de protection sur la page Rubriques: d?sactivation du moteur global module-form-dirty dans ce contexte pour ?viter toute soumission parasite.
- Correctif serveur de garde-fou sur admin-post.php sans action: redirection propre vers l'admin avec flag d'erreur, au lieu d'une page blanche.
- Correction de cause racine potentielle Quirks Mode: suppression du BOM UTF-8 sur 3 fichiers PHP charg?s c?t? admin/front (`inc/rubriques/admin/pages/overview-styles.php`, `inc/rubriques/admin/builder/builder-preview-script.php`, `inc/front/modules/slider/render.php`).

### 2026-07-08 13:16:02 (Paris)

- Reprise du chantier Rubriques BO sur `em-site` apr?s r?gression d'interface dans l'overview admin.
- R?organisation du rendu overview par sous-fichiers logiques (page + parts) et migration des styles overview vers `assets/admin/css/rubriques-overview/`.
- Correction du blocage de chargement CSS dans l'enqueue admin (suppression du retour pr?matur? qui emp?chait l'injection des styles overview).
- R?introduction du wrapper `overview-fields.php` pour restaurer la compatibilit? runtime attendue par `overview.php` et ?viter un fatal lors du chargement de la page Rubriques.
- Restauration cibl?e des versions locales ant?rieures sur les fichiers sensibles (`focus-directory.php`, `notice-and-type-card.php`, `storage.php`) pour revenir ? l'?tat de travail valid? visuellement avant les patchs incr?mentaux.
- Validation technique ex?cut?e sur les fichiers PHP critiques du lot via `php -l` : aucune erreur de syntaxe d?tect?e.

### 2026-07-08 14:45:14 (Paris)

- Ajustements UX du bandeau focus Rubriques dans l?admin : s?paration visuelle affin?e, libell? de retour remplac? par `RUBRIQUES`, suppression des d?calages r?siduels et r?glages d?espacement.
- Refonte des onglets d?items : style harmonis? avec la charte marron/blanc, proportions actif/inactif align?es, bordures/arrondis ajust?s et am?lioration de la lisibilit? des ic?nes.
- Clarification de l??tat actif : remplacement de l?ic?ne ambigu? (?il) par une ic?ne de validation (`dashicons-yes-alt`) pour signaler explicitement l?onglet ouvert.
- Comportement navigation item stabilis? :
	- arriv?e initiale et clic sur ic?ne+nom de rubrique => liste compl?te des items ferm?s,
	- clic onglet => ouverture de l?item cibl? et masquage des autres items.
- Contr?les techniques ex?cut?s pendant le lot : diagnostics VS Code sans erreur sur les fichiers modifi?s + v?rification r?p?t?e `php -l` sur `overview.php` sans erreur de syntaxe.
