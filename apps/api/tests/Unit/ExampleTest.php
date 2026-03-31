<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Covers the base unit-test bootstrap.
 */
class ExampleTest extends TestCase
{
    /**
     * Проверяет базовую работоспособность unit-test окружения.
     */
    public function test_that_true_is_true(): void
    {
        $this->assertTrue(true);
    }
}
