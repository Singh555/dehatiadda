<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AppSection
 *
 * @author singh
 */
namespace App\Models\Eloquent;
use Illuminate\Database\Eloquent\Model;
class AppSection extends Model
{
    //put your code here
    protected $table = "app_sections";
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';
    protected $guarded = [];
    protected $dates = ['created_date'];
}
