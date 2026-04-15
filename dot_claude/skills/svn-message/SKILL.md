---
name: svn-message
description: Generate an appropriate SVN commit title and description for a WordPress SVN repository, referencing WordPress commit message best practices, the GitHub PR, and the WordPress Trac ticket.
---

Generate a WordPress SVN commit title and commit description by gathering context from multiple sources.

## Arguments

- `$1` — GitHub PR URL (required)

## Steps

### 1. Read WordPress commit message best practices

Fetch and internalize the guidelines at:
https://make.wordpress.org/core/handbook/best-practices/commit-messages/

Key rules to apply:
- First line: concise summary ≤ 52 characters, written in imperative mood, no trailing period.
- Leave a blank line after the first line.
- Body lines: wrap at 72 characters.

### 2. Fetch all PR information

The GitHub repository is synchronized with the SVN repository.　Retrieve all PR information in a single request:

```
gh pr view <PR-URL> --json title,body,files,comments,reviews
```

Extract from the result:
- `title` — PR title
- `body` — PR description; parse out any Trac ticket link matching `https://core.trac.wordpress.org/ticket/{ticket_number}`
- `files[].path` — list of changed file paths
- `files[].patch` — per-file diff (may be truncated for large files; if so, note it)
- `comments`, `reviews` — all PR discussion

If the diff is empty, warn the user and stop.

### 3. Check past commits for changed files

From the file paths collected in step 2, pick one representative file per unique directory (skip newly added files), up to a maximum of 5 directories. For each selected file, retrieve recent commit history:

```
gh api repos/{owner}/{repo}/commits -F path=<file-path> -F per_page=10 --jq '.[].commit.message'
```

Read the past commit messages to understand:
- The style and conventions used in this area of the codebase.
- What language and terminology are typically used.

### 4. Fetch the WordPress Trac ticket

If a Trac ticket URL was found in the PR, fetch it with WebFetch.

Extract:
- Ticket title and description.
- Comments and patch discussion.

### 5. Synthesize and generate the commit message

Using all gathered context, produce:

**Commit title** (first line, ≤ 52 chars, imperative mood, plain text):
```
<Concise summary of the change>
```

**Commit description** (body, lines ≤ 72 chars, plain text):
```
<Detailed explanation of what changed and why,
drawn from the PR discussion and Trac ticket.>
```

## Notes

- Never run `svn commit` automatically; only generate and display the message.
- If `$1` is not provided, ask the user for the GitHub PR URL before proceeding.
