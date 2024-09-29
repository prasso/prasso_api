<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Faxt\Invenbin\Models\ErpProduct;

class SiteErpProduct extends Model
{
    protected $table = 'site_erp_products'; // The name of the pivot table

    protected $fillable = ['site_id', 'erp_product_id'];

    // Relationship with Site
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    // Relationship with ErpProduct
    public function erpProduct()
    {
        return $this->belongsTo(ErpProduct::class);
    }
}
