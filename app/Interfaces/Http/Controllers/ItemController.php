<?php

namespace App\Interfaces\Http\Controllers;

use App\Infrastructure\Persistence\ItemRepository;
use App\Models\ItemCategory;
use App\Models\Unit;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ItemController extends Controller
{
    private ItemRepository $repository;

    public function __construct(ItemRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index()
    {
        return Inertia::render('Master/Item/Index', [
            'items' => $this->repository->all(),
            'categories' => ItemCategory::all(),
            'units' => Unit::all()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_code' => 'required|string|unique:items,item_code',
            'item_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'item_category_id' => 'required|exists:item_categories,id',
            'unit_id' => 'required|exists:units,id',
            'item_type' => 'required|in:RAW,WIP,FINISHED',
            'weight' => 'nullable|numeric',
            'dimension' => 'nullable|string',
            'barcode' => 'nullable|string|unique:items,barcode',
            'reorder_level' => 'required|numeric|min:0',
            'safety_stock' => 'required|numeric|min:0',
            'max_stock' => 'required|numeric|min:0',
        ]);

        $this->repository->create($validated);

        return redirect()->back();
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'item_code' => 'required|string|unique:items,item_code,' . $id,
            'item_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'item_category_id' => 'required|exists:item_categories,id',
            'unit_id' => 'required|exists:units,id',
            'item_type' => 'required|in:RAW,WIP,FINISHED',
            'weight' => 'nullable|numeric',
            'dimension' => 'nullable|string',
            'barcode' => 'nullable|string|unique:items,barcode,' . $id,
            'reorder_level' => 'required|numeric|min:0',
            'safety_stock' => 'required|numeric|min:0',
            'max_stock' => 'required|numeric|min:0',
        ]);

        $this->repository->update($id, $validated);

        return redirect()->back();
    }

    public function destroy($id)
    {
        $this->repository->delete($id);
        return redirect()->back();
    }
}
