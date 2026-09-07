<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Major;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with(['subCategories' => function($q) {
            $q->withCount('invoices');
        }])->withCount('invoices')->latest()->get();

        $majors = Major::withCount('invoices')->orderBy('name')->get();

        return view('categories.index', compact('categories', 'majors'));
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
        return redirect()->route('categories.index')->with('success', 'Kategori Utama berhasil ditambahkan!');
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
        return redirect()->route('categories.index')->with('success', 'Kategori Utama berhasil diperbarui!');
    }

    public function destroy(Category $category)
    {
        if ($category->invoices()->count() > 0) {
            return redirect()->route('categories.index')->with('error', 'Kategori tidak bisa dihapus karena masih dipakai invoice!');
        }
        $category->delete();
        return redirect()->route('categories.index')->with('success', 'Kategori Utama berhasil dihapus!');
    }

    public function storeSubCategory(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
        ]);

        SubCategory::create($validated);
        return redirect()->route('categories.index')->with('success', 'Sub-Kategori berhasil ditambahkan!');
    }

    public function destroySubCategory(SubCategory $subCategory)
    {
        if ($subCategory->invoices()->count() > 0) {
            return redirect()->route('categories.index')->with('error', 'Sub-Kategori tidak bisa dihapus karena masih dipakai invoice!');
        }
        $subCategory->delete();
        return redirect()->route('categories.index')->with('success', 'Sub-Kategori berhasil dihapus!');
    }

    public function storeMajor(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:majors,name',
        ]);

        Major::create($validated);
        return redirect()->route('categories.index')->with('success', 'Jurusan berhasil ditambahkan!');
    }

    public function destroyMajor(Major $major)
    {
        if ($major->invoices()->count() > 0) {
            return redirect()->route('categories.index')->with('error', 'Jurusan tidak bisa dihapus karena masih dipakai invoice!');
        }
        $major->delete();
        return redirect()->route('categories.index')->with('success', 'Jurusan berhasil dihapus!');
    }
}
