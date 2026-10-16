---
name: mockup-first
description: Mock up any new or changed SocialApp screen before building it, get it agreed, and record the decision. Use for the composer, account list, calendar, post history or any other screen.
---

# Mockup first

Rule: no screen is built until a mockup was seen and agreed. A rejected build costs far more than
a rejected drawing.

1. Read `docs/DESIGN_DECISIONS.md`. If the request contradicts an entry, say so first.
2. If `.interface-design/system.md` exists, follow it. If not, and this is the first screen, run the
   `interface-design` skill to set the system up (tokens, spacing, type, both themes).
3. Build the mockup as one HTML file in **`C:\Codex\mockups\socialapp-<screen>.html`** - outside the
   project. Never inside `C:\Codex\SocialApp`.
   - Where there is real doubt, make two or three variants and recommend one.
   - Use realistic content: real post lengths, a long brand name, a failed platform, an expired
     connection, zero scheduled posts. The empty and error states are part of the mockup.
4. Show it: `cd C:\Codex && python -m http.server 8099 --bind 127.0.0.1`, open
   `http://127.0.0.1:8099/mockups/socialapp-<screen>.html` in Chrome, screenshot it for the user.
   Stop the server afterwards.
5. When the user decides, write the entry in `docs/DESIGN_DECISIONS.md` in the same reply:

```
## <date> - <screen>: <decision in one line>
Mockup: C:\Codex\mockups\socialapp-<screen>.html (variant B)
Chosen: ...
Rejected: variant A because ..., variant C because ...
```

Mockups are not under version control, so the decision entry is the only durable record. Write it
properly.

## What the core screens must always show

- **Composer:** one master text, per-platform tabs with live character counts against each limit,
  media checks per platform, a preview of each version, Schedule / Save draft / Publish now (the
  last one behind a confirm that lists every account it will post to).
- **Results:** per account - Published (with link) / Scheduled / Failed (reason) / Needs reconnect.
- **Accounts:** each connection, its brand group, and when its token expires.
