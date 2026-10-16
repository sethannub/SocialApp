---
name: adapt-post
description: Turn one master post (text plus media) into a version for each of Facebook, Instagram, LinkedIn, Threads and X, checked against each platform's limits and our brand wording rules. Use when the user gives a post to send everywhere, or when testing the composer logic. Works today, before the app exists.
---

# Adapt one post for every platform

## Inputs

- Master text, the brand (Y5Media / Y5Leads / Y5Ads / other), the target platforms, and media files
  (usually from `C:\Codex\SocialMedia\<brand>\<campaign>\`).

## Steps

1. **Read the brand's wording rules from the source, not memory.** For Y5Media, Y5Leads and Y5Ads
   that is `C:\Codex\Y5MediaSite\CLAUDE.md` ("Decisions" section): product names, prices, approved
   claims, and the rule never to say leads come from "our database". If a claim in the master text
   is not backed there, flag it.
2. **Read the `platform-rules` skill** for limits. Note which values are still unverified.
3. Write one version per platform:
   - **X:** the hook only, under the limit, with URLs counted at their fixed length. A thread
     only if the user asks.
   - **Instagram:** the full message, with the link moved to "link in bio" or the CTA (caption links
     are not clickable). Hashtags at the end, at most a handful of relevant ones.
   - **Facebook:** the full message with the link inline.
   - **LinkedIn:** a professional framing; the first two lines must work on their own before
     "see more". Fewer hashtags.
   - **Threads:** a short, conversational version under the limit.
4. **Check media per platform:** count, aspect ratio and type (e.g. 1080x1080 works for the feed on all
   of them; a 1080x1920 reel suits Instagram Reels and Threads, while LinkedIn suits landscape
   video, as in `SocialMedia/Y5Leads/2026-10-launch-reel-v2`). Say which file goes where and flag
   any mismatch.
5. Present a table: platform | text | characters used / limit | media | warnings.

## Never

- Never publish. This skill prepares versions. Publishing needs the user's explicit go-ahead for
  that post (CLAUDE.md, "Publishing is the irreversible action").
- Never invent statistics, prices or claims that are not in the brand source.
