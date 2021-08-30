<?php

namespace App\Models\Eloquent;

use Illuminate\Database\Eloquent\Model;

/**
 * Description of Category
 *
 * @author Anurag
 */
class Category extends Model
{
    protected $guarded = [];
    
    public function description() {
        return $this->hasOne('\App\Models\Eloquent\CategoryDescription', 'categories_id', 'categories_id');
    }
    public function child() {
        return $this->hasMany('\App\Models\Eloquent\Category', 'parent_id', 'categories_id');
    }
    
}
