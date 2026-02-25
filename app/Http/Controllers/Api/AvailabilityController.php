<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Booking\Application\Queries\GetAvailableSlots\GetAvailableSlotsQuery;
use Booking\Application\Queries\GetResourceSchedule\GetResourceScheduleQuery;
use Booking\Domain\ValueObjects\Duration;
use Booking\Infrastructure\Messaging\QueryBus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    public function __construct(
        private readonly QueryBus $queryBus
    ) {}

    /**
     * GET /api/resources/{resourceId}/slots
     * Returns available time slots for a resource on a given date.
     */
    public function availableSlots(Request $request, int $resourceId): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'duration' => ['sometimes', 'integer', 'min:5', 'max:480'],
        ]);

        $query = new GetAvailableSlotsQuery(
            resourceId: $resourceId,
            date: new \DateTimeImmutable($validated['date']),
            slotDuration: Duration::fromMinutes($validated['duration'] ?? 30)
        );

        $slots = $this->queryBus->dispatch($query);

        return response()->json([
            'message' => 'Available slots retrieved successfully.',
            'data' => $slots,
        ]);
    }

    /**
     * GET /api/resources/{resourceId}/schedule
     * Returns a resource's full schedule (booked slots) for a given date.
     */
    public function resourceSchedule(int $resourceId, Request $request): JsonResponse
    {
        $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        $query = new GetResourceScheduleQuery(
            resourceId: $resourceId,
            date: $request->input('date')
        );

        $schedule = $this->queryBus->dispatch($query);

        return response()->json([
            'message' => 'Resource schedule retrieved successfully.',
            'data' => $schedule,
        ]);
    }
}
