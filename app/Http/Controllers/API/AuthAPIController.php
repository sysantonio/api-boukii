<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Mail\RecoverPassword;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Response;
use Validator;

;

/**
 * Class UserController
 * @package App\Http\Controllers\API
 */

class AuthAPIController extends AppBaseController
{

    public function __construct()
    {

    }
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'type' => 'required', // Agrega el tipo de usuario como requerido
            'school_id' => 'required'
        ]);

        $user = User::where('email', $request->email)
            ->where('type', $request->type)
            ->whereHas('clientsSchools', function ($q) use($request) {
                $q ->where('school_id', $request->school_id);
            })->first();

        if (!$user) {
            return response()->json(['email' => 'No podemos encontrar un usuario con ese email y tipo.']);
        }

        // Generar token
        $token = Str::random(60);

        $user->recover_token = $token;
        $user->save();

        // Enviar correo electrónico con el enlace
        Mail::to($user->email)->send(new RecoverPassword($user));

        return response()->json(['message' => 'Se ha enviado un enlace de restablecimiento de contraseña.']);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'password' => 'required|confirmed'
        ]);

        // Verificar token
        $user = User::where('recover_token', $request->token)
            ->first();

        if (!$user) {
            return response()->json(['message' => 'Token inválido o email incorrecto.'], 404);
        }

        // Cambiar la contraseña
        $user->password = bcrypt($request->password);
        $user->recover_token = null; // Borrar el token de recuperación
        $user->save();

        return response()->json(['message' => 'Contraseña actualizada correctamente.']);
    }




}
