# Préparation MAJ PROD FRONT + BACK

Date de préparation (Paris): 2026-07-20 17:19:49

## 1) Backup local complet prêt

Dump SQL complet (structure + données) généré:
- em-site/documentation/bdd/export/em_site_bdd_export_20260720_171949.sql

Note:
- Le dump a été fait avec mariadb-dump, avec routines, triggers et events.
- Deux anciens fichiers de tentative invalide ont été supprimés.

## 2) Pré-backup obligatoire sur la PROD (avant toute copie)

Objectif:
- Sauvegarder la base PROD complète
- Sauvegarder les fichiers PROD complets

Exemples de commandes (à adapter à l'hébergement):

1. Export SQL PROD
- mysqldump -u PROD_DB_USER -pPROD_DB_PASS --databases PROD_DB_NAME --default-character-set=utf8mb4 --routines --triggers --events --single-transaction > prod_backup_YYYYMMDD_HHMMSS.sql

2. Archive fichiers PROD
- tar -czf prod_files_YYYYMMDD_HHMMSS.tar.gz /chemin/vers/site/wp

## 3) Plan de déploiement PROD de cette version (FRONT + BACK)

Source locale validée:
- em-site/wp

Cible PROD attendue:
- dossier WordPress principal du site

Ordre recommandé:

1. Activer un mode maintenance temporaire
2. Déployer le coeur WordPress 7.0.2
- Copier wp-admin
- Copier wp-includes
- Copier les fichiers racine WP mis à jour
- Ne pas écraser wp-config.php
- Ne pas supprimer le dossier wp-content/uploads
3. Déployer le thème actif FRONT + BACK
- Copier wp-content/themes/em-site
4. Déployer les plugins custom nécessaires (si modifiés)
5. Lancer la mise à niveau base WordPress
- Ouvrir /wp-admin/upgrade.php (ou wp core update-db si WP-CLI dispo)
6. Purger les caches (plugin/CDN/opcache)
7. Désactiver le mode maintenance

## 4) Contrôles post-déploiement (FRONT + BACK)

Front:
- Accueil charge sans erreurs
- Rubrique Release: alignements texte conformes aux réglages admin
- Modules clés visibles (top-bar, hero, stream, social, video, release, cta, footer)
- Liens, médias, couleurs et responsive OK

Back:
- Connexion admin OK
- Pages Rubriques / Template / Releases chargent sans erreur
- Sauvegarde d'un module test OK
- Aucun warning critique dans debug.log

Technique:
- Version core visible en 7.0.2
- Permaliens et URLs OK
- Pas d'erreur PHP fatale

## 5) Rollback prêt si incident

Si incident:
1. Réactiver maintenance
2. Restaurer archive fichiers PROD
3. Restaurer dump SQL PROD
4. Vider les caches
5. Refaire les tests smoke front/back

## 6) Check final avant exécution PROD

- Backup SQL PROD fait et vérifié
- Backup fichiers PROD fait et vérifié
- Fenêtre de maintenance validée
- Accès SSH/FTP opérant
- Plan rollback validé
