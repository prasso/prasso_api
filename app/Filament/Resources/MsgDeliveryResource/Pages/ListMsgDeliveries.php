<?php

namespace App\Filament\Resources\MsgDeliveryResource\Pages;

use App\Filament\Resources\MsgDeliveryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Facades\FilamentIcon;

class ListMsgDeliveries extends ListRecords
{
    protected static string $resource = MsgDeliveryResource::class;
    
    protected function getHeaderActions(): array
    {
        return [
            // Read-only: no create action
        ];
    }
    
    public function getTitle(): string 
    {
        return 'Message Deliveries';
    }
    
    public function getHeading(): string
    {
        return 'Message Deliveries';
    }
    
    public function getSubheading(): ?string
    {
        return 'Track and monitor message delivery status';
    }
    
    protected function getHeaderWidgets(): array
    {
        return [];
    }
}
