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
            'materials' => \App\Models\Master\Material::active()->orderBy('nama')->get(),
            'products'  => \App\Models\Master\Product::active()
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
            'item_type'      => 'required|in:material,product',
            'item_id'        => 'required|integer',
            'qty'            => 'required|numeric|min:0.0001',
            'unit'           => 'required|string|max:20',
            'scrap_percent'  => 'nullable|numeric|min:0|max:100',
            'notes'          => 'nullable|string',
        ]);

        // Cegah circular reference
        if ($validated['item_type'] === 'product' && $validated['item_id'] == $bom->product_id) {
            return back()->with('error', 'Produk tidak boleh menjadi komponen dirinya sendiri.');
        }

        $maxSeq = $bom->items()->max('sequence') ?? 0;

        // Ambil unit_cost
        $unitCost = 0;
        if ($validated['item_type'] === 'material') {
            $material = Material::find($validated['item_id']);
            $unitCost = $material ? (float) $material->price : 0;
        } else {
            $product = Product::find($validated['item_id']);
            $unitCost = $product ? (float) $product->selling_price : 0;
        }

        $scrap = $validated['scrap_percent'] ?? 0;
        $totalCost = $validated['qty'] * $unitCost * (1 + ($scrap / 100));

        BomItem::create([
            'bom_id'         => $bom->id,
            'sequence'       => $maxSeq + 1,
            'item_type'      => $validated['item_type'],
            'item_id'        => $validated['item_id'],
            'qty'            => $validated['qty'],
            'unit'           => $validated['unit'],
            'scrap_percent'  => $scrap,
            'unit_cost'      => $unitCost,
            'total_cost'     => $totalCost,
            'notes'          => $validated['notes'] ?? null,
        ]);

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
            'qty'            => 'required|numeric|min:0.0001',
            'unit'           => 'required|string|max:20',
            'scrap_percent'  => 'nullable|numeric|min:0|max:100',
            'notes'          => 'nullable|string',
        ]);

        $scrap = $validated['scrap_percent'] ?? 0;
        $totalCost = $validated['qty'] * $item->unit_cost * (1 + ($scrap / 100));

        $item->update([
            'qty'            => $validated['qty'],
            'unit'           => $validated['unit'],
            'scrap_percent'  => $scrap,
            'total_cost'     => $totalCost,
            'notes'          => $validated['notes'] ?? null,
        ]);

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