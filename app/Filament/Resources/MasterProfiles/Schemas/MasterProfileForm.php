<?php

namespace App\Filament\Resources\MasterProfiles\Schemas;

use App\Models\City;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MasterProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('city_id')
                    ->label('Город')
                    ->relationship('city', 'name_ru')
                    ->getOptionLabelFromRecordUsing(fn (City $record): string => $record->isSatellite()
                        ? "{$record->name_ru} (посёлок {$record->parent?->name_ru})"
                        : $record->name_ru)
                    ->searchable()
                    ->required(),
                Select::make('category_id')
                    ->label('Категория')
                    ->relationship('category', 'name_ru')
                    ->required(),
                Toggle::make('is_free')
                    ->label('Свободен'),
                Textarea::make('bio')
                    ->label('О себе')
                    ->columnSpanFull(),
            ]);
    }
}
