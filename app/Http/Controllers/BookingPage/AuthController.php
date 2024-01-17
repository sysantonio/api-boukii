<?php

namespace App\Http\Controllers\BookingPage;

use App\Http\Controllers\AppBaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Response;
use Validator;

;

/**
 * Class UserController
 * @package App\Http\Controllers\API
 */

class AuthController extends SlugAuthController
{

    /**
     * @OA\Post(
     *      path="/slug/login",
     *      summary="Login",
     *      tags={"BookingPage"},
     *      description="Login user",
     *      @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string")
     *         ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  ref="#/components/schemas/User"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        $school = $this->school;


        $users = User::with('clients.schools', 'clients.utilizers')
            ->where('email', $credentials['email'])
            ->where(function ($query) {
                $query->where('type', 'client')
                    ->orWhere('type', '2');
            })
            ->get();

        foreach ($users as $user) {
            // Verificar si la contraseña es correcta
            if (Hash::check($credentials['password'], $user->password)) {
                // Cargar escuelas relacionadas si las hay
                if ($user->type == 'client' || $user->type == 2) {
                    if ($user->clients[0]->schools->contains('id', $school->id)) {
                        $success['token'] = $user->createToken('Boukii')->plainTextToken;
                        $user->load('clients.utilizers.sports', 'clients.sports');
                        $user->tokenCan('client:all');
                        $success['user'] =  $user;
                        return $this->sendResponse($success, 'User login successfully.');
                    } else {
                        return $this->sendError('Unauthorized for this school.', 401);
                    }
                }
            }
        }

        return $this->sendError('Unauthorized.', 401);

    }
}
