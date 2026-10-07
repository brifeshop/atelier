<?php

namespace App\Console\Commands;

use App\Models\Warehouse\Inventory;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class CheckMinStockCommand extends Command
{
    protected $signature = 'stock:check-min';
    protected $description = 'Cek inventory yang stoknya di bawah minimum dan kirim notifikasi';

    public function handle()
    {
        $this->info('Memeriksa stok minimum...');

        $lowStocks = Inventory::with(['item', 'location.warehouse'])
            ->lowStock()
            ->whereNotNull('min_stock')
            ->get();

        if ($lowStocks->isEmpty()) {
            $this->info('✅ Semua stok aman.');
            return 0;
        }

        // Kirim ke user dengan role Purchasing, Warehouse, PPIC
        $users = User::role(['Purchasing', 'Warehouse', 'PPIC'])->get();

        if ($users->isEmpty()) {
            $this->warn('⚠️ Tidak ada user dengan role Purchasing/Warehouse/PPIC.');
            return 1;
        }

        foreach ($lowStocks as $inv) {
            Notification::send($users, new LowStockNotification($inv));
        }

        $this->info("🔔 Ditemukan {$lowStocks->count()} item di bawah minimum.");
        $this->info("📧 Notifikasi terkirim ke {$users->count()} user.");

        // Detail
        $this->newLine();
        $this->table(
            ['Item', 'Lokasi', 'Stok', 'Min', 'Gap'],
            $lowStocks->map(fn($inv) => [
                $inv->item->nama ?? '-',
                $inv->location->nama ?? '-',
                number_format($inv->qty, 2),
                number_format($inv->min_stock, 2),
                number_format($inv->min_stock - $inv->qty, 2),
            ])
        );

        return 0;
    }
}