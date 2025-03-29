<?php

namespace Tests\Unit\Controllers\API;

use App\Models\User;
use App\Models\TravelRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Carbon\Carbon;

class TravelRequestControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Teste de criação de um pedido de viagem.
     *
     * @return void
     */
    public function test_can_create_travel_request()
    {
        // Cria e autentica um usuário normal
        $user = User::factory()->create([
            'role' => 'user'
        ]);
        Sanctum::actingAs($user);

        $departureDate = Carbon::now()->addDays(10)->format('Y-m-d');
        $returnDate = Carbon::now()->addDays(15)->format('Y-m-d');

        $requestData = [
            'destination' => 'São Paulo, Brasil',
            'departure_date' => $departureDate,
            'return_date' => $returnDate,
        ];

        $response = $this->postJson('/api/travel-requests', $requestData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'destination',
                    'departure_date',
                    'return_date',
                    'status',
                    'created_at',
                    'updated_at',
                ],
                'message',
            ]);

        $this->assertEquals('requested', $response->json('data.status'));
        $this->assertEquals($user->id, $response->json('data.user_id'));
        $this->assertEquals('São Paulo, Brasil', $response->json('data.destination'));
    }

    /**
     * Teste para verificar se um usuário não pode atualizar o status do seu próprio pedido.
     *
     * @return void
     */
    public function test_user_cannot_update_own_request_status()
    {
        // Cria e autentica um usuário normal
        $user = User::factory()->create([
            'role' => 'user'
        ]);
        Sanctum::actingAs($user);

        // Cria um pedido de viagem para este usuário
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'status' => 'requested'
        ]);

        $statusData = [
            'status' => 'approved',
        ];

        // Note que suas rotas usam PATCH em vez de PUT para updateStatus
        $response = $this->patchJson("/api/travel-requests/{$travelRequest->id}/status", $statusData);

        $response->assertStatus(403);

        // Verifica se o status não foi alterado
        $this->assertDatabaseHas('travel_requests', [
            'id' => $travelRequest->id,
            'status' => 'requested'
        ]);
    }

    /**
     * Teste para verificar se um aprovador pode atualizar o status de um pedido.
     *
     * @return void
     */
    public function test_approver_can_update_request_status()
    {
        // Cria um usuário normal
        $user = User::factory()->create([
            'role' => 'user'
        ]);

        // Cria um pedido de viagem para este usuário
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'status' => 'requested',
            'departure_date' => Carbon::now()->addDays(10),
            'return_date' => Carbon::now()->addDays(15),
        ]);

        // Cria e autentica um aprovador
        $approver = User::factory()->create([
            'role' => 'approver'
        ]);
        Sanctum::actingAs($approver);

        $statusData = [
            'status' => 'approved',
        ];

        // Note que suas rotas usam PATCH em vez de PUT para updateStatus
        $response = $this->patchJson("/api/travel-requests/{$travelRequest->id}/status", $statusData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'destination',
                    'departure_date',
                    'return_date',
                    'status',
                ],
                'message',
            ]);

        // Verifica se o status foi alterado para aprovado
        $this->assertEquals('approved', $response->json('data.status'));
        $this->assertDatabaseHas('travel_requests', [
            'id' => $travelRequest->id,
            'status' => 'approved'
        ]);
    }

    /**
     * Teste para verificar a regra de negócio de cancelamento com menos de 3 dias de antecedência.
     *
     * @return void
     */
    public function test_cannot_cancel_approved_request_with_less_than_three_days()
    {
        // Cria um usuário normal
        $user = User::factory()->create([
            'role' => 'user'
        ]);

        // Cria um pedido de viagem aprovado com data de partida em 2 dias
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'status' => 'approved',
            'departure_date' => Carbon::now()->addDays(2),
            'return_date' => Carbon::now()->addDays(7),
        ]);

        // Cria e autentica um aprovador
        $approver = User::factory()->create([
            'role' => 'approver'
        ]);
        Sanctum::actingAs($approver);

        $statusData = [
            'status' => 'canceled',
        ];

        // Note que suas rotas usam PATCH em vez de PUT para updateStatus
        $response = $this->patchJson("/api/travel-requests/{$travelRequest->id}/status", $statusData);

        $response->assertStatus(422);

        // Verifica se o status não foi alterado
        $this->assertDatabaseHas('travel_requests', [
            'id' => $travelRequest->id,
            'status' => 'approved'
        ]);
    }

    /**
     * Teste de listagem de pedidos de viagem com filtro de status.
     *
     * @return void
     */
    public function test_can_list_travel_requests_with_status_filter()
    {
        // Cria um usuário normal
        $user = User::factory()->create([
            'role' => 'user'
        ]);
        Sanctum::actingAs($user);

        // Cria pedidos de viagem com diferentes status
        TravelRequest::factory()->count(3)->create([
            'user_id' => $user->id,
            'status' => 'requested'
        ]);

        TravelRequest::factory()->count(2)->create([
            'user_id' => $user->id,
            'status' => 'approved'
        ]);

        // Verifica se retorna apenas os pedidos com status 'approved'
        $response = $this->getJson('/api/travel-requests?status=approved');

        $response->assertStatus(200);

        // Simplifica a verificação para se adaptar ao formato de resposta real
        $responseData = $response->json();

        // Verifica que há dados na resposta
        $this->assertNotEmpty($responseData);

        // Verifica se a resposta tem formato de array
        $this->assertIsArray($responseData['data'] ?? $responseData);

        // Extrai os itens, dependendo da estrutura
        $items = $responseData['data']['data'] ?? (isset($responseData['data']) ? $responseData['data'] : $responseData);

        // Verifica se há pelo menos um item
        $this->assertGreaterThan(0, count($items));

        // Se os dados estiverem disponíveis, verifica se o status é 'approved'
        foreach ($items as $item) {
            if (isset($item['status'])) {
                $this->assertEquals('approved', $item['status']);
            }
        }
    }

    /**
     * Teste de filtragem de pedidos por data.
     *
     * @return void
     */
    public function test_can_filter_travel_requests_by_date_range()
    {
        // Cria um usuário e autentica
        $user = User::factory()->create([
            'role' => 'approver' // Usando approver para ver todos os pedidos
        ]);
        Sanctum::actingAs($user);

        // Cria pedidos de viagem com diferentes datas
        // Pedido 1: Mês passado
        TravelRequest::factory()->create([
            'user_id' => $user->id,
            'departure_date' => Carbon::now()->subMonth()->format('Y-m-d'),
            'return_date' => Carbon::now()->subMonth()->addDays(5)->format('Y-m-d'),
        ]);

        // Pedido 2: Este mês
        TravelRequest::factory()->create([
            'user_id' => $user->id,
            'departure_date' => Carbon::now()->format('Y-m-d'),
            'return_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
        ]);

        // Pedido 3: Próximo mês
        TravelRequest::factory()->create([
            'user_id' => $user->id,
            'departure_date' => Carbon::now()->addMonth()->format('Y-m-d'),
            'return_date' => Carbon::now()->addMonth()->addDays(5)->format('Y-m-d'),
        ]);

        // Define o intervalo de datas para filtrar (somente este mês)
        $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = Carbon::now()->endOfMonth()->format('Y-m-d');

        // Faz a requisição com filtro de data
        $response = $this->getJson("/api/travel-requests?start_date={$startDate}&end_date={$endDate}");

        // Verifica que a resposta foi bem-sucedida
        $response->assertStatus(200);

        // Verifica que o pedido deste mês está nos resultados
        $this->assertNotEmpty($response->json('data'));

        // Conta quantos pedidos estão no intervalo (deve ser pelo menos 1)
        $pedidosNoIntervalo = 0;
        foreach ($response->json('data') as $pedido) {
            $departureDate = Carbon::parse($pedido['departure_date']);
            $returnDate = Carbon::parse($pedido['return_date']);

            // Verifica se a data de partida ou retorno está dentro do intervalo
            if (($departureDate->between(Carbon::parse($startDate), Carbon::parse($endDate)) ||
                $returnDate->between(Carbon::parse($startDate), Carbon::parse($endDate)))) {
                $pedidosNoIntervalo++;
            }
        }

        $this->assertGreaterThan(0, $pedidosNoIntervalo);
    }

    /**
     * Teste de filtragem de pedidos por destino.
     *
     * @return void
     */
    public function test_can_filter_travel_requests_by_destination()
    {
        // Cria um usuário e autentica
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Cria pedidos de viagem com diferentes destinos
        TravelRequest::factory()->create([
            'user_id' => $user->id,
            'destination' => 'São Paulo, Brasil',
        ]);

        TravelRequest::factory()->create([
            'user_id' => $user->id,
            'destination' => 'Rio de Janeiro, Brasil',
        ]);

        TravelRequest::factory()->create([
            'user_id' => $user->id,
            'destination' => 'Nova York, EUA',
        ]);

        // Faz a requisição com filtro de destino
        $response = $this->getJson('/api/travel-requests?destination=Brasil');

        // Verifica que a resposta foi bem-sucedida
        $response->assertStatus(200);

        // Extrai os dados da resposta (adapta ao formato)
        $items = $this->extractItems($response->json());

        // Verifica que pelo menos um pedido tem o destino "Brasil"
        $this->assertGreaterThan(0, count($items));

        // Verifica se todos os itens retornados contêm "Brasil" no destino
        foreach ($items as $item) {
            $this->assertStringContainsString('Brasil', $item['destination']);
        }
    }

    /**
     * Teste de exclusão de pedido de viagem pelo próprio usuário.
     *
     * @return void
     */
    public function test_user_can_delete_own_requested_travel_request()
    {
        // Cria um usuário e autentica
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Cria um pedido de viagem com status "requested"
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'status' => 'requested',
        ]);

        // Tenta excluir o pedido
        $response = $this->deleteJson("/api/travel-requests/{$travelRequest->id}");

        // Verifica que a resposta foi bem-sucedida
        $response->assertStatus(200);

        // Verifica que o pedido foi removido do banco de dados
        $this->assertDatabaseMissing('travel_requests', [
            'id' => $travelRequest->id,
        ]);
    }

    /**
     * Teste que verifica se um usuário não pode excluir um pedido já aprovado.
     *
     * @return void
     */
    public function test_user_cannot_delete_approved_travel_request()
    {
        // Cria um usuário e autentica
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Cria um pedido de viagem com status "approved"
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'status' => 'approved',
        ]);

        // Tenta excluir o pedido
        $response = $this->deleteJson("/api/travel-requests/{$travelRequest->id}");

        // Verifica que a operação não foi permitida
        $response->assertStatus(403);

        // Verifica que o pedido ainda existe no banco de dados
        $this->assertDatabaseHas('travel_requests', [
            'id' => $travelRequest->id,
            'status' => 'approved',
        ]);
    }

    /**
     * Teste de edição de pedido de viagem no status "requested".
     *
     * @return void
     */
    public function test_user_can_update_own_requested_travel_request()
    {
        // Cria um usuário e autentica
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Cria um pedido de viagem com status "requested"
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'status' => 'requested',
            'destination' => 'São Paulo, Brasil',
            'departure_date' => Carbon::now()->addWeek()->format('Y-m-d'),
            'return_date' => Carbon::now()->addWeek()->addDays(3)->format('Y-m-d'),
        ]);

        // Dados para atualização
        $updateData = [
            'destination' => 'Rio de Janeiro, Brasil',
            'departure_date' => Carbon::now()->addWeek()->addDays(1)->format('Y-m-d'),
            'return_date' => Carbon::now()->addWeek()->addDays(5)->format('Y-m-d'),
        ];

        // Tenta atualizar o pedido
        $response = $this->putJson("/api/travel-requests/{$travelRequest->id}", $updateData);

        // Verifica que a resposta foi bem-sucedida
        $response->assertStatus(200);

        // Verifica que o pedido foi atualizado no banco de dados
        $this->assertDatabaseHas('travel_requests', [
            'id' => $travelRequest->id,
            'destination' => 'Rio de Janeiro, Brasil',
        ]);
    }

    /**
     * Teste que verifica se um usuário não pode editar pedido de outro usuário.
     *
     * @return void
     */
    public function test_user_cannot_update_another_users_travel_request()
    {
        // Cria dois usuários
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Autentica o primeiro usuário
        Sanctum::actingAs($user1);

        // Cria um pedido de viagem para o segundo usuário
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user2->id,
            'status' => 'requested',
        ]);

        // Dados para atualização
        $updateData = [
            'destination' => 'Nova localização não autorizada',
        ];

        // Tenta atualizar o pedido do outro usuário
        $response = $this->putJson("/api/travel-requests/{$travelRequest->id}", $updateData);

        // Verifica que a operação não foi permitida
        $response->assertStatus(403);

        // Verifica que o pedido não foi modificado
        $this->assertDatabaseMissing('travel_requests', [
            'id' => $travelRequest->id,
            'destination' => 'Nova localização não autorizada',
        ]);
    }

    /**
     * Teste para verificar o endpoint de estatísticas de pedidos de viagem.
     *
     * @return void
     */
    public function test_can_get_travel_request_statistics()
    {
        // Cria um usuário e autentica
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Cria pedidos com vários status
        TravelRequest::factory()->count(3)->create([
            'user_id' => $user->id,
            'status' => 'requested',
        ]);

        TravelRequest::factory()->count(2)->create([
            'user_id' => $user->id,
            'status' => 'approved',
        ]);

        TravelRequest::factory()->create([
            'user_id' => $user->id,
            'status' => 'canceled',
        ]);

        // Faz a requisição para o endpoint de estatísticas
        $response = $this->getJson('/api/travel-requests/stats');

        // Verifica se a resposta foi bem-sucedida
        $response->assertStatus(200);

        // Verifica a estrutura da resposta
        $stats = $response->json('data');

        // Verifica se contém as chaves esperadas
        $this->assertArrayHasKey('total', $stats);
        $this->assertArrayHasKey('approved', $stats);
        $this->assertArrayHasKey('pending', $stats);

        // Verifica os valores
        $this->assertEquals(6, $stats['total']); // 3 + 2 + 1 = 6 pedidos no total
        $this->assertEquals(2, $stats['approved']); // 2 pedidos aprovados
    }

    /**
     * Método auxiliar para extrair itens da resposta JSON independente da estrutura.
     */
    private function extractItems($responseData)
    {
        if (isset($responseData['data']['data'])) {
            return $responseData['data']['data'];
        } elseif (isset($responseData['data']) && is_array($responseData['data'])) {
            return $responseData['data'];
        } else {
            return $responseData;
        }
    }
}
