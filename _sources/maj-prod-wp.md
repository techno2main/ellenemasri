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
	- /www/wp/wp-content/themes/ellene-wp
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
- Export complet SQL depuis phpMyAdmin OVH.

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
	- emplacement sauvegarde fichiers
	- emplacement sauvegarde SQL
	- ordre de restauration (fichiers puis SQL si nécessaire)

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

Résultat attendu:
- Pas de modifications de contenu simultanées pendant l'opération.

Validation:
- Aucun autre utilisateur n'édite le site pendant la bascule.

---

### Action 8 - Upload du thème en production
But:
- Remplacer la version thème prod par la version locale finalisée.

Action unique:
- Dans FileZilla, envoyer le dossier local ellene-wp vers:
	- /www/wp/wp-content/themes/ellene-wp
- Méthode: écraser uniquement les fichiers du thème.

Résultat attendu:
- Le thème prod correspond au contenu local finalisé.

Validation:
- Les dates/tailles des fichiers clés ont bien changé côté serveur.

---

### Action 9 - Vérifier les permissions critiques
But:
- Éviter les erreurs d'inclusion PHP/lecture assets.

Action unique:
- Contrôler permissions standards:
	- dossiers: 755
	- fichiers: 644

Résultat attendu:
- Le serveur peut lire tous les fichiers du thème.

Validation:
- Pas d'erreur permission denied dans les pages admin/front.

---

## Phase D - Alignement base (uniquement si nécessaire)

### Action 10 - Décider s'il faut importer la base locale
But:
- Ne pas écraser inutilement du contenu prod.

Action unique:
- Choisir un scénario:
	- Scénario A (recommandé): pas d'import global SQL, on garde la base prod et on vérifie les options.
	- Scénario B: import SQL partiel/ciblé si des options critiques manquent.

Résultat attendu:
- Stratégie base choisie avant toute écriture SQL.

Validation:
- Le risque de perte de contenu prod est maîtrisé.

---

### Action 11 - Vérifier les options de landing en prod
But:
- S'assurer que la config admin reflète la version attendue.

Action unique:
- Ouvrir ellenemasri.com/wp/wp-admin/admin.php?page=ellene-wp_landing_options
- Vérifier chaque section clé:
	- TOP-BAR, HERO, SLIDER, STREAM, SOCIAL, VIDEO, RELEASE, CTA, FOOTER

Résultat attendu:
- Les valeurs importantes sont présentes et cohérentes.

Validation:
- Les valeurs affichées correspondent au rendu attendu.

---

### Action 12 - Corriger uniquement les options manquantes (si besoin)
But:
- Ajuster la prod sans import SQL massif.

Action unique:
- Renseigner/sauvegarder dans l'admin les champs manquants ou incohérents.

Résultat attendu:
- Les options sont alignées avec la version validée.

Validation:
- Après refresh admin, les données restent persistées.

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

## Checklist ultra-courte opérateur

1. Commit cible validé
2. Backup fichiers prod OK
3. Backup SQL prod OK
4. Upload thème vers /www/wp/wp-content/themes/ellene-wp OK
5. Vérif admin landing OK
6. Vérif front desktop/mobile OK
7. Purge cache OK
8. Console/logs OK
9. CR déploiement rédigé
10. Rollback prêt (non exécuté)
