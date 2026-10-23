<?php

namespace App\Services;

use App\Data\CreateAppointmentData;
use App\Enums\AppointmentStatus;
use App\Events\AppointmentBooked;
use App\Events\AppointmentCancelled;
use App\Exceptions\AppointmentNotCancellableException;
use App\Exceptions\SlotUnavailableException;
use App\Models\Appointment;
use App\Models\AppointmentType;
use App\Models\Professional;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Booking use cases. Controllers, jobs or commands call these methods;
 * all business rules are enforced here, never in the HTTP layer.
 */
class AppointmentService
{
    public function __construct(
        private readonly AvailabilityService $availability,
    ) {}

    /**
     * @throws SlotUnavailableException
     */
    public function book(CreateAppointmentData $data): Appointment
    {
        $type = AppointmentType::query()->findOrFail($data->appointmentTypeId);

        return DB::transaction(function () use ($type, $data): Appointment {
            // Lock the professional's row so two concurrent requests for the
            // same agenda are serialized and cannot both take the same slot.
            $professional = Professional::query()
                ->with('workingHours')
                ->lockForUpdate()
                ->findOrFail($type->professional_id);

            if (! $this->availability->isAvailable($professional, $type, $data->startsAt)) {
                throw new SlotUnavailableException;
            }

            $appointment = $professional->appointments()->create([
                'appointment_type_id' => $type->id,
                'customer_name' => $data->customerName,
                'customer_email' => $data->customerEmail,
                'starts_at' => $data->startsAt,
                'ends_at' => $data->startsAt->addMinutes($type->duration_minutes),
                'status' => AppointmentStatus::Confirmed,
            ]);

            AppointmentBooked::dispatch($appointment);

            return $appointment;
        });
    }

    /**
     * @throws AppointmentNotCancellableException
     */
    public function cancel(Appointment $appointment, ?string $reason = null): Appointment
    {
        if ($appointment->status === AppointmentStatus::Cancelled) {
            throw AppointmentNotCancellableException::alreadyCancelled();
        }

        $noticeHours = (int) config('booking.cancellation_notice_hours');

        if ($appointment->starts_at->lessThan(CarbonImmutable::now()->addHours($noticeHours))) {
            throw AppointmentNotCancellableException::tooLate($noticeHours);
        }

        $appointment->update([
            'status' => AppointmentStatus::Cancelled,
            'cancelled_at' => CarbonImmutable::now(),
            'cancellation_reason' => $reason,
        ]);

        AppointmentCancelled::dispatch($appointment);

        return $appointment;
    }
}
