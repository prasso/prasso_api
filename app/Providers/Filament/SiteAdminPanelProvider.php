<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class SiteAdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('site-admin')
            ->path('site-admin')
            ->login()
            ->colors([
                'primary' => Color::Blue,
            ])
            // Core app resources (Content, Users/Teams, etc.)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            // Messaging package resources
            ->discoverResources(in: base_path('packages/prasso/messaging/src/Filament/Resources'), for: 'Prasso\\Messaging\\Filament\\Resources')
            // Optional: other package resources if needed
            // ->discoverResources(in: base_path('packages/prasso/project_management/src/Filament/Resources'), for: 'Prasso\\ProjectManagement\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            // Discover messaging package pages (e.g., Compose & Send)
            ->discoverPages(in: base_path('packages/prasso/messaging/src/Filament/Pages'), for: 'Prasso\\Messaging\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/SiteAdmin/Widgets'), for: 'App\\Filament\\SiteAdmin\\Widgets')
            ->widgets([
                \App\Filament\SiteAdmin\Widgets\SiteAdminOverview::class,
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
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
