# GitHub-hosted Copilot Memory

Date: 2026-06-05
Status: not directly exportable in this session

## Scope

This folder documents the status of GitHub-hosted Copilot Memory for this audit.

## Current limitation

No direct API, runtime surface, or audit tool in this session exposed the contents of GitHub-hosted Copilot Memory.

As a result:
- no direct export could be produced
- no content-level comparison could be performed
- only the absence of accessible tooling/runtime exposure could be documented here

## What was still verifiable

The audit could still verify:
- runtime-injected user memory
- runtime-injected session memory
- runtime-injected repository memory
- runtime-visible repository instruction attachment
- runtime-visible external instruction paths

## Recommended next step

If GitHub later exposes direct access to hosted Copilot Memory in the active toolchain, export it into this folder and compare it against:
- `../raw/user-memory.md`
- `../normalized/CONSOLIDATED-RULES.md`
- `../ANALYSIS.md`