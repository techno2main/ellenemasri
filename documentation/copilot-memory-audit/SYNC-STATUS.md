# Sync Status - Active Rules Alignment

Date: 2026-06-05  
Status: aligned

## Local source of truth followed

The active repository instruction files currently followed are:
- `.github/copilot-instructions.md`
- `.github/git-commit-instructions.md`
- `website/AGENTS.md`
- `wp/AGENTS.md`
- `.gitignore`

These files remain the active source of truth for project behavior.

## Scope model applied

1. Global rules: reusable general preferences only.
2. Repo rules: only this repository.
3. Folder rules: nearest `AGENTS.md` for `website` or `wp`.
4. Stack-specific rules: not treated as repo-global unless explicitly requested.
5. Rules from another project: out of scope for this repository.

## Behavior lock

- Always distinguish `website` and `wp` before proposing changes.
- Never mix prototype rules with production WordPress implementation rules.
- Never run Git workflow actions (branch, commit, merge, push, pull request) without explicit request.
- Keep French Markdown accents and avoid bracket status labels that trigger `link.no-such-reference` warnings.

## Audit archive policy

- `documentation/copilot-memory-audit/` is treated as audit, control, continuity, and traceability material.
- It is versioned in Git in this repository through explicit re-inclusion in `.gitignore`.
- When a tracked rule changes, update the active instruction file first, then update the relevant audit copy, then realign behavior.

## Session continuity policy

- `documentation/copilot-memory-audit/SESSION-HANDOFF.md` is the live continuity file for session restart or handoff.
- Repository instruction files define stable rules.
- The handoff file records the current working state.
- The audit archive documents and verifies coherence, but does not override active repository instruction files.

## Alignment statement

Current alignment target is:

1. Active repository files define behavior.
2. Folder-specific `AGENTS.md` files refine behavior locally.
3. Audit files document, verify, and preserve coherence.
4. Copilot memory must align with active repository files and current audited state, not override them.