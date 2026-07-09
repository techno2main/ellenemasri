# PA - Refonte Rubriques

Date : 2026-07-07
Horodatage précis (Paris) : 2026-07-09 10:24:37
Périmètre : em-site/wp/wp-content/themes/em-site
Statut : Cadrage validé, implémentation en cours (suivi opérationnel dans `documentation/suivi/SUIVI_REFONTE.md`)

## Objectif

Refondre le système de rubriques pour supprimer toute distinction métier entre rubriques dites "initiales", "intégrées" ou "custom".

Principe cible : une rubrique est une rubrique, point final.

Le comportement attendu doit être unique pour toutes les rubriques :
- création à la volée,
- masquage/suppression safe,
- réactivation si la rubrique a été masquée,
- conservation des items rattachés,
- zéro suppression destructive involontaire.

## Décision de cadrage

### Règle métier cible

- Ne plus traiter les rubriques de démarrage comme une catégorie à part.
- Ne plus réserver un statut spécial "custom" pour les seules rubriques créées ensuite.
- Toutes les rubriques doivent suivre le même contrat fonctionnel.

### Règle de suppression

- La suppression d’une rubrique doit être un masquage logique, pas une destruction.
- Les items rattachés ne doivent pas être supprimés.
- Les options et données existantes doivent rester intactes.
- La rubrique doit pouvoir être réactivée ensuite.

### Règle de création / réactivation

- Si la rubrique n’existe pas, on la crée.
- Si la rubrique existe mais est masquée, on la réactive.
- Si la rubrique existe et est visible, on bloque la création en doublon.

## Phase 0 - Cadrage fonctionnel

### Étape 0.1 - Unifier le vocabulaire

Sous-étapes :
- Supprimer les termes métier "initiale", "intégrée" et "custom" du raisonnement produit.
- Garder uniquement le terme "rubrique".
- Décrire les comportements en termes d’état visible/masqué et non en termes d’origine.

### Étape 0.2 - Fixer le contrat de suppression

Sous-étapes :
- Documenter la suppression comme un masquage.
- Documenter explicitement que les items sont conservés.
- Documenter le fait qu’aucune purge en base ne doit être faite automatiquement.

### Étape 0.3 - Fixer le contrat de réactivation

Sous-étapes :
- Réactiver une rubrique masquée via la création.
- Préserver l’ordre et les références existantes quand cela est possible.
- Éviter toute recréation inutile des données.

## Phase 1 - Modèle cible unique

### Étape 1.1 - État canonique d’une rubrique

Sous-étapes :
- Définir un état minimal commun : slug, label, visibilité, ordre, items.
- Séparer clairement l’état visible/masqué de l’existence technique.
- Rendre l’état de la rubrique indépendant de son origine.

### Étape 1.2 - Ordre et affichage

Sous-étapes :
- L’ordre d’affichage doit ignorer les rubriques masquées.
- Les menus, l’aperçu et les contrôles doivent suivre le même état.
- Les règles spécifiques de verrouillage doivent être explicites et minimales.

### Étape 1.3 - Items rattachés

Sous-étapes :
- Les items restent liés à leur rubrique même si celle-ci est masquée.
- Un masquage ne doit pas casser les items existants.
- La réactivation doit retrouver les items déjà rattachés.

## Phase 2 - UI / UX admin

### Étape 2.1 - Bouton de suppression unique

Sous-étapes :
- Afficher la suppression sur toutes les rubriques autorisées selon la règle cible.
- Utiliser une confirmation modale mutualisée.
- Faire comprendre clairement qu’il s’agit d’un masquage, pas d’une destruction.

### Étape 2.2 - Bouton + Nouvelle Rubrique

Sous-étapes :
- Si la rubrique n’existe pas, création réelle.
- Si la rubrique est masquée, réactivation.
- Si la rubrique est visible, refuser le doublon.

### Étape 2.3 - Cohérence visuelle

Sous-étapes :
- Conserver un rendu homogène pour toutes les cartes.
- Éviter les affordances qui suggèrent une différence de nature entre rubriques.
- Harmoniser les états visuels visible/masqué.

## Phase 3 - Nettoyage du code

### Étape 3.1 - Retrait de la logique de catégorisation

Sous-étapes :
- Supprimer les branches de code qui différencient les rubriques par origine.
- Remplacer la logique d’exclusion spécifique par une logique d’état.
- Garder les exceptions techniques strictement nécessaires.

### Étape 3.2 - Suppression safe centralisée

Sous-étapes :
- Centraliser la logique de masquage.
- Centraliser la logique de réactivation.
- Centraliser les garde-fous pour éviter les suppressions destructives.

### Étape 3.3 - Compatibilité des données existantes

Sous-étapes :
- Vérifier que les rubriques déjà présentes au démarrage restent gérées comme les autres.
- Vérifier que les rubriques créées ensuite ne sont pas traitées différemment.
- Vérifier que les données en base restent stables après refonte.

## Phase 4 - Validation

### Étape 4.1 - Cas de test obligatoires

Sous-étapes :
- Masquer une rubrique contenant des items.
- Réactiver une rubrique masquée.
- Créer une nouvelle rubrique inexistante.
- Refuser une création en doublon d’une rubrique visible.
- Vérifier la conservation des items après masquage.

### Étape 4.2 - Critères de sortie

Sous-étapes :
- Plus aucune distinction métier entre rubriques de départ et rubriques créées ensuite.
- Une seule logique de suppression : masquage.
- Une seule logique de création : créer ou réactiver selon l’état.
- Aucune suppression d’items rattachés.

## Décisions ouvertes à confirmer avant implémentation

- Faut-il afficher la même action de suppression pour toutes les rubriques non verrouillées ?
- Faut-il conserver une distinction technique minimale pour top-bar/footer/header seulement ?
- Faut-il prévoir un message différent selon qu’on masque ou qu’on réactive ?

## Pause chantier

Aucune action de code ne doit être lancée à partir de ce document tant que la règle cible finale n’est pas validée.
