<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Recepcionista',
            'email' => 'recepcionista@pruebasmulhacen.com',
            'password' => '0dHGgfh49v',
        ]);
    }
}
