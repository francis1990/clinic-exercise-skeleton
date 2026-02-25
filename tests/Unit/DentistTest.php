<?php

namespace Tests\Unit;

use App\Models\Dentist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DentistTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_name_accessor_returns_name_and_last_name(): void
    {
        $dentist = Dentist::create([
            'name' => 'Roberto',
            'last_name' => 'Garcia Lopez',
            'specialties' => ['Ortodoncia'],
        ]);

        $this->assertSame('Roberto Garcia Lopez', $dentist->full_name);
    }

    public function test_specialties_is_cast_to_array(): void
    {
        $dentist = Dentist::create([
            'name' => 'Ana',
            'last_name' => 'Martinez',
            'specialties' => ['Ortodoncia', 'Endodoncia'],
        ]);

        $fresh = Dentist::find($dentist->id);

        $this->assertIsArray($fresh->specialties);
        $this->assertSame(['Ortodoncia', 'Endodoncia'], $fresh->specialties);
    }

    public function test_has_many_appointments_relationship(): void
    {
        $dentist = new Dentist;

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $dentist->appointments());
    }
}
