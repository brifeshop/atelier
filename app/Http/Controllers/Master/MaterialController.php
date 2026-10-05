<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\BaseCrudController;
use App\Models\Master\Material;
use App\Models\Master\Supplier;
use App\Helpers\UploadHelper;
use Illuminate\Http\Request;

class MaterialController extends BaseCrudController
{
    protected string $model = Material::class;
    protected string $viewPrefix = 'master.materials';
    protected string $routePrefix = 'master.materials';
    protected string $title = 'Material';

    protected array $searchable = ['kode', 'nama', 'category', 'unit', 'location'];

    protected array $validationRules = [
        'kode' => 'required|string|max:50|unique:materials,kode',
        'nama' => 'required|string|max:255',
        'category' => 'nullable|string|max:100',
        'unit' => 'required|string|max:20',
        'price' => 'nullable|numeric|min:0',
        'min_stock' => 'nullable|numeric|min:0',
        'max_stock' => 'nullable|numeric|min:0',
        'current_stock' => 'nullable|numeric|min:0',
        'supplier_id' => 'nullable|exists:suppliers,id',
        'location' => 'nullable|string|max:100',
        'notes' => 'nullable|string',
        'photo' => 'nullable|image|max:2048',
        'is_active' => 'boolean',
    ];

    public function index(Request $request)
    {
        $query = Material::with('supplier');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                foreach ($this->searchable as $field) {
                    $q->orWhere($field, 'like', "%{$search}%");
                }
            });
        }

        $items = $query->latest()->paginate(15);

        return view("{$this->viewPrefix}.index", [
            'items' => $items,
            'title' => $this->title,
            'routePrefix' => $this->routePrefix,
        ]);
    }

    public function create()
    {
        return view("{$this->viewPrefix}.create", [
            'title' => $this->title,
            'routePrefix' => $this->routePrefix,
            'generatedKode' => Material::generateKode(),
            'suppliers' => Supplier::active()->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->validationRules);

        if (empty($validated['kode'])) {
            $validated['kode'] = Material::generateKode();
        }

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $validated['photo_path'] = UploadHelper::uploadPhoto(
                $request->file('photo'),
                'materials',
                $validated['kode']
            );
        }

        unset($validated['photo']); // hapus field 'photo' dari array

        $validated['is_active'] = $request->boolean('is_active', true);

        Material::create($validated);

        return redirect()
            ->route("{$this->routePrefix}.index")
            ->with('success', "{$this->title} berhasil ditambahkan.");
    }

    public function edit(string $id)
    {
        $item = Material::findOrFail($id);

        return view("{$this->viewPrefix}.edit", [
            'item' => $item,
            'title' => $this->title,
            'routePrefix' => $this->routePrefix,
            'suppliers' => Supplier::active()->orderBy('nama')->get(),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $material = Material::findOrFail($id);

        $rules = $this->validationRules;
        $rules['kode'] = 'required|string|max:50|unique:materials,kode,' . $material->id;

        $validated = $request->validate($rules);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $validated['photo_path'] = UploadHelper::uploadPhoto(
                $request->file('photo'),
                'materials',
                $validated['kode'],
                $material->photo_path
            );
        }

        unset($validated['photo']);

        $validated['is_active'] = $request->boolean('is_active', true);

        $material->update($validated);

        return redirect()
            ->route("{$this->routePrefix}.index")
            ->with('success', "{$this->title} berhasil diperbarui.");
    }
}