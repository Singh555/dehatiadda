<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models\Web;
use Illuminate\Database\Eloquent\Model;
use DB;
use Log;
use Hash;
/**
 * Description of VendorsModel
 *
 * @author maury
 */
class VendorsModel extends Model
{
    /*
     * Vendor Registration
     */
    public static function vendorRegistration($shopfname, $shoplname, $gst_no, $address, $country_id, $state, $city, $pin_code, $account_number, $confirm_account_number,$bank_name, $holder_name, $ifsc_code, $email, $pword, $conf_pword, $phone, $first_name, $last_name)
    {
        Log::info(__CLASS__.'::'.__FUNCTION__.' Called.');
        try {
            if($account_number != $confirm_account_number)
            {
                Log::error('Wrong Account Number, Check it and Try Again.');
                Session()->put('error','Wrong Account Number, Check it and Try Again.');
                return false;
            }
            if($pword != $conf_pword)
            {
                Log::error('Password and Confirm Password Are Not Same. Plaese Try Again.');
                Session()->put('error','Password and Confirm Password Are Not Same. Plaese Try Again.');
                return false;
            }
            DB::beginTransaction();
            $userLoginCoreId = self::createRegistrationAndUserCoreId($email, $pword, $phone, $first_name, $last_name);
            if(!empty($userLoginCoreId))
            {
                $vendorId = self::saveNewVendor($shopfname, $shoplname, $gst_no, $address, $country_id, $state, $city, $pin_code, $email, $phone, $first_name, $last_name, $userLoginCoreId);
                if(!empty($vendorId))
                {
                    if(DB::table('vendor_bank_details')->insert(['vendor_id'=>$vendorId,'bank_name'=>$bank_name, 'account_no'=>$account_number, 'holder_name'=>$holder_name, 'ifsc_code'=>$ifsc_code, 'created_at'=>now(), 'updated_at'=>now(), 'created_by'=>'self']))
                    {
                        Log::info('New Vendor Registration Success.');
                        Session()->put('success','New Vendor Registerd Successfully.');
                        DB::commit();
                        return true;
                    }
                    else{
                        Log::error('Saving Vendor Bank Details Failed. Plaese Try Again.');
                        Session()->put('error','New Vendor Registration Failed. Plaese Try Again.');
                        return false;
                    }
                }
                else{
                Log::error('Saving Vendor Details Failed. Plaese Try Again.');
                Session()->put('error','New Vendor Registration Failed. Plaese Try Again.');
                return false;
                }
            }else{
            Log::error('Getting User Login Core Id Failed. Plaese Try Again.');
            Session()->put('error','Creating User Login Details Failed. Plaese Try Again.');
            return false;
            }
        } catch (Exception $ex) {
            Log::error(" Exception :: ".$ex->getMessage());
            session()->put("error","Exception While Vendor Registration. Please try again");
            return false;
        }
    }
    /*
     * Vendor Data Save in User Table
     */
    private static function createRegistrationAndUserCoreId($email, $pword, $phone, $first_name, $last_name)
    {
        Log::info(__CLASS__.'::'.__FUNCTION__.' Called');
        $userLoginCoreId = DB::table('admins')->insertGetId(['role_id'=>'3', 'user_name'=>$first_name, 'first_name'=>$first_name, 'last_name'=>$last_name, 'phone'=>$phone, 'email'=>$email, 'password'=>Hash::make($pword), 'status'=>'0','created_at'=>now()]);
        Log::info('User Login Core Id Found As :: '.$userLoginCoreId);
        if(!empty($userLoginCoreId)){
            return $userLoginCoreId;
        }
        return false;
    }
    /*
     * Vendor Registration
     */ 
    private static function saveNewVendor($shopfname, $shoplname, $gst_no, $address, $country_id, $state, $city, $pin_code, $email, $phone, $first_name, $last_name, $userLoginCoreId)
    {
        Log::info(__CLASS__.'::'.__FUNCTION__.' Called');
        $vendorId = DB::table('vendors')->insertGetId(['shopfname'=>$shopfname, 'shoplname'=>$shoplname, 'gst_no'=>$gst_no, 'address'=>$address, 'country_id'=>$country_id, 'state'=>$state, 'city'=>$city, 'pin_code'=>$pin_code, 'email'=>$email, 'phone'=>$phone, 'first_name'=>$first_name, 'last_name'=>$last_name, 'created_at'=>now(), 'updated_at'=>now(), 'created_by'=>'self', 'user_login_core_id'=>$userLoginCoreId]);
        Log::info('Vendor Core Id Found As :: '.$vendorId);
        if(!empty($vendorId)){
            return $vendorId;
        }
        return false;
    }
    
}
