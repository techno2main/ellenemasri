# Étape 6 - Rebranchement ADMIN à l'identique

## Statut
- 🟡 En validation finale pré-flow GH
- Horodatage (Paris) : 2026-07-04 14:41:24
- Objectif : retrouver un back-office em-site strictement identique à em-wp, sans régression front.

## Contraintes validées
1. Source unique : em-wp.
2. Exécution par lots courts, avec validation à chaque lot.
3. Aucun changement structurel non nécessaire.
4. Front déjà validé : ne pas le casser.

## Lot A - Cartographie source -> cible

### Écart de volumétrie
- Source em-wp
  - inc/admin : 205 fichiers
  - inc/rubriques : 55 fichiers
  - inc/shared : 14 fichiers
  - inc/vlb : 7 fichiers
- Cible em-site
  - inc/admin : 18 fichiers (stubs vides)
  - inc/core : 4 fichiers

### État cible constaté
1. Le dossier inc/admin existe mais les points d'entrée sont vides.
2. Aucun hook admin actif détecté côté em-site (admin_menu, admin_enqueue_scripts, wp_ajax_*).
3. Le bootstrap thème em-site était front-only jusqu'à cette étape.

### Action technique réalisée (démarrage sécurisé)
1. Ajout d'un point d'entrée admin dédié : inc/admin/bootstrap.php.
2. Branchement conditionnel admin dans inc/bootstrap.php via is_admin().
3. Aucun impact front attendu (chargement admin isolé).

## Matrice de rebranchement (ordre imposé)
1. Lot B - Boot admin minimal
   - Rebrancher menu dashboard et structure chrome admin.
   - Rebrancher assets admin de base.
2. Lot C - Rubriques / templates
   - Rebrancher pages rubriques + templates + stockage associé.
3. Lot D - Modules admin
   - Rebrancher pages/settings module par module (ordre source).
4. Lot E - AJAX, permissions, onboarding
   - Rebrancher actions wp_ajax_*, nonces, capabilities, redirections.
5. Lot F - Parité finale
   - Contrôles source vs cible page par page.

## Prochaines actions immédiates (Lot B)
1. Importer les points d'entrée admin em-wp strictement nécessaires au dashboard.
2. Activer un premier écran admin em-site identique visuellement à la source.
3. Valider absence de régression front (HTTP 200 + modules front OK).

## Lot B - Progression en cours

### Implémentation réalisée
1. `inc/admin/menu.php` n'est plus un stub :
   - ajout du slug dashboard identique source : `em-wp-dashboard`;
   - enregistrement de la page admin + retrait du sous-menu dupliqué;
   - rendu dashboard em-site (onglets Rubriques/Templates/Medias/Settings + cartes);
   - enqueue d'un style dédié dashboard.
2. Ajout du style dashboard : `assets/admin/css/dashboard.css`.
3. Alignement chrome/sidebar engagé :
   - renommage Dashboard WP + surbrillance écran dashboard;
   - injection de la flèche dashboard dans le menu latéral;
   - thème visuel admin (sidebar/bandeau) via `assets/admin/css/core/admin-chrome.css`.
4. Import des styles shared source pour rapprocher le rendu hub :
   - `assets/admin/css/shared/hub-cards.css`
   - `assets/admin/css/shared/live-badge.css`
   - `assets/admin/css/shared/module-common.css`
   - `assets/admin/css/shared/color-picker.css`
   - `assets/admin/css/shared/color-modal.css`
5. Rapprochement menu latéral source :
   - ajout des entrées `TEMPLATES`, `RUBRIQUES`, `VLB`;
   - renommage des entrées natives en `MEDIAS` et `PARAMÈTRES`;
   - placeholders fonctionnels temporaires en attente du branchement complet des écrans.

### Validation technique
1. Lint PHP OK :
   - `inc/admin/menu.php`
   - `inc/admin/bootstrap.php`
2. Front inchangé : HTTP 200 confirmé après branchement admin.
3. Maintenabilité : `inc/admin/menu.php` reste < 300 lignes (270).

### Note de contexte
1. Le test WP-CLI `function_exists('em_site_admin_dashboard_page_slug')` renvoie `missing` en contexte CLI car le bootstrap admin est chargé sous `is_admin()`.
2. Ce comportement est attendu et ne bloque pas le runtime wp-admin.

## Prochain sous-lot Lot B
1. Remplacer les placeholders `TEMPLATES/RUBRIQUES/VLB` par les écrans source réels (Lot C).
2. Aligner le rendu dashboard au pixel plus strictement (structure + composants hub source).
3. Vérifier en session wp-admin authentifiée source vs cible écran par écran.

## Lots C à E - Avancement réalisé

### Lot C - Rubriques / templates
1. Écrans source réels rebranchés pour templates/rubriques et VLB.
2. Import des dépendances fonctionnelles `inc/rubriques`, `inc/shared`, `inc/vlb` et `visual-links-builder`.
3. Intégration des styles nécessaires au fonctionnement admin Rubriques V4 (dont dépendances CSS front techniques).

### Lot D - Modules admin
1. Import du socle modules admin (top-bar, hero/header, stream, social, video, release, cta, contacts/footer) avec pages/settings/partials.
2. Réactivation des assets admin modules CSS/JS et des composants shared nécessaires.
3. Harmonisation progressive de la structure cible avec la source, sans refonte front.

### Lot E - AJAX, permissions, onboarding
1. Rebranchement des accès clients/admin et compatibilité des nouveaux logins avec alias source (`admin-tyson`, `admin-ellene`).
2. Réactivation des écrans login/logout source (`inc/core/login.php`, `inc/core/login-off.php`) et des assets associés.
3. Ajustement branding avatar/logo admin pour parité visuelle (fallback site icon si ressource absente).

## Correctifs de parité appliqués en fin d'import
1. BACK/FRONT : correction orchestrateur de rendu pour faire respecter en front la visibilité/squelette définis en back-office.
2. Apparence : restauration des métadonnées thème et du screenshot pour supprimer la carte vide/"Anonymous".
3. Environnement : confirmation que le site actif em-site (`localhost:8290`) écrit dans la nouvelle base `em_site_bdd`.

## Vérifications et état de clôture
1. Aucune erreur détectée sur les fichiers clés modifiés lors des contrôles ciblés.
2. Le stack legacy em-wp (`8190`) a été arrêté sans suppression des conteneurs pour isoler la recette em-site.
3. Étape suivante validée utilisateur : lancer flow GH de tout le lot "admin importé", puis démarrer le refactor admin (structure, arbo, back).
