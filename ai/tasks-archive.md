# Tasks archive - finished items, moved here from `ai/tasks.md`

---

## Sprint 2 - A made-up invitation code must not create an account   [DONE 2026-10-16]
Mode: Build (security fix in upstream code)
Why: `/register` with ANY non-empty `invitation` value created an account (proven locally).
Acceptance criteria:
- [x] Owner exists + registration off: made-up invitation -> no user created, error on `email`
- [x] Same for an expired invitation and an already-accepted invitation
- [x] Valid invitation still registers (existing behaviour kept), with the invited email
- [x] `/register?invitation=<made-up>` redirects to login (screen check uses the same rule)
- [x] First user on an empty instance can still register (owner bootstrap kept)
- [x] Full suite still passes (`scripts\test.ps1`); Pint clean on the changed files
- [x] Re-run the local curl probe against the rebuilt container: `bogus123` is refused
- [x] Browser: `/register?invitation=bogus123` while logged out shows the login page
- [x] Offer to report it upstream to coollabsio/shoutrrr (user decides - outward-facing)

Also from Sprint 1's list: item 1 ("anyone can create an account") was wrong as written and was
replaced by this sprint.

---

## Sprint 1 - Bring in Shoutrrr and run it on this PC, drafts only   [DONE 2026-10-16]
Mode: Build
Why: get the open-source base (Shoutrrr v1.5.4) into our repo and see it working locally, with no
way for it to publish anything, before we change a line of it.
Acceptance criteria:
- [x] `app/` contains Shoutrrr v1.5.4 with its LICENSE intact; history kept via git subtree
- [x] `app/.env` exists locally, is git-ignored, and has no platform credentials
- [x] Docker Desktop started; Shoutrrr's development compose file builds and starts
- [x] Shoutrrr's own test suite runs inside the container; the pass/fail count is recorded as-is
- [x] App opens in the browser; a local user is registered; a draft post is written, saved and
      reopened; confirmed that no social account is connected
- [x] Screen opened in the browser and driven (Claude in Chrome), screenshots taken
- [x] `docs/STATE.md` and `docs/UPSTREAM.md` say how to start, stop and update it
