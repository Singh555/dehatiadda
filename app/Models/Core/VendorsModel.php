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
 * Description of VendorsModel
 *
 * @author maury
 */
class VendorsModel extends Model
{
    use Sortable;
    //put your code here
    public $sortable = ['id','shopfname','gst_no','phone','created_at'];
    
    protected $table = 'vendors';
     
    public function country() {
        return $this->hasOne('App\Models\Core\Countries','countries_id','country_id');
    }
    public function bankAccount() {
        return $this->hasMany('App\Models\Core\VendorsAccountModel','vendor_id','id');
    }
}
