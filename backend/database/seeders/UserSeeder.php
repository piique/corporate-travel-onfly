<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar um usuário comum para solicitação de viagens
        User::factory()->create([
            'name' => 'Carlos Silva',
            'email' => 'joao@teste.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        // Criar um segundo usuário comum
        User::factory()->create([
            'name' => 'Mariana Costa',
            'email' => 'mariana@teste.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        // Criar um aprovador (geralmente um gestor ou supervisor)
        User::factory()->create([
            'name' => 'Roberto Mendes',
            'email' => 'roberto@teste.com',
            'password' => Hash::make('password'),
            'role' => 'approver',
        ]);
    }
}
