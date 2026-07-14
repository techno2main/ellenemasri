# Suivi Refonte em-site

## Horodatage temps r?el (Paris)
1. Fuseau de r?f?rence : Europe/Paris.
2. Format obligatoire : YYYY-MM-DD HH:mm:ss.
3. Derni?re mise ? jour : 2026-07-14 16:17:53.

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

### 2026-07-09 11:54:20 (Paris)

- Retour demandé vers le layout HEADER précédent, en une seule colonne avec HERO au-dessus du SLIDER, pour figer la preview sur le comportement simple et stable.
- Neutralisation du bloc de composition pair dans le rendu admin HEADER afin d'éviter les variations de grille introduites pendant le debug.
- Validation technique réalisée après rollback : fichier PHP concerné vérifié sans erreur de syntaxe.
13. Tests obligatoires entre lots : aucun passage au lot/?tape suivant sans tests ex?cut?s et valid?s.

## Journal d'avancement

### 2026-07-14 16:17:53 (Paris)

- Refonte compl?te de la page `Ic?nes BO` c?t? UX et logique: suppression du bloc fallback visible, fallback interne fig? sur `dashicons-warning`.
- Les ic?nes disponibles passent en mode statut explicite `Affich?e` / `Masqu?e` selon la case ? cocher.
- Les dropdowns de changement d'ic?ne utilisent uniquement les ic?nes actives (non masqu?es), group?es par cat?gories dans le m?me ordre que la section `Ic?nes disponibles`.
- Ajout de s?parateurs visuels entre cat?gories dans le menu d?roulant des ic?nes.
- Alignement de l'affichage `Ic?nes disponibles`: les ic?nes d?coch?es sont retir?es de la liste par d?faut.
- Ajustements UI finalis?s: titres principaux en majuscules, espacement du bloc `Ic?nes attribu?es` resserr?, filet superflu supprim? dans `Ic?nes disponibles`.
- Menu gauche Param?tres harmonis?: renommage en `Ic?nes BO`, mise en premier dans la section, liens en majuscules.
- Ic?ne de la page `Ic?nes BO` harmonis?e sur `dashicons-image-filter` dans l'en-t?te et dans le menu lat?ral.

### 2026-07-14 14:25:17 (Paris)

- Sous-étape `édition fine par item` implémentée dans la page Gestion des icônes.
- Ajout d'un sélecteur d'icône par entrée dans les blocs `Menus` et `Rubriques` pour permettre un changement ciblé sans modifier le code source.
- Enregistrement unifié : la sauvegarde persiste désormais à la fois la whitelist active + le fallback + les assignations fines par item.
- Les assignations fines forcent automatiquement l'activation de l'icône choisie dans la liste active pour éviter les incohérences.
- Contrôles techniques exécutés après implémentation : diagnostics sans erreur et `php -l` OK sur les fichiers modifiés.

### 2026-07-14 14:10:33 (Paris)

- Livraison du lot Gestion des icônes admin: ajout d'une page dédiée accessible depuis Paramètres et intégrée au menu accordéon.
- Centralisation de la logique d'icônes/fallback dans un socle partagé (`inc/shared/icons/icons.php`) avec lecture dynamique de la whitelist active (`dashicons-list.txt`).
- Fiabilisation du fallback global: fallback persistant, validé et imposé en première position dans la liste active.
- Refonte UX de la page Gestion des icônes: sections repliables, séparation `Icônes attribuées` / `Autres icônes`, bloc fallback en tête non désactivable.
- Durcissement du flux d'enregistrement: passage au nonce WordPress et suppression du jeton session custom.
- Ajustement final de l'action de sauvegarde: bouton `Enregsitrer` activé uniquement en cas de modification détectée côté formulaire.
- Ajustement d'ergonomie sur les icônes déjà attribuées: retrait des cases à cocher dans les cartes d'attribution.
- Alignement du rendu admin sur le fallback partagé: remplacement des icônes hardcodées sur les zones actives (dashboard, modules, rubriques, menu admin) via helpers centralisés.
- Contrôle technique effectué: `php -l` sur le manager d'icônes sans erreur de syntaxe.

### 2026-07-13 18:44:54 (Paris)

- Correctif du flux Rubriques: les actions AJAX (masquer/afficher, ordre, layout HEADER, ajout/retrait) marquent d?sormais l'?tat brouillon comme modifi?.
- Raccordement du bouton `APER?U AVANT MISE EN LIGNE`: r?activation automatique apr?s modifications Rubriques sans sauvegarde de formulaire classique.
- Ajout d'une API JS de pilotage du bouton preview (`markReady` / `clearReady`) et d'un ?v?nement global `emSiteDraftChanged` pour synchroniser l'?tat UI.
- Clarification fonctionnelle valid?e: le front public reste sur le canal live tant qu'il n'y a pas de validation de mise en ligne.

### 2026-07-13 18:31:38 (Paris)

- Ajustement de wording sur le bloc Rubriques: bouton d'action renomm? en `Ins?rer une Rubrique`.
- Ajustement de wording wireframe: `SQUELETTE` remplac? par `SQUELETTE DE MON SITE`.
- Ajustement de wording preview: `Aper?u images` remplac? par `Aper?u en images` (et variante contextualis?e).
- Contr?les techniques ex?cut?s: `php -l` sur les fichiers PHP modifi?s, sans erreur.

### 2026-07-13 18:15:09 (Paris)

- Lot de finition UI Dashboard/Rubriques appliqu?: renommages et copies harmonis?es sur la carte `MON TEMPLATE` et les zones Rubriques.
- Wireframe Rubriques align? visuellement sur le comportement attendu (HEADER, espacements, interactions de survol), avec conservation de l'arri?re-plan valid?.
- Uniformisation visuelle des filets verticaux Rubriques sur la teinte `#4f080e`.
- Bloc d'ajout rubrique refondu: intitul? `Ajouter Rubrique`, suppression des lignes explicatives superflues, panneau plus compact et plus lisible.
- Stabilisation du badge `DEFAULT LIVE` cliquable dans le Dashboard: verrouillage des ?tats lien pour ?viter soulignement/changement de couleur au survol.

### 2026-07-13 17:35:28 (Paris)

- Mutualisation du fallback front des rubriques dans un fichier unique: `inc/front/fallback-rubrique.php`.
- Rendu fallback simplifi? et uniforme pour toutes les rubriques: affichage exclusif du nom de rubrique en MAJUSCULES.
- M?nage appliqu?: suppression du syst?me historique de placeholders front distribu?s.
- R?solution du template actif durcie: suppression du repli hardcod? `mayami` dans le front et dans le resolver partag? (template actif valide, sinon premier slug du registre).
- Contr?les techniques ex?cut?s: `php -l` sur les fichiers modifi?s, sans erreur.

### 2026-07-13 12:58:23 (Paris)

- Stabilisation du hub Template/Rubriques sans bascule d'URL: conservation de `page=em-template` lors des clics sur les zones et actions du squelette.
- Correction de r?gression d'affichage: rechargement des assets Rubriques (CSS/JS wireframe + header preview) ?galement en contexte `page=em-template`.
- Harmonisation UX de la zone wireframe: suppression du bouton local `APER?U AVANT MISE EN LIGNE`, renommage `PREVIEW` en `VOIR IMAGES`, renommage du libell? `Aper?u` en `Aper?u images`, suppression du badge `LIVE` dans cet en-t?te.
- Flux preview front durci: transmission d'une URL admin de retour explicite et focus/fermeture de l'onglet preview apr?s mise en ligne vers l'onglet source attendu.

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

### 2026-07-09 10:24:37 (Paris)

- Lot UX Rubriques/HEADER poursuivi sur la page overview admin : ajout et stabilisation des onglets inline `Apparence` / `Contenu` (et `Composition` pour HEADER) avec ouverture ciblée, état actif cohérent et masquage du panneau non actif.
- Harmonisation de la ligne item (icônes, séparateurs, renommage mono-ligne, slug non interactif) et ajustements de lisibilité du bandeau focus (onglet inactif atténué, actif plus lisible).
- Refonte de la composition HEADER :
	- ordre des options réorganisé (`HERO + SLIDER`, puis `HERO seul`, puis `SLIDER seul`),
	- section `Position` conditionnelle,
	- ordre dynamique des listes HERO/SLIDER selon la position choisie,
	- suppression du scroll horizontal sur les listes d’items,
	- séparation visuelle entre bloc de réglages (Composition/Position) et bloc de sélection des items.
- Harmonisation du bloc `Apparence` de HEADER sur la structure visuelle standard des autres rubriques (lignes `Couleurs`, `Espacements`, `Mise en page`, `Image de fond`) tout en conservant les champs métier spécifiques.
- Règle métier appliquée : si la composition est `HERO seul` ou `SLIDER seul`, la ligne `Mise en page` (incluant le ratio HERO/SLIDER) est masquée ; elle réapparaît en `HERO + SLIDER`.
- Contrôles techniques exécutés pendant le lot : diagnostics VS Code sur les fichiers modifiés sans erreur remontée.

### 2026-07-09 10:57:56 (Paris)

- Ajustements UI du bandeau focus Rubriques :
	- carte de création renommée (`Ajouter`) avec sous-texte `+ NOUVELLE RUBRIQUE`,
	- icône d’action remplacée par `+`,
	- bouton `+ NOUVEL ITEM` réintroduit à droite des onglets de titre rubrique et relié au flux d’ajout existant de la carte active,
	- harmonisation typographique et colorimétrique du bouton d’ajout avec les onglets.
- Ajustements page Squelette/Template :
	- retrait des icônes d’édition dans les listes d’items (picker standard + listes HEADER dédiées),
	- réalignement du bloc de réglages sous la ligne titre rubrique,
	- largeur du bloc de réglages recalée pour correspondre visuellement à la largeur utile du cartouche titre (sans empiéter sur la zone de suppression/croix).
- Contrôles techniques exécutés : diagnostics VS Code sans erreur sur les fichiers modifiés.
