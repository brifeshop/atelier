<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\BaseCrudController;
use App\Models\Master\WorkCenter;
use Illuminate\Http\Request;

class WorkCenterController extends BaseCrudController
{
    protected string $model = WorkCenter::class;
    protected string $viewPrefix = 'master.work-centers';
    protected string $routePrefix = 'master.work-centers';
    protected string $title = 'Work Center';

    protected array $searchable = ['kode', 'nama', 'location'];

    protected array $validationRules = [
        'kode' => 'required|string|max:50|unique:work_centers,kode',
        'nama' => 'required|string|max:255',
        'description' => 'nullable|string',
        'hourly_rate' => 'nullable|numeric|min:0',
        'capacity_per_hour' => 'nullable|numeric|min:0',
        'location' => 'nullable|string|max:100',
        'notes' => 'nullable|string',
        'is_active' => 'boolean',
    ];

    public function create()
    {
        return view("{$this->viewPrefix}.create", [
            'title' => $this->title,
            'routePrefix' => $this->routePrefix,
            'generatedKode' => WorkCenter::generateKode(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->validationRules);

        if (empty($validated['kode'])) {
            $validated['kode'] = WorkCenter::generateKode();
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        WorkCenter::create($validated);

        return redirect()
            ->route("{$this->routePrefix}.index")
            ->with('success', "{$this->title} berhasil ditambahkan.");
    }

    public function update(Request $request, string $id)
    {
        $workCenter = WorkCenter::findOrFail($id);

        $rules = $this->validationRules;
        $rules['kode'] = 'required|string|max:50|unique:work_centers,kode,' . $workCenter->id;

        $validated = $request->validate($rules);

        $validated['is_active'] = $request->boolean('is_active', true);

        $workCenter->update($validated);

        return redirect()
            ->route("{$this->routePrefix}.index")
            ->with('success', "{$this->title} berhasil diperbarui.");
    }
}