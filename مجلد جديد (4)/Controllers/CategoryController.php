<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CategoryController extends Controller
{
    /**
     * عرض قائمة التصنيفات
     */
    public function index()
    {
        Gate::authorize('view-any', Category::class);

        $categories = Category::withCount('books')->paginate(10);
        return view('categories.index', compact('categories'));
    }

    /**
     * عرض نموذج إنشاء تصنيف
     */
    public function create()
    {
        Gate::authorize('create', Category::class);

        return view('categories.create');
    }

    /**
     * تخزين تصنيف جديد
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Category::class);

        $request->validate([
            'name' => 'required|unique:categories|max:255'
        ]);

        Category::create($request->only('name'));

        return redirect()->route('categories.index')
            ->with('success', 'تمت إضافة التصنيف بنجاح');
    }

    /**
     * عرض نموذج تعديل تصنيف
     */
    public function edit(Category $category)
    {
        Gate::authorize('update', $category);

        return view('categories.edit', compact('category'));
    }

    /**
     * تحديث بيانات التصنيف
     */
    public function update(Request $request, Category $category)
    {
        Gate::authorize('update', $category);

        $request->validate([
            'name' => 'required|max:255|unique:categories,name,' . $category->id
        ]);

        $category->update($request->only('name'));

        return redirect()->route('categories.index')
            ->with('success', 'تم تحديث التصنيف بنجاح');
    }

    /**
     * حذف تصنيف
     */
    public function destroy(Category $category)
    {
        Gate::authorize('delete', $category);

        if ($category->books()->exists()) {
            return back()->with('error', 'لا يمكن حذف التصنيف لأنه يحتوي على كتب');
        }

        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'تم حذف التصنيف بنجاح');
    }
}
