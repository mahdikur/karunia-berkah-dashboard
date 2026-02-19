<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $items = Item::with(['category', 'priceHistories' => fn($q) => $q->latest('changed_at')->limit(5)])
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%"))
            ->when($request->category_id, fn($q, $c) => $q->where('category_id', $c))
            ->when($request->status === 'active', fn($q) => $q->where('is_active', true))
            ->when($request->status === 'inactive', fn($q) => $q->where('is_active', false))
            ->latest()
            ->paginate(25);

        $categories = Category::active()->orderBy('name')->get();

        return view('master.items.index', compact('items', 'categories'));
    }

    public function create()
    {
        $categories = Category::active()->orderBy('name')->get();
        return view('master.items.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'code' => 'required|string|max:50|unique:items',
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->only('category_id', 'code', 'name', 'unit', 'description', 'notes');

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('items', 'public');
        }

        Item::create($data);

        return redirect()->route('items.index')->with('success', 'Item berhasil ditambahkan.');
    }

    public function show(Item $item)
    {
        $item->load('category', 'priceHistories.client', 'priceHistories.changedByUser');
        return view('master.items.show', compact('item'));
    }

    public function edit(Item $item)
    {
        $categories = Category::active()->orderBy('name')->get();
        return view('master.items.edit', compact('item', 'categories'));
    }

    public function update(Request $request, Item $item)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'code' => 'required|string|max:50|unique:items,code,' . $item->id,
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->only('category_id', 'code', 'name', 'unit', 'description', 'notes');
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('photo')) {
            if ($item->photo) {
                Storage::disk('public')->delete($item->photo);
            }
            $data['photo'] = $request->file('photo')->store('items', 'public');
        }

        $item->update($data);

        return redirect()->route('items.index')->with('success', 'Item berhasil diperbarui.');
    }

    public function destroy(Item $item)
    {
        $item->delete();
        return redirect()->route('items.index')->with('success', 'Item berhasil dihapus.');
    }

    /**
     * Quick store via AJAX - for inline product creation from PO form
     */
    public function quickStore(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'code' => 'required|string|max:50|unique:items',
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
        ]);

        $item = Item::create([
            'category_id' => $request->category_id,
            'code' => $request->code,
            'name' => $request->name,
            'unit' => $request->unit,
            'description' => $request->description,
        ]);

        $item->load('category');

        return response()->json([
            'success' => true,
            'item' => [
                'id' => $item->id,
                'code' => $item->code,
                'name' => $item->name,
                'unit' => $item->unit,
                'category' => $item->category ? $item->category->name : '-',
            ],
            'message' => 'Produk berhasil ditambahkan.',
        ]);
    }
}
