<?php

use App\Http\Controllers\DeepLinkController;
use App\Http\Controllers\MasterLinkController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// QR мастера ведёт сюда: если приложение установлено — ОС откроет его через
// Universal Links/App Links, иначе показывается эта страница (см. plan/01-backend.md, Этап 4A).
Route::get('/m/{id}', [MasterLinkController::class, 'show'])->name('master.link');

Route::get('/.well-known/apple-app-site-association', [DeepLinkController::class, 'appleAppSiteAssociation']);
Route::get('/.well-known/assetlinks.json', [DeepLinkController::class, 'androidAssetLinks']);

// Веб-панель администратора живёт под /admin через Filament (см. app/Providers/Filament/AdminPanelProvider.php)
// — сессионная авторизация ('web' guard), отдельно от Sanctum-токенов мобильного API.
