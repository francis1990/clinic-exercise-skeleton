<?php

namespace Database\Seeders;

use App\Models\Dentist;
use Illuminate\Database\Seeder;

class DentistSeeder extends Seeder
{
    public function run(): void
    {
        $dentists = [
            [
                'name' => 'Roberto',
                'last_name' => 'García López',
                'specialties' => ['Ortodoncia'],
            ],
            [
                'name' => 'Antonio',
                'last_name' => 'Sánchez Castro',
                'specialties' => ['Ortodoncia', 'Prótesis', 'Diagnosis'],
            ],
            [
                'name' => 'Miguel',
                'last_name' => 'Díaz Romero',
                'specialties' => ['Cirugía', 'General'],
            ],
            [
                'name' => 'Juan',
                'last_name' => 'Torres Navarro',
                'specialties' => ['Ortodoncia', 'Prótesis', 'Cirugía', 'General', 'Diagnosis'],
            ],
        ];

        foreach ($dentists as $dentist) {
            Dentist::create($dentist);
        }
    }
}
