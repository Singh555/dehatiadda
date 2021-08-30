<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models\Core;
use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
use Illuminate\Support\Facades\DB;
/**
 * Description of ShopModel
 *
 * @author singh
 */
class ShopModel extends Model
{
    use Sortable;
    //put your code here
    public $sortable = ['id','shop_code','shop_name','created_at'];
    
    protected $table = 'shops';
    
}
