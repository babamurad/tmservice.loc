@extends('admin.layout')

@section('title', 'Пользователи')

@section('content')
    <h1>Пользователи</h1>

    <div class="tabs">
        @foreach (['all' => 'Все', 'client' => 'Клиенты', 'master' => 'Мастера', 'admin' => 'Админы'] as $value => $label)
            <a href="{{ route('admin.users.index', ['role' => $value]) }}" class="{{ $role === $value ? 'active' : '' }}">{{ $label }}</a>
        @endforeach
    </div>

    <table>
        <thead>
            <tr><th>Телефон</th><th>Роль</th><th>Телефон подтверждён</th><th>Зарегистрирован</th></tr>
        </thead>
        <tbody>
            @forelse ($users as $user)
                <tr>
                    <td>{{ $user->phone }}</td>
                    <td>{{ $user->role }}</td>
                    <td>
                        <span class="pill {{ $user->phone_verified_at ? 'pill-active' : 'pill-inactive' }}">
                            {{ $user->phone_verified_at ? 'Да' : 'Нет' }}
                        </span>
                    </td>
                    <td>{{ $user->created_at->format('d.m.Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted">Пользователей нет.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination">
        {!! $users->links('admin.pagination') !!}
    </div>
@endsection
