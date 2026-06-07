# Comparison Targets Status

Date: 2026-06-05
Area: repository-wide instruction comparison

## Requested comparison targets

| Target file | Scope | Expected role | Access status | Notes |
|---|---|---|---|---|
| `.github/git-commit-instructions.md` | Repository | Git workflow instructions | Not directly accessible in this runtime | Target path identified during audit |
| `website/AGENTS.md` | Folder-local | Prototype and validation rules for `website` | Not directly accessible in this runtime | Target path identified during audit |
| `wp/AGENTS.md` | Folder-local | Production WordPress rules for `wp` | Not directly accessible in this runtime | Target path identified during audit |

## Runtime-visible substitutes used during analysis

| Visible source | Purpose in audit | Notes |
|---|---|---|
| `raw/github-copilot-instructions.from-runtime.md` | Used as the repository instruction baseline | Runtime attachment content was available |
| `raw/user-memory.md` | Used to compare persistent user rules vs repo instructions | Includes Git, Markdown, Supabase, and Android notes |
| `raw/session-memory.md` | Checked for active session-level instructions | Exported as empty |
| `raw/repo-memory.md` | Checked for repository memory injected at runtime | Exported as empty |
| `raw/external-visible-sources.md` | Recorded visible external instruction paths | Paths only, no direct content access |

## Comparison outcome

- Repository-level comparison was partially possible through the runtime attachment of `.github/copilot-instructions.md`.
- Folder-local comparison targets were identified, but their direct file contents were not available in the runtime export set used for this audit.
- User memory overlap and contradiction analysis was therefore based on visible runtime exports only.

## Follow-up

When direct access becomes available, compare the following files against the normalized audit outputs:
1. `.github/git-commit-instructions.md`
2. `website/AGENTS.md`
3. `wp/AGENTS.md`