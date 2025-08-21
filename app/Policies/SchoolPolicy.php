<?php

namespace App\Policies;

use App\Models\School;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SchoolPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any schools.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->can('schools.view');
    }

    /**
     * Determine whether the user can switch to the given school.
     */
    public function switch(User $user, School $school): bool
    {
        if (! $user->can('schools.switch')) {
            return false;
        }

        return $user->schools()->where('schools.id', $school->id)->exists();
    }
}
