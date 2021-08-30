<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Product
 *
 * @author Anurag
 */
namespace App\Models\Eloquent;
use Illuminate\Database\Eloquent\Model;
class Product extends Model
{
    //put your code here
    protected $table = "products";
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';
    protected $guarded = [];
    protected $dates = ['created_date'];
    
    public function description() {
        
        return $this->hasOne(ProductDescription::class, 'products_id','products_id');
    }
    public function manufacturer() {
        return $this->hasOne(Manufacturer::class, 'manufacturers_id', 'manufacturers_id');
    }
    public function categories() {
        return $this->hasMany(ProductToCategory::class, 'products_id', 'products_id');
    }
    public function wishlist() {
        return $this->hasMany(WishList::class, 'liked_products_id', 'products_id');
    }
    public function flash_sale() {
        return $this->hasMany(FlashSale::class, 'products_id', 'products_id')->where('flash_status', '=', '1');
    }
    public function images() {
        return $this->hasMany(ProductsImage::class, 'products_id', 'products_id');
    }
    public function review() {
        return $this->hasMany(Review::class, 'products_id', 'products_id')->where('reviews_status', '1');
    }
    public function product_attributes() {
        return $this->hasMany(ProductAttribute::class, 'products_id', 'products_id');
    }
}
