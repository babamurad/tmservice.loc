<?php

namespace App\Filament\Resources\MasterProfiles\Tables;

use App\Models\MasterProfile;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MasterProfilesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.phone')
                    ->label('Телефон')
                    ->searchable(),
                TextColumn::make('city.name_ru')
                    ->label('Город')
                    ->sortable(),
                TextColumn::make('category.name_ru')
                    ->label('Категория')
                    ->sortable(),
                IconColumn::make('is_free')
                    ->label('Свободен')
                    ->boolean(),
                TextColumn::make('moderation_status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'warning',
                    }),
                TextColumn::make('avg_rating')
                    ->label('Рейтинг')
                    ->numeric(1),
                TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('approve')
                    ->label('Одобрить')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (MasterProfile $record): bool => $record->moderation_status !== 'approved')
                    ->requiresConfirmation()
                    ->action(fn (MasterProfile $record) => $record->approve()),
                Action::make('reject')
                    ->label('Отклонить')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (MasterProfile $record): bool => $record->moderation_status !== 'rejected')
                    ->requiresConfirmation()
                    ->action(fn (MasterProfile $record) => $record->reject()),
                EditAction::make(),
            ]);
    }
}
