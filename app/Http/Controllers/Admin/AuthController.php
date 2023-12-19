<?php

namespace App\Http\Controllers\Admin;

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
     *      path="/admin/login",
     *      summary="Login",
     *      tags={"Auth"},
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

        // Buscar usuarios por correo electrónico y tipo
        $users = User::where('email', $credentials['email'])
            ->where(function ($query) {
                $query->where('type', 'admin')
                    ->orWhere('type', 1)
                    ->orWhere('type', 'superadmin')
                    ->orWhere('type', 4);
            })
            ->get();

        foreach ($users as $user) {
            // Verificar si la contraseña es correcta
            if (Hash::check($credentials['password'], $user->password)) {

                // Cargar escuelas relacionadas si las hay
                if ($user->type == 'superadmin' || $user->type == '4') {
                    $success['token'] = $user->createToken('Boukii', ['permissions:all'])->plainTextToken;
                    $success['user'] = $user;
                } else if ($user->type == '1' || $user->type == 'admin') {
                    $user->load('schools');
                    $success['token'] = $user->createToken('Boukii', ['admin:all'])->plainTextToken;
                    $success['user'] = $user;
                } else {
                    return $this->sendError('Unauthorized.', 401);
                }

                return $this->sendResponse($success, 'User login successfully.');
            }
        }

        // Si no se encuentra ningún usuario o la contraseña no coincide
        return $this->sendError('Unauthorized.', 401);
    }

}
