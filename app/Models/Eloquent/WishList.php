<?php

namespace App\Models\Eloquent;

use Illuminate\Database\Eloquent\Model;

/**
 * Description of WishList
 *
 * @author Anurag
 */
class WishList extends Model
{
    protected $table = "liked_products";
    protected $guarded = [];
}
