<?php

namespace App\Http\Requests;

use App\Data\CreateAppointmentData;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
{
    /**
     * Shape validation only. Business rules (working hours, overlaps,
     * lead time) belong to AppointmentService, not here.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'appointment_type_id' => ['required', 'integer', 'exists:appointment_types,id'],
            'starts_at' => ['required', 'date', 'after:now'],
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_email' => ['required', 'email', 'max:190'],
        ];
    }

    public function toData(): CreateAppointmentData
    {
        return new CreateAppointmentData(
            appointmentTypeId: $this->integer('appointment_type_id'),
            startsAt: CarbonImmutable::parse($this->string('starts_at')->toString())->utc(),
            customerName: $this->string('customer_name')->trim()->toString(),
            customerEmail: $this->string('customer_email')->lower()->trim()->toString(),
        );
    }
}
