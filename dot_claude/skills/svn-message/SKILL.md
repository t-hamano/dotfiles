---
name: svn-message
description: >-
  Generate an appropriate SVN commit title and description for a WordPress SVN
  repository, referencing WordPress commit message best practices, the GitHub
  PR, and the WordPress Trac ticket.
---

Generate a WordPress SVN commit title and commit description by gathering context from multiple sources.

## Arguments

- `$1` — GitHub PR URL (required)

## Steps

### 1. Read WordPress commit message best practices

Fetch and internalize the guidelines at:
https://make.wordpress.org/core/handbook/best-practices/commit-messages/

Key rules to apply:
- First line: concise summary, aim for around 50 characters, stopping at 70. Written in imperative mood, no trailing period.
- Leave a blank line after the first line.
- Body: write each paragraph as a single unbroken line. Do NOT insert hard line breaks mid-sentence to wrap at any column width. Only break lines between paragraphs, after a blank line.

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

If a Trac ticket URL was found in the PR, fetch it with the `wp-trac-ticket`
skill in default mode (comments and changesets are required — do not use
`--short`). Do not use WebFetch: Trac serves an HTML login page instead of
ticket data for unauthenticated requests, so the fetched content is unreliable.

If the script reports that auth is required, invoke `wp-trac-auth` to
(re)authenticate, then re-run the lookup.

Extract:
- Ticket title and description.
- Comments and patch discussion.
- Comment authors and the reporter — candidates for the `Props` line.
- Referenced changesets — candidates for a `Follow-up to [NNNNN].` line.
- Ticket number — for the `Fixes #NNNNN.` / `See #NNNNN.` trailer.

### 5. Synthesize and generate the commit message

Using all gathered context, produce:

**Commit title** (first line, ~50 chars, max 70, imperative mood, plain text):
```
<Concise summary of the change>
```

**Commit description** (body, plain text; each paragraph on one unbroken line — never wrap mid-sentence):
```
<Explanation of what changed and why, drawn from the PR discussion and Trac ticket, written as one continuous line per paragraph.>
```

Description rules:
- Keep it to two paragraphs at most; one paragraph is often enough. Cut restatements of the diff and anything the title already says.
- Describe the change in terms of behavior and user-visible effect, not implementation detail. Avoid function, method, class, hook, and variable names, and file paths, unless naming one is the only way to make the change understandable.

**Trailers** (final paragraph, after a blank line), in WordPress order:
```
Follow-up to [NNNNN].          (only when the change builds on a prior changeset)
Props username1, username2.    (ticket reporter, commenters, PR author, reviewers — omit the committer)
Fixes #NNNNN.                  (use `See #NNNNN.` when the ticket stays open)
```

Do not invent props names. Use only usernames that actually appear in the Trac
ticket or the PR, and list them in order of contribution.

## Notes

- Never run `svn commit` automatically; only generate and display the message.
- If `$1` is not provided, ask the user for the GitHub PR URL before proceeding.
