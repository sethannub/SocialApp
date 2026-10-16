# Upstream - Shoutrrr

`app/` is Shoutrrr (https://github.com/coollabsio/shoutrrr, Apache-2.0), brought in with
`git subtree`, so its history is inside our repo and newer versions can be merged in.

| | |
|---|---|
| Current version | **v1.5.4** (commit `2abb809`), added 2026-10-16 |
| Git remote | `upstream` -> `https://github.com/coollabsio/shoutrrr.git` |
| Licence | Apache-2.0 - keep `app/LICENSE`; mark files we change (Apache-2.0 section 4b) |

## Updating to a newer Shoutrrr version

Its own sprint (Audit mode first: read the upstream changelog/diff, list what touches our changes).

```
git fetch upstream tag vX.Y.Z --no-tags
git subtree pull --prefix=app upstream vX.Y.Z -m "Update Shoutrrr to vX.Y.Z"
```

Conflicts land only where we changed upstream files. Then rebuild, run the test suite, update the
version line above.

## Files that came with Shoutrrr and are not ours

`app/CLAUDE.md`, `app/AGENTS.md`, `app/.claude/skills/*`, `app/.mcp.json`, `app/.cursor`,
`app/.codex`, `app/.agents`, `app/.ai` are Shoutrrr's own AI-tool instructions (Laravel Boost
coding conventions: Pest, bun, oxlint, Pint). They apply when editing code inside `app/`. Our
root `CLAUDE.md` wins on how we work (sprints, publishing safety, plain words).

## Our changes to upstream

Each changed upstream file carries a "SocialApp change:" comment at the change.

| File | Change | Why | Sprint |
|---|---|---|---|
| `app/app/Support/InstanceSettings.php` | `registrationsAllowed()` only lets an invitation token bypass closed registration if it is a real, pending, unexpired invitation (new `invitationTokenIsValid()`) | Upstream accepted any non-empty string, so `/register?invitation=anything` created an account | 2 |
| `app/tests/Feature/Auth/RegistrationTest.php` | 5 tests: made-up / expired / accepted tokens refused, valid invite works, register screen redirects | Cover the fix above | 2 |
