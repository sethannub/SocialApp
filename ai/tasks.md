# Tasks - open items only

Ticked items move to `ai/tasks-archive.md` the day they are ticked.
Anything found while fixing something else goes here immediately, with what it breaks.

---

Waiting on the user:
- **Report the invitation bypass (Sprint 2) to Shoutrrr upstream?** Every Shoutrrr install has it.
  Outward-facing, so the user decides. Proposed: a private security advisory, drafted by Claude,
  sent by the user. Not a public issue.

---

Open items, most severe first. Items 2-6 found in Sprint 1 (2026-10-16), Shoutrrr v1.5.4 as-is.

2. **The composer defaults to "Now" and "Publish now" is the main button.** CLAUDE.md says
   schedule and draft are the default paths and publish-now needs a preview of every platform's
   version plus a confirm step. Needs a mockup first (`mockup-first`).
3. **Times are shown in UTC, not Dubai time.** The test draft saved at ~12:00 Dubai showed
   "8:00 AM". The workspace timezone is unset; Shoutrrr has a "Set your workspace timezone" step.
   Check whether setting it to Asia/Dubai is enough, or whether it needs to be the default
   (CLAUDE.md standing rule 6). `[Likely]` cause, not yet checked.
4. **Bluesky and Discord can be connected with no app keys** (app password / webhook). We don't
   use them; hide or disable them so nothing gets connected by accident.
5. **Server checklist (before EZEOS), not code:**
   - Docker publishes on all interfaces by default (`0.0.0.0`). Locally worked around with
     `APP_PORT=127.0.0.1:8095`. On EZEOS: bind to 127.0.0.1, reach it only via the tunnel.
   - Use the production compose file / `APP_ENV=production`, `APP_DEBUG=false`. With
     `APP_ENV=local` the login page pre-fills `test@example.com` / `password` and debug pages
     show internals (`FortifyServiceProvider::defaultLoginCredentials()`, read 2026-10-16).
   - `SESSION_SECURE_COOKIE=true`, `TRUSTED_PROXIES` set for the tunnel (Shoutrrr README).
6. Minor: the "release notes" link points at `.../releases/tag/` with no version (APP_VERSION not
   set in a source build). Cosmetic.
7. Minor, found in Sprint 2: `FortifyServiceProvider` passes the raw `?invitation=` query value
   to `registrationsAllowed(?string)`. A crafted `?invitation[]=x` is an array and would likely
   throw a TypeError (500 page) on the login screen. `[Likely]`, not tested. Upstream code.
