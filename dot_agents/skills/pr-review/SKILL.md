---
name: pr-review
description: >-
  Use when asked to review a pull request or diff for correctness bugs. Applies
  a detailed-by-default review: traces changed data/APIs end-to-end, considers
  missing/null/malformed/boundary/legacy-version cases, verifies checkable
  hypotheses by running code, and reports Findings/Verified/Unverified without
  trimming scope or evidence for brevity.
---

Perform a detailed review of a diff or pull request. Never shorten investigation scope or omit verification evidence for the sake of conciseness.

## Steps

1. Read the entire diff. For every changed piece of data or API, trace it end-to-end: input → validation → transformation → exposure (API/output) → consumption by callers.
2. Follow the trace outside the diff whenever needed: validators, schemas, type definitions, generated code, and call sites that interact with the changed data/API but are not themselves part of the diff.
3. Do not conclude a path is safe just because the current/sample data happens to pass through it correctly. For each change, explicitly consider:
   - missing fields, null values, type mismatches
   - invalid array elements, empty values
   - boundary values
   - combinations with older versions/schemas (backward compatibility)
4. For each candidate defect, state:
   - the trigger condition (concrete input/state)
   - the impact
   - the exact location (file:line)
   - the supporting evidence/reasoning
   Verify important, checkable hypotheses by actually running the code with a minimal reproduction rather than reasoning about them only in the abstract.
5. Run the tests/checks relevant to the change. Keep three kinds of confirmation distinct: CI passing, verification you ran yourself, and confirmation from reading the source only (no execution).
6. Report results split into three buckets:
   - **Findings** — candidate defects, with trigger condition, impact, location, and evidence.
   - **Verified** — checked by running code/tests; include what was observed and the evidence.
   - **Unverified** — plausible but not run/confirmed; say why it wasn't verified.
7. Even when there are no findings, do not stop at "no issues found." State what was reviewed (scope) and any verification limits or gaps that remain.
8. Do not optimize for a target number of findings. Distinguish real, impactful bugs from suggestions/improvements from minor nitpicks.

## Notes

- Never trim investigation scope or verification evidence to keep the review short — thoroughness takes priority over brevity here.
- Prefer actually exercising code (tests, scripts, REPL, minimal repro) over pure static reasoning whenever a hypothesis can be checked cheaply.
- For a GitHub PR target, use `gh pr diff` / `gh pr view` as needed; for a local branch/diff, use `git diff`.
