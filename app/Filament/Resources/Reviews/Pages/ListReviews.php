<?php

namespace App\Filament\Resources\Reviews\Pages;

use App\Filament\Resources\Reviews\ReviewResource;
use App\Models\Review;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListReviews extends ListRecords
{
    protected static string $resource = ReviewResource::class;

    public function getTabs(): array
    {
        return [
            'pending' => Tab::make('На модерации')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('moderation_status', 'pending'))
                ->badge(Review::where('moderation_status', 'pending')->count()),
            'approved' => Tab::make('Одобрены')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('moderation_status', 'approved')),
            'rejected' => Tab::make('Отклонены')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('moderation_status', 'rejected')),
            'all' => Tab::make('Все'),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'pending';
    }
}
