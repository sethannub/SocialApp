---
name: platform-rules
description: Per-platform limits, media specs and API access requirements for Facebook Pages, Instagram, LinkedIn, Threads and X. Read before writing any validation, adapter or composer code, or before telling the user what a platform allows. Every value is unverified until it carries a date.
---

# Platform rules

**Status of this file: written from memory on 2026-10-15. Every value below is `[Likely]` until the
"Verified" column has a date.** Platforms change these often. Before code depends on a value:

1. Check it against the official developer docs (links below) with WebSearch / WebFetch.
2. Put the date and the source in the Verified column.
3. Copy the value into the app's single limits config. Code never hard-codes a limit.

If a value cannot be verified, say so to the user and tag it `[Guessing]`.

## Text and media limits

| Platform | Text limit | Media per post | Notes | Verified |
|---|---|---|---|---|
| X | 280 chars (non-Premium) | up to 4 images, or 1 video, or 1 GIF | every URL counts as 23 chars; Premium allows longer posts | - |
| Instagram (feed) | 2,200 chars caption | carousel up to 10 items via API | max 30 hashtags; links in captions are not clickable; images JPEG via API; aspect 4:5 to 1.91:1 | - |
| Instagram (Reels) | 2,200 chars | 1 video | 9:16 | - |
| Facebook Page | very long (~63,000 chars) | multiple images, or 1 video | link posts generate a preview card | - |
| LinkedIn | 3,000 chars | multiple images, or 1 video, or 1 document | first ~210 chars show before "see more" | - |
| Threads | 500 chars | carousel up to 10-20 items | one link preview per post | - |

## API access - the slow part of the project

| Platform | What we need | Notes | Verified |
|---|---|---|---|
| Facebook Page | Meta developer app; `pages_manage_posts` etc.; App Review | Page must be managed by the account that connects. Publishing = POST `/{page-id}/feed` with `pages_manage_posts`. In Development mode the app has all permissions but only for people with a role on the app, and can only manage assets (Pages) owned by its own business. **Open:** whether Dev-mode posts to your own Page are visible to the public - not stated in the docs, test it | 2026-10-19 (developers.facebook.com/docs/permissions, /docs/pages-api, blog 2019-09-23 "Live mode for production use") - partly |
| Instagram | Same Meta app; Instagram **Business or Creator** account linked to a Facebook Page; content-publishing permission; App Review | **Media must be on a publicly reachable server at the moment of publishing - Meta fetches `image_url` / `video_url` itself, so it cannot work from localhost.** Two steps: POST `/<IG_ID>/media` (container), then `/<IG_ID>/media_publish`. Cap: 100 API-published posts per account per 24h (moving window) | 2026-10-19 (developers.facebook.com/docs/instagram-platform/content-publishing) |
| Threads | Threads API via the Meta app; App Review | daily cap on API-published posts (was 250 / 24h) | - |
| LinkedIn | LinkedIn developer app. Personal profile: "Share on LinkedIn". **Company Page: Community Management API (`w_organization_social`), which needs an approval application** | tokens expire (~60 days). Company Page posting: apply for Community Management API on the app's Products tab; starts at **Development tier** (500 calls / app / 24h, 100 / member; meant for build + test within 12 months); **Standard tier** needs a screencast of the OAuth flow + posting, test credentials, privacy policy. Only registered legal organisations, commercial use; a **super admin of the LinkedIn Page must verify** the application; business email (personal emails fail vetting); no LinkedIn/Microsoft names or logos in the app. Posting member needs a Page role (ADMINISTRATOR etc.; role list differs between doc pages) | 2026-10-19 (learn.microsoft.com/linkedin/marketing: community-management-overview, increasing-access, community-management-app-review, posts-api) |
| X | X developer account; posting access is a **paid** tier or pay-per-use, price changes often | check current pricing before promising anything | - |

Long-lived tokens expire on Meta (~60 days) and LinkedIn (~60 days). The app must show expiry and
warn before it.

## Official sources

- Meta (Facebook / Instagram / Threads): https://developers.facebook.com/docs/
- LinkedIn: https://learn.microsoft.com/en-us/linkedin/
- X: https://developer.x.com/en/docs
- Postiz provider settings (if we adopt Postiz): `postiz integrations:settings <id>` returns the
  current rules per connected account. Prefer that over this table when it is available.
