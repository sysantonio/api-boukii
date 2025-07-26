<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Services\AI\AIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AIController extends AppBaseController
{
    protected AIService $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function smartSuggestions(Request $request): JsonResponse
    {
        $data = $this->aiService->getSmartSuggestions($request->all());
        return $this->sendResponse($data, 'Smart suggestions retrieved');
    }

    public function courseRecommendations(Request $request): JsonResponse
    {
        $data = $this->aiService->getCourseRecommendations($request->all());
        return $this->sendResponse($data, 'Course recommendations retrieved');
    }

    public function predictiveAnalysis(Request $request): JsonResponse
    {
        $data = $this->aiService->runPredictiveAnalysis($request->all());
        return $this->sendResponse($data, 'Predictive analysis result');
    }
}
