# Tasks - open items only

Ticked items move to `ai/tasks-archive.md` the day they are ticked.
Anything found while fixing something else goes here immediately, with what it breaks.

---

---

---

---

Waiting on the user:
- **Reset the Meta app secret.** On 2026-10-16 the user pasted live Facebook Page access tokens
  into the chat. They stay valid until revoked. Steps were given (developers.facebook.com ->
  app -> Settings -> Basic -> App secret -> Reset; check with the access token debugger). The
  tokens are not stored anywhere in this project. Later, the App ID + new secret go into
  `app/.env` by the user, never into the chat.
- **Report the invitation bypass (Sprint 2) to Shoutrrr upstream?** Every Shoutrrr install has it.
  Outward-facing, so the user decides. Proposed: a private security advisory, drafted by Claude,
  sent by the user. Not a public issue.
- **Which account is safe to test real publishing on** (STATE.md "Test accounts"). Until then:
  drafts only, fake credential-less accounts for screens.

---

Open items, most severe first.

1. **Needs-reconnect accounts are still attempted** when publishing to the ready ones (Sprint 4).
   The server precheck doesn't know about expired logins, so the dispatcher sends a job with a dead
   token; it fails as an auth error (Failed / needs attention). Same visible result as "skipped",
   but one doomed API call. `[Likely]` from reading `PublishDispatcher`, not seen.
2. **Server checklist (before EZEOS), not code:**
   - Docker publishes on all interfaces by default (`0.0.0.0`). Locally worked around with
     `APP_PORT=127.0.0.1:8095`. On EZEOS: bind to 127.0.0.1, reach it only via the tunnel.
   - Use the production compose file / `APP_ENV=production`, `APP_DEBUG=false`. With
     `APP_ENV=local` the login page pre-fills `test@example.com` / `password` and debug pages
     show internals (`FortifyServiceProvider::defaultLoginCredentials()`, read 2026-10-16).
   - `SESSION_SECURE_COOKIE=true`, `TRUSTED_PROXIES` set for the tunnel (Shoutrrr README).
   - `APP_NAME="Y5Media Social"` (session cookie and cache prefix derive from it).
3. Minor, found in Sprint 2: `FortifyServiceProvider` passes the raw `?invitation=` query value
   to `registrationsAllowed(?string)`. A crafted `?invitation[]=x` is an array and would likely
   throw a TypeError (500 page) on the login screen. `[Likely]`, not tested. Upstream code.
4. Small, from Sprint 3: login page keeps Shoutrrr's centred layout (the branding mockup showed a
   split forest panel) and still says "Registration is disabled for this instance." (mockup:
   "New accounts by invitation only."). Ask the user whether either matters.
5. Known baseline JS test failure, not ours: `build-feedback-payload.test.ts` expects
   `application/json` but gets `application/json;charset=utf-8` in the bun 1.4.3 container (also
   fails on untouched upstream). Leave unless it starts hiding real failures.
6. Small, from Sprint 6: in a multi-paragraph post the editor's inline thread marker still shows an
   over-limit paragraph's own count in red (`tiptap/section-markers.ts` uses paragraph packing), even
   though the server and the preview split it on words. Cosmetic but confusing; decide how markers
   should show a mid-paragraph split (mockup first).
