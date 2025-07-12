<?php

namespace App\Http\Controllers;

use App\Models\Borrowing;
use App\Models\Book;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;

class BorrowingController extends Controller
{
    /**
     * عرض قائمة الإعارات
     */
    public function index(Request $request)
    {
        Gate::authorize('view-any', Borrowing::class);

        $search = $request->input('search');
        $status = $request->input('status', 'all');
        $user_id = $request->input('user_id');

        $borrowings = Borrowing::with(['user', 'book'])
            ->when($search, function ($query, $search) {
                return $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%");
                })->orWhereHas('book', function ($q) use ($search) {
                    $q->where('title', 'like', "%$search%")
                      ->orWhere('isbn', 'like', "%$search%");
                });
            })
            ->when($status !== 'all', function ($query) use ($status) {
                return $query->where('status', $status);
            })
            ->when($user_id, function ($query, $user_id) {
                return $query->where('user_id', $user_id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $users = User::where('role_id', '!=', 1)->get(); // استبعاد المديرين

        return view('borrowings.index', compact('borrowings', 'search', 'status', 'users', 'user_id'));
    }

    /**
     * عرض نموذج استعارة كتاب
     */
    public function create()
    {
        Gate::authorize('create', Borrowing::class);

        $users = User::where('role_id', '!=', 1)->get(); // استبعاد المديرين
        $books = Book::where('available_quantity', '>', 0)->get();

        return view('borrowings.create', compact('users', 'books'));
    }

    /**
     * تسجيل استعارة كتاب
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Borrowing::class);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'book_id' => 'required|exists:books,id',
            'return_date' => 'required|date|after:today'
        ]);

        $book = Book::find($request->book_id);

        if ($book->available_quantity < 1) {
            return back()->with('error', 'لا توجد نسخ متاحة من هذا الكتاب للاستعارة');
        }

        Borrowing::create([
            'user_id' => $request->user_id,
            'book_id' => $request->book_id,
            'borrow_date' => now(),
            'return_date' => $request->return_date,
            'status' => 'borrowed'
        ]);

        $book->decrement('available_quantity');

        return redirect()->route('borrowings.index')
            ->with('success', 'تمت عملية الاستعارة بنجاح');
    }

    /**
     * تسجيل إرجاع كتاب
     */
    public function return(Borrowing $borrowing)
    {
        Gate::authorize('update', $borrowing);

        if ($borrowing->status === 'returned') {
            return back()->with('error', 'هذا الكتاب تم إرجاعه مسبقاً');
        }

        $borrowing->update([
            'status' => 'returned',
            'returned_at' => now()
        ]);

        $borrowing->book->increment('available_quantity');

        return back()->with('success', 'تمت عملية الإرجاع بنجاح');
    }

    /**
     * تمديد فترة الإعارة
     */
    public function extend(Borrowing $borrowing, Request $request)
    {
        Gate::authorize('update', $borrowing);

        $request->validate([
            'new_return_date' => 'required|date|after:' . $borrowing->return_date
        ]);

        $borrowing->update([
            'return_date' => $request->new_return_date
        ]);

        return back()->with('success', 'تم تمديد فترة الإعارة بنجاح');
    }

    /**
     * إنشاء تقرير الإعارات
     */
    public function report(Request $request)
    {
        Gate::authorize('view-any', Borrowing::class);

        $start_date = $request->input('start_date', now()->subMonth()->format('Y-m-d'));
        $end_date = $request->input('end_date', now()->format('Y-m-d'));
        $status = $request->input('status', 'all');

        $borrowings = Borrowing::with(['user', 'book'])
            ->whereBetween('borrow_date', [$start_date, $end_date])
            ->when($status !== 'all', function ($query) use ($status) {
                return $query->where('status', $status);
            })
            ->orderBy('borrow_date', 'desc')
            ->get();

        return view('borrowings.report', compact('borrowings', 'start_date', 'end_date', 'status'));
    }
}
