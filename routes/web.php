<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BorrowingController;
use App\Http\Controllers\CategoryController;


Route::get('/', function () {
    return view('welcome');
});
Route::get('/posts/index', [PostController::class,'index']);
Route::get('/posts', [PostController::class,'create']);


Route::get('/posts_edit/{id}', [PostController::class,'edit']);


// الصفحة الرئيسية
Route::get('/', [HomeController::class, 'index'])->name('home');


// مسارات الكتب
Route::resource('books', BookController::class)->except(['show']);

// مسارات المستخدمين
Route::resource('users', UserController::class)->middleware('can:manage-users');

// مسارات الإعارة
Route::prefix('borrowings')->group(function () {
    Route::get('/', [BorrowingController::class, 'index'])->name('borrowings.index');
    Route::post('/{book}', [BorrowingController::class, 'borrow'])->name('borrowings.borrow');
    Route::put('/{borrowing}/return', [BorrowingController::class, 'return'])->name('borrowings.return');
});

// مسارات التصنيفات
Route::resource('categories', CategoryController::class)->except(['show']);
