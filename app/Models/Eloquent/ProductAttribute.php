<?php

namespace App\Models\Eloquent;

use Illuminate\Database\Eloquent\Model;

/**
 * Description of ProductAttribute
 *
 * @author Anurag
 */
class ProductAttribute extends Model
{
    protected $table = "products_attributes";
    protected $guarded = [];
    
    public function option() {
        return $this->hasOne(ProductOption::class, 'products_options_id', 'options_id');
    }
    public function values() {
        return $this->hasOne(ProductOptionValue::class, 'products_options_values_id', 'options_values_id');
    }
    
}
