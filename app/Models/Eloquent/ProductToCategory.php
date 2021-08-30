<?php

namespace App\Models\Eloquent;

use Illuminate\Database\Eloquent\Model;

/**
 * Description of ProductToCategory
 *
 * @author Anurag
 */
class ProductToCategory extends Model
{
    protected $table = "products_to_categories";
    protected $guarded = [];
    
    public function category() {
        return $this->hasOne('\App\Models\Eloquent\Category', 'categories_id', 'categories_id');
    }
    
}
