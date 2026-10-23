<?php

namespace App\Models;

use Database\Factories\AppointmentTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['name', 'duration_minutes', 'price_cents'])]
class AppointmentType extends Model
{
    /** @use HasFactory<AppointmentTypeFactory> */
    use HasFactory;

    /** @return BelongsTo<Professional, $this> */
    public function professional(): BelongsTo
    {
        return $this->belongsTo(Professional::class);
    }

    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'price_cents' => 'integer',
        ];
    }
}
