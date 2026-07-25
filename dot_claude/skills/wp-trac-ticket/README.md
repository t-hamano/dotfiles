# wp-trac-ticket

Look up a WordPress Trac ticket by number or URL.

```
/wp-trac-ticket 30000
/wp-trac-ticket #30000
/wp-trac-ticket https://core.trac.wordpress.org/ticket/30000
```

The default output surfaces everything visible on the ticket page: all metadata fields, the description, attachments, related changesets, the full comment discussion, and any linked GitHub pull requests. For a quick metadata-only view (no comments/attachments/changesets/PRs), use `--short`:

```
/wp-trac-ticket --short 30000
```

## Origin

Ported from [sirreal/agent-skills](https://github.com/sirreal/agent-skills/tree/main/plugins/wordpress-trac/skills/wp-trac-ticket) (GPL-2.0).
