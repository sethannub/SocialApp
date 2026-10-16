# STATE - SocialApp

Last updated: 2026-10-15. Keep under ~120 lines. History goes to `docs/state-archive/`.

## Where we are

- Shoutrrr v1.5.4 is in `app/` (git subtree, see `docs/UPSTREAM.md`), unchanged.
- Runs locally in Docker at **http://localhost:8095** (this PC only). No social account connected,
  no platform keys in `.env` - it cannot publish. Local test login is in `app/.env` (git-ignored).

## Run it locally

- Start Docker Desktop, then: `docker compose -f app/docker-compose.development.yaml --project-directory app up -d`
  (add `--build` after changing code). Stop: same with `down`. Data lives in Docker volumes
  `shoutrrr_sqlite` / `shoutrrr_storage`; `down -v` wipes them.
- Port 8080 is taken by the Odysseus stack on this PC, so `.env` sets `APP_PORT=127.0.0.1:8095`
  (the `127.0.0.1:` part keeps it off the office network).
- Tests: `.\scripts\test.ps1` (throwaway container, ~1 min after the first run). Baseline
  2026-10-16: **2030 passed**, 0 failed.

## Open decisions (these come before any code)

1. **DECIDED 2026-10-15 (user): our own build.** We still need our own developer apps and
   approvals from Meta, LinkedIn and X - no open-source base removes that.
2. **Which open-source base to fork? (awaiting user)** GitHub survey 2026-10-15:
   - **Shoutrrr** (coollabsio/shoutrrr) - RECOMMENDED. Apache-2.0, Laravel 13 + Inertia + React +
     Tailwind (same stack as Y5LeadsCRM), exactly FB Pages / IG / LinkedIn / Threads / X (+ Bluesky,
     Discord). Already has one connector interface, per-account publish jobs, a pre-publish check,
     encrypted auto-refreshed tokens, workspaces. ~450 test files, weekly releases (v1.5.4).
     Gaps: no approval step; young (June 2026); needs PHP 8.5 (this PC has 8.4).
   - TryPost (trypostit/trypost) - AGPL-3.0, Laravel + Vue, has approvals. Licence forces publishing
     our changes if we ever offer it to outside users. Mostly one developer.
   - Postiz - AGPL-3.0, TypeScript monorepo (Next + Nest), biggest (37k stars), stack foreign to us.
   - BrightBean Studio - AGPL-3.0, Python/Django. Mixpost Lite - MIT but its free version
     covers only Facebook, X and Mastodon (the rest is paid Pro). Cogsend - MIT, Cloudflare
     Workers, no Facebook or Instagram.
3. **Stack** follows decision 2 (Shoutrrr = Laravel + React).
4. **DECIDED 2026-10-16 (user): Y5Media only.** One brand. No multi-brand / workspace work needed.
5. **DECIDED 2026-10-16 (user): no approval step.** One user (the owner) writes and publishes.
   Shoutrrr's missing approval step is therefore not a gap. The confirm-before-publish screen
   (CLAUDE.md) still applies - that is a safety step, not an approval workflow.
6. **DONE 2026-10-16: git + private GitHub repo** `github.com/sethannub/SocialApp`, branch `main`.
   Push from the `!` prompt runs in Git Bash: use forward slashes in paths
   (`! git -C C:/Codex/SocialApp push`).
7. **Test accounts.** Which accounts can be used for testing without anyone seeing the posts.
   (Still open - the Y5Media accounts are real, so drafts only until this is answered.)

## Hosting candidate: EZEOS (read-only check 2026-10-16)

- `ssh ezeos-live`. Also runs the live CRM (leads.y5media.net), reports, marketing-v2, portal.
- Room: 8 cores, 15 GB RAM (~14 GB available), 228 GB disk free. Load avg 3-4; postgres ~60% CPU
  at the time of the check (one snapshot, cause not checked).
- PHP 8.4.21 on the host - Shoutrrr needs 8.5, so run it in Docker, not on host PHP.
- Docker 26.1.3 + Compose 2.27 installed but the daemon is **stopped**, 0 containers. Starting it
  is a change to a live server - needs the user's go-ahead.
- Public access is via Cloudflare Tunnel (`cloudflared-leads.service` for leads.y5media.net).
  A `social.y5media.net` would need a tunnel route + Cloudflare DNS access for y5media.net.
- Available locally: PostgreSQL 15, MariaDB 10.11, Redis, nginx 1.24, supervisord.

## Test accounts (safe to post to during development)

None recorded yet. Until there are some, development uses draft mode only.

## Outstanding problems

Found in Sprint 1, listed in `ai/tasks.md` - fix these before new features.
