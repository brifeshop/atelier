<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\BaseCrudController;
use App\Models\Master\Employee;
use Illuminate\Http\Request;

class EmployeeController extends BaseCrudController
{
    protected string $model = Employee::class;
    protected string $viewPrefix = 'master.employees';
    protected string $routePrefix = 'master.employees';
    protected string $title = 'Employee';

    protected array $searchable = ['kode', 'nama', 'department', 'position', 'phone', 'email'];

    protected array $validationRules = [
        'kode' => 'required|string|max:50|unique:employees,kode',
        'nama' => 'required|string|max:255',
        'department' => 'nullable|string|max:100',
        'position' => 'nullable|string|max:100',
        'skill_level' => 'nullable|string|max:50',
        'phone' => 'nullable|string|max:50',
        'email' => 'nullable|email|max:255',
        'join_date' => 'nullable|date',
        'hourly_rate' => 'nullable|numeric|min:0',
        'daily_rate' => 'nullable|numeric|min:0',
        'monthly_salary' => 'nullable|numeric|min:0',
        'notes' => 'nullable|string',
        'is_active' => 'boolean',
    ];

    public function create()
    {
        return view("{$this->viewPrefix}.create", [
            'title' => $this->title,
            'routePrefix' => $this->routePrefix,
            'generatedKode' => Employee::generateKode(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->validationRules);

        if (empty($validated['kode'])) {
            $validated['kode'] = Employee::generateKode();
        }

        // Auto-hitung hourly_rate kalau monthly_salary diisi dan hourly_rate kosong
        if (empty($validated['hourly_rate']) && !empty($validated['monthly_salary'])) {
            $validated['hourly_rate'] = Employee::hitungHourlyRate((float) $validated['monthly_salary']);
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        Employee::create($validated);

        return redirect()
            ->route("{$this->routePrefix}.index")
            ->with('success', "{$this->title} berhasil ditambahkan.");
    }

    public function update(Request $request, string $id)
    {
        $employee = Employee::findOrFail($id);

        $rules = $this->validationRules;
        $rules['kode'] = 'required|string|max:50|unique:employees,kode,' . $employee->id;

        $validated = $request->validate($rules);

        if (empty($validated['hourly_rate']) && !empty($validated['monthly_salary'])) {
            $validated['hourly_rate'] = Employee::hitungHourlyRate((float) $validated['monthly_salary']);
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $employee->update($validated);

        return redirect()
            ->route("{$this->routePrefix}.index")
            ->with('success', "{$this->title} berhasil diperbarui.");
    }
}