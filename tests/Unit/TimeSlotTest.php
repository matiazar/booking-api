<?php

use App\ValueObjects\TimeSlot;
use Carbon\CarbonImmutable;

it('detects overlapping slots', function () {
    $a = TimeSlot::startingAt(CarbonImmutable::parse('2026-11-03 12:00:00', 'UTC'), 30);
    $b = TimeSlot::startingAt(CarbonImmutable::parse('2026-11-03 12:15:00', 'UTC'), 30);

    expect($a->overlaps($b))->toBeTrue()
        ->and($b->overlaps($a))->toBeTrue();
});

it('treats back-to-back slots as not overlapping', function () {
    $a = TimeSlot::startingAt(CarbonImmutable::parse('2026-11-03 12:00:00', 'UTC'), 30);
    $b = TimeSlot::startingAt(CarbonImmutable::parse('2026-11-03 12:30:00', 'UTC'), 30);

    expect($a->overlaps($b))->toBeFalse();
});

it('normalizes to UTC', function () {
    $slot = TimeSlot::startingAt(CarbonImmutable::parse('2026-11-03 09:00', 'America/Argentina/Buenos_Aires'), 30);

    expect($slot->toArray())->toBe([
        'starts_at' => '2026-11-03T12:00:00Z',
        'ends_at' => '2026-11-03T12:30:00Z',
    ]);
});

it('rejects an empty or inverted range', function () {
    $start = CarbonImmutable::parse('2026-11-03 12:00:00', 'UTC');

    new TimeSlot($start, $start);
})->throws(InvalidArgumentException::class);
