<?php

namespace App\Filament\Resources\Reports\Pages;

use App\Filament\Resources\Reports\ReportResource;
use App\Models\Report;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListReports extends ListRecords
{
    protected static string $resource = ReportResource::class;

    public function getTabs(): array
    {
        return [
            'pending' => Tab::make('На рассмотрении')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending'))
                ->badge(Report::where('status', 'pending')->count()),
            'resolved' => Tab::make('Решены')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'resolved')),
            'dismissed' => Tab::make('Отклонены')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'dismissed')),
            'all' => Tab::make('Все'),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'pending';
    }
}
