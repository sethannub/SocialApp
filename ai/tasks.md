# Tasks - open items only

Ticked items move to `ai/tasks-archive.md` the day they are ticked.
Anything found while fixing something else goes here immediately, with what it breaks.

---

## Sprint 4 - "Publish now" behind a menu and a confirm window (variant B)   [OPEN]
Mode: Interface + Build. Decision: DESIGN_DECISIONS.md 2026-10-16 "Composer: Publish now lives in
a menu (variant B); user chooses on problems". Fixes open item 1.
Why: today one click or Ctrl+Enter publishes to every selected account with no confirm step.
How it fits Shoutrrr (read, not guessed): `PublishDispatcher::dispatchForPost()` already
publishes ready targets and marks precheck-blocked ones Failed with a reason;
`PublishController::store()` rejects the whole post with 422 before reaching it.
Files in scope (under `app/`):
- `resources/js/lib/compose/composer-state.ts` - new posts / unscheduled drafts open in `pick`
- `resources/js/components/compose/schedule-tray.tsx` - mode switch shows Queue / Pick time only;
  fill `defaultPickedAt(tz)` when Pick time has no time yet
- `resources/js/components/compose/submit-bar.tsx` - Schedule / Add to queue as the green button;
  "▾" menu with "Publish now…"; Ctrl+Enter only ever schedules/queues
- `resources/js/components/compose/publish-confirm-dialog.tsx` (new) - account list (ready vs
  problem + reason), per-account preview via the existing preview components, "Goes live now"
  with the time, focus on Cancel, "Publish to N accounts" / Cancel
- `app/Http/Controllers/Posts/PublishController.php` - accept `publish_ready_only: true`: skip
  the 422 and let the dispatcher mark the blocked targets Failed; still 422 if NO target is ready
- Tests: PHP feature test for the controller; vitest for state default, shortcut, dialog logic
Do NOT touch:
- `PublishDispatcher`, `PublishPrecheck` rules, the scheduler, timezone handling (open item 2)
- Schedule / Queue with problems keep Shoutrrr's current behaviour (block in the composer)
Acceptance criteria:
- [ ] New post opens in Pick time with a time filled in; no "Now" in the mode switch
- [ ] Ctrl+Enter schedules (or queues); it never publishes - tested
- [ ] "Publish now…" in the menu opens the confirm window; nothing is sent until its button
- [ ] Window lists every selected account, ready ones and problem ones with the reason
- [ ] With problems: button says "Publish to N accounts" (N = ready); Cancel always available;
      if none are ready, publishing is not offered
- [ ] Server: `publish_ready_only` publishes the ready targets and records the others as Failed
      with the reason; without the flag the old 422 still happens; with zero ready -> 422 - tested
- [ ] Needs-reconnect accounts are listed as problems too (not silently dropped)
- [ ] PHP + JS suites pass; lint/format/types clean
- [ ] Browser: window driven in both themes with a draft and NO connected real account
      (draft-mode testing only; nothing is published)

---

Waiting on the user:
- **Report the invitation bypass (Sprint 2) to Shoutrrr upstream?** Every Shoutrrr install has it.
  Outward-facing, so the user decides. Proposed: a private security advisory, drafted by Claude,
  sent by the user. Not a public issue.

---

Open items, most severe first.

1. **The composer defaults to "Now" and "Publish now" is the main button** (and Ctrl+Enter
   publishes instantly). CLAUDE.md: schedule and draft are the default paths; publish-now needs a
   preview of every platform's version plus a confirm step. Mocked up, waiting on the user.
2. **Times are shown in UTC, not Dubai time.** The test draft saved at ~12:00 Dubai showed
   "8:00 AM". The workspace timezone is unset; Shoutrrr has a "Set your workspace timezone" step.
   Check whether setting it to Asia/Dubai is enough, or whether it needs to be the default
   (CLAUDE.md standing rule 6). `[Likely]` cause, not yet checked.
3. **Bluesky and Discord can be connected with no app keys** (app password / webhook). We don't
   use them; hide or disable them so nothing gets connected by accident.
4. **Server checklist (before EZEOS), not code:**
   - Docker publishes on all interfaces by default (`0.0.0.0`). Locally worked around with
     `APP_PORT=127.0.0.1:8095`. On EZEOS: bind to 127.0.0.1, reach it only via the tunnel.
   - Use the production compose file / `APP_ENV=production`, `APP_DEBUG=false`. With
     `APP_ENV=local` the login page pre-fills `test@example.com` / `password` and debug pages
     show internals (`FortifyServiceProvider::defaultLoginCredentials()`, read 2026-10-16).
   - `SESSION_SECURE_COOKIE=true`, `TRUSTED_PROXIES` set for the tunnel (Shoutrrr README).
   - `APP_NAME="Y5Media Social"` (session cookie and cache prefix derive from it).
5. Minor, found in Sprint 2: `FortifyServiceProvider` passes the raw `?invitation=` query value
   to `registrationsAllowed(?string)`. A crafted `?invitation[]=x` is an array and would likely
   throw a TypeError (500 page) on the login screen. `[Likely]`, not tested. Upstream code.
6. Small, from Sprint 3: login page keeps Shoutrrr's centred layout (the branding mockup showed a
   split forest panel) and still says "Registration is disabled for this instance." (mockup:
   "New accounts by invitation only."). Ask the user whether either matters.
7. Known baseline JS test failure, not ours: `build-feedback-payload.test.ts` expects
   `application/json` but gets `application/json;charset=utf-8` in the bun 1.4.3 container (also
   fails on untouched upstream). Leave unless it starts hiding real failures.
