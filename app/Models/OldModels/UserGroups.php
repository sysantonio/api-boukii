<?php

namespace App\Models\OldModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserGroups extends Model
{
    use HasFactory;

    protected $connection = 'old';

protected $fillable = [
        'user_main_id',
        'user_secondary_id'
    ];

    protected $guarded = [];

    protected $hidden = ['updated_at'];


    /**
     * Create a new "secondary" user with role Client linked to a "main" one
     */
    public static function createSecondaryByData($userMainID, $firstName, $lastName, $birthDate, $languageID, $language2ID = null)
    {
        // Avoid duplicates
        $secondary = User::join('user_groups AS UG', 'users.id', '=', 'UG.user_secondary_id')
                            ->where('UG.user_main_id', '=', $userMainID)
                            ->whereNull('users.deleted_at')
                            ->where('user_type', '=', UserType::ID_CLIENT)
                            ->where('user_collective', '=', 0)
                            ->where('first_name', '=', $firstName)
                            ->where('last_name', '=', $lastName)
                            ->selectRaw('users.*')
                            ->first();

        if ($secondary)
        {
            // Just update his birth date & language
            $secondary->birth_date = $birthDate;
            $secondary->language1_id = $languageID;
            $secondary->language2_id = $language2ID;
            $secondary->save();
        }
        else
        {
            // Create, with empty email and an impossible-to-guess password,
            // because as of 2022-10 "secondaries" can't login
            $secondary = User::create([
                'user_type' => UserType::ID_CLIENT,
                'user_collective' => 0,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'birth_date' => $birthDate,
                'language1_id' => $languageID,
                'language2_id' => $language2ID,
                'email' => '',
                'password' => bcrypt( \Illuminate\Support\Str::random(16) )
            ]);

            // Link with "main" user
            UserGroups::create([
                'user_main_id' => $userMainID,
                'user_secondary_id' => $secondary->id
            ]);
        }

        // Ensure that "secondary" is linked with all the Schools of "main"
        foreach (UserSchools::where('user_id', $userMainID)
                    ->pluck('school_id')
                    ->toArray() as $sID)
        {
            UserSchools::firstOrCreate([
                'user_id' => $secondary->id,
                'school_id' => $sID
            ]);
        }

        return $secondary;
    }


    /**
     * Link a "secondary" user with role Client to a "main" one
     */
    public static function createSecondaryByID($userMainID, $userSecondaryID)
    {
        // Check that "secondary" exists
        // N.B: as of 2022-10 might be or not an "user_collective"
        $secondary = User::where('user_type', '=', UserType::ID_CLIENT)
                            ->where('id', '=', $userSecondaryID)
                            ->first();

        if ($secondary)
        {
            // Link with "main" user
            UserGroups::firstOrCreate([
                'user_main_id' => $userMainID,
                'user_secondary_id' => $secondary->id
            ]);

            // Ensure that "secondary" is linked with all the Schools of "main"
            foreach (UserSchools::where('user_id', $userMainID)
                        ->pluck('school_id')
                        ->toArray() as $sID)
            {
                UserSchools::firstOrCreate([
                    'user_id' => $secondary->id,
                    'school_id' => $sID
                ]);
            }
        }

        return $secondary;
    }


    /**
     * Check if a "secondary" user exists and is linked to a "main" one.
     */
    public static function checkSecondary($userMainID, $userSecondaryID)
    {
        if (!self::where('user_main_id', '=', $userMainID)
                    ->where('user_secondary_id', '=', $userSecondaryID)
                    ->first())
        {
            return false;
        }
        else
        {
            return User::find($userSecondaryID)->exists();
        }
    }
}
