<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Booking\Application\Commands\CancelBooking\CancelBookingCommand;
use Booking\Application\Commands\ConfirmBooking\ConfirmBookingCommand;
use Booking\Application\Commands\CreateBooking\CreateBookingCommand;
use Booking\Application\Commands\CreateBooking\CreateBookingResponse;
use Booking\Application\Commands\RescheduleBooking\RescheduleBookingCommand;
use Booking\Application\DTOs\BookingDTO;
use Booking\Application\Queries\GetBookingDetails\GetBookingDetailsQuery;
use Booking\Application\Queries\ListBookings\ListBookingsQuery;
use Booking\Domain\Exceptions\ResourceNotAvailableException;
use Booking\Domain\ValueObjects\DateTimeRange;
use Booking\Infrastructure\Messaging\CommandBus;
use Booking\Infrastructure\Messaging\QueryBus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly QueryBus $queryBus
    ) {}

    /**
     * GET /api/bookings
     * List bookings with optional filters: date, status, resource_id.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
            'status' => ['nullable', 'string', 'in:pending,confirmed,cancelled,completed'],
            'resource_id' => ['nullable', 'integer'],
        ]);

        $query = new ListBookingsQuery(filters: array_filter($request->only(['date', 'status', 'resource_id'])));

        /** @var BookingDTO[] $bookings */
        $bookings = $this->queryBus->dispatch($query);

        return response()->json([
            'message' => 'Bookings retrieved successfully.',
            'data' => $bookings,
        ]);
    }

    /**
     * GET /api/bookings/{id}
     */
    public function show(int $id): JsonResponse
    {
        $query = new GetBookingDetailsQuery(bookingId: $id);
        $details = $this->queryBus->dispatch($query);

        return response()->json([
            'message' => 'Booking details retrieved successfully.',
            'data' => $details,
        ]);
    }

    /**
     * POST /api/bookings
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'resource_id' => ['required', 'integer', 'exists:dentists,id'],
            'client_id' => ['required', 'integer', 'exists:patients,id'],
            'start_time' => ['required', 'date_format:Y-m-d H:i'],
            'duration' => ['required', 'integer', 'min:5', 'max:480'],
            'notes' => ['nullable', 'string', 'max:500'],
            'treatment_ids' => ['sometimes', 'array', 'min:1'],
            'treatment_ids.*' => ['integer', 'exists:treatments,id'],
        ]);

        $startTime = new \DateTimeImmutable($validated['start_time']);
        $endTime = $startTime->modify("+{$validated['duration']} minutes");
        $timeRange = new DateTimeRange($startTime, $endTime);

        $command = new CreateBookingCommand(
            resourceId: $validated['resource_id'],
            clientId: $validated['client_id'],
            timeRange: $timeRange,
            notes: $validated['notes'] ?? null,
            treatmentIds: $validated['treatment_ids'] ?? []
        );

        try {
            /** @var CreateBookingResponse $response */
            $response = $this->commandBus->dispatch($command);

            return response()->json([
                'message' => 'Booking created successfully.',
                'data' => $response->booking,
            ], 201);
        } catch (ResourceNotAvailableException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'data' => null,
            ], 409);
        }
    }

    /**
     * PATCH /api/bookings/{id}/reschedule
     */
    public function reschedule(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'start_time' => ['required', 'date_format:Y-m-d H:i'],
            'duration' => ['required', 'integer', 'min:5', 'max:480'],
        ]);

        $startTime = new \DateTimeImmutable($validated['start_time']);
        $endTime = $startTime->modify("+{$validated['duration']} minutes");
        $timeRange = new DateTimeRange($startTime, $endTime);

        $command = new RescheduleBookingCommand(bookingId: $id, newTimeRange: $timeRange);

        try {
            $booking = $this->commandBus->dispatch($command);

            return response()->json([
                'message' => 'Booking rescheduled successfully.',
                'data' => $booking,
            ]);
        } catch (ResourceNotAvailableException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'data' => null,
            ], 409);
        }
    }

    /**
     * POST /api/bookings/{id}/confirm
     */
    public function confirm(int $id): JsonResponse
    {
        $command = new ConfirmBookingCommand(bookingId: $id);
        $booking = $this->commandBus->dispatch($command);

        return response()->json([
            'message' => 'Booking confirmed successfully.',
            'data' => $booking,
        ]);
    }

    /**
     * DELETE /api/bookings/{id}
     */
    public function cancel(int $id): JsonResponse
    {
        $command = new CancelBookingCommand(bookingId: $id);
        $this->commandBus->dispatch($command);

        return response()->json([
            'message' => 'Booking cancelled successfully.',
            'data' => null,
        ]);
    }
}
