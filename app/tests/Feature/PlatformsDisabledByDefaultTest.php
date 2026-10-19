<?php

/**
 * SocialApp change: Bluesky and Discord start switched off (instance.defaults.platforms_disabled)
 * and their connect endpoints refuse while off. phpunit.xml empties the list for upstream's
 * tests; these set it explicitly.
 */

use App\Enums\Platform;
use App\Enums\WorkspaceRole;
use App\Models\ConnectedAccount;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMembership;
use App\Support\InstanceSettings;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config(['instance.defaults.platforms_disabled' => ['bluesky', 'discord']]);
});

function platformsOwner(): void
{
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['owner_id' => $user->id]);
    WorkspaceMembership::factory()->create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
        'role' => WorkspaceRole::Owner,
    ]);
    $user->forceFill(['current_workspace_id' => $workspace->id])->save();
    test()->actingAs($user);
}

test('a fresh install starts with Bluesky and Discord off and our five platforms on', function () {
    $enabled = app(InstanceSettings::class)->platformsEnabled();

    expect($enabled['bluesky'])->toBeFalse()
        ->and($enabled['discord'])->toBeFalse()
        ->and($enabled['facebook'])->toBeTrue()
        ->and($enabled['instagram'])->toBeTrue()
        ->and($enabled['linkedin'])->toBeTrue()
        ->and($enabled['threads'])->toBeTrue()
        ->and($enabled['x'])->toBeTrue();
});

test('a choice saved on the Platforms page wins over the default', function () {
    $settings = app(InstanceSettings::class);
    $settings->update(['platforms_enabled' => array_fill_keys(
        array_map(fn (Platform $p): string => $p->value, Platform::cases()),
        true,
    )]);

    expect($settings->platformAvailable(Platform::Bluesky))->toBeTrue()
        ->and($settings->platformAvailable(Platform::Discord))->toBeTrue();
});

test('connecting Bluesky with an app password is refused while it is off', function () {
    platformsOwner();
    Http::fake();

    $this->post('/accounts/connect/bluesky', [
        'identifier' => 'ada.bsky.social',
        'app_password' => 'app-pass-1234',
    ])->assertForbidden();

    Http::assertNothingSent();
    expect(ConnectedAccount::withoutGlobalScopes()->count())->toBe(0);
});

test('starting a Bluesky OAuth connection is refused while it is off', function () {
    platformsOwner();
    Http::fake();

    $this->get('/accounts/connect/bluesky/oauth')->assertNotFound();

    Http::assertNothingSent();
});

test('connecting a Discord webhook is refused while it is off', function () {
    platformsOwner();
    Http::fake();

    $this->post('/accounts/connect/discord', [
        'webhook_url' => 'https://discord.com/api/webhooks/999/tok-123',
    ])->assertForbidden();

    Http::assertNothingSent();
    expect(ConnectedAccount::withoutGlobalScopes()->count())->toBe(0);
});
