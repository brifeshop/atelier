<?php

namespace App\Services;

use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderStatusLog;

class SalesOrderStatusService
{
    /**
     * Ubah status SO dengan log.
     * Method utama yang dipakai semua modul.
     */
    public static function changeStatus(
        SalesOrder $so,
        string $newStatus,
        string $reason,
        ?object $reference = null
    ): void {
        $oldStatus = $so->status;

        // Skip kalau status sama
        if ($oldStatus === $newStatus) {
            return;
        }

        // Update timestamp sesuai status
        $updateData = ['status' => $newStatus];
        match ($newStatus) {
            'confirmed' => $updateData['confirmed_at'] = now(),
            'delivered' => $updateData['delivered_at'] = now(),
            'paid' => $updateData['paid_at'] = now(),
            'closed' => $updateData['closed_at'] = now(),
            default => null,
        };

        $so->update($updateData);

        // Log perubahan
        SalesOrderStatusLog::create([
            'sales_order_id' => $so->id,
            'from_status' => $oldStatus,
            'to_status' => $newStatus,
            'changed_by' => auth()->id(),
            'reason' => $reason,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference->id ?? null,
            'changed_at' => now(),
        ]);
    }

    // ==========================================
    // MANUAL TRIGGERS (dipanggil dari UI)
    // ==========================================

    /**
     * Sales konfirmasi SO
     */
    public static function confirm(SalesOrder $so): void
    {
        self::changeStatus($so, 'confirmed', 'Manual: Sales konfirmasi SO');
    }

    /**
     * Cancel SO
     */
    public static function cancel(SalesOrder $so, string $reason = null): void
    {
        self::changeStatus(
            $so,
            'cancelled',
            'Manual: ' . ($reason ?? 'SO dibatalkan')
        );
    }

    /**
     * Close SO (arsip)
     */
    public static function close(SalesOrder $so): void
    {
        self::changeStatus($so, 'closed', 'Manual: Admin close SO');
    }

    // ==========================================
    // AUTO TRIGGERS (dipanggil dari modul lain)
    // ==========================================

    /**
     * Dipanggil saat Work Order dibuat (M6)
     */
    public static function onWorkOrderCreated(SalesOrder $so, object $workOrder): void
    {
        self::changeStatus(
            $so,
            'in_production',
            "Auto: Work Order {$workOrder->wo_number} dibuat",
            $workOrder
        );
    }

    /**
     * Dipanggil saat Surat Jalan dibuat (M9)
     */
    public static function onDeliveryOrderCreated(SalesOrder $so, object $deliveryOrder): void
    {
        self::changeStatus(
            $so,
            'delivered',
            "Auto: Surat Jalan {$deliveryOrder->do_number} dibuat",
            $deliveryOrder
        );
    }

    /**
     * Dipanggil saat Payment diterima (M9)
     */
    public static function onPaymentReceived(SalesOrder $so, object $payment): void
    {
        self::changeStatus(
            $so,
            'paid',
            "Auto: Pembayaran diterima",
            $payment
        );
    }
}