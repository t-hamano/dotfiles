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
4. Detect whether the repository is a fork and determine the PR target repository:
   - `gh repo view --json isFork,parent,nameWithOwner`
   - If `isFork` is `true`, the PR target must be the upstream (`parent.nameWithOwner`), not the fork itself. Record it as `<target-repo>`.
   - If `isFork` is `false`, `<target-repo>` is the current repository (omit `--repo` later).
5. Identify the base branch (usually `main` or `trunk`) and collect diff context:
   - `git branch`
   - For forks, compare against the upstream base: `git diff <target-repo-owner>/<base-branch>...HEAD` (fetch upstream first if needed).
   - Otherwise: `git diff <base-branch>...HEAD`
6. Locate and read the PR template file:
   - Prefer `.github/pull_request_template.md` or `.github/PULL_REQUEST_TEMPLATE.md`.
   - If not found, search for `pull_request_template.md` in the repository and use that file.
   - For forks, prefer the upstream repository's template over the fork's local copy if they differ.
7. Build the PR description by following the template structure exactly (keep headings/sections, fill required fields, remove unresolved placeholders). The description must:
   - Be as concise as possible. Cut anything that does not help a reviewer understand the change.
   - Focus on **what problem was solved** and **the approach taken**, not a line-by-line account of the technical changes (the diff already shows that).
8. Save the PR body to a temporary file (for example, `pr_body.md`) so formatting is preserved.
9. Create the PR in draft status with an explicit head branch:
   - Non-fork: `gh pr create --draft --base <base-branch> --head <current-branch> --title "<PR title>" --body-file pr_body.md`
   - Fork: `gh pr create --draft --repo <target-repo> --base <base-branch> --head <fork-owner>:<current-branch> --title "<PR title>" --body-file pr_body.md`
10. Report the created PR URL and summarize what was filled in each template section.

## Notes

- Always create PRs as draft unless explicitly instructed otherwise.
- Never create a PR from `main` or `trunk`.
- For forked repositories, submit the PR to the upstream (fork source) by default. Only target the fork itself if the user explicitly requests it (e.g., internal fork workflow).
- When targeting upstream from a fork, confirm the upstream `nameWithOwner` and base branch with the user before creating the PR if there is any ambiguity.
- If no template exists, ask for confirmation before using a fallback custom structure.
- Keep PR descriptions as concise as possible. Describe the problem solved and the approach, not what the code technically does.
