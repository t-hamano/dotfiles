---
name: commit
description: Review differences from the main branch and create a commit
---

Review differences between the current branch and the main branch (main or trunk), then create a commit.

## Steps

1. Use `git branch` to identify the main branch name (main or trunk).
2. Use `git diff <main-branch>...HEAD` to review all changes on the current branch.
3. Use `git status` to check the working tree state.
4. If there are unstaged changes, stage the relevant files.
5. Analyze the diff, understand the purpose of the changes, and craft a concise, accurate commit message.
6. Create the commit.

## Notes

- Write commit messages in English and emphasize why the change exists.
- Do not commit `.env` files or secret files.
- If there are no changes to stage, report that explicitly.
