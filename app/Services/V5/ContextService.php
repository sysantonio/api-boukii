<?php

namespace App\Services\V5;

use App\Models\User;

class ContextService
{
    /**
     * Get context information for the given user.
     */
    public function getContext(User $user): array
    {
        $token = $user->currentAccessToken();

        $context = [
            'school_id' => null,
            'season_id' => null,
        ];

        if ($token && $token->context_data) {
            $data = $token->context_data;
            $context['school_id'] = $data['school_id'] ?? null;
            $context['season_id'] = $data['season_id'] ?? null;
        }

        return $context;
    }

    /**
     * Switch the current school for the authenticated user.
     *
     * @return array|null Returns updated context or null if no active token.
     */
    public function switchSchool(User $user, int $schoolId): ?array
    {
        $token = $user->currentAccessToken();

        if (! $token) {
            return null;
        }

        $contextData = $token->context_data ?? [];
        $contextData['school_id'] = $schoolId;
        $contextData['season_id'] = null;

        $token->context_data = $contextData;
        $token->save();

        return [
            'school_id' => $schoolId,
            'season_id' => null,
        ];
    }
}
