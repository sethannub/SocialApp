# STATE - SocialApp

Last updated: 2026-10-16 (end of session). Keep under ~120 lines. History goes to
`docs/state-archive/` (latest: `STATE-2026-10-16.md` - fork survey, closed decisions).

## Where we are

- **Y5Media Social** = Shoutrrr v1.5.4 (`app/`, git subtree, Apache-2.0) with our changes listed
  in `docs/UPSTREAM.md`. Six sprints done 2026-10-16 (`ai/results.md`):
  1. Runs locally in Docker, drafts only.
  2. Sign-up bypass closed (a made-up `?invitation=` code created an account).
  3. Rebranded: name, Y5 logo, icons, Y5Media colours, forest-green menu; Shoutrrr links removed.
  4. "Publish now" only via ▾ menu -> confirm window (every account, reasons, user chooses);
     opens in Pick time; Ctrl+Enter never publishes.
  5. Dubai time on screen (`APP_DISPLAY_TIMEZONE`); every schedule time stored as UTC (fixed an
     upstream bug that saved "14:30+04:00" as 14:30 UTC).
  6. Preview / counter split long text exactly like the server (server-generated parity fixture).
- **Nothing has ever been published.** No real social account is connected; no platform keys in
  `app/.env`.
- On GitHub: everything up to `25971a7` (end-of-session save, 2026-10-16).

## Run it locally

- Start Docker Desktop, then from `C:\Codex\SocialApp`:
  `docker compose -f app/docker-compose.development.yaml --project-directory app up -d --build`
  Stop: same with `down`. Data in Docker volumes `shoutrrr_sqlite` / `shoutrrr_storage`
  (`down -v` wipes them).
- Opens at **http://localhost:8095**, this PC only (`APP_PORT=127.0.0.1:8095`; port 8080 belongs
  to the Odysseus stack). Local test login: `owner@socialapp.test`, password in `app/.env`
  (`TEST_LOGIN_PASSWORD`, git-ignored). Workspace renamed "Y5Media".
- Checks (all run in throwaway containers, nothing installed on this PC):
  - PHP: `.\scripts\test.ps1` - Pest; set `$env:PINT_FILES` for Pint + Larastan on those files,
    `$env:PEST_FILTER` for one file. Baseline: **2055 passed**.
  - JS: `docker run --rm --entrypoint sh -v "$PWD\app:/src:ro" -v "$PWD\scripts\out:/out" -v "$PWD\scripts:/scripts:ro" shoutrrr:dev /scripts/run-js-tests.sh`
    -> `scripts/out/js.txt` (vitest, oxlint, `oxfmt exit`, `tsc exit`). Baseline: **920/921**,
    the 1 failure is upstream's (`build-feedback-payload.test.ts`), also fails untouched.
  - Format JS: `scripts/format-js.sh <files>` then copy from `scripts/out/fmt/`.
  - Icons: `.\scripts\branding\build-icons.ps1`.
- Rebuild the image after changing anything in `app/public` or frontend code (the test scripts
  mount PHP + `resources/`, but `public/` comes from the image).
- PowerShell 5.1 gotchas met today: `""` args are dropped, embedded quotes break native args ->
  use script files and `git commit -F <file>`; `!` commands run in Git Bash -> forward slashes.

## Testing rules in force

- Draft mode only. For screens that need accounts, create **fake credential-less** accounts
  (`remote_account_id` like `fake-%`) and remove them by direct row delete afterwards (the app's
  delete action queues platform delete jobs). Never press Publish / Schedule with them.

## Open decisions (waiting on the user)

1. **Reset the Meta app secret** - live Facebook Page tokens were pasted into the chat on
   2026-10-16. Not stored anywhere in the project. Steps were given.
2. **Report the sign-up bypass to Shoutrrr** (private advisory drafted by Claude, sent by user)?
3. **Test accounts** - which account is safe for a first real test post.
4. Small: login page layout / wording vs the branding mockup (`ai/tasks.md` item 6).

## Hosting candidate: EZEOS (read-only check 2026-10-16)

- `ssh ezeos-live`. Also runs the live CRM (leads.y5media.net), reports, marketing-v2, portal.
- Room: 8 cores, 15 GB RAM (~14 GB available), 228 GB disk free. Load avg 3-4; postgres ~60% CPU
  at the time of the check (one snapshot, cause not checked).
- Host PHP 8.4.21 - Shoutrrr needs 8.5, so it runs in Docker. Docker 26.1.3 + Compose 2.27
  installed but the daemon is **stopped**; starting it is a change to a live server (user's yes).
- Public access via Cloudflare Tunnel (`cloudflared-leads.service`). `social.y5media.net` needs a
  tunnel route + Cloudflare DNS access for y5media.net (who has it: not yet answered).
- Server checklist before deploying: `ai/tasks.md` item 4.

## Platform access

- A Meta developer app exists with access to the Y5Media Page (Page ID 105953344742297) - seen in
  what the user pasted. Shoutrrr needs that app's App ID + App secret (`FACEBOOK_CLIENT_ID` /
  `FACEBOOK_CLIENT_SECRET`), then "Connect" in the app. LinkedIn / X / Threads apps: unknown.
- No platform limit or API rule was verified against official docs yet (`platform-rules`).

## Test accounts (safe to post to during development)

None recorded yet. Until there are some, development uses draft mode only.

## Outstanding problems

Open items in `ai/tasks.md`, most severe first: (1) needs-reconnect accounts still get one doomed
attempt; (2) Bluesky / Discord connectable without keys; (3) server checklist; then minor items
(incl. the editor's inline marker for over-limit paragraphs). Next suggested sprint: item 1 or 2.
