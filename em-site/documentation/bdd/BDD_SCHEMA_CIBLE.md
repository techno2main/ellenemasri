# Schéma BDD Cible - em_site

## Objectif
Cadrer une reprise stricte depuis la source officielle stable (uniquement V4), sans refonte de schéma à ce stade.

## Source et cible
1. Source officielle : base stable actuelle (uniquement V4).
2. Base cible locale : em_site.
3. Accès principal : phpMyAdmin Docker.
4. Ports stack locale : WP 8290, PMA 8291, DB 55320.

## Stratégie de reprise (phase 2)
1. Importer la base officielle telle quelle dans l'environnement local em-site.
2. Conserver la structure WordPress standard de la source sans redesign.
3. Ne pas ouvrir de brainstorming sur un nouveau modèle de données à cette étape.
4. Limiter les adaptations aux besoins techniques strictement nécessaires au lancement local.

## Périmètre fonctionnel à reprendre depuis la source
1. Données WordPress standard (users, posts, postmeta, options, terms, term_taxonomy, term_relationships, comments, commentmeta).
2. Données métier actuellement utilisées par l'admin personnalisé et le front personnalisé.
3. Réglages templates/rubriques/ordre/visibilité/slide tels qu'ils existent dans la source officielle.

## Dossier bdd - usage
1. scripts : scripts SQL d’installation/migration.
2. backup : sauvegardes datées.
3. export : exports de contrôle et snapshots.

## Export de référence retenu
1. Fichier : em-site/documentation/bdd/export/em_wp_bdd_v4_full_2026-07-02_1652_sql_gz.sql.gz
2. Type : dump complet structure + données.
3. Compression : gzip.

## Validation du dump exporté
1. Présence du fichier : validée.
2. Taille du fichier : 556726 octets.
3. Dernière modification du fichier (Paris) : 2026-07-02 17:00:29.
4. Structure détectée : 12 CREATE TABLE.
5. Données détectées : INSERT présents, dont wpem_options, wpem_posts, wpem_users.
6. Cohérence source Docker : 12 tables source = 12 tables exportées, aucune table manquante.

## Points à valider
1. Fichier d'export SQL exact retenu comme référence V4 stable : validé.
2. Procédure d'import locale reproductible dans em-site : validée (import gzip dans em-site-local-db).
3. Liste minimale des vérifications post-import (admin/front/options critiques) : validée.

## Vérifications post-import effectuées
1. Tables présentes après import : 12 tables `wpem_*`.
2. Volumes minimaux contrôlés : options 309, posts 31, users 2.
3. Options critiques alignées sur em-site-local : `home` et `siteurl` = http://localhost:8290.