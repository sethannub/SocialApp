# Design decisions - SocialApp

A decision that is not written down did not happen. Each entry: date, what was decided, what was
rejected and why, and the mockup it came from (mockups live in `C:\Codex\mockups\socialapp-*.html`).

Read this before building any screen. If a build would contradict an entry, stop and say so.

---

## 2026-10-16 - App name: "Y5Media Social"
Decided by the user. Replaces "Shoutrrr" everywhere it shows (tab, menu, login, emails, icons).
Rejected: "SocialApp" (project working name, stays for folders and docs), "Y5Media" alone.
Why rebrand at all: the Shoutrrr name and logo were confusing the user; Apache-2.0 grants no
right to use Shoutrrr's name/logo as ours anyway. LICENSE file stays in `app/`.
Branding mockup: C:\Codex\mockups\socialapp-branding.html,
https://claude.ai/artifact/HzQGeog8wXPSfLcyp6XFh8 (version 2)

## 2026-10-16 - Branding: Y5Media palette, forest-green menu (variant A)
Mockup: C:\Codex\mockups\socialapp-branding.html (variant A), artifact version 2.
Chosen: Y5Media palette from `Y5MediaSite/src/styles/tokens.css` replaces Shoutrrr's greys/lime:
lime->green gradient (#A6E72A -> #75B630, dark text) on main buttons, paper #F4F7F3 page,
green-tinted greys, ink #14211A text, green ink #2F6B00 for links/active; menu in forest #0B261B
with lime active marker; dark theme in deep forest greens. Y5 mark replaces the megaphone; phone /
tab icons = Y5 mark in lime on forest (user said "A"; icon colour inferred to match - confirm if
gradient was wanted). "Star on GitHub", "Sponsor", release-notes link removed.
Status chips keep words + red/amber/green (CLAUDE.md rule 7).
Rejected: variant B (white menu) - closer to today, weaker Y5Media identity.

## Pending - Composer "Publish now"
Mockup: C:\Codex\mockups\socialapp-composer-publish.html,
https://claude.ai/artifact/8m24A4VtfJhA7pExNeav71 - variants A (recommended), B, C.
Waiting on: variant choice; skip-vs-block when some accounts have problems.
