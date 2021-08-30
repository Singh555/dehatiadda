<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

class OtpHistory extends Model
{
    //
    
    protected $table = 'otp_history';
    protected $attributes  = ['status'=>'ACTIVE'];
}
