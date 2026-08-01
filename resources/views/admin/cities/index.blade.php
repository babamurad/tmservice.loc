@extends('admin.layout')

@section('title', 'Города')

@section('content')
    <h1>Города</h1>

    <form method="POST" action="{{ route('admin.cities.store') }}" class="add-form">
        @csrf
        <div>
            <label for="name_ru">Название (рус.)</label>
            <input type="text" id="name_ru" name="name_ru" required>
        </div>
        <div>
            <label for="name_tm">Название (тм.)</label>
            <input type="text" id="name_tm" name="name_tm" required>
        </div>
        <button type="submit" class="btn btn-primary">Добавить город</button>
    </form>

    <table>
        <thead>
            <tr><th>Русский</th><th>Туркменский</th><th>Статус</th><th>Действия</th></tr>
        </thead>
        <tbody>
            @forelse ($cities as $city)
                <tr>
                    <td>{{ $city->name_ru }}</td>
                    <td>{{ $city->name_tm }}</td>
                    <td>
                        <span class="pill {{ $city->is_active ? 'pill-active' : 'pill-inactive' }}">
                            {{ $city->is_active ? 'Активен' : 'Скрыт' }}
                        </span>
                    </td>
                    <td class="actions">
                        <form method="POST" action="{{ route('admin.cities.update', $city) }}" class="inline-form">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="name_ru" value="{{ $city->name_ru }}">
                            <input type="hidden" name="name_tm" value="{{ $city->name_tm }}">
                            <input type="hidden" name="is_active" value="{{ $city->is_active ? '0' : '1' }}">
                            <button type="submit" class="btn btn-outline">
                                {{ $city->is_active ? 'Скрыть' : 'Показать' }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.cities.destroy', $city) }}" class="inline-form" onsubmit="return confirm('Удалить город {{ $city->name_ru }}?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-reject">Удалить</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted">Городов пока нет.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection
