<?php

namespace App\Filament\SiteAdmin\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Log;
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

    protected function getStats(): array
    {
        $user = auth()->user();
        $stats = [];

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
                    }
                }
                
                // If still no site, try team memberships
                if (!$site) {
                    $teamMemberships = $user->team_member()->with('team.site')->get();
                    Log::info('No current team, checking team memberships', [
                        'membership_count' => $teamMemberships->count()
                    ]);
                    
                    foreach ($teamMemberships as $membership) {
                        if ($membership->team && $membership->team->site) {
                            $site = $membership->team->site->first();
                            if ($site) {
                                // Update user's current team to match the site's team
                                $user->current_team_id = $membership->team_id;
                                $user->save();
                                
                                Log::info('Using site from team membership and updated current team', [
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
                            // Query by team_id to include all recipient types (user, guest, member, etc.)
                            $deliveries24h = MsgDelivery::query()
                                ->where('team_id', $team->id)
                                ->where('sent_at', '>=', now()->subDay())
                                ->count();

                            $totalDeliveries = MsgDelivery::where('team_id', $team->id)->count();
                            $successfulDeliveries = MsgDelivery::where('team_id', $team->id)
                                ->whereIn('status', ['sent', 'delivered'])
                                ->count();
                            $deliverySuccessRate = $totalDeliveries > 0 
                                ? round(($successfulDeliveries / $totalDeliveries) * 100, 1)
                                : 0;
                        }

                        // Build Stats with improved visual indicators
                        $stats = [
                            // Content Statistics
                            Stat::make('Content', $contentStats->total ?? 0)
                                ->description($this->getContentDescription($contentStats, $lastPageUpdate))
                                ->icon('heroicon-o-document-text')
                                ->color('primary')
                                ->url(route('site.edit.mysite'))
                                ->extraAttributes([
                                    'class' => 'ring-1 ring-primary-500/10 bg-primary-50 dark:bg-primary-500/10',
                                ]),

                            Stat::make('Messages (24h)', $deliveries24h)
                                ->description("$deliverySuccessRate% success rate • Compose")
                                ->descriptionIcon('heroicon-o-chat-bubble-left-right')
                                ->color('info')
                                ->url(route('filament.site-admin.pages.compose-and-send-message')),

                            // Site Health
                            Stat::make('Site Health', '')
                                ->description($this->getSiteHealthDescription($site))
                                ->icon('heroicon-o-shield-check')
                                ->color($this->getSiteHealthColor($site))
                                ->extraAttributes([
                                    'class' => 'ring-1 ring-' . $this->getSiteHealthColor($site) . '-500/10 bg-' . $this->getSiteHealthColor($site) . '-50 dark:bg-' . $this->getSiteHealthColor($site) . '-500/10',
                                ]),
                                                           // Team Activity
                            Stat::make('Team Activity', $teamMembers . ' members')
                            ->description($this->getTeamDescription($teamMembers, $activeUsers, $deliveries24h))
                            ->icon('heroicon-o-user-group')
                            ->color('success')
                            ->extraAttributes([
                                'class' => 'ring-1 ring-success-500/10 bg-success-50 dark:bg-success-500/10',
                            ]),

                        ];
                    }
                }
            }
        } catch (\Throwable $e) {
            // Log error but don't break the dashboard
            Log::error('Dashboard error: ' . $e->getMessage());
        }

        return $stats;
    }

    private function getContentDescription($contentStats, $lastPageUpdate): HtmlString
    {
        if (!$contentStats || $contentStats->total == 0) {
            return new HtmlString('<span class="text-gray-500">No content created yet</span>');
        }
        
        $publishedPercent = $contentStats->total > 0 ? round(($contentStats->published / $contentStats->total) * 100) : 0;
        $updateIcon = $lastPageUpdate == 'Never' 
            ? '<span class="text-amber-500 ml-1" title="Never updated"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="inline w-4 h-4"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" /></svg></span>' 
            : '<span class="text-green-600 ml-1" title="Last updated: ' . $lastPageUpdate . '"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="inline w-4 h-4"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg></span>';
        
        return new HtmlString(
            '<div class="flex items-center space-x-2">' .
                '<span class="text-green-600"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg></span>' .
                '<div class="w-full bg-gray-200 rounded-full h-2">' .
                    '<div class="bg-green-600 h-2 rounded-full" style="width: ' . $publishedPercent . '%"></div>' .
                '</div>' .
                '<span class="text-sm font-medium">' . $publishedPercent . '%</span>' .
            '</div>' .
            '<div class="mt-1 text-xs text-gray-500 flex items-center justify-between">' .
                '<span>' . $contentStats->published . ' published · ' . $contentStats->drafts . ' drafts</span>' .
                $updateIcon .
            '</div>'
        );
    }

    private function getTeamDescription($teamMembers, $activeUsers, $deliveries24h): HtmlString
    {
        if ($teamMembers == 0) {
            return new HtmlString('<span class="text-gray-500">No team members yet</span>');
        }
        
        $activePercent = $teamMembers > 0 ? round(($activeUsers / $teamMembers) * 100) : 0;
        $barColor = $activePercent > 50 ? 'bg-green-600' : 'bg-amber-500';
        $messageIndicator = $deliveries24h > 0 
            ? '<span class="text-blue-600 ml-1" title="' . $deliveries24h . ' messages in last 24h"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="inline w-4 h-4"><path d="M3.505 2.365A41.369 41.369 0 019 2c1.863 0 3.697.124 5.495.365 1.247.167 2.18 1.108 2.435 2.268a4.45 4.45 0 00-.577-.069 43.141 43.141 0 00-4.706 0C9.229 4.696 7.5 6.727 7.5 8.998v2.24c0 1.413.67 2.735 1.76 3.562l-2.98 2.98A.75.75 0 015 17.25v-3.443c-.501-.048-1-.106-1.495-.172C2.033 13.438 1 12.162 1 10.72V5.28c0-1.441 1.033-2.717 2.505-2.914z" /></svg></span>' 
            : '';
        
        return new HtmlString(
            '<div class="flex items-center space-x-2">' .
                '<span class="text-blue-600"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5"><path d="M10 9a3 3 0 100-6 3 3 0 000 6zM6 8a2 2 0 11-4 0 2 2 0 014 0zM1.49 15.326a.78.78 0 01-.358-.442 3 3 0 013.01-3.01.5.5 0 01.252.066h.5a.5.5 0 01.252-.066h.5a.5.5 0 01.252.066h.5a.5.5 0 01.252-.066 3 3 0 013.01 3.01.78.78 0 01-.358.442l-.5.16a.5.5 0 01-.252.066h-.5a.5.5 0 01-.252-.066l-.5-.16a.78.78 0 01-.358-.442l-.5-.16a.5.5 0 01-.252-.066h-.5a.5.5 0 01-.252.066l-.5.16z" /><path d="M18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15.326a.78.78 0 01-.358-.442 3 3 0 013.01-3.01.5.5 0 01.252.066h.5a.5.5 0 01.252-.066 3 3 0 013.01 3.01.78.78 0 01-.358.442l-.5.16a.5.5 0 01-.252.066h-.5a.5.5 0 01-.252-.066l-.5-.16z" /></svg></span>' .
                '<div class="w-full bg-gray-200 rounded-full h-2">' .
                    '<div class="' . $barColor . ' h-2 rounded-full" style="width: ' . $activePercent . '%"></div>' .
                '</div>' .
            '</div>' .
            '<div class="mt-1 text-xs text-gray-500 flex items-center justify-between">' .
                '<span>' . $activeUsers . ' active in last 30 days</span>' .
                $messageIndicator .
            '</div>' .
            '<div class="mt-1 flex space-x-2">' .
                '<a href="' . route('filament.site-admin.resources.team-users.index') . '" class="text-xs text-blue-600 hover:underline cursor-pointer">Manage Team</a>' .
                '<span class="text-gray-300">|</span>' .
                '<a href="' . route('filament.site-admin.pages.compose-and-send-message') . '" class="text-xs text-blue-600 hover:underline cursor-pointer">Send Message</a>' .
            '</div>'
        );
    }

    private function getSiteHealthDescription(Site $site): HtmlString
    {
        $status = $this->getSiteHealthStatus($site);
        $description = $this->getSiteHealthDescriptionText($site);
        $color = $this->getSiteHealthColor($site);
        
        $iconHtml = match($color) {
            'success' => '<span class="text-green-600"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg></span>',
            'warning' => '<span class="text-amber-500"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" /></svg></span>',
            'danger' => '<span class="text-red-600"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" /></svg></span>',
            default => '<span class="text-gray-600"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd" /></svg></span>'
        };
        
        $textColor = match($color) {
            'success' => 'text-green-600',
            'warning' => 'text-amber-500',
            'danger' => 'text-red-600',
            default => 'text-gray-600'
        };
        
        return new HtmlString(
            '<div class="flex items-center space-x-2">' .
                $iconHtml .
                '<span class="font-medium ' . $textColor . '">' . $status . '</span>' .
            '</div>' .
            '<div class="mt-1 text-xs text-gray-500">' . $description . '</div>'
        );
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

    private function getSiteHealthDescriptionText(Site $site): string
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