<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\SitePage;
use App\Models\SitePages;
use Illuminate\Http\Request;

class SiteMapController extends Controller
{
    public function edit(Site $site)
    {
        $pages = SitePages::where('fk_site_id', $site->id)
            ->orderBy('menu_id')
            ->orderBy('title')
            ->get();

        // Group pages by their menu level
        $hiddenPages = $pages->where('menu_id', '=', -1);
        $topLevelPages = $pages->where('menu_id', '=', 0);
        $subPages = $pages->where('menu_id', '>', 0);

        return view('sites.site-map-editor', [
            'site' => $site,
            'hiddenPages' => $hiddenPages,
            'topLevelPages' => $topLevelPages,
            'subPages' => $subPages
        ]);
    }

    public function update(Request $request, Site $site)
    {
        $validated = $request->validate([
            'pages' => ['required', 'array'],
            'pages.*.id' => ['required', 'exists:site_pages,id'],
            'pages.*.menu_id' => ['required', 'integer', 'min:-1'],
        ]);

        foreach ($validated['pages'] as $pageData) {
            SitePages::where('id', $pageData['id'])
                ->update(['menu_id' => $pageData['menu_id']]);
        }

        return response()->json(['message' => 'Site map updated successfully']);
    }
} 