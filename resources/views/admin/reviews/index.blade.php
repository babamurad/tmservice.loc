@extends('admin.layout')

@section('title', 'Отзывы')

@section('content')
    <h1>Отзывы</h1>

    <div class="tabs">
        @foreach (['pending' => 'На модерации', 'approved' => 'Одобрены', 'rejected' => 'Отклонены', 'all' => 'Все'] as $value => $label)
            <a href="{{ route('admin.reviews.index', ['status' => $value]) }}" class="{{ $status === $value ? 'active' : '' }}">{{ $label }}</a>
        @endforeach
    </div>

    <table>
        <thead>
            <tr><th>Клиент</th><th>Мастер</th><th>Оценка</th><th>Комментарий</th><th>Статус</th><th>Действия</th></tr>
        </thead>
        <tbody>
            @forelse ($reviews as $review)
                <tr>
                    <td>{{ $review->client->phone ?? '—' }}</td>
                    <td>{{ $review->masterProfile->user->phone ?? '—' }}</td>
                    <td>{{ $review->rating }} ★</td>
                    <td>{{ \Illuminate\Support\Str::limit($review->comment, 80) }}</td>
                    <td><span class="pill pill-{{ $review->moderation_status }}">{{ $review->moderation_status }}</span></td>
                    <td class="actions">
                        @if ($review->moderation_status !== 'approved')
                            <form method="POST" action="{{ route('admin.reviews.approve', $review) }}" class="inline-form">
                                @csrf
                                <button type="submit" class="btn btn-approve">Одобрить</button>
                            </form>
                        @endif
                        @if ($review->moderation_status !== 'rejected')
                            <form method="POST" action="{{ route('admin.reviews.reject', $review) }}" class="inline-form">
                                @csrf
                                <button type="submit" class="btn btn-reject">Отклонить</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="muted">Отзывов нет.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination">
        {!! $reviews->links('admin.pagination') !!}
    </div>
@endsection
