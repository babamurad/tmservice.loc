<?php

namespace App\Filament\Resources\MasterProfiles\Pages;

use App\Filament\Resources\MasterProfiles\MasterProfileResource;
use App\Models\MasterProfile;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListMasterProfiles extends ListRecords
{
    protected static string $resource = MasterProfileResource::class;

    public function getTabs(): array
    {
        return [
            'pending' => Tab::make('На модерации')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('moderation_status', 'pending'))
                ->badge(MasterProfile::where('moderation_status', 'pending')->count()),
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
