<?php

namespace App\Notifications;

use App\Models\ProductionBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BatchDeletedNotification extends Notification
{
    use Queueable;

    public $batchNumber;
    public $productName;
    public $quantity;
    public $deletedByName;
    public $reason;

    public function __construct(ProductionBatch $batch, string $deletedByName, string $reason)
    {
        $this->batchNumber   = $batch->batch_number;
        $this->productName   = $batch->product->name;
        $this->quantity      = $batch->quantity;
        $this->deletedByName = $deletedByName;
        $this->reason        = $reason;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'batch_number'    => $this->batchNumber,
            'product_name'    => $this->productName,
            'quantity'        => $this->quantity,
            'deleted_by_name' => $this->deletedByName,
            'reason'          => $this->reason,
            'message'         => "Inventory batch #{$this->batchNumber} for {$this->productName} (Qty: {$this->quantity}) has been deleted by Super Admin {$this->deletedByName}. Reason: {$this->reason}.",
            'type'            => 'deletion',
        ];
    }
}
