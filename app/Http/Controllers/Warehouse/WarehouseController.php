<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\BaseCrudController;
use App\Models\Warehouse\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends BaseCrudController
{
    protected string $model = Warehouse::class;
    protected string $viewPrefix = 'warehouse.warehouses';
    protected string $routePrefix = 'warehouse.warehouses';
    protected string $title = 'Warehouse';

    protected array $searchable = ['kode', 'nama', 'type', 'pic_name', 'phone', 'address'];

    protected array $validationRules = [
        'kode' => 'required|string|max:50|unique:warehouses,kode',
        'nama' => 'required|string|max:255',
        'type' => 'required|string|in:Raw Material,Finished Goods,WIP',
        'address' => 'nullable|string',
        'pic_name' => 'nullable|string|max:255',
        'phone' => 'nullable|string|max:50',
        'notes' => 'nullable|string',
        'is_active' => 'boolean',
    ];

    public function create()
    {
        return view("{$this->viewPrefix}.create", [
            'title' => $this->title,
            'routePrefix' => $this->routePrefix,
            'generatedKode' => Warehouse::generateKode(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->validationRules);

        if (empty($validated['kode'])) {
            $validated['kode'] = Warehouse::generateKode();
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        Warehouse::create($validated);

        return redirect()
            ->route("{$this->routePrefix}.index")
            ->with('success', "{$this->title} berhasil ditambahkan.");
    }

    public function update(Request $request, string $id)
    {
        $warehouse = Warehouse::findOrFail($id);

        $rules = $this->validationRules;
        $rules['kode'] = 'required|string|max:50|unique:warehouses,kode,' . $warehouse->id;

        $validated = $request->validate($rules);

        $validated['is_active'] = $request->boolean('is_active', true);

        $warehouse->update($validated);

        return redirect()
            ->route("{$this->routePrefix}.index")
            ->with('success', "{$this->title} berhasil diperbarui.");
    }
}