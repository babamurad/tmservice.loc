@extends('admin.layout')

@section('title', 'Мастера')

@section('content')
    <h1>Мастера</h1>

    <div class="tabs">
        @foreach (['pending' => 'На модерации', 'approved' => 'Одобрены', 'rejected' => 'Отклонены', 'all' => 'Все'] as $value => $label)
            <a href="{{ route('admin.masters.index', ['status' => $value]) }}" class="{{ $status === $value ? 'active' : '' }}">{{ $label }}</a>
        @endforeach
    </div>

    <table>
        <thead>
            <tr><th>Телефон</th><th>Город</th><th>Категория</th><th>Занятость</th><th>Статус</th><th>Действия</th></tr>
        </thead>
        <tbody>
            @forelse ($masters as $master)
                <tr>
                    <td>{{ $master->user->phone ?? '—' }}</td>
                    <td>{{ $master->city->name_ru ?? '—' }}</td>
                    <td>{{ $master->category->name_ru ?? '—' }}</td>
                    <td>{{ $master->is_free ? 'Свободен' : 'Занят' }}</td>
                    <td><span class="pill pill-{{ $master->moderation_status }}">{{ $master->moderation_status }}</span></td>
                    <td class="actions">
                        @if ($master->moderation_status !== 'approved')
                            <form method="POST" action="{{ route('admin.masters.approve', $master) }}" class="inline-form">
                                @csrf
                                <button type="submit" class="btn btn-approve">Одобрить</button>
                            </form>
                        @endif
                        @if ($master->moderation_status !== 'rejected')
                            <form method="POST" action="{{ route('admin.masters.reject', $master) }}" class="inline-form">
                                @csrf
                                <button type="submit" class="btn btn-reject">Отклонить</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="muted">Ничего нет.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination">
        {!! $masters->links('admin.pagination') !!}
    </div>
@endsection
