<?php

namespace App\Models;

use Database\Factories\WorkingHourFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An open range in the professional's local time, e.g. Monday 09:00-13:00.
 */
#[Fillable(['weekday', 'opens_at', 'closes_at'])]
class WorkingHour extends Model
{
    /** @use HasFactory<WorkingHourFactory> */
    use HasFactory;

    /** @return BelongsTo<Professional, $this> */
    public function professional(): BelongsTo
    {
        return $this->belongsTo(Professional::class);
    }

    protected function casts(): array
    {
        return [
            'weekday' => 'integer',
        ];
    }
}
