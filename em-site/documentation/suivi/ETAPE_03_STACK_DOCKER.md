# Étape 3 - Mise en place stack Docker em-site-local

## Horodatage (Paris)
1. Dernière mise à jour : 2026-07-02 17:29:24.

## Avancement
- [x] Dossier docker préparé
- [x] Stack Docker opérationnelle

## Statut
Terminé.

## Objectif
docker-compose dédié opérationnel sur 8290/8291/55320.

## Critère de sortie
Accès front + wp-admin + phpMyAdmin OK.

## Points à ne pas oublier
1. Ne pas partir d'un dossier docker vide au moment de l'étape.
2. Reprendre les fichiers docker utiles depuis la source officielle, puis adapter proprement à em-site-local.
3. Vérifier la présence minimale : docker-compose.yml, .env.example, scripts PowerShell.
4. Adapter les ports et les noms de service au périmètre em-site.

## Mise à jour temps réel
1. Stack Docker em-site-local préparée avec ports dédiés : WP 8290, PMA 8291, DB 55320.
2. Fichiers créés : docker-compose.yml, .env.example, wp-config.docker.php, php-uploads.ini, scripts PowerShell.
3. Stack démarrée : em-site-local, em-site-local-db, em-site-local-pma, em-site-local-cli.
4. Contrôles d'accès validés : front 200, phpMyAdmin 200, wp-admin accessible (redirection HTTP 301 attendue).
5. Validation manuelle utilisateur confirmée : connexion phpMyAdmin em-site-local effectuée.
6. Validation manuelle utilisateur confirmée : login admin OK pour les 2 comptes renommés.
7. Front blanc confirmé comme attendu à ce stade: thème `em-site` encore en fichiers placeholders vides (`index.php`, `front-page.php`, `functions.php`).
8. Règle Git alignée avec la source actuelle : dossier `em-site/docker/` ignoré au complet (aucun fichier Docker versionné).
