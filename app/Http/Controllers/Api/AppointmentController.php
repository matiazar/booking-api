<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Services\AppointmentService;
use Illuminate\Http\JsonResponse;

/**
 * Thin by design: validate (Form Request) -> delegate (Service) -> format (Resource).
 */
final class AppointmentController extends Controller
{
    public function __construct(private readonly AppointmentService $appointments) {}

    public function store(StoreAppointmentRequest $request): JsonResponse
    {
        $appointment = $this->appointments->book($request->toData());

        return AppointmentResource::make($appointment->load(['professional', 'appointmentType']))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Appointment $appointment): AppointmentResource
    {
        return AppointmentResource::make($appointment->load(['professional', 'appointmentType']));
    }
}
