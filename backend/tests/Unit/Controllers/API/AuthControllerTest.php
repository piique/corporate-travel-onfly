<?php

namespace Tests\Unit\Controllers\API;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testa o registro de um novo usuário com dados válidos.
     *
     * @return void
     */
    public function test_register_with_valid_data()
    {
        $userData = [
            'name' => 'Teste Usuario',
            'email' => 'teste@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // Aqui usamos a rota exata definida no seu routes/api.php
        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => [
                    'id',
                    'name',
                    'email',
                ],
                'token',
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Teste Usuario',
            'email' => 'teste@example.com',
        ]);
    }

    /**
     * Testa o login com credenciais válidas.
     *
     * @return void
     */
    public function test_login_with_valid_credentials()
    {
        // Cria um usuário primeiro
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => Hash::make('password123'),
        ]);

        $loginData = [
            'email' => 'login@example.com',
            'password' => 'password123',
        ];

        // Aqui usamos a rota exata definida no seu routes/api.php
        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'user' => [
                    'id',
                    'name',
                    'email',
                ],
                'token',
            ]);
    }
}
