# PA - Refonte Gestion Template

Date: 2026-07-07
Horodatage prÃ©cis (Paris): 2026-07-07 20:13:03
PÃ©rimÃ¨tre: em-site/wp/wp-content/themes/em-site
Branche: feature/refonte-template-gestion
Statut: ImplÃ©mentation admin template unique en cours (jalon UI atteint)

## Mise Ã  jour session (Paris): 2026-07-07 21:54:16

- Lot `emv4` purgÃ© sur le builder Rubriques et le composant Scotchs.
- Conflit fatal top-bar corrigÃ© par sÃ©paration des helpers admin et front.
- Validation du lot: grep global `emv4` Ã  0 sur le thÃ¨me actif, page `em-rubriques-overview` rÃ©tablie.

Mises Ã  jour de session:

- Incident critique traitÃ©: disparition du HEADER "Our Land" aprÃ¨s purge legacy; cause identifiÃ©e cÃ´tÃ© migration de prÃ©fixes (copie seule, sans fusion des tableaux existants).
- Correctif appliquÃ©: migration v2 de fusion legacy -> em_site dans `inc/core/legacy-option-prefix-migration.php` pour restaurer les clÃ©s/items manquants (catalogues HEADER inclus) quand la clÃ© cible existe dÃ©jÃ .

- Purge nomenclature finalisÃ©e sur le thÃ¨me actif: suppression des occurrences `em_wp_` rÃ©siduelles dans le code et les scripts admin/front.
- Stabilisation runtime admin: correction d'une collision de fonction (`em_site_stream_player_height`) et neutralisation de fichiers legacy (`menu.php`, `dashboard-menus.php`, `dashboard-routing.php`) qui provoquaient des redÃ©finitions fatales.
- RÃ©tablissement front: ajout d'une migration one-shot des options WordPress legacy (`em_wp_*` / `em_wp_v4_*`) vers le prÃ©fixe cible `em_site_*` via `inc/core/legacy-option-prefix-migration.php` pour restaurer les donnÃ©es existantes.

- Correction d'une rÃ©gression admin: mode Multi rÃ©tabli pour toutes les rubriques sauf TOP-BAR et FOOTER (restent imposÃ©es en Unique).
- RELEASE front: branchement du mode Multi (instances, navigation prev/next, dots, timer auto/manuelle, hash d'item).
- RELEASE front: correction de disparition de section via fallback robuste de rÃ©solution des items (`em_site_v4_get_items` + store brut + slug sÃ©lectionnÃ© + slug par dÃ©faut).
- RELEASE front: structuration dynamique de la colonne droite (intro, titre, lignes crÃ©dits, sÃ©parateurs) avec fallback legacy pour rollback immÃ©diat.
- RELEASE admin: premier niveau d'optimisation UX builder (mode compact crÃ©dits + ajout rapide d'une ligne crÃ©dit) pour rÃ©duire la complexitÃ© d'Ã©dition.
- RELEASE admin (itÃ©ration UX): remplacement du layout trop haut par une version compacte anti-scroll (lecture amÃ©liorÃ©e sans hausse de hauteur), en conservant strictement le mÃªme stockage de donnÃ©es.

RÃ¨gle de suivi: toujours inclure un horodatage prÃ©cis temps rÃ©el (date + heure Paris) Ã  chaque mise Ã  jour.

## 0) Cadrage du PA

- Objectif global: livrer une gestion Template unique (rubriques gÃ©nÃ©riques) avec mode single/multi par rubrique.
- Contraintes: pas de retour Ã  une logique multi-template, migration maÃ®trisÃ©e, non-rÃ©gression front/admin.
- DÃ©finition du succÃ¨s: configuration dans Squelette = rendu front identique, avec 0 bug bloquant en recette.

## 1) Phase 0 - Cadrage final et gel du pÃ©rimÃ¨tre

### Ã‰tape 0.1 - Valider le pÃ©rimÃ¨tre fonctionnel final

Sous-Ã©tape 0.1.1:

- Valider la liste de rubriques existante (rÃ©fÃ©rentiel actuel): TOP-BAR, HEADER, STREAM, SOCIAL, VIDEO, RELEASE, CTA, CONTACT, ABOUT, FOOTER.
- RÃ¨gle: cette liste est Ã©volutive dans le temps et mise Ã  jour selon les besoins validÃ©s.
- PrÃ©ciser explicitement que HEADER est une rubrique composite contenant HERO et/ou SLIDER, mais incluse dans le mÃªme flux single/multi.

Sous-Ã©tape 0.1.2:

- Confirmer les 6 actions cÅ“ur de la page Squelette:
- afficher/masquer
- ordonner
- ajouter
- retirer
- preview rubrique/global
- choisir le mode single/multi

Sous-Ã©tape 0.1.3:

- Confirmer la rÃ¨gle LIVE: portÃ©e au niveau rubrique (plus au niveau template).

### Ã‰tape 0.2 - Valider le vocabulaire et les rÃ¨gles transverses

Sous-Ã©tape 0.2.1:

- Geler la terminologie: template unique, rubrique, item, mode single/multi.

Sous-Ã©tape 0.2.2:

- Geler les rÃ¨gles de slug de haut niveau: slug canonique, propagation au renommage, traÃ§abilitÃ©.

Sous-Ã©tape 0.2.3:

- Geler la structure documentaire: cadrage dans REFONTE_TEMPLATE, exÃ©cution dans PA-REFONTE-TEMPLATE.

### Ã‰tape 0.3 - Go/No-Go vers Phase 1

Sous-Ã©tape 0.3.1:

- VÃ©rifier qu'aucun point produit n'est ambigu.

Sous-Ã©tape 0.3.2:

- VÃ©rifier qu'aucun point de pÃ©rimÃ¨tre n'est en attente.

Sous-Ã©tape 0.3.3:

- Valider officiellement le passage en Phase 1.

Livrables Phase 0:

- Cadrage gelÃ© et validÃ©.

CritÃ¨res de sortie Phase 0:

- 100% des rÃ¨gles fonctionnelles validÃ©es.
- 0 point bloquant non arbitrÃ©.

DÃ©cision de passage:

- âœ… Go Phase 1 validÃ© le 2026-07-05 18:06:03 (Paris).

## 2) Phase 1 - Contrat de donnÃ©es cible

### Ã‰tape 1.1 - DÃ©finir le modÃ¨le canonique rubrique

Sous-Ã©tape 1.1.1:

- DÃ©finir les champs: rubrique_slug, mode, active_item_slug, items[], multi_settings.
- DÃ©finir le sous-contrat spÃ©cifique HEADER: matrix, position, hero_item_slug, slider_item_slug (HEADER = HERO et/ou SLIDER).

Sous-Ã©tape 1.1.2:

- DÃ©finir les defaults par rubrique.

Sous-Ã©tape 1.1.3:

- DÃ©finir les validations (mode autorisÃ©, item actif valide, ordre des items).

#### DÃ©tail complet Ã‰tape 1.1 (version de travail)

##### 1.1.A SchÃ©ma canonique rubrique (toutes rubriques)

```
rubrique_state {
	rubrique_slug: string,            // ex: top-bar, stream, video
	enabled: boolean,                 // visible/masquÃ©e sur le front
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

RÃ¨gle:

- HEADER est inclus dans le flux standard single/multi.
- En HEADER, chaque item est un composite HERO et/ou SLIDER.

##### 1.1.C Defaults (v1 cible)

- enabled: true pour toutes les rubriques prÃ©sentes dans le squelette.
- mode par dÃ©faut (toutes rubriques): single.
- active_item_slug (single): premier item enabled par rank.
- multi_settings.autoplay: true.
- multi_settings.delay_ms: 5000.
- multi_settings.manual_nav: true.
- multi_settings.loop: true.
- HEADER.matrix par dÃ©faut: hero_slider.
- HEADER.position_mode par dÃ©faut: hero_left.

##### 1.1.D Validations obligatoires

- rubrique_slug non vide et canonicalisÃ©.
- mode autorisÃ©: single ou multi (toutes rubriques).
- active_item_slug requis si mode=single.
- active_item_slug doit exister dans items[] et Ãªtre enabled.
- rank items unique par rubrique.
- position rubrique unique dans le squelette.
- delay_ms bornÃ©: min 1000, max 120000.
- si mode=multi, au moins un item enabled requis.
- si rubrique_slug=header et matrix=hero, hero_item_slug requis.
- si rubrique_slug=header et matrix=slider, slider_item_slug requis.
- si rubrique_slug=header et matrix=hero_slider, hero_item_slug et slider_item_slug requis.

##### 1.1.E Cas limites Ã  couvrir

- Rubrique sans item actif en mode single.
- Passage single -> multi avec item actif supprimÃ©.
- Passage multi -> single avec plusieurs items enabled.
- Ordre des items incohÃ©rent (doublons de rank).
- Rubrique masquÃ©e puis rÃ©affichÃ©e (Ã©tat conservÃ©).
- HEADER hero seul sans slider.
- HEADER slider seul sans hero.
- HEADER hero+slider avec inversion de position.
- HEADER sans item requis selon matrix (erreur bloquante).

##### 1.1.F RÃ¨gles de rÃ©solution front

- Le front applique d'abord enabled+position rubrique.
- Puis applique mode rubrique (single/multi) sur toutes les rubriques.
- En single: rendu de active_item_slug uniquement.
- En multi: rendu items enabled ordonnÃ©s par rank + rÃ¨gles multi_settings.
- Pour HEADER: chaque item rendu suit son composite matrix/position_mode et ses items HERO/SLIDER.

##### 1.1.G CritÃ¨res d'acceptation Ã‰tape 1.1

- Un payload unique couvre toutes les rubriques, y compris HEADER.
- Le composite HEADER est modÃ©lisÃ© sans crÃ©er un flux parallÃ¨le.
- Les validations bloquantes sont dÃ©finies pour 100% des cas limites listÃ©s.
- Les defaults sont dÃ©finis pour 100% des champs requis.

### Ã‰tape 1.2 - DÃ©finir le contrat de slug

Sous-Ã©tape 1.2.1:

- RÃ¨gles de gÃ©nÃ©ration slug rubrique.

Sous-Ã©tape 1.2.2:

- RÃ¨gles de gÃ©nÃ©ration slug item.

Sous-Ã©tape 1.2.3:

- RÃ¨gles de propagation au renommage (registre, options, plan, visibilitÃ©, rÃ©fÃ©rences d'items).

#### DÃ©tail complet Ã‰tape 1.2 (dÃ©cisions validÃ©es)

##### 1.2.A Format canonique des slugs

- Format imposÃ©: kebab-case ASCII strict.
- CaractÃ¨res autorisÃ©s: a-z, 0-9, tiret.
- Accents interdits dans les slugs.
- Espaces et sÃ©parateurs sont normalisÃ©s en tirets.

##### 1.2.B RÃ¨gle de renommage item

- Si le label item change, le slug item est recalculÃ© automatiquement.
- Si le nouveau slug est dÃ©jÃ  utilisÃ©: afficher "slug dÃ©jÃ  pris" et demander un nouveau nom.
- Aucune suffixation automatique (-2, -3...) n'est appliquÃ©e.

##### 1.2.C RÃ¨gle de renommage rubrique

- Le slug de rubrique est modifiable.
- Toute modification de slug rubrique dÃ©clenche une propagation automatique globale.

##### 1.2.D PortÃ©e de propagation automatique (rubrique/item)

- Instances actives de rubrique.
- Ordre et visibilitÃ© du squelette.
- Previews et cache admin.
- Ancres et URLs internes.
- RÃ©fÃ©rences croisÃ©es dans le stockage fonctionnel.

##### 1.2.E Validations obligatoires

- Un slug vide est refusÃ©.
- Un slug hors format canonique est refusÃ©.
- Un slug en collision est refusÃ© avec retour utilisateur explicite.
- Toute propagation partielle est considÃ©rÃ©e comme erreur bloquante.

##### 1.2.F CritÃ¨res d'acceptation Ã‰tape 1.2

- 100% des slugs crÃ©Ã©s/Ã©ditÃ©s respectent le format ASCII strict.
- Renommage item: recalcul auto + gestion explicite des collisions.
- Renommage rubrique: propagation complÃ¨te, sans rÃ©fÃ©rence orpheline.
- Ancres/URLs internes restent cohÃ©rentes aprÃ¨s renommage.

### Ã‰tape 1.3 - DÃ©finir la matrice de compatibilitÃ©

Sous-Ã©tape 1.3.1:

- Mapper ancien stockage vers nouveau stockage.

Sous-Ã©tape 1.3.2:

- Identifier les alias temporaires nÃ©cessaires.

Sous-Ã©tape 1.3.3:

- DÃ©finir conditions de retrait des alias.

#### DÃ©tail complet Ã‰tape 1.3 (dÃ©cisions validÃ©es)

##### 1.3.A StratÃ©gie de migration en cas d'incohÃ©rence

- Mode strict validÃ©.
- Au moindre cas invalide, la migration s'arrÃªte immÃ©diatement.
- Aucune conversion partielle silencieuse n'est autorisÃ©e.

##### 1.3.B Politique de sauvegarde avant migration

- Backup complet obligatoire avant toute exÃ©cution.
- PÃ©rimÃ¨tre backup: base de donnÃ©es + fichiers/options liÃ©s au systÃ¨me template/rubriques.
- Aucun lancement de migration sans confirmation du backup complet.

##### 1.3.C Politique de reprise aprÃ¨s correction

- Reprise Ã  partir de l'Ã©tape en erreur (et non depuis le dÃ©but).
- L'Ã©tape reprise doit repasser ses validations d'entrÃ©e avant exÃ©cution.
- Journalisation obligatoire: erreur, correction appliquÃ©e, horodatage, relance.
- Niveau de log validÃ©: standard (code erreur, Ã©tape, timestamp, slug rubrique/item).
- Format code erreur validÃ© (efficacitÃ© max): double format = code court + enum lisible.
- Exemple attendu: TMP-001 / INVALID_ACTIVE_ITEM_SLUG.

##### 1.3.D Matrice minimale de compatibilitÃ© (ancien -> cible)

- Mode absent -> mode = single (default contrÃ´lÃ©).
- active_item_slug invalide -> erreur bloquante (correction requise).
- slug hors format canonique -> erreur bloquante (normalisation requise).
- HEADER incomplet selon matrix -> erreur bloquante (donnÃ©es HERO/SLIDER requises).
- ordre/visibilitÃ© incohÃ©rents -> erreur bloquante (rÃ©paration avant suite).

##### 1.3.E CritÃ¨res d'acceptation Ã‰tape 1.3

- Toute incohÃ©rence critique provoque un stop immÃ©diat.
- Backup complet vÃ©rifiÃ© avant chaque run.
- Reprise opÃ©rationnelle validÃ©e Ã  l'Ã©tape en erreur.
- 100% des cas bloquants sont tracÃ©s et rejouables.

Livrables Phase 1:

- Contrat de donnÃ©es versionnÃ© (structure + mapping + rÃ¨gles de slug).

CritÃ¨res de sortie Phase 1:

- Toutes les rubriques sont couvertes par un modÃ¨le unique.
- Toutes les opÃ©rations CRUD et renommage sont traÃ§ables.

## 3) Phase 2 - Migration et compatibilitÃ©

### Ã‰tape 2.1 - PrÃ©parer la migration

Sous-Ã©tape 2.1.1:

- Inventorier toutes les sources de donnÃ©es existantes/historiques.

Sous-Ã©tape 2.1.2:

- DÃ©finir ordre d'exÃ©cution migration (prÃ©-checks -> migration -> post-checks).

Sous-Ã©tape 2.1.3:

- DÃ©finir sauvegarde prÃ©alable obligatoire.

### Ã‰tape 2.2 - ImplÃ©menter migration

Sous-Ã©tape 2.2.1:

- Migrer structure des rubriques.
- Inclure la migration dÃ©diÃ©e de HEADER vers le contrat composite HERO/SLIDER.

Sous-Ã©tape 2.2.2:

- Migrer associations items/instances.

Sous-Ã©tape 2.2.3:

- Migrer visibilitÃ© et ordre du squelette.

### Ã‰tape 2.3 - SÃ©curiser rollback

Sous-Ã©tape 2.3.1:

- DÃ©finir rollback technique (scripts + vÃ©rifications).

Sous-Ã©tape 2.3.2:

- Tester rollback sur jeu d'essai.

Sous-Ã©tape 2.3.3:

- Documenter procÃ©dure opÃ©rateur.

Livrables Phase 2:

- ProcÃ©dure de migration + rollback validÃ©e.

CritÃ¨res de sortie Phase 2:

- 0 perte de donnÃ©es.
- Rollback exÃ©cutable et validÃ©.

## 4) Phase 3 - Refonte interface Squelette (admin)

### Ã‰tape 3.1 - Refonte interactions cÅ“ur

Sous-Ã©tape 3.1.1:

- ImplÃ©menter afficher/masquer rubrique.
- GÃ©rer la rÃ¨gle spÃ©ciale HEADER (visibilitÃ© de la rubrique composite + Ã©tats internes HERO/SLIDER).

Sous-Ã©tape 3.1.2:

- ImplÃ©menter ordre des rubriques.

Sous-Ã©tape 3.1.3:

- ImplÃ©menter ajout/retrait rubrique.

### Ã‰tape 3.2 - Gestion mode single/multi

Sous-Ã©tape 3.2.1:

- Ajouter switch single/multi par rubrique.
- Inclure HEADER dans la logique standard single/multi, avec items composites HERO/SLIDER.

Sous-Ã©tape 3.2.2:

- GÃ©rer item actif en mode single.

Sous-Ã©tape 3.2.3:

- GÃ©rer paramÃ¨tres multi (autoplay, delay, navigation manuelle, ordre des items).

### Ã‰tape 3.3 - Preview et performance

Sous-Ã©tape 3.3.1:

- Maintenir preview rubrique et preview global.

Sous-Ã©tape 3.3.2:

- Conserver chargement AJAX et cache local.

Sous-Ã©tape 3.3.3:

- Stabiliser UX sans rechargement complet.

Livrables Phase 3:

- Page Squelette v2 opÃ©rationnelle.

CritÃ¨res de sortie Phase 3:

- Les 6 actions cÅ“ur sont validÃ©es en admin.
- Pas de rÃ©gression de fluiditÃ© perceptible.

## 5) Phase 4 - Rendu front unifiÃ©

### Ã‰tape 4.1 - Brancher le rendu sur le nouveau contrat

Sous-Ã©tape 4.1.1:

- Lire mode et Ã©tat de rubrique depuis le modÃ¨le canonique.

Sous-Ã©tape 4.1.2:

- Appliquer visibilitÃ©/ordre exacts du Squelette.

Sous-Ã©tape 4.1.3:

- Appliquer comportement single/multi par rubrique.
- Appliquer le rendu spÃ©cifique HEADER en composite HERO/SLIDER via le mÃªme pipeline single/multi.

### Ã‰tape 4.2 - Uniformiser les comportements

Sous-Ã©tape 4.2.1:

- Uniformiser navigation manuelle multi.

Sous-Ã©tape 4.2.2:

- Uniformiser timer/autoplay multi.

Sous-Ã©tape 4.2.3:

- Uniformiser fallback quand items absents.

Livrables Phase 4:

- Front alignÃ© 100% sur la configuration admin.

CritÃ¨res de sortie Phase 4:

- Correspondance admin/front vÃ©rifiÃ©e rubrique par rubrique.
- Aucun fallback historique non maÃ®trisÃ© sur le parcours principal.

## 6) Phase 5 - Nettoyage technique et nomenclature

### Ã‰tape 5.1 - Nettoyer l'existant obsolÃ¨te

Sous-Ã©tape 5.1.1:

- Retirer branches multi-template obsolÃ¨tes.

Sous-Ã©tape 5.1.2:

- Retirer compatibilitÃ©s devenues inutiles.

Sous-Ã©tape 5.1.3:

- Retirer chemins morts et duplications.

### Ã‰tape 5.2 - Harmoniser nomenclature

Sous-Ã©tape 5.2.1:

- Harmoniser noms modules/fonctions.

Sous-Ã©tape 5.2.2:

- Harmoniser classes CSS/JS et assets.

Sous-Ã©tape 5.2.3:

- Harmoniser nommage des options et clÃ©s.

Livrables Phase 5:

- Codebase simplifiÃ©e et cohÃ©rente.

CritÃ¨res de sortie Phase 5:

- Baisse mesurable de la complexitÃ©.
- Aucune rÃ©fÃ©rence active au modÃ¨le multi-template.

## 7) Phase 6 - Validation QA et recette

### Ã‰tape 6.1 - Recette fonctionnelle

Sous-Ã©tape 6.1.1:

- Tester les 6 actions cÅ“ur sur toutes les rubriques.

Sous-Ã©tape 6.1.2:

- Tester mode single/multi complet.

Sous-Ã©tape 6.1.3:

- Tester previews rubrique et global.

### Ã‰tape 6.2 - Cas limites

Sous-Ã©tape 6.2.1:

- Rubrique vide.

Sous-Ã©tape 6.2.2:

- Multi sans item.

Sous-Ã©tape 6.2.3:

- Renommage, retrait, rÃ©ajout, rÃ©ordonnancement.
- Cas spÃ©cifique HEADER: HERO seul, SLIDER seul, HERO+SLIDER (matrice/position cohÃ©rentes).

### Ã‰tape 6.3 - Recette technique

Sous-Ã©tape 6.3.1:

- VÃ©rifier non-rÃ©gression admin desktop/mobile.

Sous-Ã©tape 6.3.2:

- VÃ©rifier non-rÃ©gression front desktop/mobile.

Sous-Ã©tape 6.3.3:

- VÃ©rifier logs erreurs et performances.

Livrables Phase 6:

- PV de recette + backlog de correctifs.

CritÃ¨res de sortie Phase 6:

- 0 bug bloquant.
- Parcours principal validÃ© bout en bout.

## 8) Phase 7 - DÃ©ploiement contrÃ´lÃ©

### Ã‰tape 7.1 - PrÃ©paration release

Sous-Ã©tape 7.1.1:

- PrÃ©parer runbook de dÃ©ploiement.

Sous-Ã©tape 7.1.2:

- PrÃ©parer runbook de rollback.

Sous-Ã©tape 7.1.3:

- PrÃ©parer checklist de monitoring.

### Ã‰tape 7.2 - DÃ©ploiement

Sous-Ã©tape 7.2.1:

- DÃ©ployer prÃ©-prod.

Sous-Ã©tape 7.2.2:

- Valider smoke tests.

Sous-Ã©tape 7.2.3:

- DÃ©ployer production.

### Ã‰tape 7.3 - Stabilisation post-release

Sous-Ã©tape 7.3.1:

- Monitorer incidents et erreurs.

Sous-Ã©tape 7.3.2:

- Corriger incidents prioritaires.

Sous-Ã©tape 7.3.3:

- ClÃ´turer la phase de surveillance.

Livrables Phase 7:

- Mise en production stable + rapport post-release.

CritÃ¨res de sortie Phase 7:

- Aucun incident majeur.
- FenÃªtre de monitoring stable.

## 9) PrioritÃ©s et enchaÃ®nement

1. PrioritÃ© haute: Phases 0, 1, 2.
2. PrioritÃ© haute: Phases 3, 4.
3. PrioritÃ© moyenne: Phase 5.
4. PrioritÃ© haute: Phases 6, 7.

## 10) Points de contrÃ´le permanents

- Ne pas dÃ©marrer implÃ©mentation front avant validation Phase 1.
- Ne pas retirer les couches de compatibilitÃ© existantes avant validation Phase 2 + Phase 6.
- Tracer toute opÃ©ration de renommage de slug.
- Refuser toute rÃ©introduction implicite du multi-template.

## 11) DÃ©cisions validÃ©es (session en cours)

- Liste de rubriques: rÃ©fÃ©rentiel existant, Ã©volutif dans le temps.
- HEADER: rubrique composite HERO et/ou SLIDER, incluse dans single/multi.
- Mode rubriques (toutes): single/multi validÃ©.
- RÃ¨gle LIVE: portÃ©e au niveau rubrique uniquement.
- Politique de slug canonique avec propagation au renommage: validÃ©e.
- Go/No-Go: Go Phase 1 validÃ©.
- PrioritÃ© Phase 1: Ã‰tape 1.1 (modÃ¨le canonique rubrique).
- Niveau de dÃ©tail demandÃ©: complet (champs + rÃ¨gles + cas limites).
- Ã‰tape 1.2: slug item mis Ã  jour automatiquement au renommage.
- Ã‰tape 1.2: en collision, message "dÃ©jÃ  pris" + demande d'un nouveau nom.
- Ã‰tape 1.2: slug rubrique modifiable avec propagation.
- Ã‰tape 1.2: propagation automatique partout (instances, ordre/visibilitÃ©, previews/cache, ancres/URLs internes).
- Ã‰tape 1.2: format slug imposÃ© = kebab-case ASCII strict (sans accents).
- Ã‰tape 1.3: migration en mode strict (stop immÃ©diat sur incohÃ©rence).
- Ã‰tape 1.3: backup complet obligatoire (base + fichiers/options liÃ©s).
- Ã‰tape 1.3: reprise Ã  partir de l'Ã©tape en erreur.
- Ã‰tape 1.3: journal d'erreurs niveau standard (code, Ã©tape, timestamp, slug rubrique/item).
- Ã‰tape 1.3: format code erreur = double format (ex: TMP-001 + INVALID_ACTIVE_ITEM_SLUG).

## 12) ExÃ©cution en cours - ImplÃ©mentation admin template unique

### 12.1 Objectif immÃ©diat

- Supprimer la navigation multi-template en admin et ouvrir directement le squelette unique.

### 12.2 Contenu attendu dans la prochaine mise Ã  jour

- Nettoyage final des libellÃ©s/template context dans toute l'interface Rubriques.
- VÃ©rification des derniers points UI encore orientÃ©s multi-template.

### 12.3 RÃ©alisÃ© (session)

- Mode template unique activÃ© cÃ´tÃ© handlers admin (crÃ©ation/duplication/wizard bloquÃ©s).
- EntrÃ©e menu Template redirigÃ©e directement vers la page Rubriques (squelette).
- Menu latÃ©ral simplifiÃ©: Template (singulier), sans sous-menu template.
- Sommaire templates neutralisÃ© dans le flux principal.
- LibellÃ©s rubriques rendus gÃ©nÃ©riques en mode unique (sans suffixe MAYAMI/DEFAULT).
- Fil d'Ariane ajustÃ© en mode unique (suppression du segment template nommÃ©).
- Titre panneau droit ajustÃ©: "Squelette" (sans suffixe template).
- Picker rubrique enrichi avec un choix prioritaire du principe d'affichage: single ou multi.
- Choix single/multi persistÃ© en AJAX par rubrique/template (instance V4 enrichie avec display_mode).
- Rectification produit appliquÃ©e: TOP-BAR et FOOTER restent en affichage single par dÃ©faut (mode multi non proposÃ©).
- Renommage immÃ©diat demandÃ©: items TOP-BAR/FOOTER harmonisÃ©s en "default" avec mise Ã  jour des slugs dans Rubriques.
- Vocabulaire UI ajustÃ©: TOP-BARS -> TOP-BAR et FOOTERS -> FOOTER (singulier imposÃ©).
- Option d'ajout de section retirÃ©e pour TOP-BAR et FOOTER (UI + garde-fou backend create/duplicate).
- Page Rubriques: dÃ©calage visuel ajoutÃ© pour TOP-BAR/FOOTER afin de signaler leur statut spÃ©cial.
- Correction UI validÃ©e: dÃ©calage inversÃ© corrigÃ© (rubriques standards dÃ©calÃ©es Ã  droite, TOP-BAR/FOOTER non dÃ©calÃ©s).
- Harmonisation terminologique: remplacement de "Single" par "Unique" sur les libellÃ©s et hooks JS admin ciblÃ©s.
- RÃ¨gle UX affinÃ©e: en Unique imposÃ© (TOP-BAR/FOOTER), le titre "Items disponibles pour ..." est masquÃ©.
- HEADER: le sÃ©lecteur "Principe d'affichage" (Unique/Multi) est affichÃ© avant la zone "Composition du HEADER".
- Correction confusion HEADER: le mode Unique/Multi est dÃ©sormais indÃ©pendant de la composition HERO/SLIDER (plus de bascule automatique).
- Fix UI couleur (builder Rubriques): compat variables CSS `--em-site-color-swatch` / `--em-color-swatch` pour Ã©viter les swatches gris et rÃ©tablir la mise Ã  jour live dans la modale.
- STREAM Multi finalisÃ©: config branchÃ©e (manuel/auto + timer), premier item par dÃ©faut, masquage d'items dans la rotation, persistance AJAX et rendu front multi-items.
- Ajustement UI STREAM: en mode Multi la radio gauche (mode Unique) est masquÃ©e; en Multi+Manuelle, les checkbox d'inclusion et le choix "Premier item" sont masquÃ©s.
- RÃ¨gle UI renforcÃ©e: en mode Unique, seule la radio gauche reste visible (aucun contrÃ´le Multi affichÃ©).
- Branchement front Multi STREAM: persistance explicite de la liste d'items visibles (`multi_items`) + fallback lecture option pour garantir le rendu multi-items en front.
- Ajustement front STREAM: contrÃ´les prev/next intÃ©grÃ©s visuellement au bloc STREAM actif (overlay), suppression de l'effet de bandeau sÃ©parÃ©.
- Ajustement front STREAM (itÃ©ration UI): capsule de contrÃ´le passÃ©e en fond transparent pour se fondre dans la couleur de chaque item.
- Correctif navigation ancre STREAM multi-items: support hash ciblÃ© (`#stream-<item-slug>`) pour activer l'item correspondant puis scroller sur la section STREAM.
- Correctif TOP-BAR: le champ texte `mayami_my_miami` accepte dÃ©sormais le format texte+lien (JSON) sans afficher l'objet JSON brut en front.
- Correctif chaÃ®nage ancres front: le handler global (`theme.js`) dÃ©lÃ¨gue maintenant correctement les hashes STREAM item (`#stream-...`) au module STREAM au lieu de bloquer le scroll ciblÃ©.
- Correctif admin picker (VIDEO et rubriques non-stream): sÃ©lection radio en mode Multi resynchronisÃ©e (Ã©tat `current`, badge et titre section), sans masquer les radios hors STREAM.
- Harmonisation Multi non-stream (VIDEO/SOCIAL/RELEASE/...):
	- en mode Multi, plus de confirmation "brancher" bloquante sur clic radio,
	- badge "Item en ligne actuellement" rÃ©servÃ© au mode Unique,
	- le titre rubrique du squelette reste gÃ©nÃ©rique (ex: `VIDEO`) et ne bascule plus en `VIDEO MAYAMI`.
- Modale de confirmation: wording alignÃ© template unique (cible = rubrique, plus de mention du label template legacy type "Mayami").
- Harmonisation stricte Multi (hors STREAM) avec STREAM manuel: radios de section active masquÃ©es en mode Multi et aucun flux "Section branchÃ©e" en Multi.
- Correctif d'harmonisation complet Multi (STREAM + non-STREAM):
	- cases Ã  cocher "items inclus" restaurÃ©es en mode Multi,
	- choix "premier item" restaurÃ© en mode Multi,
	- bloc "Transition Multi" (manuel/auto + timer) disponible sur VIDEO et autres rubriques multi,
	- persistance backend des champs multi gÃ©nÃ©ralisÃ©e aux rubriques non single-only.
- Harmonisation back/front consolidÃ©e:
	- front VIDEO branchÃ© en vrai mode Multi (items multiples, nav prev/next+dots, auto/manual, hash `#video-<slug>`),
	- runtime JS VIDEO enqueued + styles de switch ajoutÃ©s,
	- handler global d'ancres dÃ©lÃ¨gue aussi VIDEO,
	- cadrage fonctionnel stabilisÃ©: mode Multi disponible officiellement sur STREAM + VIDEO (rubriques non branchÃ©es front forcÃ©es en Unique pour Ã©viter divergence back/front).
- Correctif front VIDEO (champ texte enrichi): rendu row description compatible avec stockage `textarea`/JSON (`text` + `link`) + fallback legacy, avec affichage HTML sÃ©curisÃ©.
- Renforcement anti-rÃ©gression type de champ: rÃ©solution dynamique de la clÃ© front VIDEO par position (`row=4`,`col=1`) + type rÃ©el (`text`/`textarea`) au lieu d'un key figÃ©.
- Correctif style typographique dynamique: application front de `options.style` (taille/police/couleur/alignement) pour le champ texte VIDEO rÃ©solu dynamiquement.

## Mise à jour session (Paris): 2026-07-07 22:08:43

- Application de la politique "clean 100%": suppression du migrateur legacy de préfixes.
- Débranchement du chargement dans le bootstrap du thème.
- Vérification post-lot: références migrateur à 0 et préfixes legacy em_wp à 0 dans les PHP du thème actif.
