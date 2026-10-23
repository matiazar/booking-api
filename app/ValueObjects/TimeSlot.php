<?php

namespace App\ValueObjects;

use Carbon\CarbonImmutable;
use InvalidArgumentException;

/**
 * Half-open time range [start, end), always normalized to UTC.
 */
final readonly class TimeSlot
{
    public CarbonImmutable $start;

    public CarbonImmutable $end;

    public function __construct(CarbonImmutable $start, CarbonImmutable $end)
    {
        if ($end->lessThanOrEqualTo($start)) {
            throw new InvalidArgumentException('A time slot must end after it starts.');
        }

        $this->start = $start->utc();
        $this->end = $end->utc();
    }

    public static function startingAt(CarbonImmutable $start, int $minutes): self
    {
        return new self($start, $start->addMinutes($minutes));
    }

    public function overlaps(self $other): bool
    {
        return $this->start->lessThan($other->end)
            && $other->start->lessThan($this->end);
    }

    /**
     * @return array{starts_at: string, ends_at: string}
     */
    public function toArray(): array
    {
        return [
            'starts_at' => $this->start->toIso8601ZuluString(),
            'ends_at' => $this->end->toIso8601ZuluString(),
        ];
    }
}
