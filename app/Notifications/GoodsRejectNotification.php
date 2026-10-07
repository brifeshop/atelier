<?php

namespace App\Notifications;

use App\Models\Purchasing\GoodsReceipt;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class GoodsRejectNotification extends Notification
{
    use Queueable;

    public function __construct(
        public GoodsReceipt $goodsReceipt,
        public array $rejects
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'gr_number' => $this->goodsReceipt->gr_number,
            'gr_id' => $this->goodsReceipt->id,
            'supplier' => $this->goodsReceipt->supplier->nama ?? '-',
            'total_rejected' => collect($this->rejects)->sum('qty_rejected'),
            'items' => $this->rejects,
            'message' => "GR {$this->goodsReceipt->gr_number} ada barang reject",
            'url' => route('purchasing.goods-receipts.show', $this->goodsReceipt),
        ];
    }
}