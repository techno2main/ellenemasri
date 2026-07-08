# Suivi Refonte em-site

## Horodatage temps rÃ©el (Paris)
1. Fuseau de rÃ©fÃ©rence : Europe/Paris.
2. Format obligatoire : YYYY-MM-DD HH:mm:ss.
3. DerniÃ¨re mise Ã  jour : 2026-07-08 14:45:14.

## RÃ¨gles de suivi
1. Une Ã©tape = un objectif concret vÃ©rifiable.
2. Chaque Ã©tape contient statut, date, dÃ©cisions, preuves.
3. Pas de mÃ©lange entre actions faites et idÃ©es futures.
4. Un fichier dÃ©diÃ© par Ã©tape dans ce dossier.
5. ExÃ©cution par phases avec sous-Ã©tapes claires.
6. Suivi en temps rÃ©el pendant l'exÃ©cution.
7. Mise Ã  jour des documents de suivi aprÃ¨s chaque validation d'Ã©tape.
8. Flow GitHub uniquement sur demande explicite : commits atomiques par logique mÃ©tier, puis push sur la branche dÃ©diÃ©e.
9. Toute action de suivi est horodatÃ©e en heure de Paris.
10. Mot-clÃ© MAJ docs : scanner tous les documents de suivi concernÃ©s par l'avancement en temps rÃ©el, puis les mettre Ã  jour.
11. Mot-clÃ© flow GH : lancer le process GitHub selon les rÃ¨gles actives du chantier, uniquement sur demande explicite.
12. RÃˆGLE D'OR : aucun flow GH sans vÃ©rification et finalisation prÃ©alable de la MAJ docs.
13. Tests obligatoires entre lots : aucun passage au lot/Ã©tape suivant sans tests exÃ©cutÃ©s et validÃ©s.

## Journal d'avancement

### 2026-07-07 21:54:16 (Paris)

- Purge des rÃ©sidus `emv4` finalisÃ©e sur le builder Rubriques et le composant Scotchs partagÃ©.
- Renommage des prÃ©fixes d'identifiants CSS/JS pour rÃ©tablir la cohÃ©rence runtime entre PHP serveur et JS client.
- Correctif d'un conflit fatal de fonction sur le top-bar: sÃ©paration explicite des helpers admin et front pour Ã©viter le `Cannot redeclare`.
- Revalidation effectuÃ©e aprÃ¨s correction: la page `em-rubriques-overview` ne renvoie plus le 500 cÃ´tÃ© moteur PHP et le grep global `emv4` retourne 0 rÃ©sultat dans le thÃ¨me actif.

### 2026-07-07 14:12:20 (Paris)

- Migration effective des assets partagÃ©s admin `assets/admin/css/shared` + `assets/admin/js/shared` vers `assets/admin/shared/css` + `assets/admin/shared/js`, avec recÃ¢blage des enqueues.
- Migration effective des assets front partagÃ©s `assets/front/css/core` + JS core/slider vers `assets/front/shared/css` + `assets/front/shared/js`.
- Suppression du dossier fantÃ´me `assets/front/css/rubriques-v4` et recÃ¢blage des dÃ©pendances front/admin vers les nouveaux chemins.
- Purge du namespace legacy gate `ellene` cÃ´tÃ© admin et bascule vers le namespace `client`.
- Correction de rÃ©gression sur la logique d'accÃ¨s par admin connectÃ©: mapping actif rÃ©tabli sur `admin-ellene` (restreint) et `admin-tyson` (complet).
- Hotfix CSS admin: correction des imports cassÃ©s dans `assets/admin/shared/css/hub-cards.css` et `assets/admin/shared/css/module-common.css` pour restaurer l'alignement logo/pseudo et le rendu BO.

### 2026-07-07 12:56:24 (Paris)

- Standardisation des composants BO partagÃ©s vers `inc/admin/shared/components/<composant>/`.
- Migration effective des composants partagÃ©s suivants : color-picker, scotchs-control, style-panel, hub-cards, hub-breadcrumb.
- RecÃ¢blage des `require_once` admin vers les nouveaux chemins centralisÃ©s.
- Harmonisation du composant Scotchs pour un rendu identique entre Slider et Top-bar (ordre color picker puis case Ã  cocher, mÃªme logique de placement).
- Ajout de classes neutres de composant Scotchs (`em-v4-scotchs-control__color`, `em-v4-scotchs-control__check`) cÃ´tÃ© PHP, JS et CSS pour Ã©viter les divergences contextuelles.
- Renommage explicite des fichiers du builder Rubriques (prÃ©fixe `builder-...`) pour amÃ©liorer la lisibilitÃ© de reprise dÃ©veloppeur.
- RecÃ¢blage complet des includes builder aprÃ¨s renommage (bootstrap Rubriques, item render, script principal et dÃ©pendances).
- VÃ©rifications effectuÃ©es : recherche de rÃ©fÃ©rences legacy rÃ©siduelles, diagnostics d'erreurs sur les fichiers modifiÃ©s (aucune erreur remontÃ©e par l'outil de diagnostic).



### 2026-07-07 22:08:43 (Paris)

- Suppression définitive du migrateur legacy inc/core/legacy-option-prefix-migration.php.
- Retrait du chargement associé dans inc/bootstrap.php.
- Validation post-suppression: aucune référence PHP restante au migrateur, aucun résidu em_wp dans les PHP du thème, bootstrap sans erreur de diagnostic.

### 2026-07-07 23:11:23 (Paris)

- Debug ciblé de la panne d'enregistrement Rubriques/Slider avec atterrissage sur admin-post.php.
- Correctif JS de protection sur la page Rubriques: désactivation du moteur global module-form-dirty dans ce contexte pour éviter toute soumission parasite.
- Correctif serveur de garde-fou sur admin-post.php sans action: redirection propre vers l'admin avec flag d'erreur, au lieu d'une page blanche.
- Correction de cause racine potentielle Quirks Mode: suppression du BOM UTF-8 sur 3 fichiers PHP chargés côté admin/front (`inc/rubriques/admin/pages/overview-styles.php`, `inc/rubriques/admin/builder/builder-preview-script.php`, `inc/front/modules/slider/render.php`).

### 2026-07-08 13:16:02 (Paris)

- Reprise du chantier Rubriques BO sur `em-site` après régression d'interface dans l'overview admin.
- Réorganisation du rendu overview par sous-fichiers logiques (page + parts) et migration des styles overview vers `assets/admin/css/rubriques-overview/`.
- Correction du blocage de chargement CSS dans l'enqueue admin (suppression du retour prématuré qui empêchait l'injection des styles overview).
- Réintroduction du wrapper `overview-fields.php` pour restaurer la compatibilité runtime attendue par `overview.php` et éviter un fatal lors du chargement de la page Rubriques.
- Restauration ciblée des versions locales antérieures sur les fichiers sensibles (`focus-directory.php`, `notice-and-type-card.php`, `storage.php`) pour revenir à l'état de travail validé visuellement avant les patchs incrémentaux.
- Validation technique exécutée sur les fichiers PHP critiques du lot via `php -l` : aucune erreur de syntaxe détectée.

### 2026-07-08 14:45:14 (Paris)

- Ajustements UX du bandeau focus Rubriques dans l’admin : séparation visuelle affinée, libellé de retour remplacé par `RUBRIQUES`, suppression des décalages résiduels et réglages d’espacement.
- Refonte des onglets d’items : style harmonisé avec la charte marron/blanc, proportions actif/inactif alignées, bordures/arrondis ajustés et amélioration de la lisibilité des icônes.
- Clarification de l’état actif : remplacement de l’icône ambiguë (œil) par une icône de validation (`dashicons-yes-alt`) pour signaler explicitement l’onglet ouvert.
- Comportement navigation item stabilisé :
	- arrivée initiale et clic sur icône+nom de rubrique => liste complète des items fermés,
	- clic onglet => ouverture de l’item ciblé et masquage des autres items.
- Contrôles techniques exécutés pendant le lot : diagnostics VS Code sans erreur sur les fichiers modifiés + vérification répétée `php -l` sur `overview.php` sans erreur de syntaxe.
