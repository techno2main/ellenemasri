# Session Handoff

Last updated: 2026-06-05

## Purpose

This file tracks the current working state of the project so a new VS Code / Copilot session can resume cleanly from the real project state.

It does not replace repository instructions.
It complements:
- `.github/copilot-instructions.md`
- `.github/git-commit-instructions.md`
- `website/AGENTS.md`
- `wp/AGENTS.md`

## How to use this file

At the start of a new session:
1. Read the repository instruction files first.
2. Read this handoff file second.
3. Identify whether the active task concerns `website` or `wp`.
4. Confirm the current objective before making changes.

Before ending a session during active work:
1. Update this file to reflect the real current state.
2. Record what was completed.
3. Record what remains to do.
4. Record the next recommended action.

## Current project context

- Repository structure uses two separate working areas:
  - `website` = prototype and client-validation front-end work
  - `wp` = final WordPress implementation
- Always distinguish `website` and `wp` before proposing or modifying code.
- Do not mix prototype logic with production WordPress implementation.
- Repository instructions remain the primary active source of truth.
- This file is a live session handoff, not a higher-priority rule file.

## Active governance rules

- No branch, commit, merge, push, or pull request without explicit user request.
- Prefer small, atomic, reversible changes.
- Preserve accents in French Markdown files.
- Do not normalize French Markdown to ASCII unless explicitly requested.
- Avoid Markdown status labels like `[OK]`, `[EN ATTENTE]`, or `[ERREUR]`; prefer emojis, bold text, or inline code.
- Keep `documentation/copilot-memory-audit/` aligned with real project state when tracked procedures or rules change.

## Current status

- Audit and instruction alignment work has been completed.
- Repository instruction files have been relus et validés comme source de vérité active.
- `documentation/copilot-memory-audit/` is versioned in Git through explicit re-inclusion in `.gitignore`.
- Copilot scope logic has been clarified:
  - global preferences are not the same as repository rules
  - repository rules are not the same as folder-specific rules
  - unrelated project notes must not be applied to this repository

## Current focus

- No active implementation task recorded yet.
- Next session must first determine whether the work concerns `website`, `wp`, or governance/documentation.

## Last completed work

- Création et normalisation du dossier d'audit `documentation/copilot-memory-audit/`.
- Lecture et utilisation comme source de vérité (non modifiés) :
  - `.github/copilot-instructions.md`
  - `.github/git-commit-instructions.md`
  - `website/AGENTS.md`
  - `wp/AGENTS.md`
  - `.gitignore`
- Vérification de cohérence interne du dossier d'audit (session 2026-06-05).
- Clarification que `documentation/copilot-memory-audit/` est versionné via ré-inclusion dans `.gitignore`.
- Confirmation du modèle de scope :
  - préférences globales ≠ règles repo
  - règles repo ≠ règles de dossier
  - notes d'un autre projet = hors scope pour ce dépôt

## Next recommended action

- On next session start, first identify the active work area:
  - `website`
  - `wp`
  - governance/documentation
- Then confirm the exact task before proposing code or edits.
- If a new rule or procedure changes during work, update:
  1. the active file concerned
  2. this handoff file if the change affects ongoing session continuity
  3. the relevant audit documentation if the rule is being tracked there

## Notes for next session

- Do not assume there is an active coding task in progress.
- Re-check current user request before acting.
- If the request concerns workflow, instructions, memory, or audit consistency, consult `documentation/copilot-memory-audit/`.
- If the request concerns implementation, state clearly whether it belongs to `website` or `wp` before proceeding.