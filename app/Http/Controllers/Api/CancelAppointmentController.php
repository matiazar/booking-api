<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CancelAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Services\AppointmentService;

final class CancelAppointmentController extends Controller
{
    public function __construct(private readonly AppointmentService $appointments) {}

    public function __invoke(CancelAppointmentRequest $request, Appointment $appointment): AppointmentResource
    {
        $appointment = $this->appointments->cancel($appointment, $request->reason());

        return AppointmentResource::make($appointment->load(['professional', 'appointmentType']));
    }
}
