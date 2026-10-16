# STATE - SocialApp

Last updated: 2026-10-15. Keep under ~120 lines. History goes to `docs/state-archive/`.

## Where we are

- Project folder created. Working method, skills and record files set up. **No code yet.**
- Not a git repository yet (decision 5 below).

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

## Test accounts (safe to post to during development)

None recorded yet. Until there are some, development uses draft mode only.

## Outstanding problems

None (no code yet).
