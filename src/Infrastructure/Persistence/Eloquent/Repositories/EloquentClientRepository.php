<?php

declare(strict_types=1);

namespace Booking\Infrastructure\Persistence\Eloquent\Repositories;

use App\Models\Patient;
use Booking\Domain\Entities\Client;
use Booking\Domain\Repositories\ClientRepositoryInterface;
use Booking\Domain\ValueObjects\ClientId;

final class EloquentClientRepository implements ClientRepositoryInterface
{
    public function findById(ClientId $id): ?Client
    {
        $model = Patient::find($id->value());

        return $model ? $this->toDomain($model) : null;
    }

    public function save(Client $client): void
    {
        Patient::updateOrCreate(
            ['id' => $client->id()->value()],
            [
                'name' => $client->name(),
                'email' => $client->email(),
                'phone' => $client->phone(),
                'note' => $client->notes(),
            ]
        );
    }

    public function findAll(): array
    {
        return Patient::all()
            ->map(fn (Patient $m) => $this->toDomain($m))
            ->all();
    }

    private function toDomain(Patient $model): Client
    {
        return new Client(
            id: ClientId::fromInt($model->id),
            name: $model->name,
            email: $model->email,
            phone: $model->phone,
            notes: $model->note
        );
    }
}
