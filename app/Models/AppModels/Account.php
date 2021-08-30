<?php

namespace App\Models\AppModels;

use App\Http\Controllers\App\AlertController;
use App\Http\Controllers\App\AppSettingController;
use Auth;
use DB;
use File;
use Log;
use Validator;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\HttpStatus;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Core\User;

use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTFactory;
use App\Models\Core\WalletModel;
use App\Models\Eloquent\Customers;
class Account extends Model
{
    
    
    
    // validateReferralCode
    public static function validateReferralCode($request)
    {
        Log::debug(__CLASS__." :: ".__FUNCTION__." started.. Validating parameters");
        $validator = Validator::make($request->all(), [
            'referral_code' => 'required',
            
        ]);
        Log::debug(__CLASS__." :: ".__FUNCTION__." parameter validated, lets validate the response ");
        if($validator->fails()){
            Log::debug(__CLASS__." :: ".__FUNCTION__." validator failed with error, returning the response ");
            return returnResponse($validator->errors(), HttpStatus::HTTP_UNPROCESSABLE_ENTITY);
        }
        Log::debug(__CLASS__." :: ".__FUNCTION__." proceeding further");
        $referral_code = $request->referral_code;
        
       
        $consumer_data = getallheaders();
        
        $consumer_data['consumer_ip'] = $request->ip();
        // $consumer_data['consumer_ip'] = request()->header('consumer-ip');
        $consumer_data['consumer_url'] = __FUNCTION__;
        $authController = new AppSettingController();
        $authenticate = $authController->apiAuthenticate($consumer_data);
        Log::debug(__CLASS__." :: ".__FUNCTION__." fetching settings !!");
        
        Log::debug(__CLASS__." :: ".__FUNCTION__." authenticating user now !!");
        if($authenticate == 1 && auth()->user()->id) {
            Log::debug(__CLASS__." :: ".__FUNCTION__." fetching user data from database !!");
            
            Log::debug(__CLASS__." :: ".__FUNCTION__." starting try catch");

            try{
                Log::debug(__CLASS__." :: ".__FUNCTION__." inside try catch !!");
                
                    $user = Customers::where('member_code', $referral_code)->where('id','!=',auth()->user()->id)->first();
                Log::debug(__CLASS__." :: ".__FUNCTION__." user data found validating!!");
                
                
                
               if(isset($user->name)){
                   
                   Log::info(__CLASS__." :: ".__FUNCTION__." all set, committing data now");
                return returnResponse("member found !", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS,$user->name);
               }else{
                   return returnResponse("Invalid Referral code !");
               }
                
                
                        
            }catch(\Exception $e){
                Log::error("Error Occured".$e->getMessage());
                return returnResponse("Some Error Occured please try again !");
            }
        }

        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
    }
    
    //update Cartilo Pin
    public static function updateReferralCode($request)
    {
        Log::debug(__CLASS__." :: ".__FUNCTION__." started.. Validating parameters");
        $validator = Validator::make($request->all(), [
            'referral_code' => 'required',
            
        ]);
        Log::debug(__CLASS__." :: ".__FUNCTION__." parameter validated, lets validate the response ");
        if($validator->fails()){
            Log::debug(__CLASS__." :: ".__FUNCTION__." validator failed with error, returning the response ");
            return returnResponse($validator->errors(), HttpStatus::HTTP_UNPROCESSABLE_ENTITY);
        }
        Log::debug(__CLASS__." :: ".__FUNCTION__." proceeding further");
        $referral_code = $request->referral_code;
        
       
        $consumer_data = getallheaders();
        
        $consumer_data['consumer_ip'] = $request->ip();
        // $consumer_data['consumer_ip'] = request()->header('consumer-ip');
        $consumer_data['consumer_url'] = __FUNCTION__;
        $authController = new AppSettingController();
        $authenticate = $authController->apiAuthenticate($consumer_data);
        Log::debug(__CLASS__." :: ".__FUNCTION__." fetching settings !!");
        
        Log::debug(__CLASS__." :: ".__FUNCTION__." authenticating user now !!");
        if($authenticate == 1 && auth()->user()->id) {
            Log::debug(__CLASS__." :: ".__FUNCTION__." fetching user data from database !!");
            $user = Customers::where('id', auth()->user()->id)->first();
            Log::debug(__CLASS__." :: ".__FUNCTION__." user data found validating!!");
            if($user->id != auth()->user()->id){
                Log::error(__CLASS__." :: ".__FUNCTION__." user data fetching failed !!");
                return returnResponse("User data fetching failed !");
            }
            Log::debug(__CLASS__." :: ".__FUNCTION__." starting try catch");

            try{
                Log::debug(__CLASS__." :: ".__FUNCTION__." inside try catch !!");
                 $parent_info = Customers::where('member_code', $referral_code)->first();
                  if(empty($user->parent_id)) { 
                 $user->parent_id = $parent_info->id;
                    $user->referred_by = $parent_info->id;
                  }else{
                      return returnResponse("Referral code already updated");
                  }
                
               if($user->save()){
                   
                   Log::info(__CLASS__." :: ".__FUNCTION__." all set, committing data now");
                DB::commit();
                return returnResponse("Referral Code Updated Successfully !", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS);
               }
                
                
                        
            }catch(\Exception $e){
                Log::error("Error Occured".$e->getMessage());
                return returnResponse("Some Error Occured please try again !");
            }
        }

        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
    }
     //update Cartilo Pin
    public static function updateFcmToken($request)
    {
        Log::debug(__CLASS__." :: ".__FUNCTION__." started.. Validating parameters");
        $validator = Validator::make($request->all(), [
            'fcm_token' => 'required',
            
        ]);
        Log::debug(__CLASS__." :: ".__FUNCTION__." parameter validated, lets validate the response ");
        if($validator->fails()){
            Log::debug(__CLASS__." :: ".__FUNCTION__." validator failed with error, returning the response ");
            return returnResponse($validator->errors(), HttpStatus::HTTP_UNPROCESSABLE_ENTITY);
        }
        Log::debug(__CLASS__." :: ".__FUNCTION__." proceeding further");
        $fcm_token = $request->fcm_token;
        
       
        $consumer_data = getallheaders();
        
        $consumer_data['consumer_ip'] = $request->ip();
        // $consumer_data['consumer_ip'] = request()->header('consumer-ip');
        $consumer_data['consumer_url'] = __FUNCTION__;
        $authController = new AppSettingController();
        $authenticate = $authController->apiAuthenticate($consumer_data);
        Log::debug(__CLASS__." :: ".__FUNCTION__." fetching settings !!");
        
        Log::debug(__CLASS__." :: ".__FUNCTION__." authenticating user now !!");
        if($authenticate == 1 && auth()->user()->id) {
            Log::debug(__CLASS__." :: ".__FUNCTION__." fetching user data from database !!");
            $user = Customers::where('id', auth()->user()->id)->first();
            Log::debug(__CLASS__." :: ".__FUNCTION__." user data found validating!!");
            if($user->id != auth()->user()->id){
                Log::error(__CLASS__." :: ".__FUNCTION__." user data fetching failed !!");
                return returnResponse("User data fetching failed !");
            }
            Log::debug(__CLASS__." :: ".__FUNCTION__." starting try catch");

            try{
                Log::debug(__CLASS__." :: ".__FUNCTION__." inside try catch !!");
                
                    $user->fcm_token = $fcm_token;
                
                
               if($user->save()){
                   
                   Log::info(__CLASS__." :: ".__FUNCTION__." all set, committing data now");
                DB::commit();
                return returnResponse("Fcm Token Updated Successfully !", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS);
               }
                
                
                        
            }catch(\Exception $e){
                Log::error("Error Occured".$e->getMessage());
                return returnResponse("Some Error Occured please try again !");
            }
        }

        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
    }
    
    //create Cartilo Pin
    public static function createMPin($request)
    {
        Log::debug(__CLASS__." :: ".__FUNCTION__." started.. Validating parameters");
        $validator = Validator::make($request->all(), [
            'pin' => 'required',
            
        ]);
        Log::debug(__CLASS__." :: ".__FUNCTION__." parameter validated, lets validate the response ");
        if($validator->fails()){
            Log::debug(__CLASS__." :: ".__FUNCTION__." validator failed with error, returning the response ");
            return returnResponse($validator->errors(), HttpStatus::HTTP_UNPROCESSABLE_ENTITY);
        }
        Log::debug(__CLASS__." :: ".__FUNCTION__." proceeding further");
        $pin = $request->pin;
        // check the amount shoud not be less than 0
        if(empty($pin)){
            Log::info(__CLASS__." :: ".__FUNCTION__." can not process the withdrawal request with pin $pin");
            return returnResponse("pin can not be empty");
        }
       
        $consumer_data = getallheaders();
        
        $consumer_data['consumer_ip'] = $request->ip();
        // $consumer_data['consumer_ip'] = request()->header('consumer-ip');
        $consumer_data['consumer_url'] = __FUNCTION__;
        $authController = new AppSettingController();
        $authenticate = $authController->apiAuthenticate($consumer_data);
        Log::debug(__CLASS__." :: ".__FUNCTION__." fetching settings !!");
        $settings = $authController->getSetting();
        
        Log::debug(__CLASS__." :: ".__FUNCTION__." authenticating user now !!");
        if($authenticate == 1 && auth()->user()->id) {
            Log::debug(__CLASS__." :: ".__FUNCTION__." fetching user data from database !!");
            $user = Customers::where('id', auth()->user()->id)->first();
            Log::debug(__CLASS__." :: ".__FUNCTION__." user data found validating!!");
            if($user->id != auth()->user()->id){
                Log::error(__CLASS__." :: ".__FUNCTION__." user data fetching failed !!");
                return returnResponse("User data fetching failed !");
            }
            Log::debug(__CLASS__." :: ".__FUNCTION__." starting try catch");

            try{
                Log::debug(__CLASS__." :: ".__FUNCTION__." inside try catch !!");
                if($user->m_pin == null){
                    $user->m_pin = $pin;
                }else{
                   return returnResponse("Pin already created !"); 
                }
                
               if($user->save()){
                   
                   Log::info(__CLASS__." :: ".__FUNCTION__." all set, committing data now");
                DB::commit();
                return returnResponse("Pin created Successfully !", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS);
               }
                
                
                        
            }catch(\Exception $e){
                Log::error("Error Occured".$e->getMessage());
                return returnResponse("Some Error Occured please try again !");
            }
        }

        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
    }
    
    //update Cartilo Pin
    public static function updateMPin($request)
    {
        Log::debug(__CLASS__." :: ".__FUNCTION__." started.. Validating parameters");
        $validator = Validator::make($request->all(), [
            'pin' => 'required',
            'old_pin' => 'required',
            
        ]);
        Log::debug(__CLASS__." :: ".__FUNCTION__." parameter validated, lets validate the response ");
        if($validator->fails()){
            Log::debug(__CLASS__." :: ".__FUNCTION__." validator failed with error, returning the response ");
            return returnResponse($validator->errors(), HttpStatus::HTTP_UNPROCESSABLE_ENTITY);
        }
        Log::debug(__CLASS__." :: ".__FUNCTION__." proceeding further");
        $pin = $request->pin;
        $old_pin = $request->old_pin;
        // check the amount shoud not be less than 0
        if(empty($pin)){
            Log::info(__CLASS__." :: ".__FUNCTION__." can not process the withdrawal request with pin $pin");
            return returnResponse("pin can not be empty");
        }
       
        $consumer_data = getallheaders();
        
        $consumer_data['consumer_ip'] = $request->ip();
        // $consumer_data['consumer_ip'] = request()->header('consumer-ip');
        $consumer_data['consumer_url'] = __FUNCTION__;
        $authController = new AppSettingController();
        $authenticate = $authController->apiAuthenticate($consumer_data);
        Log::debug(__CLASS__." :: ".__FUNCTION__." fetching settings !!");
        $settings = $authController->getSetting();
        
        Log::debug(__CLASS__." :: ".__FUNCTION__." authenticating user now !!");
        if($authenticate == 1 && auth()->user()->id) {
            Log::debug(__CLASS__." :: ".__FUNCTION__." fetching user data from database !!");
            $user = Customers::where('id', auth()->user()->id)->first();
            Log::debug(__CLASS__." :: ".__FUNCTION__." user data found validating!!");
            if($user->id != auth()->user()->id){
                Log::error(__CLASS__." :: ".__FUNCTION__." user data fetching failed !!");
                return returnResponse("User data fetching failed !");
            }
            Log::debug(__CLASS__." :: ".__FUNCTION__." starting try catch");

            try{
                Log::debug(__CLASS__." :: ".__FUNCTION__." inside try catch !!");
                if($user->m_pin == $old_pin){
                    $user->m_pin = $pin;
                }else{
                   return returnResponse("Pin not matched !"); 
                }
                
               if($user->save()){
                   
                   Log::info(__CLASS__." :: ".__FUNCTION__." all set, committing data now");
                DB::commit();
                return returnResponse("Pin changed Successfully !", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS);
               }
                
                
                        
            }catch(\Exception $e){
                Log::error("Error Occured".$e->getMessage());
                return returnResponse("Some Error Occured please try again !");
            }
        }

        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
    }
    
    
    //make withdrawal request
    public static function makeKycRequest($request)
    {
        Log::debug(__CLASS__." :: ".__FUNCTION__." called");
        $consumer_data = getallheaders();
        Log::debug($consumer_data);
        /*
        $consumer_data['consumer_key'] = $request->header('consumer_key');
        $consumer_data['consumer_secret'] = $request->header('consumer_secret');
        $consumer_data['consumer_nonce'] = $request->header('consumer_nonce');
        $consumer_data['consumer_device_id'] = $request->header('consumer_device_id');
        */
        $consumer_data['consumer_ip'] = $request->ip();
       // $consumer_data['consumer_ip'] = request()->header('consumer-ip');
        $consumer_data['consumer_url'] = __FUNCTION__;
        Log::debug($consumer_data);
        $authController = new AppSettingController();
        $authenticate = $authController->apiAuthenticate($consumer_data);
         $settings = $authController->getSetting();
         if($settings['kyc_service'] != 'Y'){
            return returnResponse("Kyc service is temporarily closed !"); 
         }

        if ($authenticate == 1 && auth()->user()->id) {
            
            if(auth()->user()->kyc =='APPROVED'){
                return returnResponse("Kyc is already approved !"); 
            }
            
            $kycInfo = self::getKycInfo();
            if(!isset($kycInfo->id)){
                $validator = Validator::make($request->all(), [
                    'name_on_bank' => 'required',
                    'pan_no' => 'required|unique:customers_kyc,pan_no',
                    'adhar_no' => 'required|unique:customers_kyc,adhar_no',
                    'bank_name' => 'required',
                    'ifsc_code' => 'required',
                    'account_no' => 'required',
                    'adhar_front_file' => 'required|file',
                    'adhar_back_file' => 'required|file',
                    'pan_front_file' => 'required|file',
                ]);
                Log::debug(__CLASS__."::".__FUNCTION__."called with customer id ".auth()->user()->id);
                if($validator->fails()){
                    return returnResponse($validator->errors(), HttpStatus::HTTP_UNPROCESSABLE_ENTITY);
                }
            }

            

            Log::debug(__CLASS__."::".__FUNCTION__."called 2 with customer id ".auth()->user()->id);
            $data = Account::checkIfsc($request);
            Log::debug($data);
            if(isset($data["BANK"])){
                return returnResponse("Invalid IFSC Code", HttpStatus::HTTP_UNPROCESSABLE_ENTITY);
            }
            $name_on_bank = $request->name_on_bank;
            $pan_no = $request->pan_no;
            $adhar_card_no = $request->adhar_no;
            $bank_name = $request->bank_name;
            $ifsc_code = $request->ifsc_code;
            $account_no = $request->account_no;
            $pan_front = $request->file('pan_front_file');
            $adhar_front = $request->file('adhar_front_file');
            $adhar_back = $request->file('adhar_back_file');
            
             try{
                DB::beginTransaction();
                if(!isset($kycInfo->id)){
                    $adhar_front_path = uploadImage($adhar_front, 'document/kyc', 'ADHARF');
                    if(!$adhar_front_path){
                        return returnResponse("Adhar Card Front file uploading failed !"); 
                    }
                } else if($adhar_front != null){
                    $adhar_front_path = uploadImage($adhar_front, 'document/kyc', 'ADHARF');
                    if(!$adhar_front_path){
                        return returnResponse("Adhar Card Front file uploading failed !"); 
                    }
                }
                
                if(!isset($kycInfo->id)){
                    $adhar_back_path = uploadImage($adhar_back, 'document/kyc', 'ADHARB');
                    if(!$adhar_back_path){
                        return returnResponse("Adhar Card Back file uploading failed !"); 
                    }
                } else if($adhar_back != null) {
                    $adhar_back_path = uploadImage($adhar_back, 'document/kyc', 'ADHARB');
                    if(!$adhar_back_path){
                        return returnResponse("Adhar Card Back file uploading failed !"); 
                    }
                }
                if(!isset($kycInfo->id)){
                    $pan_front_path = uploadImage($pan_front, 'document/kyc', 'PANF');
                    if(!$pan_front_path){
                        return returnResponse("Pan Card Front file uploading failed !"); 
                    }
                } else if($pan_front != null) {
                    $pan_front_path = uploadImage($pan_front, 'document/kyc', 'PANF');
                    if(!$pan_front_path){
                        return returnResponse("Pan Card Front file uploading failed !"); 
                    }
                }
                
                if(!isset($kycInfo->id)){
                    $kyc_req_id = DB::table('customers_kyc')->insertGetId([
                        'customers_id'		            => auth()->user()->id,
                        'name_on_bank'                  =>	$name_on_bank,
                        'bank_name'                     =>	$bank_name,
                        'account_no'                    =>	$account_no,
                        'ifsc_code'			    =>	$ifsc_code,
                        'adhar_no'                      =>	$adhar_card_no,
                        'pan_no'                        =>	$pan_no,
                        'adhar_front_file'              =>	$adhar_front_path,
                        'adhar_back_file'               =>	$adhar_back_path,
                        'pan_front_file'                =>	$pan_front_path,
                        'created_at'                    =>	Carbon::now(),
                    ]);
                } else {
                    $data = array();
                    $data["name_on_bank"] = $name_on_bank;
                    $data["bank_name"] = $bank_name;
                    $data["account_no"] = $account_no;
                    $data["ifsc_code"] = $ifsc_code;
                    $data["adhar_no"] = $adhar_card_no;
                    $data["pan_no"] = $pan_no;
                    $data["status"] = "PENDING";
                    $data["reason"] = "";
                    
                    if(isset($adhar_front_path)){
                        $data["adhar_front_file"] = $adhar_front_path;
                    }
                    if(isset($adhar_back_path)){
                        $data["adhar_back_file"] = $adhar_back_path;
                    }
                    if(isset($pan_front_path)){
                        $data["pan_front_file"] = $pan_front_path;
                    }
                    $kyc_req_id = DB::table('customers_kyc')->where('customers_id', auth()->user()->id)->update($data);
                }
                
                if(!empty($kyc_req_id) && $kyc_req_id > 0){
                    $updated = DB::table('users')->where('id', auth()->user()->id)->update(['kyc' => "PENDING"]);
                    if ($updated) {
                        DB::commit();
                        return returnResponse("Kyc uploaded successfully and is PENDING for verification !", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS);
                    }  else {
                        Log::error("Error Occured at kyc upload... ");
                        return returnResponse("Some Error Occured please try again !");
                    }
                }
                
                
             } catch(\Exception $e){
                Log::error("Exception Occured :: ".$e->getMessage());
                return returnResponse("Some Error Occured please try again !");
            }
            
            Log::error("Error Occured at kyc upload ");
            return returnResponse("Some Error Occured please try again !");
        } 
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
    }

    
    //Upload Avatar
    public static function uploadAvatar($request)
    {
        Log::debug(__CLASS__." :: ".__FUNCTION__." called");
        $consumer_data = getallheaders();
        Log::debug($consumer_data);
        /*
        $consumer_data['consumer_key'] = $request->header('consumer_key');
        $consumer_data['consumer_secret'] = $request->header('consumer_secret');
        $consumer_data['consumer_nonce'] = $request->header('consumer_nonce');
        $consumer_data['consumer_device_id'] = $request->header('consumer_device_id');
        */
        $consumer_data['consumer_ip'] = $request->ip();
       // $consumer_data['consumer_ip'] = request()->header('consumer-ip');
        $consumer_data['consumer_url'] = __FUNCTION__;
        Log::debug($consumer_data);
        $authController = new AppSettingController();
        $authenticate = $authController->apiAuthenticate($consumer_data);
        $settings = $authController->getSetting();
         
        if ($authenticate == 1 && auth()->user()->id) {
            
            $validator = Validator::make($request->all(), [
                'avatar' => 'required|file',
            ]);
            Log::debug(__CLASS__."::".__FUNCTION__."called with customer id ".auth()->user()->id);
            if($validator->fails()){
                return returnResponse($validator->errors(), HttpStatus::HTTP_UNPROCESSABLE_ENTITY);
            }
            

            Log::debug(__CLASS__."::".__FUNCTION__."called 2 with customer id ".auth()->user()->id);
            $avatar = $request->file('avatar');
            
             try{
                $avatar_path = uploadImage($avatar, 'document/avatar', 'A');
                if(!$avatar_path){
                    return returnResponse("Profile image uploading failed !");
                }
                if(!empty($avatar_path)){
                    $updated = DB::table('users')->where('id', auth()->user()->id)->update(['avatar' => $avatar_path]);
                    if ($updated) {
                        return returnResponse("Profile uploaded successfully !", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS);
                    }  else {
                        Log::error("Error Occured at profile upload... ");
                        return returnResponse("Some Error Occured please try again !");
                    }
                }
             } catch(\Exception $e){
                Log::error("Exception Occured :: ".$e->getMessage());
                return returnResponse("Some Error Occured please try again !");
            }
            
            Log::error("Error Occured at Profile upload ");
            return returnResponse("Some Error Occured please try again !");
        }
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
    }

    public static function makeWithdrawRequest($request)
    {
        Log::debug(__CLASS__." :: ".__FUNCTION__." started.. Validating parameters");
        $validator = Validator::make($request->all(), [
            'amount' => 'required',
            'description' => 'nullable',
        ]);
        Log::debug(__CLASS__." :: ".__FUNCTION__." parameter validated, lets validate the response ");
        if($validator->fails()){
            Log::debug(__CLASS__." :: ".__FUNCTION__." validator failed with error, returning the response ");
            return returnResponse($validator->errors(), HttpStatus::HTTP_UNPROCESSABLE_ENTITY);
        }
        Log::debug(__CLASS__." :: ".__FUNCTION__." proceeding further");
        $amount = $request->amount;
        // check the amount shoud not be less than 0
        Log::debug(__CLASS__." :: ".__FUNCTION__." lets check if withdrawal amount (Rs. $amount) is less than 0");
        if($amount < 0){
            Log::error(__CLASS__." :: ".__FUNCTION__." withdrawal amount found as $amount");
            Log::info(__CLASS__." :: ".__FUNCTION__." can not process the withdrawal request with amount $amount");
            return returnResponse("Can not process withdrawal with amount Rs. $amount !");
        }
        $description = $request->description;
        Log::debug(__CLASS__."::".__FUNCTION__."called with customer id ".auth()->user()->id." and amount $amount");
        

        Log::debug(__CLASS__." :: ".__FUNCTION__." fetching customer data !!");
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
        Log::debug(__CLASS__." :: ".__FUNCTION__." fetching settings !!");
        $settings = $authController->getSetting();
        Log::debug(__CLASS__." :: ".__FUNCTION__." settings found, validating !!");
        if($settings['withdraw_request_service'] != 'Y'){
            Log::debug(__CLASS__." :: ".__FUNCTION__." wihdraw service is closed, returning !!");
            return returnResponse("Withdrawal service is temporarily closed !"); 
        }
        Log::debug(__CLASS__." :: ".__FUNCTION__." validating for min amount allowed for withdrawal !!");
        if($settings['withdraw_request_min_amt'] > $amount){
            Log::error(__CLASS__." :: ".__FUNCTION__." min amount allowed for withdrawal is Rs. ".$settings['withdraw_request_min_amt']." and requested amount is $amount");
            Log::error(__CLASS__." :: ".__FUNCTION__." returning response !!");
            return returnResponse("Minimum withdraw amount permitted is Rs. ".$settings['withdraw_request_min_amt']." !"); 
        }
        Log::debug(__CLASS__." :: ".__FUNCTION__." validating for max amount allowed for withdrawal !!");
        if($settings['withdraw_request_max_amt'] < $amount){
            Log::error(__CLASS__." :: ".__FUNCTION__." Max amount allowed for withdrawal is Rs. ".$settings['withdraw_request_max_amt']." and requested amount is $amount");
            Log::error(__CLASS__." :: ".__FUNCTION__." returning response !!");
            return returnResponse("Maximum withdraw amount permitted is Rs. ".$settings['withdraw_request_max_amt']." !"); 
        }
        Log::debug(__CLASS__." :: ".__FUNCTION__." authenticating user now !!");
        if($authenticate == 1 && auth()->user()->id) {
            Log::debug(__CLASS__." :: ".__FUNCTION__." fetching user data from database !!");
            $user = DB::table('users')->where('id', auth()->user()->id)->first();
            Log::debug(__CLASS__." :: ".__FUNCTION__." user data found validating!!");
            if($user->id != auth()->user()->id){
                Log::error(__CLASS__." :: ".__FUNCTION__." user data fetching failed !!");
                return returnResponse("User data fetching failed !");
            }
            Log::debug(__CLASS__." :: ".__FUNCTION__." starting try catch");

            try{
                Log::debug(__CLASS__." :: ".__FUNCTION__." inside try catch !!");
                Log::debug(__CLASS__." :: ".__FUNCTION__." fetching main wallet balance and blocked amount !!");
                $m_wallet_balance = $user->m_wallet;
                $m_wallet_block = $user->m_wallet_block;
                Log::debug("m wallet balance $m_wallet_balance");
                Log::debug("m wallet block $m_wallet_block");
                $withdrawable_balance = $m_wallet_balance - $m_wallet_block;
                Log::debug("withdrawable balance is $withdrawable_balance");
                if($withdrawable_balance < $amount){
                    Log::debug("Insufficient Withdrawable Balance Rs. $withdrawable_balance !");
                    return returnResponse("Insufficient Withdrawable Balance Rs. $withdrawable_balance !");
                }
                Log::debug("proceeding for withdrawal as withdrawable_balance is $withdrawable_balance and amount is $amount");
                $balance_after = $user->m_wallet - $amount;
                Log::debug("balance after us updated as $balance_after");
                Log::debug("starting DB transaction");
                DB::beginTransaction();
                Log::debug("Making entry in withdraw table");
                $withdraw_req_id = DB::table('withdrawal_request')->insertGetId([
                                'customer_id'		            =>	$user->id,
                                'amount'		      =>	$amount,
                                'description'			        =>	$description,
                              ]);
                Log::debug(__CLASS__." :: ".__FUNCTION__." withdraw request saved with id $withdraw_req_id");
                
                if(empty($withdraw_req_id)){
                        Log::error("Withdraw request saving failed ");
                        return returnResponse("Withdraw Request saving failed !!");
                }
                Log::debug(__CLASS__." :: ".__FUNCTION__." lets debit in main wallet");
                if(!WalletModel::debitFromMainWallet($user->id, $amount, $balance_after, "Withdraw Request".$description, $withdraw_req_id, 'WITHDRAWAL')){
                    Log::error("Error Occured at wallet update ");
                    return returnResponse("Some Error Occured please try again !"); 
                }
                
                Log::info(__CLASS__." :: ".__FUNCTION__." all set, committing data now");
                DB::commit();
                return returnResponse("Withdrawal Request Made Successfully !", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS);
                        
            }catch(\Exception $e){
                Log::error("Error Occured".$e->getMessage());
                return returnResponse("Some Error Occured please try again !");
            }
        }

        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
    }
    
    
    public static function getKycInfo(){
        return DB::table('customers_kyc')
        ->where('customers_id', '=', auth()->user()->id)->where('status', '!=', 'DELETED')->first();
    }

    public static function checkIfsc($request)
    {
        $ifscCode = $request->ifsc_code;
        Log::debug("IFSC Code : ".$ifscCode);
        $url = "https://ifsc.razorpay.com/".$ifscCode;
        Log::debug("Calling URL : ".$url);
        $data = callUrl($url);
        Log::debug("Data : ".$data);
        return $data;
    }
}
