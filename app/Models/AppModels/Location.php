<?php

namespace App\Models\AppModels;

use App\Http\Controllers\App\AppSettingController;
use DB;
use Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\HttpStatus;
class Location extends Model
{

    public static function addshippingaddress($request)
    {

        $user_id = auth()->user()->id;
        $entry_firstname = $request->entry_firstname;
        $entry_lastname = $request->entry_lastname;
        $entry_street_address = $request->entry_street_address;
        $entry_suburb = $request->entry_suburb;
        $entry_postcode = $request->entry_postcode;
        $entry_city = $request->entry_city;
        if(isset($request->entry_state)){
            $entry_state = $request->entry_state;
            $entry_country_id = 0;
            $entry_zone_id = 0;
        } else {
            $entry_state = $request->entry_zone_id;
            $entry_country_id = $request->entry_country_id;
            $entry_zone_id = $request->entry_zone_id;
        }
        $entry_gender = 1;
        $entry_company = $request->entry_company;
        $is_default = $request->is_default;
        $entry_latitude = $request->entry_latitude;
        $entry_longitude = $request->entry_longitude;
        $entry_phone = $request->phone;
        
        $validator = Validator::make($request->all(), [
            'entry_firstname' => 'required',
            'entry_street_address' => 'required',
            'entry_postcode' => 'required',
            'entry_city' => 'required',
            'entry_state' => 'required',
            //'entry_country_id' => 'required',
            'is_default' => 'required',
        ]);
        
        if ($validator->fails()) {
            Log::error(__CLASS__."::".__FUNCTION__." Validation failed !");
            return returnResponse($validator->errors(), HttpStatus::HTTP_UNPROCESSABLE_ENTITY);
        }
        
        
        $consumer_data = getallheaders();
      /*
      $consumer_data['consumer_key'] = $request->header('consumer_key');
      $consumer_data['consumer_secret'] = $request->header('consumer_secret');
      $consumer_data['consumer_nonce'] = $request->header('consumer_nonce');
      $consumer_data['consumer_device_id'] = $request->header('consumer_device_id');
      */
      $consumer_data['consumer_ip'] = $request->ip();
        $consumer_data['consumer_url'] = __FUNCTION__;
        $authController = new AppSettingController();
        $authenticate = $authController->apiAuthenticate($consumer_data);

        if ($authenticate == 1) {

            if (!empty($user_id)) {

                $address_book_data = array(
                    'entry_firstname' => $entry_firstname,
                    'entry_lastname' => $entry_lastname,
                    'entry_street_address' => $entry_street_address,
                    'entry_suburb' => $entry_suburb,
                    'entry_postcode' => $entry_postcode,
                    'entry_city' => $entry_city,
                    'entry_state' => $entry_state,
                    'entry_country_id' => $entry_country_id,
                    'entry_zone_id' => $entry_zone_id,
                    'user_id'                     =>   $user_id,
                    'entry_gender' => $entry_gender,
                    'entry_company' => $entry_company,
                    'entry_latitude' => $entry_latitude,
                    'entry_longitude' => $entry_longitude,
                    'phone' => $entry_phone,
                );

                $address_book_id = DB::table('address_book')->insertGetId($address_book_data);

                if ($is_default == '1') {
                    DB::table('user_to_address')->where('user_id', $user_id)
                        ->update(['is_default' => 0]);
                }

                DB::table('user_to_address')->insertGetId(['user_id' => $user_id, 'address_book_id' => $address_book_id, 'is_default' => $is_default]);

            }
            //$responseData = array('success' => '1', 'data' => array(), 'message' => "Shipping address has been added successfully!");
            return returnResponse("Address has been added successfully!", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS,array('id'=>$address_book_id));
        } 
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
    }

    public static function updateshippingaddress($request)
    {

        $user_id = auth()->user()->id;
        $address_book_id = $request->address_book_id;
        $entry_firstname = $request->entry_firstname;
        $entry_lastname = $request->entry_lastname;
        $entry_street_address = $request->entry_street_address;
        $entry_suburb = $request->entry_suburb;
        $entry_postcode = $request->entry_postcode;
        $entry_city = $request->entry_city;
        $entry_state = $request->entry_zone_id;
        $entry_country_id = $request->entry_country_id;
        $entry_zone_id = $request->entry_zone_id;
        $entry_gender = 1;
        $entry_company = $request->entry_company;
        $is_default = $request->is_default;
        $entry_latitude = $request->entry_latitude;
        $entry_longitude = $request->entry_longitude;
        $entry_phone = $request->phone;
        $validator = Validator::make($request->all(), [
            'address_book_id' => 'required',
            'entry_firstname' => 'required',
            'entry_street_address' => 'required',
            'entry_postcode' => 'required',
            'entry_city' => 'required',
            'entry_zone_id' => 'required',
            'entry_country_id' => 'required',
            'is_default' => 'required',
        ]);
        
        if ($validator->fails()) {
            Log::error(__CLASS__."::".__FUNCTION__." Validation failed !");
            return returnResponse($validator->errors(), HttpStatus::HTTP_UNPROCESSABLE_ENTITY);
        }
        
        
        $consumer_data = getallheaders();
      /*
      $consumer_data['consumer_key'] = $request->header('consumer_key');
      $consumer_data['consumer_secret'] = $request->header('consumer_secret');
      $consumer_data['consumer_nonce'] = $request->header('consumer_nonce');
      $consumer_data['consumer_device_id'] = $request->header('consumer_device_id');
      */
      $consumer_data['consumer_ip'] = $request->ip();
        $consumer_data['consumer_url'] = __FUNCTION__;
        $authController = new AppSettingController();
        $authenticate = $authController->apiAuthenticate($consumer_data);

        if ($authenticate == 1) {
            if (!empty($user_id)) {

                $address_book_data = array(
                    'entry_firstname' => $entry_firstname,
                    'entry_lastname' => $entry_lastname,
                    'entry_street_address' => $entry_street_address,
                    'entry_suburb' => $entry_suburb,
                    'entry_postcode' => $entry_postcode,
                    'entry_city' => $entry_city,
                    'entry_state' => $entry_state,
                    'entry_country_id' => $entry_country_id,
                    'entry_zone_id' => $entry_zone_id,

                    'entry_gender' => $entry_gender,
                    'entry_company' => $entry_company,
                    'entry_latitude' => $entry_latitude,
                    'entry_longitude' => $entry_longitude,
                    'phone' => $entry_phone,
                );

                //add address into address book
                DB::table('address_book')->where('address_book_id', $address_book_id)->update($address_book_data);

                if ($is_default == '1') {
                    DB::table('user_to_address')->where('user_id', $user_id)
                        ->update(['is_default' => 0]);

                    DB::table('user_to_address')->where('user_id', $user_id)->where('address_book_id', $address_book_id)->update(['is_default' => $is_default]);
                }
            }
            //$responseData = array('success' => '1', 'data' => array(), 'message' => "Shipping address has been updated successfully!");
            return returnResponse("Shipping address has been updated successfully!", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS);
        } 
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
            
    }

    public static function deleteshippingaddress($request)
    {
        $user_id = auth()->user()->id;
        $address_book_id = $request->address_book_id;
        $consumer_data = getallheaders();
      /*
      $consumer_data['consumer_key'] = $request->header('consumer_key');
      $consumer_data['consumer_secret'] = $request->header('consumer_secret');
      $consumer_data['consumer_nonce'] = $request->header('consumer_nonce');
      $consumer_data['consumer_device_id'] = $request->header('consumer_device_id');
      */
      $consumer_data['consumer_ip'] = $request->ip();
        $consumer_data['consumer_url'] = __FUNCTION__;
        $authController = new AppSettingController();
        $authenticate = $authController->apiAuthenticate($consumer_data);
        
        $validator = Validator::make($request->all(), [
            'address_book_id' => 'required',
        ]);
        
        if ($validator->fails()) {
            Log::error(__CLASS__."::".__FUNCTION__." Validation failed !");
            return returnResponse($validator->errors(), HttpStatus::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($authenticate == 1) {

            if (!empty($user_id)) {
                DB::table('address_book')->where('address_book_id', $address_book_id)->delete();
                DB::table('user_to_address')->where('address_book_id', $address_book_id)->delete();
            }

            return returnResponse("Shipping address has been deleted successfully!", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS);
        } 
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
    }

    public static function getalladdress($request)
    {
        $user_id = auth()->user()->id;
        $consumer_data = getallheaders();
      /*
      $consumer_data['consumer_key'] = $request->header('consumer_key');
      $consumer_data['consumer_secret'] = $request->header('consumer_secret');
      $consumer_data['consumer_nonce'] = $request->header('consumer_nonce');
      $consumer_data['consumer_device_id'] = $request->header('consumer_device_id');
      */
      $consumer_data['consumer_ip'] = $request->ip();
        $consumer_data['consumer_url'] = __FUNCTION__;
        $authController = new AppSettingController();
        $authenticate = $authController->apiAuthenticate($consumer_data);

        if ($authenticate == 1) {
          if($request->has('language_id')){
            $language_id = $request->language_id;
            }
      if($request->has('page')) {  
          
      $skip = $request->page . '0';
      }else{
          $skip = '0';
      }
            $addresses = DB::table('user_to_address')
                ->leftjoin('address_book', 'address_book.address_book_id', '=', 'user_to_address.address_book_id')
                ->leftJoin('countries', 'countries.countries_id', '=', 'address_book.entry_country_id')
                ->leftJoin('zones', 'zones.zone_id', '=', 'address_book.entry_zone_id')
                ->select(
                    'user_to_address.is_default as default_address',
                    'address_book.address_book_id as address_id',
                    'address_book.entry_gender as gender',
                    'address_book.entry_company as company',
                    'address_book.entry_firstname as firstname',
                    'address_book.entry_lastname as lastname',
                    'address_book.entry_street_address as street',
                    'address_book.entry_suburb as suburb',
                    'address_book.entry_postcode as postcode',
                    'address_book.entry_city as city',
                    'address_book.entry_state as state',
                    'address_book.entry_latitude as latitude',
                    'address_book.entry_longitude as longitude',
                    'address_book.phone as phone',

                    'countries.countries_id as countries_id',
                    'countries.countries_name as country_name',

                    'zones.zone_id as zone_id',
                    'zones.zone_code as zone_code',
                    'zones.zone_name as zone_name'
                )
                ->where('address_book.is_user_address', '=',  'N')
                ->where('user_to_address.user_id', $user_id)->skip($skip)->take(10)->get();

            if (count($addresses) > 0) {
               // $addresses_data = $addresses;
               // $responseData = array('success' => '1', 'data' => $addresses_data, 'message' => "Return shipping addresses successfully");
                return returnResponse("Return shipping addresses successfully", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $addresses);
            } else {
                //$addresses_data = array();
                //$responseData = array('success' => '0', 'data' => $addresses_data, 'message' => "Addresses are not added yet.");
                return returnResponse("Addresses are not added yet.", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $addresses);
            }
        } 
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
    }

    public static function updatedefaultaddress($request)
    {
        $user_id = auth()->user()->id;
        $address_book_id = $request->address_book_id;
        $consumer_data = getallheaders();
      /*
      $consumer_data['consumer_key'] = $request->header('consumer_key');
      $consumer_data['consumer_secret'] = $request->header('consumer_secret');
      $consumer_data['consumer_nonce'] = $request->header('consumer_nonce');
      $consumer_data['consumer_device_id'] = $request->header('consumer_device_id');
      */
      $consumer_data['consumer_ip'] = $request->ip();
        $consumer_data['consumer_url'] = __FUNCTION__;
        $authController = new AppSettingController();
        $authenticate = $authController->apiAuthenticate($consumer_data);
        
        $validator = Validator::make($request->all(), [
            'address_book_id' => 'required',
        ]);
        
        if ($validator->fails()) {
            Log::error(__CLASS__."::".__FUNCTION__."Validation failed !");
            return returnResponse($validator->errors(), HttpStatus::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($authenticate == 1) {
            DB::table('user_to_address')->where('user_id', $user_id)
                ->update(['is_default' => 0]);

			DB::table('user_to_address')
				->where('user_id', $user_id)
				->where('address_book_id', $address_book_id)
                ->update(['is_default' => 1]);

            return returnResponse("Default address has been changed successfully!", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS);
        } 
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
    }

    public static function getTaxRate($request)
    {

        $tax_zone_id = $request->tax_zone_id;
        $consumer_data = getallheaders();
      /*
      $consumer_data['consumer_key'] = $request->header('consumer_key');
      $consumer_data['consumer_secret'] = $request->header('consumer_secret');
      $consumer_data['consumer_nonce'] = $request->header('consumer_nonce');
      $consumer_data['consumer_device_id'] = $request->header('consumer_device_id');
      */
      $consumer_data['consumer_ip'] = $request->ip();
        $consumer_data['consumer_url'] = __FUNCTION__;
        $authController = new AppSettingController();
        $authenticate = $authController->apiAuthenticate($consumer_data);
        $validator = Validator::make($request->all(), [
            'products.*' => 'required',
        ]);
        
        if ($validator->fails()) {
            Log::error(__CLASS__."::".__FUNCTION__."Validation failed !");
            return returnResponse($validator->errors(), HttpStatus::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($authenticate == 1) {

            $index = '0';
            $total_tax = '0';
            foreach ($request->products as $products_data) {
                $final_price = $request->products[$index]['final_price'];
                $products = DB::table('products')
                    ->LeftJoin('tax_rates', 'tax_rates.tax_class_id', '=', 'products.products_tax_class_id')
                    ->where('tax_rates.tax_zone_id', $tax_zone_id)
                    ->where('products_id', $products_data['products_id'])->get();

                $tax_value = $products[0]->tax_rate / 100 * $final_price;
                $total_tax = $total_tax + $tax_value;
                $index++;
            }
            if ($total_tax > 0) {
                $rate = $total_tax;
            } else {
                $rate = '0';
            }

            return returnResponse("Tax rate is returned !", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $rate);
        } 
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
    }

    public static function countries($request)
    {
        $consumer_data = getallheaders();
      /*
      $consumer_data['consumer_key'] = $request->header('consumer_key');
      $consumer_data['consumer_secret'] = $request->header('consumer_secret');
      $consumer_data['consumer_nonce'] = $request->header('consumer_nonce');
      $consumer_data['consumer_device_id'] = $request->header('consumer_device_id');
      */
      $consumer_data['consumer_ip'] = $request->ip();
        $consumer_data['consumer_url'] = __FUNCTION__;
        $authController = new AppSettingController();
        $authenticate = $authController->apiAuthenticate($consumer_data);

        if ($authenticate == 1) {

            $countries = DB::table('countries')->get();

            //$responseData = array('success' => '1', 'data' => $countries, 'message' => "Returned all countries.");
            return returnResponse("Returned all countries. !", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $countries);
        } 
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
    }

    public static function zones($request)
    {
        $consumer_data = getallheaders();
      /*
      $consumer_data['consumer_key'] = $request->header('consumer_key');
      $consumer_data['consumer_secret'] = $request->header('consumer_secret');
      $consumer_data['consumer_nonce'] = $request->header('consumer_nonce');
      $consumer_data['consumer_device_id'] = $request->header('consumer_device_id');
      */
      $consumer_data['consumer_ip'] = $request->ip();
        $consumer_data['consumer_url'] = __FUNCTION__;
        $authController = new AppSettingController();
        $authenticate = $authController->apiAuthenticate($consumer_data);
        $validator = Validator::make($request->all(), [
            'zone_country_id' => 'required',
        ]);
        
        if ($validator->fails()) {
            Log::error(__CLASS__."::".__FUNCTION__."Validation failed !");
            return returnResponse($validator->errors(), HttpStatus::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($authenticate == 1) {
            $zone_country_id = $request->zone_country_id;
            $zones = DB::table('zones')->where('zone_country_id', $zone_country_id)->get();

            return returnResponse("Returned all states. !", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $zones);
        } 
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
            
    }
    
    
     public static function getdefaultaddress($request)
    {
        $user_id = auth()->user()->id;
        $consumer_data = getallheaders();
      /*
      $consumer_data['consumer_key'] = $request->header('consumer_key');
      $consumer_data['consumer_secret'] = $request->header('consumer_secret');
      $consumer_data['consumer_nonce'] = $request->header('consumer_nonce');
      $consumer_data['consumer_device_id'] = $request->header('consumer_device_id');
      */
      $consumer_data['consumer_ip'] = $request->ip();
        $consumer_data['consumer_url'] = __FUNCTION__;
        $authController = new AppSettingController();
        $authenticate = $authController->apiAuthenticate($consumer_data);

        if ($authenticate == 1) {

            $addresses = DB::table('user_to_address')
                ->leftjoin('address_book', 'address_book.address_book_id', '=', 'user_to_address.address_book_id')
                ->leftJoin('countries', 'countries.countries_id', '=', 'address_book.entry_country_id')
                ->leftJoin('zones', 'zones.zone_id', '=', 'address_book.entry_zone_id')
                ->select(
                    'user_to_address.is_default as default_address',
                    'address_book.address_book_id as address_id',
                    'address_book.entry_gender as gender',
                    'address_book.entry_company as company',
                    'address_book.entry_firstname as firstname',
                    'address_book.entry_lastname as lastname',
                    'address_book.entry_street_address as street',
                    'address_book.entry_suburb as suburb',
                    'address_book.entry_postcode as postcode',
                    'address_book.entry_city as city',
                    'address_book.entry_state as state',
                    'address_book.entry_latitude as latitude',
                    'address_book.entry_longitude as longitude',
                    'address_book.phone as phone',

                    'countries.countries_id as countries_id',
                    'countries.countries_name as country_name',

                    'zones.zone_id as zone_id',
                    'zones.zone_code as zone_code',
                    'zones.zone_name as zone_name'
                )->where('user_to_address.is_default',1)
                ->where('address_book.is_user_address', '=',  'N')
                ->where('user_to_address.user_id', $user_id)->first();

            if (isset($addresses->address_id)) {
               // $addresses_data = $addresses;
               // $responseData = array('success' => '1', 'data' => $addresses_data, 'message' => "Return shipping addresses successfully");
                return returnResponse("Return shipping addresses successfully", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $addresses);
            } else {
                //$addresses_data = array();
                //$responseData = array('success' => '0', 'data' => $addresses_data, 'message' => "Addresses are not added yet.");
                return returnResponse("Addresses are not added yet.", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $addresses);
            }
        } 
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
    }
    
    
}
