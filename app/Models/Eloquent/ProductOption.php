<?php

namespace App\Models\Eloquent;

use Illuminate\Database\Eloquent\Model;

/**
 * Description of ProductOption
 *
 * @author Anurag
 */
class ProductOption extends Model
{
    protected $table = "products_options";
    protected $guarded = [];
    
    public function description() {
        return $this->hasOne('\App\Models\Eloquent\ProductOptionDescription', 'products_options_id', 'products_options_id');
    }
    
}
