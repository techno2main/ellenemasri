## Roadmap de suivi en temps réel

Clôture:
- Déploiement terminé et validé en production le 2026-06-07.
- Site fonctionnel côté admin et front.
- Un micro bug visuel non bloquant reste à traiter plus tard sur le bouton mute/sound du slider hero.

Statut:
- [x] Action 1 - Identifier le commit exact à déployer: `4eaad8e` (dernier commit global de la branche)
- [x] Action 2 - Préparer un package du thème seulement (`_sources/deploy-packages/ellene-wp_4eaad8e_20260607`)
- [x] Action 3 - Vérifier les fichiers à exclure avant upload (node_modules/.git/.copilot-snapshots absents du package)
- [x] Action 4 - Sauvegarde fichiers prod via FileZilla (copie réalisée dans `_sources/backup_wp`)
- [x] Action 5 - Sauvegarde base de données OVH (`_sources/backup_wp/ovh_prod_backup_2026-06-07.sql.gz`)
- [x] Action 6 - Vérifier le rollback avant d'écrire
- [x] Action 7 - Mettre le site en fenêtre de maintenance courte (fenêtre retenue: 2026-06-07 23:08:46, aucune édition admin concurrente pendant la bascule)
- [x] Action 8 - Upload du thème en production
- [x] Action 9 - Vérifier les permissions critiques (passes FileZilla appliquées: dossiers 0755, fichiers 0644)
- [x] Action 10 - Décider s'il faut importer la base locale (décision retenue: oui, écrasement complet de la base prod par une base locale réexportée si nécessaire)
- [x] Action 11 - Importer la base locale en prod (1er import techniquement réussi avec `_sources/dump_base_sql/wp_ellene_local_2026-06-07.sql`, mais non concluant fonctionnellement)
- [x] Action 12 - Vérifier et corriger l'admin après import (réimport avec `_sources/dump_base_sql/wp_ellene_local_OK.sql`: back et front revenus)
- [x] Action 13 - Contrôle front complet desktop (validation utilisateur finale: tout est OK en prod)
- [x] Action 14 - Contrôle front mobile (validation utilisateur finale: tout est OK en prod)
- [x] Action 15 - Contrôle admin ciblé (validation utilisateur finale: tout est OK en prod)
- [ ] Action 16 - Purge cache navigateur/CDN
- [ ] Action 17 - Contrôle console et logs
- [x] Action 18 - Rédiger un CR de bascule
- [x] Action 19 - Procédure rollback (si incident) - prête, non exécutée

Note commit:
- Dernier commit global: `4eaad8e`
- Dernier commit runtime thème/admin: `8fd03fc` (inclut l'affichage discret du nom du thème en admin)
- Delta `8fd03fc..4eaad8e`: documentation uniquement (`_sources/maj-prod-wp.md`), aucun changement du thème.

# PA détaillé - Bascule version locale finalisée vers production OVH

Date: 2026-06-07

Objectif:
- Déployer la version WordPress locale finalisée vers le site de production OVH ellenemasri.com sans perte de données.

Contexte confirmé:
- Site de production: ellenemasri.com
- Admin production: ellenemasri.com/wp/wp-admin/
- Arborescence distante visible: /www/wp/
- Base OVH: phpMyAdmin OVH (route fournie)
- Outil FTP: FileZilla prêt

Principe d'exécution:
- 1 action = 1 validation avant de passer à l'action suivante.
- Ne jamais faire upload fichiers + import SQL en même temps.
- Toujours disposer d'un rollback complet avant la moindre écriture en production.

---

## Phase A - Préparation locale (sans impact prod)

### Action 1 - Figer la version à livrer
But:
- Identifier le commit exact à déployer pour éviter un décalage entre code testé et code livré.

Action unique:
- Noter le hash du dernier commit stable sur la branche feature locale (celui validé fonctionnellement).

Résultat attendu:
- Un hash de référence est inscrit dans votre note de déploiement.

Validation:
- Le hash choisi correspond à la version testée (admin + front validés).

---

### Action 2 - Préparer un package du thème seulement
But:
- Déployer uniquement le thème custom, pas le coeur WordPress.

Action unique:
- Créer une copie locale du dossier:
	- wp/wp-content/themes/ellene-wp

Résultat attendu:
- Un dossier source propre prêt à être envoyé en FTP.

Validation:
- Le dossier contient au minimum: functions.php, style.css, inc/, template-parts/, assets/, visual-links-builder/.

---

### Action 3 - Vérifier les fichiers à exclure avant upload
But:
- Éviter d'envoyer des fichiers inutiles en production.

Action unique:
- Retirer du package toute donnée locale/dev inutile (si présente):
	- node_modules/
	- .git/
	- .copilot-snapshots/
	- fichiers temporaires locaux

Résultat attendu:
- Package allégé, orienté runtime production.

Validation:
- Aucun dossier outil/dev lourd n'est prêt à partir en FTP.

---

## Phase B - Sauvegardes production (obligatoire avant modification)

### Action 4 - Sauvegarde fichiers prod via FileZilla
But:
- Pouvoir restaurer immédiatement l'état actuel du site.

Action unique:
- Télécharger depuis OVH:
	- /www/wp/wp-content/themes/mayami
	- /www/wp/wp-content/uploads (au minimum la partie critique si volumineux)

Résultat attendu:
- Une sauvegarde locale horodatée des fichiers de prod.

Validation:
- Les dossiers téléchargés sont complets et lisibles localement.

---

### Action 5 - Sauvegarde base de données OVH
But:
- Garantir un rollback base en cas d'erreur après déploiement.

Action unique:
- Export complet SQL depuis phpMyAdmin OVH, avec paramètres stricts:
	1. Ouvrir phpMyAdmin OVH et sélectionner la base de production (colonne gauche).
	2. Aller dans l'onglet Exporter.
	3. Méthode: Personnalisée.
	4. Format: SQL.
	5. Tables: toutes les tables de la base (aucune exclusion).
	6. Sortie:
		- Enregistrer la sortie dans un fichier: oui.
		- Compression: gzip (recommandé) ou aucune si besoin.
	7. Options de création d'objets:
		- Ajouter l'instruction DROP TABLE / VIEW / PROCEDURE / FUNCTION / EVENT / TRIGGER: activé.
		- Ajouter l'instruction CREATE TABLE: activé.
		- IF NOT EXISTS: activé.
	8. Options de données:
		- Étendu inserts (multi-lignes): activé.
		- Transactions: activé si disponible.
		- Désactiver la vérification des clés étrangères: activé si disponible.
	9. Jeu de caractères du fichier: utf8mb4.
	10. Lancer l'export et enregistrer le fichier dans un dossier local horodaté.

Script SQL de contrôle rapide (optionnel) dans l'onglet SQL avant export:
- SELECT DATABASE() AS base_active;
- SHOW TABLES;

Résultat attendu:
- Un fichier SQL complet de la base prod (horodaté).

Validation:
- Le fichier SQL n'est pas vide et contient bien les tables wp_ attendues.

---

### Action 6 - Vérifier le rollback avant d'écrire
But:
- Confirmer qu'un retour arrière est possible sans improvisation.

Action unique:
- Noter noir sur blanc:
	- emplacement sauvegarde fichiers: `C:\xampp\htdocs\web-am\dev.tad\MyWebsites\ellenemasri.com\www\_sources\backup_wp\wp`
	- emplacement sauvegarde SQL: `C:\xampp\htdocs\web-am\dev.tad\MyWebsites\ellenemasri.com\www\_sources\backup_wp\ovh_prod_backup_2026-06-07.sql.gz`
	- ordre de restauration:
		1. Restaurer d'abord les fichiers de prod sauvegardés via FileZilla vers `/www/wp/` si le site casse après l'upload.
		2. Vérifier immédiatement si le front et l'admin redeviennent accessibles.
		3. Restaurer ensuite la base SQL OVH uniquement si l'incident vient des données/options et pas seulement des fichiers.
		4. Après import SQL, revérifier l'admin, le front et les pages critiques.

Résultat attendu:
- Plan de rollback prêt, clair, exécutable.

Validation:
- Vous savez restaurer en moins de 10 minutes sans chercher des fichiers.

---

## Phase C - Déploiement fichiers thème

### Action 7 - Mettre le site en fenêtre de maintenance courte (option recommandé)
But:
- Réduire les risques d'écriture concurrente pendant la bascule.

Action unique:
- Choisir une fenêtre courte de déploiement (trafic bas) et éviter toute édition admin pendant l'upload.
- Fenêtre retenue pour cette bascule: 2026-06-07 23:08:46.
- Consigne opératoire: ne pas ouvrir d'autre session d'édition admin ni effectuer de sauvegarde de contenu pendant l'upload du thème.

Résultat attendu:
- Pas de modifications de contenu simultanées pendant l'opération.

Validation:
- Aucun autre utilisateur n'édite le site pendant la bascule.

---

### Action 8 - Upload du thème en production
But:
- Déposer la version locale finalisée du thème `ellene-wp` sur la prod, sans détruire la sauvegarde du thème prod historique `mayami`.

Action unique:
- Dans FileZilla, envoyer le package local nettoyé vers:
	- source locale: `_sources/deploy-packages/ellene-wp_4eaad8e_20260607`
	- destination distante: `/www/wp/wp-content/themes/ellene-wp`
- Méthode:
	1. Ne pas écraser le dossier distant `mayami`.
	2. Créer/mettre à jour uniquement le dossier distant `ellene-wp`.
	3. Vérifier que tous les fichiers du package sont transférés sans erreur.

Résultat attendu:
- Le thème `ellene-wp` présent sur le serveur correspond au package local finalisé, tandis que `mayami` reste disponible comme secours immédiat.

Validation:
- Les dates/tailles des fichiers clés du dossier `/www/wp/wp-content/themes/ellene-wp` ont bien changé côté serveur.

---

### Action 9 - Vérifier les permissions critiques
But:
- Éviter les erreurs d'inclusion PHP/lecture assets.

Action unique:
- Contrôler permissions standards:
	- dossiers: 755
	- fichiers: 644
	- procédure FileZilla:
		1. Dans `/www/wp/wp-content/themes/ellene-wp`, clic droit sur un dossier clé (`assets`, `inc`, `template-parts`, `visual-links-builder`) > Droits d'accès au fichier.
		2. Vérifier que la valeur numérique est `755`.
		3. Si nécessaire, appliquer `755` aux dossiers uniquement.
		4. Clic droit sur un fichier clé (`functions.php`, `style.css`, `front-page.php`, `style-compiled.css`) > Droits d'accès au fichier.
		5. Vérifier que la valeur numérique est `644`.
		6. Si nécessaire, appliquer `644` aux fichiers uniquement.
		7. Ne jamais mettre `777`.
		8. Priorité de contrôle: `functions.php`, `front-page.php`, `inc/`, `assets/`, `template-parts/`, `visual-links-builder/`.

Résultat attendu:
- Le serveur peut lire tous les fichiers du thème.

Validation:
- Pas d'erreur permission denied dans les pages admin/front.

---

## Phase D - Alignement base (écrasement volontaire demandé)

### Action 10 - Décider s'il faut importer la base locale
But:
- Figer la stratégie base avant l'écriture SQL.

Action unique:
- Décision utilisateur actée: écrasement complet de la base prod par la base locale.
- En cas d'écart entre la prod et l'état local attendu, réexporter la base locale puis réimporter ce dump frais.

Résultat attendu:
- Stratégie SQL claire, assumée et prête à exécution.

Validation:
- Le dump local est identifié et le backup SQL prod reste disponible pour rollback.

---

### Action 11 - Importer la base locale en prod
But:
- Remplacer totalement l'état des données/options prod par la version locale validée.

Action unique:
- Dans phpMyAdmin OVH:
	1. Sélectionner la base de production.
	2. Ouvrir l'onglet Importer.
	3. Choisir le fichier local `_sources/dump_base_sql/wp_ellene_local_2026-06-07.sql`.
	4. Format: SQL.
	5. Lancer l'import complet et attendre la confirmation sans erreur fatale.

Résultat attendu:
- La base prod contient désormais exactement les données/options de la base locale exportée.

Validation:
- 1er import confirmé par phpMyAdmin, puis réévaluation fonctionnelle avant validation finale.

---

### Action 12 - Vérifier et corriger l'admin après import
But:
- Confirmer que l'import a bien remis en prod l'état local attendu.

Action unique:
- Si le site est encore en maintenance, supprimer ou renommer d'abord le fichier distant `/www/wp/.maintenance` dans FileZilla.
- Attendre 5 a 10 secondes puis recharger l'admin.
- Ouvrir ellenemasri.com/wp/wp-admin/admin.php?page=ellene-wp_landing_options
- Vérifier chaque section clé:
	- TOP-BAR, HERO, SLIDER, STREAM, SOCIAL, VIDEO, RELEASE, CTA, FOOTER
- Si une valeur reste incohérente, la corriger directement dans l'admin et sauvegarder.

Résultat attendu:
- Les options affichées en admin correspondent à l'état local attendu.

Validation:
- Après retrait de `/www/wp/.maintenance`, l'admin redevient accessible puis, après refresh, les valeurs restent persistées et cohérentes avec le front.

---

## Phase E - Contrôles post-déploiement

### Action 13 - Contrôle front complet desktop
But:
- Valider le rendu global utilisateur.

Action unique:
- Vérifier en navigation réelle:
	- chargement home
	- sections visibles
	- liens plateformes Stream
	- VLB publié si utilisé
	- footer et accents

Résultat attendu:
- Aucun bloc cassé ni texte corrompu.

Validation:
- 0 erreur visuelle critique.

---

### Action 14 - Contrôle front mobile
But:
- Vérifier le comportement responsive en production.

Action unique:
- Tester format mobile (barre sticky, scroll, CTA, stream cards).

Résultat attendu:
- Interface utilisable et cohérente sur mobile.

Validation:
- 0 régression bloquante mobile.

---

### Action 15 - Contrôle admin ciblé
But:
- Confirmer que les fixes admin sont bien en prod.

Action unique:
- Vérifier dans l'admin:
	- navbar sticky sections
	- onglet MODULES présent
	- couleur MODULES active bleue
	- label discret du thème actif au-dessus de Tableau de bord
	- section STREAM: la case Active reflète le front

Résultat attendu:
- Admin et front alignés fonctionnellement.

Validation:
- Cas SoundCloud: décoché en admin -> masqué front, et reste décoché après refresh admin.

---

### Action 16 - Purge cache navigateur/CDN
But:
- Éliminer les faux positifs liés au cache.

Action unique:
- Hard refresh navigateur + purge cache éventuel OVH/CDN/plugin cache.

Résultat attendu:
- Les assets récents sont bien servis.

Validation:
- Les changements CSS/JS sont visibles sans ambiguïté.

---

### Action 17 - Contrôle console et logs
But:
- Détecter les erreurs silencieuses.

Action unique:
- Vérifier:
	- console navigateur sur home et admin
	- logs PHP/OVH si accessibles

Résultat attendu:
- Pas d'erreur critique JS/PHP post-déploiement.

Validation:
- 0 erreur bloquante détectée.

---

## Phase F - Clôture et rollback prêt

### Action 18 - Rédiger un CR de bascule
But:
- Garder une traçabilité claire de ce qui a été livré.

Action unique:
- Noter:
	- date/heure
	- commit livré
	- fichiers déployés
	- checks passés
	- anomalies restantes (si présentes)

Résultat attendu:
- Historique de mise en prod exploitable.

Validation:
- Un tiers peut comprendre ce qui a été fait sans contexte oral.

---

### Action 19 - Procédure rollback (à exécuter seulement si incident)
But:
- Revenir rapidement à l'état stable précédent.

Action unique:
- Restaurer d'abord fichiers thème depuis la sauvegarde.
- Si nécessaire ensuite restaurer la base SQL exportée avant bascule.

Résultat attendu:
- Retour à l'état prod antérieur.

Validation:
- Le site redevient fonctionnel comme avant déploiement.

---

## CR de bascule

- Horodatage de clôture: 2026-06-07 23:34:48
- Commit global de référence: `4eaad8e`
- Commit runtime thème/admin: `8fd03fc`
- Fichiers déployés: thème `ellene-wp` vers `/www/wp/wp-content/themes/ellene-wp`
- Sauvegardes confirmées avant écriture:
	- fichiers: `_sources/backup_wp/wp`
	- base: `_sources/backup_wp/ovh_prod_backup_2026-06-07.sql.gz`
- Import SQL initial effectué: `_sources/dump_base_sql/wp_ellene_local_2026-06-07.sql`
- Incident rencontré après premier import: front et admin partiellement vides
- Cause opérationnelle identifiée: dump initial non aligné avec la clé d'options active attendue par le thème
- Correctif opératoire appliqué: réexport local frais puis réimport prod avec `_sources/dump_base_sql/wp_ellene_local_OK.sql`
- Import SQL final retenu comme source de vérité: `_sources/dump_base_sql/wp_ellene_local_OK.sql`
- Correctif front complémentaire déployé ensuite: bouton mute/sound du slider hero corrigé dans `wp/wp-content/themes/ellene-wp/template-parts/sections/slider/index.php`
- Résultat final validé par l'utilisateur:
	- admin OK
	- front OK
	- prod fonctionnelle
- Anomalies restantes connues: aucune bloquante signalée après le correctif final du slider hero
- Rollback: prêt, non exécuté

---

## Checklist ultra-courte opérateur

1. Commit cible validé
2. Backup fichiers prod OK
3. Backup SQL prod OK
4. Upload thème vers /www/wp/wp-content/themes/ellene-wp OK
5. Import SQL local vers prod OK
6. Vérif admin landing OK
7. Vérif front desktop/mobile OK
8. Purge cache: non documentée dans ce runbook
9. Console/logs: non documentés dans ce runbook
10. CR déploiement rédigé
11. Rollback prêt (non exécuté)
