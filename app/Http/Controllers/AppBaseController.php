<?php

namespace App\Http\Controllers;

use App\Http\Utils\ResponseUtil;

/**
 * @OA\Server(url="/api")
 * @OA\Info(
 *   title="Boukii API",
 *   version="1.0.0"
 * ),
 * @SWG\SecurityScheme(
 *      securityScheme="bearer_token",   // you can name it whatever you want, but not forget to use the same in your request
 *      type="http",
 *      scheme="bearer"
 *      )
 * This class should be parent class for other API controllers
 * Class AppBaseController
 */
class AppBaseController extends Controller
{
    public function sendResponse($result, $message)
    {
        return response()->json(ResponseUtil::makeResponse($message, $result));
    }

    public function sendError($error, $code = 404)
    {
        return response()->json(ResponseUtil::makeError($error), $code);
    }

    public function sendSuccess($message)
    {
        return response()->json([
            'success' => true,
            'message' => $message
        ], 200);
    }

    public function sendSuccessWithErrors($message, $errors, $data): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'errors' => $errors
        ], 200);
    }

    public function getMonitor($request) {
        $user = $request->user();
        $user->load('monitors');
        return $user->monitors[0];
    }

    public function getSchool($request) {
        $user = $request->user();
        $user->load('schools');
        return $user->schools[0];
    }
}
