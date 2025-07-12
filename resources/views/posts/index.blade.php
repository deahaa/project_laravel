@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">قائمة الكتب</h5>

        <div class="d-flex">
            <form action="{{ route('books.index') }}" method="GET" class="me-2">
                <div class="input-group">
                    <input type="text" name="search" class="form-control"
                           placeholder="ابحث عن كتاب..." value="{{ request('search') }}">
                    <button class="btn btn-light" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>

            @can('create', App\Models\Book::class)
            <a href="{{ route('books.create') }}" class="btn btn-light">
                <i class="bi bi-plus-circle"></i> إضافة كتاب جديد
            </a>
            @endcan
        </div>
    </div>

    <div class="card-body">
        @if($books->isEmpty())
            <div class="alert alert-info text-center">
                <i class="bi bi-info-circle"></i> لا توجد كتب متاحة حالياً
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>العنوان</th>
                            <th>المؤلف</th>
                            <th>التصنيف</th>
                            <th>الكمية</th>
                            <th>المتاح</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($books as $book)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($book->cover_image)
                                        <img src="{{ asset('storage/' . $book->cover_image) }}"
                                             alt="{{ $book->title }}"
                                             class="rounded me-2"
                                             style="width: 40px; height: 60px; object-fit: cover;">
                                    @else
                                        <div class="bg-light border rounded d-flex align-items-center justify-content-center me-2"
                                             style="width: 40px; height: 60px;">
                                            <i class="bi bi-book text-muted"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <strong>{{ $book->title }}</strong>
                                        <div class="text-muted small">ISBN: {{ $book->isbn }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $book->author }}</td>
                            <td>
                                <span class="badge bg-info">
                                    {{ $book->category->name }}
                                </span>
                            </td>
                            <td>{{ $book->quantity }}</td>
                            <td>
                                <span class="badge {{ $book->available_quantity > 0 ? 'bg-success' : 'bg-danger' }}">
                                    {{ $book->available_quantity }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex">
                                    <a href="{{ route('books.show', $book->id) }}"
                                       class="btn btn-sm btn-info me-1"
                                       data-bs-toggle="tooltip" title="عرض التفاصيل">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    @can('update', $book)
                                    <a href="{{ route('books.edit', $book->id) }}"
                                       class="btn btn-sm btn-warning me-1"
                                       data-bs-toggle="tooltip" title="تعديل">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @endcan

                                    @can('delete', $book)
                                    <form action="{{ route('books.destroy', $book->id) }}" method="POST"
                                          onsubmit="return confirm('هل أنت متأكد من حذف هذا الكتاب؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                                data-bs-toggle="tooltip" title="حذف">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $books->appends(request()->query())->links() }}
            </div>

            <div class="mt-3 text-muted text-center">
                عرض {{ $books->firstItem() }} - {{ $books->lastItem() }} من أصل {{ $books->total() }} كتاب
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    // تفعيل أدوات التلميحات
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endsection
