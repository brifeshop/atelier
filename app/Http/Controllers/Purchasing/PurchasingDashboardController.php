<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\Purchasing\GoodsReceipt;
use App\Models\Purchasing\GoodsReceiptItem;
use App\Models\Purchasing\PurchaseOrder;
use App\Models\Purchasing\PurchaseRequisition;
use Illuminate\Http\Request;

class PurchasingDashboardController extends Controller
{
    public function index()
    {
        // Reject terbaru (7 hari terakhir)
        $recentRejects = GoodsReceiptItem::where('qty_rejected', '>', 0)
            ->whereHas('goodsReceipt', function ($q) {
                $q->where('date', '>=', now()->subDays(7));
            })
            ->with(['goodsReceipt.supplier', 'material'])
            ->latest()
            ->get();

        // Stats
        $stats = [
            'pr_pending' => PurchaseRequisition::where('status', 'draft')->count(),
            'po_pending' => PurchaseOrder::where('status', 'draft')->count(),
            'po_sent' => PurchaseOrder::where('status', 'sent')->count(),
            'gr_draft' => GoodsReceipt::where('status', 'draft')->count(),
            'total_rejected_7days' => $recentRejects->sum('qty_rejected'),
        ];

        return view('purchasing.dashboard.index', compact('recentRejects', 'stats'));
    }
}