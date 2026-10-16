---
name: sprint
description: Run one scoped piece of build work on SocialApp through the full loop - plan into ai/tasks.md, implement, adversarial review, fix, record in ai/results.md, commit. Use whenever starting code work, or when the user says "go", "next step", "build X".
---

# Sprint loop

Before starting: read `docs/STATE.md` and `ai/tasks.md`. If the outstanding-problems list is not
empty, the sprint fixes one of those, not a new feature (CLAUDE.md rule 3). Name the engineering
mode (Build / Audit / Debug / Interface).

## 1. Plan - write it into `ai/tasks.md` before any code

```
## <sprint name>   [OPEN]
Mode: <mode>
Why: <one line, plain words>
Files in scope: <exact paths>
Do NOT touch: <paths / areas>
Acceptance criteria:
- [ ] ...
- [ ] Screen opened in the browser and driven (if it has a screen)
```

If the sprint has a new or changed screen and no agreed mockup, stop and run `mockup-first`.
If it depends on a platform limit or API behaviour, read the `platform-rules` skill first.

Tell the user the plan in two or three plain sentences. Wait for "go" unless they already said it.

## 2. Implement

Exactly what the task says. Complete code, no placeholders, no TODOs. Read every name before
writing it (route, column, config key, API field, scope).

## 3. Adversarial review - a separate pass with the opposite intent

Re-read the whole diff, cold, trying to break it. Do not spawn a subagent unless the user asks.

- Could this publish to a real account by accident? Could a retry post twice?
- Partial failure: 3 of 5 platforms succeed. Is each result recorded and shown?
- Expired or revoked token, platform rate limit, platform 5xx, timeout mid-upload.
- Limits: empty post, exactly-at-limit text, emoji and URLs in the count, too many images,
  wrong aspect ratio, video too long.
- Time: UTC stored, Dubai shown, schedule in the past, daylight-saving-free but still one conversion.
- Security: secrets in logs or responses, missing auth/CSRF, unescaped user text, SSRF on media URLs.
- Was every acceptance criterion actually met, or only claimed?
- Every name in the diff: read from source, or remembered?

Rank findings by severity.

## 4. Fix what the review found.

## 5. Look at it

If there is a screen: open it with Claude in Chrome, use the controls, screenshot. Write down what
could not be observed.

## 6. Record - THE STEP THAT GETS SKIPPED

Add the entry to `ai/results.md` (shape is at the top of that file). Move the task in
`ai/tasks.md` to `ai/tasks-archive.md`. Anything new that was found goes into `ai/tasks.md` as open.

## 7. Commit

Descriptive message: what changed and why. Then hand the user `! git push origin main` and stop.
