<?php

namespace App\Filament\SiteAdmin\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\Site;
use App\Models\SitePages;
use App\Models\TeamUser;
use App\Models\Team;
use App\Models\User;
use App\Models\Package;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Prasso\Messaging\Models\MsgDelivery;

class SiteAdminOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected function getCards(): array
    {
        $user = auth()->user();
        $cards = [];

        try {
            if ($user) {
                
                // Use the application's site resolution logic
                $site = app(Site::class);
                
                // If site host is empty, try to get site from the current host
                if (empty($site->host)) {
                    $site = \App\Http\Controllers\Controller::getClientFromHost();
                   
                }
                
                // If still no site, fall back to user's current team
                if (!$site || empty($site->id)) {
                    $currentTeam = $user->currentTeam;
                    if ($currentTeam) {
                        $site = $currentTeam->site()->first();
                        if ($site) {
                            
                        }
                    }
                }
                
                // If still no site, try team memberships
                if (!$site) {
                    $teamMemberships = $user->team_member()->with('team.site')->get();
                    \Log::info('No current team, checking team memberships', [
                        'membership_count' => $teamMemberships->count()
                    ]);
                    
                    foreach ($teamMemberships as $membership) {
                        if ($membership->team && $membership->team->site) {
                            $site = $membership->team->site->first();
                            if ($site) {
                                // Update user's current team to match the site's team
                                $user->current_team_id = $membership->team_id;
                                $user->save();
                                
                                \Log::info('Using site from team membership and updated current team', [
                                    'team_id' => $membership->team_id,
                                    'site_id' => $site->id,
                                    'site_name' => $site->name
                                ]);
                                break;
                            }
                        }
                    }
                }
                
                // If no site from teams, try to get as site owner
                if (!$site) {
                    $siteId = $user->getUserOwnerSiteId();
                    if ($siteId) {
                        $site = Site::with(['teams.users', 'packages'])->find($siteId);
                    }
                }

                if ($site) {
                    $siteId = $site->id;
                    $site = Site::with(['teams.users', 'packages'])->find($siteId);
                    if ($site) {
                        // Site Health Metrics
                        $lastPageUpdate = SitePages::where('fk_site_id', $siteId)
                            ->latest('updated_at')
                            ->value('updated_at');
                        $lastPageUpdate = $lastPageUpdate ? $lastPageUpdate->diffForHumans() : 'Never';

                        // Content Statistics
                        $contentStats = SitePages::where('fk_site_id', $siteId)
                            ->select(
                                DB::raw('COUNT(*) as total'),
                                DB::raw('SUM(CASE WHEN is_published = 1 THEN 1 ELSE 0 END) as published'),
                                DB::raw('SUM(CASE WHEN is_published = 0 THEN 1 ELSE 0 END) as drafts')
                            )
                            ->first();

                        // User/Team Statistics
                        $team = $site->teams->first();
                        $teamMembers = $team ? $team->users->count() : 0;
                        $activeUsers = $team ? $team->users->filter(fn($user) => $user->last_active_at && $user->last_active_at->gt(now()->subDays(30)))->count() : 0;

                        // Package Status
                        $activePackages = $site->packages()->wherePivot('is_active', true)->count();
                        $expiringSoon = $site->packages()
                            ->wherePivot('is_active', true)
                            ->wherePivot('expires_at', '<=', now()->addDays(30))
                            ->wherePivot('expires_at', '>=', now())
                            ->count();

                        // Message Stats
                        $deliveries24h = 0;
                        $deliverySuccessRate = 0;
                        if ($team) {
                            $userIds = $team->users->pluck('id');
                            if ($userIds->count() > 0) {
                                $deliveries24h = MsgDelivery::query()
                                    ->where('recipient_type', 'user')
                                    ->whereIn('recipient_id', $userIds)
                                    ->where('sent_at', '>=', now()->subDay())
                                    ->count();

                                $totalDeliveries = MsgDelivery::whereIn('recipient_id', $userIds)->count();
                                $successfulDeliveries = MsgDelivery::whereIn('recipient_id', $userIds)
                                    ->where('status', 'delivered')
                                    ->count();
                                $deliverySuccessRate = $totalDeliveries > 0 
                                    ? round(($successfulDeliveries / $totalDeliveries) * 100, 1)
                                    : 0;
                            }
                        }

                        // Build Cards
                        $cards = [
                            // Content Statistics
                            Card::make('Total Pages', $contentStats->total ?? 0)
                                ->description($contentStats ? "{$contentStats->published} published • {$contentStats->drafts} drafts" : 'No content')
                                ->descriptionIcon('heroicon-o-document-text')
                                ->color('primary')
                                ->url(route('site.edit.mysite')),

                            Card::make('Last Page Updated', $lastPageUpdate)
                                ->description('Content last updated')
                                ->descriptionIcon('heroicon-o-clock')
                                ->color('gray'),

                            // User/Team Stats
                            Card::make('Team Members', $teamMembers)
                                ->description("$activeUsers active in last 30d")
                                ->descriptionIcon('heroicon-o-users')
                                ->color('success')
                                ->url(route('filament.site-admin.resources.team-users.index')),

                            // Package Status
                           /* Card::make('Active Packages', $activePackages)
                                ->description($expiringSoon ? "$expiringSoon expiring soon" : 'All active')
                                ->descriptionIcon('heroicon-o-cube')
                                ->color($expiringSoon ? 'warning' : 'success'),
*/
                            // Message Stats
                            Card::make('Messages (24h)', $deliveries24h)
                                ->description("$deliverySuccessRate% success rate • Compose")
                                ->descriptionIcon('heroicon-o-chat-bubble-left-right')
                                ->color('info')
                                ->url(route('filament.site-admin.pages.compose-and-send-message')),

                            // Site Health
                            Card::make('Site Health', $this->getSiteHealthStatus($site))
                                ->description($this->getSiteHealthDescription($site))
                                ->descriptionIcon('heroicon-o-shield-check')
                                ->color($this->getSiteHealthColor($site))
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {
            // Log error but don't break the dashboard
            \Log::error('Dashboard error: ' . $e->getMessage());
        }

        return $cards;
    }

    private function getSiteHealthStatus(Site $site): string
    {
        if ($site->is_down_for_maintenance) {
            return 'Maintenance Mode';
        }

        if ($site->ssl_expires_at && $site->ssl_expires_at->lt(now()->addDays(7))) {
            return 'SSL Expiring';
        }

        return 'Operational';
    }

    private function getSiteHealthDescription(Site $site): string
    {
        if ($site->is_down_for_maintenance) {
            return 'Site is in maintenance mode';
        }

        if ($site->ssl_expires_at) {
            $days = now()->diffInDays($site->ssl_expires_at);
            return $days > 0 
                ? "SSL expires in $days days"
                : 'SSL expired';
        }

        return 'All systems operational';
    }

    private function getSiteHealthColor(Site $site): string
    {
        if ($site->is_down_for_maintenance) {
            return 'warning';
        }

        if ($site->ssl_expires_at && $site->ssl_expires_at->lt(now()->addDays(7))) {
            return 'danger';
        }

        return 'success';
    }
}
