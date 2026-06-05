# Git Commit Instructions

- Never generate a commit message for unrelated or mixed changes
- Assume commits must be atomic: one logical change per commit
- If staged changes are not atomic, recommend splitting them before committing
- Before any Git action, verify that the user explicitly asked for it
- Never assume a branch, merge, or pull request workflow unless explicitly requested
- Never mention pull requests, review flows, or merge requests in commit messages
- Use a short, specific subject line
- Use imperative mood
- Avoid vague messages like "update", "fix stuff", or "changes"
- Describe the real intent of the commit
- Add a short body only when useful
- Keep commit messages aligned with the actual scope of the staged changes
- If documentation updates are part of the same logical change, include them coherently in the commit scope