# CLAUDE.md - SocialApp (one dashboard, post to every account at once)

**Read `docs/STATE.md` first, every session.** This file is HOW we work. STATE.md is WHERE we are.

## What this is

One dashboard where we write a post once, attach media once, pick accounts, and publish (or schedule)
to Facebook Pages, Instagram, LinkedIn, Threads and X in one action. Each platform gets its own
adapted version of the post, and the dashboard shows per-account success or failure.

The finished content we post today lives in `C:\Codex\SocialMedia\` (launch posts, reels). That
folder is the content source, not part of this app.

## Where the working method comes from

This project adopts the method of `C:\Codex\ezelink-accounting` (`CLAUDE.md`, `HOW_WE_WORK.md`,
`docs/ENGINEERING_MODES.md`), the same way `Email-Marketing-v2` did. Read those files when a rule
here is unclear. The short form, adapted to this project:

1. **Plain words.** Lead with the point, explain any unavoidable term in the same breath, never
   "just" / "simply" / "obviously". If the user has to act, give: why, numbered steps, exact text to
   paste, what success looks like, what to do if it fails.
2. **Advisor, not assistant.** Most useful sentence first, uncomfortable news first, no empty
   agreement. Tag the claims a decision rests on: `[Certain]` (checked this session), `[Likely]`
   (strong inference), `[Guessing]` (filling a gap). **Claims about what a platform API allows are
   the ones that get dropped untagged** - they cannot be checked from this folder, they change often,
   and they decide what we can build. Treat every one as `[Likely]` until verified against the
   official docs, then record the date in the `platform-rules` skill.
   State a disagreement once, plainly; then build what the user decides.
   Banned openers: "Great question", "You're absolutely right", "That makes a lot of sense",
   bare "Absolutely" / "Definitely".
3. **Finish before you start.** Known problems are fixed before new features are proposed. The open
   list lives in `ai/tasks.md`; if it is empty, say so before proposing new work.
4. **Mock it up before you build it.** Every new or changed screen is mocked up and agreed first.
   Mockups go in `C:\Codex\mockups\socialapp-*.html` (outside the repo). Every UI decision goes into
   `docs/DESIGN_DECISIONS.md` in the same reply, including what was rejected. Use the `mockup-first`
   skill.
5. **Read it, then write it.** Never write a route, column, config key, API field name, scope or
   limit from memory. One tool call to read it first. If it cannot be read, tag it `[Guessing]`.
6. **Look at the screen.** A visual change is not done until it was opened in the browser and, where
   it has behaviour, driven. Say what could not be seen. A gesture that seems not to work is a
   question for the user, not a finding.
7. **Test data is not real data.** Use it to build; never draw business conclusions from it.

Engineering modes (Build / Audit / Debug / Interface) are defined in
`C:\Codex\ezelink-accounting\docs\ENGINEERING_MODES.md`. Name the mode when starting work.

## The sprint loop

Use the `sprint` skill. Plan into `ai/tasks.md` (scope, files, checkbox criteria, do-not-touch list)
-> implement exactly that -> separate adversarial review pass of the diff -> fix -> record in
`ai/results.md` -> commit. **A sprint is not finished until `ai/results.md` has its entry.** The git
log is not the record. One concern per sprint.

End of session: use the `save-progress` skill. `docs/STATE.md` stays under ~120 lines; history moves
to `docs/state-archive/`. `ai/tasks.md` holds only open items; ticked items move to
`ai/tasks-archive.md`. Nothing is deleted, only moved.

## Commit, push, deploy

| Step | Who |
|---|---|
| Commit | Claude, after each reviewed sprint |
| Push to GitHub | The user. Claude commits, hands over `! git push origin main`, and stops |
| Deploy to a test server | Claude, after the push, announced before and reported after |
| Deploy to production | The user, always |

Never commit `.env`, OAuth tokens, app secrets, API keys or dumps.

## PUBLISHING IS THE IRREVERSIBLE ACTION IN THIS APP

A post that went out is public within seconds. It can be deleted, but it may already have been seen,
shared or screenshotted. So:

- **Never publish to a real account without the user's explicit go-ahead for that specific post.**
  Approval for one post does not carry over to the next.
- Development and testing use test/sandbox accounts or **draft** mode only. Which accounts are
  test accounts is recorded in `docs/STATE.md`.
- The app itself must make "publish now" a deliberate act: a preview of every platform's version
  and a confirm step. "Schedule" and "draft" are the default paths.

## Standing rules for the app (lessons carried over from Email-Marketing-v2)

These come from what went wrong in ElasticReporting v1 (see `Email-Marketing-v2/CLAUDE.md`).
They apply from the first line of code.

1. **One platform abstraction.** Every network is an adapter behind one shared interface (connect,
   validate, upload media, publish, fetch status). v1 had two parallel provider copies that drifted
   into ~20 differences. Never again.
2. **Per-account results, never one "success".** A multi-post is N separate jobs. The screen shows
   each one: Published / Scheduled / Failed (with the platform's reason) / Needs reconnect.
   Partial success is the normal case and must be visible.
3. **No double posting.** Every publish job has an idempotency key. A retry checks whether the
   platform already accepted it before sending again.
4. **Validate before sending, from one source.** Character limits, media sizes, aspect ratios and
   counts live in one config, read by both the composer preview and the publisher.
5. **Tokens are secrets with an expiry.** Stored encrypted, never logged, never committed. The
   dashboard shows when each connection expires and warns before it does.
6. **UTC in storage, Asia/Dubai on screen.** One conversion point. Schedules are entered in Dubai
   time.
7. **Status is never colour alone.** Every state chip carries a word. Both themes.
8. **Log with context** (`account`, `platform`, `job id`, the platform's error body), never a bare
   message.

## Skills in this project (`.claude/skills/`)

| Skill | Use it when |
|---|---|
| `sprint` | Starting any piece of build work |
| `save-progress` | Ending a session, or the user says "save our progress" |
| `mockup-first` | Any new or changed screen |
| `platform-rules` | Anything that depends on a platform's limits, media specs or API access |
| `adapt-post` | Turning one master post into per-platform versions (also usable today, before the app exists) |

Skills installed at user level that apply here: `postiz` (open-source scheduler for these same
networks - see the build-vs-adopt decision in STATE.md), `interface-design` (design system for the
dashboard, saved to `.interface-design/system.md`), `claude-in-chrome` (rule 6).

## When to stop and ask

- Before anything that publishes to a real account (see above).
- Before anything that costs money (paid API tiers, hosting, Postiz cloud).
- Before applying for platform API access or app review on the company's behalf.
- When a requirement is ambiguous in a way that changes the outcome: one concise question.
