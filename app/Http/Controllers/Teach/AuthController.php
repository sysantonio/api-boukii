<?php

namespace App\Http\Controllers\Teach;

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

class AuthController extends AppBaseController
{

    public function __construct()
    {

    }


    /**
     * @OA\Post(
     *      path="/teach/login",
     *      summary="Teach Login",
     *      tags={"Auth"},
     *      description="Login user",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"email", "password"},
     *               @OA\Property(property="email", type="email"),
     *               @OA\Property(property="password", type="password")
     *            ),
     *        ),
     *    ),
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

        // Buscar usuarios por correo electrónico y tipo
        $users = User::where('email', $credentials['email'])
            ->where('type', 'monitor')->orWhere('type', 3)
            ->get();

        foreach ($users as $user) {
            // Verificar si la contraseña es correcta
            if (Hash::check($credentials['password'], $user->password)) {
                // Cargar escuelas relacionadas si las hay
                if ($user->type == 'monitor') {
                    $success['token'] = $user->createToken('Boukii')->plainTextToken;
                    $user->load('monitors');
                    $user->tokenCan('teach:all');
                    $success['user'] =  $user;
                    return $this->sendResponse($success, 'User login successfully.');
                }
            }
        }

        // Si no se encuentra ningún usuario o la contraseña no coincide
        return $this->sendError('Unauthorized.', 401);

    }

}
