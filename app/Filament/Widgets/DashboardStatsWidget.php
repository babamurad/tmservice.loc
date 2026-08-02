<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\City;
use App\Models\MasterProfile;
use App\Models\Review;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Городов', City::count()),
            Stat::make('Категорий', Category::count()),
            Stat::make('Мастеров на модерации', MasterProfile::where('moderation_status', 'pending')->count())
                ->color('warning'),
            Stat::make('Мастеров одобрено', MasterProfile::where('moderation_status', 'approved')->count())
                ->color('success'),
            Stat::make('Отзывов на модерации', Review::where('moderation_status', 'pending')->count())
                ->color('warning'),
            Stat::make('Пользователей', User::count()),
        ];
    }
}
