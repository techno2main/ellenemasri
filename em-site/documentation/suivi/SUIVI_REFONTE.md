# Suivi Refonte em-site

## Horodatage temps réel (Paris)
1. Fuseau de référence : Europe/Paris.
2. Format obligatoire : YYYY-MM-DD HH:mm:ss.
3. Dernière mise à jour : 2026-07-07 14:12:20.

## Règles de suivi
1. Une étape = un objectif concret vérifiable.
2. Chaque étape contient statut, date, décisions, preuves.
3. Pas de mélange entre actions faites et idées futures.
4. Un fichier dédié par étape dans ce dossier.
5. Exécution par phases avec sous-étapes claires.
6. Suivi en temps réel pendant l'exécution.
7. Mise à jour des documents de suivi après chaque validation d'étape.
8. Flow GitHub uniquement sur demande explicite : commits atomiques par logique métier, puis push sur la branche dédiée.
9. Toute action de suivi est horodatée en heure de Paris.
10. Mot-clé MAJ docs : scanner tous les documents de suivi concernés par l'avancement en temps réel, puis les mettre à jour.
11. Mot-clé flow GH : lancer le process GitHub selon les règles actives du chantier, uniquement sur demande explicite.
12. RÈGLE D'OR : aucun flow GH sans vérification et finalisation préalable de la MAJ docs.
13. Tests obligatoires entre lots : aucun passage au lot/étape suivant sans tests exécutés et validés.

## Journal d'avancement

### 2026-07-07 14:12:20 (Paris)

- Migration effective des assets partagés admin `assets/admin/css/shared` + `assets/admin/js/shared` vers `assets/admin/shared/css` + `assets/admin/shared/js`, avec recâblage des enqueues.
- Migration effective des assets front partagés `assets/front/css/core` + JS core/slider vers `assets/front/shared/css` + `assets/front/shared/js`.
- Suppression du dossier fantôme `assets/front/css/rubriques-v4` et recâblage des dépendances front/admin vers les nouveaux chemins.
- Purge du namespace legacy gate `ellene` côté admin et bascule vers le namespace `client`.
- Correction de régression sur la logique d'accès par admin connecté: mapping actif rétabli sur `admin-ellene` (restreint) et `admin-tyson` (complet).
- Hotfix CSS admin: correction des imports cassés dans `assets/admin/shared/css/hub-cards.css` et `assets/admin/shared/css/module-common.css` pour restaurer l'alignement logo/pseudo et le rendu BO.

### 2026-07-07 12:56:24 (Paris)

- Standardisation des composants BO partagés vers `inc/admin/shared/components/<composant>/`.
- Migration effective des composants partagés suivants : color-picker, scotchs-control, style-panel, hub-cards, hub-breadcrumb.
- Recâblage des `require_once` admin vers les nouveaux chemins centralisés.
- Harmonisation du composant Scotchs pour un rendu identique entre Slider et Top-bar (ordre color picker puis case à cocher, même logique de placement).
- Ajout de classes neutres de composant Scotchs (`em-v4-scotchs-control__color`, `em-v4-scotchs-control__check`) côté PHP, JS et CSS pour éviter les divergences contextuelles.
- Renommage explicite des fichiers du builder Rubriques (préfixe `builder-...`) pour améliorer la lisibilité de reprise développeur.
- Recâblage complet des includes builder après renommage (bootstrap Rubriques, item render, script principal et dépendances).
- Vérifications effectuées : recherche de références legacy résiduelles, diagnostics d'erreurs sur les fichiers modifiés (aucune erreur remontée par l'outil de diagnostic).


