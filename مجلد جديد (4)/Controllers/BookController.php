<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Models\Borrowing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;

class BookController extends Controller
{
    /**
     * عرض قائمة الكتب مع إمكانية البحث
     */
    public function index(Request $request)
    {
        // التحقق من الصلاحية
        Gate::authorize('view-any', Book::class);

        $search = $request->input('search');
        $category = $request->input('category');

        $books = Book::with('category')
            ->when($search, function ($query, $search) {
                return $query->where('title', 'like', "%$search%")
                             ->orWhere('author', 'like', "%$search%")
                             ->orWhere('isbn', 'like', "%$search%");
            })
            ->when($category, function ($query, $category) {
                return $query->where('category_id', $category);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $categories = Category::all();

        return view('books.index', compact('books', 'search', 'categories', 'category'));
    }

    /**
     * عرض تفاصيل كتاب معين
     */
    public function show(Book $book)
    {
        Gate::authorize('view', $book);

        $borrowings = $book->borrowings()
            ->with('user')
            ->where('status', 'borrowed')
            ->paginate(5, ['*'], 'borrowings');

        return view('books.show', compact('book', 'borrowings'));
    }

    /**
     * عرض نموذج إنشاء كتاب جديد
     */
    public function create()
    {
        Gate::authorize('create', Book::class);

        $categories = Category::all();
        return view('books.create', compact('categories'));
    }

    /**
     * تخزين كتاب جديد في قاعدة البيانات
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Book::class);

        $request->validate([
            'title' => 'required|max:255',
            'author' => 'required|max:255',
            'isbn' => 'required|unique:books|max:20',
            'category_id' => 'required|exists:categories,id',
            'quantity' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'publication_year' => 'nullable|digits:4|integer|min:1900|max:'.(date('Y')+1)
        ]);

        $data = $request->only([
            'title', 'author', 'isbn', 'category_id', 'quantity', 'description', 'publication_year'
        ]);

        $data['available_quantity'] = $request->quantity;

        // تحميل صورة الغلاف إذا وجدت
        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('book_covers', 'public');
            $data['cover_image'] = $path;
        }

        Book::create($data);

        return redirect()->route('books.index')
            ->with('success', 'تمت إضافة الكتاب بنجاح');
    }

    /**
     * عرض نموذج تعديل كتاب
     */
    public function edit(Book $book)
    {
        Gate::authorize('update', $book);

        $categories = Category::all();
        return view('books.edit', compact('book', 'categories'));
    }

    /**
     * تحديث بيانات الكتاب في قاعدة البيانات
     */
    public function update(Request $request, Book $book)
    {
        Gate::authorize('update', $book);

        $request->validate([
            'title' => 'required|max:255',
            'author' => 'required|max:255',
            'isbn' => 'required|max:20|unique:books,isbn,' . $book->id,
            'category_id' => 'required|exists:categories,id',
            'quantity' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'publication_year' => 'nullable|digits:4|integer|min:1900|max:'.(date('Y')+1)
        ]);

        $data = $request->only([
            'title', 'author', 'isbn', 'category_id', 'quantity', 'description', 'publication_year'
        ]);

        // حساب الكمية المتاحة الجديدة
        $diff = $request->quantity - $book->quantity;
        $data['available_quantity'] = max(0, $book->available_quantity + $diff);

        // تحديث صورة الغلاف إذا وجدت
        if ($request->hasFile('cover_image')) {
            // حذف الصورة القديمة إذا كانت موجودة
            if ($book->cover_image) {
                Storage::disk('public')->delete($book->cover_image);
            }

            $path = $request->file('cover_image')->store('book_covers', 'public');
            $data['cover_image'] = $path;
        }

        // حذف صورة الغلاف إذا طلب المستخدم ذلك
        if ($request->has('remove_cover') && $book->cover_image) {
            Storage::disk('public')->delete($book->cover_image);
            $data['cover_image'] = null;
        }

        $book->update($data);

        return redirect()->route('books.index')
            ->with('success', 'تم تحديث بيانات الكتاب بنجاح');
    }

    /**
     * حذف كتاب من قاعدة البيانات
     */
    public function destroy(Book $book)
    {
        Gate::authorize('delete', $book);

        // التأكد من عدم وجود إعارات نشطة للكتاب
        if ($book->borrowings()->where('status', 'borrowed')->exists()) {
            return redirect()->route('books.index')
                ->with('error', 'لا يمكن حذف الكتاب لأنه معار حالياً');
        }

        // حذف صورة الغلاف إذا كانت موجودة
        if ($book->cover_image) {
            Storage::disk('public')->delete($book->cover_image);
        }

        $book->delete();

        return redirect()->route('books.index')
            ->with('success', 'تم حذف الكتاب بنجاح');
    }

    /**
     * API: الحصول على قائمة الكتب (للتطبيقات الخارجية)
     */
    public function apiIndex(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search');
        $category = $request->input('category');

        $books = Book::with('category')
            ->when($search, function ($query, $search) {
                return $query->where('title', 'like', "%$search%")
                             ->orWhere('author', 'like', "%$search%")
                             ->orWhere('isbn', 'like', "%$search%");
            })
            ->when($category, function ($query, $category) {
                return $query->where('category_id', $category);
            })
            ->orderBy('title')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $books
        ]);
    }

    /**
     * API: الحصول على تفاصيل كتاب (للتطبيقات الخارجية)
     */
    public function apiShow(Book $book)
    {
        return response()->json([
            'success' => true,
            'data' => $book->load('category')
        ]);
    }
}
