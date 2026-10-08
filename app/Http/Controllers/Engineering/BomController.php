<?php

namespace App\Http\Controllers\Engineering;

use App\Http\Controllers\Controller;
use App\Models\Engineering\Bom;
use App\Models\Engineering\BomItem;
use App\Models\Master\Material;
use App\Models\Master\Product;
use App\Services\BomService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BomController extends Controller
{
    public function index(Request $request)
    {
        $query = Bom::with(['product', 'creator']);

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                  ->orWhereHas('product', function ($q2) use ($search) {
                      $q2->where('nama', 'like', "%{$search}%")
                         ->orWhere('kode', 'like', "%{$search}%");
                  });
            });
        }

        $items = $query->latest('effective_date')->latest('id')->paginate(20)->withQueryString();

        $stats = [
            'total'     => Bom::count(),
            'draft'     => Bom::where('status', 'draft')->count(),
            'active'    => Bom::where('status', 'active')->count(),
            'obsolete'  => Bom::where('status', 'obsolete')->count(),
        ];

        return view('engineering.boms.index', [
            'items'    => $items,
            'stats'    => $stats,
            'products' => Product::active()->orderBy('nama')->get(),
        ]);
    }

    public function create()
    {
        return view('engineering.boms.create', [
            'generatedKode' => Bom::generateKode(),
            'products'      => Product::active()->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode'           => 'required|string|max:50|unique:boms,kode',
            'product_id'     => 'required|exists:products,id',
            'version'        => 'required|string|max:20',
            'effective_date' => 'required|date',
            'notes'          => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $bom = Bom::create([
                'kode'           => $validated['kode'],
                'product_id'     => $validated['product_id'],
                'version'        => $validated['version'],
                'effective_date' => $validated['effective_date'],
                'status'         => 'draft',
                'notes'          => $validated['notes'] ?? null,
                'created_by'     => auth()->id(),
            ]);

            DB::commit();

            return redirect()
                ->route('engineering.boms.show', $bom)
                ->with('success', "BOM {$bom->kode} berhasil dibuat. Tambahkan material di bawah.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal membuat BOM: ' . $e->getMessage());
        }
    }

    public function show(Bom $bom)
    {
        $bom->load(['product', 'items.item', 'creator', 'approver']);

        return view('engineering.boms.show', [
            'item'      => $bom,
            'materials' => Material::active()->orderBy('nama')->get(),
            'products'  => Product::active()
                ->where('id', '!=', $bom->product_id)
                ->orderBy('nama')
                ->get(),
        ]);
    }

    public function edit(Bom $bom)
    {
        if (!$bom->canEdit()) {
            return redirect()
                ->route('engineering.boms.show', $bom)
                ->with('error', 'BOM yang sudah aktif tidak bisa diedit.');
        }

        return view('engineering.boms.edit', [
            'item'     => $bom,
            'products' => Product::active()->orderBy('nama')->get(),
        ]);
    }

    public function update(Request $request, Bom $bom)
    {
        if (!$bom->canEdit()) {
            return back()->with('error', 'BOM yang sudah aktif tidak bisa diedit.');
        }

        $validated = $request->validate([
            'kode'           => 'required|string|max:50|unique:boms,kode,' . $bom->id,
            'product_id'     => 'required|exists:products,id',
            'version'        => 'required|string|max:20',
            'effective_date' => 'required|date',
            'notes'          => 'nullable|string',
        ]);

        $bom->update($validated);

        return redirect()
            ->route('engineering.boms.show', $bom)
            ->with('success', 'BOM berhasil diperbarui.');
    }

    public function destroy(Bom $bom)
    {
        if ($bom->status === 'active') {
            return back()->with('error', 'BOM yang sudah aktif tidak bisa dihapus.');
        }

        $kode = $bom->kode;
        $bom->delete();

        return redirect()
            ->route('engineering.boms.index')
            ->with('success', "BOM {$kode} berhasil dihapus.");
    }

    /**
     * Activate BOM
     */
    public function activate(Bom $bom)
    {
        if (!$bom->canActivate()) {
            return back()->with('error', 'BOM belum bisa diaktifkan. Pastikan sudah ada item.');
        }

        // Hitung cost dulu
        BomService::updateCost($bom);

        $bom->update([
            'status'      => 'active',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()
            ->route('engineering.boms.show', $bom)
            ->with('success', 'BOM berhasil diaktifkan.');
    }

    /**
     * Obsolete BOM
     */
    public function obsolete(Bom $bom)
    {
        if (!$bom->canObsolete()) {
            return back()->with('error', 'Hanya BOM aktif yang bisa di-obsolete.');
        }

        $bom->update(['status' => 'obsolete']);

        return redirect()
            ->route('engineering.boms.show', $bom)
            ->with('success', 'BOM berhasil di-obsolete.');
    }

    /**
     * Recalculate cost
     */
    public function recalculate(Bom $bom)
    {
        try {
            BomService::updateCost($bom);
            return back()->with('success', 'Cost BOM berhasil dihitung ulang.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal recalculate: ' . $e->getMessage());
        }
    }

    // ============ BOM ITEMS ============

    public function addItem(Request $request, Bom $bom)
    {
        if (!$bom->canEdit()) {
            return back()->with('error', 'BOM yang sudah aktif tidak bisa diubah.');
        }

        $validated = $request->validate([
            'item_type'         => 'required|in:material,product',
            'item_id'           => 'required|integer',
            'qty'               => 'required|numeric|min:0.0001',
            'unit'              => 'required|string|max:20',
            // Spesifikasi & hierarki
            'spesifikasi'       => 'nullable|string',
            'divisi'            => 'nullable|string|max:100',
            'level'             => 'nullable|string|max:20',
            // Dimensi pakai
            'panjang_pakai'     => 'nullable|numeric|min:0',
            'lebar_pakai'       => 'nullable|numeric|min:0',
            'tinggi_pakai'      => 'nullable|numeric|min:0',
            'berat_pakai'       => 'nullable|numeric|min:0',
            'volume_pakai'      => 'nullable|numeric|min:0',
            // Lainnya
            'scrap_percent'     => 'nullable|numeric|min:0|max:100',
            'notes'             => 'nullable|string',
        ]);

        // Cegah circular reference
        if ($validated['item_type'] === 'product' && $validated['item_id'] == $bom->product_id) {
            return back()->with('error', 'Produk tidak boleh menjadi komponen dirinya sendiri.');
        }

        // Ambil material atau product
        $itemModel = $validated['item_type'] === 'material'
            ? Material::find($validated['item_id'])
            : Product::find($validated['item_id']);

        if (!$itemModel) {
            return back()->with('error', 'Item tidak ditemukan.');
        }

        $maxSeq = $bom->items()->max('sequence') ?? 0;

        // Buat BomItem dengan fillable
        $bomItem = new BomItem();
        $bomItem->bom_id         = $bom->id;
        $bomItem->sequence       = $maxSeq + 1;
        $bomItem->item_type      = $validated['item_type'];
        $bomItem->item_id        = $validated['item_id'];
        $bomItem->qty            = $validated['qty'];
        $bomItem->unit           = $validated['unit'];
        $bomItem->spesifikasi    = $validated['spesifikasi'] ?? null;
        $bomItem->divisi         = $validated['divisi'] ?? null;
        $bomItem->level          = $validated['level'] ?? null;
        $bomItem->panjang_pakai  = $validated['panjang_pakai'] ?? null;
        $bomItem->lebar_pakai    = $validated['lebar_pakai'] ?? null;
        $bomItem->tinggi_pakai   = $validated['tinggi_pakai'] ?? null;
        $bomItem->berat_pakai    = $validated['berat_pakai'] ?? null;
        $bomItem->volume_pakai   = $validated['volume_pakai'] ?? null;
        $bomItem->scrap_percent  = $validated['scrap_percent'] ?? 0;
        $bomItem->notes          = $validated['notes'] ?? null;

        // Hitung cost
        if ($validated['item_type'] === 'material') {
            $cost = BomService::calculateItemCost($bomItem, $itemModel);
            $bomItem->unit_cost  = $cost['unit_cost'];
            $bomItem->total_cost = $cost['total_cost'];
        } else {
            // Sub-assembly: pakai cost dari BOM aktif
            $subBom = Bom::where('product_id', $itemModel->id)
                ->where('status', 'active')
                ->latest('effective_date')
                ->first();

            $unitCost = $subBom ? (float) $subBom->total_cost : (float) ($itemModel->selling_price ?? 0);
            $scrapMultiplier = 1 + ((float) $bomItem->scrap_percent / 100);
            $bomItem->unit_cost  = $unitCost;
            $bomItem->total_cost = (float) $bomItem->qty * $unitCost * $scrapMultiplier;
        }

        $bomItem->save();

        // Recalculate BOM cost
        BomService::updateCost($bom);

        return back()->with('success', 'Item berhasil ditambahkan.');
    }

    public function updateItem(Request $request, Bom $bom, BomItem $item)
    {
        abort_if($item->bom_id !== $bom->id, 404);

        if (!$bom->canEdit()) {
            return back()->with('error', 'BOM yang sudah aktif tidak bisa diubah.');
        }

        $validated = $request->validate([
            'qty'               => 'required|numeric|min:0.0001',
            'unit'              => 'required|string|max:20',
            'spesifikasi'       => 'nullable|string',
            'divisi'            => 'nullable|string|max:100',
            'level'             => 'nullable|string|max:20',
            'panjang_pakai'     => 'nullable|numeric|min:0',
            'lebar_pakai'       => 'nullable|numeric|min:0',
            'tinggi_pakai'      => 'nullable|numeric|min:0',
            'berat_pakai'       => 'nullable|numeric|min:0',
            'volume_pakai'      => 'nullable|numeric|min:0',
            'scrap_percent'     => 'nullable|numeric|min:0|max:100',
            'notes'             => 'nullable|string',
        ]);

        $item->update($validated);

        // Recalculate cost untuk item ini
        if ($item->item_type === 'material' && $item->item) {
            $cost = BomService::calculateItemCost($item, $item->item);
            $item->update([
                'unit_cost'  => $cost['unit_cost'],
                'total_cost' => $cost['total_cost'],
            ]);
        } elseif ($item->item_type === 'product' && $item->item) {
            // Sub-assembly
            $subBom = Bom::where('product_id', $item->item_id)
                ->where('status', 'active')
                ->latest('effective_date')
                ->first();

            $unitCost = $subBom ? (float) $subBom->total_cost : (float) ($item->item->selling_price ?? 0);
            $scrapMultiplier = 1 + ((float) $item->scrap_percent / 100);

            $item->update([
                'unit_cost'  => $unitCost,
                'total_cost' => (float) $item->qty * $unitCost * $scrapMultiplier,
            ]);
        }

        // Recalculate BOM cost total
        BomService::updateCost($bom);

        return back()->with('success', 'Item berhasil diperbarui.');
    }

    public function removeItem(Bom $bom, BomItem $item)
    {
        abort_if($item->bom_id !== $bom->id, 404);

        if (!$bom->canEdit()) {
            return back()->with('error', 'BOM yang sudah aktif tidak bisa diubah.');
        }

        $item->delete();
        BomService::updateCost($bom);

        return back()->with('success', 'Item berhasil dihapus.');
    }
}