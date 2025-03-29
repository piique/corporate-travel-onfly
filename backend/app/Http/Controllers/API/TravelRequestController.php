<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\TravelRequest;
use App\Models\Notification;
use App\Traits\ApiResponser;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\TravelRequestResource;
use App\Http\Resources\TravelRequestCollection;
use App\Notifications\TravelRequestStatusChanged;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class TravelRequestController extends Controller
{
    use ApiResponser;

    public function index(Request $request): JsonResponse
    {
        try {
            $query = $this->buildQuery($request);

            if ($request->has('start_date') && $request->has('end_date')) {
                $startDate = $request->input('start_date');
                $endDate = $request->input('end_date');

                $query->where(function($q) use ($startDate, $endDate) {
                    $q->where('departure_date', '<=', $endDate)
                        ->where('return_date', '>=', $startDate);
                });
            } elseif ($request->has('start_date')) {
                $startDate = $request->input('start_date');
                $query->where('return_date', '>=', $startDate);
            } elseif ($request->has('end_date')) {
                $endDate = $request->input('end_date');
                $query->where('departure_date', '<=', $endDate);
            }

            $travelRequests = $query->paginate(10);

            return $this->successResponse(
                new TravelRequestCollection($travelRequests),
                'Pedidos de viagem recuperados com sucesso'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Falha ao recuperar pedidos de viagem',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'destination' => 'required|string|max:255',
                'departure_date' => 'required|date|after_or_equal:today',
                'return_date' => 'required|date|after_or_equal:departure_date',
            ]);

            $travelRequest = new TravelRequest($validatedData);
            $travelRequest->user_id = Auth::id();
            $travelRequest->status = 'requested';
            $travelRequest->save();

            return $this->successResponse(
                new TravelRequestResource($travelRequest),
                'Pedido de viagem criado com sucesso',
                201
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse(
                'Falha de validação',
                $e->errors(),
                422
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Falha ao criar pedido de viagem',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $travelRequest = $this->getTravelRequest($id);

            return $this->successResponse(
                new TravelRequestResource($travelRequest),
                'Pedido de viagem recuperado com sucesso'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse(
                'Pedido de viagem não encontrado',
                ['id' => 'Nenhum pedido de viagem encontrado com o ID especificado'],
                404
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Falha ao recuperar pedido de viagem',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Update método para atualizar um pedido de viagem com tratamento adequado de erros de autorização.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $travelRequest = TravelRequest::findOrFail($id);
            $user = Auth::user();

            // Verificação de autorização
            if ($travelRequest->user_id !== $user->id && !$user->isApprover()) {
                return $this->errorResponse(
                    'Você não está autorizado a acessar este pedido de viagem',
                    ['permission' => 'Permissões insuficientes'],
                    403
                );
            }

            // Verificação adicional - só pode atualizar pedidos no status 'requested'
            if ($travelRequest->user_id !== Auth::id() || $travelRequest->status !== 'requested') {
                return $this->errorResponse(
                    'Você não está autorizado a atualizar este pedido de viagem',
                    ['permission' => 'Permissões insuficientes ou o pedido não pode ser atualizado no estado atual'],
                    403
                );
            }

            $validatedData = $request->validate([
                'destination' => 'sometimes|string|max:255',
                'departure_date' => 'sometimes|date|after_or_equal:today',
                'return_date' => 'sometimes|date|after_or_equal:departure_date',
            ]);

            $travelRequest->update($validatedData);

            return $this->successResponse(
                new TravelRequestResource($travelRequest),
                'Pedido de viagem atualizado com sucesso'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse(
                'Falha de validação',
                $e->errors(),
                422
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse(
                'Pedido de viagem não encontrado',
                ['id' => 'Nenhum pedido de viagem encontrado com o ID especificado'],
                404
            );
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            // Captura a exceção de autorização e retorna um status 403 adequado
            return $this->errorResponse(
                'Acesso não autorizado',
                ['permission' => $e->getMessage()],
                403
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Falha ao atualizar pedido de viagem',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        try {
            $travelRequest = $this->getTravelRequest($id);

            if (!Auth::user()->isApprover() || $travelRequest->user_id === Auth::id()) {
                return $this->errorResponse(
                    'Você não está autorizado a atualizar o status deste pedido de viagem',
                    ['permission' => 'Permissões insuficientes'],
                    403
                );
            }

            $validatedData = $request->validate([
                'status' => 'required|string|in:approved,canceled',
            ]);

            if ($validatedData['status'] === 'canceled' && $travelRequest->status === 'approved') {
                $today = now()->startOfDay();
                $departureDate = \Carbon\Carbon::parse($travelRequest->departure_date)->startOfDay();

                if ($today->diffInDays($departureDate) < 3) {
                    return $this->errorResponse(
                        'Violação de regra de negócio',
                        ['date' => 'Não é possível cancelar um pedido de viagem com menos de 3 dias de antecedência ou com viagem já iniciada'],
                        422
                    );
                }
            }

            $oldStatus = $travelRequest->status;
            $travelRequest->status = $validatedData['status'];
            $travelRequest->save();

            $notification = new Notification([
                'user_id' => $travelRequest->user_id,
                'travel_request_id' => $travelRequest->id,
                'message' => "Seu pedido de viagem para {$travelRequest->destination} foi {$validatedData['status']}.",
                'read' => false,
            ]);
            $notification->save();

            $user = \App\Models\User::findOrFail($travelRequest->user_id);

            $user->notify(new TravelRequestStatusChanged($travelRequest, $oldStatus));

            Log::info('Notificação enviada com sucesso', [
                'user_id' => $user->id,
                'travel_request_id' => $travelRequest->id,
                'old_status' => $oldStatus,
                'new_status' => $validatedData['status']
            ]);

            return $this->successResponse(
                new TravelRequestResource($travelRequest),
                "Status do pedido de viagem atualizado para {$validatedData['status']}"
            );
        } catch (ValidationException $e) {
            return $this->errorResponse(
                'Falha de validação',
                $e->errors(),
                422
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse(
                'Pedido de viagem não encontrado',
                ['id' => 'Nenhum pedido de viagem encontrado com o ID especificado'],
                404
            );
        }catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return $this->errorResponse(
                'Acesso não autorizado',
                ['permission' => $e->getMessage()],
                403
            );
        } catch (\Exception $e) {
            Log::error('Falha ao enviar notificação', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse(
                'Falha ao atualizar status do pedido de viagem',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $travelRequest = $this->getTravelRequest($id);

            if ($travelRequest->user_id !== Auth::id() || $travelRequest->status === 'approved') {
                return $this->errorResponse(
                    'Você não está autorizado a excluir este pedido de viagem',
                    ['permission' => 'Permissões insuficientes ou o pedido não pode ser excluído no estado atual'],
                    403
                );
            }

            $travelRequest->delete();

            return $this->successResponse(
                null,
                'Pedido de viagem excluído com sucesso'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse(
                'Pedido de viagem não encontrado',
                ['id' => 'Nenhum pedido de viagem encontrado com o ID especificado'],
                404
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Falha ao excluir pedido de viagem',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * @throws AuthorizationException
     */
    private function getTravelRequest(int $id): TravelRequest
    {
        $user = Auth::user();
        $travelRequest = TravelRequest::findOrFail($id);

        if ($travelRequest->user_id !== $user->id && !$user->isApprover()) {
            throw new \Illuminate\Auth\Access\AuthorizationException(
                'Você não está autorizado a acessar este pedido de viagem'
            );
        }

        return $travelRequest;
    }

    private function buildQuery(Request $request): Builder
    {
        $user = Auth::user();
        $query = TravelRequest::query();

        if (!$user->isApprover()) {
            $query->where('user_id', $user->id);
        }

        if ($request->has('status')) {
            $query->byStatus($request->status);
        }

        if ($request->has('destination')) {
            $query->byDestination($request->destination);
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->byDateRange($request->start_date, $request->end_date);
        }

        $query->latest();

        return $query;
    }

    public function getStats(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $query = TravelRequest::query();

            if (!$user->isApprover()) {
                $query->where('user_id', $user->id);
            }

            $totalRequests = $query->count();
            $approvedRequests = (clone $query)->byStatus('approved')->count();
            $canceledRequests = (clone $query)->byStatus('canceled')->count();
            $pendingRequests = (clone $query)->byStatus('requested')->count();

            return $this->successResponse([
                'total' => $totalRequests,
                'approved' => $approvedRequests,
                'canceled' => $canceledRequests,
                'pending' => $pendingRequests,
            ], 'Estatísticas de pedidos de viagem recuperadas com sucesso');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Falha ao recuperar estatísticas de pedidos de viagem',
                ['error' => $e->getMessage()],
                500
            );
        }
    }
}
