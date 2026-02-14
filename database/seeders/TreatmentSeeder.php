<?php

namespace Database\Seeders;

use App\Models\Treatment;
use Illuminate\Database\Seeder;

class TreatmentSeeder extends Seeder
{
    public function run(): void
    {
        $treatments = [
            [
                'name' => 'Brackets',
                'specialty' => 'Ortodoncia',
                'base_price' => 3999.95,
                'duration_minutes' => 45,
            ],
            [
                'name' => 'Composite',
                'specialty' => 'Prótesis',
                'base_price' => 680.00,
                'duration_minutes' => 60,
            ],
            [
                'name' => 'Exp. Maxilar',
                'specialty' => 'Cirugía',
                'base_price' => 9000.00,
                'duration_minutes' => 120,
            ],
            [
                'name' => 'Radiografía panorámica',
                'specialty' => 'Diagnosis',
                'base_price' => 50.00,
                'duration_minutes' => 10,
            ],
            [
                'name' => 'Blanqueamiento',
                'specialty' => 'General',
                'base_price' => 199.62,
                'duration_minutes' => 20,
            ],
        ];

        foreach ($treatments as $treatment) {
            Treatment::create($treatment);
        }
    }
}
