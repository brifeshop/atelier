<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\BaseCrudController;
use App\Models\Master\Machine;
use Illuminate\Http\Request;

class MachineController extends BaseCrudController
{
    protected string $model = Machine::class;
    protected string $viewPrefix = 'master.machines';
    protected string $routePrefix = 'master.machines';
    protected string $title = 'Machine';

    protected array $searchable = ['kode', 'nama', 'type', 'brand', 'model', 'serial_number', 'location'];

    protected array $validationRules = [
        'kode' => 'required|string|max:50|unique:machines,kode',
        'nama' => 'required|string|max:255',
        'type' => 'nullable|string|max:100',
        'brand' => 'nullable|string|max:100',
        'model' => 'nullable|string|max:100',
        'serial_number' => 'nullable|string|max:100',
        'purchase_date' => 'nullable|date',
        'purchase_price' => 'nullable|numeric|min:0',
        'useful_life_years' => 'nullable|integer|min:1|max:50',
        'salvage_value' => 'nullable|numeric|min:0',
        'power_kw' => 'nullable|numeric|min:0',
        'capacity_per_hour' => 'nullable|numeric|min:0',
        'maintenance_cost_per_month' => 'nullable|numeric|min:0',
        'location' => 'nullable|string|max:100',
        'status' => 'nullable|string|in:Active,Maintenance,Broken',
        'notes' => 'nullable|string',
        'is_active' => 'boolean',
    ];

    public function create()
    {
        return view("{$this->viewPrefix}.create", [
            'title' => $this->title,
            'routePrefix' => $this->routePrefix,
            'generatedKode' => Machine::generateKode(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->validationRules);

        if (empty($validated['kode'])) {
            $validated['kode'] = Machine::generateKode();
        }

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['status'] = $validated['status'] ?? 'Active';

        Machine::create($validated);

        return redirect()
            ->route("{$this->routePrefix}.index")
            ->with('success', "{$this->title} berhasil ditambahkan.");
    }

    public function update(Request $request, string $id)
    {
        $machine = Machine::findOrFail($id);

        $rules = $this->validationRules;
        $rules['kode'] = 'required|string|max:50|unique:machines,kode,' . $machine->id;

        $validated = $request->validate($rules);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['status'] = $validated['status'] ?? 'Active';

        $machine->update($validated);

        return redirect()
            ->route("{$this->routePrefix}.index")
            ->with('success', "{$this->title} berhasil diperbarui.");
    }
}