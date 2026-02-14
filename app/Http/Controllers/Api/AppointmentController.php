<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\SlotNotAvailableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function __construct(
        private readonly AppointmentService $appointmentService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        $date = Carbon::createFromFormat('Y-m-d', $request->query('date'));

        $appointments = Appointment::with('treatments')
            ->whereDate('start_time', $date)
            ->orderBy('start_time')
            ->get();

        return response()->json([
            'message' => 'Citas del día ' . $date->format('Y-m-d') . '.',
            'data' => AppointmentResource::collection($appointments),
        ]);
    }

    public function store(StoreAppointmentRequest $request): JsonResponse
    {
        try {
            $appointment = $this->appointmentService->create($request->validated());

            return response()->json([
                'message' => 'Cita creada correctamente.',
                'data' => new AppointmentResource($appointment),
            ], 201);
        } catch (SlotNotAvailableException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'data' => null,
            ], 409);
        }
    }
}
