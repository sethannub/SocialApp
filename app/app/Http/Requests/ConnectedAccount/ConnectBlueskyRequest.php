<?php

declare(strict_types=1);

namespace App\Http\Requests\ConnectedAccount;

use App\Enums\Platform;
use App\Models\ConnectedAccount;
use App\Support\InstanceSettings;
use Illuminate\Foundation\Http\FormRequest;

class ConnectBlueskyRequest extends FormRequest
{
    public function authorize(): bool
    {
        // SocialApp change: refuse while Bluesky is switched off under Instance settings ->
        // Platforms (upstream only hid the button; the endpoint still accepted requests).
        return $this->user()->can('create', ConnectedAccount::class)
            && app(InstanceSettings::class)->platformAvailable(Platform::Bluesky);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'identifier' => ['required', 'string', 'max:255'],
            'app_password' => ['required', 'string', 'max:255'],
            'pds_url' => ['nullable', 'url', 'max:255'],
            // "This app password has DM access" — Bluesky has no OAuth scopes to
            // inspect, so the operator self-declares it at connect time instead.
            'dm_access' => ['sometimes', 'boolean'],
        ];
    }
}
