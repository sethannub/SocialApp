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
