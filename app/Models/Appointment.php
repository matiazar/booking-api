<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Database\Factories\AppointmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property AppointmentStatus $status
 * @property CarbonImmutable $starts_at
 * @property CarbonImmutable $ends_at
 */
#[Fillable([
    'appointment_type_id',
    'customer_name',
    'customer_email',
    'starts_at',
    'ends_at',
    'status',
    'cancelled_at',
    'cancellation_reason',
])]
class Appointment extends Model
{
    /** @use HasFactory<AppointmentFactory> */
    use HasFactory, HasUlids;

    /** @return BelongsTo<Professional, $this> */
    public function professional(): BelongsTo
    {
        return $this->belongsTo(Professional::class);
    }

    /** @return BelongsTo<AppointmentType, $this> */
    public function appointmentType(): BelongsTo
    {
        return $this->belongsTo(AppointmentType::class);
    }

    /**
     * Appointments that occupy the agenda (i.e. not cancelled).
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function blocking(Builder $query): void
    {
        $query->whereIn('status', AppointmentStatus::blocking());
    }

    /**
     * Appointments whose time range intersects [$start, $end).
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function overlapping(Builder $query, CarbonInterface $start, CarbonInterface $end): void
    {
        $query->where('starts_at', '<', $end->toImmutable()->utc())
            ->where('ends_at', '>', $start->toImmutable()->utc());
    }

    protected function casts(): array
    {
        return [
            'status' => AppointmentStatus::class,
            'starts_at' => 'immutable_datetime',
            'ends_at' => 'immutable_datetime',
            'cancelled_at' => 'immutable_datetime',
        ];
    }
}
