<?php

namespace App\Filament\Resources\SiteErpProductResource\Pages;

use App\Filament\Resources\SiteErpProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSiteErpProduct extends EditRecord
{
    protected static string $resource = SiteErpProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
