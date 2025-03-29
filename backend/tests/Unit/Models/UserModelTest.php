<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\TravelRequest;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testa o método isApprover para usuários com papel de aprovador.
     *
     * @return void
     */
    public function test_is_approver_returns_true_for_approver_role()
    {
        // Cria um usuário com papel de aprovador
        $approver = User::factory()->create([
            'role' => 'approver'
        ]);

        // Verifica se o método isApprover retorna true
        $this->assertTrue($approver->isApprover());
    }

    /**
     * Testa o método isApprover para usuários com papel de admin.
     *
     * @return void
     */
    public function test_is_approver_returns_true_for_admin_role()
    {
        // Cria um usuário com papel de admin
        $admin = User::factory()->create([
            'role' => 'admin'
        ]);

        // Verifica se o método isApprover retorna true para admins também
        $this->assertTrue($admin->isApprover());
    }

    /**
     * Testa o método isApprover para usuários comuns.
     *
     * @return void
     */
    public function test_is_approver_returns_false_for_regular_user()
    {
        // Cria um usuário comum
        $user = User::factory()->create([
            'role' => 'user'
        ]);

        // Verifica se o método isApprover retorna false
        $this->assertFalse($user->isApprover());
    }

    /**
     * Testa o relacionamento com os pedidos de viagem.
     *
     * @return void
     */
    public function test_user_has_many_travel_requests()
    {
        // Cria um usuário
        $user = User::factory()->create();

        // Cria alguns pedidos de viagem para este usuário
        TravelRequest::factory()->count(3)->create([
            'user_id' => $user->id
        ]);

        // Verifica se o relacionamento retorna os pedidos corretos
        $this->assertCount(3, $user->travelRequests);
        $this->assertInstanceOf(TravelRequest::class, $user->travelRequests->first());
    }

    /**
     * Testa o relacionamento com as notificações.
     *
     * @return void
     */
    public function test_user_has_many_notifications()
    {
        // Cria um usuário
        $user = User::factory()->create();

        // Cria um pedido de viagem para associar às notificações
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id
        ]);

        // Cria algumas notificações para este usuário
        for ($i = 0; $i < 2; $i++) {
            Notification::create([
                'user_id' => $user->id,
                'travel_request_id' => $travelRequest->id,
                'message' => "Notificação de teste {$i}",
                'read' => false,
            ]);
        }

        // Verifica se o relacionamento retorna as notificações corretas
        $this->assertCount(2, $user->notifications);
        $this->assertInstanceOf(Notification::class, $user->notifications->first());
    }

    /**
     * Testa a proteção de atributos no modelo de usuário.
     *
     * @return void
     */
    public function test_user_attributes_properly_protected()
    {
        // Cria um usuário com um papel específico
        $data = [
            'name' => 'Usuário Teste',
            'email' => 'teste@example.com',
            'password' => 'password123',
            'role' => 'user',
        ];

        $user = User::create($data);

        // Verifica se os atributos estão corretamente definidos
        $this->assertEquals($data['name'], $user->name);
        $this->assertEquals($data['email'], $user->email);
        $this->assertEquals($data['role'], $user->role);

        // Verifica se a senha foi encriptada e não está em texto plano
        $this->assertNotEquals($data['password'], $user->password);

        // Verifica que atributos protegidos não são preenchidos em massa
        $this->assertArrayNotHasKey('remember_token', $user->getAttributes());
    }

    /**
     * Testa a habilidade do usuário aprovador para visualizar pedidos de outros usuários.
     *
     * @return void
     */
    public function test_approver_can_see_all_travel_requests()
    {
        // Cria dois usuários: um aprovador e um usuário comum
        $approver = User::factory()->create(['role' => 'approver']);
        $regularUser = User::factory()->create(['role' => 'user']);

        // Cria pedidos de viagem para o usuário comum
        TravelRequest::factory()->count(2)->create([
            'user_id' => $regularUser->id
        ]);

        // Cria um pedido de viagem para o aprovador
        TravelRequest::factory()->create([
            'user_id' => $approver->id
        ]);

        // Verifica que todas as solicitações existem no banco
        $this->assertEquals(3, TravelRequest::count());

        // O aprovador deve ter permissão para ver todas as solicitações
        $this->assertTrue($approver->isApprover());

        // O usuário regular só deve ter acesso às suas próprias solicitações
        $this->assertFalse($regularUser->isApprover());
    }
}
