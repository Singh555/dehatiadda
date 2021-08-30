<?php
namespace App\Http\Controllers\App;

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
use App\Models\AppModels\Customer;
use App\Models\AppModels\CustomerLogin;
use App\Models\Core\User;
use App\Helpers\HttpStatus;
use App\Models\AppModels\Cart;
use App\Models\Core\CustomerModel;
class CustomersController extends Controller
{
     //Check User
    public function googleAuth(Request $request){
      return Customer::googleAuth($request);
    }
     //Check User
    public function googleAuthRegister(Request $request){
      return Customer::googleAuthRegister($request);
    }
    

	//login
	public function processlogin(Request $request){
          return Customer::processlogin($request);
	}

	//registration
	public function processregistration(Request $request){
         return Customer::processregistration($request);
		
	}
        
        // Otp Sending and registration if not already
        public function normalLogin(Request $request){
         return Customer::usualLoginSendOtp($request);
		
	}
        //verify otp and send login token
        public function verifyLogin(Request $request){
         return Customer::verifyLogin($request);
		
	}
        
        // prime signup
        public function primeSignup(Request $request){
         return Customer::primeSignup($request);
		
	}
        // prime signup verify
        public function primeSignupVerify(Request $request){
         return Customer::primeSignupVerify($request);
		
	}
        // Normal signup
        public function normalSignup(Request $request){
         return Customer::normalSignup($request);
		
	}
        // Normal signup
        public function normalSignupVerify(Request $request){
         return Customer::normalSignupVerify($request);
		
	}
        
    //Validate Refferal code
    public function validateReferralCode(Request $request){
         return Customer::validateReferralCode($request);
		
	}
    //Update Refferal url
    public function updateReferralUrl(Request $request){
         return Customer::updateReferralUrl($request);
		
	}
    
   //Update Refferal url
   public function updateFcmToken(Request $request){
        return Customer::updateFcmToken($request);
   }

        
	//notify_me
	public function notify_me(Request $request){
         return Customer::notify_me($request);
		
	}

	//update profile
	public function updatecustomerinfo(Request $request){
          return Customer::updatecustomerinfo($request);
		

	}

	//processforgotPassword
	public function processforgotpassword(Request $request){
           return  Customer::processforgotpassword($request);
		
	}

	//facebookregistration
	public function facebookregistration(Request $request){
	  return Customer::facebookregistration($request);
		


	}


	//googleregistration
	public function googleregistration(Request $request){
          return Customer::googleregistration($request);
		


		}
                
                 // Otp Sending for mobile no verification
    public function sendOtpForMobile(Request $request){
         return Customer::sendOtpForMobile($request);
		
	}
    //verify otp for mobile no verification
    public function verifyOtpForMobile(Request $request){
         return Customer::verifyOtpForMobile($request);
		
	}

	//generate random password
	function createRandomPassword() {
		$pass = substr(uniqid(mt_rand(), true) , 0, 6);
		return Hash::make($pass);
	}

	//generate random password
	function registerdevices(Request $request) {
    	return Customer::registerdevices($request);
		
	}

	function updatepassword(Request $request) {
		return Customer::updatepassword($request);
		
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
            return returnResponse('User successfully signed out', HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS);
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
        
             $token = JWTAuth::getToken();
             Log::debug(__CLASS__."::".__FUNCTION__."user token got as $token");
             $apy = JWTAuth::getPayload($token)->toArray();
             Log::debug(__CLASS__."::".__FUNCTION__."user token data got as ".json_encode($apy));
             $expiry_date_string = $apy['exp'];
             $datetime = new \DateTime("@$expiry_date_string");
             $datetime = $datetime->format('Y-m-d');
             $datetime_ckeck = new Carbon('2021-08-05');
             Log::debug(__CLASS__."::".__FUNCTION__."user token expi date as $datetime");
             Log::debug(__CLASS__."::".__FUNCTION__."user token expi check date as ".$datetime_ckeck->format('Y-m-d'));
             if($datetime < $datetime_ckeck->format('Y-m-d')){
                Log::debug(__CLASS__."::".__FUNCTION__."user token invalidating");
                  return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED, '');
                  /*
             if(auth()->invalidate(true)){
                   Log::debug(__CLASS__."::".__FUNCTION__."user token invalidating");
                  return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED, '');
             }
             */
             }
            $user = CustomerModel::where('id', auth()->user()->id)->where('status','1')->first();
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
            $kyc_info = DB::table('customers_kyc')->where('customers_id', '=', auth()->user()->id)->first();
            $user->kyc_info = $kyc_info;
            $user->first_name = $user->name;
            //$today_task_count = TaskActivity::where('customer_id', $user->customers_id)->where('is_rewarded', 'Y')->whereDate('created_at', date('Y-m-d'))->count();
            $user->liked_products = $products;
            ////// Update Here
            $quiz_dashboard_message = null;
            $can_play_quiz = "Y"; // Y, N
            $notice = 'Our update is under review at play store, quiz will be available after update!'; // For notice to perticular user
            Log::debug(__CLASS__." ".__FUNCTION__." checking for app version received in request");
            $app_version = $request->device_app_version;
            Log::debug(__CLASS__." :: ".__FUNCTION__." device app version found as ".$app_version);
            $c_version = '1.0.7';
            Log::debug(__CLASS__." :: ".__FUNCTION__." lets compare the current app version with device app version!!");
            if(version_compare($app_version, $c_version, '>=')){
              Log::info(__CLASS__." :: ".__FUNCTION__." version matched with latest one, lest dont send the notice to user !!");
              $notice=null;  
            }
            Log::debug(__CLASS__." :: ".__FUNCTION__." notice updated as $notice");
            $user->notice =$notice;
            Log::debug(__CLASS__." :: ".__FUNCTION__." notice updated in response");
            //// Update End
            // new update by ajit
            $can_play_quiz_without_ad = 'Y'; // Y or N
            $quiz_ad_type = 'INTERSTITIAL'; // INTERSTITIAL or REWARDED or NO 
            // we need to add the coading for quiz type based on task count..
            
            /*
            if($can_play_quiz_without_ad == 'N'){
              if($today_task_count == 3 || $today_task_count == 6 || $today_task_count == 9 ){
                $quiz_ad_type = 'REWARDED';
              }
              else{
                $quiz_ad_type = 'INTERSTITIAL';
              }
              //$quiz_ad_type = 'REWARDED'; // INTERSTITIAL or REWARDED
            }
            */
          

            
            $cn_version = '1.1.2';
            $user->mobile_no_force_update = false;
            if(version_compare($app_version, $c_version, '>=')){
              $user->mobile_no_force_update = true;
            }
            
            $user->is_imps_allowed = true; // later we will use a method to fetch the status on various dependency
            if($user->is_active=='YES'){
                $user->min_imps_amount = 10; // later we will use a method to fetch the config based on transaction done by customer
                $user->max_imps_amount = 10000; // later we will use a method to fetch the config based on transaction done by customer
            }
            else{
                $user->min_imps_amount = 500; // later we will use a method to fetch the config based on transaction done by customer
                $user->max_imps_amount = 1000; // later we will use a method to fetch the config based on transaction done by customer
            }
            
            
            // New update by ajit
            // New Update By Anurag
            $myCart = Cart::myCart();
            $user->cart = $myCart;
            Log::debug("user data udated as $user");
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
        $existUser = CustomerModel
                    ::where('id', auth()->user()->id)->where('status', '1')->get();

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

                      CustomerModel::where('id', $customers_id)->update([
                            'default_address_id' => $customers_default_address_id,
                        ]);
                    }

                    //check if already login or not
                    $already_login = DB::table('whos_online')->where('customer_id', '=', $customers_id)->get();

                    if (count($already_login) > 0) {
                        DB::table('whos_online')
                            ->where('customer_id', $customers_id)
                            ->update([
                                'full_name' => $existUser[0]->name,
                                'time_entry' => date('Y-m-d H:i:s'),
                            ]);
                    } else {
                        DB::table('whos_online')
                            ->insert([
                                'full_name' => $existUser[0]->name,
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
                        //'studentList' => StudentModel::getStudentList(auth()->user()->customers_id),
                    );
       
                //Log::debug($data);
                return returnResponse("Login Success !", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $data);
       
      }
    }

    
}
