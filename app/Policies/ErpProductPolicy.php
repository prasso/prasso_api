<?php

namespace App\Policies;

use App\Models\User;
use Faxt\Invenbin\Models\ErpProduct;
use App\Models\Site;

class ErpProductPolicy
{
    /**
     * Determine if the user can view the ErpProduct.
     */
    public function view(User $user, ErpProduct $product)
    {
        // Retrieve the teams the user belongs to
        $userTeamIds = $user->teams()->pluck('teams.id');

        // Check if the product is associated with any site owned by one of the user's teams
        return Site::whereHas('erpProducts', function ($query) use ($product) {
                $query->where('erp_product_id', $product->id); // Match the product in the pivot table
            })
            ->whereHas('teams', function ($query) use ($userTeamIds) {
                $query->whereIn('team_id', $userTeamIds); // Check if the site is owned by one of the user's teams
            })
            ->exists();
    }
    

    /**
     * Determine if the user can manage (update, delete) the ErpProduct.
     */
    public function manage(User $user, ErpProduct $product)
    {
        // Similar logic to viewing, but can be stricter based on your rules
        return $this->view($user, $product);
    }

    public function update(User $user, ErpProduct $product)
    {
        if ($user->isSuperAdmin()) return true;
        
        // Retrieve the team IDs that the user belongs to
        $userTeamIds = $user->teams()->pluck('teams.id');

        // Check if the product is associated with any site owned by one of the user's teams
        return Site::whereHas('erpProducts', function ($query) use ($product) {
                $query->where('erp_product_id', $product->id); // Match the product in the pivot table
            })
            ->whereHas('teams', function ($query) use ($userTeamIds) {
                $query->whereIn('team_id', $userTeamIds); // Check if the site is owned by one of the user's teams
            })
            ->exists();
    }

}
