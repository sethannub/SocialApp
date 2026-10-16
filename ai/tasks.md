# Tasks - open items only

Ticked items move to `ai/tasks-archive.md` the day they are ticked.
Anything found while fixing something else goes here immediately, with what it breaks.

---

## Sprint 2 - A made-up invitation code must not create an account   [OPEN]
Mode: Build (security fix in upstream code)
Why: sign-up is already off after the first user (Shoutrrr default), but `/register` with ANY
non-empty `invitation` value creates an account - proven locally 2026-10-16 with `bogus123`.
On a public server a stranger gets their own workspace and could connect their own social
accounts through OUR Meta/LinkedIn/X developer apps.
Cause (read, not guessed): `InstanceSettings::registrationsAllowed()` returns true for any
non-empty token without looking it up; `CreateNewUser` then creates the user, and the failed
`acceptByToken()` result is ignored.
Files in scope:
- `app/app/Support/InstanceSettings.php` - `registrationsAllowed()`: a token only counts if
  `WorkspaceInvitation::findByToken()` finds it and `isValid()` (not expired, not accepted)
- `app/tests/Feature/Auth/RegistrationTest.php` - new tests (below)
- `docs/UPSTREAM.md` - "Our changes to upstream" list (Apache-2.0 4b: mark changed files)
Do NOT touch:
- Any other auth code (login, passkeys, 2FA, Socialite - Socialite is off by default)
- Workspace-creation setting, Bluesky/Discord, composer, timezones (separate items below)
- The local probe user `probe-b@socialapp.test` stays until the fix is verified, then removed
Acceptance criteria:
- [ ] Owner exists + registration off: made-up invitation -> no user created, error on `email`
- [ ] Same for an expired invitation and an already-accepted invitation
- [ ] Valid invitation still registers (existing behaviour kept), with the invited email
- [ ] `/register?invitation=<made-up>` redirects to login (screen check uses the same rule)
- [ ] First user on an empty instance can still register (owner bootstrap kept)
- [ ] Full suite still passes (`scripts\test.ps1`); Pint clean on the changed files
- [ ] Re-run the local curl probe against the rebuilt container: `bogus123` is refused
- [ ] Browser: `/register?invitation=bogus123` while logged out shows the login page
- [ ] Offer to report it upstream to coollabsio/shoutrrr (user decides - outward-facing)

---

Found in Sprint 1 (2026-10-16), Shoutrrr v1.5.4 as-is. Not fixed yet. Most severe first.

1. ~~Anyone who can reach the app can create an account.~~ Corrected 2026-10-16: wrong as
   written - sign-up is off after the first user. The real hole is Sprint 2 above.
2. **The composer defaults to "Now" and "Publish now" is the main button.** CLAUDE.md says
   schedule and draft are the default paths and publish-now needs a preview of every platform's
   version plus a confirm step. Needs a mockup first (`mockup-first`).
3. **Times are shown in UTC, not Dubai time.** The test draft saved at ~12:00 Dubai showed
   "8:00 AM". The workspace timezone is unset; Shoutrrr has a "Set your workspace timezone" step.
   Check whether setting it to Asia/Dubai is enough, or whether it needs to be the default
   (CLAUDE.md standing rule 6). `[Likely]` cause, not yet checked.
4. **Bluesky and Discord can be connected with no app keys** (app password / webhook). We don't
   use them; hide or disable them so nothing gets connected by accident.
5. **Docker publishes on all network interfaces by default** (`0.0.0.0`). Worked around locally
   with `APP_PORT=127.0.0.1:8095`. On EZEOS it must sit behind the tunnel / nginx only.
6. Minor: the "release notes" link points at `.../releases/tag/` with no version (APP_VERSION not
   set in a source build). Cosmetic.
