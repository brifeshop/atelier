<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\Purchasing\SupplierPrice;
use App\Models\Master\Supplier;
use App\Models\Master\Material;
use Illuminate\Http\Request;

class SupplierPriceController extends Controller
{
    public function index(Request $request)
    {
        $query = SupplierPrice::with(['supplier', 'material']);

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('material_id')) {
            $query->where('material_id', $request->material_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('supplier', function ($q2) use ($search) {
                    $q2->where('nama', 'like', "%{$search}%");
                })->orWhereHas('material', function ($q2) use ($search) {
                    $q2->where('nama', 'like', "%{$search}%")
                       ->orWhere('kode', 'like', "%{$search}%");
                });
            });
        }

        $items = $query->latest()->paginate(20);

        return view('purchasing.supplier-prices.index', [
            'items' => $items,
            'suppliers' => Supplier::active()->orderBy('nama')->get(),
            'materials' => Material::active()->orderBy('nama')->get(),
        ]);
    }

    public function create(Request $request)
    {
        return view('purchasing.supplier-prices.create', [
            'suppliers' => Supplier::active()->orderBy('nama')->get(),
            'materials' => Material::active()->orderBy('nama')->get(),
            'selectedSupplierId' => $request->input('supplier_id'),
            'selectedMaterialId' => $request->input('material_id'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'material_id' => 'required|exists:materials,id',
            'price' => 'required|numeric|min:0',
            'min_order_qty' => 'nullable|numeric|min:0',
            'lead_time_days' => 'nullable|integer|min:0',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Cek duplikat
        $exists = SupplierPrice::where('supplier_id', $validated['supplier_id'])
            ->where('material_id', $validated['material_id'])
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'Harga untuk supplier & material ini sudah ada. Edit yang lama saja.');
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        SupplierPrice::create($validated);

        return redirect()
            ->route('purchasing.supplier-prices.index')
            ->with('success', 'Harga supplier berhasil ditambahkan.');
    }

    public function edit(SupplierPrice $supplierPrice)
    {
        return view('purchasing.supplier-prices.edit', [
            'item' => $supplierPrice,
            'suppliers' => Supplier::active()->orderBy('nama')->get(),
            'materials' => Material::active()->orderBy('nama')->get(),
        ]);
    }

    public function update(Request $request, SupplierPrice $supplierPrice)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'material_id' => 'required|exists:materials,id',
            'price' => 'required|numeric|min:0',
            'min_order_qty' => 'nullable|numeric|min:0',
            'lead_time_days' => 'nullable|integer|min:0',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Cek duplikat (selain diri sendiri)
        $exists = SupplierPrice::where('supplier_id', $validated['supplier_id'])
            ->where('material_id', $validated['material_id'])
            ->where('id', '!=', $supplierPrice->id)
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'Harga untuk supplier & material ini sudah ada.');
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $supplierPrice->update($validated);

        return redirect()
            ->route('purchasing.supplier-prices.index')
            ->with('success', 'Harga supplier berhasil diperbarui.');
    }

    public function destroy(SupplierPrice $supplierPrice)
    {
        $supplierPrice->delete();

        return redirect()
            ->route('purchasing.supplier-prices.index')
            ->with('success', 'Harga supplier berhasil dihapus.');
    }

    /**
     * Bandingkan harga material di semua supplier
     */
    public function compare(Request $request)
    {
        $materialId = $request->input('material_id');
        $material = null;
        $prices = collect();

        if ($materialId) {
            $material = Material::find($materialId);
            if ($material) {
                $prices = SupplierPrice::with('supplier')
                    ->where('material_id', $materialId)
                    ->active()
                    ->orderBy('price', 'asc')
                    ->get();
            }
        }

        return view('purchasing.supplier-prices.compare', [
            'materials' => Material::active()->orderBy('nama')->get(),
            'material' => $material,
            'prices' => $prices,
        ]);
    }

    public function getPrice(Request $request)
    {
        $supplierId = $request->input('supplier_id');
        $materialId = $request->input('material_id');

        if (!$supplierId || !$materialId) {
            return response()->json(['price' => null]);
        }

        $price = SupplierPrice::where('supplier_id', $supplierId)
            ->where('material_id', $materialId)
            ->active()
            ->first();

        if ($price) {
            return response()->json([
                'price' => (float) $price->price,
                'min_order_qty' => (float) $price->min_order_qty,
                'lead_time_days' => $price->lead_time_days,
            ]);
        }

        // Fallback ke harga material
        $material = Material::find($materialId);
        if ($material && $material->price > 0) {
            return response()->json([
                'price' => (float) $material->price,
                'fallback' => true,
            ]);
        }

        return response()->json(['price' => null]);
    }
}