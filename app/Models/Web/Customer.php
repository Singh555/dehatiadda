<?php

namespace App\Models\Web;

use App\Http\Controllers\Web\AlertController;
use App\Models\Web\Index;
use App\Models\Web\Products;
use App\User;
use Auth;
use Hash;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Lang;
use Session;
use Socialite;
use Carbon\Carbon;
use App\Models\Core\OtpHistory;
use App\Models\Core\UserDirectIncome;
use App\Models\Core\SmsService;
use Illuminate\Support\Facades\Log;
use App\Helpers\HttpStatus;
class Customer extends Model
{

    public function addToCompare($request)
    {
        if (!empty(auth()->guard('customer')->user()->id)) {
            $check = DB::table('compare')->where('product_ids', $request->product_id)->where('customer_id', auth()->guard('customer')->user()->id)->first();
            if (!$check) {
                $id = DB::table('compare')
                    ->insertGetId([
                        'product_ids' => $request->product_id,
                        'customer_id' => auth()->guard('customer')->user()->id,
                    ]);
            }
            $count = DB::table('compare')->where('customer_id', auth()->guard('customer')->user()->id)->count();
            return $count;
        } else {
            $responseData = array('success' => '0', 'message' => Lang::get("website.Please Login First!"));
        }
        $cartResponse = json_encode($responseData);
        return $cartResponse;
    }

    public function DeleteCompare($id)
    {
        DB::table('compare')->where('product_ids', $id)->where('customer_id', auth()->guard('customer')->user()->id)->delete();
        $responseData = array('success' => '1', 'message' => Lang::get("website.Removed Successfully"));
        return $responseData;
    }

    public function updateMyProfile($request)
    {

        $customers_id = auth()->guard('customer')->user()->id;
        $customers_firstname = $request->customers_firstname;
        $customers_lastname = $request->customers_lastname;
        $customers_fax = $request->fax;
        $customers_newsletter = $request->newsletter;
        $customers_telephone = $request->customers_telephone;
        $customers_gender = $request->gender;
        $customers_dob = $request->customers_dob;
        $customers_info_date_account_last_modified = date('y-m-d h:i:s');

        $extensions = array('gif', 'jpg', 'jpeg', 'png');
        if ($request->hasFile('picture') and in_array($request->picture->extension(), $extensions)) {
            $image = $request->picture;
            $fileName = time() . '.' . $image->getClientOriginalName();
            $image->move('resources/assets/images/user_profile/', $fileName);
            $customers_picture = 'resources/assets/images/user_profile/' . $fileName;
        } else {
            $customers_picture = $request->customers_old_picture;
        }

        $customer_data = array(
            'first_name' => $customers_firstname,
            'last_name' => $customers_lastname,
            'phone' => $customers_telephone,
            'gender' => $customers_gender,
            'dob' => $customers_dob,
            'avatar' => $customers_picture,
            'updated_at' => date('Y-m-d H:i:s'),
        );

        //update into customer
        DB::table('users')->where('id', $customers_id)->update($customer_data);

        DB::table('customers_info')->where('customers_info_id', $customers_id)->update(['customers_info_date_account_last_modified' => $customers_info_date_account_last_modified]);
        $message = Lang::get("website.Profile has been updated successfully");

        return $message;

    }

    public function updateMyPassword($request)
    {

        $old_session = Session::getId();
        $customers_id = auth()->guard('customer')->user()->id;
        $new_password = $request->new_password;
        $current_password = $request->current_password;

        $updated_at = date('y-m-d h:i:s');
        $customers_info_date_account_last_modified = date('y-m-d h:i:s');

        $customer_data = array(
            'password' => bcrypt($new_password),
            'updated_at' => date('y-m-d h:i:s'),
        );

        $userData = DB::table('users')->where('id', $customers_id)->update($customer_data);
        $user = DB::table('users')->where('id', $customers_id)->get();

        DB::table('customers_info')->where('customers_info_id', $customers_id)->update(['customers_info_date_account_last_modified' => $customers_info_date_account_last_modified]);

        $message = Lang::get("website.Password has been updated successfully");
        return $message;

    }

    public function createRandomPassword()
    {
        $pass = substr(md5(uniqid(mt_rand(), true)), 0, 8);
        return $pass;
    }

    public function handleSocialLoginCallback($social)
    {
        $old_session = Session::getId();

        $user = Socialite::driver($social)->stateless()->user();
        $password = $this->createRandomPassword();
        // OAuth Two Providers
        $token = $user->token;
        if (!empty($user['gender'])) {
            if ($user['gender'] == 'male') {
                $customers_gender = '0';
            } else {
                $customers_gender = '1';
            }
        } else {
            $customers_gender = '0';
        }

        // All Providers
        $social_id = $user->getId();

        $customers_firstname = substr($user->getName(), 0, strpos($user->getName(), ' '));
        $customers_lastname = str_replace($customers_firstname . ' ', '', $user->getName());

        $email = $user->getEmail();
        if (empty($email)) {
            $email = '';
        }

        if ($social == 'facebook') {

            $existUser = DB::table('users')
                ->Where('users.email', '=', $email)->get();
    
            if (count($existUser) > 0) {
                
                $customers_id = $existUser[0]->id;

                //update data of customer
                DB::table('users')->where('id', '=', $customers_id)->update([
                    'first_name' => $customers_firstname,
                    'last_name' => $customers_lastname,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'status' => '1',
                    'created_at' => time(),
                ]);
                DB::table('customers')->where('user_id', '=', $customers_id)->update([
                    'fb_id' => $social_id,
                ]);
            } else {
                //insert data of customer
                $customers_id = DB::table('users')->insertGetId([
                    'role_id' => 2,
                    'first_name' => $customers_firstname,
                    'last_name' => $customers_lastname,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'status' => '1',
                    'created_at' => time(),
                ]);
                DB::table('customers')->insertGetId([
                    'user_id' => $customers_id,
                    'fb_id' => $social_id,
                ]);
                
            }
        }

        if ($social == 'google') {

            $existUser = DB::table('users')
                ->Where('users.email', '=', $email)->get();

            if (count($existUser) > 0) {

                $customers_id = $existUser[0]->id;

                //update data of customer
                DB::table('users')->where('id', '=', $customers_id)->update([
                    'first_name' => $customers_firstname,
                    'last_name' => $customers_lastname,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'status' => '1',
                    'created_at' => time(),
                ]);
                DB::table('customers')->where('user_id', '=', $customers_id)->update([
                    'google_id' => $social_id,
                ]);
            } else {
                //insert data of customer
                $customers_id = DB::table('users')->insertGetId([
                    'role_id' => 2,
                    'first_name' => $customers_firstname,
                    'last_name' => $customers_lastname,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'status' => '1',
                    'created_at' => time(),
                ]);
                DB::table('customers')->insertGetId([
                    'user_id' => $customers_id,
                    'google_id' => $social_id,
                ]);
            }
        }

        $userData = DB::table('users')->where('id', '=', $customers_id)->get();

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

        $customerInfo = array("email" => $email, "password" => $password);
        //dd($customerInfo);
        $old_session = Session::getId();

        if (auth()->guard('customer')->attempt($customerInfo)) {
            $customer = auth()->guard('customer')->user();

            //set session
            session(['customers_id' => $customer->id]);

            //cart
            $cart = DB::table('customers_basket')->where([
                ['session_id', '=', $old_session],
            ])->get();

            if (count($cart) > 0) {
                foreach ($cart as $cart_data) {
                    $exist = DB::table('customers_basket')->where([
                        ['customers_id', '=', $customer->id],
                        ['products_id', '=', $cart_data->products_id],
                        ['is_order', '=', '0'],
                    ])->delete();
                }
            }

            DB::table('customers_basket')->where('session_id', '=', $old_session)->update([
                'customers_id' => $customer->id,
            ]);

            DB::table('customers_basket_attributes')->where('session_id', '=', $old_session)->update([
                'customers_id' => $customer->id,
            ]);

            //insert device id
            if (!empty(session('device_id'))) {
                DB::table('devices')->where('device_id', session('device_id'))->update(['user_id' => $customer->id]);
            }

            $result['customers'] = DB::table('users')->where('id', $customer->id)->get();
            return $result;
        }
        $result = "";
        return $result;

    }

    public function likeMyProduct($request)
    {

        if (!empty(auth()->guard('customer')->user()->id)) {
            $liked_products_id = $request->products_id;

            $liked_customers_id = auth()->guard('customer')->user()->id;
            $date_liked = date('Y-m-d H:i:s');

            //to avoide duplicate record
            $record = DB::table('liked_products')->where([
                'liked_products_id' => $liked_products_id,
                'liked_customers_id' => $liked_customers_id,
            ])->get();

            if (count($record) > 0) {

                DB::table('liked_products')->where([
                    'liked_products_id' => $liked_products_id,
                    'liked_customers_id' => $liked_customers_id,
                ])->delete();

                $total_wishlist = 0;
                if (!empty(session('customers_id'))) {
                    $total_wishlist = DB::table('liked_products')
                        ->leftjoin('products', 'products.products_id', '=', 'liked_products.liked_products_id')
                        ->where('products_status', '1')
                        ->where('liked_customers_id', '=', session('customers_id'))->count();
                }

                DB::table('products')->where('products_id', '=', $liked_products_id)->decrement('products_liked');
                $products = DB::table('products')->where('products_id', '=', $liked_products_id)->get();

                $responseData = array('success' => '1', 'message' => Lang::get("website.Product is disliked"), 'total_likes' => $products[0]->products_liked, 'id' => 'like_count_' . $liked_products_id, 'total_wishlist' => $total_wishlist);
            } else {

                DB::table('liked_products')->insert([
                    'liked_products_id' => $liked_products_id,
                    'liked_customers_id' => $liked_customers_id,
                    'date_liked' => $date_liked,
                ]);
                DB::table('products')->where('products_id', '=', $liked_products_id)->increment('products_liked');

                $total_wishlist = 0;
                if (!empty(session('customers_id'))) {
                    $total_wishlist = DB::table('liked_products')
                        ->leftjoin('products', 'products.products_id', '=', 'liked_products.liked_products_id')
                        ->where('products_status', '1')
                        ->where('liked_customers_id', '=', session('customers_id'))->count();
                }
                $products = DB::table('products')->where('products_id', '=', $liked_products_id)->get();

                $responseData = array('success' => '2', 'message' => Lang::get("website.Product is liked"), 'total_likes' => $products[0]->products_liked, 'id' => 'like_count_' . $liked_products_id, 'total_wishlist' => $total_wishlist);

            }

        } else {
            $responseData = array('success' => '0', 'message' => Lang::get("website.Please login first to like this product"));
        }
        $cartResponse = json_encode($responseData);
        return $cartResponse;
    }

    public function unlikeMyProduct($id)
    {

        $liked_products_id = $id;

        $liked_customers_id = auth()->guard('customer')->user()->id;

        DB::table('liked_products')->where([
            'liked_products_id' => $liked_products_id,
            'liked_customers_id' => $liked_customers_id,
        ])->delete();

        DB::table('products')->where('products_id', '=', $liked_products_id)->decrement('products_liked');

    }

    public function wishlist($request)
    {
        $index = new Index();
        $productss = new Products();
        $result = array();
        $result['commonContent'] = $index->commonContent();

        if (!empty($request->limit)) {
            $limit = $request->limit;
        } else {
            $limit = 15;
        }

        $data = array('page_number' => 0, 'type' => 'wishlist', 'limit' => $limit, 'categories_id' => '', 'search' => '', 'min_price' => '', 'max_price' => '');
        $products = $productss->products($data);
        $result['products'] = $products;
        $cart = '';
        $result['cartArray'] = $productss->cartIdArray($cart);

        //liked products
        $result['liked_products'] = $productss->likedProducts();
        if ($limit > $result['products']['total_record']) {
            $result['limit'] = $result['products']['total_record'];
        } else {
            $result['limit'] = $limit;
        }

        //echo '<pre>'.print_r($result['products'], true).'</pre>';
        return $result;
    }

    public function processLogin($request, $old_session)
    {
        $result = array();
        $customer = auth()->guard('customer')->user();
        session(['guest_checkout' => 0]);

        //set session
        session(['customers_id' => $customer->id]);

        //cart
        $cart = DB::table('customers_basket')->where([
            ['session_id', '=', $old_session],
        ])->get();

        if (count($cart) > 0) {
            foreach ($cart as $cart_data) {
                $exist = DB::table('customers_basket')->where([
                    ['customers_id', '=', $customer->id],
                    ['products_id', '=', $cart_data->products_id],
                    ['is_order', '=', '0'],
                ])->delete();
            }
        }

        DB::table('customers_basket')->where('session_id', '=', $old_session)->update([
            'customers_id' => $customer->id,
        ]);

        DB::table('customers_basket_attributes')->where('session_id', '=', $old_session)->update([
            'customers_id' => $customer->id,
        ]);

        //insert device id
        if (!empty(session('device_id'))) {
            DB::table('devices')->where('device_id', session('device_id'))->update(['user_id' => $customer->id]);
        }

        $result['customers'] = DB::table('users')->where('id', $customer->id)->get();
        return $result;

    }

    public function Compare()
    {
        $compare = DB::table('compare')->where('customer_id', auth()->guard('customer')->user()->id)->get();
        return $compare;
    }

    public function ExistUser($email)
    {
        $existUser = DB::table('users')->where('role_id', 2)->where('email', $email)->get();
        return $existUser;
    }

    public function UpdateExistUser($email, $password)
    {
        DB::table('users')->where('email', $email)->update([
            'password' => Hash::make($password),
        ]);
    }

    public function updateDevice($request, $device_data)
    {

        //check device exist
        $device_id = DB::table('devices')->where('device_id', '=', $request->device_id)->get();

        if (count($device_id) > 0) {

            $dataexist = DB::table('devices')->where('device_id', '=', $request->device_id)->where('user_id', '==', '0')->get();

            DB::table('devices')
                ->where('device_id', $request->device_id)
                ->update($device_data);

            if (auth()->guard('customer')->check()) {
                $userData = DB::table('users')->where('id', '=', auth()->guard('customers')->user()->id)->get();
                //notification
                $myVar = new AlertController();
                $alertSetting = $myVar->createUserAlert($userData);
            }
        } else {
             DB::table('devices')->insertGetId($device_data);
        }

        return 'success';

    }

    public function signupProcess($request)
    {
        $res = array();
        $old_session = Session::getId();
        $firstName = $request->firstName;
        $lastName = $request->lastName;
        $gender = $request->gender;
        $email = $request->email;
        $password = $request->password;
        $customers_dob = $request->customers_dob;
        //$token = $request->token;
        $date = date('y-m-d h:i:s');
        $profile_photo = 'images/user.png';
        $phone = $request->phone;

        //echo "Value is completed";
        $data = array(
            'first_name' => $request->firstName,
            'last_name' => $request->lastName,
            'gender' => $request->gender,
            'role_id' => 2,
            'email' => $request->email,
            'password' => Hash::make($password),
            'dob' => $customers_dob,
            'created_at' => $date,
            'updated_at' => $date,
        );

        //eheck email already exit
        $user_email = DB::table('users')->select('email')->where('role_id', 2)->where('email', $email)->get();
        if (count($user_email) > 0) {
            $res['email'] = "true";
            return $res;
        } else {
            $res['email'] = "false";
            if (DB::table('users')->insert([
                'first_name' => $request->firstName,
                'last_name' => $request->lastName,
                'gender' => $request->gender,
                'role_id' => 2,
                'email' => $request->email,
                'dob' => $customers_dob,
                'phone'=>$phone,
                'password' => Hash::make($password),
                'created_at' => $date,
                'updated_at' => $date,
            ])
            ) {
                $res['insert'] = "true";

                //check authentication of email and password

                if (auth()->guard('customer')->attempt(['email' => $request->email, 'password' => $request->password])) {
                    $res['auth'] = "true";
                    $customer = auth()->guard('customer')->user();
                    //set session
                    session(['customers_id' => $customer->id]);

                    //cart
                    $cart = DB::table('customers_basket')->where([
                        ['session_id', '=', $old_session],
                    ])->get();

                    if (count($cart) > 0) {
                        foreach ($cart as $cart_data) {
                            $exist = DB::table('customers_basket')->where([
                                ['customers_id', '=', $customer->id],
                                ['products_id', '=', $cart_data->products_id],
                                ['is_order', '=', '0'],
                            ])->delete();
                        }
                    }

                    DB::table('customers_basket')->where('session_id', '=', $old_session)->update([
                        'customers_id' => $customer->id,
                    ]);

                    DB::table('customers_basket_attributes')->where('session_id', '=', $old_session)->update([
                        'customers_id' => $customer->id,
                    ]);

                    //insert device id
                    if (!empty(session('device_id'))) {
                        DB::table('devices')->where('device_id', session('device_id'))->update(['customers_id' => $customer->id]);
                    }

                    $customers = DB::table('users')->where('id', $customer->id)->get();
                    $result['customers'] = $customers;
                    //email and notification
                    $myVar = new AlertController();
                    $alertSetting = $myVar->createUserAlert($customers);
                    $res['result'] = $result;
                    return $res;
                } else {
                    $res['auth'] = "true";
                    return $res;
                }

            } else {
                $res['insert'] = "false";
                return $res;
            }
        }

    }
    
    
    
    // send otp for Normal Signup
    public static function normalSignup($request)
    {
        $request->validate([
            'mobile_no' => 'required|unique:users,phone|string|between:10,12',
            'name' => 'required|string|max:200',
            'password' => 'required|confirmed',
            'email' => 'nullable|email',
            'referral_code' => 'nullable|string',
        ]);
        $currentDateTime = Carbon::now();
        $otpExpiry = Carbon::now()->addMinute(15);
        
        $mobile_no = $request->mobile_no;
        Log::debug(__CLASS__."::".__FUNCTION__."Called with mobile no $mobile_no");

      

          $otp_for = 'normal_signup';

            //check email existance
            $existUser = DB::table('users')->where('phone', $mobile_no)->where('role_id', '2')->where('status', '1')->get();
            $otp = generateOtp($mobile_no);
            OtpHistory::where('mobile_no',$mobile_no)->where('status','!=','USED')->update(['status'=>'EXPIRED']);
            if (isset($existUser[0]->first_name) && count($existUser) > 0) {
                
                session()->put('error',"User Already Exists !");
                    return false;  
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
                session()->put('success',"Otp Sent successfully !");
                    return true; 
                
                }
                
                }
                
                session()->put('error',"Otp Seding Failed! !");
                    return false; 
            }

    }
    
    public static function normalSignupVerify($request)
    {
       
       $request->validate([
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
        
            $mobile = $request->mobile_no;
            $email = $request->email;
            $otp = $request->otp;
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
                    session()->put('error',"Invalid Referal Code");
                    return false;   
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
            
            $user = User::where('id', $customers_id)->where('status', '1')->where('role_id', '2')->first();
                if (isset($user->id) && Auth::guard('customer')->loginUsingId($user->id)) {

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
                            'customers' => $user,
                        );

                        DB::commit();
                    session()->put('success',"Welcome !");
                    return $data; 

                    } else {
                         session()->put('error',"Your account has been deactivated. !");
                    return false; 
                    }
                } else {
                    Log::error(__CLASS__."::".__FUNCTION__." Login attempt failed !");
                     session()->put('error',"Login attempt failed . !");
                    return false; 

                }
            
            
        
                }
              catch (Exception $exc) {
                  Log::error(__CLASS__."::".__FUNCTION__." Exception : ".$exc->getMessage());
                   session()->put('error',"Exception Occured Otp ! ".$exc->getMessage());
                    return false;
              }
                
            }else{
                 session()->put('error',"Invalid Otp !");
                    return false;
            }
        
       
    }
    
    
     // Validate Referral Code
    public static function validateReferralCode($request)
    {
        Log::debug(__CLASS__."".__FUNCTION__."called");
        
        $request->validate([
            'referral_code' => 'required',
            'type' => 'required',
        ]);

        
        $member_code = $request->referral_code;
        $type = $request->type;
        Log::debug(__CLASS__."".__FUNCTION__."type recieved as $type");
        Log::debug(__CLASS__."".__FUNCTION__."referral code recieved as $member_code");


            $data = array();
            $existUser = DB::table('users')->where('member_code', $member_code)->where('status', '1')->first();
               
            if (isset($existUser->id)) {
                $name['name'] = $existUser->first_name.' '.$existUser->last_name;
                $name['is_prime'] = $existUser->is_prime;
                //response if email already exit
                //$responseData = array('success' => '0', 'data' => $postData, 'message' => "Email address is already exist");
                if($type == 'PRIME' && $existUser->is_prime == 'N'){
                 $data['code'] = 404;
                $data['status'] = 'error';
                $data['message'] = 'Referral code is invalid !';
                }
                $data['code'] = 200;
                $data['status'] = 'success';
                $data['message'] = 'Referral code is valid !';
                $data['data'] = $name;
                
            } else {
                  $data = DB::table('users')->where('is_prime', 'Y')->get();
                if($member_code == 'COMPANY' && count($data) == 0){
                    $name['name'] = config('app.app_name');
                    $name['is_prime'] = 'Y';
                    $data['code'] = 200;
                $data['status'] = 'success';
                $data['message'] = 'Referral code is valid !';
                $data['data'] = $name;
                }else{
                    $data['code'] = 404;
                $data['status'] = 'error';
                $data['message'] = 'Referral code is invalid !';
                }
                //$responseData = array('success' => '1', 'data' => $userData, 'message' => "Sign Up successfully!");
            }

        return response()->json($data);
    }
    
    //Send Otp for Prime Signup
     public static function primeSignup($request)
    {
        $request->validate([
            'mobile_no' => 'required|string|between:10,12',
            'name' => 'required',
            'referral_code' => 'required',
            'email' => 'required|email',
            'dob' => 'required',
            'city' => 'required',
            'pin_code' => 'required',
            'password' => 'required|confirmed',
        ]);

        $currentDateTime = Carbon::now();
        $otpExpiry = Carbon::now()->addMinute(15);
        
        $mobile_no = $request->mobile_no;
    Log::debug(__CLASS__."::".__FUNCTION__." Called with mobile $mobile_no");
          $otp_for = 'prime_signup';

            //check mobile number existance
            $existUser = DB::table('users')->where('phone', $mobile_no)->where('role_id', '2')->get();
            if (isset($existUser->first_name)) {
                
                if(isset($existUser->is_prime) && $existUser->is_prime == "Y"){
                     session()->put('error',"User Already Exists !");
                    return false;  
                }
            }
            $otp = generateOtp($mobile_no);
             OtpHistory::where('mobile_no',$mobile_no)->where('status','!=','USED')->update(['status'=>'EXPIRED']);
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
                   session()->put('success',"Otp Sent successfully !");
                    return true; 
                                
                }
            
            }
            
            session()->put('error',"Otp Seding Failed! !");
                    return false; 
    }
 //prime signup verify
    
    public static function primeSignupVerify($request)
    {
        $request->validate([
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
        
        

            $mobile = $request->mobile_no;
            $name = $request->name;
            $otp = $request->otp;
            $email = $request->email;
            $referral_id = $request->referral_code;
            $dob = Carbon::createFromFormat('d/m/Y', $request->dob)->format('Y-m-d');
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
                   session()->put('error',"Already Prime Member Login to continue");
                    return false;
               }
                if(isset($existUser[0]->id) && $existUser[0]->is_prime =='N'){
                    $user = User::where('phone', $mobile)->where('status', '1')->where('role_id', '2')->first();
                    if (isset($user->id) && Auth::guard('customer')->loginUsingId($user->id)) {

                        if (count($existUser) > 0) {

                            $customers_id = $user->id;
                            $parent_id = self::getParentCodeByReferralCode($referral_id,5,1);
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
                                    'phone_verified' => '1',
                                    'phone' => $mobile,
                                    'first_name' => $name,
                                    'dob' => $dob,
                                    'email' => $email,
                                    'parent_id' => $parent_id,
                                    'password' => $password,
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
                                 session()->put('error',"Distributing Level Income failed !");
                                  return false;
                            }

                           
                            $user_data = User::where('id', $user->id)->where('status', '1')->where('role_id', '2')->first();

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
                                'customers' => $user_data,

                            );
                        DB::commit();
                        //Log::debug($data);
                         session()->put('success',"Welcome $user_data->first_name");
                        return  $data;

                        } else {
                            //$responseData = array('success' => '0', 'data' => array(), 'message' => "Your account has been deactivated.");
                             session()->put('error',"Your account has been deactivated !");
                                  return false;
                        }
                    } else {
                        Log::error(__CLASS__."::".__FUNCTION__." Login attempt failed !");
                       session()->put('error',"Login attempt failed !");
                                  return false;
                    }
                }else{
                    
                    Log::debug('Getting Parent ID');
                 $parent_id = self::getParentCodeByReferralCode($referral_id,5,1);
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
                    
                    $user = User::where('id', $customer_id)->where('status', '1')->where('role_id', '2')->first();
                    if (isset($user->id) && Auth::guard('customer')->loginUsingId($user->id)) {


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




                           
                            $user_data = User::where('id', $user->id)->where('status', '1')->where('role_id', '2')->first();

                            Log::debug('CAlling Method for Distributing Level Income');
                            if(!self::levelIncomeDistribute(1, $referral_id, $parent_id,$user_data->id)){
                                 Log::debug('Distributing Level Income failed');
                                 session()->put('error',"Distributing Level Income failed!");
                                  return false;
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
                                'customers' => $user_data,

                            );
                        DB::commit();
                        //Log::debug($data);
                         session()->put('success',"Welcome $user_data->first_name");
                        return  $data;

                         
                    } else {
                        Log::error(__CLASS__."::".__FUNCTION__." Login attempt failed !");
                      session()->put('error',"Login attempt failed !");
                                  return false;
                    }
                    
                }
             }
            session()->put('error',"Invalid Otp");
                    return false;
        
        }
        catch (Exception $exc) {
            Log::error(__CLASS__."::".__FUNCTION__." Exception : ".$exc->getMessage());
            session()->put('error',"Exception Occured Otp ! ".$exc->getMessage());
                    return false;
        }
        session()->put('error',"Some Error Occured");
                    return false;
       
    }    
    
    
    
//Send Otp for customer Login
    
    public static function usualLoginSendOtp($request)
    {
        $request->validate([
            'mobile_no' => 'required|string|between:10,12',
            
        ]);

        $currentDateTime = Carbon::now();
        $otpExpiry = Carbon::now()->addMinute(15);
        
        $mobile = $request->mobile_no;

        Log::debug(__CLASS__."::".__FUNCTION__."called with Mobile no. $mobile");
          $otp_for = 'login';

            //check email existance
            $existUser = DB::table('users')->where('phone', $mobile)->where('role_id', '2')->where('status', '1')->first();
             OtpHistory::where('mobile_no',$mobile)->where('status','!=','USED')->update(['status'=>'EXPIRED']);
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
                //if(SmsService::scheduleNewSMS($mobile, $message_text, 'otp', '1')){
                if(true){
                Log::debug("Otp Sent $otp");
               // session()->put('success', "Otp Sent successfully!");
                return true;
                }
                }
                session()->put('error', "Otp Seding Failed !");
                return false;
                
                }
                session()->put('error', "Your Account is deactive !");
                return false;
            } else {
                
                session()->put('error', "User Dosen't Exists !");
                return false;
            }

    }
    
    
    //customer Login with Otp
    
    public static function verifyLogin($request)
    {
       $request->validate([
            'mobile_no' => 'required|string|between:10,12',
            'otp' => 'required|numeric|min:4',
        ]);
        $mobile = $request->mobile_no;
            $otp = $request->otp;
            Log::debug(__CLASS__."::".__FUNCTION__."Called with mobile $mobile and otp $otp");
        
        

            
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
                $user = User::where('phone', $mobile)->where('status', '1')->where('role_id', '2')->first();
                if (isset($user->id) && Auth::guard('customer')->loginUsingId($user->id)) {

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
                         $now = Carbon::now();
                        $data = array(
                            'customers' => $user,
                            //'studentList' => StudentModel::getStudentList(auth()->user()->id),
                        );

                    //Log::debug($data);
                     session()->put('success',"Welcome $user->first_name");
                    return $data;

                    } else {
                        //$responseData = array('success' => '0', 'data' => array(), 'message' => "Your account has been deactivated.");
                        session()->put('error', "Your account has been deactivated");
                        return false;
                    }
                } else {
                    Log::error(__CLASS__."::".__FUNCTION__." Login attempt failed !");
                  session()->put('error', "Login attempt failed !");
                        return false;
                }
            }else{
                session()->put('error', " Invalid Otp");
            return false;
            }
            
            
        
        }
        catch (Exception $exc) {
            Log::error(__CLASS__."::".__FUNCTION__." Exception : ".$exc->getMessage());
            session()->put('error', " Exception Occured : ".$exc->getMessage());
            return false;
        }
            return false;
        
       
    }
    
    
    protected static function generateReferralToken() {
        $code = "";
        do {
            //$code = substr(uniqid(mt_rand(), true) , 0, 6);
            $code = 'KING'.substr(sha1(time()), 0, 8);
            $data = DB::table('users')->where('member_code', $code)->get();
        } while ($data->count() > 0);
        return $code;
    }
    
    
    protected static function getParentCodeByReferralCode($referral_code,$matrix_of,$count) {
        Log::debug(__CLASS__."::".__FUNCTION__."Called");
        if(is_array($referral_code)){
            Log::debug(__CLASS__."::".__FUNCTION__."Called with count $count and referral code ::");
            Log::debug($referral_code);
        } else {
            Log::debug(__CLASS__."::".__FUNCTION__."Called with refereal code $referral_code and count $count");
        }
        if($referral_code == 'COMPANY'){
            if(is_array($referral_code)){
                Log::debug('returnig parent id ');
                Log::debug($referral_code);
            } else {
                Log::debug('returnig parent id '.$referral_code);
            }
            return $referral_code;
        }
        try{
            if($count == 1){
              //$data = DB::table('users')->where('prime_referral',$referral_code)->where('role_id',2)->orderBY('prime_time')->get();  
            	$data = DB::table('users')->where('parent_id',$referral_code)->where('role_id',2)->orderBY('prime_time')->get();  
            }else{
               //$data = DB::table('users')->whereIn('prime_referral',$referral_code)->where('role_id',2)->orderBY('prime_time')->get(); 
            	$data = DB::table('users')->whereIn('parent_id',$referral_code)->where('role_id',2)->orderBY('prime_time')->get(); 
            }
           if(!isset($data)){
               Log::error(__CLASS__."::".__FUNCTION__." Data not found");
            return returnResponse("Error While Processing !", HttpStatus::HTTP_BAD_REQUEST);
           } 
        
        Log::debug('Child Count'.$data->count());
        $total_child_req = pow($matrix_of,$count);
        Log::debug('total child required '.$total_child_req.' for count '.$count);
        
        if($data->count() > $total_child_req ){
            
            Log::error(__CLASS__."::".__FUNCTION__." child count is ".count($data)." and required only $total_child_req");
            return returnResponse("Error While Processing !", HttpStatus::HTTP_BAD_REQUEST);
            
            
        }else if(count($data) < $total_child_req){
            if(is_array($referral_code)){
                $data_explode = $referral_code;
            } else {
                $data_explode = explode("','", $referral_code);
            }
            
            Log::debug(__CLASS__." :: ".__FUNCTION__." lets get the parent id from array count ". count($data_explode));
            for($i=0; $i < count($data_explode); $i++)
            {
               //$child_data2 = DB::table('users')->where('prime_referral',$data_explode[$i])->where('role_id',2)->get();
            	$child_data2 = DB::table('users')->where('parent_id',$data_explode[$i])->where('role_id',2)->get();
               // $child_data2 = DatabaseFactory::executeQueryAndGetData("select * from members where parent_id = '{$data_explode[$i]}'", $con);
                Log::debug(__CLASS__." :: ".__FUNCTION__." child count found as ". count($child_data2)." for member id $data_explode[$i]");
                if(count($child_data2) < $matrix_of)
                {
                    Log::debug(__CLASS__." :: ".__FUNCTION__." returning member id for parent id as $data_explode[$i]");
                    return $data_explode[$i];
                }
            }

        }
        else{
            $count++;
            //            $member_id_new = "";
            //                $comma = "";
            //            foreach ($data as $value) {
            //                    $member_id_new .= $comma . $value->member_code;
            //                    $comma = "','";
            //            }
                        
                        $member_id_new = array();
                        foreach ($data as $value){
                            array_push($member_id_new, $value->member_code);
                        }

            Log::debug(__CLASS__." :: ".__FUNCTION__." lets call the method again with member id updated as ");
            Log::debug($member_id_new);
                    return self::getParentCodeByReferralCode($member_id_new,$matrix_of, $count);

        }
        } catch (Exception $e){
            
        }
    }
    
    // distribute level income
    protected static function levelIncomeDistribute($level,$referral_code,$parent_code,$child_id) {
        Log::debug(__CLASS__."::".__FUNCTION__."Called with refereal code $referral_code and parent code $parent_code");
        if($parent_code == 'COMPANY'){
            Log::debug('returnig parent id '.$parent_code);
            return true;
        }
         try{
             
            Log::debug('calculating income for level '.$level);
            $income = self::calculateLevelIncome($level);
            Log::debug('Level income calculated as '.$income.' for level '.$level);
            if($income == 'fail'){
                Log::error(__CLASS__." :: ".__FUNCTION__." level income calcaulation failed for level ".$level);
                return false;
            }
            Log::debug(__CLASS__." :: ".__FUNCTION__." validating if level income is greather than 0");
            if($income > 0){
                Log::debug(__CLASS__." :: ".__FUNCTION__." Making an entry for level income");
                DB::table('user_level_incomes')->insert([
                    'payment_date' => date('Y-m-d H:i:s'),
                    'created_at' => date('Y-m-d H:i:s'),
                    'member_code' => $parent_code,
                    'amount' => $income,
                    'referral_code' => $referral_code,
                    'child_id' => $child_id,
                    'level' => $level,
                ]);
            }
            Log::debug(__CLASS__." :: ".__FUNCTION__." increasing level from ".$level);
            $level++;
            Log::debug('level updated to '.$level);
            if($level > 10){
                Log::debug('returning true as level reached at '.$level);
                return true;
            }
            Log::debug(__CLASS__." :: ".__FUNCTION__." fetching parent code for next level income distribution");
            $parent_code_new = self::getParentCode($parent_code);
            Log::debug("Got Parent Id ".$parent_code_new);
            if(empty($parent_code_new)){
                Log::error(__CLASS__." :: ".__FUNCTION__." parent code is empty i.e. ".$parent_code_new);
                return false;
            }else{
                return self::levelIncomeDistribute($level,$referral_code,$parent_code_new,$child_id);
            }
            
         } catch (Exception $e){
             Log::error('Error while distributing level income');
         }
        return false;
    }
    
    public static function getParentCode($child_code) {
        return DB::table('users')->where('member_code',$child_code)->first()->parent_id;
    }
    
    protected static function calculateLevelIncome($level) {
        
        $income = DB::table('level_income')->where('level',$level)->first();
        if(isset($income->income)){
            Log::debug("returning income $income->income for level $level");
        return $income->income;
        }
        Log::error('Income getting Failed');
        return 'fail';
    }
    
    // distribute Direct Income
    
    protected static function distributeDirectIncome($child_id,$parent_code) {
        $referralData = DB::table('users')->where('normal_referral',$parent_code)->where('role_id',2)->get();
        if($referralData->count() < 5 && isset($referralData[0]->id)){
            
           $user = User::where('member_code',$parent_code)->first();
           $oldBalance = $user->m_wallet;
           $newBalance = $oldBalance+20;
           $user->m_wallet = $newBalance;
           if($user->save()){
               $directIncome = new UserDirectIncome;
               $directIncome->payment_date = Carbon::now();
               $directIncome->member_code = $parent_code;
               $directIncome->amount = 20;
               $directIncome->child_id = $child_id;
               if(!$directIncome->save()){
                   return false;
               }
           }
           
           return true;
        }
        return false;
    }

}
