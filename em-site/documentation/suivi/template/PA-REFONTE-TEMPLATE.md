# PA - Refonte Gestion Template

Date: 2026-07-05
Horodatage précis (Paris): 2026-07-05 19:25:07
Périmètre: em-site/wp/wp-content/themes/em-site
Branche: feature/refonte-template-gestion
Statut: Implémentation admin template unique en cours (jalon UI atteint)

Règle de suivi: toujours inclure un horodatage précis temps réel (date + heure Paris) à chaque mise à jour.

## 0) Cadrage du PA

- Objectif global: livrer une gestion Template unique (rubriques génériques) avec mode single/multi par rubrique.
- Contraintes: pas de retour à une logique multi-template, migration maîtrisée, non-régression front/admin.
- Définition du succès: configuration dans Squelette = rendu front identique, avec 0 bug bloquant en recette.

## 1) Phase 0 - Cadrage final et gel du périmètre

### Étape 0.1 - Valider le périmètre fonctionnel final

Sous-étape 0.1.1:

- Valider la liste de rubriques existante (référentiel actuel): TOP-BAR, HEADER, STREAM, SOCIAL, VIDEO, RELEASE, CTA, CONTACT, ABOUT, FOOTER.
- Règle: cette liste est évolutive dans le temps et mise à jour selon les besoins validés.
- Préciser explicitement que HEADER est une rubrique composite contenant HERO et/ou SLIDER, mais incluse dans le même flux single/multi.

Sous-étape 0.1.2:

- Confirmer les 6 actions cœur de la page Squelette:
- afficher/masquer
- ordonner
- ajouter
- retirer
- preview rubrique/global
- choisir le mode single/multi

Sous-étape 0.1.3:

- Confirmer la règle LIVE: portée au niveau rubrique (plus au niveau template).

### Étape 0.2 - Valider le vocabulaire et les règles transverses

Sous-étape 0.2.1:

- Geler la terminologie: template unique, rubrique, item, mode single/multi.

Sous-étape 0.2.2:

- Geler les règles de slug de haut niveau: slug canonique, propagation au renommage, traçabilité.

Sous-étape 0.2.3:

- Geler la structure documentaire: cadrage dans REFONTE_TEMPLATE, exécution dans PA-REFONTE-TEMPLATE.

### Étape 0.3 - Go/No-Go vers Phase 1

Sous-étape 0.3.1:

- Vérifier qu'aucun point produit n'est ambigu.

Sous-étape 0.3.2:

- Vérifier qu'aucun point de périmètre n'est en attente.

Sous-étape 0.3.3:

- Valider officiellement le passage en Phase 1.

Livrables Phase 0:

- Cadrage gelé et validé.

Critères de sortie Phase 0:

- 100% des règles fonctionnelles validées.
- 0 point bloquant non arbitré.

Décision de passage:

- ✅ Go Phase 1 validé le 2026-07-05 18:06:03 (Paris).

## 2) Phase 1 - Contrat de données cible

### Étape 1.1 - Définir le modèle canonique rubrique

Sous-étape 1.1.1:

- Définir les champs: rubrique_slug, mode, active_item_slug, items[], multi_settings.
- Définir le sous-contrat spécifique HEADER: matrix, position, hero_item_slug, slider_item_slug (HEADER = HERO et/ou SLIDER).

Sous-étape 1.1.2:

- Définir les defaults par rubrique.

Sous-étape 1.1.3:

- Définir les validations (mode autorisé, item actif valide, ordre des items).

#### Détail complet Étape 1.1 (version de travail)

##### 1.1.A Schéma canonique rubrique (toutes rubriques)

```
rubrique_state {
	rubrique_slug: string,            // ex: top-bar, stream, video
	enabled: boolean,                 // visible/masquée sur le front
	position: integer >= 1,           // ordre d'affichage front
	mode: "single" | "multi",
	active_item_slug: string | null,  // requis en single
	items: [
		{
			item_slug: string,
			label: string,
			enabled: boolean,
			rank: integer >= 1
		}
	],
	multi_settings: {
		autoplay: boolean,
		delay_ms: integer,
		manual_nav: boolean,
		loop: boolean
	}
}
```

##### 1.1.B Sous-contrat HEADER (rubrique composite dans le flux standard)

```
header_state {
	rubrique_slug: "header",
	enabled: boolean,
	position: integer >= 1,
	mode: "single" | "multi",
	active_item_slug: string | null,
	items: [
		{
			item_slug: string,
			label: string,
			enabled: boolean,
			rank: integer >= 1,
			composite: {
				matrix: "hero" | "slider" | "hero_slider",
				position_mode: "hero_left" | "slider_left",
				hero_item_slug: string | null,
				slider_item_slug: string | null,
				shared_appearance: {
					bg_color: string | null,
					bg_image_id: integer | null,
					bg_image_url: string | null
				}
			}
		}
	],
	multi_settings: {
		autoplay: boolean,
		delay_ms: integer,
		manual_nav: boolean,
		loop: boolean
	}
}
```

Règle:

- HEADER est inclus dans le flux standard single/multi.
- En HEADER, chaque item est un composite HERO et/ou SLIDER.

##### 1.1.C Defaults (v1 cible)

- enabled: true pour toutes les rubriques présentes dans le squelette.
- mode par défaut (toutes rubriques): single.
- active_item_slug (single): premier item enabled par rank.
- multi_settings.autoplay: true.
- multi_settings.delay_ms: 5000.
- multi_settings.manual_nav: true.
- multi_settings.loop: true.
- HEADER.matrix par défaut: hero_slider.
- HEADER.position_mode par défaut: hero_left.

##### 1.1.D Validations obligatoires

- rubrique_slug non vide et canonicalisé.
- mode autorisé: single ou multi (toutes rubriques).
- active_item_slug requis si mode=single.
- active_item_slug doit exister dans items[] et être enabled.
- rank items unique par rubrique.
- position rubrique unique dans le squelette.
- delay_ms borné: min 1000, max 120000.
- si mode=multi, au moins un item enabled requis.
- si rubrique_slug=header et matrix=hero, hero_item_slug requis.
- si rubrique_slug=header et matrix=slider, slider_item_slug requis.
- si rubrique_slug=header et matrix=hero_slider, hero_item_slug et slider_item_slug requis.

##### 1.1.E Cas limites à couvrir

- Rubrique sans item actif en mode single.
- Passage single -> multi avec item actif supprimé.
- Passage multi -> single avec plusieurs items enabled.
- Ordre des items incohérent (doublons de rank).
- Rubrique masquée puis réaffichée (état conservé).
- HEADER hero seul sans slider.
- HEADER slider seul sans hero.
- HEADER hero+slider avec inversion de position.
- HEADER sans item requis selon matrix (erreur bloquante).

##### 1.1.F Règles de résolution front

- Le front applique d'abord enabled+position rubrique.
- Puis applique mode rubrique (single/multi) sur toutes les rubriques.
- En single: rendu de active_item_slug uniquement.
- En multi: rendu items enabled ordonnés par rank + règles multi_settings.
- Pour HEADER: chaque item rendu suit son composite matrix/position_mode et ses items HERO/SLIDER.

##### 1.1.G Critères d'acceptation Étape 1.1

- Un payload unique couvre toutes les rubriques, y compris HEADER.
- Le composite HEADER est modélisé sans créer un flux parallèle.
- Les validations bloquantes sont définies pour 100% des cas limites listés.
- Les defaults sont définis pour 100% des champs requis.

### Étape 1.2 - Définir le contrat de slug

Sous-étape 1.2.1:

- Règles de génération slug rubrique.

Sous-étape 1.2.2:

- Règles de génération slug item.

Sous-étape 1.2.3:

- Règles de propagation au renommage (registre, options, plan, visibilité, références d'items).

#### Détail complet Étape 1.2 (décisions validées)

##### 1.2.A Format canonique des slugs

- Format imposé: kebab-case ASCII strict.
- Caractères autorisés: a-z, 0-9, tiret.
- Accents interdits dans les slugs.
- Espaces et séparateurs sont normalisés en tirets.

##### 1.2.B Règle de renommage item

- Si le label item change, le slug item est recalculé automatiquement.
- Si le nouveau slug est déjà utilisé: afficher "slug déjà pris" et demander un nouveau nom.
- Aucune suffixation automatique (-2, -3...) n'est appliquée.

##### 1.2.C Règle de renommage rubrique

- Le slug de rubrique est modifiable.
- Toute modification de slug rubrique déclenche une propagation automatique globale.

##### 1.2.D Portée de propagation automatique (rubrique/item)

- Instances actives de rubrique.
- Ordre et visibilité du squelette.
- Previews et cache admin.
- Ancres et URLs internes.
- Références croisées dans le stockage fonctionnel.

##### 1.2.E Validations obligatoires

- Un slug vide est refusé.
- Un slug hors format canonique est refusé.
- Un slug en collision est refusé avec retour utilisateur explicite.
- Toute propagation partielle est considérée comme erreur bloquante.

##### 1.2.F Critères d'acceptation Étape 1.2

- 100% des slugs créés/édités respectent le format ASCII strict.
- Renommage item: recalcul auto + gestion explicite des collisions.
- Renommage rubrique: propagation complète, sans référence orpheline.
- Ancres/URLs internes restent cohérentes après renommage.

### Étape 1.3 - Définir la matrice de compatibilité

Sous-étape 1.3.1:

- Mapper ancien stockage vers nouveau stockage.

Sous-étape 1.3.2:

- Identifier les alias temporaires nécessaires.

Sous-étape 1.3.3:

- Définir conditions de retrait des alias.

#### Détail complet Étape 1.3 (décisions validées)

##### 1.3.A Stratégie de migration en cas d'incohérence

- Mode strict validé.
- Au moindre cas invalide, la migration s'arrête immédiatement.
- Aucune conversion partielle silencieuse n'est autorisée.

##### 1.3.B Politique de sauvegarde avant migration

- Backup complet obligatoire avant toute exécution.
- Périmètre backup: base de données + fichiers/options liés au système template/rubriques.
- Aucun lancement de migration sans confirmation du backup complet.

##### 1.3.C Politique de reprise après correction

- Reprise à partir de l'étape en erreur (et non depuis le début).
- L'étape reprise doit repasser ses validations d'entrée avant exécution.
- Journalisation obligatoire: erreur, correction appliquée, horodatage, relance.
- Niveau de log validé: standard (code erreur, étape, timestamp, slug rubrique/item).
- Format code erreur validé (efficacité max): double format = code court + enum lisible.
- Exemple attendu: TMP-001 / INVALID_ACTIVE_ITEM_SLUG.

##### 1.3.D Matrice minimale de compatibilité (ancien -> cible)

- Mode absent -> mode = single (default contrôlé).
- active_item_slug invalide -> erreur bloquante (correction requise).
- slug hors format canonique -> erreur bloquante (normalisation requise).
- HEADER incomplet selon matrix -> erreur bloquante (données HERO/SLIDER requises).
- ordre/visibilité incohérents -> erreur bloquante (réparation avant suite).

##### 1.3.E Critères d'acceptation Étape 1.3

- Toute incohérence critique provoque un stop immédiat.
- Backup complet vérifié avant chaque run.
- Reprise opérationnelle validée à l'étape en erreur.
- 100% des cas bloquants sont tracés et rejouables.

Livrables Phase 1:

- Contrat de données versionné (structure + mapping + règles de slug).

Critères de sortie Phase 1:

- Toutes les rubriques sont couvertes par un modèle unique.
- Toutes les opérations CRUD et renommage sont traçables.

## 3) Phase 2 - Migration et compatibilité

### Étape 2.1 - Préparer la migration

Sous-étape 2.1.1:

- Inventorier toutes les sources de données existantes/historiques.

Sous-étape 2.1.2:

- Définir ordre d'exécution migration (pré-checks -> migration -> post-checks).

Sous-étape 2.1.3:

- Définir sauvegarde préalable obligatoire.

### Étape 2.2 - Implémenter migration

Sous-étape 2.2.1:

- Migrer structure des rubriques.
- Inclure la migration dédiée de HEADER vers le contrat composite HERO/SLIDER.

Sous-étape 2.2.2:

- Migrer associations items/instances.

Sous-étape 2.2.3:

- Migrer visibilité et ordre du squelette.

### Étape 2.3 - Sécuriser rollback

Sous-étape 2.3.1:

- Définir rollback technique (scripts + vérifications).

Sous-étape 2.3.2:

- Tester rollback sur jeu d'essai.

Sous-étape 2.3.3:

- Documenter procédure opérateur.

Livrables Phase 2:

- Procédure de migration + rollback validée.

Critères de sortie Phase 2:

- 0 perte de données.
- Rollback exécutable et validé.

## 4) Phase 3 - Refonte interface Squelette (admin)

### Étape 3.1 - Refonte interactions cœur

Sous-étape 3.1.1:

- Implémenter afficher/masquer rubrique.
- Gérer la règle spéciale HEADER (visibilité de la rubrique composite + états internes HERO/SLIDER).

Sous-étape 3.1.2:

- Implémenter ordre des rubriques.

Sous-étape 3.1.3:

- Implémenter ajout/retrait rubrique.

### Étape 3.2 - Gestion mode single/multi

Sous-étape 3.2.1:

- Ajouter switch single/multi par rubrique.
- Inclure HEADER dans la logique standard single/multi, avec items composites HERO/SLIDER.

Sous-étape 3.2.2:

- Gérer item actif en mode single.

Sous-étape 3.2.3:

- Gérer paramètres multi (autoplay, delay, navigation manuelle, ordre des items).

### Étape 3.3 - Preview et performance

Sous-étape 3.3.1:

- Maintenir preview rubrique et preview global.

Sous-étape 3.3.2:

- Conserver chargement AJAX et cache local.

Sous-étape 3.3.3:

- Stabiliser UX sans rechargement complet.

Livrables Phase 3:

- Page Squelette v2 opérationnelle.

Critères de sortie Phase 3:

- Les 6 actions cœur sont validées en admin.
- Pas de régression de fluidité perceptible.

## 5) Phase 4 - Rendu front unifié

### Étape 4.1 - Brancher le rendu sur le nouveau contrat

Sous-étape 4.1.1:

- Lire mode et état de rubrique depuis le modèle canonique.

Sous-étape 4.1.2:

- Appliquer visibilité/ordre exacts du Squelette.

Sous-étape 4.1.3:

- Appliquer comportement single/multi par rubrique.
- Appliquer le rendu spécifique HEADER en composite HERO/SLIDER via le même pipeline single/multi.

### Étape 4.2 - Uniformiser les comportements

Sous-étape 4.2.1:

- Uniformiser navigation manuelle multi.

Sous-étape 4.2.2:

- Uniformiser timer/autoplay multi.

Sous-étape 4.2.3:

- Uniformiser fallback quand items absents.

Livrables Phase 4:

- Front aligné 100% sur la configuration admin.

Critères de sortie Phase 4:

- Correspondance admin/front vérifiée rubrique par rubrique.
- Aucun fallback historique non maîtrisé sur le parcours principal.

## 6) Phase 5 - Nettoyage technique et nomenclature

### Étape 5.1 - Nettoyer l'existant obsolète

Sous-étape 5.1.1:

- Retirer branches multi-template obsolètes.

Sous-étape 5.1.2:

- Retirer compatibilités devenues inutiles.

Sous-étape 5.1.3:

- Retirer chemins morts et duplications.

### Étape 5.2 - Harmoniser nomenclature

Sous-étape 5.2.1:

- Harmoniser noms modules/fonctions.

Sous-étape 5.2.2:

- Harmoniser classes CSS/JS et assets.

Sous-étape 5.2.3:

- Harmoniser nommage des options et clés.

Livrables Phase 5:

- Codebase simplifiée et cohérente.

Critères de sortie Phase 5:

- Baisse mesurable de la complexité.
- Aucune référence active au modèle multi-template.

## 7) Phase 6 - Validation QA et recette

### Étape 6.1 - Recette fonctionnelle

Sous-étape 6.1.1:

- Tester les 6 actions cœur sur toutes les rubriques.

Sous-étape 6.1.2:

- Tester mode single/multi complet.

Sous-étape 6.1.3:

- Tester previews rubrique et global.

### Étape 6.2 - Cas limites

Sous-étape 6.2.1:

- Rubrique vide.

Sous-étape 6.2.2:

- Multi sans item.

Sous-étape 6.2.3:

- Renommage, retrait, réajout, réordonnancement.
- Cas spécifique HEADER: HERO seul, SLIDER seul, HERO+SLIDER (matrice/position cohérentes).

### Étape 6.3 - Recette technique

Sous-étape 6.3.1:

- Vérifier non-régression admin desktop/mobile.

Sous-étape 6.3.2:

- Vérifier non-régression front desktop/mobile.

Sous-étape 6.3.3:

- Vérifier logs erreurs et performances.

Livrables Phase 6:

- PV de recette + backlog de correctifs.

Critères de sortie Phase 6:

- 0 bug bloquant.
- Parcours principal validé bout en bout.

## 8) Phase 7 - Déploiement contrôlé

### Étape 7.1 - Préparation release

Sous-étape 7.1.1:

- Préparer runbook de déploiement.

Sous-étape 7.1.2:

- Préparer runbook de rollback.

Sous-étape 7.1.3:

- Préparer checklist de monitoring.

### Étape 7.2 - Déploiement

Sous-étape 7.2.1:

- Déployer pré-prod.

Sous-étape 7.2.2:

- Valider smoke tests.

Sous-étape 7.2.3:

- Déployer production.

### Étape 7.3 - Stabilisation post-release

Sous-étape 7.3.1:

- Monitorer incidents et erreurs.

Sous-étape 7.3.2:

- Corriger incidents prioritaires.

Sous-étape 7.3.3:

- Clôturer la phase de surveillance.

Livrables Phase 7:

- Mise en production stable + rapport post-release.

Critères de sortie Phase 7:

- Aucun incident majeur.
- Fenêtre de monitoring stable.

## 9) Priorités et enchaînement

1. Priorité haute: Phases 0, 1, 2.
2. Priorité haute: Phases 3, 4.
3. Priorité moyenne: Phase 5.
4. Priorité haute: Phases 6, 7.

## 10) Points de contrôle permanents

- Ne pas démarrer implémentation front avant validation Phase 1.
- Ne pas retirer les couches de compatibilité existantes avant validation Phase 2 + Phase 6.
- Tracer toute opération de renommage de slug.
- Refuser toute réintroduction implicite du multi-template.

## 11) Décisions validées (session en cours)

- Liste de rubriques: référentiel existant, évolutif dans le temps.
- HEADER: rubrique composite HERO et/ou SLIDER, incluse dans single/multi.
- Mode rubriques (toutes): single/multi validé.
- Règle LIVE: portée au niveau rubrique uniquement.
- Politique de slug canonique avec propagation au renommage: validée.
- Go/No-Go: Go Phase 1 validé.
- Priorité Phase 1: Étape 1.1 (modèle canonique rubrique).
- Niveau de détail demandé: complet (champs + règles + cas limites).
- Étape 1.2: slug item mis à jour automatiquement au renommage.
- Étape 1.2: en collision, message "déjà pris" + demande d'un nouveau nom.
- Étape 1.2: slug rubrique modifiable avec propagation.
- Étape 1.2: propagation automatique partout (instances, ordre/visibilité, previews/cache, ancres/URLs internes).
- Étape 1.2: format slug imposé = kebab-case ASCII strict (sans accents).
- Étape 1.3: migration en mode strict (stop immédiat sur incohérence).
- Étape 1.3: backup complet obligatoire (base + fichiers/options liés).
- Étape 1.3: reprise à partir de l'étape en erreur.
- Étape 1.3: journal d'erreurs niveau standard (code, étape, timestamp, slug rubrique/item).
- Étape 1.3: format code erreur = double format (ex: TMP-001 + INVALID_ACTIVE_ITEM_SLUG).

## 12) Exécution en cours - Implémentation admin template unique

### 12.1 Objectif immédiat

- Supprimer la navigation multi-template en admin et ouvrir directement le squelette unique.

### 12.2 Contenu attendu dans la prochaine mise à jour

- Nettoyage final des libellés/template context dans toute l'interface Rubriques.
- Vérification des derniers points UI encore orientés multi-template.

### 12.3 Réalisé (session)

- Mode template unique activé côté handlers admin (création/duplication/wizard bloqués).
- Entrée menu Template redirigée directement vers la page Rubriques (squelette).
- Menu latéral simplifié: Template (singulier), sans sous-menu template.
- Sommaire templates neutralisé dans le flux principal.
- Libellés rubriques rendus génériques en mode unique (sans suffixe MAYAMI/DEFAULT).
- Fil d'Ariane ajusté en mode unique (suppression du segment template nommé).
- Titre panneau droit ajusté: "Squelette" (sans suffixe template).
- Picker rubrique enrichi avec un choix prioritaire du principe d'affichage: single ou multi.
- Choix single/multi persisté en AJAX par rubrique/template (instance V4 enrichie avec display_mode).
- Rectification produit appliquée: TOP-BAR et FOOTER restent en affichage single par défaut (mode multi non proposé).
- Renommage immédiat demandé: items TOP-BAR/FOOTER harmonisés en "default" avec mise à jour des slugs dans Rubriques.
- Vocabulaire UI ajusté: TOP-BARS -> TOP-BAR et FOOTERS -> FOOTER (singulier imposé).
- Option d'ajout de section retirée pour TOP-BAR et FOOTER (UI + garde-fou backend create/duplicate).
- Page Rubriques: décalage visuel ajouté pour TOP-BAR/FOOTER afin de signaler leur statut spécial.
- Correction UI validée: décalage inversé corrigé (rubriques standards décalées à droite, TOP-BAR/FOOTER non décalés).
- Harmonisation terminologique: remplacement de "Single" par "Unique" sur les libellés et hooks JS admin ciblés.
- Règle UX affinée: en Unique imposé (TOP-BAR/FOOTER), le titre "Items disponibles pour ..." est masqué.
- HEADER: le sélecteur "Principe d'affichage" (Unique/Multi) est affiché avant la zone "Composition du HEADER".
- Correction confusion HEADER: le mode Unique/Multi est désormais indépendant de la composition HERO/SLIDER (plus de bascule automatique).
- Fix UI couleur (builder Rubriques): compat variables CSS `--em-wp-color-swatch` / `--em-color-swatch` pour éviter les swatches gris et rétablir la mise à jour live dans la modale.
- STREAM Multi finalisé: config branchée (manuel/auto + timer), premier item par défaut, masquage d'items dans la rotation, persistance AJAX et rendu front multi-items.
- Ajustement UI STREAM: en mode Multi la radio gauche (mode Unique) est masquée; en Multi+Manuelle, les checkbox d'inclusion et le choix "Premier item" sont masqués.
- Règle UI renforcée: en mode Unique, seule la radio gauche reste visible (aucun contrôle Multi affiché).
- Branchement front Multi STREAM: persistance explicite de la liste d'items visibles (`multi_items`) + fallback lecture option pour garantir le rendu multi-items en front.
- Ajustement front STREAM: contrôles prev/next intégrés visuellement au bloc STREAM actif (overlay), suppression de l'effet de bandeau séparé.
- Ajustement front STREAM (itération UI): capsule de contrôle passée en fond transparent pour se fondre dans la couleur de chaque item.
