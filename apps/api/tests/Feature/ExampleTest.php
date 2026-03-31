<?php

declare(strict_types=1);

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers the base HTTP health response.
 */
class ExampleTest extends TestCase
{
    /**
     * Проверяет, что базовый HTTP-эндпоинт отвечает успешно.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
