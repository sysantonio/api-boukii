<?php

namespace App\Services;

use App\Models\User;

class ContextService
{
    /**
     * Get the current context (school_id, season_id) for the given user.
     */
    public function get(User $user): array
    {
        $context = [
            'school_id' => null,
            'season_id' => null,
        ];

        $token = $user->currentAccessToken();

        if ($token && $token->meta) {
            $meta = $token->meta;
            if (is_string($meta)) {
                $meta = json_decode($meta, true) ?? [];
            }
            $saved = $meta['context'] ?? [];
            $context['school_id'] = $saved['school_id'] ?? null;
            $context['season_id'] = array_key_exists('season_id', $saved) ? $saved['season_id'] : null;
        }

        return $context;
    }

    /**
     * Set the current school for the user, resetting the season.
     */
    public function setSchool(User $user, int $schoolId): array
    {
        $context = [
            'school_id' => $schoolId,
            'season_id' => null,
        ];

        $token = $user->currentAccessToken();

        if ($token) {
            $meta = $token->meta;
            if (is_string($meta)) {
                $meta = json_decode($meta, true) ?? [];
            } elseif (! is_array($meta)) {
                $meta = [];
            }

            $meta['context'] = $context;
            $token->meta = $meta;
            $token->save();
        }

        return $context;
    }
}
