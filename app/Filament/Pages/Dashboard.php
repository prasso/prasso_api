<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Prasso\Church\Filament\Widgets\ChurchOverview;
use Prasso\Church\Filament\Widgets\ChurchMembershipGrowth;
use Prasso\Church\Filament\Widgets\ChurchQuickActions;
use Prasso\Church\Filament\Widgets\ChurchRecentActivity;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.pages.dashboard';

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\SiteAdmin\Widgets\SiteAdminOverview::class,
            ChurchOverview::class,
            ChurchMembershipGrowth::class,
            ChurchQuickActions::class,
            ChurchRecentActivity::class,
        ];
    }
}
