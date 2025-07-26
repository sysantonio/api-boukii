<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class AiApiTest extends TestCase
{
    use WithoutMiddleware, DatabaseTransactions;

    /** @test */
    public function test_smart_suggestions_endpoint()
    {
        $this->response = $this->json('POST', '/api/ai/smart-suggestions', []);
        $this->response->assertStatus(200);
        $this->response->assertJsonStructure(['success', 'data' => ['suggestions'], 'message']);
    }

    /** @test */
    public function test_course_recommendations_endpoint()
    {
        $this->response = $this->json('POST', '/api/ai/course-recommendations', []);
        $this->response->assertStatus(200);
        $this->response->assertJsonStructure(['success', 'data' => ['courses'], 'message']);
    }

    /** @test */
    public function test_predictive_analysis_endpoint()
    {
        $this->response = $this->json('POST', '/api/ai/predictive-analysis', []);
        $this->response->assertStatus(200);
        $this->response->assertJsonStructure(['success', 'data' => ['predictions'], 'message']);
    }
}
