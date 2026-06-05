# Copilot Memory Audit Index

Date d’audit : 2026-06-05  
Workspace : `c:/xampp/htdocs/web-am/dev.tad/MyWebsites/ellenemasri.com/www`

## Inventaire des sources

| Source name | Scope | Original path or source | Local export path | Type | Summary | Status |
|---|---|---|---|---|---|---|
| Persistent user memory (runtime context) | VS Code local memory | Runtime-injected userMemory context | `documentation/copilot-memory-audit/raw/user-memory.md` | local VS Code memory | Notes persistantes utilisateur visibles au runtime, avec distinction à faire entre global, stack-specific et hors projet | Exported |
| Session memory (runtime context) | VS Code local memory | Runtime-injected sessionMemory context | `documentation/copilot-memory-audit/raw/session-memory.md` | local VS Code memory | Mémoire de session visible : vide | Exported |
| Repository memory (runtime context) | VS Code local memory | Runtime-injected repoMemory context | `documentation/copilot-memory-audit/raw/repo-memory.md` | local VS Code memory | Mémoire repository visible : vide au moment de l’export brut initial | Exported |
| Repo instruction attachment | Workspace/repository instruction | `.github/copilot-instructions.md` (attachment in runtime context) | `documentation/copilot-memory-audit/raw/github-copilot-instructions.from-runtime.md` | instruction file | Règles repo visibles dans le runtime, incluant séparation `website` / `wp` et règles Git / safety | Exported from runtime attachment |
| User-level prompts folder (path only) | User-level prompts | `c:/Users/Tyson/AppData/Roaming/Code/User/prompts` | `documentation/copilot-memory-audit/raw/user-prompts-path-only.md` | prompt file | Dossier de prompts utilisateur signalé par variable de session | Path captured; content not directly accessible here |
| Skill prompt: project-setup-info-local | Copilot-managed external instruction | `c:/Users/Tyson/AppData/Local/Programs/Microsoft VS Code/6a44c352bd/resources/app/extensions/copilot/assets/prompts/skills/project-setup-info-local/SKILL.md` | `documentation/copilot-memory-audit/raw/external-visible-sources.md` | instruction file | Source visible dans le contexte d’agent | Path captured; content not directly accessible here |
| Skill prompt: get-search-view-results | Copilot-managed external instruction | `c:/Users/Tyson/AppData/Local/Programs/Microsoft VS Code/6a44c352bd/resources/app/extensions/copilot/assets/prompts/skills/get-search-view-results/SKILL.md` | `documentation/copilot-memory-audit/raw/external-visible-sources.md` | instruction file | Source visible dans le contexte d’agent | Path captured; content not directly accessible here |
| Skill prompt: agent-customization | Copilot-managed external instruction | `c:/Users/Tyson/AppData/Local/Programs/Microsoft VS Code/6a44c352bd/resources/app/extensions/copilot/assets/prompts/skills/agent-customization/SKILL.md` | `documentation/copilot-memory-audit/raw/external-visible-sources.md` | instruction file | Source visible dans le contexte d’agent | Path captured; content not directly accessible here |
| Skill prompt: fix-customization-evaluation-diagnostics | Copilot-managed external instruction | `c:/Users/Tyson/.vscode/extensions/ms-vscode.vscode-chat-customizations-evaluations-1.0.6/skills/fix-customization-evaluation-diagnostics/SKILL.md` | `documentation/copilot-memory-audit/raw/external-visible-sources.md` | instruction file | Source visible dans le contexte d’agent | Path captured; content not directly accessible here |
| Workspace instruction target | Workspace/repository instruction | `.github/git-commit-instructions.md` | `documentation/copilot-memory-audit/comparison/targets-status.md` | instruction file | Cible demandée pour comparaison et alignement | Contenu lu et comparé en session 2026-06-05 (référencé, non modifié) |
| Workspace instruction target | Workspace/repository instruction | `website/AGENTS.md` | `documentation/copilot-memory-audit/comparison/targets-status.md` | instruction file | Cible demandée pour comparaison et alignement | Contenu lu et comparé en session 2026-06-05 (référencé, non modifié) |
| Workspace instruction target | Workspace/repository instruction | `wp/AGENTS.md` | `documentation/copilot-memory-audit/comparison/targets-status.md` | instruction file | Cible demandée pour comparaison et alignement | Contenu lu et comparé en session 2026-06-05 (référencé, non modifié) |
| GitHub-hosted Copilot Memory | GitHub-hosted | Not exposed in this runtime/toolset | `documentation/copilot-memory-audit/github-hosted/README.md` | GitHub-hosted Copilot Memory | Aucune API ou outil disponible ici pour export direct | Not exportable directly in this session |
| Analysis report | Audit artifact | Derived from exported content | `documentation/copilot-memory-audit/ANALYSIS.md` | analysis report | Recouvrements, contradictions, règles obsolètes ou manquantes, recommandations | Generated |
| Draft updates | Audit artifact | Derived from exported content | `documentation/copilot-memory-audit/DRAFT-UPDATES.md` | draft update plan | Propositions de mise à jour avec gate de confirmation | Generated |
| Normalized consolidated rules | Audit artifact | Derived from raw exports | `documentation/copilot-memory-audit/normalized/CONSOLIDATED-RULES.md` | normalized copy | Consolidation locale non destructive des règles observées | Generated |
| Sync status | Audit artifact | Aligned with active validated local files | `documentation/copilot-memory-audit/SYNC-STATUS.md` | status record | État de synchronisation entre règles actives et archive d’audit | Updated |
| Session handoff | Audit artifact | Live session continuity file | `documentation/copilot-memory-audit/SESSION-HANDOFF.md` | handoff file | État vivant du chantier pour reprise de session et continuité entre sessions VS Code | Updated |

## Limitation explicite

Cet audit exporte tout le contenu effectivement visible dans le contexte runtime de cette session ainsi que les chemins externes déclarés.

Cette session ne fournit pas :
- d’outil de lecture directe des fichiers hors contexte injecté
- d’accès API direct à une mémoire Copilot hébergée par GitHub
- de garantie qu’une mémoire non exposée dans le runtime soit lisible depuis cette session

## Statut de versionnement du dossier d’audit

Dans l’état actuel de `.gitignore`, `documentation/copilot-memory-audit/` est ré-inclus et donc versionné.

Ce dossier sert de :
- trace d’audit
- archive de cohérence
- support de reprise de session
- point de contrôle entre fichiers actifs du repo et mémoire/outillage Copilot

## Rôle du dossier dans la hiérarchie

`documentation/copilot-memory-audit/` ne remplace pas les fichiers actifs du projet.

Les fichiers actifs qui pilotent le comportement restent :
- `.github/copilot-instructions.md`
- `.github/git-commit-instructions.md`
- `website/AGENTS.md`
- `wp/AGENTS.md`

Le dossier d’audit sert à documenter, vérifier, tracer et reprendre l’avancement réel, sans prévaloir sur les instructions actives du dépôt.