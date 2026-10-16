# Tasks archive - finished items, moved here from `ai/tasks.md`

---

## Sprint 1 - Bring in Shoutrrr and run it on this PC, drafts only   [DONE 2026-10-16]
Mode: Build
Why: get the open-source base (Shoutrrr v1.5.4) into our repo and see it working locally, with no
way for it to publish anything, before we change a line of it.
Acceptance criteria:
- [x] `app/` contains Shoutrrr v1.5.4 with its LICENSE intact; history kept via git subtree
- [x] `app/.env` exists locally, is git-ignored, and has no platform credentials
- [x] Docker Desktop started; Shoutrrr's development compose file builds and starts
- [x] Shoutrrr's own test suite runs inside the container; the pass/fail count is recorded as-is
- [x] App opens in the browser; a local user is registered; a draft post is written, saved and
      reopened; confirmed that no social account is connected
- [x] Screen opened in the browser and driven (Claude in Chrome), screenshots taken
- [x] `docs/STATE.md` and `docs/UPSTREAM.md` say how to start, stop and update it
