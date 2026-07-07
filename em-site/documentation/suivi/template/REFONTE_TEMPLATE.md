# Refonte template unique avec multi-items

Date: 2026-07-07
Horodatage prÃ©cis (Paris): 2026-07-07 20:13:03
PÃ©rimÃ¨tre: em-site/wp/wp-content/themes/em-site
Statut: ImplÃ©mentation admin template unique en cours

## Mise Ã  jour session (Paris): 2026-07-07 21:54:16

- Purge des rÃ©sidus `emv4` du builder Rubriques et du composant Scotchs terminÃ©e.
- Le conflit fatal de fonction top-bar a Ã©tÃ© rÃ©solu par sÃ©paration des helpers admin/front.
- Le rendu de la page Rubriques est de nouveau exÃ©cutable sans 500.

RÃ¨gle de suivi: toujours inclure un horodatage prÃ©cis temps rÃ©el (date + heure Paris) Ã  chaque mise Ã  jour de ce document.

## 1) Objectif validÃ©

Mettre en place un modÃ¨le cible avec:

- une seule page Template / Squelette (unique)
- des noms de rubriques gÃ©nÃ©riques
- pour chaque rubrique, un mode d'affichage au choix:
	- mode item unique
	- mode multi-items avec timer et navigation manuelle
- aucune notion de templates multiples (plus de duplication, nouveau template, variantes de template)

Rubriques cibles demandÃ©es:

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

PrÃ©cision structurelle:

- HEADER est une rubrique composite contenant HERO et/ou SLIDER.

## 1.1 RÃ´le final de la page Template

La page Template unique doit permettre:

- Afficher / masquer une rubrique sur le front
- Changer l'ordre des rubriques sur le front
- Ajouter une nouvelle rubrique disponible
- Enlever une rubrique existante
- PrÃ©visualiser le template par rubrique ou en global
- DÃ©finir, pour chaque rubrique, le systÃ¨me d'items: fixe ou multi

RÃ¨gle de fonctionnement HEADER:

- HEADER utilise aussi le flux standard single/multi.
- La spÃ©cificitÃ© HEADER est la structure de chaque item (composite HERO et/ou SLIDER).

## 1.2 FonctionnalitÃ©s dÃ©jÃ  existantes (socle actuel)

En complÃ©ment du rÃ´le final ci-dessus, la page Squelette dispose dÃ©jÃ  de:

- Ouverture/fermeture d'une rubrique sans rechargement de page, avec synchronisation de l'URL (paramÃ¨tre open)
- Chargement AJAX des pickers de rubrique avec cache local pour accÃ©lÃ©rer la navigation
- Lien APERÃ‡U du site public (nouvel onglet)
- AperÃ§u d'une rubrique dans le wireframe via l'icÃ´ne Å“il
- Auto-aperÃ§u d'une rubrique lors de l'ouverture du panneau
- Choix de la position d'insertion lors de l'ajout d'une rubrique au squelette
- Gestion spÃ©cifique du HEADER en composite (hero/slider, matrice, position, apparence partagÃ©e)
- Raccourci d'Ã©dition d'un item depuis le squelette vers la page RUBRIQUES

Note produit:

- Avec un template unique, la notion LIVE n'est plus portÃ©e par le template.
- La notion LIVE est dÃ©placÃ©e au niveau des rubriques (Ã©tat/impact rubrique par rubrique).

## 2) RÃ©ponses concrÃ¨tes Ã  tes questions

### 2.0 DÃ©cision produit verrouillÃ©e

- Les noms de rubriques du template restent gÃ©nÃ©riques: TOP-BAR, HEADER, STREAM, SOCIAL, VIDEO, RELEASE, CTA, CONTACT, ABOUT, FOOTER.
- Le template est unique: une seule structure commune, aucune gestion de templates diffÃ©rents.
- HEADER est explicitement traitÃ© comme rubrique composite (HERO et/ou SLIDER), incluse dans single/multi.

### 2.1 Si on renomme les rubriques, les slugs seront-ils mis Ã  jour correctement?

RÃ©ponse courte: oui, avec une rÃ¨gle unique pour toutes les rubriques et tous les items, sans distinction natif/custom.

DÃ©tail:

- Une seule politique de slug doit s'appliquer:
	- slug de rubrique: stable, gÃ©nÃ©rique et indÃ©pendant du nom d'artiste
	- slug d'item: dÃ©rivÃ© du label, unique, et mis Ã  jour lors du renommage
	- slug de template: unique et commun (plus de dÃ©clinaisons mÃ©tier)

- Lors d'un renommage, la propagation attendue est globale:
	- registre template (unique)
	- options de rubriques
	- plan/squelette
	- visibilitÃ©
	- template actif et contexte d'Ã©dition
	- rÃ©fÃ©rences d'items dÃ©jÃ  branchÃ©s

- Aucun comportement diffÃ©renciÃ© natif/custom n'est visÃ© dans la cible.

Convention cible validÃ©e:

- aucune rÃ©fÃ©rence legacy ni em_site_v4 dans le modÃ¨le final
- aucun suffixe versionnÃ© dans le nommage mÃ©tier
- aucune branche fonctionnelle sÃ©parÃ©e natif/custom pour les slugs

## 3) Ã‰tat actuel vs cible (template unique)

Ã‰tat actuel confirmÃ©:

- Le squelette Rubriques est dÃ©jÃ  centralisÃ© dans une page sommaire.
- Les labels de rubriques intÃ©grÃ©es sont dÃ©jÃ  gÃ©nÃ©riques (TOP-BAR, HEADER, etc.).

Ã‰cart Ã  fermer pour ta cible:

- Uniformiser la stratÃ©gie single item vs multi-items par rubrique, avec une UX homogÃ¨ne.
- Finaliser le nettoyage du wording mÃ©tier et technique rÃ©siduel.
- Poser une politique claire de slug canonique pour Ã©viter les divergences futures.

## 4) PrÃ©paration refonte (sans exÃ©cution)

### Phase A - Contrat fonctionnel unique

- DÃ©finir un contrat commun rubrique:
	- mode = single | multi
	- item par dÃ©faut
	- paramÃ¨tres multi: autoplay, delay, navigation manuelle, ordre
- DÃ©finir le comportement front attendu pour chaque rubrique en mode multi.

### Phase B - ModÃ¨le de donnÃ©es unifiÃ©

- Unifier le stockage autour d'une structure unique par rubrique:
	- item courant
	- collection d'items
	- mode d'affichage
	- configuration de dÃ©filement
- PrÃ©voir une migration des donnÃ©es existantes compatible avec l'existant.

### Phase C - Interface admin unique

- Garder une seule entrÃ©e Template/Squelette.
- Dans chaque rubrique, fournir:
	- un switch clair item unique / multi
	- la gestion de la liste d'items (ordre, renommage, suppression)
	- les rÃ©glages timer + clic manuel

Ce bloc Interface admin couvre explicitement les 6 actions produit validÃ©es en section 1.1.

### Phase D - Nettoyage technique et nomenclature

- Inventorier et normaliser les Ã©lÃ©ments de nomenclature technique:
	- fonctions/action hooks
	- classes CSS/JS
	- options WordPress
	- assets et dossiers
- DÃ©cider ce qui doit Ãªtre:
	- renommÃ© immÃ©diatement
	- gardÃ© en alias de compatibilitÃ© temporaire
	- dÃ©prÃ©ciÃ© avec fenÃªtre de suppression

## 5) DÃ©cisions proposÃ©es pour la suite

1. Valider la rÃ¨gle de slug canonique:
	 - rubriques: top-bar, header, stream, social, video, release, cta, contact, about, footer
	 - items: slug dÃ©rivÃ© du label, stable aprÃ¨s renommage contrÃ´lÃ©
2. Valider le contrat de mode d'affichage par rubrique: single vs multi.
3. Valider la stratÃ©gie de transition de nomenclature:
	 - nomenclature mÃ©tier uniforme dans toute l'interface
	 - nomenclature technique alignÃ©e avec le modÃ¨le final
4. Valider l'arrÃªt du multi-template:
	 - plus de duplication de templates
	 - plus de crÃ©ation de nouveaux templates
	 - une seule page Template/Squelette commune

## 6) Position finale au 2026-07-05

- Le besoin est faisable dans l'architecture actuelle.
- Le modÃ¨le final attendu est un template unique avec rubriques gÃ©nÃ©riques uniquement.
- Le nettoyage de la nomenclature technique reste un chantier explicite de refonte.
- Le modÃ¨le cible retenu est unifiÃ©: une seule logique de slug et aucun dÃ©coupage natif/custom.

## 7) PA dÃ©diÃ©

Le plan d'action dÃ©taillÃ© (phases, Ã©tapes, sous-Ã©tapes, critÃ¨res Go/No-Go) est maintenant centralisÃ© dans:

- em-site/documentation/suivi/template/PA-REFONTE-TEMPLATE.md

## 8) Ã‰tat implÃ©mentÃ© (session)

- Le menu gauche "Template" est en singulier et n'affiche plus de sous-menu template.
- Le clic sur "Template" ouvre directement la page Rubriques/Squelette.
- Les actions de crÃ©ation/duplication/wizard sont neutralisÃ©es dans le flux principal.
- Les rubriques du squelette sont affichÃ©es en gÃ©nÃ©rique (sans suffixe artiste/template).
- Le fil d'Ariane et le titre du panneau squelette sont alignÃ©s sur la logique template unique.
- Le picker rubrique propose dÃ©sormais en premier le principe d'affichage single/multi.
- Le choix single/multi est sauvegardÃ© par rubrique dans l'instance template.
- Rectification validÃ©e: TOP-BAR et FOOTER restent en single par dÃ©faut (pas de multi sur ces deux rubriques).
- Harmonisation nomenclature items: TOP-BAR et FOOTER migrent vers des items "default" (slugs alignÃ©s dans Rubriques).
- Harmonisation libellÃ©s rubrique: TOP-BARS devient TOP-BAR et FOOTERS devient FOOTER.
- RÃ¨gle UX/produit: ajout de section dÃ©sactivÃ© pour TOP-BAR et FOOTER (rubriques figÃ©es Ã  item unique).
- CohÃ©rence visuelle Rubriques: TOP-BAR et FOOTER reÃ§oivent aussi un dÃ©calage visuel dÃ©diÃ© (rubriques spÃ©ciales).
- Ajustement final du sens du dÃ©calage: les rubriques standards sont dÃ©calÃ©es Ã  droite; TOP-BAR/FOOTER restent alignÃ©s Ã  gauche.
- Harmonisation wording: remplacement de "Single" par "Unique" sur l'admin concernÃ©.
- Ajustement d'affichage: pour les rubriques en Unique imposÃ© (TOP-BAR/FOOTER), ne pas afficher "Items disponibles pour ...".
- HEADER: prioritÃ© d'UI mise en place avec "Principe d'affichage" (Unique/Multi) affichÃ© avant "Composition du HEADER".
- Correction de logique: le choix Unique/Multi et le choix de composition HERO/SLIDER sont sÃ©parÃ©s et persistÃ©s indÃ©pendamment.
- Correctif JS/CSS: la pastille couleur (fond) se met Ã  jour en direct et n'apparaÃ®t plus grise aprÃ¨s duplication d'item.
- STREAM Multi: sÃ©paration Unique/Multi conservÃ©e, rÃ©glages de transition (manuel/auto + timer), choix du premier item, masquage d'items, et prise en compte cÃ´tÃ© front.
- RÃ¨gle UI validÃ©e: en Multi, la radio de sÃ©lection Unique est cachÃ©e; en Multi+Manuelle, les options d'inclusion et de premier item sont cachÃ©es.
- Distinction stricte: en Unique, seule la radio de gauche est visible (contrÃ´les Multi masquÃ©s).
- STREAM Multi (front): rendu rÃ©ellement multi-items via `multi_items` persistÃ© et rÃ©solu cÃ´tÃ© helpers, mÃªme si le registre d'items n'est pas chargÃ© cÃ´tÃ© front.
- STREAM Multi (UI front): contrÃ´les prev/next intÃ©grÃ©s au bloc (plus de bandeau sÃ©parÃ©), avec style harmonisÃ© Ã  la section active.
- STREAM Multi (UI front, itÃ©ration): fond du conteneur de navigation rendu transparent pour Ã©pouser la couleur de l'item affichÃ©.
- STREAM Multi (navigation): un lien d'ancre ciblÃ© (`#stream-<item-slug>`) active dÃ©sormais l'item visÃ© puis positionne la vue sur la section STREAM.
- TOP-BAR (rendu texte lien): correction du champ `mayami_my_miami` pour Ã©viter l'affichage JSON brut quand un lien est dÃ©fini.
- Navigation interne front: correction de l'interception des ancres pour Ã©viter que le handler global annule les ancres STREAM item sans dÃ©lÃ©gation au module STREAM.
- Picker admin non-stream (ex: VIDEO): correction de la mise Ã  jour des radios en mode Multi (sÃ©lection persistÃ©e + feedback visuel cohÃ©rent).
- Harmonisation de la logique Unique/Multi hors STREAM: mode Multi sans renommage de rubrique par item et sans badge "en ligne" (rÃ©servÃ© au mode Unique).
- Modale de confirmation admin: formulation mise Ã  jour pour le modÃ¨le template unique (rÃ©fÃ©rence Ã  la rubrique, sans "Mayami").
- Multi non-stream alignÃ© sur STREAM manuel: plus de sÃ©lection radio "active" ni de confirmation "section branchÃ©e" quand le mode est Multi.
- Rubriques V4 (builder): en ligne contenant un slider, le bouton "Ajouter une colonne" est masquÃ© pour garder la contrainte mono-colonne sans retirer l'icÃ´ne de drag.
- Rubriques V4 (preview mini): correction du doublon de vignettes prÃ¨s de "Contenu" en masquant la vignette "colonne" quand la ligne ouverte est en 1 colonne.
- Harmonisation Multi globale: contrÃ´les "items inclus" + "premier item" + "transition" appliquÃ©s de maniÃ¨re cohÃ©rente Ã  STREAM et aux rubriques non single-only (ex: VIDEO).
- Harmonisation back/front effective: VIDEO dispose maintenant du rendu Multi front complet (navigation + transition + hash ciblÃ©), et la disponibilitÃ© du mode Multi est alignÃ©e cÃ´tÃ© admin/backend sur les rubriques rÃ©ellement branchÃ©es front (STREAM + VIDEO).
- VIDEO row description: support complet du type "Texte enrichi" cÃ´tÃ© front (HTML + lien), avec fallback sur l'ancien champ legacy.
- VIDEO description: clÃ© front rÃ©solue dynamiquement Ã  partir des mÃ©tadonnÃ©es de champ (row/col/type), pour rester stable mÃªme si la clÃ© technique change.
- VIDEO description: style du champ (taille/police/couleur/alignement) dÃ©sormais appliquÃ© depuis la dÃ©finition du champ (`options.style`) en front.
- RÃ©gression corrigÃ©e: le mode Multi est de nouveau disponible pour toutes les rubriques sauf TOP-BAR et FOOTER (imposÃ©es en Unique uniquement).
- RELEASE front: mode Multi branchÃ© (items multiples, navigation, auto/manual, hash ciblÃ©) avec rÃ©cupÃ©ration de la section rÃ©tablie aprÃ¨s correction du fallback items.
- RELEASE front: la colonne droite n'est plus figÃ©e sur des clÃ©s hardcodÃ©es ; extraction dynamique des blocs (intro/titre/lignes crÃ©dits/sÃ©parateurs) avec compatibilitÃ© legacy.
- RELEASE admin: optimisation d'ergonomie en mode compact pour les lignes crÃ©dits, avec action rapide d'ajout de ligne crÃ©dit.
- RELEASE admin: ajustement de la mise en forme vers une version compacte anti-scroll (moins de hauteur, meilleure lisibilitÃ©), sans modification du schÃ©ma de donnÃ©es.
- Purge nomenclature: les occurrences `em_wp` rÃ©siduelles ont Ã©tÃ© supprimÃ©es du thÃ¨me `em-site` (code, JS admin, variables internes), avec vÃ©rification de recherche globale Ã  0 dans ce pÃ©rimÃ¨tre.
- Correctif runtime admin: suppression d'une collision de fonction stream et neutralisation de fichiers legacy redondants pour Ã©viter les Ã©crans "critical error" sur Dashboard/Rubriques.
- RÃ©tablissement front: ajout d'une migration one-shot de compatibilitÃ© base de donnÃ©es pour recopier les options `em_wp_*` / `em_wp_v4_*` vers `em_site_*` et restaurer les contenus front existants.
- Incident Header "Our Land": correction d'une perte d'entrÃ©es causÃ©e par une migration incomplÃ¨te (copie seule) via une migration v2 de fusion des tableaux legacy vers les options `em_site_*` dÃ©jÃ  prÃ©sentes.

## Mise à jour session (Paris): 2026-07-07 22:08:43

- Nettoyage legacy durci: suppression du migrateur de préfixes legacy dans le thème actif.
- Le bootstrap ne charge plus de fallback de migration one-shot.
- Vérification de non-régression statique réalisée sur le périmètre PHP modifié.
