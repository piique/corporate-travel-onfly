<?php

namespace App\Notifications;

use App\Models\TravelRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TravelRequestStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    protected TravelRequest $travelRequest;
    protected string $oldStatus;
    protected string $newStatus;
    protected array $statusMap = [
        'approved' => 'aprovado',
        'canceled' => 'cancelado',
        'requested' => 'pendente',
    ];


    public function __construct(TravelRequest $travelRequest, string $oldStatus)
    {
        $this->travelRequest = $travelRequest;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $this->statusMap[$oldStatus];
    }

    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $status = $this->travelRequest->status;
        $subject = "Pedido de Viagem {$status}";
        $startDate = $this->travelRequest->departure_date->format('d/m/Y');
        $endDate = $this->travelRequest->return_date->format('d/m/Y');

        return (new MailMessage)
            ->subject($subject)
            ->greeting("Olá {$notifiable->name}!")
            ->line("Seu pedido de viagem para {$this->travelRequest->destination} foi {$this->newStatus}.")
            ->line("Data de partida: {$startDate}")
            ->line("Data de retorno: {$endDate}")
            ->action('Ver Pedido de Viagem', url('/travel-requests/' . $this->travelRequest->id))
            ->line('Obrigado por usar nossa aplicação!');
    }

    public function toArray(mixed $notifiable): array
    {
        return [
            'travel_request_id' => $this->travelRequest->id,
            'old_status' => $this->oldStatus,
            'new_status' => $this->travelRequest->status,
            'message' => "Seu pedido de viagem para {$this->travelRequest->destination} foi {$this->newStatus}."
        ];
    }
}
