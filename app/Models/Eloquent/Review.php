<?php

namespace App\Models\Eloquent;

use Illuminate\Database\Eloquent\Model;

/**
 * Description of Review
 *
 * @author Anurag
 */
class Review extends Model
{
    protected $guarded = [];
    
     public function user() {
        return $this->hasOne('\App\Models\Eloquent\User', 'id', 'customers_id');
    }
}
