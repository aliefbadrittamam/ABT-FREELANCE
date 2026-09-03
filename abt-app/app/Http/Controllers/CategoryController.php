<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('invoices')->latest()->get();
        return view('categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'invoice_prefix' => 'nullable|string|max:20',
            'brand_name' => 'nullable|string|max:255',
            'tagline' => 'nullable|string|max:255',
        ]);
        Category::create($validated);
        return redirect()->route('categories.index')->with('success', 'Kategori berhasil ditambahkan!');
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'invoice_prefix' => 'nullable|string|max:20',
            'brand_name' => 'nullable|string|max:255',
            'tagline' => 'nullable|string|max:255',
        ]);
        $category->update($validated);
        return redirect()->route('categories.index')->with('success', 'Kategori berhasil diperbarui!');
    }

    public function destroy(Category $category)
    {
        if ($category->invoices()->count() > 0) {
            return redirect()->route('categories.index')->with('error', 'Kategori tidak bisa dihapus karena masih dipakai invoice!');
        }
        $category->delete();
        return redirect()->route('categories.index')->with('success', 'Kategori berhasil dihapus!');
    }
}
