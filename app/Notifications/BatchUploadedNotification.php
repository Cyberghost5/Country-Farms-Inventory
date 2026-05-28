<?php

namespace App\Notifications;

use App\Models\ProductionBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BatchUploadedNotification extends Notification
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
            'uploader_name'=> $this->batch->uploader->name,
            'message'      => "New inventory batch #{$this->batch->batch_number} for {$this->batch->product->name} (Qty: {$this->batch->quantity}) has been uploaded by {$this->batch->uploader->name} and is awaiting verification.",
            'type'         => 'upload',
        ];
    }
}
