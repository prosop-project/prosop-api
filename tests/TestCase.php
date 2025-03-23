<?php

declare(strict_types=1);

namespace Tests;

use Aws\Rekognition\RekognitionClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Carbon;
use Mockery\MockInterface;
use Tests\TestSupport\MockRekognitionTrait;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;
    use MockRekognitionTrait;

    public Carbon $now;

    protected function setUp(): void
    {
        parent::setUp();

        $this->now = Carbon::now();
    }

    /**
     * Mock the Rekognition client for testing in order to avoid making real requests.
     *
     * @param string $methodName
     *
     * @return void
     */
    protected function mockRekognitionClient(string $methodName): void
    {
        $mockResponse = $this->mockRekognitionResponse($methodName);

        $this->mock(RekognitionClient::class, function (MockInterface $mock) use($methodName, $mockResponse) {
            $mock->shouldReceive($methodName)
                ->once()
                ->andReturn($mockResponse);
        });
    }
}
