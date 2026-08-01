@extends('admin.layout')

@section('title', 'Категории')

@section('content')
    <h1>Категории</h1>

    <form method="POST" action="{{ route('admin.categories.store') }}" class="add-form">
        @csrf
        <div>
            <label for="name_ru">Название (рус.)</label>
            <input type="text" id="name_ru" name="name_ru" required>
        </div>
        <div>
            <label for="name_tm">Название (тм.)</label>
            <input type="text" id="name_tm" name="name_tm" required>
        </div>
        <div>
            <label for="icon_url">Иконка (необязательно)</label>
            <input type="text" id="icon_url" name="icon_url">
        </div>
        <button type="submit" class="btn btn-primary">Добавить категорию</button>
    </form>

    <table>
        <thead>
            <tr><th>Русский</th><th>Туркменский</th><th>Статус</th><th>Действия</th></tr>
        </thead>
        <tbody>
            @forelse ($categories as $category)
                <tr>
                    <td>{{ $category->name_ru }}</td>
                    <td>{{ $category->name_tm }}</td>
                    <td>
                        <span class="pill {{ $category->is_active ? 'pill-active' : 'pill-inactive' }}">
                            {{ $category->is_active ? 'Активна' : 'Скрыта' }}
                        </span>
                    </td>
                    <td class="actions">
                        <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="inline-form">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="name_ru" value="{{ $category->name_ru }}">
                            <input type="hidden" name="name_tm" value="{{ $category->name_tm }}">
                            <input type="hidden" name="icon_url" value="{{ $category->icon_url }}">
                            <input type="hidden" name="is_active" value="{{ $category->is_active ? '0' : '1' }}">
                            <button type="submit" class="btn btn-outline">
                                {{ $category->is_active ? 'Скрыть' : 'Показать' }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="inline-form" onsubmit="return confirm('Удалить категорию {{ $category->name_ru }}?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-reject">Удалить</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted">Категорий пока нет.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection
