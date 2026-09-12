---
name: pr-feedback
description: "Use when addressing PR review feedback from a GitHub PR review comment URL (e.g. .../pull/123#discussion_r456). Fetches the linked comment and the full thread context, then applies the requested code change locally."
---

Resolve a piece of GitHub PR review feedback. Given a review comment URL, fetch the entire thread the comment belongs to, understand the request in full context, then apply the change to the working tree.

## Input

A GitHub PR review (inline) comment URL of the form:

`https://github.com/<owner>/<repo>/pull/<pr>#discussion_r<comment_id>`

## Steps

1. Parse the URL. Extract `<owner>`, `<repo>`, `<pr>`, and `<comment_id>`.
2. Fetch the linked comment: `gh api repos/<owner>/<repo>/pulls/comments/<comment_id>`
3. Resolve the thread.
   - Walk `in_reply_to_id` upward until it is null — that comment is the thread root.
   - List every comment in the thread: `gh api --paginate repos/<owner>/<repo>/pulls/<pr>/comments`
   - Keep entries where `id == <root_id>` or `in_reply_to_id == <root_id>`.
   - Sort ascending by `created_at`. Include the linked comment **and every preceding comment** in the same thread; later replies in the thread should also be included so intent is not lost.
4. Capture for each retrieved comment: `user.login`, `body`, `path`, `line` (or `original_line`), `diff_hunk`, `commit_id`, `created_at`.
5. Read the referenced source. Use `path` + `line` to open the discussed region with full surrounding context. If the comment refers to a specific commit (`commit_id`) that no longer matches `HEAD`, note any drift between the discussion line and the current code.
6. Decide the change.
   - If the thread converges on a concrete request, implement that.
   - If multiple proposals were raised and none was accepted, summarize the options and ask the user which to follow before editing.
   - If the request is a question rather than a change request, draft an answer and ask the user whether to reply on GitHub or close the loop another way — do not edit code.
7. Apply the change. Match existing style; do not refactor surrounding code beyond what the comment requests.
8. Report what was changed, citing the comment author and a short verbatim quote of the decisive request, plus the comment URL.

## Notes

- Pass the URL through verbatim. Never reconstruct or guess one.
- Do not reply on GitHub, push commits, or resolve the thread from this skill. It only prepares the local change.
- If the linked PR is in a different repository than the current checkout, stop and confirm with the user — the working tree may not be the right place to apply the change.
