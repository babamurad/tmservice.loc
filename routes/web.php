<?php

use App\Http\Controllers\AdminPanel\AuthController as AdminAuthController;
use App\Http\Controllers\AdminPanel\CategoryController as AdminCategoryController;
use App\Http\Controllers\AdminPanel\CityController as AdminCityController;
use App\Http\Controllers\AdminPanel\DashboardController as AdminDashboardController;
use App\Http\Controllers\AdminPanel\MasterController as AdminMasterController;
use App\Http\Controllers\AdminPanel\ReviewController as AdminReviewController;
use App\Http\Controllers\AdminPanel\UserController as AdminUserController;
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

// Веб-панель администратора: сессионная авторизация ('web' guard), отдельно от
// Sanctum-токенов мобильного API (см. plan/01-backend.md, блок C2).
// GET /admin/login оставлен без префикса имени 'admin.', так как именно на
// route('login') редиректит стандартный middleware 'auth' для гостей.
Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('/cities', [AdminCityController::class, 'index'])->name('cities.index');
    Route::post('/cities', [AdminCityController::class, 'store'])->name('cities.store');
    Route::put('/cities/{city}', [AdminCityController::class, 'update'])->name('cities.update');
    Route::delete('/cities/{city}', [AdminCityController::class, 'destroy'])->name('cities.destroy');

    Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('/masters', [AdminMasterController::class, 'index'])->name('masters.index');
    Route::post('/masters/{master}/approve', [AdminMasterController::class, 'approve'])->name('masters.approve');
    Route::post('/masters/{master}/reject', [AdminMasterController::class, 'reject'])->name('masters.reject');

    Route::get('/reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
    Route::post('/reviews/{review}/approve', [AdminReviewController::class, 'approve'])->name('reviews.approve');
    Route::post('/reviews/{review}/reject', [AdminReviewController::class, 'reject'])->name('reviews.reject');

    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
});
