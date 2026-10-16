# Tasks - open items only

Ticked items move to `ai/tasks-archive.md` the day they are ticked.
Anything found while fixing something else goes here immediately, with what it breaks.

---

## Sprint 1 - Bring in Shoutrrr and run it on this PC, drafts only   [OPEN]
Mode: Build
Why: get the open-source base (Shoutrrr v1.5.4) into our repo and see it working locally, with no
way for it to publish anything, before we change a line of it.
Files in scope:
- `app/` (new) - Shoutrrr v1.5.4 source, brought in with `git subtree` from
  `https://github.com/coollabsio/shoutrrr` tag `v1.5.4`, so later upstream versions can be pulled in
- `app/.env` - local only, git-ignored, created from Shoutrrr's own example file
- `docs/STATE.md` - "how to run it locally" section
- `docs/UPSTREAM.md` (new) - which Shoutrrr version we are on and how to update it
Do NOT touch:
- Any Shoutrrr code (no edits inside `app/` beyond the local `.env`) - this sprint is run-as-is
- EZEOS or any server
- Any real social account. **No platform credentials (`X_*`, `LINKEDIN_*`, `FACEBOOK_*`,
  `THREADS_*`) go into `.env`** - without them no account can be connected, so nothing can publish
Acceptance criteria:
- [ ] `app/` contains Shoutrrr v1.5.4 with its LICENSE intact; history kept via git subtree
- [ ] `app/.env` exists locally, is git-ignored, and has no platform credentials
- [ ] Docker Desktop started; Shoutrrr's development compose file builds and starts
- [ ] Shoutrrr's own test suite runs inside the container; the pass/fail count is recorded as-is
- [ ] App opens in the browser; a local user is registered; a draft post is written, saved and
      reopened; confirmed that no social account is connected
- [ ] Screen opened in the browser and driven (Claude in Chrome), screenshots taken
- [ ] `docs/STATE.md` and `docs/UPSTREAM.md` say how to start, stop and update it
