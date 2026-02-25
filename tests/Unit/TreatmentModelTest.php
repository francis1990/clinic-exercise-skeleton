<?php

namespace Tests\Unit;

use App\Models\Treatment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TreatmentModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_base_price_is_cast_to_decimal(): void
    {
        $treatment = Treatment::create([
            'name' => 'Limpieza',
            'specialty' => 'General',
            'base_price' => 49.9,
            'duration_minutes' => 30,
        ]);

        $fresh = Treatment::find($treatment->id);

        $this->assertSame('49.90', $fresh->base_price);
    }

    public function test_duration_minutes_is_cast_to_integer(): void
    {
        $treatment = Treatment::create([
            'name' => 'Limpieza',
            'specialty' => 'General',
            'base_price' => 50.00,
            'duration_minutes' => 30,
        ]);

        $fresh = Treatment::find($treatment->id);

        $this->assertIsInt($fresh->duration_minutes);
    }

    public function test_belongs_to_many_appointments_relationship(): void
    {
        $treatment = new Treatment;

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $treatment->appointments());
    }
}
