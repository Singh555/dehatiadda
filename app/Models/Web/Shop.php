<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models\Web;
use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
use Illuminate\Support\Facades\DB;
use Log;
/**
 * Description of ShopModel
 *
 * @author singh
 */
class Shop extends Model
{
    
    
    public static function registration($shop_name, $gst_no, $address, $country, $state, $city, $pin_code, $email, $phone, $cperson_name, $cperson_phone,$shop_image,$gst_image,$shop_logo)
    {
        $imageDb = '';
        $logoDb = ''; $gstImageDb='';
        try{
            DB::beginTransaction();
            $shop_code = self::generateShopCode();
                $imageDb = uploadImage($shop_image, 'images/shop/front','shop-front');
                $gstImageDb = uploadImage($gst_image, 'images/shop/front','shop-gst');
                if(!empty($shop_logo)){
                $logoDb = uploadImage($shop_logo, 'images/shop/logo','shop-logo');
                if(empty($logoDb)){
                   session()->flash('error',"Error Occured While Logo Image Storing For Shop :: $shop_name. Please try again !");
                  return false; 
                }
                }
                if(empty($imageDb)){
                   session()->flash('error',"Error Occured While Shop Image Storing For Shop :: $shop_name. Please try again !");
                  return false; 
                }
                if(empty($gstImageDb)){
                   session()->flash('error',"Error Occured While Shop Gst Image Storing For Shop :: $shop_name. Please try again !");
                  return false; 
                }
            
            $newShop = new Shop;
            $newShop->shop_name = $shop_name;
            $newShop->shop_code = $shop_code;
            if(!empty($logoDb)){
                $newShop->logo = $logoDb;
            }
            
                $newShop->image = $imageDb;
            
            
                $newShop->gst_image = $gstImageDb;
            
            $newShop->gst_no = $gst_no;
            $newShop->address = $address;
            $newShop->country = $country;
            $newShop->state = $state;
            $newShop->city = $city;
            $newShop->pin_code = $pin_code;
            $newShop->email = $email;
            $newShop->phone = $phone;
            $newShop->contact_person_name = $cperson_name;
            $newShop->contact_person_phone = $cperson_phone;
            
            $newShop->created_by = $email;
            if($newShop->save()){
                 DB::commit();
             session()->flash('success',"Data Saved Successfully For Shop :: $shop_name.");
             return true;
            }else{
                DB::rollback();
            Log::error(__CLASS__."::".__FUNCTION__."Eror occured ");
            if(!empty($imageDb)){
                unlink(public_path().$imageDb);
            }
            if(!empty($logoDb)){
                unlink(public_path().$logoDb);
            }
            if(!empty($gstImageDb)){
                unlink(public_path().$gstImageDb);
            }
            session()->flash('error',"Error Occured While Data Storing For Shop :: $shop_name. Please try again !");
            return false;
            }
            
        }catch(\Exception $e){
            DB::rollback();
            Log::error(__CLASS__."::".__FUNCTION__."Exception occured :: ".$e->getMessage());
            if(!empty($imageDb)){
                unlink(public_path().$imageDb);
            }
            if(!empty($logoDb)){
                unlink(public_path().$logoDb);
            }
            if(!empty($gstImageDb)){
                unlink(public_path().$gstImageDb);
            }
            session()->flash('error',"Exception While Data Storing For Shop :: $shop_name. Please try again !");
            return false;
        }
        return false;
    }
    
    protected static function generateShopCode() {
        $code = "";
        do {
            $code = substr(uniqid(mt_rand(), true) , 0, 12);
            $data = self::where('shop_code', $code)->get();
        } while ($data->count() > 0);
        return $code;
    }
    
    
    
}
