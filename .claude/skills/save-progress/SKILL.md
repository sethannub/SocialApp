---
name: save-progress
description: Write down where SocialApp work stopped so the next session starts clean - update docs/STATE.md (under 120 lines), archive history, tidy ai/tasks.md. Use at the end of a session or when the user says "save our progress".
---

# Save progress

1. **`docs/STATE.md`** - rewrite "Where we are", "Open decisions", "Outstanding problems" and
   "Test accounts" so they are true today. Update the date.
   - Cap: ~120 lines. If it would go over, move older material **whole** into
     `docs/state-archive/STATE-<yyyy-mm-dd>.md`. Never delete.
2. **`ai/tasks.md`** - only open items stay. Ticked items move to `ai/tasks-archive.md` (append).
   Anything found but not yet recorded goes in now, with what it breaks.
3. **`ai/results.md`** - every sprint finished this session has an entry. If one is missing, write
   it now; the sprint is not finished without it.
4. **`docs/DESIGN_DECISIONS.md`** - every UI decision made this session is recorded, including the
   rejected options.
5. **`platform-rules` skill** - any platform fact verified this session has its "verified on" date
   filled in.
6. Tell the user in three lines or fewer: where we stopped, what is next, and anything waiting on
   them.
