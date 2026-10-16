# Results - one entry per finished sprint

A sprint is not finished until it has an entry here. Newest at the top.

Entry shape:

## <date> - <sprint name>
- Mode: Build / Audit / Debug / Interface
- What changed (files)
- Acceptance criteria: each one, met or not
- Review findings and what was fixed
- What was looked at in the browser, and what could NOT be seen
- Commit sha

---

## 2026-10-16 - Sprint 4: "Publish now" behind a menu and a confirm window (variant B)
- Mode: Interface + Build. Decision: DESIGN_DECISIONS.md 2026-10-16 (variant B, user chooses on
  problems). Mockup: https://claude.ai/artifact/8m24A4VtfJhA7pExNeav71
- What changed (all marked "SocialApp change:"):
  - `lib/compose/composer-state.ts`: posts open in Pick time; `'now'` removed from `ScheduleMode`
    so the type checker rejects any path back to it.
  - `components/compose/schedule-tray.tsx`: Queue / Pick time only; fills `defaultPickedAt(tz)`.
  - `components/compose/submit-bar.tsx`: main button = Schedule / Add to queue (Ctrl+Enter too);
    "▾" menu -> "Publish now…" -> confirm window; `handlePublishNow()` is the only publish call;
    `TrayButton` forwards props/ref (needed as a Base UI render target).
  - `components/compose/publish-confirm-dialog.tsx` (new): `planPublish()` + the window.
  - `components/compose/composer.tsx`: shared `previewFor(account)`; new SubmitBar props.
  - `app/Http/Controllers/Posts/PublishController.php`: `publish_ready_only` flag.
  - Tests: `tests/Feature/Publishing/PublishReadyOnlyTest.php` (4),
    `__tests__/publish-confirm-dialog.test.ts` (7), updated composer-state + submit-bar tests.
  - Scripts: `scripts/format-js.sh` (oxfmt write via container); `run-js-tests.sh` prints
    explicit `oxfmt exit` / `tsc exit`; `test.ps1` takes `$env:PEST_FILTER`.
- Acceptance criteria:
  - Opens in Pick time with a time; no Now: met (seen: "Queue | Pick time", "Oct 16, 12:00 PM").
  - Ctrl+Enter never publishes: met by construction (`'now'` gone from the type; `handleSubmit`
    has no publish call - asserted by test). Not pressed in the browser.
  - Menu opens the window; nothing sent until its button: met (seen; queue empty afterwards).
  - Window lists every account with reasons; "Publish to N accounts"; none-ready = no button:
    met (seen with 3 fake accounts: LinkedIn "Needs reconnect" first, 2 Ready, "Publish to 2
    accounts"); none-ready case covered by test only, not seen.
  - Server flag: met. 4 tests; 2 of them fail on the original controller (proves they test it).
  - Needs-reconnect listed as a problem: met (seen).
  - PHP **2039 passed**, Pint clean; JS **908/909** (1 = known baseline), oxlint 0/0,
    **oxfmt exit 0**, tsc exit 0.
  - Browser both themes: met (light + dark screenshots; dark via a page-only class toggle).
  - Keyboard focus starts on Cancel: met (checked `document.activeElement` = Cancel).
- Review findings, most severe first:
  - **Correction to Sprint 3:** it claimed "oxfmt clean"; 5 Sprint 3 files were not formatted.
    My output filter hid the file list. Fixed now (all 428 files pass) and the script prints an
    explicit exit code so an empty section can't be read as a pass again.
  - The X preview (Shoutrrr's own, also in the composer) shows "Single post 318/280" in red while
    the saved X target auto-splits into 2 parts and the server precheck finds no problem. Upstream
    inconsistency, not fixed -> `ai/tasks.md`.
  - Needs-reconnect accounts: with `publish_ready_only` the server still dispatches them (the
    precheck doesn't know about expired logins); they fail as auth errors -> Failed / needs
    attention. `[Likely]` from reading, not seen. Same visible outcome as "skipped" but one doomed
    API call. Not fixed -> `ai/tasks.md`.
  - "Goes live" time shows "(UTC)" - open item 2 (Dubai time), shown honestly.
- Test data: 3 fake credential-less accounts + a "LOCAL TEST, NOT FOR PUBLISHING" draft created,
  window opened twice and cancelled, nothing published (posts Draft, targets pending, 0 jobs),
  then all removed by direct row delete (the app's delete action queues platform delete jobs).
- Could NOT see: Ctrl+Enter pressed for real; the none-ready window; a real publish (by design).
- Commit: see git log ("Sprint 4: ...")

## 2026-10-16 - Sprint 3: rebrand to Y5Media Social
- Mode: Interface. Decision + mockup: DESIGN_DECISIONS.md "Branding: Y5Media palette, forest-green
  menu (variant A)", https://claude.ai/artifact/HzQGeog8wXPSfLcyp6XFh8 v2.
- What changed: see `docs/UPSTREAM.md` Sprint 3 rows. New: `scripts/branding/` (icon master page,
  `make-icons.php`, `build-icons.ps1`), `scripts/run-js-tests.sh` (vitest + oxlint + oxfmt + tsc
  in a throwaway container, with Wayfinder helpers generated). `scripts/test.ps1` now also mounts
  `resources/`. Local data: workspace renamed to "Y5Media".
- Acceptance criteria:
  - No "Shoutrrr" visible on login, dashboard, menu, posts: met (seen in browser). Remaining
    occurrences are identifiers, billing-only text (SELF_HOSTED=false only) and the MCP server
    name seen by AI clients - all listed as out of scope in the plan.
  - Forest menu, Y5 mark, gradient buttons, Y5Media palette, both themes: met (seen).
  - Status chips keep words: "Draft" chip seen in both themes; other states not seen (no data).
  - Icons regenerated at every expected size: met (contact sheet looked at, 16px legible).
  - PHP: **2035 passed**, Pint clean. JS: **901/902 passed**, oxlint 0/0, tsc clean.
    ~~oxfmt clean~~ WRONG - 5 files were unformatted; found and fixed in Sprint 4.
    The 1 failure (`build-feedback-payload.test.ts`, "application/json;charset=utf-8") also
    fails on the untouched upstream tree in the same container -> pre-existing, not ours.
  - Browser: dashboard light + dark (Claude in Chrome), login light + dark (headless, logged out).
- Review findings, most severe first:
  - Menu logo rendered at 16px: SidebarMenuButton forces `[&_svg]:size-4`. **Fixed** with
    `h-auto! w-8!` on the logo only.
  - Lime logo on white (login, error, MCP pages) would be near-invisible. **Fixed**: new
    `--brand-mark` token (green ink #2F6B00 light, lime dark).
  - My own comment contained "Star on GitHub" and tripped the absence test. **Fixed** (reworded).
  - Changing APP_NAME changes the session cookie / cache prefix (slug of APP_NAME): everyone is
    logged out once after deploy. Accepted, noted for the server checklist.
  - Differences from the mockup, not fixed (small, ask user): login keeps Shoutrrr's centred
    layout (mockup showed a split forest panel); footer text still "Registration is disabled for
    this instance." (mockup: "New accounts by invitation only.").
- Could NOT see: the welcome box (only shows on first login), emails (no mail sent; MAIL=log),
  phone home-screen icon on a real phone, Failed / Needs-reconnect chips (no accounts connected).
- Commit: see git log ("Sprint 3: ...")

## 2026-10-16 - Sprint 2: a made-up invitation code must not create an account
- Mode: Build (security fix in upstream code)
- Correction first: Sprint 1's item 1 ("anyone can create an account") was wrong. Shoutrrr turns
  sign-up off once the first user (the owner) exists; I had tested as the first user.
- The real hole: `InstanceSettings::registrationsAllowed()` accepted any non-empty invitation
  token without looking it up. Proven locally: POST /register with `invitation=bogus123` ->
  account created and logged in. Covers the Socialite path too (same function).
- What changed:
  - `app/app/Support/InstanceSettings.php`: new `invitationTokenIsValid()` (token must match a
    pending, unexpired invitation); `registrationsAllowed()` uses it. Marked "SocialApp change:".
  - `app/tests/Feature/Auth/RegistrationTest.php`: 5 new tests.
  - `scripts/test.ps1` / `run-tests.sh`: working-tree PHP code is now mounted (tests see edits
    without a rebuild); optional Pint check via `$env:PINT_FILES`.
  - `docs/UPSTREAM.md`: "Our changes to upstream" table.
- Acceptance criteria: all met (see `ai/tasks-archive.md`).
  - Full suite: **2035 passed** (2030 + 5 new), Pint clean on both files.
  - The new tests prove something: run against the ORIGINAL code in the image, the 4 security
    tests FAIL and "valid invitation still registers" passes; against the fix all pass.
  - Rebuilt container, curl probe: GET `/register?invitation=bogus123` -> 302 to /login;
    POST with `bogus123` -> refused, no user created.
- Review findings: social login path covered (same function); invitee email still forced to the
  invited address (tested); first-user bootstrap unchanged (existing test passes). New, not
  fixed (out of scope, upstream): array `?invitation[]=` likely 500s the login page -> tasks #7.
  Login page pre-fills test credentials when `APP_ENV=local` only (read) -> server checklist #5.
- Browser: logged the test user out, opened `/register?invitation=bogus123` -> login page with
  "Registration is disabled for this instance." Could NOT see: the user menu's log-out item
  (page reader did not expose the dropdown; logged out via the app's own POST /logout instead).
- Cleanup: local probe user `probe-b@socialapp.test` and its empty workspace removed.
- Commit: see git log ("Sprint 2: ...")

## 2026-10-16 - Sprint 1: bring in Shoutrrr, run it locally, drafts only
- Mode: Build
- What changed: `app/` = Shoutrrr v1.5.4 via `git subtree` (commit 6749d26, unchanged upstream);
  `docs/UPSTREAM.md` (new); `docs/STATE.md` (run instructions); `scripts/test.ps1` +
  `scripts/run-tests.sh` (new); `.gitattributes` (LF for .sh); `.gitignore` (`scripts/out/`).
  Local only, not committed: `app/.env` (app key, port, `MAIL_MAILER=log`, local test login).
- Acceptance criteria: all met (see `ai/tasks-archive.md`).
  - Tests: **2030 passed, 0 failed** (7470 assertions). First runs showed 849 failures and then
    2030 warnings - both were my test setup (no APP_KEY, then no `.env` file in the throwaway
    container), fixed in `scripts/run-tests.sh`, not Shoutrrr code.
- Review findings (adversarial pass), most severe first:
  - Port bound to `0.0.0.0` = reachable from the office network. **Fixed**: `APP_PORT=127.0.0.1:8095`.
  - Port 8080 already used by the Odysseus stack. **Fixed**: moved to 8095, Odysseus untouched.
  - Bluesky/Discord connect without keys, so "no keys = cannot publish" was not the whole story.
    Real safeguard: no account connected (verified on /accounts: "No accounts connected").
  - Open registration, publish-now default, UTC times, Bluesky/Discord, release link: **not fixed**,
    upstream behaviour, moved to `ai/tasks.md` items 1-6.
  - `.env` with secrets: verified git-ignored (`git check-ignore`), not in `git status`.
  - Shoutrrr ships its own `CLAUDE.md`/`.claude/skills`/`.mcp.json`: read; coding conventions and a
    local Laravel Boost MCP only. Recorded in `docs/UPSTREAM.md`.
- Browser (Claude in Chrome, http://localhost:8095): login page, registered local test user,
  dashboard, skipped "Connect an account", wrote + saved a draft (list shows "Draft", "0
  accounts"), reopened it from Posts (text intact, "No accounts", Publish now disabled), Accounts
  page shows "No accounts connected".
  Could NOT see: screenshots of the reopened draft's later pages timed out twice (tab likely in the
  background); Accounts page was checked via the page's text instead. Dark theme not looked at.
  Media upload not tried. Nothing about real platform behaviour can be seen without accounts.
- Commit: see git log ("Sprint 1: ...")

## 2026-10-15 - Project setup
- Mode: none (setup)
- Created `CLAUDE.md`, `docs/STATE.md`, `docs/DESIGN_DECISIONS.md`, `ai/tasks.md`, `ai/results.md`
  and five project skills in `.claude/skills/`.
- Method adopted from `ezelink-accounting` and `Email-Marketing-v2`.
- No commit: not a git repository yet.
