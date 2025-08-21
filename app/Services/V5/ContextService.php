<?php

namespace App\Services\V5;

use App\Models\User;

class ContextService
{
    /**
     * Obtain the current context for the provided user.
     */
    public function get(User $user): array
    {
        $token = $user->currentAccessToken();

        $context = [
            'school_id' => null,
            'season_id' => null,
        ];

        if ($token && $token->context_data) {
            $data = $token->context_data;
            $context['school_id'] = $data['school_id'] ?? null;
            if (array_key_exists('season_id', $data)) {
                $context['season_id'] = $data['season_id'];
            }
        }

        return $context;
    }

    /**
     * Update the current school for the authenticated user without affecting
     * other context keys.
     */
    public function setSchool(User $user, int $schoolId): ?array
    {
        $token = $user->currentAccessToken();

        if (! $token) {
            return null;
        }

        $contextData = $token->context_data ?? [];
        $contextData['school_id'] = $schoolId;

        // Ensure the key exists but preserve existing value if present.
        if (! array_key_exists('season_id', $contextData)) {
            $contextData['season_id'] = null;
        }

        $token->context_data = $contextData;
        $token->save();

        return [
            'school_id' => $contextData['school_id'],
            'season_id' => $contextData['season_id'],
        ];
    }
}
