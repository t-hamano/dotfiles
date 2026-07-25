# wp-trac-auth

Set up or refresh WordPress Trac authentication. Checks your current cookie and, when it's missing or expired, walks you through copying the `Cookie:` request header from a logged-in browser session, saves it securely, and verifies it works. The other skills defer to this one automatically when a request fails with an auth-required error.

```
/wp-trac-auth
```

## Origin

Ported from [sirreal/agent-skills](https://github.com/sirreal/agent-skills/tree/main/plugins/wordpress-trac/skills/wp-trac-auth) (GPL-2.0).
