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

        $user = User::with('clients.schools', 'clients.utilizers')->where('email', $credentials['email'])
            ->where('type', 'client')->orWhere('type', '2')
            ->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
                //$user->load('clients.schools');
                // Comprobar si el school->id está en la lista de escuelas del usuario
                if ($user->clients[0]->schools->contains('id', $school->id)) {
                    $success['token'] = $user->createToken('Boukii', ['client:all'])->plainTextToken;
                    $success['user'] =  $user;
                    return $this->sendResponse($success, 'User login successfully.');
                } else {
                    return $this->sendError('Unauthorized. School not associated with user.', 401);
                }

        } else {
            return $this->sendError('Unauthorized.', 401);
        }
    }

}
