<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AvailabilityRequest;
use App\Models\Professional;
use App\Services\AvailabilityService;
use App\ValueObjects\TimeSlot;
use Illuminate\Http\JsonResponse;

final class AvailabilityController extends Controller
{
    public function __construct(private readonly AvailabilityService $availability) {}

    /**
     * The Professional parameter is what triggers route model binding;
     * the Form Request reads the bound model from the route.
     */
    public function __invoke(AvailabilityRequest $request, Professional $professional): JsonResponse
    {
        $query = $request->toQuery();

        $slots = $this->availability->slotsFor($query->professional, $query->appointmentType, $query->date);

        return response()->json([
            'data' => [
                'date' => $query->date->format('Y-m-d'),
                'timezone' => $query->professional->timezone,
                'slots' => $slots->map(fn (TimeSlot $slot) => $slot->toArray())->all(),
            ],
        ]);
    }
}
