<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait ProblemDetails
{
    protected function problem(string $detail, int $status, array $errors = null): JsonResponse
    {
        $problem = [
            'type' => 'about:blank',
            'title' => Response::$statusTexts[$status] ?? 'Error',
            'status' => $status,
            'detail' => $detail,
        ];

        if ($errors) {
            $problem['errors'] = $errors;
        }

        return response()->json($problem, $status, ['Content-Type' => 'application/problem+json']);
    }
}
