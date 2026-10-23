<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProfessionalResource;
use App\Models\Professional;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class ProfessionalController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $professionals = Professional::query()
            ->with('appointmentTypes')
            ->orderBy('name')
            ->paginate(20);

        return ProfessionalResource::collection($professionals);
    }
}
