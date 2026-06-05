# Ellene Local Docker Stack

Stack Docker isolée pour le chantier Ellene, sans toucher aux conteneurs existants.

## Services
- `ellene-local-db` : MariaDB, port local `3308`
- `ellene-local-wp` : WordPress, port local `8090`
- `ellene-local-pma` : phpMyAdmin, port local `8091`

## Démarrage
Depuis `docker/ellene-local` :

```bash
docker compose up -d
```

## URLs locales
- Site WordPress: http://localhost:8090
- phpMyAdmin: http://localhost:8091

## Base de données
- Nom: `wp_ellene_local`
- Utilisateur: `ellene`
- Mot de passe: `ellene`
- Root: `root`

Le dump de production est monté automatiquement dans le conteneur DB via :
- `../../documentation/migration-ovh/ubocrhyem26db.sql.gz`

L’import se fait à la première initialisation du volume DB.

## Config WordPress
La stack utilise une config dédiée locale:
- `wp-config.docker.php`

Cette config évite de toucher au `wp-config.php` du dépôt.

## Points de sécurité
- Aucun conteneur existant n’est réutilisé.
- Les ports sont séparés de XAMPP et des autres stacks.
- La base est isolée dans un volume Docker dédié.
