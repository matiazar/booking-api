<?php

namespace App\Http\Resources;

use App\Models\AppointmentType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AppointmentType */
class AppointmentTypeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'duration_minutes' => $this->duration_minutes,
            'price_cents' => $this->price_cents,
        ];
    }
}
