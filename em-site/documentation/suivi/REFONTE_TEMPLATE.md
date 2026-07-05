# Refonte template unique avec multi-items

Date: 2026-07-05
Périmètre: em-site/wp/wp-content/themes/em-site
Statut: cadrage fonctionnel et technique, aucune action de code

## 1) Objectif validé

Mettre en place un modèle cible avec:

- une seule page Template / Squelette (unique)
- des noms de rubriques génériques
- pour chaque rubrique, un mode d'affichage au choix:
	- mode item unique
	- mode multi-items avec timer et navigation manuelle
- aucune notion de templates multiples (plus de duplication, nouveau template, variantes de template)

Rubriques cibles demandées:

- TOP-BAR
- HEADER
- STREAM
- SOCIAL
- VIDEO
- RELEASE
- CTA
- CONTACT
- ABOUT
- FOOTER

## 1.1 Rôle final de la page Template

La page Template unique doit permettre:

- Afficher / masquer une rubrique sur le front
- Changer l'ordre des rubriques sur le front
- Ajouter une nouvelle rubrique disponible
- Enlever une rubrique existante
- Prévisualiser le template par rubrique ou en global
- Définir, pour chaque rubrique, le système d'items: fixe ou multi

## 1.2 Fonctionnalités déjà existantes (socle actuel)

En complément du rôle final ci-dessus, la page Squelette dispose déjà de:

- Ouverture/fermeture d'une rubrique sans rechargement de page, avec synchronisation de l'URL (paramètre open)
- Chargement AJAX des pickers de rubrique avec cache local pour accélérer la navigation
- Lien APERÇU du site public (nouvel onglet)
- Aperçu d'une rubrique dans le wireframe via l'icône œil
- Auto-aperçu d'une rubrique lors de l'ouverture du panneau
- Choix de la position d'insertion lors de l'ajout d'une rubrique au squelette
- Gestion spécifique du HEADER en composite (hero/slider, matrice, position, apparence partagée)
- Raccourci d'édition d'un item depuis le squelette vers la page RUBRIQUES

Note produit:

- Avec un template unique, la notion LIVE n'est plus portée par le template.
- La notion LIVE est déplacée au niveau des rubriques (état/impact rubrique par rubrique).

## 2) Réponses concrètes à tes questions

### 2.0 Décision produit verrouillée

- Les noms de rubriques du template restent génériques: TOP-BAR, HEADER, STREAM, SOCIAL, VIDEO, RELEASE, CTA, CONTACT, ABOUT, FOOTER.
- Le template est unique: une seule structure commune, aucune gestion de templates différents.

### 2.1 Si on renomme les rubriques, les slugs seront-ils mis à jour correctement?

Réponse courte: oui, avec une règle unique pour toutes les rubriques et tous les items, sans distinction natif/custom.

Détail:

- Une seule politique de slug doit s'appliquer:
	- slug de rubrique: stable, générique et indépendant du nom d'artiste
	- slug d'item: dérivé du label, unique, et mis à jour lors du renommage
	- slug de template: unique et commun (plus de déclinaisons métier)

- Lors d'un renommage, la propagation attendue est globale:
	- registre template (unique)
	- options de rubriques
	- plan/squelette
	- visibilité
	- template actif et contexte d'édition
	- références d'items déjà branchés

- Aucun comportement différencié natif/custom n'est visé dans la cible.

Convention cible validée:

- aucune référence em_wp ou em_wp_v4 dans le modèle final
- aucun suffixe versionné dans le nommage métier
- aucune branche fonctionnelle séparée natif/custom pour les slugs

## 3) État actuel vs cible (template unique)

État actuel confirmé:

- Le squelette Rubriques est déjà centralisé dans une page sommaire.
- Les labels de rubriques intégrées sont déjà génériques (TOP-BAR, HEADER, etc.).

Écart à fermer pour ta cible:

- Uniformiser la stratégie single item vs multi-items par rubrique, avec une UX homogène.
- Finaliser le nettoyage du wording métier et technique résiduel.
- Poser une politique claire de slug canonique pour éviter les divergences futures.

## 4) Préparation refonte (sans exécution)

### Phase A - Contrat fonctionnel unique

- Définir un contrat commun rubrique:
	- mode = single | multi
	- item par défaut
	- paramètres multi: autoplay, delay, navigation manuelle, ordre
- Définir le comportement front attendu pour chaque rubrique en mode multi.

### Phase B - Modèle de données unifié

- Unifier le stockage autour d'une structure unique par rubrique:
	- item courant
	- collection d'items
	- mode d'affichage
	- configuration de défilement
- Prévoir une migration des données existantes compatible avec l'existant.

### Phase C - Interface admin unique

- Garder une seule entrée Template/Squelette.
- Dans chaque rubrique, fournir:
	- un switch clair item unique / multi
	- la gestion de la liste d'items (ordre, renommage, suppression)
	- les réglages timer + clic manuel

Ce bloc Interface admin couvre explicitement les 6 actions produit validées en section 1.1.

### Phase D - Nettoyage technique et nomenclature

- Inventorier et normaliser les éléments de nomenclature technique:
	- fonctions/action hooks
	- classes CSS/JS
	- options WordPress
	- assets et dossiers
- Décider ce qui doit être:
	- renommé immédiatement
	- gardé en alias de compatibilité temporaire
	- déprécié avec fenêtre de suppression

## 5) Décisions proposées pour la suite

1. Valider la règle de slug canonique:
	 - rubriques: top-bar, header, stream, social, video, release, cta, contact, about, footer
	 - items: slug dérivé du label, stable après renommage contrôlé
2. Valider le contrat de mode d'affichage par rubrique: single vs multi.
3. Valider la stratégie de transition de nomenclature:
	 - nomenclature métier uniforme dans toute l'interface
	 - nomenclature technique alignée avec le modèle final
4. Valider l'arrêt du multi-template:
	 - plus de duplication de templates
	 - plus de création de nouveaux templates
	 - une seule page Template/Squelette commune

## 6) Position finale au 2026-07-05

- Le besoin est faisable dans l'architecture actuelle.
- Le modèle final attendu est un template unique avec rubriques génériques uniquement.
- Le nettoyage de la nomenclature technique reste un chantier explicite de refonte.
- Le modèle cible retenu est unifié: une seule logique de slug et aucun découpage natif/custom.

## 7) PA détaillé par phase (refonte gestion Template)

### Phase 0 - Cadrage final et gel du périmètre

Objectif:

- Verrouiller le contrat fonctionnel du template unique avant tout dev.

Actions:

- Valider la liste finale des rubriques gérées.
- Valider les 6 actions cœur de la page Squelette (visibilité, ordre, ajout, retrait, preview, mode single/multi).
- Valider la terminologie unique (single/multi, rubrique, item).

Livrables:

- Spécification fonctionnelle gelée dans ce document.

Critères de sortie:

- Aucune ambiguïté produit restante.
- Aucun nouveau besoin "hors périmètre" non arbitré.

### Phase 1 - Contrat de données cible

Objectif:

- Définir la structure de données unique supportant single/multi pour toutes les rubriques.

Actions:

- Définir le schéma cible par rubrique: mode, item actif, liste d'items, réglages multi.
- Définir les clés de stockage stables (sans variation par ancien template).
- Définir la politique de slug canonique et de propagation au renommage.

Livrables:

- Contrat de données versionné (mapping ancien -> nouveau).

Critères de sortie:

- Toutes les rubriques peuvent être représentées avec le même modèle.
- Les règles de slug couvrent création, renommage et suppression.

### Phase 2 - Migration et compatibilité

Objectif:

- Migrer l'existant vers le modèle template unique sans casser le front.

Actions:

- Écrire les scripts/mécanismes de migration de données.
- Prévoir les alias de compatibilité strictement temporaires.
- Définir un plan de rollback technique.

Livrables:

- Procédure de migration exécutable et testable.

Critères de sortie:

- Données historiques reprises sans perte.
- Rollback documenté et validé sur un jeu d'essai.

### Phase 3 - Refonte interface Squelette (admin)

Objectif:

- Livrer l'UI unique qui pilote tout le comportement front rubrique par rubrique.

Actions:

- Implémenter la gestion visibilité/ordre/ajout/retrait sur page unique.
- Implémenter le switch single/multi par rubrique.
- Implémenter les réglages multi (timer, navigation manuelle, ordre des items).
- Conserver les previews (rubrique + global) avec performances stables.

Livrables:

- Page Squelette v2 fonctionnelle.

Critères de sortie:

- Les 6 actions cœur sont opérationnelles sans navigation annexe obligatoire.
- Temps de réponse UI conforme (pas de régression perceptible).

### Phase 4 - Rendu front unifié

Objectif:

- Aligner le rendu public sur le contrat single/multi unifié.

Actions:

- Adapter le pipeline front pour lire le nouveau modèle.
- Uniformiser les comportements d'affichage rubrique par rubrique.
- Garantir l'ordre/visibilité/mode configurés dans Squelette.

Livrables:

- Rendu front branché 100% sur le modèle cible.

Critères de sortie:

- Le front reflète exactement l'état configuré dans Squelette.
- Aucun fallback legacy non maîtrisé sur le parcours principal.

### Phase 5 - Nettoyage technique et nomenclature

Objectif:

- Simplifier le codebase après bascule pour réduire la dette.

Actions:

- Retirer les chemins legacy devenus inutiles.
- Harmoniser la nomenclature des modules/fonctions/assets.
- Supprimer les duplications liées à l'ancien mode multi-template.

Livrables:

- Base technique nettoyée, cohérente et maintenable.

Critères de sortie:

- Réduction mesurable de la complexité (moins de branches de compatibilité).
- Plus de références actives à l'ancien modèle multi-template.

### Phase 6 - Validation QA et recette

Objectif:

- Valider fonctionnellement et techniquement la refonte avant mise en ligne.

Actions:

- Exécuter la recette complète des rubriques (single/multi, ordre, visibilité, preview).
- Tester cas limites: rubrique vide, multi sans item, renommage, retrait puis ajout.
- Vérifier régression admin/front sur desktop et mobile.

Livrables:

- PV de recette et liste des correctifs bloquants/non bloquants.

Critères de sortie:

- 0 bug bloquant en recette.
- Parcours principal validé de bout en bout.

### Phase 7 - Déploiement contrôlé

Objectif:

- Basculer en production avec risque maîtrisé.

Actions:

- Préparer le runbook de déploiement et de rollback.
- Déployer sur environnement de pré-prod puis prod.
- Monitorer erreurs et signaux post-release.

Livrables:

- Mise en production stable de la gestion Template refondue.

Critères de sortie:

- Aucun incident majeur post-déploiement.
- Monitoring stable sur la fenêtre de surveillance.

## 8) Priorités d'exécution

1. Priorité haute: Phases 0, 1, 2 (contrat + migration).
2. Priorité haute: Phases 3, 4 (UI admin + rendu front).
3. Priorité moyenne: Phase 5 (nettoyage technique).
4. Priorité haute: Phases 6, 7 (qualité et déploiement sécurisé).

## 9) Points de vigilance

- Ne pas recoder le front avant validation complète du contrat de données.
- Ne pas supprimer la compatibilité legacy avant validation migration + recette.
- Garder une traçabilité claire des changements de slug.
- Éviter tout retour implicite vers une logique multi-template.
