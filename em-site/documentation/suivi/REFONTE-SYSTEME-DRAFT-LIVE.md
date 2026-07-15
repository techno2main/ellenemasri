# REFONTE SYSTÈME DRAFT / LIVE

Date : 2026-07-15
Horodatage précis (Paris) : 2026-07-15 17:43:18
Périmètre : em-site/wp/wp-content/themes/em-site
Statut : version de refonte validée, base unique de travail

Branche dédiée : feature/refonte-draft-live-separation

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

### Phase 1 - Simplification Template
- Renommer le bouton.
- Centraliser la détection dirty globale de la page.
- Ouvrir Preview uniquement après enregistrement draft réussi.

### Phase 2 - Moteur de diff Preview
- Construire snapshots Draft et Live normalisés.
- Implémenter le diff réel unique.
- Piloter l’UI Preview uniquement avec ce diff.

### Phase 3 - Validation et publication
- Activer `VALIDER LA MISE EN LIGNE` seulement si diff réel.
- Gérer le cas sans diff avec message dédié.
- Conserver un retour BO propre avec modale.

### Phase 4 - Durcissement
- Tests complets multi-rubriques / multi-options.
- Tests refresh, changements rapides, multi-onglets.
- Vérification non-régression publication.

## Règle de conduite

- Pas de patch isolé hors plan.
- Pas d’approximation.
- Une seule base de vérité : ce document.
