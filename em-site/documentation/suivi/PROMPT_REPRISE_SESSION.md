# Prompt de reprise de session - em-site

Utilise ce prompt au début d'une nouvelle session pour figer l'état réel du chantier.

## Prompt à copier-coller
```text
Contexte chantier em-site (à respecter strictement) :

- Dossier de travail : em-site/
- Branche active : feature/em-site
- Source officielle autorisée : em-wp (V4 uniquement)
- Interdictions : aucune copie récursive globale, aucun legacy, aucun fallback, aucun import non whitelisté
- Règle lots : copie étape par étape, tests obligatoires, validation utilisateur avant lot suivant

État figé actuel :
1) Arbo cible finalisée modules-first (assets + PHP) et validée.
2) Thème em-site en base zéro import : fichiers structurels créés, contenu non importé (fichiers vides).
3) Audit structure validé : EXPECTED_COUNT=122, ACTUAL_COUNT=122, MISSING_COUNT=0, EXTRA_COUNT=0, NON_EMPTY_COUNT=0.
4) Étapes validées : 0,1,2,3,4,5.
5) Prochaine action autorisée : préparer lot 1 V4 en whitelist stricte puis exécuter les tests obligatoires du lot.

Consignes d'exécution :
- Toujours mettre à jour la documentation de suivi avant tout flow GH.
- Ne rien copier hors whitelist validée.
- Stopper après chaque lot pour validation utilisateur explicite.
```

## Vérifications rapides de reprise
1. Vérifier la branche : `git rev-parse --abbrev-ref HEAD`
2. Vérifier l'état git : `git status --short --untracked-files=all`
3. Vérifier l'état structure thème :
   - `Get-ChildItem -Recurse -File em-site\wp\wp-content\themes\em-site | Measure-Object`
   - `Get-ChildItem -Recurse -File em-site\wp\wp-content\themes\em-site | Where-Object {$_.Length -gt 0} | Measure-Object`
