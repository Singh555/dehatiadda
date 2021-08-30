<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models\Eloquent;

/**
 * Description of AppSectionData
 *
 * @author singh
 */
use Illuminate\Database\Eloquent\Model;
class AppSectionData extends Model
{
    //put your code here
    
    protected $table = "app_section_data";
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';
    protected $guarded = [];
    protected $dates = ['created_date'];
    
  
    
}
