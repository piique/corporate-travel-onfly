<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\NotificationCollection;

class NotificationController extends Controller
{
    use ApiResponser;

    public function index(): JsonResponse
    {
        try {
            $notifications = Notification::where('user_id', Auth::id())
                ->latest()
                ->paginate(10);

            return $this->successResponse(
                new NotificationCollection($notifications),
                'Notificações recuperadas com sucesso'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Falha ao recuperar notificações',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    public function markAsRead(int $id): JsonResponse
    {
        try {
            $notification = Notification::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$notification) {
                return $this->errorResponse(
                    'Notificação não encontrada',
                    ['id' => 'Nenhuma notificação encontrada com o ID especificado ou você não tem permissão para acessá-la'],
                    404
                );
            }

            $notification->read = true;
            $notification->save();

            return $this->successResponse(
                new NotificationResource($notification),
                'Notificação marcada como lida'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Falha ao marcar notificação como lida',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    public function markAllAsRead(): JsonResponse
    {
        try {
            $count = Notification::where('user_id', Auth::id())
                ->where('read', false)
                ->count();

            Notification::where('user_id', Auth::id())
                ->where('read', false)
                ->update(['read' => true]);

            return $this->successResponse(
                ['updated_count' => $count],
                'Todas as notificações foram marcadas como lidas'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Falha ao marcar todas as notificações como lidas',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    public function getUnreadCount(): JsonResponse
    {
        try {
            $count = Notification::where('user_id', Auth::id())
                ->where('read', false)
                ->count();

            return $this->successResponse(
                ['unread_count' => $count],
                'Contagem de notificações não lidas recuperada com sucesso'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Falha ao recuperar contagem de notificações não lidas',
                ['error' => $e->getMessage()],
                500
            );
        }
    }
}
