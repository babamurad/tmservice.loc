<?php

namespace App\Filament\Resources\MasterProfiles\Pages;

use App\Filament\Resources\MasterProfiles\MasterProfileResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMasterProfile extends EditRecord
{
    protected static string $resource = MasterProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
