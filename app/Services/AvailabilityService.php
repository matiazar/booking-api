<?php

namespace App\Services;

use App\Models\AppointmentType;
use App\Models\Professional;
use App\Models\WorkingHour;
use App\ValueObjects\TimeSlot;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Computes which slots a professional can still take for a given
 * appointment type. It is the single source of truth for "is this time
 * free?", used both to list availability and to validate a booking.
 */
class AvailabilityService
{
    /**
     * Free slots for one local calendar day of the professional.
     *
     * @return Collection<int, TimeSlot>
     */
    public function slotsFor(Professional $professional, AppointmentType $type, CarbonImmutable $date): Collection
    {
        $day = CarbonImmutable::parse($date->format('Y-m-d'), $professional->timezone)->startOfDay();

        if ($this->isOutsideBookingWindow($day)) {
            return collect();
        }

        $earliestStart = CarbonImmutable::now()->addMinutes((int) config('booking.min_lead_minutes'));
        $taken = $this->takenSlots($professional, $day);

        return $professional->workingHours
            ->where('weekday', $day->dayOfWeekIso)
            ->flatMap(fn (WorkingHour $range) => $this->candidateSlots($range, $day, $type->duration_minutes))
            ->reject(fn (TimeSlot $slot) => $slot->start->lessThan($earliestStart))
            ->reject(fn (TimeSlot $slot) => $taken->contains(fn (TimeSlot $busy) => $busy->overlaps($slot)))
            ->sortBy(fn (TimeSlot $slot) => $slot->start->getTimestamp())
            ->values();
    }

    public function isAvailable(Professional $professional, AppointmentType $type, CarbonImmutable $startsAt): bool
    {
        $localDay = $startsAt->setTimezone($professional->timezone);

        return $this->slotsFor($professional, $type, $localDay)
            ->contains(fn (TimeSlot $slot) => $slot->start->equalTo($startsAt));
    }

    /**
     * Every slot that fits in a working-hours range, stepping by the configured interval.
     *
     * @return list<TimeSlot>
     */
    private function candidateSlots(WorkingHour $range, CarbonImmutable $day, int $durationMinutes): array
    {
        $interval = (int) config('booking.slot_interval_minutes');
        $cursor = $day->setTimeFromTimeString($range->opens_at);
        $closesAt = $day->setTimeFromTimeString($range->closes_at);
        $slots = [];

        while ($cursor->addMinutes($durationMinutes)->lessThanOrEqualTo($closesAt)) {
            $slots[] = TimeSlot::startingAt($cursor, $durationMinutes);
            $cursor = $cursor->addMinutes($interval);
        }

        return $slots;
    }

    /**
     * @return Collection<int, TimeSlot>
     */
    private function takenSlots(Professional $professional, CarbonImmutable $day): Collection
    {
        return $professional->appointments()
            ->blocking()
            ->overlapping($day, $day->endOfDay())
            ->get(['starts_at', 'ends_at'])
            ->map(fn ($appointment) => new TimeSlot($appointment->starts_at, $appointment->ends_at));
    }

    private function isOutsideBookingWindow(CarbonImmutable $day): bool
    {
        $today = CarbonImmutable::now($day->timezone)->startOfDay();
        $lastDay = $today->addDays((int) config('booking.max_days_ahead'));

        return $day->lessThan($today) || $day->greaterThan($lastDay);
    }
}
