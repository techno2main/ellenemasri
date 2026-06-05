# AGENTS.md — wp

## Purpose

This folder contains the final WordPress implementation of the project.
It is the production target, not the prototype area.

## Stack

- Standard WordPress installation
- Active custom theme: `wp-content/themes/mayami`
- Custom admin fields and metaboxes: CMB2
- Styling: Tailwind CSS 4 with `@tailwindcss/cli`
- Theme structure relies on PHP templates, `template-parts/`, and `inc/`
- Tailwind scans `./**/*.php`, `./template-parts/**/*.php`, and `./inc/**/*.php`

Do not assume React, Next.js, or headless architecture in this folder.

## Theme behavior

- Theme supports `title-tag` and `post-thumbnails`
- Compiled CSS is loaded from `style-compiled.css`
- Theme includes custom frontend/admin assets
- Theme contains custom admin UX and a Visual Links Builder workflow
- Some admin behavior is intentionally simplified for client accounts
- The landing may be forced to `noindex` before launch

Do not remove or rewrite existing behavior unless explicitly requested.

## Relationship with website

- `website` = prototype and client validation
- `wp` = final WordPress implementation

When porting from `website` to `wp`:
- preserve validated visual intent
- adapt to WordPress constraints
- do not copy React patterns blindly into PHP templates
- prefer simple, maintainable WordPress implementations

## CMB2 rules

- Use CMB2 as the default admin field framework
- Keep field registration, saved meta, and frontend rendering clearly separated
- Do not replace CMB2 with ACF or another framework unless explicitly requested

## Working rules

- Preserve WordPress-native patterns
- Prefer changes in theme files, `inc/`, or `template-parts/`
- Reuse existing structure before creating new abstractions
- Respect the current Tailwind-based styling workflow
- Avoid unnecessary npm dependencies or frontend tooling changes
- Do not treat rules from unrelated projects or stack-specific memories as local `wp` rules unless explicitly confirmed

## Safety rules

- Never modify WordPress core files
- Never put custom business logic in WordPress core
- Do not store secrets, API keys, or sensitive notes in this folder
- If a request is ambiguous, clarify whether the goal is prototype parity or production-safe WordPress implementation

## Response behavior

- Explain where code belongs: theme, admin, AJAX, template, or meta logic
- Prefer production-safe WordPress solutions over frontend-style complexity
- Highlight mismatches between prototype ideas and good WordPress implementation
- If a request is ambiguous, state clearly that `wp` is the production WordPress target and not the prototype area