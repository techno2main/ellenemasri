# AGENTS.md — website

## Purpose

This folder is the prototype and client-validation area of the project.
Use it to design, build, and refine pages or sections before they are ported to WordPress.

## Role of this folder

- Build visual prototypes and polished front-end sections
- Prioritize final rendering, UX, responsiveness, and presentation quality
- Treat this folder as the design/reference source before WordPress integration

This is not the final production WordPress implementation.

## Relationship with wp

- `website` = prototype and validation
- `wp` = final WordPress implementation

When working here:
- optimize for visual quality and clarity
- explore layouts and interactions freely
- keep components realistic enough to be portable into WordPress later
- avoid architecture choices that would make WordPress integration unnecessarily difficult

## Working rules

- Favor clean, modular, reusable front-end structure
- Keep sections easy to translate into PHP templates later
- Prefer simple data structures and predictable markup
- Avoid overengineering prototype logic
- Use realistic content structure, not vague placeholders, when possible
- Prioritize responsive behavior and clean visual hierarchy
- Do not treat rules from unrelated projects or stack-specific memories as local `website` rules unless explicitly confirmed

## Porting rules

When a section is approved and later moved into `wp`:
- preserve the validated visual intent
- simplify implementation if needed for WordPress maintainability
- do not assume every prototype interaction must be reproduced exactly
- favor portable HTML/CSS structure over framework-specific cleverness

## Response behavior

- Focus on front-end quality, layout, UX, and presentational clarity
- Explain structure in a way that helps later WordPress conversion
- Flag anything that may be hard to maintain once ported into WordPress
- If a request is ambiguous, clarify whether the goal is prototype exploration or preparation for WordPress porting