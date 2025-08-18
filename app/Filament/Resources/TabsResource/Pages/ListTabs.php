<?php

namespace App\Filament\Resources\TabsResource\Pages;

use App\Filament\Resources\TabsResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListTabs extends ListRecords
{
    protected static string $resource = TabsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
