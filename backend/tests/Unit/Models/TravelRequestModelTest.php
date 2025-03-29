<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\TravelRequest;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class TravelRequestModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testa o relacionamento com o usuário.
     *
     * @return void
     */
    public function test_travel_request_belongs_to_user()
    {
        // Cria um usuário
        $user = User::factory()->create();

        // Cria um pedido de viagem para este usuário
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id
        ]);

        // Verifica se o relacionamento retorna o usuário correto
        $this->assertEquals($user->id, $travelRequest->user->id);
        $this->assertInstanceOf(User::class, $travelRequest->user);
    }

    /**
     * Testa o relacionamento com notificações.
     *
     * @return void
     */
    public function test_travel_request_has_many_notifications()
    {
        // Cria um usuário
        $user = User::factory()->create();

        // Cria um pedido de viagem
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id
        ]);

        // Cria algumas notificações para este pedido
        Notification::create([
            'user_id' => $user->id,
            'travel_request_id' => $travelRequest->id,
            'message' => 'Notificação de teste 1',
            'read' => false,
        ]);

        Notification::create([
            'user_id' => $user->id,
            'travel_request_id' => $travelRequest->id,
            'message' => 'Notificação de teste 2',
            'read' => true,
        ]);

        // Verifica se o relacionamento retorna as notificações corretas
        $this->assertCount(2, $travelRequest->notifications);
        $this->assertInstanceOf(Notification::class, $travelRequest->notifications->first());
    }

    /**
     * Testa o escopo para filtrar por status.
     *
     * @return void
     */
    public function test_scope_by_status()
    {
        // Cria pedidos com diferentes status
        TravelRequest::factory()->create(['status' => 'requested']);
        TravelRequest::factory()->create(['status' => 'approved']);
        TravelRequest::factory()->create(['status' => 'approved']);
        TravelRequest::factory()->create(['status' => 'canceled']);

        // Utiliza o escopo para filtrar
        $approvedRequests = TravelRequest::byStatus('approved')->get();

        // Verifica se retornou apenas os pedidos com status 'approved'
        $this->assertCount(2, $approvedRequests);
        foreach ($approvedRequests as $request) {
            $this->assertEquals('approved', $request->status);
        }
    }

    /**
     * Testa o escopo para filtrar por intervalo de datas.
     *
     * @return void
     */
    public function test_scope_by_date_range()
    {
        // Datas para os testes
        $startDate = Carbon::now()->addDays(10)->format('Y-m-d');
        $endDate = Carbon::now()->addDays(20)->format('Y-m-d');

        // Pedido dentro do intervalo (partida e retorno dentro)
        TravelRequest::factory()->create([
            'departure_date' => Carbon::now()->addDays(12),
            'return_date' => Carbon::now()->addDays(15),
        ]);

        // Pedido que cruza o início do intervalo (partida fora, retorno dentro)
        TravelRequest::factory()->create([
            'departure_date' => Carbon::now()->addDays(8),
            'return_date' => Carbon::now()->addDays(12),
        ]);

        // Pedido que cruza o fim do intervalo (partida dentro, retorno fora)
        TravelRequest::factory()->create([
            'departure_date' => Carbon::now()->addDays(18),
            'return_date' => Carbon::now()->addDays(22),
        ]);

        // Pedido totalmente fora do intervalo
        TravelRequest::factory()->create([
            'departure_date' => Carbon::now()->addDays(5),
            'return_date' => Carbon::now()->addDays(8),
        ]);

        // Utiliza o escopo para filtrar
        $filteredRequests = TravelRequest::byDateRange($startDate, $endDate)->get();

        // Deveria retornar 3 pedidos (os que têm alguma sobreposição com o intervalo)
        $this->assertCount(3, $filteredRequests);
    }

    /**
     * Testa o escopo para filtrar por destino.
     *
     * @return void
     */
    public function test_scope_by_destination()
    {
        // Cria pedidos com diferentes destinos
        TravelRequest::factory()->create(['destination' => 'São Paulo, Brasil']);
        TravelRequest::factory()->create(['destination' => 'Rio de Janeiro, Brasil']);
        TravelRequest::factory()->create(['destination' => 'Nova York, EUA']);
        TravelRequest::factory()->create(['destination' => 'Paris, França']);

        // Utiliza o escopo para filtrar
        $brazilRequests = TravelRequest::byDestination('Brasil')->get();

        // Verifica se retornou apenas os pedidos com destino contendo 'Brasil'
        $this->assertCount(2, $brazilRequests);
        foreach ($brazilRequests as $request) {
            $this->assertStringContainsString('Brasil', $request->destination);
        }
    }

    /**
     * Testa as datas como instâncias de Carbon.
     *
     * @return void
     */
    public function test_dates_are_carbon_instances()
    {
        // Cria um pedido de viagem
        $travelRequest = TravelRequest::factory()->create([
            'departure_date' => '2023-12-01',
            'return_date' => '2023-12-10',
        ]);

        // Verifica se as datas são instâncias de Carbon
        $this->assertInstanceOf(Carbon::class, $travelRequest->departure_date);
        $this->assertInstanceOf(Carbon::class, $travelRequest->return_date);

        // Verifica se as datas foram convertidas corretamente
        $this->assertEquals('2023-12-01', $travelRequest->departure_date->format('Y-m-d'));
        $this->assertEquals('2023-12-10', $travelRequest->return_date->format('Y-m-d'));
    }
}
