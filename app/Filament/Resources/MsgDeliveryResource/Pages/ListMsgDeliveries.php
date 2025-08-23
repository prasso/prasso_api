<?php

namespace App\Filament\Resources\MsgDeliveryResource\Pages;

use App\Filament\Resources\MsgDeliveryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMsgDeliveries extends ListRecords
{
    protected static string $resource = MsgDeliveryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Read-only: no create action
        ];
    }
}
