<?php

namespace App\Notifications;

use App\Models\Warehouse\Inventory;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification
{
    use Queueable;

    public function __construct(public Inventory $inventory) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $item = $this->inventory->item;
        $location = $this->inventory->location;
        $gap = $this->inventory->min_stock - $this->inventory->qty;

        return [
            'title'    => '⚠️ Stok Menipis',
            'message'  => ($item->nama ?? 'Item') .
                          " di " . ($location->nama ?? '-') .
                          " tersisa " . number_format($this->inventory->qty, 2) .
                          " (min: " . number_format($this->inventory->min_stock, 2) . ")",
            'item'     => $item->nama ?? '-',
            'item_kode'=> $item->kode ?? '-',
            'qty'      => (float) $this->inventory->qty,
            'min_stock'=> (float) $this->inventory->min_stock,
            'gap'      => (float) $gap,
            'location' => $location->nama ?? '-',
            'url'      => route('warehouse.low-stocks.index'),
        ];
    }
}