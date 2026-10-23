<?php

namespace App\Http\Requests;

use App\Data\AvailabilityQuery;
use App\Models\AppointmentType;
use App\Models\Professional;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AvailabilityRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'appointment_type_id' => [
                'required',
                'integer',
                // The type must belong to the professional in the URL.
                Rule::exists('appointment_types', 'id')
                    ->where('professional_id', $this->professional()->getKey()),
            ],
            'date' => ['required', 'date_format:Y-m-d'],
        ];
    }

    public function toQuery(): AvailabilityQuery
    {
        return new AvailabilityQuery(
            professional: $this->professional()->loadMissing('workingHours'),
            appointmentType: AppointmentType::query()->findOrFail($this->integer('appointment_type_id')),
            date: CarbonImmutable::createFromFormat('Y-m-d', $this->string('date')->toString()),
        );
    }

    private function professional(): Professional
    {
        /** @var Professional */
        return $this->route('professional');
    }
}
