<?php

namespace App\V5\Logging;

use Illuminate\Http\Request;

class ContextProcessor
{
    public function __invoke(array $record): array
    {
        $request = request();

        if ($request instanceof Request) {
            $record['extra'] = array_merge($record['extra'] ?? [], [
                'correlation_id' => V5Logger::getCorrelationId(),
                'user_id' => $request->user()?->id,
                'season_id' => $request->get('season_id'),
                'school_id' => $request->get('school_id'),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return $record;
    }
}
