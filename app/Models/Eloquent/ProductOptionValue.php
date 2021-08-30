<?php

namespace App\Models\Eloquent;

use Illuminate\Database\Eloquent\Model;

/**
 * Description of ProductOptionValue
 *
 * @author Anurag
 */
class ProductOptionValue extends Model
{
    protected $table = "products_options_values";
    protected $guarded = [];
    
    public function description() {
        return $this->hasOne('\App\Models\Eloquent\ProductOptionValueDescription', 'products_options_values_id', 'products_options_values_id')->where('language_id', '=', '1');
    }
    
}
