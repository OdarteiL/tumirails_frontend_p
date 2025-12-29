<?php

namespace Tests\Unit\Actions\Recommendation;

use App\Actions\Recommendation\GenerateRecommendationsAction;
use App\Models\Estimation;
use App\Services\RecommendationService;
use Tests\TestCase;

class GenerateRecommendationsActionTest extends TestCase
{
    public function test_service_delegates_to_action_with_defaults(): void
    {
        $estimation = $this->createMock(Estimation::class);

        $expected = [
            ['providers' => [], 'components' => [], 'total_price' => 0.0, 'currency' => 'GHS'],
        ];

        $actionMock = $this->createMock(GenerateRecommendationsAction::class);
        $actionMock->expects($this->once())
            ->method('execute')
            ->with($estimation, $this->callback(function ($opts) {
                return isset($opts['top_n']) && $opts['top_n'] === 5 && isset($opts['beam']) && $opts['beam'] === 20;
            }))
            ->willReturn($expected);

        $service = new RecommendationService($actionMock);

        $result = $service->generateRecommendations($estimation);

        $this->assertSame($expected, $result);
    }
}
