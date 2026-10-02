<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\BaseCrudController;
use App\Models\Master\Product;
use Illuminate\Http\Request;

class ProductController extends BaseCrudController
{
    protected string $model = Product::class;
    protected string $viewPrefix = 'master.products';
    protected string $routePrefix = 'master.products';
    protected string $title = 'Product';

    protected array $searchable = ['kode', 'nama', 'category', 'unit'];

    protected array $validationRules = [
        'kode' => 'required|string|max:50|unique:products,kode',
        'nama' => 'required|string|max:255',
        'category' => 'nullable|string|max:100',
        'unit' => 'required|string|max:20',
        'description' => 'nullable|string',
        'selling_price' => 'nullable|numeric|min:0',
        'notes' => 'nullable|string',
        'is_active' => 'boolean',
    ];

    public function create()
    {
        return view("{$this->viewPrefix}.create", [
            'title' => $this->title,
            'routePrefix' => $this->routePrefix,
            'generatedKode' => Product::generateKode(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->validationRules);

        if (empty($validated['kode'])) {
            $validated['kode'] = Product::generateKode();
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        Product::create($validated);

        return redirect()
            ->route("{$this->routePrefix}.index")
            ->with('success', "{$this->title} berhasil ditambahkan.");
    }

    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);

        $rules = $this->validationRules;
        $rules['kode'] = 'required|string|max:50|unique:products,kode,' . $product->id;

        $validated = $request->validate($rules);

        $validated['is_active'] = $request->boolean('is_active', true);

        $product->update($validated);

        return redirect()
            ->route("{$this->routePrefix}.index")
            ->with('success', "{$this->title} berhasil diperbarui.");
    }
}