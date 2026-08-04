<?php

namespace App\Filament\Resources\Reports\Tables;

use App\Models\Report;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reporter.phone')
                    ->label('От кого')
                    ->searchable(),
                TextColumn::make('masterProfile.user.phone')
                    ->label('На мастера')
                    ->searchable(),
                TextColumn::make('reason')
                    ->label('Причина')
                    ->limit(80)
                    ->wrap(),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'resolved' => 'success',
                        'dismissed' => 'gray',
                        default => 'warning',
                    }),
                TextColumn::make('created_at')
                    ->label('Создана')
                    ->dateTime('d.m.Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('resolve')
                    ->label('Решено')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (Report $record): bool => $record->status !== 'resolved')
                    ->requiresConfirmation()
                    ->action(fn (Report $record) => $record->resolve()),
                Action::make('dismiss')
                    ->label('Отклонить')
                    ->icon('heroicon-o-x-mark')
                    ->color('gray')
                    ->visible(fn (Report $record): bool => $record->status !== 'dismissed')
                    ->requiresConfirmation()
                    ->action(fn (Report $record) => $record->dismiss()),
            ]);
    }
}
