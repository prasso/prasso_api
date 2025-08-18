<?php

namespace App\Filament\SiteAdmin\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\Site;
use App\Models\SitePages;
use App\Models\TeamUser;
use Illuminate\Support\Carbon;
use Prasso\Messaging\Models\MsgDelivery;

class SiteAdminOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected function getCards(): array
    {
        $user = auth()->user();
        $sitesOwned = 0;
        $pages = 0;
        $packages = 0;
        $deliveries24h = 0;

        try {
            if ($user) {
                $sitesOwned = (int) $user->getSiteCount();
                $siteId = $user->getUserOwnerSiteId();
                if ($siteId) {
                    $pages = SitePages::where('fk_site_id', $siteId)->count();
                    $site = Site::find($siteId);
                    if ($site) {
                        $packages = $site->packages()->wherePivot('is_active', true)->count();

                        // Scope deliveries to users in this site's team
                        $team = $site->teams()->first();
                        if ($team) {
                            $userIds = TeamUser::where('team_id', $team->id)->pluck('user_id');
                            if ($userIds->count() > 0) {
                                $deliveries24h = MsgDelivery::query()
                                    ->where('recipient_type', 'user')
                                    ->whereIn('recipient_id', $userIds)
                                    ->where('sent_at', '>=', now()->subDay())
                                    ->count();
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // swallow; show zeros on error
        }

        return [
            Card::make('Sites Owned', (string) $sitesOwned),
            Card::make('Pages', (string) $pages),
            Card::make('Active Packages', (string) $packages),
            Card::make('Msg Deliveries (24h)', (string) $deliveries24h)->description('Sent in last 24h'),
        ];
    }
}
