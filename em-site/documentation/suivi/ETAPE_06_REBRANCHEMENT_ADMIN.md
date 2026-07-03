# Étape 6 - Rebranchement ADMIN à l'identique

## Statut
- 🟢 Démarrée
- Horodatage (Paris) : 2026-07-03 13:00:35
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
