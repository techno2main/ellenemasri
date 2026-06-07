# Draft Updates (Pending Confirmation)

Date: 2026-06-05

## What can be applied now

These changes concern local audit copies only, without modifying original sources yet:

1. ⏳ Add scope tags to exported memory notes.
2. ✅ Add a normalized summary file that groups rules by domain:
- Git workflow.
- Markdown conventions.
- Security and database.
- Platform-specific build notes.
3. ⏳ Add a conflict matrix between user memory and repository instructions.

## Proposed updates to original sources

Do **not** apply these without confirmation.

### 1. `.github/copilot-instructions.md`

✅ **Déjà appliqué.** Le fichier actif contient déjà :
- une section `Instruction precedence` (ordre de priorité + protocole de résolution de conflit)
- une section `Documentation rules` (accents français, pas d'ASCII-fication, labels Markdown)
- une règle explicite contre l'import de règles d'un autre projet

Aucune modification supplémentaire requise sur ce point.

### 2. User memory organization

- Split global notes from repository-specific notes.
- Add a textual scope prefix to each note title, for example:
  - `scope: global`
  - `scope: repo / ellenemasri.com`
  - `scope: stack / supabase`

### 3. Optional sync target files after content access is available

✅ **Traité.** Les fichiers cibles ont été lus et comparés pendant l'audit :
- `.github/git-commit-instructions.md`
- `website/AGENTS.md`
- `wp/AGENTS.md`

Aucune action de synchronisation supplémentaire n'est requise sur ce point.

## Confirmation gate

Before any original source modification, confirm:

1. Which originals to edit first.
2. Whether to migrate domain-specific user memory into scoped files.

## Recommended order

If approved, apply changes in this order:

1. Local audit copies.
2. Scope-specific memory organization updates (if requested).
3. Reconciliation of audit status and sync notes.