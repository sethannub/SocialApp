<?php

/**
 * SocialApp change: INSTANCE_OWNER_EMAIL locks who can register as the first user (the owner)
 * on a freshly exposed server.
 */

use App\Models\User;
use Illuminate\Testing\TestResponse;

function registerAs(string $email): TestResponse
{
    return test()->post(route('register.store'), [
        'name' => 'Someone',
        'email' => $email,
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);
}

test('a stranger cannot claim the owner account when an owner email is set', function () {
    config(['instance.owner_email' => 'owner@y5media.test']);

    registerAs('stranger@example.com')->assertSessionHasErrors('email');

    $this->assertGuest();
    expect(User::count())->toBe(0);
});

test('the configured owner email registers and becomes the owner', function () {
    config(['instance.owner_email' => 'Owner@Y5Media.test']);

    registerAs('owner@y5media.test')->assertSessionHasNoErrors();

    expect(auth()->user()?->isInstanceOwner())->toBeTrue();
});

test('without an owner email the first user still registers (upstream behaviour)', function () {
    config(['instance.owner_email' => '']);

    registerAs('first@example.com')->assertSessionHasNoErrors();

    expect(auth()->user()?->isInstanceOwner())->toBeTrue();
});
