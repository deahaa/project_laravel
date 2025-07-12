@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">تفاصيل الكتاب</h5>
        <div>
            <a href="{{ route('books.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> العودة للقائمة
            </a>
            @can('update', $book)
            <a href="{{ route('books.edit', $book->id) }}" class="btn btn-warning">
                <i class="bi bi-pencil"></i> تعديل
            </a>
            @endcan
        </div>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-4 text-center">
                @if($book->cover_image)
                    <img src="{{ asset('storage/' . $book->cover_image) }}"
                         alt="{{ $book->title }}"
                         class="img-fluid rounded mb-3"
                         style="max-height: 300px;">
                @else
                    <div class="bg-light border rounded d-flex align-items-center justify-content-center"
                         style="height: 300px; width: 100%;">
                        <i class="bi bi-book" style="font-size: 5rem; color: #6c757d;"></i>
                    </div>
                @endif

                <div class="mt-3">
                    <span class="badge bg-primary fs-6">
                        {{ $book->available_quantity }} / {{ $book->quantity }} نسخة متاحة
                    </span>
                </div>
            </div>

            <div class="col-md-8">
                <h3>{{ $book->title }}</h3>
                <p class="text-muted">بواسطة {{ $book->author }}</p>

                <div class="mb-4">
                    <span class="badge bg-info fs-6">{{ $book->category->name }}</span>
                    <span class="badge bg-secondary fs-6">ISBN: {{ $book->isbn }}</span>
                </div>

                <div class="mb-4">
                    <h5>وصف الكتاب:</h5>
                    <p>{{ $book->description ?? 'لا يوجد وصف متوفر' }}</p>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <p><strong>تاريخ الإضافة:</strong> {{ $book->created_at->format('Y-m-d') }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>آخر تحديث:</strong> {{ $book->updated_at->format('Y-m-d') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4">

        <h5 class="mb-3">الإعارات الحالية:</h5>
        @if($borrowings->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>المستخدم</th>
                            <th>تاريخ الإعارة</th>
                            <th>تاريخ الإرجاع المتوقع</th>
                            <th>الأيام المتبقية</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($borrowings as $borrowing)
                        <tr>
                            <td>{{ $borrowing->user->name }}</td>
                            <td>{{ $borrowing->borrow_date->format('Y-m-d') }}</td>
                            <td>{{ $borrowing->return_date->format('Y-m-d') }}</td>
                            <td>
                                @php
                                    $remaining = now()->diffInDays($borrowing->return_date, false);
                                @endphp
                                <span class="badge {{ $remaining > 3 ? 'bg-success' : 'bg-danger' }}">
                                    {{ $remaining > 0 ? $remaining . ' أيام' : 'منتهي' }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $borrowings->links() }}
        @else
            <div class="alert alert-info">
                لا توجد إعارات نشطة لهذا الكتاب.
            </div>
        @endif
    </div>
</div>
//ضافة مؤشرات التقدم للإعارة
<div class="progress mt-3" style="height: 20px;">
    @php
        $percentage = ($book->quantity - $book->available_quantity) / $book->quantity * 100;
    @endphp
    <div class="progress-bar bg-{{ $percentage > 80 ? 'danger' : ($percentage > 50 ? 'warning' : 'success') }}"
         role="progressbar"
         style="width: {{ $percentage }}%"
         aria-valuenow="{{ $percentage }}"
         aria-valuemin="0"
         aria-valuemax="100">
         {{ number_format($percentage, 1) }}% مستعار
    </div>
</div>
@endsection
