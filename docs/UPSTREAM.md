# Upstream - Shoutrrr

`app/` is Shoutrrr (https://github.com/coollabsio/shoutrrr, Apache-2.0), brought in with
`git subtree`, so its history is inside our repo and newer versions can be merged in.

| | |
|---|---|
| Current version | **v1.5.4** (commit `2abb809`), added 2026-10-16 |
| Git remote | `upstream` -> `https://github.com/coollabsio/shoutrrr.git` |
| Licence | Apache-2.0 - keep `app/LICENSE`; mark files we change (Apache-2.0 section 4b) |

## Updating to a newer Shoutrrr version

Its own sprint (Audit mode first: read the upstream changelog/diff, list what touches our changes).

```
git fetch upstream tag vX.Y.Z --no-tags
git subtree pull --prefix=app upstream vX.Y.Z -m "Update Shoutrrr to vX.Y.Z"
```

Conflicts land only where we changed upstream files. Then rebuild, run the test suite, update the
version line above.

## Files that came with Shoutrrr and are not ours

`app/CLAUDE.md`, `app/AGENTS.md`, `app/.claude/skills/*`, `app/.mcp.json`, `app/.cursor`,
`app/.codex`, `app/.agents`, `app/.ai` are Shoutrrr's own AI-tool instructions (Laravel Boost
coding conventions: Pest, bun, oxlint, Pint). They apply when editing code inside `app/`. Our
root `CLAUDE.md` wins on how we work (sprints, publishing safety, plain words).

## Our changes to upstream

Each changed upstream file carries a "SocialApp change:" comment at the change.

| File | Change | Why | Sprint |
|---|---|---|---|
| `app/app/Support/InstanceSettings.php` | `registrationsAllowed()` only lets an invitation token bypass closed registration if it is a real, pending, unexpired invitation (new `invitationTokenIsValid()`) | Upstream accepted any non-empty string, so `/register?invitation=anything` created an account | 2 |
| `app/tests/Feature/Auth/RegistrationTest.php` | 5 tests: made-up / expired / accepted tokens refused, valid invite works, register screen redirects | Cover the fix above | 2 |
| `app/resources/css/app.css` | Y5Media palette in `:root` / `.dark` / sidebar tokens; lime->green primary gradient; new `--brand-mark` token | Rebrand | 3 |
| `app/resources/js/components/layout/app-logo-icon.tsx`, `app-logo.tsx` | Y5 mark (filled) replaces the megaphone; "Y5Media Social" | Rebrand | 3 |
| `app/resources/js/layouts/auth/auth-{simple,card,split}-layout.tsx`, `pages/error.tsx`, `pages/mcp/landing.tsx`, `components/onboarding/welcome-modal.tsx` | Logo sizing for the wide mark, `text-brand-mark` colour | Rebrand | 3 |
| `app/resources/js/components/layout/sidebar-footer-card.tsx`, `app-sidebar.tsx` | Removed "Star on GitHub"/"Sponsor" links and the version/release-notes badge | Rebrand | 3 |
| Visible text in `pages/auth/{login,register}.tsx`, `pages/{engagement/index,error,sync,share/show,settings/workspace/api-keys,mcp/landing}.tsx`, `components/settings/create-sync-pipeline-dialog.tsx`, `views/vendor/mail/html/header.blade.php`, `app/Http/Controllers/Settings/NativeTrackingController.php`, `config/{app,scramble}.php`, `resources/js/app.tsx` | "Shoutrrr" -> "Y5Media Social" (whole word only; identifiers untouched) | Rebrand | 3 |
| `app/public/*` icons, `favicon.svg`, `site.webmanifest`, `views/app.blade.php`, `views/oauth/authorize.blade.php` | Regenerated with `scripts/branding/build-icons.ps1`; theme colour #0b261b | Rebrand | 3 |
| `.env.example*` | `APP_NAME="Y5Media Social"` | Rebrand | 3 |
| Tests: `AppIconAssetsTest.php`, `app-sidebar.test.ts`, `sidebar-footer-card.test.ts`, `auth-brand-text.test.ts`, `engagement/index.test.ts` | Follow the rebrand; removed links asserted absent | Rebrand | 3 |

| `app/resources/js/lib/compose/composer-state.ts`, `components/compose/schedule-tray.tsx` | No "Now" mode; open in Pick time | Publish now must be deliberate | 4 |
| `app/resources/js/components/compose/submit-bar.tsx`, `composer.tsx`, new `publish-confirm-dialog.tsx` | Schedule as main button; "Publish now…" menu + confirm window; shared `previewFor` | Publish now must be deliberate | 4 |
| `app/app/Http/Controllers/Posts/PublishController.php` | `publish_ready_only` flag | User chooses to publish to the ready accounts | 4 |
| Tests: `PublishReadyOnlyTest.php`, `publish-confirm-dialog.test.ts`, `composer-state.test.ts`, `submit-bar.test.ts` | New / follow the change | | 4 |

| `app/config/app.php` | New `display_timezone` (env `APP_DISPLAY_TIMEZONE`, default Asia/Dubai) | Dubai on screen, UTC in storage | 5 |
| `app/app/Http/Middleware/HandleInertiaRequests.php`, `Posts/NextSlotController.php`, `Posts/PostingScheduleController.php`, `Settings/WorkspaceSettingsController.php` | `'UTC'` fallbacks -> `config('app.display_timezone')` | Same | 5 |
| `app/app/Models/Post.php` | `scheduledAt()` setter stores any incoming time as UTC | Upstream dropped the offset: 14:30+04:00 saved as 14:30 UTC | 5 |
| `app/phpunit.xml` | `APP_DISPLAY_TIMEZONE=UTC` for upstream tests | Keep upstream tests unchanged | 5 |
| Tests: `DisplayTimezoneTest.php`, `publish-confirm-dialog.test.ts` (`timezoneLabel`) | New | | 5 |

| `app/resources/js/lib/compose/section-split.ts`, `platform-preview.ts` | `chunkSegment()` = exact port of `PostSplitter::chunk`; preview uses it | Preview showed an over-limit paragraph as one post; server splits it | 6 |
| `app/resources/js/components/compose/composer.tsx` | `liveSections()` for counter + tab chip; per-part severity | Counter read page-load targets | 6 |
| `app/tests/Fixtures/post-splitter-overlimit-parity.json` (new), `tests/Unit/Posts/PostSplitterOverlimitParityTest.php` (new), `section-split.test.ts`, `platform-preview.test.ts`, `composer-tabs.test.ts` | Server-generated parity cases; one upstream expectation corrected | | 6 |

| `app/config/instance.php`, `app/Support/InstanceSettings.php` | `platforms_disabled` default (Bluesky, Discord) when no choice is saved | We don't use them; they connect without app keys | 7 |
| `app/Http/Requests/ConnectedAccount/Connect{Bluesky,Discord}Request.php`, `ConnectedAccounts/BlueskyOAuthController.php` | Refuse while the platform is switched off | Upstream only hid the button | 7 |
| `app/phpunit.xml`, `tests/Feature/PlatformsDisabledByDefaultTest.php` (new) | Upstream tests keep "all on"; ours set the list | | 7 |

Merge note: a future `git subtree pull` will conflict in most of the Sprint 3 files. Resolve by
keeping upstream's code and re-applying the name/colour change; `scripts/branding/build-icons.ps1`
regenerates the icons.
