<?php

namespace App\Notifications;

use App\Models\Dispatch;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DispatchCompletedNotification extends Notification
{
    use Queueable;

    public $dispatch;

    public function __construct(Dispatch $dispatch)
    {
        $this->dispatch = $dispatch;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $itemsCount = $this->dispatch->items()->count();
        $formattedAmount = number_format($this->dispatch->total_amount, 2);

        return [
            'dispatch_id'      => $this->dispatch->id,
            'dispatch_number'  => $this->dispatch->dispatch_number,
            'distributor_name' => $this->dispatch->distributor->name,
            'items_count'      => $itemsCount,
            'total_amount'     => $this->dispatch->total_amount,
            'message'          => "Dispatch #{$this->dispatch->dispatch_number} containing {$itemsCount} item(s) (Total: ₦{$formattedAmount}) has been completed and sent to {$this->dispatch->distributor->name}.",
            'type'             => 'dispatch',
        ];
    }
}
