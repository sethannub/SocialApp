<?php

/**
 * SocialApp change: times are shown and entered in the display timezone (Asia/Dubai) when the
 * workspace hasn't picked one, and are always stored in UTC (CLAUDE.md standing rule 6).
 * phpunit.xml pins APP_DISPLAY_TIMEZONE=UTC for upstream's tests; these set Dubai explicitly.
 */

use App\Enums\PostStatus;
use App\Enums\WorkspaceRole;
use App\Models\Post;
use App\Models\PostingSchedule;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMembership;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    config(['app.display_timezone' => 'Asia/Dubai']);
});

function memberOfNewWorkspace(): array
{
    $workspace = Workspace::factory()->create();
    $user = User::factory()->create(['current_workspace_id' => $workspace->id]);
    WorkspaceMembership::factory()->create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
        'role' => WorkspaceRole::Owner,
    ]);
    test()->actingAs($user);

    return [$workspace, $user];
}

test('a workspace without a posting schedule is shown in the display timezone', function () {
    memberOfNewWorkspace();

    $this->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page->where('workspaces.current.timezone', 'Asia/Dubai'));
});

test('a workspace that picked its own timezone keeps it', function () {
    [$workspace] = memberOfNewWorkspace();
    PostingSchedule::query()->create(['workspace_id' => $workspace->id, 'timezone' => 'Europe/London']);

    $this->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page->where('workspaces.current.timezone', 'Europe/London'));
});

test('a new posting schedule starts in the display timezone', function () {
    [$workspace] = memberOfNewWorkspace();

    $this->put(route('queue.update'), ['slots' => []]);

    expect(PostingSchedule::query()->where('workspace_id', $workspace->id)->value('timezone'))
        ->toBe('Asia/Dubai');
});

test('a time entered in Dubai time is stored in UTC', function (string $sent) {
    [$workspace, $user] = memberOfNewWorkspace();
    $post = Post::factory()->create([
        'workspace_id' => $workspace->id,
        'author_id' => $user->id,
        'status' => PostStatus::Draft,
    ]);

    Carbon::setTestNow('2026-10-16 08:00:00');
    $this->putJson(route('posts.schedule', $post), ['scheduled_at' => $sent])->assertOk();

    $stored = (string) DB::table('posts')->where('id', $post->id)->value('scheduled_at');
    expect($stored)->toStartWith('2026-10-20 10:30:00');
})->with([
    'with a Dubai offset' => '2026-10-20T14:30:00+04:00',
    'already in UTC' => '2026-10-20T10:30:00Z',
]);
