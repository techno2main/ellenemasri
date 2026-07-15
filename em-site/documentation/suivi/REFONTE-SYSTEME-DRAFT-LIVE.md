# REFONTE SYSTÈME DRAFT / LIVE

Date : 2026-07-15
Horodatage précis (Paris) : 2026-07-15 18:50:26
Périmètre : em-site/wp/wp-content/themes/em-site
Statut : phase 1 finalisée, phase 2 démarrée (implémentation diff Preview)

Branche dédiée : feature/refonte-draft-live-separation

## Avancement d'implémentation

### 2026-07-15 17:51:38 (Paris)

- Démarrage effectif sur la branche dédiée.
- Bouton top renommé en `ENREGISTRER LES MODIFICATIONS` (libellé et title).
- Retrait de la persistance locale `emSitePreviewReady` dans le contrôleur central du bouton.
- Retrait des fallbacks `localStorage` dans les flux Rubriques (`instance-picker` et `header-section`).
- Validation syntaxique PHP effectuée sur tous les fichiers touchés.

### 2026-07-15 18:23:30 (Paris)

- Nettoyage des reliquats hors périmètre phase 1 finalisé.
- Correctif robuste Header ajouté : protection anti-réponses AJAX obsolètes sur les enregistrements, pour garantir la cohérence du bouton quand on revient à l’item initial.
- Vérification OK : sur Header, retour à l’item précédent désactive correctement le bouton comme sur les autres rubriques.
- Phase 1 déclarée terminée.
- Phase 2 (diff réel Draft vs Live en Preview) explicitement reportée.

### 2026-07-15 18:26:06 (Paris)

- Ajustement complémentaire Header appliqué après re-test KO.
- Correction de la normalisation de comparaison : en mode `single`, les champs multi (`first_item`, `hidden_items`) sont neutralisés pour éviter un faux état dirty résiduel.
- Résultat attendu : retour à l’item initial du Header => désactivation correcte du bouton global.

### 2026-07-15 18:41:15 (Paris) — Diagnostic expert KO persistant

- Inspection globale effectuée :
  - rendu/attributs picker HEADER (`data-config`, `data-live-config`) ;
  - logique JS de dirty Header (`collectConfig`, `normalize`, `hasPendingHeaderChanges`, `notifyHeaderDraftChanged`) ;
  - agrégation globale du bouton top (`EmSitePreviewButton.setDraftDirty`) ;
  - pipeline save AJAX Header (`em_site_set_header`, `em_site_admin_header_section_save/get`).
- Cause probable (forte) : comparaison dirty Header sur un périmètre trop large par rapport aux champs réellement pilotés par le picker HEADER.
- Cause certaine : la comparaison précédente incluait des champs de composition d’item (`matrix`, `hero`, `slider`, `appearance`, etc.) non pilotés par la sélection d’item du picker principal, ce qui permettait un état dirty résiduel malgré retour à l’item initial.
- Inconnues restantes : éventuels cas UX non couverts en mode `multi` avec transitions rapides côté navigateur.
- Correction minimale appliquée : normalisation Header restreinte au périmètre piloté par le picker principal (`header_item`, `display_mode`, `transition_mode`, `transition_timer`, `first_item`, `hidden_items`).
- Validation technique locale : lint PHP OK sur le fichier modifié.
- Validation fonctionnelle terrain : en attente de re-test utilisateur (aucune déclaration de résolution sans preuve de reproduction complète).

### 2026-07-15 18:41:15 (Paris) — Correctif runtime ciblé KO persistant

- Nouveau correctif minimal appliqué sur le flux `isItemControl` du picker Header.
- Pour les changements d’item Header, `hasPendingChanges` est désormais forcé via une comparaison explicite avec l’item live (`live.header_item`) au moment du save AJAX.
- Objectif : aligner le comportement Header sur les autres rubriques (dirty = item courant différent de l’item live), sans dépendre d’un état de config plus large.
- Validation technique locale : lint PHP OK.
- Validation fonctionnelle terrain : en attente (ne pas conclure sans preuve utilisateur).

### 2026-07-15 18:46:23 (Paris) — Validation fonctionnelle utilisateur

- Re-test terrain confirmé par l’utilisateur : le cas Header est désormais conforme.
- Cas validé : changement d’item Header => bouton activé, retour à l’item initial => bouton désactivé sans refresh.
- Phase 1 confirmée comme clôturée fonctionnellement.

### 2026-07-15 18:49:25 (Paris) — MAJ doc globale demandée

- Vérification complète du document effectuée.
- Le bloc `Plan d’implémentation méthodique` est désormais statué explicitement phase par phase.
- Conformité documentaire remise à niveau avant enchaînement Git et démarrage phase 2.

### 2026-07-15 18:50:26 (Paris) — Démarrage phase 2

- Implémentation du diff réel Draft vs Live sur la page Preview.
- Ajout d’un calcul de snapshots normalisés (draft/live) et comparaison déterministe.
- UI Preview branchée sur ce diff :
  - affichage de `VALIDER LA MISE EN LIGNE` uniquement si diff réel,
  - affichage de `AUCUNE MODIFICATION DÉTECTÉE` sinon.
- Action de publication protégée : si aucun diff réel, redirection vers Preview sans publication.
- Validation technique locale : lint PHP OK sur `inc/shared/template/active.php`.

## Décision de cadrage

Objectif : simplifier fortement le système pour fiabiliser le comportement global et supprimer les incohérences d’état.

Principe retenu :
- La page Template ne décide pas de la mise en ligne.
- La page Template détecte uniquement s’il existe des modifications à enregistrer.
- La page Preview compare réellement Draft vs Live et décide si la mise en ligne est possible.

## Séparation des rôles par page

### Page Template (BO)

Rôle unique : édition + détection de changement + enregistrement.

Comportement cible :
- Le bouton principal devient `ENREGISTRER LES MODIFICATIONS`.
- Bouton inactif si aucun changement détecté.
- Bouton actif dès qu’une modification existe, n’importe où sur la page.
- Au clic, on enregistre le draft puis on ouvre la page Preview dédiée.

Interdits sur cette page :
- Pas de logique de comparaison Draft vs Live approfondie.
- Pas de logique de décision de mise en ligne.
- Pas de badges métier dépendants de la publication réelle.

### Page Preview (pré-publication)

Rôle unique : comparaison réelle et validation de mise en ligne.

Comportement cible :
- Charger l’état Draft enregistré.
- Charger l’état Live publié.
- Calculer un diff réel Draft vs Live.
- S’il existe au moins une différence :
  - afficher et activer `VALIDER LA MISE EN LIGNE`.
  - garder `RETOURNER AUX MODIFICATIONS` disponible.
- S’il n’existe aucune différence :
  - masquer le bouton de validation,
  - afficher le message `Aucune modification détectée`,
  - laisser uniquement `RETOURNER AUX MODIFICATIONS` actif.

## Règles fonctionnelles globales

Ces règles doivent s’appliquer à toutes les rubriques et toutes les options :
- sélection d’item,
- mode d’affichage,
- visibilité,
- ordre,
- layout,
- paramètres de configuration.

Aucune exception spécifique Header.

## Moteur de vérité

### Contrat de comparaison

Le système repose sur deux snapshots canonisés :
- `draftSnapshot` : état BO enregistré,
- `liveSnapshot` : état actuellement publié.

Règle stricte de persistance métier :
- aucune décision métier ne dépend d’un état local navigateur,
- aucune source de vérité locale de type localStorage/sessionStorage/cookie,
- la source de vérité métier est uniquement en base WordPress (draft et live).

Règle :
- `hasRealDiff = (draftSnapshot != liveSnapshot)`

Cette règle est évaluée sur la page Preview, pas sur la page Template.

### Exigence de robustesse

Le diff doit être structurel et déterministe :
- comparaison de données normalisées,
- ordre des clés stabilisé,
- formats normalisés,
- mêmes règles de normalisation pour Draft et Live.

## UX cible de bout en bout

1. L’utilisateur modifie la page Template.
2. `ENREGISTRER LES MODIFICATIONS` s’active.
3. L’utilisateur clique, le draft est enregistré, puis la Preview s’ouvre.
4. La Preview calcule le diff réel Draft vs Live.
5. Si diff présent : `VALIDER LA MISE EN LIGNE` est actif.
6. Si aucun diff : message `Aucune modification détectée`.

## Règle de retour vers le BO

Si l’utilisateur clique `RETOURNER AUX MODIFICATIONS` depuis Preview :
- afficher une modale d’avertissement (texte à finaliser),
- fermer l’onglet Preview,
- rendre le focus à la page BO initiale.

Important :
- les modifications déjà enregistrées restent en brouillon,
- elles ne sont pas perdues tant qu’elles sont stockées en draft.

## Critères d’acceptation

1. Sur Template, le bouton `ENREGISTRER LES MODIFICATIONS` reflète uniquement l’existence de changements locaux.
2. Sur Preview, la disponibilité de `VALIDER LA MISE EN LIGNE` dépend uniquement du diff réel Draft vs Live.
3. Si aucun diff réel : affichage explicite `Aucune modification détectée`.
4. Le comportement est identique sur toutes les rubriques/options, pas seulement Header.
5. Aucun état visuel contradictoire entre Template et Preview.

## Plan d’implémentation méthodique

### Phase 1 - Simplification Template — ✅ FAIT
- Renommer le bouton.
- Centraliser la détection dirty globale de la page.
- Ouvrir Preview uniquement après enregistrement draft réussi.

### Phase 2 - Moteur de diff Preview — ✅ FAIT
- Construire snapshots Draft et Live normalisés.
- Implémenter le diff réel unique.
- Piloter l’UI Preview uniquement avec ce diff.

### Phase 3 - Validation et publication — 🔄 EN COURS
- Activer `VALIDER LA MISE EN LIGNE` seulement si diff réel.
- Gérer le cas sans diff avec message dédié.
- Conserver un retour BO propre avec modale.

### Phase 4 - Durcissement — ⏳ EN ATTENTE
- Tests complets multi-rubriques / multi-options.
- Tests refresh, changements rapides, multi-onglets.
- Vérification non-régression publication.

## Règle de conduite

- Pas de patch isolé hors plan.
- Pas d’approximation.
- Une seule base de vérité : ce document.
