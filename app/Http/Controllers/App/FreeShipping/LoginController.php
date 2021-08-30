<?php
namespace App\Http\Controllers\App\FreeShipping;

use Illuminate\Foundation\Auth\ThrottlesLogins;
use Validator;
use Mail;
use DateTime;
use Auth;
use DB;
use Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\AppModels\FreeShipping\Customer;
use App\Models\AppModels\FreeShipping\Login;
use App\Models\Core\User;
use App\Helpers\HttpStatus;
use App\Http\Controllers\App\AppSettingController;
use App\Models\Core\OtpHistory;
use App\Models\Core\SmsService;
use App\Models\AppModels\CustomerLogin;


class LoginController extends Controller
{

    //login
    public function processLogin(Request $request){
      return Customer::processLogin($request);
    }

    //Send Otp for customer Login
    public static function sendLoginOtp(Request $request)
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
    
    public static function verifyLoginOtp(Request $request)
    {
        $consumer_data = getallheaders();
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

                        $now = Carbon::now();
                        $data = array(
                            'access_token' => $token,
                            'token_type' => 'Bearer',
                            'expires_in' => JWTFactory::getTTL() * 60,
                            'last_login' => substr($now, 0, strlen($now)),
                            'user' => $user,
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
    
    
    //Validate Refferal code
    public function validateReferralCode(Request $request){
     return Customer::validateReferralCode($request);

    }
    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request) {
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
        auth()->logout();

        return response()->json(['message' => 'User successfully signed out']);
       }
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh(Request $request) {
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
        return $this->createNewToken(auth()->refresh());
        }
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile(Request $request) {
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

        if ($authenticate == 1 && auth()->user()->id) {
        
            $user = User::leftJoin('images','images.id', '=', 'users.avatar')
            ->leftJoin('user_to_address','users.id', '=', 'user_to_address.user_id')
            ->leftJoin('address_book','address_book.address_book_id', '=', 'user_to_address.address_book_id')
            ->select('users.*', 'users.avatar as image','address_book.*')
            ->where('users.id', auth()->user()->id)->where('users.role_id','2')->first();
            $products = DB::table('liked_products')->select('liked_products_id as products_id')
                ->where('liked_customers_id', '=', auth()->user()->id)
                ->get();

            if (count($products) > 0) {
                $liked_products = $products;
            } else {
                $liked_products = array();
            }
            Log::debug($user);

            Log::debug($user->member_code);
            if(isset($user->member_code)){
                $user_level_income = DB::table('user_level_incomes')
                ->where('member_code', '=', $user->member_code)
                ->where('status', '=', "REGISTERED")
                ->where('is_paid', '=', "N")
                ->sum('amount');
                $user->f_wallet = $user_level_income;
            }
            Log::debug($user);
            $kyc_info = DB::table('customers_kyc')->where('user_id', '=', auth()->user()->id)->first();
            $user->kyc_info = $kyc_info;
            $user->liked_products = $products;
         return returnResponse("User Profile data !", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $user);
            
        }
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token){
        $existUser = DB::table('users')
                    ->where('phone', auth()->user()->phone)->where('status', '1')->get();

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
       
      }
    }

    
}
