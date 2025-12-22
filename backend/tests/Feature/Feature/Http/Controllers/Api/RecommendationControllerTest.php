<?php

namespace Tests\Feature\Feature\Http\Controllers\Api;

use Tests\TestCase;

class RecommendationControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
