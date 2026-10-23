<?php

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

const TEST_TZ = 'America/Argentina/Buenos_Aires';

/**
 * Freeze the clock at a local Buenos Aires time ("2026-11-02 08:00").
 */
function freezeAt(string $localDateTime): void
{
    test()->travelTo(CarbonImmutable::parse($localDateTime, TEST_TZ));
}

function local(string $localDateTime): CarbonImmutable
{
    return CarbonImmutable::parse($localDateTime, TEST_TZ);
}
