<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancelAppointmentRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function reason(): ?string
    {
        return $this->filled('reason') ? $this->string('reason')->trim()->toString() : null;
    }
}
