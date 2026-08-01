@extends('admin.layout')

@section('title', 'Обзор')

@section('content')
    <h1>Обзор</h1>
    <table>
        <tr><td>Городов</td><td><strong>{{ $stats['cities'] }}</strong></td></tr>
        <tr><td>Категорий</td><td><strong>{{ $stats['categories'] }}</strong></td></tr>
        <tr>
            <td>Мастеров на модерации</td>
            <td><strong>{{ $stats['mastersPending'] }}</strong> — <a href="{{ route('admin.masters.index', ['status' => 'pending']) }}">посмотреть</a></td>
        </tr>
        <tr><td>Мастеров одобрено</td><td><strong>{{ $stats['mastersApproved'] }}</strong></td></tr>
        <tr>
            <td>Отзывов на модерации</td>
            <td><strong>{{ $stats['reviewsPending'] }}</strong> — <a href="{{ route('admin.reviews.index', ['status' => 'pending']) }}">посмотреть</a></td>
        </tr>
        <tr><td>Пользователей всего</td><td><strong>{{ $stats['users'] }}</strong></td></tr>
    </table>
@endsection
