# Description système - Module WP Visual Links Builder (VLB)

Date de mise à jour: 2026-06-02
Statut: Document de référence fonctionnelle et historique

## 1) But du module

Le module VLB (Visual Links Builder) sert à produire des visuels cliquables administrables dans WordPress, avec deux objectifs métier:

1. Gestion de visuels interactifs en admin
- upload d’image,
- création/édition de zones cliquables,
- sauvegarde et réouverture de brouillons,
- publication/retour hors ligne côté front.

2. Production de livrables export
- Template-HTML (usage web/preview),
- Template-Email (usage emailing),
- génération de ressources d’email (images découpées),
- export code HTML/TXT.

## 2) Historique complet du module (création -> aujourd’hui)

### Phase 1 - Origine EPK (première implémentation)

Le module est initialement conçu autour d’un périmètre EPK (Electronic Press Kit):
- nomenclature technique epk_*,
- section front dédiée,
- champ custom CMB2 pour piloter un visuel à hotspots,
- logique brouillon/publication.

Caractéristiques de départ:
- payload stocké en JSON dans les options,
- preview privée admin,
- publication conditionnée par validation,
- fonctions et actions WordPress nommées en epk.

### Phase 2 - Intégration builder HTML autonome

Ajout d’un builder visuel complet embarqué en iframe admin:
- outil de dessin de rectangles cliquables,
- gestion drag/resize des zones,
- édition URL/ancre,
- sauvegarde AJAX des drafts,
- page “Liste des visuels” pour reprise d’édition.

### Phase 3 - Pipeline export templates

Ajout d’un flux PREVIEW -> EXPORTER TEMPLATE:
- export HTML serveur,
- gestion de buckets Template-HTML et Template-Email,
- purge avant régénération,
- stockage des métadonnées d’export (URL/fichier/date).

### Phase 4 - Itérations UX/fiabilité

Améliorations successives:
- modales de confirmation (suppression zones/drafts),
- états “à jour / obsolète” sur exports,
- organisation UI par blocs,
- gestion PDF (URL + CTA + switch actif/inactif),
- corrections d’alignement zones au rechargement,
- correction de régressions JS de preview.

### Phase 5 - Problématique email clients (Gmail)

Constat principal:
- image map intégrée (area/map) pas fiable selon clients email, surtout Gmail mobile.

Décisions prises:
1. Tentative image unique + map: abandonnée pour fiabilité de clic insuffisante.
2. Retour à une stratégie slices cliquables en table HTML email-compatible.

### Phase 6 - Renommage produit en Visual Links Builder

Renommage fonctionnel de EPK vers VLB:
- labels admin/front renvoyés vers “Visual Links”,
- menu admin dédié “Visual Links Builder”,
- logique export explicitement orientée templates.

### Phase 7 - Nettoyage structurel et pluralisation

Nettoyage des artefacts EPK “fichiers visibles”:
- suppression des anciens fichiers epk.php/epk.css/template section epk,
- remplacement par visual-links.php / visual-links.css / section visual-links.

Renommage dossier builder:
- visual-link-builder -> visual-links-builder.

Compatibilité maintenue:
- aliases/redirections legacy conservés pour éviter casse des anciennes URLs/slugs/actions.

## 3) Fonctionnalités complètes du module VLB

### 3.1. Fonctions d’édition visuelle

1. Chargement d’image
- upload local,
- sélection médiathèque WordPress,
- remplacement avec conservation/recalage des zones.

2. Gestion des zones
- création par dessin,
- déplacement (drag),
- redimensionnement,
- suppression unitaire ou globale,
- type de cible: URL externe ou ancre.

3. Données de zone
- coordonnées et dimensions,
- hrefType,
- hrefValue,
- identifiant technique.

### 3.2. Fonctions brouillons/admin

1. Sauvegarde en base (AJAX)
- création nouveau draft,
- mise à jour draft existant,
- horodatage updated_at.

2. Réouverture de draft
- chargement payload,
- reconstruction des zones,
- restauration des infos export existantes.

3. Liste des visuels
- tri par date de mise à jour,
- ouverture rapide,
- suppression avec modal de confirmation.

### 3.3. Fonctions front/publication

1. Prévisualisation privée
- affichage brouillon visible seulement admin authentifié.

2. Publication
- copie brouillon -> payload publié,
- garde-fou “validation finale” avant publication,
- action de retrait du front.

### 3.4. Fonctions export templates

1. Template-HTML
- export du preview HTML,
- destination dédiée Template-HTML.

2. Template-Email
- génération email HTML,
- génération des slices image,
- upload des slices dans Template-Email/img,
- écriture d’un .html + .txt côté serveur.

3. Purge
- purge bucket Template-HTML,
- purge bucket Template-Email,
- évite les fichiers obsolètes avant régénération.

## 4) Architecture technique actuelle

### 4.1. Couches

1. Admin WP
- menus et sous-menus VLB,
- page builder en iframe,
- page liste des visuels.

2. Builder front-end autonome
- fichier HTML principal,
- logique UI/preview/export.

3. Serveur PHP
- endpoints AJAX,
- sanitation et persistance,
- génération/écriture des exports.

4. Front public
- rendu du visuel cliquable,
- gestion preview privée/payload publié.

### 4.2. CMB2 et options

Option key principale:
- mayami_landing_options

Fallback legacy:
- mayami_options

IDs techniques hérités (conservés):
- epk_draft_payload
- epk_published_payload
- epk_validation_ready
- epk_builder

## 5) Slugs, actions et compatibilité

### 5.1. Slugs admin actifs

- mayami_visual_links_builder
- mayami_visual_links_builder_new
- mayami_visual_links_drafts

### 5.2. Redirections legacy

Anciennes pages EPK redirigées vers nouvelles pages VLB:
- mayami_epk_html_builder
- mayami_epk_html_builder_new
- mayami_epk_drafts

### 5.3. Actions AJAX

Actions VLB actives:
- mayami_save_visual_links_draft
- mayami_get_visual_links_draft
- mayami_export_visual_links_html
- mayami_upload_visual_links_slice
- mayami_purge_visual_export_bucket

Actions legacy encore supportées:
- mayami_save_epk_draft
- mayami_get_epk_draft

## 6) Arborescence export templates

Racine:
- wp-content/themes/mayami/visual-links-builder/exports-html

Par visuel:
- <visuel>/Template-HTML
- <visuel>/Template-Email
- <visuel>/Template-Email/img

## 7) Bug actuel (à date) - export templates email

### 7.1. Symptômes observés

1. Gmail Desktop
- possible apparition d’un bloc d’aperçu YouTube sous le visuel.

2. Gmail Mobile
- rendu jugé encore non satisfaisant,
- besoin de validation finale sur qualité visuelle et comportement de clic.

### 7.2. Analyse

1. Le bloc YouTube n’est pas un bloc volontaire du builder.
2. Il est généré par Gmail (link preview) lorsqu’un lien vidéo est détecté dans le contenu mail.
3. La fiabilité de cliquabilité dépend du pattern HTML compatible client email.

### 7.3. État technique actuel

1. Moteur email actif: slices cliquables en table HTML.
2. Moteur image map intégré: abandonné pour compatibilité Gmail.
3. Code compile sans erreur, mais qualification UX email finale encore ouverte.

## 8) Fichiers principaux du module

### 8.1. Serveur WordPress

- wp-content/themes/mayami/functions.php
- wp-content/themes/mayami/inc/cmb2-config.php
- wp-content/themes/mayami/inc/visual-links.php

### 8.2. Builder

- wp-content/themes/mayami/visual-links-builder/visual-links-builder.html

### 8.3. Front

- wp-content/themes/mayami/front-page.php
- wp-content/themes/mayami/template-parts/sections/visual-links.php
- wp-content/themes/mayami/assets/visual-links.css

## 9) Décisions techniques importantes

1. Maintenir epk_* en interne tant que la migration DB complète n’est pas planifiée.
2. Prioriser la compatibilité des données et des liens admin existants.
3. Traiter le renommage produit d’abord au niveau structure/UI.
4. Stabiliser le flux export avant migration technique profonde.

## 10) Prochaines étapes recommandées

1. Validation email multi-clients finale (Gmail desktop/mobile prioritaire).
2. Décision explicite sur neutralisation des previews YouTube.
3. Si validé: phase 2 de migration technique epk_* -> visual_links_* avec script de migration et rollback.

## 11) Stack technique et environnement (passation dev/IA)

### 11.1. Stack applicative

1. CMS / Runtime principal
- WordPress (thème custom `mayami`).
- PHP côté serveur pour menus admin, CMB2, endpoints AJAX et exports.

2. Back-office VLB
- Page admin WordPress avec iframe vers un builder HTML autonome.
- JS natif (pas de framework SPA) dans `visual-links-builder.html`.
- API média WordPress (`wp.media`) utilisée pour sélection d’images.

3. Stockage
- Options WordPress (`wp_options`) pour:
	- `mayami_landing_options` (payload publié/brouillon section VLB),
	- `mayami_epk_drafts_store` (liste des drafts builder et métadonnées export).

4. Styling
- CSS thème + Tailwind compilé (`style-compiled.css`).
- Dépendances de build CSS dans `package.json`:
	- `tailwindcss` `^4.3.0`
	- `@tailwindcss/cli` `^4.3.0`

### 11.2. Entrées/sorties techniques du module

1. Entrées utilisateur
- Upload image (fichier local ou médiathèque).
- Définition des zones (coordonnées + lien/ancre).
- Paramètres PDF (CTA, URL, actif/inactif).

2. Sorties module
- Template-HTML exporté côté serveur.
- Template-Email exporté côté serveur.
- Slices email uploadées dans `Template-Email/img`.
- Fichier `.txt` (code HTML) généré pour usage emailing.

### 11.3. Endpoints et sécurité

1. Transport
- Appels via `admin-ajax.php`.

2. Sécurité
- Contrôle capability `manage_options` sur actions sensibles.
- Vérification nonce sur endpoints (`mayami_epk_draft` conservé pour compat).
- Sanitization systématique payload (URLs, champs texte, dimensions zones).

3. Compatibilité legacy volontaire
- Actions/slugs historiques `epk_*` toujours supportés via alias/redirections.

### 11.4. Contraintes d’infrastructure

1. Permissions fichiers
- Le dossier d’exports doit être créable et inscriptible:
	- `wp-content/themes/mayami/visual-links-builder/exports-html`

2. Environnement local observé
- Windows + XAMPP.
- Vérification PHP généralement via:
	- `C:\xampp\php\php.exe -l <fichier>`

3. Déploiement
- Upload des fichiers modifiés du thème.
- Suppression des artefacts renommés si encore présents côté serveur.

### 11.5. Cibles de validation et matrice de bug

1. Cible prioritaire
- Gmail Desktop et Gmail Mobile.

2. Risques fonctionnels connus
- Preview card YouTube injectée par Gmail si lien vidéo détecté.
- Rendu mobile email instable selon structure HTML et stratégie d’images.

3. Critères de validation “done”
- Clic zones fiable sur clients cibles.
- Absence d’artefacts majeurs visuels.
- Exports Template-HTML et Template-Email cohérents et à jour.

### 11.6. Workflow de debug recommandé (pour dev/IA)

1. Vérifier d’abord les préconditions infra
- Nonce/capability,
- permissions d’écriture `exports-html`,
- présence des dossiers `Template-HTML` et `Template-Email`.

2. Reproduire sur un visuel test minimal
- 2 à 3 zones,
- 1 lien YouTube explicite,
- 1 lien non vidéo,
- 1 export complet après purge.

3. Contrôler le triplet export
- HTML généré,
- TXT généré,
- slices uploadées.

4. Tester rendu final dans Gmail
- Desktop puis Mobile,
- comparaison avant/après chaque ajustement,
- journaliser exactement le comportement de clic zone par zone.

---

Ce document couvre l’historique complet du module VLB depuis son origine EPK jusqu’à son état actuel, avec son périmètre fonctionnel complet et le bug export templates encore ouvert.