<?php

namespace App\Filament\Resources\Reviews\Tables;

use App\Models\Review;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('client.phone')
                    ->label('Клиент')
                    ->searchable(),
                TextColumn::make('masterProfile.user.phone')
                    ->label('Мастер')
                    ->searchable(),
                TextColumn::make('rating')
                    ->label('Оценка')
                    ->formatStateUsing(fn (int $state): string => str_repeat('★', $state)),
                TextColumn::make('comment')
                    ->label('Комментарий')
                    ->limit(80)
                    ->wrap(),
                TextColumn::make('moderation_status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'warning',
                    }),
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
                    ->visible(fn (Review $record): bool => $record->moderation_status !== 'approved')
                    ->requiresConfirmation()
                    ->action(fn (Review $record) => $record->approve()),
                Action::make('reject')
                    ->label('Отклонить')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (Review $record): bool => $record->moderation_status !== 'rejected')
                    ->requiresConfirmation()
                    ->action(fn (Review $record) => $record->reject()),
            ]);
    }
}
