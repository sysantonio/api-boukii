<?php

namespace App\V5\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

abstract class V5Exception extends Exception
{
    protected string $errorCode;

    protected int $httpStatusCode;

    protected array $context = [];

    public function __construct(
        string $message = '',
        string $errorCode = '',
        int $httpStatusCode = 500,
        array $context = [],
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);

        $this->errorCode = $errorCode ?: $this->getDefaultErrorCode();
        $this->httpStatusCode = $httpStatusCode;
        $this->context = $context;
    }

    abstract protected function getDefaultErrorCode(): string;

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function toResponse(): JsonResponse
    {
        $response = [
            'error' => true,
            'code' => $this->getErrorCode(),
            'message' => __($this->getMessage()),
            'timestamp' => now()->toISOString(),
        ];

        if (! empty($this->context)) {
            $response['context'] = $this->context;
        }

        if (config('app.debug')) {
            $response['debug'] = [
                'file' => $this->getFile(),
                'line' => $this->getLine(),
                'trace' => $this->getTraceAsString(),
            ];
        }

        return response()->json($response, $this->httpStatusCode);
    }
}
