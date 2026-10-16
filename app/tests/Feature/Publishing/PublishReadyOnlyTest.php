<?php

/**
 * SocialApp change: "Publish now" confirm window. When some accounts can't take the post, the
 * user may choose to publish to the ready ones (publish_ready_only). Without that explicit
 * choice the whole post is still refused.
 */

use App\Enums\Platform;
use App\Enums\PostStatus;
use App\Enums\PostTargetStatus;
use App\Enums\WorkspaceRole;
use App\Jobs\PublishPostTarget;
use App\Models\ConnectedAccount;
use App\Models\Post;
use App\Models\PostTarget;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMembership;
use Illuminate\Support\Facades\Bus;

/**
 * @return array{post: Post, ready: PostTarget, blocked: PostTarget}
 */
function postWithOneReadyAndOneBlockedTarget(): array
{
    $workspace = Workspace::factory()->create();
    $user = User::factory()->create(['current_workspace_id' => $workspace->id]);
    WorkspaceMembership::factory()->create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
        'role' => WorkspaceRole::Member,
    ]);
    test()->actingAs($user);

    $post = Post::factory()->create([
        'workspace_id' => $workspace->id,
        'author_id' => $user->id,
        'status' => PostStatus::Draft,
    ]);

    $ready = PostTarget::factory()->for($post)->create([
        'connected_account_id' => ConnectedAccount::factory()->create([
            'workspace_id' => $workspace->id,
            'platform' => Platform::Bluesky->value,
        ])->id,
        'platform' => Platform::Bluesky->value,
        'sections' => ['Short enough for every platform.'],
        'auto_split' => false,
    ]);

    $blocked = PostTarget::factory()->for($post)->create([
        'connected_account_id' => ConnectedAccount::factory()->create([
            'workspace_id' => $workspace->id,
            'platform' => Platform::Bluesky->value,
        ])->id,
        'platform' => Platform::Bluesky->value,
        'sections' => [str_repeat('x', 400)],
        'auto_split' => false,
    ]);

    return ['post' => $post, 'ready' => $ready, 'blocked' => $blocked];
}

test('without publish_ready_only a post with a blocked account is refused as a whole', function () {
    Bus::fake();
    ['post' => $post] = postWithOneReadyAndOneBlockedTarget();

    $this->postJson("/posts/{$post->id}/publish")
        ->assertUnprocessable()
        ->assertJsonPath('message', "Some accounts can't be published yet.")
        ->assertJsonCount(1, 'blocked');

    Bus::assertNotDispatched(PublishPostTarget::class);
    expect($post->fresh()->status)->toBe(PostStatus::Draft);
});

test('publish_ready_only publishes the ready account and records the blocked one as failed', function () {
    Bus::fake();
    ['post' => $post, 'ready' => $ready, 'blocked' => $blocked] = postWithOneReadyAndOneBlockedTarget();

    $this->postJson("/posts/{$post->id}/publish", ['publish_ready_only' => true])->assertOk();

    Bus::assertDispatchedTimes(PublishPostTarget::class, 1);
    expect($blocked->fresh()->status)->toBe(PostTargetStatus::Failed)
        ->and($blocked->fresh()->error_message)->not->toBeEmpty()
        ->and($ready->fresh()->status)->not->toBe(PostTargetStatus::Failed);
});

test('publish_ready_only is still refused when no account is ready', function () {
    Bus::fake();
    ['post' => $post, 'ready' => $ready] = postWithOneReadyAndOneBlockedTarget();
    $ready->forceFill(['sections' => [str_repeat('y', 400)]])->save();

    $this->postJson("/posts/{$post->id}/publish", ['publish_ready_only' => true])
        ->assertUnprocessable()
        ->assertJsonPath('message', 'None of the selected accounts can take this post yet.')
        ->assertJsonCount(2, 'blocked');

    Bus::assertNotDispatched(PublishPostTarget::class);
});

test('publish_ready_only changes nothing when every account is ready', function () {
    Bus::fake();
    ['post' => $post, 'blocked' => $blocked] = postWithOneReadyAndOneBlockedTarget();
    $blocked->forceFill(['sections' => ['Now short too.']])->save();

    $this->postJson("/posts/{$post->id}/publish", ['publish_ready_only' => true])->assertOk();

    Bus::assertDispatchedTimes(PublishPostTarget::class, 2);
});
