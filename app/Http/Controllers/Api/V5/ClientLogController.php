<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\V5\ClientLogRequest;
use App\Models\ClientLog;
use App\Models\School;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class ClientLogController extends Controller
{
    /**
     * Store a newly created log entry.
     */
    public function store(ClientLogRequest $request): JsonResponse
    {
        $data = $request->validated();

        $message = $this->sanitizeMessage($data['message']);

        $context = $data['context'] ?? [];
        $context['client_time'] = $data['clientTime'];
        $context = $this->sanitizeContext($context);
        $contextJson = json_encode($context);
        if (strlen($contextJson) > 2048) {
            $context = ['truncated' => true];
        }

        $user = $request->user();
        $schoolId = $this->resolveSchoolId($request->user(), $request->header('X-School-ID'));

        $log = ClientLog::create([
            'level' => $data['level'],
            'message' => $message,
            'context' => $context,
            'user_id' => $user?->id,
            'school_id' => $schoolId,
            'created_at' => now(),
        ]);

        return response()->json(['id' => $log->id], 202);
    }

    private function sanitizeMessage(string $message): string
    {
        $clean = Str::limit(strip_tags($message), 2048, '');
        $clean = preg_replace('/[\w.+-]+@[\w.-]+\.[A-Za-z]{2,6}/', '[redacted]', $clean);
        $clean = preg_replace('/\b\d{3}[-.]?\d{3}[-.]?\d{4}\b/', '[redacted]', $clean);
        return $clean;
    }

    private function sanitizeContext(array $context): array
    {
        $pii = ['email', 'password', 'token', 'phone', 'ssn', 'address', 'name'];
        $clean = [];
        foreach ($context as $key => $value) {
            if (in_array(strtolower($key), $pii, true)) {
                continue;
            }
            if (is_array($value)) {
                $clean[$key] = $this->sanitizeContext($value);
            } elseif (is_string($value)) {
                $clean[$key] = Str::limit(strip_tags($value), 2048, '');
            } else {
                $clean[$key] = $value;
            }
        }
        return $clean;
    }

    private function resolveSchoolId($user, $headerSchoolId): ?int
    {
        if ($headerSchoolId) {
            return (int) $headerSchoolId;
        }
        if (!$user) {
            return null;
        }
        $token = $user->currentAccessToken();
        $context = $token ? $token->context_data : null;
        if (is_string($context)) {
            $context = json_decode($context, true);
        }
        if (is_array($context) && isset($context['school_id'])) {
            return (int) $context['school_id'];
        }
        if (is_array($context) && isset($context['school_slug'])) {
            $school = School::where('slug', $context['school_slug'])->first();
            return $school?->id;
        }
        return null;
    }
}
