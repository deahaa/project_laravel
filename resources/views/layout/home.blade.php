@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">أحدث الكتب المضافة</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($books as $book)
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title">{{ $book->title }}</h6>
                                <p class="card-text text-muted small">
                                    {{ $book->author }}<br>
                                    <span class="badge bg-info">{{ $book->category->name }}</span>
                                </p>
                                <p class="card-text">
                                    <span class="badge {{ $book->available_quantity > 0 ? 'bg-success' : 'bg-danger' }}">
                                        متاح: {{ $book->available_quantity }}/{{ $book->quantity }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">أحدث عمليات الإعارة</h5>
            </div>
            <div class="card-body">
                <ul class="list-group">
                    @foreach($recentBorrowings as $borrowing)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">{{ $borrowing->book->title }}</h6>
                            <small class="text-muted">
                                {{ $borrowing->user->name }} -
                                {{ $borrowing->borrow_date->format('Y-m-d') }}
                            </small>
                        </div>
                        <span class="badge bg-warning">قيد الإعارة</span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">إحصائيات سريعة</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-6 mb-3">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h2 class="text-primary">{{ App\Models\Book::count() }}</h2>
                                <p class="mb-0">الكتب</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h2 class="text-info">{{ App\Models\User::count() }}</h2>
                                <p class="mb-0">المستخدمين</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h2 class="text-warning">{{ App\Models\Borrowing::where('status', 'borrowed')->count() }}</h2>
                                <p class="mb-0">كتب معارة</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h2 class="text-success">{{ App\Models\Category::count() }}</h2>
                                <p class="mb-0">تصنيفات</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
