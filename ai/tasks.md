# Tasks - open items only

Ticked items move to `ai/tasks-archive.md` the day they are ticked.
Anything found while fixing something else goes here immediately, with what it breaks.

---

## Sprint 3 - Rebrand to Y5Media Social (name, logo, icons, colours, links)   [OPEN]
Mode: Interface
Why: the Shoutrrr name/logo confuses the user; decided 2026-10-16 (DESIGN_DECISIONS.md, branding
variant A). Mockup: https://claude.ai/artifact/HzQGeog8wXPSfLcyp6XFh8 (v2).
Files in scope (all under `app/`):
- Colours: `resources/css/app.css` (`:root` + `.dark` tokens, sidebar tokens, primary gradient)
- Logo: `resources/js/components/layout/app-logo-icon.tsx` (Y5 mark, fill currentColor),
  `app-logo.tsx` (name)
- Name shown on screen: `config/app.php` default, `resources/js/app.tsx` fallback,
  `public/site.webmanifest`, `.env.example*` APP_NAME, and the visible "Shoutrrr" strings in
  `pages/auth/login.tsx`, `pages/auth/register.tsx`, `components/onboarding/welcome-modal.tsx`,
  `pages/engagement/index.tsx`, `pages/error.tsx`, `pages/sync.tsx`, `pages/share/show.tsx`,
  `pages/settings/workspace/api-keys.tsx`, `pages/mcp/landing.tsx`,
  `components/settings/create-sync-pipeline-dialog.tsx`, `views/vendor/mail/html/header.blade.php`,
  `app/Http/Controllers/Settings/NativeTrackingController.php`, `config/scramble.php`
- Links: remove "Star on GitHub"/"Sponsor" (`sidebar-footer-card.tsx`) and the version/release-notes
  badge (`app-sidebar.tsx`)
- Icons: regenerate every file in `public/` (favicons, apple/android/mstile, icon-*.png,
  favicon.ico, favicon.svg, shoutrrr.png mail logo) from the Y5 mark, lime on forest
- Tests that pin the old brand: `tests/Feature/AppIconAssetsTest.php`,
  `resources/js/layouts/auth/__tests__/auth-brand-text.test.ts`, `pages/engagement/index.test.ts`
- Local data, no code: rename the local workspace to "Y5Media"
Do NOT touch:
- Internal identifiers (storage keys `shoutrrr:*`, class `ShoutrrrServer`, cache lock names,
  `price_shoutrrr_*`) - invisible, renaming them only grows the upstream diff
- "Shoutrrr Cloud" billing text (only shown when SELF_HOSTED=false; we run self-hosted)
- Test fixtures that use "shoutrrr" as sample data (handles, URLs in linkify tests)
- `app/LICENSE`; composer behaviour (Sprint 4, waiting on decisions)
Acceptance criteria:
- [ ] No "Shoutrrr" text or megaphone visible on: login, register, dashboard, menu, welcome box,
      posts, accounts, engagement, error page; browser tab says "Y5Media Social"
- [ ] Menu is forest green with the Y5 mark; buttons use the lime->green gradient; page and greys
      use the Y5Media palette - in both light and dark themes
- [ ] Status chips still carry words and stay readable in both themes (rule 7)
- [ ] All icon files regenerated at the sizes the icon test expects
- [ ] PHP suite passes (`scripts\test.ps1`), JS suite passes (vitest), Pint clean
- [ ] Rebuilt, opened in the browser, screenshots of login + dashboard in both themes
- [ ] Changed upstream files listed in `docs/UPSTREAM.md`

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
