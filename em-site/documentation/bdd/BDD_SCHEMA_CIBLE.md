# Schéma BDD Cible - em_site

## Objectif
Lister les tables nécessaires pour démarrer proprement sur une base neuve.

## Base cible
1. Nom base : em_site.
2. Accès principal : phpMyAdmin Docker.
3. Ports stack locale : WP 8290, PMA 8291, DB 55320.

## Tableaux de référence
1. Tables WordPress standard : conservées (users, posts, postmeta, options, terms, term_taxonomy, term_relationships, comments, commentmeta).
2. Tables métier dédiées : à confirmer selon choix final d’implémentation.
3. Option recommandée phase 1 : stocker le métier dans options WP, sans table custom.
4. Option phase 2 : introduire des tables custom uniquement si volume/performance le justifie.

## Tables métier à prévoir logiquement
1. Templates actifs et registre.
2. Squelettes de sections.
3. Instances par section.
4. Réglages de visibilité.
5. Réglages mode slide multi-items.

## Dossier bdd - usage
1. scripts : scripts SQL d’installation/migration.
2. backup : sauvegardes datées.
3. export : exports de contrôle et snapshots.

## Points à valider
1. Préfixe final des tables WordPress (proposition : ems_).
2. Stratégie initiale : options-only ou tables custom dès le départ.