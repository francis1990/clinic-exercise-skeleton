<?php

declare(strict_types=1);

namespace Booking\Infrastructure\Persistence\Eloquent\Repositories;

use App\Models\Appointment;
use Booking\Domain\Entities\Booking;
use Booking\Domain\Repositories\BookingRepositoryInterface;
use Booking\Domain\ValueObjects\BookingId;
use Booking\Domain\ValueObjects\BookingStatus;
use Booking\Domain\ValueObjects\ClientId;
use Booking\Domain\ValueObjects\DateTimeRange;
use Booking\Domain\ValueObjects\ResourceId;

final class EloquentBookingRepository implements BookingRepositoryInterface
{
    public function findById(BookingId $id): ?Booking
    {
        $model = Appointment::with('treatments')->find($id->value());

        return $model ? $this->toDomain($model) : null;
    }

    public function save(Booking $booking): void
    {
        if ($booking->id() === null) {
            $model = Appointment::create([
                'patient_id' => $booking->clientId()->value(),
                'dentist_id' => $booking->resourceId()->value(),
                'start_time' => $booking->timeRange()->start(),
                'end_time' => $booking->timeRange()->end(),
                'reason' => $booking->notes(),
                'status' => $booking->status()->value(),
            ]);

            if (! empty($booking->treatmentIds())) {
                $model->treatments()->attach($booking->treatmentIds());
            }

            $booking->assignId(BookingId::fromInt($model->id));

            return;
        }

        $model = Appointment::findOrFail($booking->id()->value());
        $model->update([
            'patient_id' => $booking->clientId()->value(),
            'dentist_id' => $booking->resourceId()->value(),
            'start_time' => $booking->timeRange()->start(),
            'end_time' => $booking->timeRange()->end(),
            'reason' => $booking->notes(),
            'status' => $booking->status()->value(),
        ]);

        if (! empty($booking->treatmentIds())) {
            $model->treatments()->sync($booking->treatmentIds());
        }
    }

    public function delete(BookingId $id): void
    {
        Appointment::destroy($id->value());
    }

    public function findByResourceAndDateRange(ResourceId $resourceId, DateTimeRange $range): array
    {
        return Appointment::with('treatments')
            ->where('dentist_id', $resourceId->value())
            ->where('start_time', '<', $range->end())
            ->where('end_time', '>', $range->start())
            ->get()
            ->map(fn (Appointment $m) => $this->toDomain($m))
            ->all();
    }

    public function findByClientId(ClientId $clientId, array $filters = []): array
    {
        $query = Appointment::with('treatments')->where('patient_id', $clientId->value());

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->get()->map(fn (Appointment $m) => $this->toDomain($m))->all();
    }

    public function findAll(array $filters = []): array
    {
        $query = Appointment::with('treatments');

        if (isset($filters['date'])) {
            $query->whereDate('start_time', $filters['date']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['resource_id'])) {
            $query->where('dentist_id', $filters['resource_id']);
        }

        return $query->orderBy('start_time')->get()
            ->map(fn (Appointment $m) => $this->toDomain($m))
            ->all();
    }

    private function toDomain(Appointment $model): Booking
    {
        $treatmentIds = $model->relationLoaded('treatments')
            ? $model->treatments->pluck('id')->all()
            : [];

        return Booking::reconstitute(
            id: BookingId::fromInt($model->id),
            resourceId: ResourceId::fromInt($model->dentist_id),
            clientId: ClientId::fromInt($model->patient_id),
            timeRange: new DateTimeRange(
                new \DateTimeImmutable($model->start_time->toDateTimeString()),
                new \DateTimeImmutable($model->end_time->toDateTimeString())
            ),
            status: BookingStatus::fromString($model->status ?? BookingStatus::PENDING),
            notes: $model->reason,
            treatmentIds: $treatmentIds
        );
    }
}
