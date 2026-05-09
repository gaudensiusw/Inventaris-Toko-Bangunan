<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Product\Models\Category;
use Modules\Product\Models\SubCategory;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('subCategories')->get();
        return view('product::categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
        ]);

        Category::create($request->only('nama'));

        return redirect()->back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
        ]);

        $category->update($request->only('nama'));

        return redirect()->back()->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->back()->with('success', 'Kategori berhasil dihapus.');
    }

    public function storeSub(Request $request)
    {
        $request->validate([
            'kategori_id' => 'required|exists:kategori,id',
            'nama'        => 'required|string|max:255',
        ]);

        SubCategory::create($request->only(['kategori_id', 'nama']));

        return redirect()->back()->with('success', 'Sub-Kategori berhasil ditambahkan.');
    }

    public function updateSub(Request $request, SubCategory $subCategory)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
        ]);

        $subCategory->update($request->only('nama'));

        return redirect()->back()->with('success', 'Sub-Kategori berhasil diperbarui.');
    }

    public function destroySub(SubCategory $subCategory)
    {
        $subCategory->delete();
        return redirect()->back()->with('success', 'Sub-Kategori berhasil dihapus.');
    }

    public function getSubCategories($categoryId)
    {
        $subCategories = SubCategory::where('kategori_id', $categoryId)->get();
        return response()->json($subCategories);
    }
}
