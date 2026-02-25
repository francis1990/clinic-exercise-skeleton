<?php

declare(strict_types=1);

namespace Booking\Domain\Repositories;

use Booking\Domain\Entities\Booking;
use Booking\Domain\ValueObjects\BookingId;
use Booking\Domain\ValueObjects\ClientId;
use Booking\Domain\ValueObjects\DateTimeRange;
use Booking\Domain\ValueObjects\ResourceId;

interface BookingRepositoryInterface
{
    public function findById(BookingId $id): ?Booking;

    /**
     * Persist a new or updated booking.
     * For new bookings (id === null), assigns the generated ID to the entity.
     */
    public function save(Booking $booking): void;

    public function delete(BookingId $id): void;

    /**
     * Find active (non-cancelled) bookings for a resource that overlap the given range.
     *
     * @return Booking[]
     */
    public function findByResourceAndDateRange(ResourceId $resourceId, DateTimeRange $range): array;

    /**
     * Find all bookings for a client, optionally filtered.
     *
     * @param  array<string, mixed>  $filters
     * @return Booking[]
     */
    public function findByClientId(ClientId $clientId, array $filters = []): array;

    /**
     * Find all bookings, optionally filtered by date or status.
     *
     * @param  array<string, mixed>  $filters  Supported keys: date (Y-m-d), status, resource_id
     * @return Booking[]
     */
    public function findAll(array $filters = []): array;
}
