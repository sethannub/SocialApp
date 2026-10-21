<?php

declare(strict_types=1);

return [
    'self_hosted' => env('SELF_HOSTED', false),

    // SocialApp change: only this email may register as the first user (the owner).
    // Empty = upstream behaviour (whoever registers first).
    'owner_email' => env('INSTANCE_OWNER_EMAIL', ''),

    'community' => [
        'repo' => env('SHOUTRRR_GITHUB_REPO', 'coollabsio/shoutrrr'),
        'sponsor_url' => env('SHOUTRRR_SPONSOR_URL', 'https://github.com/sponsors/coollabsio'),
    ],

    'defaults' => [
        'registrations_enabled' => env('INSTANCE_REGISTRATIONS_ENABLED', false),
        'workspace_creation_enabled' => env(
            'INSTANCE_WORKSPACE_CREATION_ENABLED',
            env('WORKSPACES_CAN_CREATE_WORKSPACE', true),
        ),
        'usage_tracking_enabled' => env('INSTANCE_USAGE_TRACKING_ENABLED', false),
        'quote_tweets_enabled' => env('INSTANCE_QUOTE_TWEETS_ENABLED', false),
        'linkedin_community_management_enabled' => env('INSTANCE_LINKEDIN_COMMUNITY_MANAGEMENT_ENABLED', false),
        // SocialApp change: platforms that start switched OFF under Instance settings -> Platforms
        // until the owner saves a choice there. We use Facebook, Instagram, LinkedIn and Threads
        // (user, 2026-10-21: X skipped - its API is paid per post). Bluesky and Discord also connect
        // without app keys.
        'platforms_disabled' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('INSTANCE_PLATFORMS_DISABLED', 'bluesky,discord,x')),
        ))),
        'polling' => [
            'engagement' => [
                'x' => 360,
                'bluesky' => 15,
                'linkedin' => 15,
            ],
            'post_metrics' => [
                'x' => 360,
                'bluesky' => 15,
                'linkedin' => 15,
            ],
            'account_metrics' => [
                'x' => 1440,
                'bluesky' => 1440,
                'linkedin' => 1440,
            ],
        ],
    ],
];
