# Tasks - open items only

Ticked items move to `ai/tasks-archive.md` the day they are ticked.
Anything found while fixing something else goes here immediately, with what it breaks.

---

Found in Sprint 1 (2026-10-16), Shoutrrr v1.5.4 as-is. Not fixed yet. Most severe first.

1. **Anyone who can reach the app can create an account.** `/register` is open. Harmless on
   localhost; on a public address (EZEOS) a stranger could sign up and, once accounts are
   connected, see or post through them. Must be closed (registration off, or invite-only) before
   it goes on any server.
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
