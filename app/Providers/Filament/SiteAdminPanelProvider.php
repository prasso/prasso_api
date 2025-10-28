<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use App\Models\Site;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Http\Middleware\SetFilamentSiteNameMiddleware;
use Filament\Navigation\MenuItem;

class SiteAdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $host = request()->getHttpHost();
        $site = Site::getClient($host);
        $primaryHex = $site?->main_color ?? '#0ea5e9';

        return $panel
            ->id('site-admin')
            ->path('site-admin')
            ->login()
            ->colors([
                'primary' => Color::hex($primaryHex),
            ])
            // Core app resources (Content, Users/Teams, etc.)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            // Messaging package resources
            ->discoverResources(in: base_path('packages/prasso/messaging/src/Filament/Resources'), for: 'Prasso\\Messaging\\Filament\\Resources')
            // Church package resources
            ->discoverResources(in: base_path('packages/prasso/church/src/Filament/Resources'), for: 'Prasso\\Church\\Filament\\Resources')
            // Optional: other package resources if needed
            // ->discoverResources(in: base_path('packages/prasso/project_management/src/Filament/Resources'), for: 'Prasso\\ProjectManagement\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            // Discover messaging package pages (e.g., Compose & Send)
            ->discoverPages(in: base_path('packages/prasso/messaging/src/Filament/Pages'), for: 'Prasso\\Messaging\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/SiteAdmin/Widgets'), for: 'App\\Filament\\SiteAdmin\\Widgets')
            ->discoverWidgets(in: base_path('packages/prasso/church/src/Filament/Widgets'), for: 'Prasso\\Church\\Filament\\Widgets')
            ->widgets([
                \App\Filament\SiteAdmin\Widgets\SiteAdminOverview::class,
                \Prasso\Church\Filament\Widgets\ChurchOverview::class,
                \Prasso\Church\Filament\Widgets\ChurchMembershipGrowth::class,
                \Prasso\Church\Filament\Widgets\ChurchQuickActions::class,
                \Prasso\Church\Filament\Widgets\ChurchRecentActivity::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                SetFilamentSiteNameMiddleware::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->userMenuItems([
                \Filament\Navigation\MenuItem::make('Edit Profile')
                    ->url(function () {
                        $user = auth()->guard('web')->user();
                        return $user ? '/site-admin/users/' . $user->id : '#';
                    })
                    ->icon('heroicon-o-user')
                    ->label("Edit Profile"),
                
            ]);
    }
}
