<?php

namespace App\Filament\Resources\MasterProfiles;

use App\Filament\Resources\MasterProfiles\Pages\EditMasterProfile;
use App\Filament\Resources\MasterProfiles\Pages\ListMasterProfiles;
use App\Filament\Resources\MasterProfiles\Schemas\MasterProfileForm;
use App\Filament\Resources\MasterProfiles\Tables\MasterProfilesTable;
use App\Models\MasterProfile;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MasterProfileResource extends Resource
{
    protected static ?string $model = MasterProfile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static ?string $navigationLabel = 'Мастера';

    protected static ?string $modelLabel = 'мастер';

    protected static ?string $pluralModelLabel = 'мастера';

    // Профили создаются мастерами через мобильное приложение при регистрации,
    // админ только модерирует и правит существующие — создание из панели не нужно.
    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return MasterProfileForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MasterProfilesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user', 'city', 'category']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMasterProfiles::route('/'),
            'edit' => EditMasterProfile::route('/{record}/edit'),
        ];
    }
}
