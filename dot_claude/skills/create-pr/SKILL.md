---
name: create-pr
description: "Use when creating a pull request with GitHub CLI, especially when you must follow pull_request_template.md and open the PR as draft"
---

Create a GitHub pull request by reading `pull_request_template.md`, building the PR body in that format, and then creating a draft PR with `gh`.

## Steps

1. Confirm you are in a git repository and working branch:
   - `git rev-parse --is-inside-work-tree`
   - `git branch --show-current`
2. If the current branch is `main` or `trunk`, stop immediately and ask the user to switch to a feature branch.
3. Verify GitHub CLI authentication before attempting PR creation:
   - `gh auth status`
4. Identify the base branch (usually `main` or `trunk`) and collect diff context:
   - `git branch`
   - `git diff <base-branch>...HEAD`
5. Locate and read the PR template file:
   - Prefer `.github/pull_request_template.md` or `.github/PULL_REQUEST_TEMPLATE.md`.
   - If not found, search for `pull_request_template.md` in the repository and use that file.
6. Build the PR description by following the template structure exactly (keep headings/sections, fill required fields, remove unresolved placeholders).
7. Save the PR body to a temporary file (for example, `pr_body.md`) so formatting is preserved.
8. Create the PR in draft status with an explicit head branch:
   - `gh pr create --draft --base <base-branch> --head <current-branch> --title "<PR title>" --body-file pr_body.md`
9. Report the created PR URL and summarize what was filled in each template section.

## Notes

- Always create PRs as draft unless explicitly instructed otherwise.
- Never create a PR from `main` or `trunk`.
- If no template exists, ask for confirmation before using a fallback custom structure.
- Keep PR descriptions concise but complete, and ensure they reflect the actual diff.
