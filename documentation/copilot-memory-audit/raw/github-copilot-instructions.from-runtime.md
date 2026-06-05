# Raw Export - .github/copilot-instructions.md (runtime attachment)

Source: runtime attachment provided to the agent
Captured at: 2026-06-05

# Copilot Instructions

This repository contains two separate working areas:
- `website`: prototype and client-validation front-end work
- `wp`: final WordPress implementation

Always identify which area the request concerns before suggesting code.

## Local instructions

This repository also uses local `AGENTS.md` files for folder-specific rules:
- `website/AGENTS.md` for prototype and validation work
- `wp/AGENTS.md` for final WordPress implementation

Use these local instructions together with this file.
When working inside one of these folders, follow the nearest `AGENTS.md` for local constraints and conventions.

## Repository rules

- Do not confuse prototype code with production WordPress code
- `website` is for visual exploration and validation
- `wp` is the production target and must remain WordPress-compatible
- Prefer the existing structure over introducing new architecture without reason

## Folder-specific behavior

If the task concerns `website`:
- prioritize front-end quality, rendering, responsiveness, and portability to WordPress later

If the task concerns `wp`:
- prioritize maintainability, WordPress-native patterns, CMB2 compatibility, and production-safe implementation

## Conversion rule

When converting from `website` to `wp`:
- preserve validated visual intent
- adapt implementation to WordPress constraints
- do not blindly copy prototype patterns into WordPress
- prefer simple and maintainable integration

## Git workflow rules

- Never create a branch unless explicitly requested
- Never create a pull request
- Never suggest a pull request workflow unless explicitly requested
- Never push automatically unless explicitly requested
- Never merge automatically unless explicitly requested
- Never commit automatically unless explicitly requested
- Prefer atomic commits: one logical change per commit
- Prefer small, reversible changes
- Before any Git action, verify that the user explicitly asked for it
- If a Git action is ambiguous, ask before proceeding

## Safety rules

- Never modify WordPress core files
- Never assume a headless setup unless explicitly requested
- Do not replace existing CMB2 logic unless explicitly requested
- Clarify ambiguous requests before making structural decisions

## Response behavior

- Always state which folder the change belongs to
- Prefer direct, practical solutions
- Avoid unnecessary rewrites, abstractions, or tooling changes
- Give the most optimized final version first
- Do not propose multiple variants unless explicitly requested