# ETAPE 09 - REFACTOR JS ADMIN

Date de mise à jour : 2026-07-05 02:47:57

## État

⏳ **En pause (non terminé)**

## Cartographie initiale (constat)

- Inventaire JS admin actuel: 54 fichiers .js.
- Répartition observée: core (1), modules (19), pages (6), shared (13), template (15).
- Points d'entrée principaux confirmés côté enqueue: shared assets, rubriques, hub-cards, template list, template wizard, menu accordion.

## Réalisé (lot 1)

- Mise en place d'un bootstrap runtime partagé: assets/admin/js/core/admin-bootstrap.js.
- Exposition d'une base utilitaire non intrusive (domReady, délégation d'événements).
- Rattachement du bootstrap à la chaîne d'enqueue shared pour préparer la normalisation des scripts suivants.
- Migration de scripts existants vers le runtime partagé (fallback conservé):
	- shared/notice-autodismiss.js
	- shared/menu-accordion.js
	- shared/accordion.js
	- shared/catalog-slug-switch.js
	- modules/header/header.js
	- template/banner.js
	- template/list-delete-confirm.js
	- template/new-template-launcher.js
	- template/template-create-deeplink.js

- Réorganisation du dossier shared en sous-dossiers métiers (sans changement fonctionnel):
	- shared/navigation/* (accordion, menu-accordion, catalog-slug-switch, quit-editing-nav)
	- shared/modals/* (confirm-modal, color-modal, color-picker)
	- shared/preview/* (admin-module-style-preview, hub-sommaire-preview)
	- shared/state/* (module-form-dirty)
	- shared/media/* (slide-sortable)
	- shared/feedback/* (notice-autodismiss)
	- shared/compat/* (class-prefix-compat)

- Mise à jour de tous les chemins d'enqueue PHP vers la nouvelle arborescence shared/*.

- Application ciblée de la règle de taille sur les points signalés:
	- shared/state/module-form-dirty.js réduit à un wrapper léger (21 lignes)
	- shared/modals/color-modal.js réduit à un wrapper léger (15 lignes)
	- logique déplacée vers des fichiers engine dédiés, chargés en dépendance pour éviter toute régression:
		- shared/state/module-form-dirty/engine.js
		- shared/modals/color-modal/engine.js

- Raffinement supplémentaire du découpage color modal:
	- extraction d'utilitaires vers shared/modals/color-modal/helpers.js
	- réduction de shared/modals/color-modal/engine.js à 231 lignes
	- contrôle global shared validé: aucun fichier > 300 lignes

- Purge du dossier modules (aligné avec la logique appliquée sur CSS):
	- suppression des `index.js` vides et non référencés dans chaque sous-module admin
	- conservation des scripts métiers effectivement enqueued (`header.js`, `hero/*/hero.js`, `release.js`, `slider/*/slider.js`, `top-bar.js`)

## Validation finale

- Plus aucune référence legacy vers `assets/admin/js/shared/*.js` à plat dans les enqueues PHP.
- Arborescence shared réorganisée en sous-domaines métiers cohérents.
- Règle de taille respectée sur le périmètre refactoré shared (aucun fichier shared > 300 lignes).
- Vérifications de syntaxe OK sur les fichiers JS/PHP touchés du lot.

## Finalisation (lot 2 - partiel)

- Réalignement structurel validé: le dossier `modules` ne contient plus de code commun (`modules/common` supprimé).
- Réalignement structurel validé: le dossier `modules` ne contient plus de dossier `rubriques`.
- Scripts Rubriques déplacés vers `assets/admin/js/pages/rubriques/*` et enqueues repointés.
- Dépendances JS communes conservées en `shared/*` (slide-sortable, module-style-preview, catalog-slug-switch, module-form-dirty).
- Découpage réel ajouté côté wizard:
	- `wizard/navigation/wizard-navigation-core.js`
	- `wizard/navigation/wizard-navigation-flow.js`
	- `wizard/skeleton/wizard-skeleton-helpers.js`
	- `wizard/skeleton/wizard-skeleton-core.js`
	- wrappers compatibles conservés (`wizard-navigation.js`, `wizard-skeleton.js`)
- Vérifications de syntaxe globales au moment de cette mise à jour: **0 erreur détectée**.

## Points bloquants restants (important)

- La règle des 300 lignes n'est **pas** finalisée sur le wizard.
- Fichiers encore au-dessus de 300 lignes:
	- `assets/admin/js/template/wizard/wizard-draft.js` (1030 lignes)
	- `assets/admin/js/template/wizard/wizard-guide.js` (1281 lignes)
	- `assets/admin/js/template/wizard/wizard-wireframe.js` (729 lignes)
- Conclusion: refactor ETAPE 09 **à revoir entièrement plus tard** sur décision explicite.

## Méthode appliquée (résumé)

1. Cartographier les scripts existants par domaine: core, modules, shared, pages.
2. Définir une arborescence cible stable et explicite (fichiers courts, noms fonctionnels).
3. Déplacer/refactorer par lots atomiques sans casser les points d'entrée existants.
4. Normaliser les conventions (naming, init, events, guards, sélecteurs).
5. Supprimer progressivement les doublons et branches legacy.
6. Vérifier après chaque lot: syntaxe, comportement, console, non-régression écran par écran.
7. Mettre à jour la documentation de suivi en temps réel avec date+heure.

## Étape suivante

- ETAPE 09 reste ouverte et mise en pause.
- Reprise uniquement sur décision explicite pour un nouveau lot complet de découpage wizard (`draft`, `guide`, `wireframe`) avec cible < 300 lignes/fichier.

