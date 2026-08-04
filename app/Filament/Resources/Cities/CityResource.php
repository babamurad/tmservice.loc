<?php

namespace App\Filament\Resources\Cities;

use App\Filament\Resources\Cities\Pages\ManageCities;
use App\Models\City;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CityResource extends Resource
{
    protected static ?string $model = City::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static ?string $navigationLabel = 'Города';

    protected static ?string $modelLabel = 'город';

    protected static ?string $pluralModelLabel = 'города';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name_ru')
                    ->label('Название (рус.)')
                    ->required()
                    ->maxLength(255),
                TextInput::make('name_tm')
                    ->label('Название (тм.)')
                    ->required()
                    ->maxLength(255),
                Select::make('parent_city_id')
                    ->label('Головной город (заполнить, если это посёлок-спутник)')
                    ->relationship(
                        name: 'parent',
                        titleAttribute: 'name_ru',
                        modifyQueryUsing: fn (Builder $query, ?Model $record) => $query
                            ->whereNull('parent_city_id')
                            ->when($record, fn (Builder $q) => $q->whereKeyNot($record->getKey())),
                    )
                    ->searchable()
                    ->helperText('Пусто — обычный город. Заполнено — посёлок, мастера из него включаются в поиск по головному городу.'),
                Toggle::make('is_active')
                    ->label('Активен')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name_ru')
                    ->label('Русский')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name_tm')
                    ->label('Туркменский')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('parent.name_ru')
                    ->label('Головной город')
                    ->placeholder('—')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('parent');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCities::route('/'),
        ];
    }
}
