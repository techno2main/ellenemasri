# Copilot Memory Audit Analysis

Date: 2026-06-05  
Area: repository-wide (cross-folder governance for `website` and `wp`)

## Method

Compared exported runtime-visible memory and instruction content against the requested target files when content was available.

## Overlaps

### 1. Git workflow strictness

User memory and repository instructions both require explicit user request before Git actions.  
Both also emphasize atomic, small, reversible changes.

### 2. Scope separation

Repository instructions clearly separate `website` prototype work from `wp` production work.  
This aligns with the user preference to avoid accidental cross-context workflow drift.

### 3. Documentation discipline

User memory requests documentation consistency before commit.  
Repository instructions also request direct, practical solutions and controlled changes.

## Contradictions

### 1. Markdown accents policy mismatch risk

✅ **Traité.** La section `Documentation rules` de `.github/copilot-instructions.md` couvre explicitement la préservation des accents en Markdown français et l'interdiction de normalisation ASCII. Aucune action supplémentaire requise sur ce point.

### 2. Security guidance portability risk

✅ **Cadré.** Les fichiers actifs (`.github/copilot-instructions.md`, `website/AGENTS.md`, `wp/AGENTS.md`) imposent déjà de ne pas importer des règles d'autres projets ni des règles stack-specific hors contexte. Le risque est identifié et couvert par la gouvernance actuelle.

## Outdated or potentially stale rules

### 1. Android APK workflow note

This appears project-specific to another codebase unless this repository also ships Android artifacts.  
Mark it as global-only with a scope note, or move it to a scoped memory bucket.

### 2. Supabase function policy

This is high-value but domain-specific.  
It should be tagged by stack or repository to prevent over-application.

## Missing rules

### 1. Explicit precedence order

✅ **Traité.** La section `Instruction precedence` est présente dans `.github/copilot-instructions.md` et définit l'ordre : fichier repo > AGENTS local > mémoire utilisateur. Aucune action supplémentaire requise.

### 2. Conflict resolution protocol

✅ **Traité.** La section `Instruction precedence` de `.github/copilot-instructions.md` indique déjà : appliquer la règle locale la plus proche ; en cas d'ambigüité persistante, demander avant toute action structurelle ou Git.

### 3. Audit metadata standard

Recommend a timestamp plus source hash or fingerprint for future audits.

## Limitations

### 1. Fichiers cibles de comparaison

✅ **Levée.** Les fichiers `.github/git-commit-instructions.md`, `website/AGENTS.md` et `wp/AGENTS.md` ont été lus et comparés lors de la session du 2026-06-05.

### 2. Could not directly export GitHub-hosted Copilot Memory

- No direct API or tool access was exposed in this session.

## Scope conclusion

Le contenu visible est globalement cohérent avec les fichiers sources actifs du dépôt.

✅ **Déjà couvert dans les fichiers actifs :**
- ordre de priorité des instructions,
- protocole de résolution des conflits,
- séparation `website` / `wp`,
- cadrage des règles stack-specific et hors projet,
- préservation des accents en Markdown français sans ASCII-fication.

⏳ **Reste en amélioration potentielle (non bloquant) :**
- standardiser des métadonnées d'audit (exemple : fingerprint/hash des sources) si souhaité.