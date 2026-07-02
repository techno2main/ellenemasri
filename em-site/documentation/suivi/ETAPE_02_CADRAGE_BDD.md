# Étape 2 - Cadrage BDD cible

## Horodatage (Paris)
1. Dernière mise à jour : 2026-07-02 17:31:34.

## Avancement
- [x] Cadrage BDD démarré
- [x] Règles BDD validées

## Statut
Terminé.

## Objectif
Figer BDD_SCHEMA_CIBLE.md et conventions scripts/backup/export.

## Critère de sortie
Base em_site installable avec règles validées.

## Mise à jour temps réel
1. Cadrage BDD simplifié acté : reprise stricte depuis la source officielle stable (uniquement V4), sans brainstorming de nouveau schéma.
2. Dump complet structure + données exporté : em_wp_bdd_v4_full_2026-07-02_1652_sql_gz.sql.gz.
3. Référence V4 de l'étape 2 verrouillée dans le dossier bdd/export.
4. Contrôle de complétude du dump effectué : structure + données valides, tables exportées conformes à la source Docker.
5. Import test validé dans em_site_bdd + contrôles post-import (tables, volumes, options critiques).
6. Correction post-import appliquée : `template` et `stylesheet` alignés sur `em-site` pour cohérence avec le thème cible.
7. Réimport propre relancée après purge manuelle des tables via phpMyAdmin (préparation utilisateur confirmée).
8. Contrôle post-réimport validé : 12 tables `wpem_*`, volumes minimaux inchangés (options 309, posts 31, users 2).
9. Correction immédiate post-réimport appliquée : `home` et `siteurl` réajustés sur `http://localhost:8290` (source importée en 8190).
