<?php

namespace App\Http\Controllers\BookingPage;

use App\Http\Controllers\AppBaseController;
use App\Services\Auth\LoginService;
use Illuminate\Http\Request;

/**
 * Class UserController
 * @package App\Http\Controllers\API
 */

class AuthController extends SlugAuthController
{
    protected LoginService $loginService;

    public function __construct(LoginService $loginService, Request $request)
    {
        parent::__construct($request);
        $this->loginService = $loginService;
    }

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
        $result = $this->loginService->authenticate($request, ['client', 2], $this->school);

        if (!$result) {
            return $this->sendError('Unauthorized.', 401);
        }

        return $this->sendResponse($result, 'User login successfully.');

    }
}
