<?php

namespace App\Models\Eloquent;

use Illuminate\Database\Eloquent\Model;

/**
 * Description of ManufacturerInfo
 *
 * @author Anurag
 */
class ManufacturerInfo extends Model
{
    protected $table = "manufacturers_info";
    protected $guarded = [];
    
    public function info() {
        return $this->hasOne('\App\Models\Eloquent\ManufacturerInfo', 'manufacturers_id', 'manufacturers_id');
    }
    
}
