<?php

namespace App\Http\Controllers\Teach;

use App\Http\Controllers\AppBaseController;
use App\Services\Auth\LoginService;
use Illuminate\Http\Request;

/**
 * Class UserController
 * @package App\Http\Controllers\API
 */

class AuthController extends AppBaseController
{
    protected LoginService $loginService;

    public function __construct(LoginService $loginService)
    {
        $this->loginService = $loginService;
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
        $result = $this->loginService->authenticate($request, ['monitor', 3]);

        if (!$result) {
            return $this->sendError('Unauthorized.', 401);
        }

        return $this->sendResponse($result, 'User login successfully.');

    }

}
