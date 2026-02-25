<?php

declare(strict_types=1);

namespace Booking\Infrastructure\Persistence\Eloquent\Repositories;

use App\Models\Dentist;
use Booking\Domain\Entities\Resource;
use Booking\Domain\Repositories\ResourceRepositoryInterface;
use Booking\Domain\ValueObjects\ResourceId;

final class EloquentResourceRepository implements ResourceRepositoryInterface
{
    public function findById(ResourceId $id): ?Resource
    {
        $model = Dentist::find($id->value());

        return $model ? $this->toDomain($model) : null;
    }

    public function findAll(): array
    {
        return Dentist::all()
            ->map(fn (Dentist $m) => $this->toDomain($m))
            ->all();
    }

    private function toDomain(Dentist $model): Resource
    {
        return new Resource(
            id: ResourceId::fromInt($model->id),
            name: $model->name,
            lastName: $model->last_name,
            specialties: $model->specialties ?? []
        );
    }
}
