<?php

use App\Models\User;
use App\Models\WorkspaceInvitation;
use App\Support\InstanceSettings;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('registration screen preloads the email for a valid invitation', function () {
    [$plain, $hash] = WorkspaceInvitation::generateToken();
    WorkspaceInvitation::factory()->create([
        'email' => 'invited@example.com',
        'token' => $hash,
    ]);

    $this->get(route('register', ['invitation' => $plain]))
        ->assertInertia(fn (Assert $page) => $page
            ->component('auth/register')
            ->where('invitation', $plain)
            ->where('invitationEmail', 'invited@example.com'));
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('first registered user becomes the instance owner', function () {
    $this->post(route('register.store'), [
        'name' => 'Instance Owner',
        'email' => 'owner@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    expect(auth()->user()->isInstanceOwner())->toBeTrue();
});

test('public registration is disabled by default after the first user registers', function () {
    $this->post(route('register.store'), [
        'name' => 'Instance Owner',
        'email' => 'owner@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    auth()->logout();

    $this->post(route('register.store'), [
        'name' => 'Blocked User',
        'email' => 'blocked@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrors('email');

    expect(User::where('email', 'blocked@example.com')->exists())->toBeFalse();
});

test('registration can be disabled after the instance owner exists', function () {
    User::factory()->instanceOwner()->create();

    app(InstanceSettings::class)->update([
        'registrations_enabled' => false,
    ]);

    $this->post(route('register.store'), [
        'name' => 'Blocked User',
        'email' => 'blocked@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrors('email');
});

test('registration screen redirects to login when public registration is disabled', function () {
    User::factory()->instanceOwner()->create();

    app(InstanceSettings::class)->update([
        'registrations_enabled' => false,
    ]);

    $this->get(route('register'))
        ->assertRedirect(route('login', absolute: false))
        ->assertSessionMissing('status');
});

test('a made-up invitation token does not bypass closed registration', function () {
    User::factory()->instanceOwner()->create();

    $this->post(route('register.store'), [
        'name' => 'Intruder',
        'email' => 'intruder@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'invitation' => 'bogus123',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
    expect(User::where('email', 'intruder@example.com')->exists())->toBeFalse();
});

test('an expired invitation does not bypass closed registration', function () {
    User::factory()->instanceOwner()->create();
    [$plain, $hash] = WorkspaceInvitation::generateToken();
    WorkspaceInvitation::factory()->expired()->create([
        'email' => 'late@example.com',
        'token' => $hash,
    ]);

    $this->post(route('register.store'), [
        'name' => 'Late User',
        'email' => 'late@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'invitation' => $plain,
    ])->assertSessionHasErrors('email');

    expect(User::where('email', 'late@example.com')->exists())->toBeFalse();
});

test('an already accepted invitation does not bypass closed registration', function () {
    User::factory()->instanceOwner()->create();
    [$plain, $hash] = WorkspaceInvitation::generateToken();
    WorkspaceInvitation::factory()->create([
        'email' => 'reused@example.com',
        'token' => $hash,
        'accepted_at' => now()->subHour(),
    ]);

    $this->post(route('register.store'), [
        'name' => 'Second Use',
        'email' => 'someone-else@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'invitation' => $plain,
    ])->assertSessionHasErrors('email');

    expect(User::where('email', 'someone-else@example.com')->exists())->toBeFalse();
});

test('a valid invitation still registers with the invited email', function () {
    User::factory()->instanceOwner()->create();
    [$plain, $hash] = WorkspaceInvitation::generateToken();
    WorkspaceInvitation::factory()->create([
        'email' => 'invited@example.com',
        'token' => $hash,
    ]);

    $this->post(route('register.store'), [
        'name' => 'Invited User',
        'email' => 'typed-something-else@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'invitation' => $plain,
    ])->assertSessionHasNoErrors();

    $this->assertAuthenticated();
    expect(auth()->user()->email)->toBe('invited@example.com');
});

test('registration screen with a made-up invitation redirects to login', function () {
    User::factory()->instanceOwner()->create();

    $this->get(route('register', ['invitation' => 'bogus123']))
        ->assertRedirect(route('login', absolute: false));
});

test('login screen knows public registration is disabled', function () {
    User::factory()->instanceOwner()->create();

    app(InstanceSettings::class)->update([
        'registrations_enabled' => false,
    ]);

    $this->get(route('login'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('auth/login')
            ->where('canRegister', false)
            ->where('registrationDisabledMessage', 'Registration is disabled for this instance.'));
});
