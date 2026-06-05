# Consolidated Rules

Date: 2026-06-05
Source set: runtime-visible memory, runtime instruction attachment, local audit analysis
Scope: normalized local audit copy only

## Instruction layers

1. Repository/runtime instructions
- Repository instructions define the main working split between `website` and `wp`.
- They also define Git, safety, and response constraints for this repository.

2. Local folder instructions
- Folder-level `AGENTS.md` files refine behavior inside `website` and `wp`.
- These rules should be applied together with top-level repository instructions.

3. User memory notes
- User memory contains persistent preferences and workflow rules.
- These notes are valuable, but they should be scoped carefully when they are stack-specific or unrelated to this repository.

## Repository governance rules

### Scope separation

- `website` is the prototype and client-validation area.
- `wp` is the final WordPress implementation.
- Prototype code and production WordPress code must not be confused.
- When a request is ambiguous, identify which area it belongs to before proposing code.

### Conversion from website to wp

- Preserve validated visual intent.
- Adapt implementation to WordPress constraints.
- Do not blindly copy prototype patterns into WordPress.
- Prefer simple, maintainable WordPress integration.

## Git workflow rules

- Never create a branch unless explicitly requested.
- Never create a pull request unless explicitly requested.
- Never suggest a pull request workflow unless explicitly requested.
- Never push automatically unless explicitly requested.
- Never merge automatically unless explicitly requested.
- Never commit automatically unless explicitly requested.
- Prefer atomic commits: one logical change per commit.
- Prefer small, reversible changes.
- Before any Git action, verify that the user explicitly asked for it.
- If a Git action is ambiguous, ask before proceeding.

## Documentation rules

- Preserve accents in French Markdown files.
- Do not normalize French Markdown content to ASCII unless explicitly requested.
- Keep documentation updates consistent with the change being made before any requested commit.
- In Markdown status notes, avoid bracket labels that trigger missing-link warnings.
- Prefer plain text, inline code, or emoji-based status markers instead.

## Safety and implementation rules

### WordPress safety

- Never modify WordPress core files.
- Never assume a headless setup unless explicitly requested.
- Do not replace existing CMB2 logic unless explicitly requested.
- Clarify ambiguous requests before making structural decisions.

### website guidance

- Prioritize front-end quality, rendering, responsiveness, and portability to WordPress later.
- Keep prototype structure realistic enough to be portable.
- Avoid unnecessary architecture that would complicate later WordPress integration.

### wp guidance

- Prioritize maintainability, WordPress-native patterns, CMB2 compatibility, and production-safe implementation.
- Prefer theme, template, admin, and meta-logic changes that fit the current structure.
- Avoid unnecessary rewrites, abstractions, or tooling changes.

## User memory notes requiring scope control

### Global workflow notes

- Documentation consistency before commit.
- Stay on the current feature branch unless explicitly asked otherwise.
- Do not propose or launch a pull request without explicit request.
- Under PowerShell, do not append `2>&1` to Git commands.

### Global Markdown notes

- Always preserve accents in French Markdown.
- Avoid status labels such as `[OK]`, `[EN ATTENTE]`, or `[ERREUR]` in Markdown notes because they can trigger `link.no-such-reference`.
- Prefer emoji and bold text instead.

### Stack-specific notes

- Supabase SQL rules should be treated as stack-specific, not repository-global by default.
- Android APK build notes should be treated as platform/project-specific, not repository-global by default.

## Recommended normalization actions

1. Add explicit instruction precedence in repository instructions.
2. Add a short conflict resolution protocol.
3. Tag user memory notes by scope:
- `scope: global`
- `scope: repo / ellenemasri.com`
- `scope: stack / supabase`
- `scope: platform / android`

## Audit limitations

- Some requested target files were identified but not directly readable in the runtime toolset used for this audit.
- GitHub-hosted Copilot Memory was not directly exportable in this session.
- This file is a normalized audit artifact, not a direct source-of-truth instruction file.