<?php

namespace App\Http\Resources;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Appointment */
class AppointmentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'starts_at' => $this->starts_at->toIso8601ZuluString(),
            'ends_at' => $this->ends_at->toIso8601ZuluString(),
            'customer' => [
                'name' => $this->customer_name,
                'email' => $this->customer_email,
            ],
            'professional' => ProfessionalResource::make($this->whenLoaded('professional')),
            'appointment_type' => AppointmentTypeResource::make($this->whenLoaded('appointmentType')),
            'cancelled_at' => $this->cancelled_at?->toIso8601ZuluString(),
            'cancellation_reason' => $this->cancellation_reason,
        ];
    }
}
