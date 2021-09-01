<?php

namespace App\Models\AppModels;

use App\Http\Controllers\App\AlertController;
use App\Http\Controllers\App\AppSettingController;
use Auth;
use DB;
use File;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Log;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\HttpStatus;
use Validator;
use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTFactory;
use App\Models\AppModels\CustomerLogin;
use App\Models\Core\SmsService;
use Illuminate\Support\Str;
use App\Models\Core\User;
use App\Models\Core\OtpHistory;
use App\Models\Core\UserDirectIncome;
use App\Models\AppModels\Cart;

use App\Models\Eloquent\Customers;
class Customer extends Model
{

    public static function googleAuth($request) {
        Log::debug(__CLASS__ . "::" . __FUNCTION__ . " called");
        $consumer_data = getallheaders();
        $consumer_data['consumer_ip'] = $request->ip();
        $consumer_data['consumer_url'] = __FUNCTION__;
        $authController = new AppSettingController();
        $authenticate = $authController->apiAuthenticate($consumer_data);
        $validator = Validator::make($request->all(), [
                    'email_id' => 'required',
                    'access_token' => 'nullable',
                    'id_token' => 'nullable',
                    'name' => 'nullable',
                    'picture' => 'nullable',
                    'google_id' => 'nullable',
                    'is_new_user' => 'nullable',
        ]);

        if ($validator->fails()) {
            Log::error(__CLASS__ . "::" . __FUNCTION__ . " Login failed ! Validation failed !");
            return returnResponse($validator->errors(), HttpStatus::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($authenticate == 1) {
            Log::debug(__CLASS__ . "::" . __FUNCTION__ . " Authenticate = $authenticate");
            $emailId = $request->email_id;
            $accessToken = $request->access_token;
            $idToken = $request->id_token;
            $name = $request->name;
            $picture = $request->picture;
            $googleId = $request->google_id;
            $isNewUser = $request->is_new_user;

            $deviceId = "";
            $appVersion = "";
            $appDeviceManufacturer = "";
            $appDeviceModel = "";
            if ($request->has('consumer_device_id')) {
                $deviceId = $request->consumer_device_id;
            }
            if ($request->has('device_app_version')) {
                $appVersion = $request->device_app_version;
            }
            if ($request->has('device_model')) {
                $appDeviceModel = $request->device_model;
            }
            if ($request->has('device_manufacturer')) {
                $appDeviceManufacturer = $request->device_manufacturer;
            }

            Log::debug(__CLASS__ . "::" . __FUNCTION__ . " Email Id : $emailId, Access Token : $accessToken, Id Token : $idToken");
            Log::debug(__CLASS__ . "::" . __FUNCTION__ . " Name : $name, Picture : $picture, Google Id : $googleId, Is New User : $isNewUser");

            try {
                //$existUser = Users::where('email', $emailId)->first();
                $existUser = self::getUserDataByEmail($emailId);
                if (isset($existUser->id)) {
                    Log::debug(__CLASS__ . "::" . __FUNCTION__ . " User Exists");
                    if ($existUser->status == 1) {
                        Log::debug(__CLASS__ . "::" . __FUNCTION__ . " User status = 1");
                        $user = CustomerLogin::where('id', $existUser->id)->where('status', '1')->first();
                        Log::debug($user);
                        if ($user and $token = JWTAuth::fromUser($user)) {
                            $existUser->mobile_no_force_update = false;
                            $app_version = $request->device_app_version;
                            Log::debug(__CLASS__." :: ".__FUNCTION__." device app version found as ".$app_version);
                            $c_version = '1.1.2';
                            Log::debug(__CLASS__." :: ".__FUNCTION__." lets compare the current app version with device app version!!");
                            if(version_compare($app_version, $c_version, '>=')){
                              $existUser->mobile_no_force_update = true;
                            }
                            
                            $existUser->is_imps_allowed = true; // later we will use a method to fetch the status on various dependency
                            if($existUser->is_active =='YES'){
                                $existUser->min_imps_amount = 10; // later we will use a method to fetch the config based on transaction done by customer
                                $existUser->max_imps_amount = 10000; // later we will use a method to fetch the config based on transaction done by customer
                            }
                            else{
                                $existUser->min_imps_amount = 500; // later we will use a method to fetch the config based on transaction done by customer
                                $existUser->max_imps_amount = 1000; // later we will use a method to fetch the config based on transaction done by customer
                            }
                            
                            $customers_id = $existUser->id;
                            $now = Carbon::now();
                            $data = array(
                                'access_token' => $token,
                                'token_type' => 'Bearer',
                                'expires_in' => JWTFactory::getTTL() * 60 * 60 * 7,
                                'last_login' => substr($now, 0, strlen($now)),
                                'user' => $existUser,
                                'is_new' => 'N',
                            );
                            Log::debug('Returning Data = ');
                            Log::debug($data);
                            self::updateLastLogin($customers_id, $consumer_data['consumer_ip'], $deviceId);
                            self::checkWhoIsOnline($customers_id, $name);

                            return returnResponse("User Already Exists !", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $data);
                        } else {
                            return returnResponse("Unauthorized !", HttpStatus::HTTP_UNAUTHORIZED, HttpStatus::HTTP_ERROR);
                        }
                    } else {
                        return returnResponse("Account is deactivated !");
                    }
                } else {
                    Log::debug(__CLASS__ . " :: " . __FUNCTION__ . " New user request");
                    

                            return returnResponse("User does not exists !", 204);
                        }
                    
                }
             catch (JWTException $exc) {
                Log::error(__CLASS__ . "::" . __FUNCTION__ . " Exception : " . $exc->getMessage());
                return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
            }
        }
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
    }
    
    
    
    public static function googleAuthRegister($request) {
        Log::debug(__CLASS__ . "::" . __FUNCTION__ . " called");
        $consumer_data = getallheaders();
        $consumer_data['consumer_ip'] = $request->ip();
        $consumer_data['consumer_url'] = __FUNCTION__;
        $authController = new AppSettingController();
        $authenticate = $authController->apiAuthenticate($consumer_data);
        $validator = Validator::make($request->all(), [
                    'email_id' => 'required',
                    'access_token' => 'nullable',
                    'id_token' => 'nullable',
                    'name' => 'nullable',
                    'picture' => 'nullable',
                    'google_id' => 'nullable',
                    'is_new_user' => 'nullable',
                    'referral_code' => 'nullable',
                    'mobile_no' => 'required|string|between:10,12',
                    'dob' => 'required|date',
                    'city' => 'required',
                    'pin_code' => 'required|digits:6',
        ]);

        if ($validator->fails()) {
            Log::error(__CLASS__ . "::" . __FUNCTION__ . " Login failed ! Validation failed !");
            return returnResponse($validator->errors(), HttpStatus::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($authenticate == 1) {
            Log::debug(__CLASS__ . "::" . __FUNCTION__ . " Authenticate = $authenticate");
            $emailId = $request->email_id;
            $accessToken = $request->access_token;
            $idToken = $request->id_token;
            $name = $request->name;
            $picture = $request->picture;
            $googleId = $request->google_id;
            $isNewUser = $request->is_new_user;
            $phone = $request->mobile_no;
            $dob = $request->dob;
            $city = $request->city;
            $pin_code = $request->pin_code;

            $deviceId = "";
            $appVersion = "";
            $appDeviceManufacturer = "";
            $appDeviceModel = "";
            if ($request->has('consumer_device_id')) {
                $deviceId = $request->consumer_device_id;
            }
            if ($request->has('device_app_version')) {
                $appVersion = $request->device_app_version;
            }
            if ($request->has('device_model')) {
                $appDeviceModel = $request->device_model;
            }
            if ($request->has('device_manufacturer')) {
                $appDeviceManufacturer = $request->device_manufacturer;
            }

            Log::debug(__CLASS__ . "::" . __FUNCTION__ . " Email Id : $emailId, Access Token : $accessToken, Id Token : $idToken");
            Log::debug(__CLASS__ . "::" . __FUNCTION__ . " got request data as Name : $name, Picture : $picture, Google Id : $googleId, Is New User : $isNewUser mobile no $phone city $city dob $dob pincode $pin_code");

            try {
                //$existUser = Users::where('email', $emailId)->first();
                $existUser = self::getUserDataByEmail($emailId);
                if (isset($existUser->id)) {
                    Log::debug(__CLASS__ . "::" . __FUNCTION__ . " User Exists");
                     {
                        return returnResponse("User Already Exists please Login !");
                    }
                } else {
                    Log::debug(__CLASS__ . " :: " . __FUNCTION__ . " cerating new user in system");
                    $referred_by = null;
                    Log::debug(__CLASS__ . " :: " . __FUNCTION__ . " default referral code set as $referred_by");
                    if ($request->has('referral_code') and ! empty($request->referral_code)) {
                        Log::debug(__CLASS__ . " :: " . __FUNCTION__ . " fetching code referral code which is $request->referral_code");
                        $referred_by_new = self::getCoreIdFromMemberCode($request->referral_code);
                        Log::debug(__CLASS__ . " :: " . __FUNCTION__ . " referral code found from databse id $referred_by_new");
                        if ($referred_by_new) {
                            Log::debug(__CLASS__ . " :: " . __FUNCTION__ . " referral code found as $referred_by_new");
                            Log::debug(__CLASS__ . " :: " . __FUNCTION__ . " Updating !!");
                            $referred_by = $referred_by_new;
                            Log::debug(__CLASS__ . " :: " . __FUNCTION__ . " referred_by updated as $referred_by !!");
                        } else {
                            Log::error(__CLASS__ . " :: " . __FUNCTION__ . " invalid referral code $request->referral_code");
                            return returnResponse("Invalid Referral Code !!");
                        }
                    }
                    Log::debug(__CLASS__ . " :: " . __FUNCTION__ . " generating password");
                    $password = rand(10000000, 99999999);
                    Log::debug(__CLASS__ . " :: " . __FUNCTION__ . " starting DB transaction");
                    \DB::beginTransaction();
                    Log::debug(__CLASS__ . " :: " . __FUNCTION__ . " starting user object for db insertion");


                    $member_code = self::generateReferralToken();
                    if (empty($member_code)) {
                        Log::error("Member code generating failed !");
                        return returnResponse("Member code generating failed !");
                    }

                   
                    Log::debug(__CLASS__ . " :: " . __FUNCTION__ . " preparing customer data for save");
                    $customer = new Customers;
                    $customer->member_code = $member_code;
                    $customer->parent_id = $referred_by;
                    $customer->referred_by = $referred_by;
                    $customer->name = $name;
                    $customer->avatar = $picture;
                    $customer->email = strtolower($emailId);
                    $customer->google_id = $googleId;
                    $customer->google_access_token = $accessToken;
                    $customer->google_id_token = $idToken;
                    $customer->device_ip = $consumer_data['consumer_ip'];
                    $customer->device_id = $deviceId;
                    $customer->device_app_version = $appVersion;
                    $customer->device_app_manufacturer = $appDeviceManufacturer;
                    $customer->device_app_model = $appDeviceModel;
                    $customer->phone = $phone;
                    $customer->dob = $dob;
                    $customer->city = $city;
                    $customer->pin_code = $pin_code;
                    Log::debug(__CLASS__ . " :: " . __FUNCTION__ . " saving customer data");
                    if (!$customer->save()) {
                        Log::error(__CLASS__ . " :: " . __FUNCTION__ . " customer data saving failed !!");
                        return returnResponse("User registration failed !");
                    }
                    // code for updating the level & direct team of referral customer

                    $customerData = $customer->id;
                    Log::debug(__CLASS__."::".__FUNCTION__." Updating Team Count ");
                    if(!self::updateTeamCount($customer)){
                         Log::error(__CLASS__."::".__FUNCTION__." Error Updating Team count for with customer id $customer->id ");
                        return returnResponse('Error Updating Team count');
                    }
                    Log::debug(__CLASS__."::".__FUNCTION__." Updating Sponsor Count and active level");
                    if(!self::updateSponsorCount($customer)){
                         Log::error(__CLASS__."::".__FUNCTION__." Error Updating Sponsor Count and active level for with customer id $customer->id ");
                        return returnResponse('Error Updating Sponsor Count');
                    }
                    
                    Log::debug("Customer Id generated as " . $customerData);
                    $customers_id = $customerData;

                    Log::debug(__CLASS__ . " :: " . __FUNCTION__ . " fetching customer info");
                    //update record of customers_info
                    $existUserInfo = DB::table('customers_info')->where('customers_info_id', $customers_id)->get();

                    $customers_info_id = $customers_id;
                    $customers_info_date_of_last_logon = date('Y-m-d H:i:s');
                    $customers_info_number_of_logons = '1';
                    $customers_info_date_account_created = date('Y-m-d H:i:s');
                    $global_product_notifications = '1';

                    if (count($existUserInfo) > 0) {
                        //update customers_info table
                        DB::table('customers_info')->where('customers_info_id', $customers_info_id)->update([
                            'customers_info_date_of_last_logon' => $customers_info_date_of_last_logon,
                            'global_product_notifications' => $global_product_notifications,
                            'customers_info_number_of_logons' => DB::raw('customers_info_number_of_logons + 1'),
                        ]);
                    } else {
                        //insert customers_info table
                        DB::table('customers_info')->insertGetId(
                                ['customers_info_id' => $customers_info_id,
                                    'customers_info_date_of_last_logon' => $customers_info_date_of_last_logon,
                                    'customers_info_number_of_logons' => $customers_info_number_of_logons,
                                    'customers_info_date_account_created' => $customers_info_date_account_created,
                                    'global_product_notifications' => $global_product_notifications,
                                ]
                        );
                    }
                    DB::commit();
                    Log::debug('Data committed...');
                    $existUser = self::getUserDataById($customers_id);
                    //$existUser = DB::table('users')->where('id', $user->id)->first();
                    if ($existUser) {
                        Log::debug('New user Inserted Now login');
                        $userLogin = CustomerLogin::where('id', $existUser->id)->where('status', '1')->first();
                        if ($userLogin and $token = JWTAuth::fromUser($userLogin)) {
                            Log::debug('New user login');
                            $existUser->mobile_no_force_update = false;
                            $app_version = $request->device_app_version;
                            Log::debug(__CLASS__." :: ".__FUNCTION__." device app version found as ".$app_version);
                            $c_version = '1.1.2';
                            Log::debug(__CLASS__." :: ".__FUNCTION__." lets compare the current app version with device app version!!");
                            if(version_compare($app_version, $c_version, '>=')){
                              $existUser->mobile_no_force_update = true;
                            }
                            $existUser->is_imps_allowed = true; // later we will use a method to fetch the status on various dependency
                            if($existUser->is_active =='YES'){
                                $existUser->min_imps_amount = 10; // later we will use a method to fetch the config based on transaction done by customer
                                $existUser->max_imps_amount = 10000; // later we will use a method to fetch the config based on transaction done by customer
                            }
                            else{
                                $existUser->min_imps_amount = 500; // later we will use a method to fetch the config based on transaction done by customer
                                $existUser->max_imps_amount = 1000; // later we will use a method to fetch the config based on transaction done by customer
                            }
                            
                            $now = Carbon::now();
                            $data = array(
                                'access_token' => $token,
                                'token_type' => 'Bearer',
                                'expires_in' => JWTFactory::getTTL() * 60 * 60 * 7,
                                'last_login' => substr($now, 0, strlen($now)),
                                'user' => $existUser,
                                'is_new' => 'Y',
                            );
                            Log::debug('Returning Data = ');
                            Log::debug($data);
                            self::updateLastLogin($existUser->id, $consumer_data['consumer_ip'], $deviceId);
                            self::checkWhoIsOnline($existUser->id, $name);

                            return returnResponse("User Created !", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $data);
                        }
                    }
                }
            } catch (JWTException $exc) {
                Log::error(__CLASS__ . "::" . __FUNCTION__ . " Exception : " . $exc->getMessage());
                return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
            }
        }
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
    }

    public static function getCoreIdFromMemberCode($member_code) {
        Log::debug(__CLASS__ . " :: " . __FUNCTION__ . " fetching core id of customer with member code $member_code");
        try {
            //$referral_data = DB::table('customers')->select('customers_id', 'member_code')->where('member_code', $member_code)->first();
            $referral_data = Customers::select('id', 'member_code')->where('member_code', $member_code)->first();
            if (isset($referral_data->member_code) && $referral_data->member_code == $member_code) {
                return $referral_data->id;
            }
        } catch (Exception $e) {
            Log::error(__CLASS__ . "::" . __FUNCTION__ . " Exception : " . $exc->getMessage());
        }
        return false;
    }

    public static function getCoreIdFromUserId($user_id) {
        Log::debug(__CLASS__ . " :: " . __FUNCTION__ . " fetching core id of customer with user id $user_id");
        try {
            //$referral_data = DB::table('customers')->select('customers_id', 'member_code')->where('member_code', $member_code)->first();
            $referral_data = Customers::select('id', 'user_id')->where('user_id', $user_id)->first();
            if (isset($referral_data->user_id) && $referral_data->user_id == $user_id) {
                return $referral_data->id;
            }
        } catch (Exception $e) {
            Log::error(__CLASS__ . "::" . __FUNCTION__ . " Exception : " . $exc->getMessage());
        }
        return false;
    }

    private static function getUserDataById($id) {
        return DB::table('customers')
                        ->where('id', $id)->first();
    }

    private static function getUserDataByEmail($emailId) {
        return DB::table('customers')
                        ->where('email', $emailId)->first();
    }

    private static function updateLastLogin($customers_id, $ip, $device_id) {
        DB::table('customers')
                ->where('id', $customers_id)
                ->update([
                    'last_login_time' => date('Y-m-d H:i:s'),
                    'last_login_ip' => $ip,
                    'last_login_device_id' => $device_id,
        ]);
    }

    private static function checkWhoIsOnline($customers_id, $name) {
        //check if already login or not
        $already_login = DB::table('whos_online')->where('customer_id', '=', $customers_id)->get();

        if (count($already_login) > 0) {
            DB::table('whos_online')
                    ->where('customer_id', $customers_id)
                    ->update([
                        'full_name' => $name,
                        'time_entry' => date('Y-m-d H:i:s'),
            ]);
        } else {

            DB::table('whos_online')
                    ->insert([
                        'full_name' => $name,
                        'time_entry' => date('Y-m-d H:i:s'),
                        'customer_id' => $customers_id,
            ]);
        }
    }
    
    
    ####################################################################################################################
    
    public static function processlogin($request)
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
            'mobile' => 'required|numeric|digits:10',
            'password' => 'required|string|min:4',
        ]);
        
        if ($validator->fails()) {
            Log::error(__CLASS__."::".__FUNCTION__." Login failed ! Validation failed !");
            return returnResponse($validator->errors(), HttpStatus::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($authenticate == 1) {

            $mobile = $request->mobile;
            $password = $request->password;

            $customerInfo = array("phone" => $mobile, "password" => $password, 'role_id' => 2);
             try {
            if ($token = JWTAuth::attempt($customerInfo)) {

                $existUser = DB::table('users')
                    ->where('phone', $mobile)->where('status', '1')->get();

                if (count($existUser) > 0) {

                    $customers_id = $existUser[0]->id;

                    //update record of customers_info
                    $existUserInfo = DB::table('customers_info')->where('customers_info_id', $customers_id)->get();
                    $customers_info_id = $customers_id;
                    $customers_info_date_of_last_logon = date('Y-m-d H:i:s');
                    $customers_info_number_of_logons = '1';
                    $customers_info_date_account_created = date('Y-m-d H:i:s');
                    $global_product_notifications = '1';

                    if (count($existUserInfo) > 0) {
                        //update customers_info table
                        DB::table('customers_info')->where('customers_info_id', $customers_info_id)->update([
                            'customers_info_date_of_last_logon' => $customers_info_date_of_last_logon,
                            'global_product_notifications' => $global_product_notifications,
                            'customers_info_number_of_logons' => DB::raw('customers_info_number_of_logons + 1'),
                        ]);

                    } else {
                        //insert customers_info table
                        $customers_default_address_id = DB::table('customers_info')->insertGetId(
                            ['customers_info_id' => $customers_info_id,
                                'customers_info_date_of_last_logon' => $customers_info_date_of_last_logon,
                                'customers_info_number_of_logons' => $customers_info_number_of_logons,
                                'customers_info_date_account_created' => $customers_info_date_account_created,
                                'global_product_notifications' => $global_product_notifications,
                            ]
                        );

                        DB::table('users')->where('id', $customers_id)->update([
                            'default_address_id' => $customers_default_address_id,
                        ]);
                    }

                    //check if already login or not
                    $already_login = DB::table('whos_online')->where('customer_id', '=', $customers_id)->get();

                    if (count($already_login) > 0) {
                        DB::table('whos_online')
                            ->where('customer_id', $customers_id)
                            ->update([
                                'full_name' => $existUser[0]->first_name . ' ' . $existUser[0]->last_name,
                                'time_entry' => date('Y-m-d H:i:s'),
                            ]);
                    } else {
                        DB::table('whos_online')
                            ->insert([
                                'full_name' => $existUser[0]->first_name . ' ' . $existUser[0]->last_name,
                                'time_entry' => date('Y-m-d H:i:s'),
                                'customer_id' => $customers_id,
                            ]);
                    }

                    //get liked products id
                    $products = DB::table('liked_products')->select('liked_products_id as products_id')
                        ->where('liked_customers_id', '=', $customers_id)
                        ->get();

                    if (count($products) > 0) {
                        $liked_products = $products;
                    } else {
                        $liked_products = array();
                    }

                    $existUser[0]->liked_products = $products;

                    //$responseData = array('success' => '1', 'data' => $existUser, 'message' => 'Data has been returned successfully!');
                     $now = Carbon::now();
                    $data = array(
                        'access_token' => $token,
                        'token_type' => 'Bearer',
                        'expires_in' => JWTFactory::getTTL() * 60,
                        'last_login' => substr($now, 0, strlen($now)),
                        'user' => $existUser,
                        //'studentList' => StudentModel::getStudentList(auth()->user()->id),
                    );
       
                //Log::debug($data);
                return returnResponse("Login Success !", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $data);

                } else {
                    //$responseData = array('success' => '0', 'data' => array(), 'message' => "Your account has been deactivated.");
                    return returnResponse("Your account has been deactivated.", HttpStatus::HTTP_UNAUTHORIZED);
                }
            } else {
                Log::error(__CLASS__."::".__FUNCTION__." Login attempt failed !");
                return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);

            }
        
        }
        catch (JWTException $exc) {
            Log::error(__CLASS__."::".__FUNCTION__." Exception : ".$exc->getMessage());
            return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
        }
        } 
            return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
        
       
    }

    public static function processregistration($request)
    {
        $validator = Validator::make($request->all(), [
            'customers_firstname' => 'required|string|between:2,100',
            'customers_lastname' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'customers_telephone' => 'required|string|between:10,12|unique:users,phone',
            'password' => 'required|string|min:6',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), HttpStatus::HTTP_BAD_REQUEST);
        }
        
        $customers_firstname = $request->customers_firstname;
        $customers_lastname = $request->customers_lastname;
        $email = $request->email;
        $password = $request->password;
        $customers_telephone = $request->customers_telephone;
        $customers_info_date_account_created = date('y-m-d h:i:s');

        $consumer_data = getallheaders();
      /*
      $consumer_data['consumer_key'] = $request->header('consumer_key');
      $consumer_data['consumer_secret'] = $request->header('consumer_secret');
      $consumer_data['consumer_nonce'] = $request->header('consumer_nonce');
      $consumer_data['consumer_device_id'] = $request->header('consumer_device_id');
      */
      $consumer_data['consumer_ip'] = $request->ip();
       // $consumer_data['consumer_ip'] = request()->header('consumer-ip');
        $consumer_data['consumer_url'] = __FUNCTION__;
        $authController = new AppSettingController();
        $authenticate = $authController->apiAuthenticate($consumer_data);

        $extensions = array('gif', 'jpg', 'jpeg', 'png');

        if ($authenticate == 1) {

            //check email existance
            $existUser = DB::table('users')->where('phone', $customers_telephone)->get();

            if (count($existUser) > 0) {
                //response if email already exit
                //$responseData = array('success' => '0', 'data' => $postData, 'message' => "Email address is already exist");
                return returnResponse("Mobile No. is already exist!", HttpStatus::HTTP_BAD_REQUEST);
            } else {

                //insert data into customer
                $customers_id = DB::table('users')->insertGetId([
                    'role_id' => 2,
                    'first_name' => $customers_firstname,
                    'last_name' => $customers_lastname,
                    'phone' => $customers_telephone,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'status' => '1',
                    'created_at' => date('y-m-d h:i:s'),
                ]);

                $userData = DB::table('users')
                    ->where('users.id', '=', $customers_id)->where('status', '1')->get();
                //$responseData = array('success' => '1', 'data' => $userData, 'message' => "Sign Up successfully!");
                return returnResponse("Sign Up successfully!", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $userData);
            }

        } 
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
    }

    public static function notify_me($request)
    {
        $device_id = $request->device_id;
        $is_notify = $request->is_notify;
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

            $devices = DB::table('devices')->where('device_id', $device_id)->get();
            if (!empty($devices[0]->customers_id)) {
                $customers = DB::table('users')->where('id', $devices[0]->customers_id)->get();

                if (count($customers) > 0) {

                    foreach ($customers as $customers_data) {

                        DB::table('devices')->where('user_id', $customers_data->customers_id)->update([
                            'is_notify' => $is_notify,
                        ]);
                    }

                }
            } else {

                DB::table('devices')->where('device_id', $device_id)->update([
                    'is_notify' => $is_notify,
                ]);
            }

            $responseData = array('success' => '1', 'data' => '', 'message' => "Notification setting has been changed successfully!");
        } else {
            $responseData = array('success' => '0', 'data' => array(), 'message' => "Unauthenticated call.");
        }
        $categoryResponse = json_encode($responseData);

        return $categoryResponse;
    }

    public static function updatecustomerinfo($request)
    {
        $customers_id            			=   $request->customers_id;
        $customers_firstname            	=   $request->customers_firstname;
        $customers_lastname           		=   $request->customers_lastname;
        $customers_telephone          		=   $request->customers_telephone;
        $customers_gender          		   	=   $request->customers_gender;
        $customers_dob          		   		=   $request->customers_dob;
        $customers_info_date_account_last_modified 	=   date('y-m-d h:i:s');
        $consumer_data = getallheaders();
      /*
      $consumer_data['consumer_key'] = $request->header('consumer_key');
      $consumer_data['consumer_secret'] = $request->header('consumer_secret');
      $consumer_data['consumer_nonce'] = $request->header('consumer_nonce');
      $consumer_data['consumer_device_id'] = $request->header('consumer_device_id');
      */
      $consumer_data['consumer_ip'] = $request->ip();
        $consumer_data['consumer_url']  	  =  __FUNCTION__;
        $authController = new AppSettingController();
        $authenticate = $authController->apiAuthenticate($consumer_data);

        if($authenticate==1){
        $cehckexist = DB::table('users')->where('id', $customers_id)->where('role_id', 2)->first();
            if($cehckexist){

                $customer_data = array(
                    'role_id' => 2,
                    'first_name'			 =>  $customers_firstname,
                    'last_name'			 =>  $customers_lastname,
                    'phone'			 =>  $customers_telephone,
                    'gender'				 =>  $customers_gender,
                    'dob'					 =>  $customers_dob,
                );


            //update into customer
            DB::table('users')->where('id', $customers_id)->update($customer_data);

            DB::table('customers_info')->where('customers_info_id', $customers_id)->update(['customers_info_date_account_last_modified'   =>   $customers_info_date_account_last_modified]);

            $userData = DB::table('users')
                ->select('users.*')->where('users.id', '=', $customers_id)->where('status', '1')->get();

            $responseData = array('success'=>'1', 'data'=>$userData, 'message'=>"Customer information has been Updated successfully");


            }else{
            $responseData = array('success'=>'3', 'data'=>array(),  'message'=>"Record not found.");
            }

        }else{
            $responseData = array('success'=>'0', 'data'=>array(),  'message'=>"Unauthenticated call.");
        }
        $userResponse = json_encode($responseData);

        return $userResponse;
    }


    public static function updatepassword($request)
    {
    $customers_id            					=   $request->customers_id;
    $customers_info_date_account_last_modified 	=   date('y-m-d h:i:s');
    $consumer_data = getallheaders();
      /*
      $consumer_data['consumer_key'] = $request->header('consumer_key');
      $consumer_data['consumer_secret'] = $request->header('consumer_secret');
      $consumer_data['consumer_nonce'] = $request->header('consumer_nonce');
      $consumer_data['consumer_device_id'] = $request->header('consumer_device_id');
      */
      $consumer_data['consumer_ip'] = $request->ip();
    $consumer_data['consumer_url']  	  =  __FUNCTION__;
    $authController = new AppSettingController();
    $authenticate = $authController->apiAuthenticate($consumer_data);


    if($authenticate==1){
        $cehckexist = DB::table('users')->where('id', $customers_id)->where('role_id', 2)->first();
            if($cehckexist){
                $oldpassword    = $request->oldpassword;
                $newPassword    = $request->newpassword;

                $content = DB::table('users')->where('id', $customers_id)->first();

                $customerInfo = array("email" => $cehckexist->email, "password" => $oldpassword);

                if (Auth::attempt($customerInfo)) {

                    DB::table('users')->where('id', $customers_id)->update([
                    'password'			 =>  Hash::make($newPassword)
                    ]);

                    //get user data
                    $userData = DB::table('users')
                        ->select('users.*')
                        ->where('users.id', '=', $customers_id)->where('status', '1')->get();
                    $responseData = array('success'=>'1', 'data'=>$userData, 'message'=>"Information has been Updated successfully");
                }else{
                    $responseData = array('success'=>'2', 'data'=>array(),  'message'=>"current password does not match.");
                }
        }else{
            $responseData = array('success'=>'3', 'data'=>array(),  'message'=>"Record not found.");
        }

        }else{
            $responseData = array('success'=>'0', 'data'=>array(),  'message'=>"Unauthenticated call.");
        }

        $userResponse = json_encode($responseData);
        return $userResponse;
    }

    public static function processforgotpassword($request)
    {

        $email = $request->email;
        $postData = array();

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

            //check email exist
            $existUser = DB::table('users')->where('email', $email)->get();

            if (count($existUser) > 0) {
                $password = substr(md5(uniqid(mt_rand(), true)), 0, 8);

                DB::table('users')->where('email', $email)->update([
                    'password' => Hash::make($password),
                ]);

                $existUser[0]->password = $password;

                $myVar = new AlertController();
                $alertSetting = $myVar->forgotPasswordAlert($existUser);
                $responseData = array('success' => '1', 'data' => $postData, 'message' => "Your password has been sent to your email address.");
            } else {
                $responseData = array('success' => '0', 'data' => $postData, 'message' => "Email address doesn't exist!");
            }
        } else {
            $responseData = array('success' => '0', 'data' => array(), 'message' => "Unauthenticated call.");
        }
        $userResponse = json_encode($responseData);

        return $userResponse;
    }

    public static function facebookregistration($request)
    {
        require_once app_path('vendor/autoload.php');
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
            //get function from other controller
            $myVar = new AppSettingController();
            $setting = $myVar->getSetting();

            $password = substr(md5(uniqid(mt_rand(), true)), 0, 8);
            $access_token = $request->access_token;

            $fb = new \Facebook\Facebook([
                'app_id' => $setting['facebook_app_id'],
                'app_secret' => $setting['facebook_secret_id'],
                'default_graph_version' => 'v2.2',
            ]);

            try {
                $response = $fb->get('/me?fields=id,name,email,first_name,last_name,gender,public_key', $access_token);
            } catch (Facebook\Exceptions\FacebookResponseException $e) {
                echo 'Graph returned an error: ' . $e->getMessage();
            } catch (Facebook\Exceptions\FacebookSDKException $e) {
                echo 'Facebook SDK returned an error: ' . $e->getMessage();
            }

            $user = $response->getGraphUser();

            $fb_id = $user['id'];
            $customers_firstname = $user['first_name'];
            $customers_lastname = $user['last_name'];
            $name = $user['name'];
            if (empty($user['gender']) or $user['gender'] == 'male') {
                $customers_gender = '0';
            } else {
                $customers_gender = '1';
            }
            if (!empty($user['email'])) {
                $email = $user['email'];
            } else {
                $email = '';
            }

            //user information
            $fb_data = array(
                'fb_id' => $fb_id,
            );
            $customer_data = array(
                'role_id' => 2,
                'first_name' => $customers_firstname,
                'last_name' => $customers_lastname,
                'email' => $email,
                'password' => Hash::make($password),
                'status' => '1',
                'created_at' => date('Y-m-d H:i:s'),
            );

            $existUser = DB::table('customers')->where('fb_id', '=', $fb_id)->get();
            if (count($existUser) > 0) {

                $customers_id = $existUser[0]->customers_id;
                $success = "2";
                $message = "Customer record has been updated.";
                //update data of customer
                DB::table('customers')->where('user_id', '=', $customers_id)->update($fb_data);
            } else {
                $success = "1";
                $message = "Customer account has been created.";
                //insert data of customer
                $customers_id = DB::table('users')->insertGetId($customer_data);
                DB::table('customers')->insertGetId([
                    'fb_id' => $fb_id,
                    'user_id' => $customers_id,

                ]);

            }

            $userData = DB::table('users')->where('id', '=', $customers_id)->get();

            //update record of customers_info
            $existUserInfo = DB::table('customers_info')->where('customers_info_id', $customers_id)->get();
            $customers_info_id = $customers_id;
            $customers_info_date_of_last_logon = date('Y-m-d H:i:s');
            $customers_info_number_of_logons = '1';
            $customers_info_date_account_created = date('Y-m-d H:i:s');
            $global_product_notifications = '1';

            if (count($existUserInfo) > 0) {
                //update customers_info table
                DB::table('customers_info')->where('customers_info_id', $customers_info_id)->update([
                    'customers_info_date_of_last_logon' => $customers_info_date_of_last_logon,
                    'global_product_notifications' => $global_product_notifications,
                    'customers_info_number_of_logons' => DB::raw('customers_info_number_of_logons + 1'),
                ]);

            } else {
                //insert customers_info table
                $customers_default_address_id = DB::table('customers_info')->insertGetId([
                    'customers_info_id' => $customers_info_id,
                    'customers_info_date_of_last_logon' => $customers_info_date_of_last_logon,
                    'customers_info_number_of_logons' => $customers_info_number_of_logons,
                    'customers_info_date_account_created' => $customers_info_date_account_created,
                    'global_product_notifications' => $global_product_notifications,
                ]);

            }

            //check if already login or not
            $already_login = DB::table('whos_online')->where('customer_id', '=', $customers_id)->get();
            if (count($already_login) > 0) {
                DB::table('whos_online')
                    ->where('customer_id', $customers_id)
                    ->update([
                        'full_name' => $userData[0]->first_name . ' ' . $userData[0]->last_name,
                        'time_entry' => date('Y-m-d H:i:s'),
                    ]);
            } else {
                DB::table('whos_online')
                    ->insert([
                        'full_name' => $userData[0]->first_name . ' ' . $userData[0]->last_name,
                        'time_entry' => date('Y-m-d H:i:s'),
                        'customer_id' => $customers_id,
                    ]);
            }

            $responseData = array('success' => $success, 'data' => $userData, 'message' => $message);
        } else {
            $responseData = array('success' => '0', 'data' => array(), 'message' => "Unauthenticated call.");
        }
        $userResponse = json_encode($responseData);

        return $userResponse;
    }

    public static function googleregistration($request)
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

            $password = substr(md5(uniqid(mt_rand(), true)), 0, 8);
            //gmail user information
            $access_token = $request->idToken;
            $google_id = $request->userId;
            $customers_firstname = $request->givenName;
            $customers_lastname = $request->familyName;
            $email = $request->email;

            //user information
            $google_data = array(
                'google_id' => $google_id,
            );

            $customer_data = array(
                'role_id' => 2,
                'first_name' => $customers_firstname,
                'last_name' => $customers_lastname,
                'email' => $email,
                'password' => Hash::make($password),
                'status' => '1',
                'created_at' => date('Y-m-d H:i:s'),
            );

            $existUser = DB::table('customers')->where('google_id', '=', $google_id)->get();
            if (count($existUser) > 0) {
                $customers_id = $existUser[0]->customers_id;
                DB::table('users')->where('id', $customers_id)->update($customer_data);
            } else {
                //insert data into customer
                $customers_id = DB::table('users')->insertGetId($customer_data);
                DB::table('customers')->insertGetId([
                    'google_id' => $google_id,
                    'user_id' => $customers_id,
                ]);

            }

            $userData = DB::table('users')->where('id', '=', $customers_id)->get();

            //update record of customers_info
            $existUserInfo = DB::table('customers_info')->where('customers_info_id', $customers_id)->get();
            $customers_info_id = $customers_id;
            $customers_info_date_of_last_logon = date('Y-m-d H:i:s');
            $customers_info_number_of_logons = '1';
            $customers_info_date_account_created = date('Y-m-d H:i:s');
            $customers_info_date_account_last_modified = date('Y-m-d H:i:s');
            $global_product_notifications = '1';

            if (count($existUserInfo) > 0) {
                $success = '2';
            } else {
                //insert customers_info table
                $customers_default_address_id = DB::table('customers_info')->insertGetId(
                    [
                        'customers_info_id' => $customers_info_id,
                        'customers_info_date_of_last_logon' => $customers_info_date_of_last_logon,
                        'customers_info_number_of_logons' => $customers_info_number_of_logons,
                        'customers_info_date_account_created' => $customers_info_date_account_created,
                        'global_product_notifications' => $global_product_notifications,
                    ]
                );
                $success = '1';
            }

            //check if already login or not
            $already_login = DB::table('whos_online')->where('customer_id', '=', $customers_id)->get();

            if (count($already_login) > 0) {
                DB::table('whos_online')
                    ->where('customer_id', $customers_id)
                    ->update([
                        'full_name' => $userData[0]->first_name . ' ' . $userData[0]->last_name,
                        'time_entry' => date('Y-m-d H:i:s'),
                    ]);
            } else {

                DB::table('whos_online')
                    ->insert([
                        'full_name' => $userData[0]->first_name . ' ' . $userData[0]->last_name,
                        'time_entry' => date('Y-m-d H:i:s'),
                        'customer_id' => $customers_id,
                    ]);
            }

            //$userData = $request->all();
            $responseData = array('success' => $success, 'data' => $userData, 'message' => "Login successfully");
        } else {
            $responseData = array('success' => '0', 'data' => array(), 'message' => "Unauthenticated call.");
        }
        $userResponse = json_encode($responseData);

        return $userResponse;
    }

    public static function registerdevices($request)
    {
       /* 
        $validator = Validator::make($request->all(), [
            'consumer_device_id' => 'required',
            'device_type' => 'required',
            'device_manufacturer' => 'required',
            'customers_id' => 'nullable',
        ]);
        if ($validator->fails()) {
            Log::error(__CLASS__."::".__FUNCTION__." Validation failed !");
            return returnResponse($validator->errors(), HttpStatus::HTTP_UNPROCESSABLE_ENTITY);
        }
        */
        Log::error(__CLASS__."::".__FUNCTION__." Consumer data !".json_encode($request->all()));
        $consumer_data = array();
        
        $consumer_data['consumer_nonce'] = request()->header('consumer-nonce');
        $consumer_data['consumer_device_id'] = request()->header('consumer_device_id');
        //$consumer_data['consumer_ip'] = request()->header('consumer-ip');
        $consumer_data['consumer_url'] = __FUNCTION__;
       

            $myVar = new AppSettingController();
            $setting = $myVar->getSetting();
            $myVar2 = new AddressController();
            $setting['countries'] = $myVar2->getAllCountries();

            $device_type = $request->device_type;
           $type = 3;
            if ($device_type == 'iOS') { /* iphone */
                $type = 1;
            } elseif ($device_type == 'Android') { /* android */
                $type = 2;
            } elseif ($device_type == 'Desktop') { /* other */
                $type = 3;
            }

            if (!empty($request->customers_id)) {

                $device_data = array(
                    'device_id' => $request->consumer_device_id,
                    'device_type' => $type,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'ram' => $request->device_ram,
                    'status' => '1',
                    'processor' => $request->device_processor,
                    'device_os' => $request->device_platform,
                    'location' => $request->device_location,
                    'device_model' => $request->device_model,
                    'customers_id' => $request->customers_id,
                    'manufacturer' => $request->device_manufacturer,
                    'device_app_version' => $request->device_app_version,
                    'device_os_version' => $request->device_os_version,
                    $setting['default_notification'] => '1',
                );

            } else {

                $device_data = array(
                    'device_id' => $request->consumer_device_id,
                    'device_type' => $type,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'status' => '1',
                    'ram' => $request->device_ram,
                    'processor' => $request->device_processor,
                    'device_os' => $request->device_device_platform,
                    'location' => $request->device_location,
                    'device_model' => $request->device_model,
                    'manufacturer' => $request->device_manufacturer,
                    'device_app_version' => $request->device_app_version,
                    'device_os_version' => $request->device_os_version,
                    $setting['default_notification'] => '1',
                );

            }

            //check device exist
            $device_id = DB::table('devices')->where('device_id', '=', $request->consumer_device_id)->get();

            if (count($device_id) > 0) {

                $dataexist = DB::table('devices')->where('device_id', '=', $request->consumer_device_id)->where('user_id', '=', '0')->get();

                DB::table('devices')
                    ->where('device_id', $request->consumer_device_id)
                    ->update($device_data);

                if (count($dataexist) >= 0 && isset($request->customers_id)) {
                    $userData = DB::table('users')->where('id', '=', $request->customers_id)->get();
                    //notification
                    $myVar = new AlertController();
                    $alertSetting = $myVar->createUserAlert($userData);
                }
            } else {
                $device_id = DB::table('devices')->insertGetId($device_data);
            }

           // $responseData = array('success' => '1', 'data' => array(), 'message' => "Device is registered.");
        
       return returnResponse("Device is registered.", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $setting);
    }
    
    
    // send otp for Normal Signup
    public static function normalSignup($request)
    {
        $validator = Validator::make($request->all(), [
            'mobile_no' => 'required|string|between:10,12',
            'name' => 'required|string|max:200',
            'password' => 'required|confirmed',
            'email' => 'nullable|email',
            'referral_code' => 'nullable|string',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), HttpStatus::HTTP_BAD_REQUEST);
        }
        $currentDateTime = Carbon::now();
        $otpExpiry = Carbon::now()->addMinute(15);
        
        $mobile_no = $request->mobile_no;
        Log::debug(__CLASS__."::".__FUNCTION__."Called with mobile no $mobile_no");

        $consumer_data = getallheaders();
      /*
      $consumer_data['consumer_key'] = $request->header('consumer_key');
      $consumer_data['consumer_secret'] = $request->header('consumer_secret');
      $consumer_data['consumer_nonce'] = $request->header('consumer_nonce');
      $consumer_data['consumer_device_id'] = $request->header('consumer_device_id');
      */
      $consumer_data['consumer_ip'] = $request->ip();
       // $consumer_data['consumer_ip'] = request()->header('consumer-ip');
        $consumer_data['consumer_url'] = __FUNCTION__;
        $authController = new AppSettingController();
        $authenticate = $authController->apiAuthenticate($consumer_data);

          $otp_for = 'normal_signup';
        if ($authenticate == 1) {

            //check email existance
            $existUser = DB::table('users')->where('phone', $mobile_no)->where('role_id', '2')->where('status', '1')->get();
            $otp = generateOtp($mobile_no);
            if (isset($existUser[0]->first_name) && count($existUser) > 0) {
                
                return returnResponse('User Already Exists !', HttpStatus::HTTP_BAD_REQUEST);
            } else {
                
                
                
                $otpHistory = new OtpHistory;
                $otpHistory->mobile_no = $mobile_no;
                $otpHistory->otp = $otp;
                $otpHistory->otp_for = $otp_for;
                $otpHistory->otp_expiry = $otpExpiry;
                if($otpHistory->save()){
                $message_text = "Dear User, OTP for Signup is ".$otp.". Please dont share this to anyone.
Team,
".config('app.send_sms_company_name');
                Log::debug("sms scheduled as $message_text");
                
                if(SmsService::scheduleNewSMS($mobile_no, $message_text, 'otp', '1')){
                Log::debug("Otp Sent $otp");
                return returnResponse("Otp Sent successfully!", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, 'true');
                
                }
                
                }
                
                return returnResponse("Otp Seding Failed!", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, 'false');
            }

        } 
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
    }
    
    
    //Send Otp for Prime Signup
     public static function primeSignup($request)
    {
        $validator = Validator::make($request->all(), [
            'mobile_no' => 'required|string|between:10,12',
            'name' => 'required',
            'referral_code' => 'required',
            'email' => 'required|email',
            'dob' => 'required',
            'city' => 'required',
            'pin_code' => 'required',
            'password' => 'required|confirmed',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), HttpStatus::HTTP_BAD_REQUEST);
        }
        $currentDateTime = Carbon::now();
        $otpExpiry = Carbon::now()->addMinute(15);
        
        $mobile_no = $request->mobile_no;
    Log::debug(__CLASS__."::".__FUNCTION__." Called with mobile $mobile_no");
        $consumer_data = getallheaders();
      /*
      $consumer_data['consumer_key'] = $request->header('consumer_key');
      $consumer_data['consumer_secret'] = $request->header('consumer_secret');
      $consumer_data['consumer_nonce'] = $request->header('consumer_nonce');
      $consumer_data['consumer_device_id'] = $request->header('consumer_device_id');
      */
      $consumer_data['consumer_ip'] = $request->ip();
       // $consumer_data['consumer_ip'] = request()->header('consumer-ip');
        $consumer_data['consumer_url'] = __FUNCTION__;
        $authController = new AppSettingController();
        $authenticate = $authController->apiAuthenticate($consumer_data);

          $otp_for = 'prime_signup';
        if ($authenticate == 1) {

            //check mobile number existance
            //$existUser = DB::table('users')->where('phone', $mobile_no)->where('role_id', '2')->where('status', '1')->get();
            $existUser = DB::table('users')->where('phone', $mobile_no)->where('role_id', '2')->get();
            if (isset($existUser->first_name)) {
                
                if(isset($existUser->is_prime) && $existUser->is_prime == "Y"){
                    return returnResponse('User Already Exists !', HttpStatus::HTTP_BAD_REQUEST);
                }
            }
            $otp = generateOtp($mobile_no);
            $otpHistory = new OtpHistory;
                            $otpHistory->mobile_no = $mobile_no;
                            $otpHistory->otp = $otp;
                            $otpHistory->otp_for = $otp_for;
                            $otpHistory->otp_expiry = $otpExpiry;
                            if($otpHistory->save()){
                            $message_text = "Dear User, OTP for Signup is ".$otp.". Please dont share this to anyone.
Team,
".config('app.send_sms_company_name');
                            
                     Log::debug("Sms Schedule as $message_text");       
                if(SmsService::scheduleNewSMS($mobile_no, $message_text, 'otp', '1')){
                    Log::debug("Otp Sent $otp");
                    return returnResponse("Otp Sent successfully!", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, 'true');
                
                }
            
            }
            
            return returnResponse("Otp Seding Failed!", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, 'false');
        } 
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
    }

    //prime signup verify    
    public static function primeSignupVerify($request)
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
            'mobile_no' => 'required|string|between:10,12',
            'otp' => 'required|numeric|min:4',
            'name' => 'required',
            'referral_code' => 'required',
            'email' => 'required|email',
            'dob' => 'required',
            'city' => 'required',
            'pin_code' => 'required',
            'password' => 'required|confirmed',
        ]);
        
        if ($validator->fails()) {
            Log::error(__CLASS__."::".__FUNCTION__." Login failed ! Validation failed !");
            return returnResponse($validator->errors(), HttpStatus::HTTP_UNPROCESSABLE_ENTITY);
        }
        
        if ($authenticate == 1) {

            $mobile = $request->mobile_no;
            $name = $request->name;
            $otp = $request->otp;
            $email = $request->email;
            $referral_id = $request->referral_code;
            $dob = Carbon::parse($request->dob)->format('Y-m-d');
            $city = $request->city;
            $pin_code = $request->pin_code;
            $password = Hash::make($request->password);
            $country = 99;
            $country_code = 'IND';
            $referal_token = self::generateReferralToken();
            
            $existUser = DB::table('users')
                    ->where('phone', $mobile)->where('status', '1')->get();
            //$password = Crypt::decrypt($existUser[0]->password);
            Log::debug(__CLASS__."::".__FUNCTION__."CAlled with mobile $mobile otp $otp email $email referral code $referral_id");
            
            $currentTime = Carbon::now();
             try {
            DB::beginTransaction();
            $otp_for = $request->otp_for;
            $existOtp = OtpHistory::where('mobile_no', $mobile)->where('otp', $otp)->where('status', 'ACTIVE')->first();
                    
            //$password = Crypt::decrypt($existUser[0]->password);
            
            $currentTime = Carbon::now();
             
        if(isset($existOtp->otp) && $otp == $existOtp->otp && $currentTime<= $existOtp->otp_expiry){
            OtpHistory::where('id',$existOtp->id)->update(['status'=>'USED']);
               if(isset($existUser[0]->is_prime) && $existUser[0]->is_prime == 'Y'){
                   return returnResponse('Already Prime Member Login to continue');
               }
                if(isset($existUser[0]->id) && $existUser[0]->is_prime =='N'){
                    $user = CustomerLogin::where('phone', $mobile)->where('status', '1')->where('role_id', '2')->first();
                    if ($token = JWTAuth::fromUser($user)) {

                        if (count($existUser) > 0) {

                            $customers_id = $user->id;
                            $parent_id = self::getParentCodeByReferralCode($referral_id,4,1);
                            //update record of customers_info
                            $existUserInfo = DB::table('customers_info')->where('customers_info_id', $customers_id)->get();
                            $customers_info_id = $customers_id;
                            $customers_info_date_of_last_logon = date('Y-m-d H:i:s');
                            $customers_info_number_of_logons = '1';
                            $customers_info_date_account_created = date('Y-m-d H:i:s');
                            $global_product_notifications = '1';

                            if (count($existUserInfo) > 0) {
                                //update customers_info table
                                DB::table('customers_info')->where('customers_info_id', $customers_info_id)->update([
                                    'customers_info_date_of_last_logon' => $customers_info_date_of_last_logon,
                                    'global_product_notifications' => $global_product_notifications,
                                    'customers_info_number_of_logons' => DB::raw('customers_info_number_of_logons + 1'),
                                ]);

                            } else {
                                //insert customers_info table
                                $customers_default_address_id = DB::table('customers_info')->insertGetId(
                                    ['customers_info_id' => $customers_info_id,
                                        'customers_info_date_of_last_logon' => $customers_info_date_of_last_logon,
                                        'customers_info_number_of_logons' => $customers_info_number_of_logons,
                                        'customers_info_date_account_created' => $customers_info_date_account_created,
                                        'global_product_notifications' => $global_product_notifications,
                                    ]
                                );


                            }

                            // data to address book

                            //check if record exist
                            $exist = DB::table('user_to_address')->where('user_id','=',$user->id)->first();

                            if(isset($exist->address_book_id)){
                              $address_book_id = $exist->address_book_id;
                              DB::table('address_book')->where('user_id','=', $user->id)->where('address_book_id','=', $address_book_id)->update([
                                'entry_firstname'	      =>	$name,
                                'entry_lastname'		      =>	'',
                                'entry_street_address'		=>	'',
                                'entry_city'			        =>	$city,
                                'entry_state'			      =>	'',
                                'entry_postcode'		     	=>	$pin_code,
                                'entry_country_id'		    =>	$country,
                              ]);

                            }else{
                             $address_book_id = DB::table('address_book')->insertGetId([
                                'is_user_address'          =>    'Y',
                                'user_id'		            =>	$user->id,
                                'entry_firstname'	      =>	$name,
                                'entry_lastname'		      =>	'',
                                'entry_street_address'		=>	'',
                                'entry_city'			        =>	$city,
                                'entry_state'			      =>	'',
                                'entry_postcode'		     	=>	$pin_code,
                                'entry_country_id'		    =>	$country,
                              ]);

                              if($address_book_id){
                                $user_to_address =  DB::table('user_to_address')->insertGetId([
                                   'user_id'		            =>	$user->id,
                                   'address_book_id'	      =>	$address_book_id,
                                   'is_default'    =>  1
                                 ]);
                              }
                            }

                            DB::table('users')->where('id', $customers_id)->update([
                                    'default_address_id' => $address_book_id,
                                    'phone_verified' => '1',
                                    'phone' => $mobile,
                                    'first_name' => $name,
                                    'dob' => $dob,
                                    'email' => $email,
                                    'parent_id' => $parent_id,
                                    'password' => $password,
                                   // 'member_code' => $referal_token,
                                    'country_code' => $country_code,
                                    'prime_referral' => $referral_id,
                                    'prime_time' => date('y-m-d h:i:s'),
                                    'is_prime' => 'Y',
                                ]);

                            //check if already login or not
                            $already_login = DB::table('whos_online')->where('customer_id', '=', $customers_id)->get();

                            if (count($already_login) > 0) {
                                DB::table('whos_online')
                                    ->where('customer_id', $customers_id)
                                    ->update([
                                        'full_name' => $user->first_name . ' ' . $user->last_name,
                                        'time_entry' => date('Y-m-d H:i:s'),
                                    ]);
                            } else {
                                DB::table('whos_online')
                                    ->insert([
                                        'full_name' => $user->first_name . ' ' . $user->last_name,
                                        'time_entry' => date('Y-m-d H:i:s'),
                                        'customer_id' => $customers_id,
                                    ]);
                            }

                            Log::debug('CAlling Method for Distributing Level Income');
                            if(!self::levelIncomeDistribute(1, $referral_id, $parent_id,$customers_id)){
                                 Log::debug('Distributing Level Income failed');
                                 return returnResponse("Error While Processing !");
                            }

                           
                            $user_data = CustomerLogin::where('id', $user->id)->where('status', '1')->where('role_id', '2')->first();

                            if(isset($user_data->member_code)){
                                $user_level_income = DB::table('user_level_incomes')
                                ->where('member_code', '=', $user_data->member_code)
                                ->where('status', '=', "REGISTERED")
                                ->where('is_paid', '=', "N")
                                ->sum('amount');
                                $user_data->f_wallet = $user_level_income;
                            }


                            //$responseData = array('success' => '1', 'data' => $existUser, 'message' => 'Data has been returned successfully!');
                             $now = Carbon::now();
                            $data = array(
                                'access_token' => $token,
                                'token_type' => 'Bearer',
                                'expires_in' => JWTFactory::getTTL() * 60,
                                'last_login' => substr($now, 0, strlen($now)),
                                'user' => $user_data,

                            );
                        DB::commit();
                        //Log::debug($data);
                        return returnResponse("Prime Registration done Successfully !", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $data);

                        } else {
                            //$responseData = array('success' => '0', 'data' => array(), 'message' => "Your account has been deactivated.");
                            return returnResponse("Your account has been deactivated.", HttpStatus::HTTP_UNAUTHORIZED);
                        }
                    } else {
                        Log::error(__CLASS__."::".__FUNCTION__." Login attempt failed !");
                        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);

                    }
                }else{
                    
                    Log::debug('Getting Parent ID');
                 $parent_id = self::getParentCodeByReferralCode($referral_id,4,1);
                    if(!empty($email)){
                        $email = strtolower($email);
                    }
                    
                 $customer_id =   DB::table('users')->insertGetId([
                                    'phone_verified' => '1',
                                    'first_name' => $name,
                                    'phone' => $mobile,
                                    'dob' => $dob,
                                    'email' => $email,
                                    'password' => $password,
                                    'country_code' => $country_code,
                                    'member_code' => $referal_token,
                                    'prime_referral' => $referral_id,
                                    'role_id' => '2',
                                    'parent_id' => $parent_id,
                                    'prime_time' => date('y-m-d h:i:s'),
                                    'status' => '1',
                                    'is_prime' => 'Y',
                                    'created_at' => date('y-m-d h:i:s'),
                                ]);
                    
                    $user = CustomerLogin::where('id', $customer_id)->where('status', '1')->where('role_id', '2')->first();
                    if ($token = JWTAuth::fromUser($user)) {


                            $customers_id = $user->id;

                            //update record of customers_info
                            $existUserInfo = DB::table('customers_info')->where('customers_info_id', $customers_id)->get();
                            $customers_info_id = $customers_id;
                            $customers_info_date_of_last_logon = date('Y-m-d H:i:s');
                            $customers_info_number_of_logons = '1';
                            $customers_info_date_account_created = date('Y-m-d H:i:s');
                            $global_product_notifications = '1';

                            if (count($existUserInfo) > 0) {
                                //update customers_info table
                                DB::table('customers_info')->where('customers_info_id', $customers_info_id)->update([
                                    'customers_info_date_of_last_logon' => $customers_info_date_of_last_logon,
                                    'global_product_notifications' => $global_product_notifications,
                                    'customers_info_number_of_logons' => DB::raw('customers_info_number_of_logons + 1'),
                                ]);

                            } else {
                                //insert customers_info table
                                $customers_default_address_id = DB::table('customers_info')->insertGetId(
                                    ['customers_info_id' => $customers_info_id,
                                        'customers_info_date_of_last_logon' => $customers_info_date_of_last_logon,
                                        'customers_info_number_of_logons' => $customers_info_number_of_logons,
                                        'customers_info_date_account_created' => $customers_info_date_account_created,
                                        'global_product_notifications' => $global_product_notifications,
                                    ]
                                );


                            }

                            // data to address book

                            //check if record exist
                            $exist = DB::table('user_to_address')->where('user_id','=',$user->id)->first();

                            if(isset($exist->address_book_id)){
                              $address_book_id = $exist->address_book_id;
                              DB::table('address_book')->where('user_id','=', $user->id)->where('address_book_id','=', $address_book_id)->update([
                                'entry_firstname'	      =>	$name,
                                'entry_lastname'		      =>	'',
                                'entry_street_address'		=>	'',
                                'entry_city'			        =>	$city,
                                'entry_state'			      =>	'',
                                'entry_postcode'		     	=>	$pin_code,
                                'entry_country_id'		    =>	$country,
                              ]);

                            }else{
                             $address_book_id = DB::table('address_book')->insertGetId([
                                'user_id'		            =>	$user->id,
                                'entry_firstname'	      =>	$name,
                                'entry_lastname'		      =>	'',
                                'entry_street_address'		=>	'',
                                'entry_city'			        =>	$city,
                                'entry_state'			      =>	'',
                                'entry_postcode'		     	=>	$pin_code,
                                'entry_country_id'		    =>	$country,
                              ]);

                              if($address_book_id){
                                $user_to_address =  DB::table('user_to_address')->insertGetId([
                                   'user_id'		            =>	$user->id,
                                   'address_book_id'	      =>	$address_book_id,
                                   'is_default'    =>  1
                                 ]);
                              }
                            }

                            DB::table('users')->where('id', $customers_id)->update([
                                    'default_address_id' => $address_book_id,
                                    
                                ]);

                            //check if already login or not
                            $already_login = DB::table('whos_online')->where('customer_id', '=', $customers_id)->get();

                            if (count($already_login) > 0) {
                                DB::table('whos_online')
                                    ->where('customer_id', $customers_id)
                                    ->update([
                                        'full_name' => $user->first_name . ' ' . $user->last_name,
                                        'time_entry' => date('Y-m-d H:i:s'),
                                    ]);
                            } else {
                                DB::table('whos_online')
                                    ->insert([
                                        'full_name' => $user->first_name . ' ' . $user->last_name,
                                        'time_entry' => date('Y-m-d H:i:s'),
                                        'customer_id' => $customers_id,
                                    ]);
                            }




                           
                            $user_data = CustomerLogin::where('id', $user->id)->where('status', '1')->where('role_id', '2')->first();

                            Log::debug('CAlling Method for Distributing Level Income');
                            if(!self::levelIncomeDistribute(1, $referral_id, $parent_id,$user_data->id)){
                                 Log::debug('Distributing Level Income failed');
                                 return returnResponse("Error While Processing !");
                            }

                            if(isset($user_data->member_code)){
                                $user_level_income = DB::table('user_level_incomes')
                                ->where('member_code', '=', $user_data->member_code)
                                ->where('status', '=', "REGISTERED")
                                ->where('is_paid', '=', "N")
                                ->sum('amount');
                                $user_data->f_wallet = $user_level_income;
                            }
                            //$responseData = array('success' => '1', 'data' => $existUser, 'message' => 'Data has been returned successfully!');
                             $now = Carbon::now();
                            $data = array(
                                'access_token' => $token,
                                'token_type' => 'Bearer',
                                'expires_in' => JWTFactory::getTTL() * 60,
                                'last_login' => substr($now, 0, strlen($now)),
                                'user' => $user_data,

                            );
                        DB::commit();
                        //Log::debug($data);
                        return returnResponse("Prime Registration done Successfully !", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $data);

                         
                    } else {
                        Log::error(__CLASS__."::".__FUNCTION__." Login attempt failed !");
                        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);

                    }
                    
                }
             }
            return returnResponse('Invalid Otp', HttpStatus::HTTP_UNAUTHORIZED);
        
        }
        catch (JWTException $exc) {
            Log::error(__CLASS__."::".__FUNCTION__." Exception : ".$exc->getMessage());
            return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
        }
        } 
            return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
        
       
    }
    
    public static function normalSignupVerify($request)
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
            'mobile_no' => 'required|string|between:10,12',
            'name' => 'required|string|max:200',
            'otp' => 'required|numeric|min:4',
            'password' => 'required|confirmed',
            'email' => 'nullable|email',
            'referral_code' => 'nullable|string',
        ]);
        $customers_firstname = $request->name;
        $otp_for = 'normal_signup';
        $password = '';
        if ($validator->fails()) {
            Log::error(__CLASS__."::".__FUNCTION__." Login failed ! Validation failed !");
            return returnResponse($validator->errors(), HttpStatus::HTTP_UNPROCESSABLE_ENTITY);
        }
        
        if ($authenticate == 1) {

            $mobile = $request->mobile_no;
            $email = $request->email;
            $otp = $request->otp;
            $otp_for = $request->otp_for;
            $existOtp = OtpHistory::where('mobile_no', $mobile)->where('otp', $otp)->where('status', 'ACTIVE')->first();
                    
            //$password = Crypt::decrypt($existUser[0]->password);
            
            $currentTime = Carbon::now();
             
        if(isset($existOtp->otp) && $otp == $existOtp->otp && $currentTime<= $existOtp->otp_expiry){
            OtpHistory::where('id',$existOtp->id)->update(['status'=>'USED']);
            DB::beginTransaction();
            $password = Hash::make($request->password);
                   
                   $referal_token = self::generateReferralToken();
                   $referral_id = '';
                   if($request->has('referral_code')){
                   $referral_id = $request->referral_code;
                   $existreferal = DB::table('users')->where('member_code', $referral_id)->where('status', '1')->first();
                   if(!isset($existreferal->id)){
                    return returnResponse('Invalid Referal Code', HttpStatus::HTTP_BAD_REQUEST);   
                   }
                   }
            
            if(!empty($email)){
                $email = strtolower($email);
            }
                //insert data into customer
                $customers_id = DB::table('users')->insertGetId([
                    'role_id' => 2,
                    'first_name' => $customers_firstname,
                    'phone' => $mobile,
                    'email' => $email,
                    'phone_verified' => '1',
                    'password' => $password,
                    'member_code' => $referal_token,
                    'normal_referral' => $referral_id,
                    'status' => '1',
                    'created_at' => date('y-m-d h:i:s'),
                    'is_prime' => 'N',
                ]);
                if($otp_for =='normal_signup' && !empty($referral_id)){
                 self::distributeDirectIncome($customers_id,$referral_id);
                }    
                try {
            
            $user = CustomerLogin::where('id', $customers_id)->where('status', '1')->where('role_id', '2')->first();
                if ($token = JWTAuth::fromUser($user)) {

                    if (isset($user->id)) {

                        $customers_id = $user->id;

                        //update record of customers_info
                        $existUserInfo = DB::table('customers_info')->where('customers_info_id', $customers_id)->get();
                        $customers_info_id = $customers_id;
                        $customers_info_date_of_last_logon = date('Y-m-d H:i:s');
                        $customers_info_number_of_logons = '1';
                        $customers_info_date_account_created = date('Y-m-d H:i:s');
                        $global_product_notifications = '1';

                        if (count($existUserInfo)>0) {
                            //update customers_info table
                            DB::table('customers_info')->where('customers_info_id', $customers_info_id)->update([
                                'customers_info_date_of_last_logon' => $customers_info_date_of_last_logon,
                                'global_product_notifications' => $global_product_notifications,
                                'customers_info_number_of_logons' => DB::raw('customers_info_number_of_logons + 1'),
                            ]);

                        } else {
                            //insert customers_info table
                            $customers_default_address_id = DB::table('customers_info')->insertGetId(
                                ['customers_info_id' => $customers_info_id,
                                    'customers_info_date_of_last_logon' => $customers_info_date_of_last_logon,
                                    'customers_info_number_of_logons' => $customers_info_number_of_logons,
                                    'customers_info_date_account_created' => $customers_info_date_account_created,
                                    'global_product_notifications' => $global_product_notifications,
                                ]
                            );

                            DB::table('users')->where('id', $customers_id)->update([
                                'default_address_id' => $customers_default_address_id,
                                'phone_verified' => '1',
                            ]);
                        }

                        //check if already login or not
                        $already_login = DB::table('whos_online')->where('customer_id', '=', $customers_id)->get();

                        if (count($already_login) > 0) {
                            DB::table('whos_online')
                                ->where('customer_id', $customers_id)
                                ->update([
                                    'full_name' => $user->first_name . ' ' . $user->last_name,
                                    'time_entry' => date('Y-m-d H:i:s'),
                                ]);
                        } else {
                            DB::table('whos_online')
                                ->insert([
                                    'full_name' => $user->first_name . ' ' . $user->last_name,
                                    'time_entry' => date('Y-m-d H:i:s'),
                                    'customer_id' => $customers_id,
                                ]);
                        }

                       
                        if(isset($user->member_code)){
                            $user_level_income = DB::table('user_level_incomes')
                            ->where('member_code', '=', $user->member_code)
                            ->where('status', '=', "REGISTERED")
                            ->where('is_paid', '=', "N")
                            ->sum('amount');
                            $user->f_wallet = $user_level_income;
                        }
                        //$responseData = array('success' => '1', 'data' => $existUser, 'message' => 'Data has been returned successfully!');
                         $now = Carbon::now();
                        $data = array(
                            'access_token' => $token,
                            'token_type' => 'Bearer',
                            'expires_in' => JWTFactory::getTTL() * 60,
                            'last_login' => substr($now, 0, strlen($now)),
                            'user' => $user,
                        );

                        DB::commit();
                    return returnResponse("Registration done Successfully !", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $data);

                    } else {
                        //$responseData = array('success' => '0', 'data' => array(), 'message' => "Your account has been deactivated.");
                        return returnResponse("Your account has been deactivated.", HttpStatus::HTTP_UNAUTHORIZED);
                    }
                } else {
                    Log::error(__CLASS__."::".__FUNCTION__." Login attempt failed !");
                    return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);

                }
            
            
        
                }
              catch (JWTException $exc) {
                  Log::error(__CLASS__."::".__FUNCTION__." Exception : ".$exc->getMessage());
                  return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
              }
                
            }else{
                return returnResponse('Invalid Otp', HttpStatus::HTTP_UNAUTHORIZED);
            }
        } 
            return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
        
       
    }
    
//Send Otp for customer Login
    
    public static function usualLoginSendOtp($request)
    {
        $validator = Validator::make($request->all(), [
            'mobile_no' => 'required|string|between:10,12',
            
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), HttpStatus::HTTP_BAD_REQUEST);
        }
        $currentDateTime = Carbon::now();
        $otpExpiry = Carbon::now()->addMinute(15);
        
        $mobile = $request->mobile_no;

        $consumer_data = getallheaders();
        Log::debug(__CLASS__."::".__FUNCTION__."called with Mobile no. $mobile");
      /*
      $consumer_data['consumer_key'] = $request->header('consumer_key');
      $consumer_data['consumer_secret'] = $request->header('consumer_secret');
      $consumer_data['consumer_nonce'] = $request->header('consumer_nonce');
      $consumer_data['consumer_device_id'] = $request->header('consumer_device_id');
      */
      $consumer_data['consumer_ip'] = $request->ip();
       // $consumer_data['consumer_ip'] = request()->header('consumer-ip');
        $consumer_data['consumer_url'] = __FUNCTION__;
        $authController = new AppSettingController();
        $authenticate = $authController->apiAuthenticate($consumer_data);

          $otp_for = 'login';
        if ($authenticate == 1) {

            //check email existance
            $existUser = DB::table('users')->where('phone', $mobile)->where('role_id', '2')->where('status', '1')->first();
            
            $otp = generateOtp($mobile);
            if (isset($existUser->id)) {
                if($existUser->status == '1'){
                $otpHistory = new OtpHistory;
                $otpHistory->mobile_no = $mobile;
                $otpHistory->otp = $otp;
                $otpHistory->otp_for = $otp_for;
                $otpHistory->otp_expiry = $otpExpiry;
                if($otpHistory->save()){
                $message_text = "Dear ".$existUser->first_name.", OTP for Login is ".$otp.". Please dont share this to anyone.
Team,
".config('app.send_sms_company_name');
                
                Log::debug("sms scheduled as $message_text");
                if(SmsService::scheduleNewSMS($mobile, $message_text, 'otp', '1')){
                Log::debug("Otp Sent $otp");
                return returnResponse("Otp Sent successfully!", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, 'true');
                
                }
                }
                return returnResponse("Otp Seding Failed!", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, 'false');
               
                
                }
                return returnResponse('Your Account is deactive !', HttpStatus::HTTP_BAD_REQUEST);
            } else {
                
                return returnResponse("User Dosen't Exists !", HttpStatus::HTTP_BAD_REQUEST);
            }

        } 
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
    }
    
    
    //customer Login with Otp
    
    public static function verifyLogin($request)
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
            'mobile_no' => 'required|string|between:10,12',
            'otp' => 'required|numeric|min:4',
        ]);
        $mobile = $request->mobile_no;
            $otp = $request->otp;
            Log::debug(__CLASS__."::".__FUNCTION__."Called with mobile $mobile and otp $otp");
        
        if ($validator->fails()) {
            Log::error(__CLASS__."::".__FUNCTION__." Login failed ! Validation failed !");
            return returnResponse($validator->errors(), HttpStatus::HTTP_UNPROCESSABLE_ENTITY);
        }
        
        if ($authenticate == 1) {
            DB::beginTransaction();
            
            $existUser = DB::table('users')
                    ->where('phone', $mobile)->where('status', '1')->get();
            //$password = Crypt::decrypt($existUser[0]->password);
            
            
            $currentTime = Carbon::now();
             try {
            $otp_for = 'login';
            $existOtp = OtpHistory::where('mobile_no', $mobile)->where('otp', $otp)->where('status', 'ACTIVE')->first();
                    
            //$password = Crypt::decrypt($existUser[0]->password);
            
             
        if(isset($existOtp->otp) && $otp == $existOtp->otp && $currentTime<= $existOtp->otp_expiry && count($existUser) > 0 && isset($existUser[0]->id)){
            OtpHistory::where('id',$existOtp->id)->update(['status'=>'USED']);
                $user = CustomerLogin::where('phone', $mobile)->where('status', '1')->where('role_id', '2')->first();
                if ($token = JWTAuth::fromUser($user)) {

                    if ($existUser[0]->status == '1') {

                        $customers_id = $user->id;

                        //update record of customers_info
                        $existUserInfo = DB::table('customers_info')->where('customers_info_id', $customers_id)->get();
                        $customers_info_id = $customers_id;
                        $customers_info_date_of_last_logon = date('Y-m-d H:i:s');
                        $customers_info_number_of_logons = '1';
                        $customers_info_date_account_created = date('Y-m-d H:i:s');
                        $global_product_notifications = '1';

                        if (count($existUserInfo) > 0) {
                            //update customers_info table
                            DB::table('customers_info')->where('customers_info_id', $customers_info_id)->update([
                                'customers_info_date_of_last_logon' => $customers_info_date_of_last_logon,
                                'global_product_notifications' => $global_product_notifications,
                                'customers_info_number_of_logons' => DB::raw('customers_info_number_of_logons + 1'),
                            ]);

                        } else {
                            //insert customers_info table
                            $customers_default_address_id = DB::table('customers_info')->insertGetId(
                                ['customers_info_id' => $customers_info_id,
                                    'customers_info_date_of_last_logon' => $customers_info_date_of_last_logon,
                                    'customers_info_number_of_logons' => $customers_info_number_of_logons,
                                    'customers_info_date_account_created' => $customers_info_date_account_created,
                                    'global_product_notifications' => $global_product_notifications,
                                ]
                            );

                            DB::table('users')->where('id', $customers_id)->update([
                                'default_address_id' => $customers_default_address_id,
                                'phone_verified' => '1',
                            ]);
                        }

                        //check if already login or not
                        $already_login = DB::table('whos_online')->where('customer_id', '=', $customers_id)->get();

                        if (count($already_login) > 0) {
                            DB::table('whos_online')
                                ->where('customer_id', $customers_id)
                                ->update([
                                    'full_name' => $user->first_name . ' ' . $user->last_name,
                                    'time_entry' => date('Y-m-d H:i:s'),
                                ]);
                        } else {
                            DB::table('whos_online')
                                ->insert([
                                    'full_name' => $user->first_name . ' ' . $user->last_name,
                                    'time_entry' => date('Y-m-d H:i:s'),
                                    'customer_id' => $customers_id,
                                ]);
                        }

                        if(isset($user->member_code)){
                            $user_level_income = DB::table('user_level_incomes')
                            ->where('member_code', '=', $user->member_code)
                            ->where('status', '=', "REGISTERED")
                            ->where('is_paid', '=', "N")
                            ->sum('amount');
                            $user->f_wallet = $user_level_income;
                        }

                        //$responseData = array('success' => '1', 'data' => $existUser, 'message' => 'Data has been returned successfully!');
                     
                        $myCart = Cart::myCart(null,$customers_id);
                       
                         $now = Carbon::now();
                        $data = array(
                            'access_token' => $token,
                            'token_type' => 'Bearer',
                            'expires_in' => JWTFactory::getTTL() * 60,
                            'last_login' => substr($now, 0, strlen($now)),
                            'user' => $user,
                            'cart' => $myCart,
                           
                        );

                    //Log::debug($data);
                        DB::commit();
                    return returnResponse("Login Success !", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $data);

                    } else {
                        //$responseData = array('success' => '0', 'data' => array(), 'message' => "Your account has been deactivated.");
                        return returnResponse("Your account has been deactivated.", HttpStatus::HTTP_UNAUTHORIZED);
                    }
                } else {
                    Log::error(__CLASS__."::".__FUNCTION__." Login attempt failed !");
                    return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);

                }
            }else{
                return returnResponse('Invalid Otp', HttpStatus::HTTP_UNAUTHORIZED);
            }
            
            
        
        }
        catch (JWTException $exc) {
            Log::error(__CLASS__."::".__FUNCTION__." Exception : ".$exc->getMessage());
            return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
        }
        } 
            return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
        
       
    }
    
    
    
    
    
    // Validate Referral Code
    public static function validateReferralCode($request)
    {
        Log::debug(__CLASS__."".__FUNCTION__."called");
        
        $validator = Validator::make($request->all(), [
            'referral_code' => 'required',
            'type' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), HttpStatus::HTTP_BAD_REQUEST);
        }
        
        $member_code = $request->referral_code;
        $type = $request->type;
        Log::debug(__CLASS__."".__FUNCTION__."type recieved as $type");
        Log::debug(__CLASS__."".__FUNCTION__."referral code recieved as $member_code");

        $consumer_data = getallheaders();
      /*
      $consumer_data['consumer_key'] = $request->header('consumer_key');
      $consumer_data['consumer_secret'] = $request->header('consumer_secret');
      $consumer_data['consumer_nonce'] = $request->header('consumer_nonce');
      $consumer_data['consumer_device_id'] = $request->header('consumer_device_id');
      */
      $consumer_data['consumer_ip'] = $request->ip();
       // $consumer_data['consumer_ip'] = request()->header('consumer-ip');
        $consumer_data['consumer_url'] = __FUNCTION__;
        $authController = new AppSettingController();
        $authenticate = $authController->apiAuthenticate($consumer_data);


        if ($authenticate == 1) {

            
            $existUser = DB::table('customers')->where('member_code', $member_code)->where('status', '1')->first();
               
            if (isset($existUser->id)) {
                $name['name'] = $existUser->first_name.' '.$existUser->last_name;
                $name['is_prime'] = $existUser->is_prime;
                //response if email already exit
                //$responseData = array('success' => '0', 'data' => $postData, 'message' => "Email address is already exist");
                if($type == 'PRIME' && $existUser->is_prime == 'N'){
                 return returnResponse("Referral code is invalid !");
                }
                return returnResponse("Referral code is valid !", HttpStatus::HTTP_OK,HttpStatus::HTTP_SUCCESS,$name);
                
            } else {
                  $data = DB::table('users')->where('is_prime', 'Y')->get();
                if($member_code == 'COMPANY' && count($data) == 0){
                    $name['name'] = config('app.app_name');
                    $name['is_prime'] = 'Y';
                    return returnResponse("Referral code is valid !", HttpStatus::HTTP_OK,HttpStatus::HTTP_SUCCESS,$name);
                }
                //$responseData = array('success' => '1', 'data' => $userData, 'message' => "Sign Up successfully!");
                return returnResponse("Referral code is not valid !", HttpStatus::HTTP_BAD_REQUEST);
            }

        } 
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
    }
    
    //update Referral Url
    
    public static function updateReferralUrl($request)
    {
        $validator = Validator::make($request->all(), [
            'referral_url' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), HttpStatus::HTTP_BAD_REQUEST);
        }
        
        $referral_url = $request->referral_url;
        

        $consumer_data = getallheaders();
        
        Log::debug(__CLASS__."::".__FUNCTION__."called with user id".auth()->user()->id);
        Log::debug(__CLASS__."::".__FUNCTION__."called with user id".auth()->user()->id);
      /*
      $consumer_data['consumer_key'] = $request->header('consumer_key');
      $consumer_data['consumer_secret'] = $request->header('consumer_secret');
      $consumer_data['consumer_nonce'] = $request->header('consumer_nonce');
      $consumer_data['consumer_device_id'] = $request->header('consumer_device_id');
      */
      $consumer_data['consumer_ip'] = $request->ip();
       // $consumer_data['consumer_ip'] = request()->header('consumer-ip');
        $consumer_data['consumer_url'] = __FUNCTION__;
        $authController = new AppSettingController();
        $authenticate = $authController->apiAuthenticate($consumer_data);


        if ($authenticate == 1) {

            $user = DB::table('customers')->where('id', auth()->user()->id)->first();
            Log::debug(__FUNCTION__."user id ".$user->id.", Referral URL : ".$user->referral_url);
            if(isset($user->id) and empty($user->referral_url)){
                $updated = DB::table('customers')->where('id', auth()->user()->id)->update(['referral_url'=>$referral_url]);
                if ($updated) {
                    return returnResponse("Referral url is Updated !", HttpStatus::HTTP_OK,HttpStatus::HTTP_SUCCESS);
                } else {
                    return returnResponse("Referral url updating Failed !", HttpStatus::HTTP_BAD_REQUEST);
                }
            }else{
                return returnResponse("Referral url already Exists !");
            }

        } 
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
    }
    
    //update FCM Token
    
    public static function updateFcmToken($request)
    {
        $validator = Validator::make($request->all(), [
            'fcm_token' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), HttpStatus::HTTP_BAD_REQUEST);
        }
        
        $fcm_token = $request->fcm_token;
        

        $consumer_data = getallheaders();
        
        Log::debug(__CLASS__."::".__FUNCTION__."called with user id ".auth()->user()->id);
      /*
      $consumer_data['consumer_key'] = $request->header('consumer_key');
      $consumer_data['consumer_secret'] = $request->header('consumer_secret');
      $consumer_data['consumer_nonce'] = $request->header('consumer_nonce');
      $consumer_data['consumer_device_id'] = $request->header('consumer_device_id');
      */
      $consumer_data['consumer_ip'] = $request->ip();
       // $consumer_data['consumer_ip'] = request()->header('consumer-ip');
        $consumer_data['consumer_url'] = __FUNCTION__;
        $authController = new AppSettingController();
        $authenticate = $authController->apiAuthenticate($consumer_data);


        if ($authenticate == 1) {

            $user = DB::table('customers')->where('id', auth()->user()->id)->first();
            Log::debug(__FUNCTION__."user id = ".$user->id.", FCM Token : ".$user->fcm_token);
            if(isset($user->id)){
                try{
                    $updated = DB::table('customers')->where('id', auth()->user()->id)->update(['fcm_token'=>$fcm_token]);
                    Log::error(__FUNCTION__." Exception found! ");
                    Log::error($updated);
                    if ($updated) {
                        return returnResponse("FCM Token is Updated !", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS);
                    } else {
                        return returnResponse("FCM Token updating Failed !", HttpStatus::HTTP_BAD_REQUEST);
                    }
                } catch(Exception $ex){
                    Log::error(__FUNCTION__." Exception found! ");
                    Log::error(__FUNCTION__." Exception : ".$ex->getMessage());
                }
            }
        }
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
    }
    
   
    
    
    //generate random password
    protected static function createRandomPassword() {
		$pass = substr(uniqid(mt_rand(), true) , 0, 6);
		return Crypt::encrypt($pass);
	}
   // Generate referral token
    protected static function generateReferralToken() {
        $code = "";
        do {
            $code = substr(uniqid(mt_rand(), true), 0, 8);
            $data = DB::table('customers')->where('member_code', $code)->get();
        } while ($data->count() > 0);
        return $code;
    }

    protected static function getParentCodeByReferralCode($referral_code, $matrix_of, $count) {
        Log::debug(__CLASS__ . "::" . __FUNCTION__ . "Called");
        if (is_array($referral_code)) {
            Log::debug(__CLASS__ . "::" . __FUNCTION__ . "Called with count $count and referral code ::");
            Log::debug($referral_code);
        } else {
            Log::debug(__CLASS__ . "::" . __FUNCTION__ . "Called with refferal code $referral_code and count $count");
        }
        if ($referral_code == 'COMPANY' or $referral_code == "" or $referral_code == null) {
            if (is_array($referral_code)) {
                Log::debug('returnig parent id ');
                Log::debug($referral_code);
            } else {
                Log::debug('returnig parent id ' . $referral_code);
            }
            return $referral_code;
        }
        try {
            if ($count == 1) {
                $data = DB::table('customers')->where('parent_id', DB::table('customers')->where('member_code', $referral_code)->first()->id)->where('role_id', 2)->orderBY('updated_at')->get();
            } else {
                //$data = DB::table('users')->whereIn('prime_referral',$referral_code)->where('role_id',2)->orderBY('prime_time')->get(); 
                $data = DB::table('customers')->whereIn('parent_id', DB::table('customers')->where('member_code', $referral_code)->first()->id)->where('role_id', 2)->orderBY('prime_time')->get();
            }
            if (!isset($data)) {
                Log::error(__CLASS__ . "::" . __FUNCTION__ . " Data not found");
                return returnResponse("Error While Processing !", HttpStatus::HTTP_BAD_REQUEST);
            }

            Log::debug('Child Count' . $data->count());
            $total_child_req = pow($matrix_of, $count);
            Log::debug('total child required ' . $total_child_req . ' for count ' . $count);

            if ($data->count() > $total_child_req) {

                Log::error(__CLASS__ . "::" . __FUNCTION__ . " child count is " . count($data) . " and required only $total_child_req");
                return returnResponse("Error While Processing !", HttpStatus::HTTP_BAD_REQUEST);
            } else if (count($data) < $total_child_req) {
                if (is_array($referral_code)) {
                    $data_explode = $referral_code;
                } else {
                    $data_explode = explode("','", $referral_code);
                }

                Log::debug(__CLASS__ . " :: " . __FUNCTION__ . " lets get the parent id from array count " . count($data_explode));
                for ($i = 0; $i < count($data_explode); $i++) {
                    //$child_data2 = DB::table('users')->where('prime_referral',$data_explode[$i])->where('role_id',2)->get();
                    $child_data2 = DB::table('customers')->where('parent_id', DB::table('customers')->where('id', $data_explode[$i])->first()->id)->get();
                    // $child_data2 = DatabaseFactory::executeQueryAndGetData("select * from members where parent_id = '{$data_explode[$i]}'", $con);
                    Log::debug(__CLASS__ . " :: " . __FUNCTION__ . " child count found as " . count($child_data2) . " for member id $data_explode[$i]");
                    if (count($child_data2) < $matrix_of) {
                        Log::debug(__CLASS__ . " :: " . __FUNCTION__ . " returning member id for parent id as $data_explode[$i]");
                        return $data_explode[$i];
                    }
                }
            } else {
                $count++;
                //            $member_id_new = "";
                //                $comma = "";
                //            foreach ($data as $value) {
                //                    $member_id_new .= $comma . $value->member_code;
                //                    $comma = "','";
                //            }

                $member_id_new = array();
                foreach ($data as $value) {
                    array_push($member_id_new, $value->member_code);
                }

                Log::debug(__CLASS__ . " :: " . __FUNCTION__ . " lets call the method again with member id updated as ");
                Log::debug($member_id_new);
                return self::getParentCodeByReferralCode($member_id_new, $matrix_of, $count);
            }
        } catch (Exception $e) {
            
        }
    }

    // distribute level income
    protected static function levelIncomeDistribute($level, $referral_code, $parent_code, $child_id) {
        Log::debug(__CLASS__ . "::" . __FUNCTION__ . "Called with refereal code $referral_code and parent code $parent_code");
        if ($parent_code == 'COMPANY') {
            Log::debug('returnig parent id ' . $parent_code);
            return true;
        }
        try {

            Log::debug('calculating income for level ' . $level);
            $income = self::calculateLevelIncome($level);

            if ($income == 'fail') {
                return false;
            }
            DB::table('user_level_incomes')->insert([
                'payment_date' => date('Y-m-d H:i:s'),
                'member_code' => $parent_code,
                'amount' => $income,
                'referral_code' => $referral_code,
                'child_id' => $child_id,
                'level' => $level,
            ]);

            $level++;
            Log::debug('level updated to ' . $level);
            if ($level > 8) {
                Log::debug('returning true ');
                return true;
            }
            $parent_code_new = self::getParentCode($parent_code);
            Log::debug("Got PArent Id $parent_code_new");
            if (empty($parent_code_new)) {

                return false;
            } else {
                return self::levelIncomeDistribute($level, $referral_code, $parent_code_new, $child_id);
            }
        } catch (Exception $e) {
            Log::error('Error while distributing level income');
        }
        return false;
    }

    public static function getParentCode($child_code) {
        return DB::table('users')->where('member_code', $child_code)->first()->parent_id;
    }

    protected static function calculateLevelIncome($level) {

        $income = DB::table('level_income')->where('level', $level)->first();
        if (isset($income->income)) {
            Log::debug("returning income $income->income for level $level");
            return $income->income;
        }
        Log::error('Income getting Failed');
        return 'fail';
    }

    // distribute Direct Income

    protected static function distributeDirectIncome($child_id, $parent_code) {
        $referralData = DB::table('users')->where('normal_referral', $parent_code)->where('role_id', 2)->get();
        if ($referralData->count() < 5 && isset($referralData[0]->id)) {

            $user = User::where('member_code', $parent_code)->first();
            $oldBalance = $user->m_wallet;
            $newBalance = $oldBalance + 20;
            $user->m_wallet = $newBalance;
            if ($user->save()) {
                $directIncome = new UserDirectIncome;
                $directIncome->payment_date = Carbon::now();
                $directIncome->member_code = $parent_code;
                $directIncome->amount = 20;
                $directIncome->child_id = $child_id;
                if (!$directIncome->save()) {
                    return false;
                }
            }

            return true;
        }
        return false;
    }

     public static function updateSponsorCount($cust_info, $is_active = null) {
        Log::debug(__CLASS__ . "::" . __FUNCTION__ . " Called with customer id $cust_info->parent_id and is active $is_active");
        if (empty($cust_info->parent_id) || $cust_info->parent_id == null) {
            return true;
        }
        try {
            $column_name = 'sponsor_count';
            $column_name_level = 'level';
            if (!empty($is_active)) {
                $column_name = $is_active . '_' . $column_name;
                $column_name_level = $is_active . '_' . $column_name_level;
            }
            $parent_info = Customers::find($cust_info->parent_id);
            
            
            if ($parent_info->is_boosted == 'N' && $parent_info->is_active =='YES') {
                $current_sponsor_count = Customers::where('parent_id',$parent_info->id)->where('id','!=',$cust_info->id)->whereDate('created_at','>=',Carbon::now()->subDays(5)->toDateTimeString())->count();
            
                if ($current_sponsor_count >= 10) {
                    if (!Customers::where('id', $parent_info->id)->update(['is_boosted' => 'Y'])) {
                        Log::error(__CLASS__ . "::" . __FUNCTION__ . " Error updating is_boosted for customer id  $parent_info->id");
                        return false;
                    }
                }
            }
            if (!Customers::where('id', $parent_info->id)->increment($column_name)) {
                Log::error(__CLASS__ . "::" . __FUNCTION__ . " Error updating $column_name for customer id  $parent_info->id");
                return false;
            }
            if ($parent_info->level < 11) {
                if (!Customers::where('id', $parent_info->id)->increment($column_name_level)) {
                    Log::error(__CLASS__ . "::" . __FUNCTION__ . " Error updating $column_name for customer id  $parent_info->id");
                    return false;
                }
            }
            return true;
        } catch (\Exception $e) {
            Log::error(__CLASS__ . "::" . __FUNCTION__ . " Exception Occured " . $e->getMessage());
        }
        return false;
    }

    public static function updateTeamCount($cust_info, $is_active = '', $level = 1) {
        Log::debug(__CLASS__ . "::" . __FUNCTION__ . " Called with customer id $cust_info->id and is active $is_active and level $level");


        try {
            if ($level > 11) {
                return true;
            }
            Log::debug(__CLASS__ . "::" . __FUNCTION__ . " fetching parent information for customer id  $cust_info->id");
            if ($cust_info->parent_id == null) {
                Log::debug(__CLASS__ . "::" . __FUNCTION__ . " parent id found as $cust_info->parent_id returning true");
                return true;
            }
            $parent_info = Customers::find($cust_info->parent_id);
            $column_name = 'team_count';
            if (!empty($is_active)) {
                $column_name = $is_active . '_' . $column_name;
            }
            if (!Customers::where('id', $parent_info->id)->increment($column_name)) {
                Log::error(__CLASS__ . "::" . __FUNCTION__ . " Error updating $column_name for customer id  $parent_info->id");
                return false;
            }



            $level++;
            return self::updateTeamCount($parent_info, $is_active, $level);
        } catch (\Exception $e) {
            Log::error(__CLASS__ . "::" . __FUNCTION__ . " Exception Occured " . $e->getMessage());
        }
        return false;
    }

    // Activate customer for pool and give a coupon balance of Rs. 200 
    // Activate customer for pool and give a coupon balance of Rs. 200 
    public static function upgradeMemberSubscription($cust_info)
    {
        Log::debug(__CLASS__." :: ".__FUNCTION__." started with customer core id as $cust_info->id");    
        $currentTime = Carbon::now();
        Log::debug(__CLASS__." :: ".__FUNCTION__." current time found as $currentTime");
        Log::debug(__CLASS__." :: ".__FUNCTION__." Starting try catch !!");
        try {
            Log::debug(__CLASS__." :: ".__FUNCTION__." lets fetch the parent id from sponsor id ($cust_info->sponsor_id) for customer $cust_info->id !! ");
            $parent_id = self::getParentCodeByReferralCode($cust_info->sponsor_id, 4, 1);
        
        }
        catch (JWTException $exc) {
            Log::error(__CLASS__."::".__FUNCTION__." Exception : ".$exc->getMessage());
            return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
        }
        return returnResponse("Error while processing !!");
       
    }


}
