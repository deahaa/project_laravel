<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BookController extends Controller
{


    
    /**
     * عرض قائمة الكتب
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $books = Book::with('category')
            ->when($search, function ($query, $search) {
                return $query->where('title', 'like', "%$search%")
                             ->orWhere('author', 'like', "%$search%")
                             ->orWhere('isbn', 'like', "%$search%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('books.index', compact('books', 'search'));
    }

    /**
     * عرض تفاصيل كتاب معين
     */
    public function show(Book $book)
    {
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
        $categories = Category::all();
        return view('books.create', compact('categories'));
    }

    /**
     * تخزين كتاب جديد في قاعدة البيانات
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:255',
            'author' => 'required|max:255',
            'isbn' => 'required|unique:books|max:20',
            'category_id' => 'required|exists:categories,id',
            'quantity' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $data = $request->only([
            'title', 'author', 'isbn', 'category_id', 'quantity', 'description'
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
        $categories = Category::all();
        return view('books.edit', compact('book', 'categories'));
    }

    /**
     * تحديث بيانات الكتاب في قاعدة البيانات
     */
    public function update(Request $request, Book $book)
    {
        $request->validate([
            'title' => 'required|max:255',
            'author' => 'required|max:255',
            'isbn' => 'required|max:20|unique:books,isbn,' . $book->id,
            'category_id' => 'required|exists:categories,id',
            'quantity' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $data = $request->only([
            'title', 'author', 'isbn', 'category_id', 'quantity', 'description'
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

        $book->update($data);

        return redirect()->route('books.index')
            ->with('success', 'تم تحديث بيانات الكتاب بنجاح');

            // حذف صورة الغلاف إذا طلب المستخدم ذلك
       if ($request->has('remove_cover') && $book->cover_image) {
    Storage::disk('public')->delete($book->cover_image);
    $data['cover_image'] = null;
}
    }

    /**
     * حذف كتاب من قاعدة البيانات
     */
    public function destroy(Book $book)
    {
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
}
