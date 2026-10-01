<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

abstract class BaseCrudController extends Controller
{
    protected string $model;
    protected string $viewPrefix;
    protected string $routePrefix;
    protected string $title;
    protected array $searchable = [];
    protected array $validationRules = [];
    protected int $perPage = 15;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = $this->model::query();

        // Search
        if ($request->filled('search') && !empty($this->searchable)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                foreach ($this->searchable as $field) {
                    $q->orWhere($field, 'like', "%{$search}%");
                }
            });
        }

        $items = $query->latest()->paginate($this->perPage);

        return view("{$this->viewPrefix}.index", [
            'items' => $items,
            'title' => $this->title,
            'routePrefix' => $this->routePrefix,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view("{$this->viewPrefix}.create", [
            'title' => $this->title,
            'routePrefix' => $this->routePrefix,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate($this->validationRules);

        $item = $this->model::create($validated);

        // Hook untuk custom logic
        $this->afterStore($item, $request);

        return redirect()
            ->route("{$this->routePrefix}.index")
            ->with('success', "{$this->title} berhasil ditambahkan.");
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $item = $this->model::findOrFail($id);

        return view("{$this->viewPrefix}.show", [
            'item' => $item,
            'title' => $this->title,
            'routePrefix' => $this->routePrefix,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $item = $this->model::findOrFail($id);

        return view("{$this->viewPrefix}.edit", [
            'item' => $item,
            'title' => $this->title,
            'routePrefix' => $this->routePrefix,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $item = $this->model::findOrFail($id);
        $validated = $request->validate($this->validationRules);

        $item->update($validated);

        // Hook untuk custom logic
        $this->afterUpdate($item, $request);

        return redirect()
            ->route("{$this->routePrefix}.index")
            ->with('success', "{$this->title} berhasil diperbarui.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $item = $this->model::findOrFail($id);
        $item->delete();

        return redirect()
            ->route("{$this->routePrefix}.index")
            ->with('success', "{$this->title} berhasil dihapus.");
    }

    /**
     * Hook: setelah store
     */
    protected function afterStore(Model $item, Request $request): void
    {
        // Override di child controller jika perlu
    }

    /**
     * Hook: setelah update
     */
    protected function afterUpdate(Model $item, Request $request): void
    {
        // Override di child controller jika perlu
    }
}