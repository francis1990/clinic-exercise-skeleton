<?php

namespace Tests\Unit\Application\Queries;

use Booking\Application\Queries\GetAvailableSlots\AvailableSlotDTO;
use Booking\Application\Queries\GetAvailableSlots\GetAvailableSlotsHandler;
use Booking\Application\Queries\GetAvailableSlots\GetAvailableSlotsQuery;
use Booking\Domain\Entities\Resource;
use Booking\Domain\Entities\TimeSlot;
use Booking\Domain\Repositories\ResourceRepositoryInterface;
use Booking\Domain\Services\AvailabilityService;
use Booking\Domain\ValueObjects\DateTimeRange;
use Booking\Domain\ValueObjects\Duration;
use Booking\Domain\ValueObjects\ResourceId;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetAvailableSlotsHandlerTest extends TestCase
{
    private AvailabilityService&MockObject $availabilityService;

    private ResourceRepositoryInterface&MockObject $resourceRepository;

    private GetAvailableSlotsHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->availabilityService = $this->createMock(AvailabilityService::class);
        $this->resourceRepository = $this->createMock(ResourceRepositoryInterface::class);

        $this->handler = new GetAvailableSlotsHandler(
            $this->availabilityService,
            $this->resourceRepository
        );
    }

    public function test_returns_available_slots_as_dtos(): void
    {
        $resourceId = ResourceId::fromInt(1);
        $resource = new Resource($resourceId, 'John', 'Doe');

        $slot = new TimeSlot(
            $resourceId,
            new DateTimeRange(
                new \DateTimeImmutable('2026-03-01 09:00'),
                new \DateTimeImmutable('2026-03-01 09:30')
            )
        );

        $this->resourceRepository
            ->method('findById')
            ->willReturn($resource);

        $this->availabilityService
            ->method('getAvailableSlots')
            ->willReturn([$slot]);

        $query = new GetAvailableSlotsQuery(
            resourceId: 1,
            date: new \DateTimeImmutable('2026-03-01'),
            slotDuration: Duration::fromMinutes(30)
        );

        $result = $this->handler->handle($query);

        $this->assertCount(1, $result);
        $this->assertInstanceOf(AvailableSlotDTO::class, $result[0]);
        $this->assertSame('2026-03-01 09:00', $result[0]->startTime);
        $this->assertSame('2026-03-01 09:30', $result[0]->endTime);
        $this->assertSame(30, $result[0]->durationMinutes);
    }

    public function test_throws_when_resource_not_found(): void
    {
        $this->resourceRepository->method('findById')->willReturn(null);

        $this->expectException(\RuntimeException::class);

        $query = new GetAvailableSlotsQuery(
            resourceId: 999,
            date: new \DateTimeImmutable('2026-03-01'),
            slotDuration: Duration::fromMinutes(30)
        );

        $this->handler->handle($query);
    }
}
