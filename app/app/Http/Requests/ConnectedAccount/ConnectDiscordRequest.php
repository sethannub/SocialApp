<?php

declare(strict_types=1);

namespace App\Http\Requests\ConnectedAccount;

use App\Enums\Platform;
use App\Models\ConnectedAccount;
use App\Support\InstanceSettings;
use Illuminate\Foundation\Http\FormRequest;

class ConnectDiscordRequest extends FormRequest
{
    public function authorize(): bool
    {
        // SocialApp change: refuse while Discord is switched off under Instance settings ->
        // Platforms (upstream only hid the button; the endpoint still accepted requests).
        return $this->user()->can('create', ConnectedAccount::class)
            && app(InstanceSettings::class)->platformAvailable(Platform::Discord);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'webhook_url' => ['required', 'string', 'url', 'max:2048'],
        ];
    }
}
