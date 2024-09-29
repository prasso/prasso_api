<?php

namespace App\Filament\Resources\SiteErpProductResource\Pages;

use App\Filament\Resources\SiteErpProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSiteErpProducts extends ListRecords
{
    protected static string $resource = SiteErpProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
