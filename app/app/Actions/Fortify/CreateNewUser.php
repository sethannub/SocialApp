<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use App\Models\WorkspaceInvitation;
use App\Services\Workspace\WorkspaceProvisioningService;
use App\Support\InstanceSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    public function __construct(
        private WorkspaceProvisioningService $provisioning,
        private InstanceSettings $settings,
    ) {}

    /**
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        $invitationToken = request()->input('invitation');
        $invitationToken = is_string($invitationToken) ? $invitationToken : null;

        if (! $this->settings->registrationsAllowed($invitationToken)) {
            throw ValidationException::withMessages([
                'email' => 'Registration is disabled for this instance.',
            ]);
        }

        // SocialApp change: on a public server the first person to register becomes the
        // instance owner. When INSTANCE_OWNER_EMAIL is set, only that email may claim it.
        $ownerEmail = mb_strtolower(trim((string) config('instance.owner_email')));
        if (
            $ownerEmail !== ''
            && ! $this->settings->ownerExists()
            && mb_strtolower(trim((string) ($input['email'] ?? ''))) !== $ownerEmail
        ) {
            throw ValidationException::withMessages([
                'email' => 'Registration is disabled for this instance.',
            ]);
        }

        if ($invitationToken !== null) {
            $invitation = WorkspaceInvitation::findByToken($invitationToken);

            if ($invitation?->isValid()) {
                $input['email'] = $invitation->email;
            }
        }

        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        return DB::transaction(function () use ($input): User {
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
            ]);

            $this->settings->claimOwnerIfMissing($user);
            $this->provisioning->provisionForNewUser($user, request()->input('invitation'));

            return $user->refresh();
        });
    }
}
