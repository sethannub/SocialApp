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

## 2026-10-16 - Composer: "Publish now" lives in a menu (variant B); user chooses on problems
Mockup: C:\Codex\mockups\socialapp-composer-publish.html (variant B tray + A2/A3 confirm
window), https://claude.ai/artifact/8m24A4VtfJhA7pExNeav71
Chosen (user): variant B. Mode switch shows only Queue / Pick time; opens in Pick time; green
button is Schedule. "Publish now…" sits in a small menu next to Schedule and always opens the
confirm window (every account, its version, "Goes live now" in Dubai time, focus on Cancel).
Ctrl+Enter never publishes.
Problems (expired connection, text too long, ...): the window lists them with the reason and
the user decides - "Publish to N ready accounts" or Cancel. No automatic block (user's words:
"let me choose whether to publish or not if there is an error").
Change from the mockup: skipped accounts are not kept as a separate draft; they show on the
post as Failed with the reason (Shoutrrr's existing per-account result) and can be retried
after the fix.
Rejected: variant A (Now kept in the mode switch) - user preferred it out of the way;
variant C (type PUBLISH) - friction without new information; automatic block - user's choice.
