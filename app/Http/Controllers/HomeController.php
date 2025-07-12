<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Borrowing;

class HomeController extends Controller
{
    public function index()
    {
        $books = Book::orderBy('created_at', 'desc')->take(5)->get();
        $recentBorrowings = Borrowing::with(['user', 'book'])
            ->where('status', 'borrowed')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('home', compact('books', 'recentBorrowings'));
    }
}
