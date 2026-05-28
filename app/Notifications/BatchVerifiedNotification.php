<?php

namespace App\Notifications;

use App\Models\ProductionBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BatchVerifiedNotification extends Notification
{
    use Queueable;

    public $batch;

    public function __construct(ProductionBatch $batch)
    {
        $this->batch = $batch;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'batch_id'     => $this->batch->id,
            'batch_number' => $this->batch->batch_number,
            'product_name' => $this->batch->product->name,
            'quantity'     => $this->batch->quantity,
            'verifier_name'=> $this->batch->verifier->name,
            'message'      => "Inventory batch #{$this->batch->batch_number} for {$this->batch->product->name} (Qty: {$this->batch->quantity}) has been verified by Store Manager {$this->batch->verifier->name}.",
            'type'         => 'verification',
        ];
    }
}
