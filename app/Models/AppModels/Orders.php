<?php

namespace App\Models\AppModels;

use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Admin\AdminSiteSettingController;
use App\Http\Controllers\Admin\AdminCategoriesController;
use App\Http\Controllers\Admin\AdminProductsController;
use App\Http\Controllers\App\AppSettingController;
use App\Http\Controllers\App\AlertController;
use App\Models\AppModels\Product;
use DB;
use Lang;
use Log;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Validator;
use Mail;
use DateTime;
use Auth;
use Carbon\Carbon;
use Hash;
use App\Helpers\HttpStatus;
use App\Models\AppModels\PaymentGatewayModel;
use App\Models\Eloquent\CartModel;
use App\Models\Core\WalletModel;
use App\Models\Eloquent\PaymentMethod;

class Orders extends Model {

    public static function convertprice($current_price, $requested_currency) {
        $required_currency = DB::table('currencies')->where('is_current', 1)->where('code', 'INR')->first();
        $products_price = $current_price * $required_currency->value;

        return $products_price;
    }

    public static function converttodefaultprice($current_price, $requested_currency) {
        $required_currency = DB::table('currencies')->where('is_current', 1)->where('code', 'INR')->first();
        $products_price = $current_price * $required_currency->value;
        return $products_price;
    }

    public static function currencies($currency_code) {
        $currencies = DB::table('currencies')->where('is_current', 1)->where('code', $currency_code)->first();
        return $currency_code;
    }

    public static function hyperpaytoken($request) {
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
            $payments_setting = Orders::payments_setting_for_hyperpay($request);

            //check envinment
            if ($payments_setting[0]->hyperpay_enviroment == '0') {
                $env_url = "https://test.oppwa.com/v1/checkouts";
            } else {
                $env_url = "https://oppwa.com/v1/checkouts";
            }

            //use currency account currency only e:g. 'SAR'
            $url = $env_url;
            $data = "authentication.userId=" . $payments_setting['userid']->value .
                    "&authentication.password=" . $payments_setting['password']->value .
                    "&authentication.entityId=" . $payments_setting['entityid']->value .
                    "&amount=" . $request->amount .
                    "&currency=SAR" .
                    "&paymentType=DB" .
                    "&customer.email=" . $request->email .
                    "&testMode=INTERNAL" .
                    "&merchantTransactionId=" . uniqid();

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // this should be set to true in production
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $responseData = curl_exec($ch);
            if (curl_errno($ch)) {
                return curl_error($ch);
            }
            curl_close($ch);

            $data = json_decode($responseData);

            if ($data->result->code == '000.200.100') {
                $responseData = array('success' => '1', 'token' => $data->id, 'message' => "Token generated.");
            } else {
                $responseData = array('success' => '2', 'token' => array(), 'message' => $data->result->description);
            }
        } else {
            $responseData = array('success' => '0', 'data' => array(), 'message' => "Unauthenticated call.");
        }
        $orderResponse = json_encode($responseData);

        return $orderResponse;
    }

    public static function generatebraintreetoken($request) {
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
            $payments_setting = Orders::payments_setting_for_brain_tree($request);

            //braintree transaction get nonce
            $is_transaction = '0';    # For payment through braintree

            if ($payments_setting['merchant_id']->environment == '0') {
                $braintree_environment = 'sandbox';
            } else {
                $environment = 'production';
            }

            $braintree_merchant_id = $payments_setting['merchant_id']->value;
            $braintree_public_key = $payments_setting['public_key']->value;
            $braintree_private_key = $payments_setting['private_key']->value;

            //for token please check braintree.php file
            require_once app_path('braintree/Braintree.php');

            $responseData = array('success' => '1', 'token' => $clientToken, 'message' => "Token generated.");
        } else {
            $responseData = array('success' => '0', 'data' => array(), 'message' => "Unauthenticated call.");
        }
        $orderResponse = json_encode($responseData);

        return $orderResponse;
    }

    public static function getpaymentmethods($request) {
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

            $result = array();
            $for = "";
            if($request->has('for')){
                $for = htmlspecialchars(strip_tags($request->for));
            }
            $paymentMethods = PaymentMethod::where('status', '=', '1')
            //->where('environment', '=', '1')
              ->where('active', '=', '1');
            if($for == "prime"){
                $paymentMethods->where('prime', '=', '1');
            }
            $paymentMethods = $paymentMethods->with(['description', 'details'])
            ->get();
        
            if(!empty($paymentMethods) and count($paymentMethods) > 0){
                $i=0;
                foreach ($paymentMethods as $obj) {
                    $apiKey = ""; $apiSecret = "";
                    if(!empty($obj->details)){
                        foreach ($obj->details as $details) {
                            if($details->key == "RAZORPAY_KEY"){
                                $apiKey = $details->value;
                            } else if($details->key == "RAZORPAY_SECRET"){
                                $apiSecret = $details->value;
                            } else if($details->key == "CASHFREE_KEY"){
                                $apiKey = $details->value;
                            } else if($details->key == "CASHFREE_SECRET"){
                                $apiSecret = $details->value;
                            }
                        }
                    }
                    $row = array(
                        'environment' => $obj->environment,
                        'payment_method'=> $obj->payment_method,
                        'api_key' => $apiKey,
                        'api_secret' => $apiSecret,
                        'name' => $obj->description->name,
                        'status' => $obj->status,
                        'is_default' => $obj->is_default,
                    );
                    $result[$i] = $row;
                    $i++;
                }
            }
            return returnResponse("Payment methods are returned.!", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $result);
        }

        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
    }

    public static function payments_setting_for_wallet($request) {
        $payments_setting = DB::table('payment_description')
                        ->leftjoin('payment_methods', 'payment_methods.payment_methods_id', '=', 'payment_description.payment_methods_id')
                        ->select('payment_description.name', 'payment_methods.environment', 'payment_methods.status', 'payment_methods.payment_method')
                        ->where('language_id', 1)
                        ->where('payment_methods.status', 1)
                        ->where('payment_description.payment_methods_id', 3)->first();

        if (empty($payments_setting->name)) {
            $payments_setting = array();
        }
        return $payments_setting;
    }

    public static function payments_setting_for_brain_tree($request) {
        $payments_setting = DB::table('payment_methods_detail')
                        ->leftjoin('payment_description', 'payment_description.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
                        ->leftjoin('payment_methods', 'payment_methods.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
                        ->select('payment_methods_detail.*', 'payment_description.sub_name_1', 'payment_description.sub_name_2', 'payment_description.name', 'payment_methods.environment', 'payment_methods.status', 'payment_methods.payment_method')
                        ->where('language_id', 1)
                        ->where('payment_description.payment_methods_id', 1)->get()->keyBy('key');

        if (empty($payments_setting) or count($payments_setting) == 0) {
            $payments_setting = DB::table('payment_methods_detail')
                            ->leftjoin('payment_description', 'payment_description.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
                            ->leftjoin('payment_methods', 'payment_methods.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
                            ->select('payment_methods_detail.*', 'payment_description.sub_name_1', 'payment_description.sub_name_2', 'payment_description.name', 'payment_methods.environment', 'payment_methods.status', 'payment_methods.payment_method')
                            ->where('language_id', 1)
                            ->where('payment_description.payment_methods_id', 1)->get()->keyBy('key');
        }

        return $payments_setting;
    }

    public static function payments_setting_for_stripe($request) {
        $payments_setting = DB::table('payment_methods_detail')
                        ->leftjoin('payment_description', 'payment_description.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
                        ->leftjoin('payment_methods', 'payment_methods.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
                        ->select('payment_methods_detail.*', 'payment_description.name', 'payment_methods.environment', 'payment_methods.status', 'payment_methods.payment_method')
                        ->where('language_id', 1)
                        ->where('payment_description.payment_methods_id', 2)->get()->keyBy('key');

        if (empty($payments_setting) or count($payments_setting) == 0) {
            $payments_setting = DB::table('payment_methods_detail')
                            ->leftjoin('payment_description', 'payment_description.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
                            ->leftjoin('payment_methods', 'payment_methods.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
                            ->select('payment_methods_detail.*', 'payment_description.name', 'payment_methods.environment', 'payment_methods.status', 'payment_methods.payment_method')
                            ->where('language_id', 1)
                            ->where('payment_description.payment_methods_id', 2)->get()->keyBy('key');
        }

        return $payments_setting;
    }

    public static function payments_setting_for_cod($request) {
        $payments_setting = DB::table('payment_description')
                        ->leftjoin('payment_methods', 'payment_methods.payment_methods_id', '=', 'payment_description.payment_methods_id')
                        ->select('payment_description.name', 'payment_methods.environment', 'payment_methods.status', 'payment_methods.payment_method')
                        ->where('language_id', 1)
                    ->where('payment_methods.status', 1)
                        ->where('payment_description.payment_methods_id', 1)->first();

        if (empty($payments_setting->name)) {
            $payments_setting = array();
        }
        return $payments_setting;
    }

    public static function payments_setting_for_paypal($request) {
        $payments_setting = DB::table('payment_methods_detail')
                        ->leftjoin('payment_description', 'payment_description.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
                        ->leftjoin('payment_methods', 'payment_methods.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
                        ->select('payment_methods_detail.*', 'payment_description.name', 'payment_methods.environment', 'payment_methods.status', 'payment_methods.payment_method')
                        ->where('language_id', 1)
                        ->where('payment_description.payment_methods_id', 3)->get()->keyBy('key');
        if (empty($payments_setting) or count($payments_setting) == 0) {
            $payments_setting = DB::table('payment_methods_detail')
                            ->leftjoin('payment_description', 'payment_description.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
                            ->leftjoin('payment_methods', 'payment_methods.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
                            ->select('payment_methods_detail.*', 'payment_description.name', 'payment_methods.environment', 'payment_methods.status', 'payment_methods.payment_method')
                            ->where('language_id', 1)
                            ->where('payment_description.payment_methods_id', 3)->get()->keyBy('key');
        }
        return $payments_setting;
    }

    public static function payments_setting_for_instamojo($request) {
        $payments_setting = DB::table('payment_methods_detail')
                        ->leftjoin('payment_description', 'payment_description.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
                        ->leftjoin('payment_methods', 'payment_methods.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
                        ->select('payment_methods_detail.*', 'payment_description.name', 'payment_methods.environment', 'payment_methods.status', 'payment_methods.payment_method')
                        ->where('language_id', 1)
                        ->where('payment_description.payment_methods_id', 5)->get()->keyBy('key');

        if (empty($payments_setting) or count($payments_setting) == 0) {
            $payments_setting = DB::table('payment_methods_detail')
                            ->leftjoin('payment_description', 'payment_description.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
                            ->leftjoin('payment_methods', 'payment_methods.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
                            ->select('payment_methods_detail.*', 'payment_description.name', 'payment_methods.environment', 'payment_methods.status', 'payment_methods.payment_method')
                            ->where('language_id', 1)
                            ->where('payment_description.payment_methods_id', 5)->get()->keyBy('key');
        }

        return $payments_setting;
    }

    public static function payments_setting_for_directbank($request) {
        $payments_setting = DB::table('payment_methods_detail')
                        ->leftjoin('payment_description', 'payment_description.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
                        ->leftjoin('payment_methods', 'payment_methods.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
                        ->select('payment_methods_detail.*', 'payment_description.name', 'payment_methods.environment', 'payment_methods.status', 'payment_methods.payment_method', 'payment_description.sub_name_1')
                        ->where('language_id', 1)
                        ->where('payment_description.payment_methods_id', 9)
                        ->get()->keyBy('key');

        if (empty($payments_setting) or count($payments_setting) == 0) {
            $payments_setting = DB::table('payment_methods_detail')
                            ->leftjoin('payment_description', 'payment_description.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
                            ->leftjoin('payment_methods', 'payment_methods.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
                            ->select('payment_methods_detail.*', 'payment_description.name', 'payment_methods.environment', 'payment_methods.status', 'payment_methods.payment_method', 'payment_description.sub_name_1')
                            ->where('language_id', 1)
                            ->where('payment_description.payment_methods_id', 9)
                            ->get()->keyBy('key');
        }

        return $payments_setting;
    }

    public static function payments_setting_for_paystack($request) {
        $payments_setting = DB::table('payment_methods_detail')
                        ->leftjoin('payment_description', 'payment_description.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
                        ->leftjoin('payment_methods', 'payment_methods.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
                        ->select('payment_methods_detail.*', 'payment_description.name', 'payment_methods.environment', 'payment_methods.status', 'payment_methods.payment_method')
                        ->where('language_id', 1)
                        ->where('payment_description.payment_methods_id', 10)->get()->keyBy('key');

        if (empty($payments_setting) or count($payments_setting) == 0) {
            $payments_setting = DB::table('payment_methods_detail')
                            ->leftjoin('payment_description', 'payment_description.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
                            ->leftjoin('payment_methods', 'payment_methods.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
                            ->select('payment_methods_detail.*', 'payment_description.name', 'payment_methods.environment', 'payment_methods.status', 'payment_methods.payment_method')
                            ->where('language_id', 1)
                            ->where('payment_description.payment_methods_id', 10)->get()->keyBy('key');
        }

        return $payments_setting;
    }

    public static function payments_setting_for_hyperpay($request) {
        $payments_setting = DB::table('payment_methods_detail')
                        ->leftjoin('payment_description', 'payment_description.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
                        ->leftjoin('payment_methods', 'payment_methods.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
                        ->select('payment_methods_detail.*', 'payment_description.name', 'payment_methods.environment', 'payment_methods.status', 'payment_methods.payment_method')
                        ->where('language_id', 1)
                        ->where('payment_description.payment_methods_id', 6)->get()->keyBy('key');

        if (empty($payments_setting) or count($payments_setting) == 0) {
            $payments_setting = DB::table('payment_methods_detail')
                            ->leftjoin('payment_description', 'payment_description.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
                            ->leftjoin('payment_methods', 'payment_methods.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
                            ->select('payment_methods_detail.*', 'payment_description.name', 'payment_methods.environment', 'payment_methods.status', 'payment_methods.payment_method')
                            ->where('language_id', 1)
                            ->where('payment_description.payment_methods_id', 6)->get()->keyBy('key');
        }
        return $payments_setting;
    }

    public static function payments_setting_for_razorpay($request) {
        $payments_setting = DB::table('payment_methods_detail')
                        ->leftjoin('payment_description', 'payment_description.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
                        ->leftjoin('payment_methods', 'payment_methods.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
                        ->select('payment_methods_detail.*', 'payment_description.name', 'payment_methods.environment', 'payment_methods.status', 'payment_methods.payment_method')
                        ->where('language_id', 1)
                        ->where('payment_methods.status', 1)
                        ->where('payment_description.payment_methods_id', 2)
                        ->get()->keyBy('key');

        if (empty($payments_setting) or count($payments_setting) == 0) {
            $payments_setting = array();
        }
        return $payments_setting;
    }
    
    
    public static function payments_setting_for_cashfree($request) {
        $payments_setting = DB::table('payment_methods_detail')
                        ->leftjoin('payment_description', 'payment_description.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
                        ->leftjoin('payment_methods', 'payment_methods.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
                        ->select('payment_methods_detail.*', 'payment_description.name', 'payment_methods.environment', 'payment_methods.status', 'payment_methods.payment_method')
                        ->where('language_id', 1)
                ->where('payment_methods.status', 1)
                        ->where('payment_description.payment_methods_id', 4)
                        ->get()->keyBy('key');

        if (empty($payments_setting) or count($payments_setting) == 0) {
            $payments_setting = array();
        }
        return $payments_setting;
    }

    public static function payments_setting_for_paytm($request) {
        $payments_setting = DB::table('payment_methods_detail')
                        ->leftjoin('payment_description', 'payment_description.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
                        ->leftjoin('payment_methods', 'payment_methods.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
                        ->select('payment_methods_detail.*', 'payment_description.name', 'payment_methods.environment', 'payment_methods.status', 'payment_methods.payment_method')
                        ->where('language_id', 1)
                        ->where('payment_description.payment_methods_id', 8)
                        ->get()->keyBy('key');

        if (empty($payments_setting) or count($payments_setting) == 0) {
            $payments_setting = DB::table('payment_methods_detail')
                            ->leftjoin('payment_description', 'payment_description.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
                            ->leftjoin('payment_methods', 'payment_methods.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
                            ->select('payment_methods_detail.*', 'payment_description.name', 'payment_methods.environment', 'payment_methods.status', 'payment_methods.payment_method')
                            ->where('language_id', 1)
                            ->where('payment_description.payment_methods_id', 8)
                            ->get()->keyBy('key');
        }

        return $payments_setting;
    }

    public static function getrate($request) {
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

            //tax rate
            $tax_zone_id = $request->tax_zone_id;
            //$requested_currency =   $request->currency_code;
            $requested_currency = 'INR';

            $index = '0';
            $total_tax = '0';
            $is_number = true;
            foreach ($request->products as $products_data) {
                $final_price = $request->products[$index]['final_price'];
                $products = DB::table('products')
                                ->LeftJoin('tax_rates', 'tax_rates.tax_class_id', '=', 'products.products_tax_class_id')
                                ->where('tax_rates.tax_zone_id', $tax_zone_id)
                                ->where('products_id', $products_data['products_id'])->get();
                if (count($products) > 0) {
                    $tax_value = $products[0]->tax_rate / 100 * $final_price;
                    $total_tax = $total_tax + $tax_value;
                    $index++;
                }
            }

            if ($total_tax > 0) {
                $total_tax = Orders::convertprice($total_tax, $requested_currency);
                $data['tax'] = $total_tax;
            } else {
                $data['tax'] = '0';
            }


            $countries = DB::table('countries')->where('countries_id', '=', $request->country_id)->get();

            //website path
            $websiteURL = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
            $replaceURL = str_replace('getRate', '', $websiteURL);
            $requiredURL = $replaceURL . 'app/ups/ups.php';


            //default shipping method
            $shippings = DB::table('shipping_methods')->get();

            $result = array();

            foreach ($shippings as $shipping_methods) {
                //ups shipping rate
                if ($shipping_methods->methods_type_link == 'upsShipping' and $shipping_methods->status == '1') {
                    $result2 = array();
                    $is_transaction = '0';

                    $ups_shipping = DB::table('ups_shipping')->where('ups_id', '=', '1')->get();

                    //shipp from and all credentials
                    $accessKey = $ups_shipping[0]->access_key;
                    $userId = $ups_shipping[0]->user_name;
                    $password = $ups_shipping[0]->password;

                    //ship from address
                    $fromAddress = $ups_shipping[0]->address_line_1;
                    $fromPostalCode = $ups_shipping[0]->post_code;
                    $fromCity = $ups_shipping[0]->city;
                    $fromState = $ups_shipping[0]->state;
                    $fromCountry = $ups_shipping[0]->country;

                    //ship to address
                    $toPostalCode = $request->postcode;
                    $toCity = $request->city;
                    $toState = $request->state;
                    $toCountry = $countries[0]->countries_iso_code_2;
                    $toAddress = $request->street_address;

                    //product detail
                    $products_weight = $request->products_weight;
                    $products_weight_unit = $request->products_weight_unit;
                    $productsWeight = 0;
                    //change G or KG to LBS
                    if ($products_weight_unit == 'g') {
                        $productsWeight = $products_weight / 453.59237;
                    } else if ($products_weight_unit == 'kg') {
                        $productsWeight = $products_weight / 0.45359237;
                    }

                    //production or test mode
                    if ($ups_shipping[0]->shippingEnvironment == 1) {    #production mode
                        $useIntegration = true;
                    } else {
                        $useIntegration = false;        #test mode
                    }

                    $serviceData = explode(',', $ups_shipping[0]->serviceType);


                    $index = 0;
                    $description = DB::table('shipping_description')->where([
                                ['language_id', '=', 1],
                                ['table_name', '=', 'ups_shipping'],
                            ])->get();

                    $sub_labels = json_decode($description[0]->sub_labels);

                    foreach ($serviceData as $value) {
                        if ($value == "US_01") {
                            $name = $sub_labels->nextDayAir;
                            $serviceTtype = "1DA";
                        } else if ($value == "US_02") {
                            $name = $sub_labels->secondDayAir;
                            $serviceTtype = "2DA";
                        } else if ($value == "US_03") {
                            $name = $sub_labels->ground;
                            $serviceTtype = "GND";
                        } else if ($value == "US_12") {
                            $name = $sub_labels->threeDaySelect;
                            $serviceTtype = "3DS";
                        } else if ($value == "US_13") {
                            $name = $sub_labels->nextDayAirSaver;
                            $serviceTtype = "1DP";
                        } else if ($value == "US_14") {
                            $name = $sub_labels->nextDayAirEarlyAM;
                            $serviceTtype = "1DM";
                        } else if ($value == "US_59") {
                            $name = $sub_labels->secondndDayAirAM;
                            $serviceTtype = "2DM";
                        } else if ($value == "IN_07") {
                            $name = Lang::get("labels.Worldwide Express");
                            $serviceTtype = "UPSWWE";
                        } else if ($value == "IN_08") {
                            $name = Lang::get("labels.Worldwide Expedited");
                            $serviceTtype = "UPSWWX";
                        } else if ($value == "IN_11") {
                            $name = Lang::get("labels.Standard");
                            $serviceTtype = "UPSSTD";
                        } else if ($value == "IN_54") {
                            $name = Lang::get("labels.Worldwide Express Plus");
                            $serviceTtype = "UPSWWEXPP";
                        }

                        $some_data = array(
                            'access_key' => $accessKey, # UPS License Number
                            'user_name' => $userId, # UPS Username
                            'password' => $password, # UPS Password
                            'pickUpType' => '03', # Drop Off Location
                            'shipToPostalCode' => $toPostalCode, # Destination  Postal Code
                            'shipToCountryCode' => $toCountry, # Destination  Country
                            'shipFromPostalCode' => $fromPostalCode, # Origin Postal Code
                            'shipFromCountryCode' => $fromCountry, # Origin Country
                            'residentialIndicator' => 'IN', # Residence Shipping and for commercial shipping "COM"
                            'cServiceCodes' => $serviceTtype, # Sipping rate for UPS Ground
                            'packagingType' => '02',
                            'packageWeight' => $productsWeight
                        );

                        $curl = curl_init();
                        // You can also set the URL you want to communicate with by doing this:
                        // $curl = curl_init('http://localhost/echoservice');
                        // We POST the data
                        curl_setopt($curl, CURLOPT_POST, 1);
                        // Set the url path we want to call
                        curl_setopt($curl, CURLOPT_URL, $requiredURL);
                        // Make it so the data coming back is put into a string
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                        // Insert the data
                        curl_setopt($curl, CURLOPT_POSTFIELDS, $some_data);

                        // You can also bunch the above commands into an array if you choose using: curl_setopt_array
                        // Send the request
                        $rate = curl_exec($curl);
                        // Free up the resources $curl is using
                        curl_close($curl);
                        $ups_description = DB::table('shipping_description')->where('table_name', 'ups_shipping')->where('language_id', 1)->get();
                        if (!empty($ups_description[0]->name)) {
                            $methodName = $ups_description[0]->name;
                        } else {
                            $methodName = 'UPS Shipping';
                        }
                        if (is_numeric($rate)) {
                            $rate = Orders::convertprice($rate, $requested_currency);
                            $success = array('success' => '1', 'message' => "Rate is returned.", 'name' => $methodName);
                            $result2[$index] = array('name' => $name, 'rate' => $rate, 'currencyCode' => $requested_currency, 'shipping_method' => 'upsShipping');
                            $index++;
                        } else {
                            $success = array('success' => '0', 'message' => "Selected regions are not supported for UPS shipping", 'name' => $ups_description[0]->name);
                        }

                        $success['services'] = $result2;
                        $result['upsShipping'] = $success;
                    }
                } else if ($shipping_methods->methods_type_link == 'flateRate' and $shipping_methods->status == '1') {
                    $description = DB::table('shipping_description')->where('table_name', 'flate_rate')->where('language_id', 1)->get();

                    if (!empty($description[0]->name)) {
                        $methodName = $description[0]->name;
                    } else {
                        $methodName = 'Flate Rate';
                    }

                    $ups_shipping = DB::table('flate_rate')->where('id', '=', '1')->get();
                    $rate = Orders::convertprice($ups_shipping[0]->flate_rate, $requested_currency);
                    $data2 = array('name' => $methodName, 'rate' => $rate, 'currencyCode' => $requested_currency, 'shipping_method' => 'flateRate');
                    if (count($ups_shipping) > 0) {
                        $success = array('success' => '1', 'message' => "Rate is returned.", 'name' => $methodName);
                        $success['services'][0] = $data2;
                        $result['flateRate'] = $success;
                    }
                } else if ($shipping_methods->methods_type_link == 'localPickup' and $shipping_methods->status == '1') {
                    $description = DB::table('shipping_description')->where('table_name', 'local_pickup')->where('language_id', 1)->get();

                    if (!empty($description[0]->name)) {
                        $methodName = $description[0]->name;
                    } else {
                        $methodName = 'Local Pickup';
                    }

                    $data2 = array('name' => $methodName, 'rate' => '0', 'currencyCode' => $requested_currency, 'shipping_method' => 'localPickup');
                    $success = array('success' => '1', 'message' => "Rate is returned.", 'name' => $methodName);
                    $success['services'][0] = $data2;
                    $result['localPickup'] = $success;
                } else if ($shipping_methods->methods_type_link == 'freeShipping' and $shipping_methods->status == '1') {
                    $description = DB::table('shipping_description')->where('table_name', 'free_shipping')->where('language_id', 1)->get();

                    if (!empty($description[0]->name)) {
                        $methodName = $description[0]->name;
                    } else {
                        $methodName = 'Free Shipping';
                    }

                    $data2 = array('name' => $methodName, 'rate' => '0', 'currencyCode' => $requested_currency, 'shipping_method' => 'freeShipping');
                    $success = array('success' => '1', 'message' => "Rate is returned.", 'name' => $methodName);
                    $success['services'][0] = $data2;
                    $result['freeShipping'] = $success;
                } else if ($shipping_methods->methods_type_link == 'shippingByWeight' and $shipping_methods->status == '1') {
                    $description = DB::table('shipping_description')->where('table_name', 'shipping_by_weight')->where('language_id', 1)->get();
                    if (!empty($description[0]->name)) {
                        $methodName = $description[0]->name;
                    } else {
                        $methodName = 'Shipping Price';
                    }

                    $weight = $request->products_weight;

                    //check price by weight
                    $priceByWeight = DB::table('products_shipping_rates')->where('weight_from', '<=', $weight)->where('weight_to', '>=', $weight)->get();

                    if (!empty($priceByWeight) and count($priceByWeight) > 0) {
                        $price = $priceByWeight[0]->weight_price;
                        $rate = Orders::convertprice($price, $requested_currency);
                    } else {
                        $rate = 0;
                    }

                    $data2 = array('name' => $methodName, 'rate' => $rate, 'currencyCode' => $requested_currency, 'shipping_method' => 'Shipping Price');
                    $success = array('success' => '1', 'message' => "Rate is returned.", 'name' => $methodName);
                    $success['services'][0] = $data2;
                    $result['freeShipping'] = $success;
                }
            }
            $data['shippingMethods'] = $result;

            $responseData = array('success' => '1', 'data' => $data, 'message' => "Data is returned.");
        } else {
            $responseData = array('success' => '0', 'data' => array(), 'message' => "Unauthenticated call.");
        }
        $orderResponse = json_encode($responseData);

        return $orderResponse;
    }

    public static function getcoupon($request) {

        $result = array();
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

            $coupons = DB::table('coupons')->where('code', '=', $request->code)->get();

            if (count($coupons) > 0) {

                if (!empty($coupons[0]->product_ids)) {
                    $product_ids = explode(',', $coupons[0]->product_ids);
                    $coupons[0]->product_ids = $product_ids;
                } else {
                    $coupons[0]->product_ids = array();
                }

                if (!empty($coupons[0]->exclude_product_ids)) {
                    $exclude_product_ids = explode(',', $coupons[0]->exclude_product_ids);
                    $coupons[0]->exclude_product_ids = $exclude_product_ids;
                } else {
                    $coupons[0]->exclude_product_ids = array();
                }

                if (!empty($coupons[0]->product_categories)) {
                    $product_categories = explode(',', $coupons[0]->product_categories);
                    $coupons[0]->product_categories = $product_categories;
                } else {
                    $coupons[0]->product_categories = array();
                }

                if (!empty($coupons[0]->excluded_product_categories)) {
                    $excluded_product_categories = explode(',', $coupons[0]->excluded_product_categories);
                    $coupons[0]->excluded_product_categories = $excluded_product_categories;
                } else {
                    $coupons[0]->excluded_product_categories = array();
                }

                if (!empty($coupons[0]->email_restrictions)) {
                    $email_restrictions = explode(',', $coupons[0]->email_restrictions);
                    $coupons[0]->email_restrictions = $email_restrictions;
                } else {
                    $coupons[0]->email_restrictions = array();
                }

                if (!empty($coupons[0]->used_by)) {
                    $used_by = explode(',', $coupons[0]->used_by);
                    $coupons[0]->used_by = $used_by;
                } else {
                    $coupons[0]->used_by = array();
                }

                $responseData = array('success' => '1', 'data' => $coupons, 'message' => "Coupon info is returned.");
            } else {
                $responseData = array('success' => '0', 'data' => $coupons, 'message' => "Coupon doesn't exist.");
            }
        } else {
            $responseData = array('success' => '0', 'data' => array(), 'message' => "Unauthenticated call.");
        }

        $orderResponse = json_encode($responseData);

        return $orderResponse;
    }

    public static function addtoorder($request) {
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
        $ipAddress = Orders::get_client_ip_env();

        if ($authenticate == 1 && auth()->user()->id) {
            Log::debug('Total Price : ' . $request->totalPrice);
            $cust_info = DB::table('customers')->where('id', auth()->user()->id)->where('status', '1')->first();
            Log::debug('Cust Info >>');
            if (isset($cust_info->id)) {
                Log::debug($request->products);
                DB::beginTransaction();
                $produstsArray = json_decode($request->products, true);
                Log::debug($produstsArray);
                foreach ($produstsArray as $product) {
                    $products = json_decode($product, true);
                    $req = array();
                    $req['products_id'] = $products['products_id'];
                    $attributes_data = '';
                    if (isset($products['attributes']) && count($products['attributes']) > 0) {
                        $req['attributes'] = $products['attributes'];
                        $attributes_data = array();
                        for ($j = 0; $j < count($products['attributes']); $j++) {
                            array_push($attributes_data, $products['attributes'][$j]["products_options_values"]["products_attributes_id"]);
                        }
                    }
                    $check = Product::getquantity($req, $products['products_id'], $attributes_data);
                    //$check = json_decode($check, true);
                    if ($products['customers_basket_quantity'] > $check['stock']) {
                        $responseData = array('success' => '1', 'data' => array(), 'products_id' => $products['products_id'], 'message' => "Some Products are out of Stock.");
                        $orderResponse = json_encode($responseData);
                        return $orderResponse;
                    }
                }

                //$guest_status             = $request->guest_status;
                $address_book_id = $request->address_id;
                Log::debug('Payment Method : ' . $request->payment_method);
                $addresses = DB::table('address_book')->where('address_book_id', $address_book_id)
                        ->leftJoin('countries', 'countries.countries_id', '=', 'address_book.entry_country_id')
                        ->leftJoin('zones', 'zones.zone_id', '=', 'address_book.entry_zone_id')
                        ->first();
                /*
                  if($guest_status == 1){
                  $check = DB::table('users')->where('role_id',2)->where('id',auth()->user()->id)->first();
                  if($check == null){
                  $customers_id = DB::table('users')
                  ->insertGetId([
                  'role_id' => 2,
                  'email' => $request->email,
                  'password' => Hash::make('123456dfdfdf'),
                  'first_name' => $request->delivery_firstname,
                  'last_name' => $request->delivery_lastname,
                  'phone' => $request->customers_telephone
                  ]);
                  }
                  else{
                  $customers_id = $check->id;
                  }
                  }
                  else{
                  $customers_id            				=   auth()->user()->id;
                  }
                 */
                $customers_id = auth()->user()->id;

                $date_added = date('Y-m-d h:i:s');
                $customers_telephone = auth()->user()->phone;
                $email = auth()->user()->email;
                $name = $addresses->entry_firstname;
                $mobile_no = auth()->user()->phone;
                Log::debug("Name : $name, Mobile : $mobile_no");
                $delivery_firstname = $addresses->entry_firstname;
                $delivery_lastname = $addresses->entry_lastname;
                $delivery_street_address = $addresses->entry_street_address;
                $delivery_suburb = $addresses->entry_suburb;
                $delivery_city = $addresses->entry_city;
                $delivery_postcode = $addresses->entry_postcode;
                $delivery_state = $addresses->zone_name;
                $delivery_country = $addresses->countries_name;


                $billing_firstname = $addresses->entry_firstname; //$request->entry_firstname;
                $billing_lastname = $addresses->entry_lastname; //$request->entry_lastname;
                $billing_street_address = $addresses->entry_street_address; //$request->entry_street_address;
                $billing_suburb = $addresses->entry_suburb; //$request->entry_suburb;
                $billing_city = $addresses->entry_city;
                $billing_postcode = $addresses->entry_postcode;
                $billing_state = $addresses->zone_name;
                $billing_country = $addresses->countries_name; //$request->countries_name;

                $payment_method = $request->payment_method;
                $platform = $request->platform;


                $order_information = array();

                $cc_type = '';
                $cc_owner = '';
                $cc_number = '';
                $cc_expires = '';
                $last_modified = date('Y-m-d H:i:s');
                $date_purchased = date('Y-m-d H:i:s');
                $order_price = $request->totalPrice;
                $currency_code = 'INR';
                $shipping_cost = 0;
                $wallet_amount = $request->wallet_amount;
                $pgateway_amount = $request->pgateway_amount;
                $cod_amount = $request->cod_amount;
                $total_quantity = $request->total_quantity;
                $net_amount = $request->net_amount;

                $orders_status = '5';
                $orders_date_finished = $request->orders_date_finished;
                $comments = 'New Order Recieved';

                //additional fields
                $delivery_phone = auth()->user()->phone;
                $billing_phone = auth()->user()->phone;

                $settings = DB::table('settings')->get();
                $currency_value = '1';

                //tax info
                $total_tax = $request->total_tax;
                $total_tax = Orders::converttodefaultprice($request->total_tax, $currency_code);

                $products_tax = 1;
                //coupon info
                $is_coupon_applied = $request->is_coupon_applied;

                if ($is_coupon_applied == 1) {

                    $code = array();
                    $coupon_amount = 0;
                    $exclude_product_ids = array();
                    $product_categories = array();
                    $excluded_product_categories = array();
                    $exclude_product_ids = array();

                    $coupon_amount = $request->coupon_amount;

                    //convert price to default currency price
                    $coupon_amount = Orders::converttodefaultprice($coupon_amount, $currency_code);

                    foreach ($request->coupons as $coupons_data) {

                        //update coupans
                        $coupon_id = DB::statement("UPDATE `coupons` SET `used_by`= CONCAT(used_by,',$customers_id') WHERE `code` = '" . $coupons_data['code'] . "'");
                    }
                    $code = json_encode($request->coupons);
                } else {
                    $code = '';
                    $coupon_amount = 0;
                }
                $shipping_method = "Shiprocket";
                $status = 'PENDING';
                //payment methods
                Log::debug('payment method ' . $payment_method);
                $paymentMethodName = '';
                if ($payment_method == 'cod') {
                    Log::debug('in cod' . $payment_method);
                    $payments_setting = Orders::payments_setting_for_cod($request);
                    $paymentMethodName = 'Cash on Delivery';
                    $payment_method = 'Cash on Delivery';
                    $payment_status = 'PENDING';
                    $status = 'ORDERED';
                    $pgateway_amount = 0;
                } else if ($payment_method == 'razorpay') {
                    Log::debug('in razror pay' . $payment_method);
                    $payments_setting = Orders::payments_setting_for_razorpay($request);
                    Log::debug('in razror pay settings' . json_encode($payments_setting));
                    $paymentMethodName = 'Razor Pay';
                    $payment_status = 'PENDING';
                    $status = 'PENDING';
                    $orders_status = '1';
                    if ($pgateway_amount < 1) {
                        Log::error("Error Occured please try again ! Payment Gateway Amount is less than 1. i.e. $pgateway_amount");
                        return returnResponse("Error Occured please try again ! Payment is less than 1.");
                    }
                } else if ($payment_method == 'wallet') {
                    Log::debug('in Wallet ' . $payment_method);
                    $payments_setting = Orders::payments_setting_for_wallet($request);
                    Log::debug('in Wallet settings' . json_encode($payments_setting));
                    $paymentMethodName = 'WALLET';
                    if ($wallet_amount == $net_amount) {
                        $payment_status = 'SUCCESS';
                        $status = 'ORDERED';
                    } else {
                        $payment_status = 'PENDING';
                        $status = 'PENDING';
                        $orders_status = '1';
                    }
                } else {
                    Log::error("Unknown payment method i.e. $payment_method ");
                    return returnResponse("Error Occured please try again ! Unknown payment method.");
                }

                $order_id = generateOrderId();
                if (empty($paymentMethodName)) {
                    //$paymentMethodName = "Razorpay";
                    return returnResponse("Error Occured please try again ! Unknown payment method.");
                }
                Log::debug("Order Id : $order_id");
                if (empty($order_id)) {
                    Log::error("Order Id Not Generated ");
                    return returnResponse("Error Occured please try again!");
                }
                Log::debug("Order Id : $order_id");


                if ($wallet_amount > 0) {
                    if ($cust_info->wallet_balance >= $wallet_amount) {
                        $payable_amount = $net_amount - $wallet_amount;
                        Log::debug("Payable Amount $payable_amount ");
                        Log::debug("Form Wallet Amount $wallet_amount ");
                        $balance_after = $cust_info->wallet_balance - $wallet_amount;
                        $order_type = "PRODUCT ORDER";
                        $txn_desc = "Product Order";
                        if (!WalletModel::debitFromMainWallet($customers_id, $wallet_amount, $balance_after, $txn_desc, $order_id, $order_type)) {
                            Log::error("error while debiting Wallet !!!");
                            return returnResponse("Order failed ! Wallet debiting failed");
                        }
                    } else {
                        Log::error("Wallet balance " . $cust_info->wallet_balance . " is less than wallet $wallet_amount used !!");
                        return returnResponse("Wallet can be used up to " . $cust_info->wallet_balance);
                    }
                }


                //insert order
                $orders_id = DB::table('orders')->insertGetId(
                        ['customers_id' => $customers_id,
                            'customers_name' => $delivery_firstname . ' ' . $delivery_lastname,
                            'customers_street_address' => $delivery_street_address,
                            'customers_suburb' => $delivery_suburb,
                            'customers_city' => $delivery_city,
                            'customers_postcode' => $delivery_postcode,
                            'customers_state' => $delivery_state,
                            'customers_country' => $delivery_country,
                            'customers_telephone' => $customers_telephone,
                            'email' => $email,
                            'delivery_name' => $delivery_firstname . ' ' . $delivery_lastname,
                            'delivery_street_address' => $delivery_street_address,
                            'delivery_suburb' => $delivery_suburb,
                            'delivery_city' => $delivery_city,
                            'delivery_postcode' => $delivery_postcode,
                            'delivery_state' => $delivery_state,
                            'delivery_country' => $delivery_country,
                            'billing_name' => $billing_firstname . ' ' . $billing_lastname,
                            'billing_street_address' => $billing_street_address,
                            'billing_suburb' => $billing_suburb,
                            'billing_city' => $billing_city,
                            'billing_postcode' => $billing_postcode,
                            'billing_state' => $billing_state,
                            'billing_country' => $billing_country,
                            'payment_method' => $paymentMethodName,
                            'cc_type' => $cc_type,
                            'cc_owner' => $cc_owner,
                            'cc_number' => $cc_number,
                            'cc_expires' => $cc_expires,
                            'last_modified' => $last_modified,
                            'date_purchased' => $date_purchased,
                            'order_price' => $order_price,
                            'wallet_amount' => $wallet_amount,
                            'pgateway_amount' => $pgateway_amount,
                            'cod_amount' => $cod_amount,
                            'total_quantity' => $total_quantity,
                            'net_amount' => $net_amount,
                            'shipping_cost' => $shipping_cost,
                            'shipping_method' => $shipping_method,
                            'currency' => $currency_code,
                            'currency_value' => $currency_value,
                            'order_information' => json_encode($order_information),
                            'coupon_code' => $code,
                            'coupon_amount' => $coupon_amount,
                            'total_tax' => $total_tax,
                            'ordered_source' => '2', // App
                            'delivery_phone' => $delivery_phone,
                            'billing_phone' => $billing_phone,
                            'order_id' => $order_id,
                            'payment_status' => $payment_status,
                            'status' => $status,
                ]);


                //orders status history
                $orders_history_id = DB::table('orders_status_history')->insertGetId(
                        ['orders_id' => $orders_id,
                            'orders_status_id' => $orders_status,
                            'date_added' => $date_added,
                            'customer_notified' => '1',
                            'comments' => $comments
                ]);

                foreach ($produstsArray as $product) {
                    $products = json_decode($product, true);
                    //dd($products['price'], $currency_code);
                    $c_price = str_replace(',', '', $products['price']);
                    $c_final_price = str_replace(',', '', $products['final_price']);
                    $price = Orders::converttodefaultprice($c_price, $currency_code);
                    $final_price = $c_final_price * $products['customers_basket_quantity'];
                    $final_price = Orders::converttodefaultprice($final_price, $currency_code);

                    $orders_products_id = DB::table('orders_products')->insertGetId(
                            [
                                'orders_id' => $orders_id,
                                'products_id' => $products['products_id'],
                                'products_name' => $products['products_name'],
                                'products_price' => $price,
                                'final_price' => $final_price,
                                'products_tax' => $products_tax,
                                'products_quantity' => $products['customers_basket_quantity'],
                    ]);

                    $inventory_ref_id = DB::table('inventory')->insertGetId([
                        'products_id' => $products['products_id'],
                        'reference_code' => '',
                        'stock' => $products['customers_basket_quantity'],
                        'admin_id' => 0,
                        'added_date' => time(),
                        'purchase_price' => 0,
                        'stock_type' => 'out',
                    ]);


                    if (!empty($products['attributes'])) {
                        Log::debug('attributes' . json_encode($products['attributes']));
                        foreach ($products['attributes'] as $attribute) {
                            Log::debug('attributes foreach' . json_encode($attribute));
                            Log::debug('product option id' . $attribute['products_options']["id"]);
                            Log::debug('product option values id' . $attribute['products_options_values']["products_attributes_id"]);
                            DB::table('orders_products_attributes')->insert(
                                    [
                                        'orders_id' => $orders_id,
                                        'products_id' => $products['products_id'],
                                        'orders_products_id' => $orders_products_id,
                                        'products_options' => $attribute['products_options']["id"],
                                        'products_options_values' => $attribute['products_options_values']["value"],
                                        'products_options_values_name' => $attribute['products_options_values']["name"],
                                        'products_options_name' => $attribute['products_options']["name"],
                                        'options_values_price' => $attribute['products_options_values']['price'],
                                        'swatch_type' => $attribute['products_options']['swatch_type'],
                                        'price_prefix' => $attribute['products_options_values']['price_prefix']
                            ]);

                            $products_attributes = DB::table('products_attributes')->where([
                                        ['options_id', '=', $attribute['products_options']["id"]],
                                        ['products_attributes_id', '=', $attribute['products_options_values']["products_attributes_id"]],
                                    ])->get();

                            DB::table('inventory_detail')->insert([
                                'inventory_ref_id' => $inventory_ref_id,
                                'products_id' => $products['products_id'],
                                'attribute_id' => $products_attributes[0]->products_attributes_id,
                            ]);
                        }
                    }
                }


                //send order email to user
                $order = DB::table('orders')
                                ->LeftJoin('orders_status_history', 'orders_status_history.orders_id', '=', 'orders.orders_id')
                                ->LeftJoin('orders_status', 'orders_status.orders_status_id', '=', 'orders_status_history.orders_status_id')
                                ->where('orders.orders_id', '=', $orders_id)->orderby('orders_status_history.date_added', 'DESC')->get();

                //foreach
                foreach ($order as $data) {
                    $orders_id = $data->orders_id;

                    $orders_products = DB::table('orders_products')
                                    ->join('products', 'products.products_id', '=', 'orders_products.products_id')
                                    ->select('orders_products.*', 'products.products_image as image')
                                    ->where('orders_products.orders_id', '=', $orders_id)->get();
                    $i = 0;
                    $total_price = 0;
                    $product = array();
                    $subtotal = 0;
                    foreach ($orders_products as $orders_products_data) {
                        $product_attribute = DB::table('orders_products_attributes')
                                ->where([
                                    ['orders_products_id', '=', $orders_products_data->orders_products_id],
                                    ['orders_id', '=', $orders_products_data->orders_id],
                                ])
                                ->get();

                        $orders_products_data->attribute = $product_attribute;
                        $product[$i] = $orders_products_data;
                        //$total_tax	 = $total_tax+$orders_products_data->products_tax;
                        $total_price = $total_price + $orders_products[$i]->final_price;

                        $subtotal += $orders_products[$i]->final_price;

                        $i++;
                    }

                    $data->data = $product;
                    $orders_data[] = $data;
                }

                $orders_status_history = DB::table('orders_status_history')
                                ->LeftJoin('orders_status', 'orders_status.orders_status_id', '=', 'orders_status_history.orders_status_id')
                                ->orderBy('orders_status_history.date_added', 'desc')
                                ->where('orders_id', '=', $orders_id)->get();

                $orders_status = DB::table('orders_status')->get();

                $ordersData['orders_data'] = $orders_data;
                $ordersData['total_price'] = $total_price;
                $ordersData['orders_status'] = $orders_status;
                $ordersData['orders_status_history'] = $orders_status_history;
                $ordersData['subtotal'] = $subtotal;

                if ($wallet_amount > 0 && $pgateway_amount > 0) {
                    if ($net_amount != ($wallet_amount + $pgateway_amount)) {
                        Log::error(__CLASS__ . " :: " . __FUNCTION__ . " Order placing failed ! Wallet amount : $wallet_amount + P Gateway : $pgateway_amount != Net Amount : $net_amount");
                        return returnResponse("Order failed !!!");
                    }
                } else if ($pgateway_amount > 0 && $pgateway_amount != $net_amount) {
                    Log::error(__CLASS__ . " :: " . __FUNCTION__ . " Order placing failed ! P Gateway : $pgateway_amount != Net Amount : $net_amount");
                    return returnResponse("Order failed !!!");
                } else if ($wallet_amount > 0 && ($wallet_amount + $cod_amount) != $net_amount) {
                    Log::error(__CLASS__ . " :: " . __FUNCTION__ . " Order placing failed ! Wallet Amount : $wallet_amount + COD Amount : $cod_amount != Net Amount : $net_amount");
                    return returnResponse("Order failed !!!");
                }
                if ($payment_method == "razorpay") {
                    $txn_id = generateGatewayTxnId();
                    Log::debug(__CLASS__ . " :: " . __FUNCTION__ . " pgateway_amount = $pgateway_amount");
                    $payment_data = PaymentGatewayModel::initiatePayment($customers_id, $customers_id, $orders_id, $order_id, $txn_id, $name, $mobile_no, auth()->user()->email, $pgateway_amount, $platform);
                    if ($payment_data) {
                        if (isset($payment_data["order_id"])) {
                            $data = array(
                                'razorpay_order_id' => $payment_data["order_id"],
                                'order_id' => $order_id,
                                'txn_id' => $txn_id,
                            );
                            DB::commit();
                            Log::info(__CLASS__ . " :: " . __FUNCTION__ . " Order placing success with razorpay order id " . $payment_data["order_id"]);
                            return returnResponse("Order has been placed successfully.", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $data);
                        } else {
                            Log::error(__CLASS__ . " :: " . __FUNCTION__ . " Order placing failed ! razorpay_order_id not set .");
                            return returnResponse("Order failed !!!");
                        }
                    } else {
                        Log::error(__CLASS__ . " :: " . __FUNCTION__ . " error while placing order !!!!!");
                        return returnResponse("Order failed !");
                    }
                }



                //notification/email
                //$myVar = new AlertController();
                //$alertSetting = $myVar->orderAlert($ordersData);
                //$responseData = array('success'=>'1', 'data'=>array(), 'customer_id' => $customers_id,'message'=>"Order has been placed successfully.");
                DB::commit();
                return returnResponse("Order has been placed successfully.", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, array('order_id' => $order_id));
            } else {
                
            }
        }
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
    }

    public static function getorders($request) {

        //$customers_id =  $request->customers_id;
        $customers_id = auth()->user()->id;
        $language_id = 1;
        $requested_currency = $request->currency_code;
        $consumer_data = getallheaders();
        $consumer_data['consumer_ip'] = $request->ip();
        $consumer_data['consumer_url'] = __FUNCTION__;
        $authController = new AppSettingController();
        $authenticate = $authController->apiAuthenticate($consumer_data);

        if ($authenticate == 1) {
            if ($request->has('offset')) {
                $offset = $request->offset;
            } else {
                $offset = '0';
            }

            $limit = 10;
            Log::debug('Offset : ' . $offset . ", Limit : $limit");
            $order = DB::table('orders')->orderBy('orders_id', 'desc')
                            ->where([
                                ['customers_id', '=', $customers_id],
                            ])->offset($offset)->take($limit)->get();


            if (count($order) > 0) {
                //foreach
                $index = '0';
                foreach ($order as $data) {
                    $data->total_tax = Orders::convertprice($data->total_tax, $requested_currency);
                    $data->order_price = Orders::convertprice($data->order_price, $requested_currency);
                    $data->shipping_cost = Orders::convertprice($data->shipping_cost, $requested_currency);
                    $data->coupon_amount = Orders::convertprice($data->coupon_amount, $requested_currency);

                    if (!empty($data->product_discount_percentage)) {
                        $product_ids = explode(',', $coupons[0]->product_ids);
                        $data->product_ids = $product_ids;
                    } else {
                        $data->product_ids = array();
                    }

                    if (!empty($data->discount_type)) {
                        $exclude_product_ids = explode(',', $data->discount_type);
                        $data->discount_type = $exclude_product_ids;
                    } else {
                        $data->discount_type = array();
                    }

                    if (!empty($data->amount)) {
                        $product_categories = explode(',', $data[0]->amount);
                        $data->amount = $product_categories;
                    } else {
                        $data->amount = array();
                    }

                    if (!empty($data->product_ids)) {
                        $excluded_product_categories = explode(',', $data->product_ids);
                        $data->product_ids = $excluded_product_categories;
                    } else {
                        $data->product_ids = array();
                    }

                    if (!empty($data->exclude_product_ids)) {
                        $email_restrictions = explode(',', $data->exclude_product_ids);
                        $data->exclude_product_ids = $email_restrictions;
                    } else {
                        $data->exclude_product_ids = array();
                    }

                    if (!empty($data->usage_limit)) {
                        $used_by = explode(',', $data->usage_limit);
                        $data->usage_limit = $used_by;
                    } else {
                        $data->usage_limit = array();
                    }

                    if (!empty($data->product_categories)) {
                        $used_by = explode(',', $data->product_categories);
                        $data->product_categories = $used_by;
                    } else {
                        $data->product_categories = array();
                    }

                    if (!empty($data->excluded_product_categories)) {
                        $used_by = explode(',', $data->excluded_product_categories);
                        $data->excluded_product_categories = $used_by;
                    } else {
                        $data->excluded_product_categories = array();
                    }

                    if (!empty($data->coupon_code)) {

                        $coupon_code = $data->coupon_code;

                        $coupon_datas = array();
                        $index_c = 0;
                        foreach (json_decode($coupon_code) as $coupon_codes) {

                            if (!empty($coupon_codes->code)) {
                                $code = explode(',', $coupon_codes->code);
                                $coupon_datas[$index_c]['code'] = $code[0];
                            } else {
                                $coupon_datas[$index_c]['code'] = '';
                            }

                            if (!empty($coupon_codes->amount)) {
                                $amount = explode(',', $coupon_codes->amount);
                                $amount = Orders::convertprice($amount, $requested_currency);
                                $coupon_datas[$index_c]['amount'] = $amount[0];
                            } else {
                                $coupon_datas[$index_c]['amount'] = '';
                            }


                            if (!empty($coupon_codes->discount_type)) {
                                $discount_type = explode(',', $coupon_codes->discount_type);
                                $coupon_datas[$index_c]['discount_type'] = $discount_type[0];
                            } else {
                                $coupon_datas[$index_c]['discount_type'] = '';
                            }

                            $index_c++;
                        }
                        $order[$index]->coupons = $coupon_datas;
                    } else {
                        $coupon_code = array();
                        $order[$index]->coupons = $coupon_code;
                    }

                    unset($data->coupon_code);

                    $orders_id = $data->orders_id;

                    $orders_status_history = DB::table('orders_status_history')
                                    ->LeftJoin('orders_status', 'orders_status.orders_status_id', '=', 'orders_status_history.orders_status_id')
                                    ->LeftJoin('orders_status_description', 'orders_status_description.orders_status_id', '=', 'orders_status_history.orders_status_id')
                                    ->select('orders_status_description.orders_status_name', 'orders_status.orders_status_id', 'orders_status_history.comments')
                                    ->where('orders_id', '=', $orders_id)->orderby('orders_status_history.orders_status_history_id', 'ASC')->get();

                    $order[$index]->orders_status_id = $orders_status_history[0]->orders_status_id;
                    $order[$index]->orders_status = $orders_status_history[0]->orders_status_name;
                    $order[$index]->customer_comments = $orders_status_history[0]->comments;

                    $total_comments = count($orders_status_history);
                    $i = 1;

                    foreach ($orders_status_history as $orders_status_history_data) {

                        if ($total_comments == $i && $i != 1) {
                            $order[$index]->orders_status_id = $orders_status_history_data->orders_status_id;
                            $order[$index]->orders_status = $orders_status_history_data->orders_status_name;
                            $order[$index]->admin_comments = $orders_status_history_data->comments;
                        } else {
                            $order[$index]->admin_comments = '';
                        }

                        $i++;
                    }

                    $orders_products = DB::table('orders_products')
                                    ->join('products', 'products.products_id', '=', 'orders_products.products_id')
                                    ->select('orders_products.*', 'products.products_image_url as image')
                                    ->where('orders_products.orders_id', '=', $orders_id)->get();
                    $k = 0;
                    $product = array();
                    foreach ($orders_products as $orders_products_data) {
                        $orders_products_data->products_price = Orders::convertprice($orders_products_data->products_price, $requested_currency);
                        $orders_products_data->final_price = Orders::convertprice($orders_products_data->final_price, $requested_currency);
                        //categories
                        $categories = DB::table('products_to_categories')
                                        ->leftjoin('categories', 'categories.categories_id', 'products_to_categories.categories_id')
                                        ->leftjoin('categories_description', 'categories_description.categories_id', 'products_to_categories.categories_id')
                                        ->select('categories.categories_id', 'categories_description.categories_name', 'categories.categories_image_url as categories_image', 'categories.categories_icon_url as categories_icon', 'categories.parent_id')
                                        ->where('products_id', '=', $orders_products_data->products_id)
                                        ->where('categories_description.language_id', '=', $language_id)->get();

                        $orders_products_data->categories = $categories;

                        $product_attribute = DB::table('orders_products_attributes')
                                ->where([
                                    ['orders_products_id', '=', $orders_products_data->orders_products_id],
                                    ['orders_id', '=', $orders_products_data->orders_id],
                                ])
                                ->get();

                        $orders_products_data->attributes = $product_attribute;
                        $product[$k] = $orders_products_data;
                        $k++;
                    }
                    $data->data = $product;
                    $orders_data[] = $data;
                    $index++;
                }
                return returnResponse("Returned all orders.", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $orders_data);
            } else {
                return returnResponse("No orders.", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS);
            }
        }
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
    }

    public static function getorderdetails($request) {

        //$customers_id =  $request->customers_id;

        $language_id = 1;
        $consumer_data = getallheaders();

        $consumer_data['consumer_ip'] = $request->ip();
        $consumer_data['consumer_url'] = __FUNCTION__;
        $authController = new AppSettingController();
        $authenticate = $authController->apiAuthenticate($consumer_data);

        if ($authenticate == 1) {
            $customers_id = auth()->user()->id;
            $order_id = null;
            if ($request->has('id')) {
                $order_id = $request->id;
            } else {
                // return
            }
            $requested_currency = $request->currency_code;
            $order = DB::table('orders')->where('customers_id', '=', $customers_id)->where('orders_id', '=', $order_id)->get();


            if (count($order) > 0) {
                //foreach
                $index = '0';
                foreach ($order as $data) {


                    if (!empty($data->product_discount_percentage)) {
                        $product_ids = explode(',', $coupons[0]->product_ids);
                        $data->product_ids = $product_ids;
                    } else {
                        $data->product_ids = array();
                    }

                    if (!empty($data->discount_type)) {
                        $exclude_product_ids = explode(',', $data->discount_type);
                        $data->discount_type = $exclude_product_ids;
                    } else {
                        $data->discount_type = array();
                    }

                    if (!empty($data->amount)) {
                        $product_categories = explode(',', $data[0]->amount);
                        $data->amount = $product_categories;
                    } else {
                        $data->amount = array();
                    }

                    if (!empty($data->product_ids)) {
                        $excluded_product_categories = explode(',', $data->product_ids);
                        $data->product_ids = $excluded_product_categories;
                    } else {
                        $data->product_ids = array();
                    }

                    if (!empty($data->exclude_product_ids)) {
                        $email_restrictions = explode(',', $data->exclude_product_ids);
                        $data->exclude_product_ids = $email_restrictions;
                    } else {
                        $data->exclude_product_ids = array();
                    }

                    if (!empty($data->usage_limit)) {
                        $used_by = explode(',', $data->usage_limit);
                        $data->usage_limit = $used_by;
                    } else {
                        $data->usage_limit = array();
                    }

                    if (!empty($data->product_categories)) {
                        $used_by = explode(',', $data->product_categories);
                        $data->product_categories = $used_by;
                    } else {
                        $data->product_categories = array();
                    }

                    if (!empty($data->excluded_product_categories)) {
                        $used_by = explode(',', $data->excluded_product_categories);
                        $data->excluded_product_categories = $used_by;
                    } else {
                        $data->excluded_product_categories = array();
                    }

                    if (!empty($data->coupon_code)) {

                        $coupon_code = $data->coupon_code;

                        $coupon_datas = array();
                        $index_c = 0;
                        foreach (json_decode($coupon_code) as $coupon_codes) {

                            if (!empty($coupon_codes->code)) {
                                $code = explode(',', $coupon_codes->code);
                                $coupon_datas[$index_c]['code'] = $code[0];
                            } else {
                                $coupon_datas[$index_c]['code'] = '';
                            }

                            if (!empty($coupon_codes->amount)) {
                                $amount = explode(',', $coupon_codes->amount);
                                $amount = Orders::convertprice($amount, $requested_currency);
                                $coupon_datas[$index_c]['amount'] = $amount[0];
                            } else {
                                $coupon_datas[$index_c]['amount'] = '';
                            }


                            if (!empty($coupon_codes->discount_type)) {
                                $discount_type = explode(',', $coupon_codes->discount_type);
                                $coupon_datas[$index_c]['discount_type'] = $discount_type[0];
                            } else {
                                $coupon_datas[$index_c]['discount_type'] = '';
                            }

                            $index_c++;
                        }
                        $order[$index]->coupons = $coupon_datas;
                    } else {
                        $coupon_code = array();
                        $order[$index]->coupons = $coupon_code;
                    }

                    unset($data->coupon_code);

                    $orders_id = $data->orders_id;

                    $orders_status_history = DB::table('orders_status_history')
                                    ->LeftJoin('orders_status', 'orders_status.orders_status_id', '=', 'orders_status_history.orders_status_id')
                                    ->LeftJoin('orders_status_description', 'orders_status_description.orders_status_id', '=', 'orders_status_history.orders_status_id')
                                    ->select('orders_status_description.orders_status_name', 'orders_status.orders_status_id', 'orders_status_history.comments')
                                    ->where('orders_id', '=', $orders_id)->orderby('orders_status_history.orders_status_history_id', 'ASC')->get();

                    $order[$index]->orders_status_id = $orders_status_history[0]->orders_status_id;
                    $order[$index]->orders_status = $orders_status_history[0]->orders_status_name;
                    $order[$index]->customer_comments = $orders_status_history[0]->comments;

                    $total_comments = count($orders_status_history);
                    $i = 1;

                    foreach ($orders_status_history as $orders_status_history_data) {

                        if ($total_comments == $i && $i != 1) {
                            $order[$index]->orders_status_id = $orders_status_history_data->orders_status_id;
                            $order[$index]->orders_status = $orders_status_history_data->orders_status_name;
                            $order[$index]->admin_comments = $orders_status_history_data->comments;
                        } else {
                            $order[$index]->admin_comments = '';
                        }

                        $i++;
                    }

                    $orders_products = DB::table('orders_products')
                                    ->join('products', 'products.products_id', '=', 'orders_products.products_id')
                                    ->select('orders_products.*', 'products.products_image_url as image')
                                    ->where('orders_products.orders_id', '=', $orders_id)->get();
                    $k = 0;
                    $product = array();
                    foreach ($orders_products as $orders_products_data) {
                        $orders_products_data->products_price = Orders::convertprice($orders_products_data->products_price, $requested_currency);
                        $orders_products_data->final_price = Orders::convertprice($orders_products_data->final_price, $requested_currency);
                        //categories
                        $categories = DB::table('products_to_categories')
                                        ->leftjoin('categories', 'categories.categories_id', 'products_to_categories.categories_id')
                                        ->leftjoin('categories_description', 'categories_description.categories_id', 'products_to_categories.categories_id')
                                        ->select('categories.categories_id', 'categories_description.categories_name', 'categories.categories_image', 'categories.categories_icon', 'categories.parent_id')
                                        ->where('products_id', '=', $orders_products_data->products_id)
                                        ->where('categories_description.language_id', '=', $language_id)->get();

                        $orders_products_data->categories = $categories;

                        $product_attribute = DB::table('orders_products_attributes')
                                ->where([
                                    ['orders_products_id', '=', $orders_products_data->orders_products_id],
                                    ['orders_id', '=', $orders_products_data->orders_id],
                                ])
                                ->get();

                        $orders_products_data->attributes = $product_attribute;
                        $product[$k] = $orders_products_data;
                        $k++;
                    }
                    $current_date = Carbon::now()->format('Y-m-d');
                    $order_date = Carbon::parse($data->date_purchased)->addDay()->format('Y-m-d');
                    $data->data = $product;
                    $data->can_cancel_order = 'N';
                    if($current_date <= $order_date){
                        $data->can_cancel_order = 'Y';
                    }
                    $orders_data[] = $data;
                    $index++;
                }
                return returnResponse("Order details found", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $orders_data);
            } else {
                return returnResponse("No data found", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS);
            }
        } else {
            return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
        }
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
    }

    // Cancel Order
    public static function cancelOrder($request) {

        Log::debug(__CLASS__ . "::" . __FUNCTION__ . " called");
        $consumer_data = getallheaders();
        $consumer_data['consumer_ip'] = $request->ip();
        $consumer_data['consumer_url'] = __FUNCTION__;
        $authController = new AppSettingController();
        $authenticate = $authController->apiAuthenticate($consumer_data);

        $validator = Validator::make($request->all(), [
                    'order_id' => 'required',
                    'reason' => 'required',
        ]);

        if ($validator->fails()) {
            Log::error(__CLASS__ . "::" . __FUNCTION__ . " Validation failed !");
            return returnResponse($validator->errors(), HttpStatus::HTTP_UNPROCESSABLE_ENTITY);
        }
        $order_id = $request->order_id;
        $reason = $request->reason;
        Log::debug(__CLASS__ . "::" . __FUNCTION__ . " Order Id : $order_id");
        Log::debug(__CLASS__ . "::" . __FUNCTION__ . "started with Reason : $reason");
        if ($authenticate == 1 && auth()->user()->id) {
            Log::debug(__CLASS__ . "::" . __FUNCTION__ . " Authenticated");
            $cust_info = DB::table('users')->where('id', auth()->user()->id)->where('status', '1')->first();
            Log::debug('Cust Info >>');
            if (isset($cust_info->id)) {

                try {
                    DB::beginTransaction();
                    $date_added = date('Y-m-d h:i:s');
                    $data = DB::table('orders')
                            ->where('orders_id', $order_id)
                            ->where('customers_id', auth()->user()->id)
                            ->first();

                    if (isset($data->orders_id)) {
                        if ($data->status == "ORDERED") {
                            $status = DB::table('orders_status')->LeftJoin('orders_status_description', 'orders_status_description.orders_status_id', '=', 'orders_status.orders_status_id')
                                            ->where('orders_status_description.language_id', '=', 1)->where('role_id', '<=', 2)->where('orders_status_description.orders_status_id', '=', '3')->first();

                            //orders status history
                            $orders_history_id = DB::table('orders_status_history')->insertGetId(
                                    ['orders_id' => $data->orders_id,
                                        'orders_status_id' => '3',
                                        'date_added' => $date_added,
                                        'customer_notified' => '1',
                                        'comments' => 'Cancel by request : ' . $reason,
                            ]);
                            Log::debug(__CLASS__ . "::" . __FUNCTION__ . " Order History Insert Id : $orders_history_id");
                            $reverseStock = self::reverseStock($data->orders_id);
                            if (!$reverseStock) {
                                Log::debug(__CLASS__ . "::" . __FUNCTION__ . " Reverse Stock updating failed");
                                return returnResponse("Error at updating order quantity please try again.");
                            }
                            $updateStatus = DB::table('orders')->where('orders_id', '=', $data->orders_id)
                                            ->where('customers_id', '=', auth()->user()->id)->update(['status' => $status->orders_status_name, 'updated_by' => auth()->user()->id, 'updated_at' => $date_added]);

                            if (!$updateStatus) {
                                Log::error(__CLASS__ . "::" . __FUNCTION__ . " Error at updating order status please try again");
                                return returnResponse("Error at updating order status please try again.");
                            }

                            if ($data->payment_method == "Razorpay" && $data->payment_status == "SUCCESS" && $data->pgateway_amount > 0) {
                                $balance_after = $cust_info->wallet_balance + $data->pgateway_amount;
                                $order_type = "PRODUCT ORDER";
                                $txn_desc = "Credit By Cancel Order By Self";
                                if (!WalletModel::creditInMainWallet(auth()->user()->id, $data->pgateway_amount, $balance_after, $txn_desc, $data->order_id, $order_type)) {
                                    Log::error("error while credit in Wallet !!!");
                                    return returnResponse("Payment updating failed ! Wallet credit failed");
                                }
                            }
                            if ($data->wallet_amount > 0 && $data->wallet_amount <= $data->net_amount) {
                                $balance_after = $cust_info->wallet_balance + $data->wallet_amount;
                                $order_type = "PRODUCT ORDER";
                                $txn_desc = "Credit By Cancel Order By Self";
                                if (!WalletModel::creditInMainWallet(auth()->user()->id, $data->wallet_amount, $balance_after, $txn_desc, $data->order_id, $order_type)) {
                                    Log::error("error while credit in Wallet !!!");
                                    return returnResponse("Payment updating failed ! Wallet credit failed");
                                }
                            }
                            $wallet_balance_block = $cust_info->wallet_block;
                            Log::debug(__CLASS__ . " :: " . __FUNCTION__ . " wallet_balance_block amount found as $wallet_balance_block");
                            $wallet_balance_block_after = $wallet_balance_block + $data->pgateway_amount + $data->wallet_amount;
                            Log::debug(__CLASS__ . " :: " . __FUNCTION__ . " wallet_balance_block amount updated as $wallet_balance_block_after");
                            $update_block_amount = DB::table('customers')->where('id', $cust_info->id)->update(['wallet_block' => $wallet_balance_block_after]);
                            if (!$update_block_amount) {
                                Log::error(__CLASS__ . " :: " . __FUNCTION__ . " error while updating the balance after refund! ");
                                return returnResponse("Payment updating failed !");
                            }

                            if ($data->op_disc_amount > 0 && $data->pgateway_amount > 0) {
                                $balance_after = $cust_info->wallet_balance + $data->op_disc_amount;
                                $order_type = "PRODUCT ORDER";
                                $txn_desc = "Credit By Cancel Order By Self";
                                if (!WalletModel::creditInMainWallet(auth()->user()->id, $data->op_disc_amount, $balance_after, $txn_desc, $data->order_id, $order_type)) {
                                    Log::error("error while credit in Advance Wallet !!!");
                                    return returnResponse("Payment error updating failed ! Advance Wallet credit failed");
                                }
                            }

                            DB::commit();
                            return returnResponse("Order Cancelled successfully.", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS);
                        } else {
                            Log::error(__CLASS__ . "::" . __FUNCTION__ . " Order can't be cancelled.");
                            return returnResponse("Order can't be cancelled.");
                        }
                    } else {
                        Log::error(__CLASS__ . "::" . __FUNCTION__ . " Order Details not found.");
                        return returnResponse("Order Details not found.");
                    }
                } catch (\Exception $exc) {
                    Log::error(__CLASS__ . "::" . __FUNCTION__ . " Exception : " . $exc->getMessage());
                    return returnResponse("Oops Error occured please try again !");
                }
            }
        }
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
    }

    //Reverse stock   
    public static function reverseStock($order_id) {
        Log::debug(__CLASS__ . "::" . __FUNCTION__ . " started with order id $order_id");
        $orders_products = DB::table('orders_products')->where('orders_id', '=', $order_id)->get();
        Log::debug(__CLASS__ . "::" . __FUNCTION__ . " orders_products Count = " . count($orders_products));
        if (count($orders_products) > 0) {
            foreach ($orders_products as $products_data) {
                Log::debug(__CLASS__ . "::" . __FUNCTION__ . " Product Id : " . $products_data->products_id);
                $product_detail = DB::table('products')->where('products_id', $products_data->products_id)->first();
                Log::debug(__CLASS__ . "::" . __FUNCTION__ . " Product Detail ");
                Log::debug(__CLASS__ . "::" . __FUNCTION__ . " Product Detail Id = " . $product_detail->products_id);
                if (!empty($product_detail->products_id)) {
                    Log::debug(__CLASS__ . "::" . __FUNCTION__ . " Product Type = " . $product_detail->products_type);
                    $date_added = date('Y-m-d h:i:s');
                    $inventory_ref_id = DB::table('inventory')->insertGetId([
                        'products_id' => $products_data->products_id,
                        'stock' => $products_data->products_quantity,
                        'admin_id' => auth()->user()->id,
                        'created_at' => $date_added,
                        'stock_type' => 'in',
                    ]);
                    if (!$inventory_ref_id) {
                        Log::error(__CLASS__ . "::" . __FUNCTION__ . " !inventory_ref_id");
                        return false;
                    }
                    //dd($product_detail);
                    if ($product_detail->products_type == 1) {
                        $product_attribute = DB::table('orders_products_attributes')
                                ->where([
                                    ['orders_products_id', '=', $products_data->orders_products_id],
                                    ['orders_id', '=', $products_data->orders_id],
                                ])
                                ->get();
                        Log::debug(__CLASS__ . "::" . __FUNCTION__ . " Products Attributes Count = " . count($product_attribute));
                        if (count($product_attribute) > 0) {
                            foreach ($product_attribute as $attribute) {
                                Log::debug(__CLASS__ . " :: " . __FUNCTION__ . " Options : " . $attribute->products_options);
                                Log::debug(__CLASS__ . " :: " . __FUNCTION__ . " Options Values : " . $attribute->products_options_values);
                                $prodocuts_attributes = DB::table('products_attributes')
                                        ->join('products_options_descriptions', 'products_options_descriptions.products_options_id', '=', 'products_attributes.options_id')
                                        ->join('products_options_values_descriptions', 'products_options_values_descriptions.products_options_values_id', '=', 'options_values_id')
                                        ->where('products_attributes.products_id', $attribute->products_id)
                                        ->where('products_options_values_descriptions.options_values', $attribute->products_options_values)
                                        ->where('products_attributes.options_id', $attribute->products_options)
                                        ->select('products_attributes.products_attributes_id')
                                        ->first();

                                if (isset($prodocuts_attributes->products_attributes_id)) {
                                    $stockupdate = DB::table('inventory_detail')->insert([
                                        'inventory_ref_id' => $inventory_ref_id,
                                        'products_id' => $products_data->products_id,
                                        'attribute_id' => $prodocuts_attributes->products_attributes_id,
                                    ]);
                                    if (!$inventory_ref_id) {
                                        Log::error(__CLASS__ . "::" . __FUNCTION__ . "Failed to update stock at product $products_data->orders_products_id attribute option $attribute->products_options attribute value $attribute->products_options_values");
                                        return false;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return true;
    }

    public static function updatestatus($request) {

        $orders_id = $request->orders_id;
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

            $date_added = date('Y-m-d h:i:s');
            $comments = '';
            $orders_history_id = DB::table('orders_status_history')->insertGetId(
                    ['orders_id' => $orders_id,
                        'orders_status_id' => '3',
                        'date_added' => $date_added,
                        'customer_notified' => '1',
                        'comments' => $comments
            ]);

            $responseData = array('success' => '1', 'data' => array(), 'message' => "Status has been changed succefully.");
        } else {
            $responseData = array('success' => '0', 'data' => array(), 'message' => "Unauthenticated call.");
        }
        $orderResponse = json_encode($responseData);

        return $orderResponse;
    }

    public static function updateOrderStatus($customer_id, $user_id, $order_id, $status, $payment_status) {

        return DB::table('orders')->where('customers_id', '=', $customer_id)->where('payment_status', '=', 'PENDING')->where('status', '=', 'PENDING')->where('order_id', '=', $order_id)->update(
                        ['status' => $status,
                            'payment_status' => $payment_status,
                            'updated_by' => $user_id
        ]);
    }

    public static function get_client_ip_env() {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if (getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if (getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if (getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if (getenv('HTTP_FORWARDED'))
            $ipaddress = getenv('HTTP_FORWARDED');
        else if (getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';

        return $ipaddress;
    }

################################################################################################################################################################################################################################
################################################################################################################################################################################################################################
##########################@##
# New place order api 6/7/2021
############################        

    public static function placeOrder($request) {
        $consumer_data = getallheaders();
        
        $consumer_data['consumer_ip'] = $request->ip();
        $consumer_data['consumer_url'] = __FUNCTION__;

        $authController = new AppSettingController();
        $authenticate = $authController->apiAuthenticate($consumer_data);
        $ipAddress = Orders::get_client_ip_env();

        if ($authenticate == 1 && auth()->user()->id) {
            Log::debug('Total Price : ' . $request->total_mrp);
            $cust_info = DB::table('customers')->where('id', auth()->user()->id)->where('status', '1')->first();
            Log::debug('Cust Info >>');
            try {
                if (isset($cust_info->id)) {
                    Log::debug($request->products);
                    DB::beginTransaction();
                    $produstsArray = json_decode($request->products, true);
                    Log::debug($produstsArray);
                    foreach ($produstsArray as $product) {
                        $products = json_decode($product, true);
                        $req = array();
                        $req['products_id'] = $products['products_id'];
                        $attributes_data = '';
                        if (isset($products['attributes']) && count($products['attributes']) > 0) {
                            $req['attributes'] = $products['attributes'];
                            $attributes_data = array();
                            for ($j = 0; $j < count($products['attributes']); $j++) {
                                array_push($attributes_data, $products['attributes'][$j]["products_options_values"]["products_attributes_id"]);
                            }
                        }
                        //checking quantity
                        $check = Product::getquantity($req, $products['products_id'], $attributes_data);
                        //$check = json_decode($check, true);
                        if ($products['customers_basket_quantity'] > $check['stock']) {
                            Log::error(__CLASS__ . "::" . __FUNCTION__ . " Product id " . $products['products_id'] . " is out of stock");
                            return returnResponse(" Product " . $products['products_name'] . " is out of stock");
                        }

                        //checking if data exists in cart or not
                        $checkCart = CartModel::where('customers_basket_id', $products['cart_id'])->where('customers_id', auth()->user()->id)->where('is_order', 0)->first();
                        if (!isset($checkCart->customers_basket_id)) {
                            return returnResponse(" Product " . $products['products_name'] . " is not in cart");
                        }

                        //Checking price and matching them
                        $isFlash = DB::table('flash_sale')->where('products_id', $products['products_id'])
                                ->where('flash_expires_date', '>=', time())->where('flash_status', '=', 1)
                                ->get();
                        //get products detail  is not default
                        if (!empty($isFlash) and count($isFlash) > 0) {
                            $type = "flashsale";
                        } else {
                            $type = "";
                        }

                        $detail = Product::productDetail($products['products_id']);
                        $result['detail'] = $detail;
                        $cart_final_price = $products['final_price'];
                        $server_final_price = 0;


                        if (!empty($result['detail']['product_data'][0]->flash_price)) {
                            $server_final_price = $result['detail']['product_data'][0]->flash_price + 0;
                        } elseif (!empty($result['detail']['product_data'][0]->discount_price)) {
                            $server_final_price = $result['detail']['product_data'][0]->discount_price + 0;
                        } else {
                            $server_final_price = $result['detail']['product_data'][0]->products_price + 0;
                        }
                        $productType = $result['detail']['product_data'][0]->products_type;
                        //$variables_prices = 0
                        if ($productType == 1) {
                            $attributeidArray = $products['attributes'];

                            Log::debug(__CLASS__ . "::" . __FUNCTION__ . " Variable product with attributes " . json_encode($attributeidArray));
                            $attribute_price = 0;
                            if (!empty($attributeidArray) and count($attributeidArray) > 0) {
                                Log::debug(__CLASS__ . "::" . __FUNCTION__ . " Checking attributes " . json_encode($attributeidArray));

                                for ($j = 0; $j < count($attributeidArray); $j++) {
                                    $attributeid = $attributeidArray[$j]["products_options_values"]["products_attributes_id"];
                                    $attribute = DB::table('products_attributes')->where('products_attributes_id', $attributeid)->first();
                                    Log::debug(__CLASS__ . "::" . __FUNCTION__ . " getting attribute data as " . json_encode($attributeid));
                                    $symbol = $attribute->price_prefix;
                                    $values_price = $attribute->options_values_price;
                                    if ($symbol == '+') {
                                        $server_final_price = intval($server_final_price) + intval($values_price);
                                    }
                                    if ($symbol == '-') {
                                        $server_final_price = intval($server_final_price) - intval($values_price);
                                    }
                                }
                            }
                        }

                        if($cart_final_price != $server_final_price){
                        Log::error(__CLASS__ . "::" . __FUNCTION__ . " Product id " . $products['products_id'] . " price miss match cart final price $cart_final_price and server price $server_final_price");
                        return returnResponse("Product ".$result['detail']['product_data'][0]->products_name." Price Has been updated Please try again !");
                        }
                    }

                    //$guest_status             = $request->guest_status;
                    $address_book_id = $request->address_id;
                    Log::debug('Payment Method : ' . $request->payment_method);
                    $addresses = DB::table('address_book')->where('address_book_id', $address_book_id)
                            ->first();

                    $customers_id = auth()->user()->id;

                    $date_added = date('Y-m-d h:i:s');
                    $customers_telephone = auth()->user()->phone;
                    $email = auth()->user()->email;
                    $name = $addresses->entry_firstname;
                    $mobile_no = auth()->user()->phone;
                    Log::debug("Name : $name, Mobile : $mobile_no");
                    $delivery_firstname = $addresses->entry_firstname;
                    $delivery_lastname = $addresses->entry_lastname;
                    $delivery_street_address = $addresses->entry_street_address;
                    $delivery_suburb = $addresses->entry_suburb;
                    $delivery_city = $addresses->entry_city;
                    $delivery_postcode = $addresses->entry_postcode;
                    $delivery_state = $addresses->entry_state;
                    $delivery_country = 'INDIA';


                    $billing_firstname = $addresses->entry_firstname; //$request->entry_firstname;
                    $billing_lastname = $addresses->entry_lastname; //$request->entry_lastname;
                    $billing_street_address = $addresses->entry_street_address; //$request->entry_street_address;
                    $billing_suburb = $addresses->entry_suburb; //$request->entry_suburb;
                    $billing_city = $addresses->entry_city;
                    $billing_postcode = $addresses->entry_postcode;
                    $billing_state = $addresses->entry_state;
                    $billing_country = 'INDIA'; //$request->countries_name;

                    $payment_method = $request->payment_method;
                    $platform = $request->platform;


                    $order_information = array();

                    $cc_type = '';
                    $cc_owner = '';
                    $cc_number = '';
                    $cc_expires = '';
                    $last_modified = date('Y-m-d H:i:s');
                    $date_purchased = date('Y-m-d H:i:s');
                    $order_price = $request->total_amount;
                    $total_mrp = $request->total_mrp;
                    $op_disc_type = $request->op_disc_type;
                    $op_disc_val = $request->op_disc_val;
                    if (empty($op_disc_val)) {
                        $op_disc_val = 0;
                    }
                    $op_disc_amount = $request->op_disc_amount;
                    if (empty($op_disc_val)) {
                        $op_disc_amount = 0;
                    }
                    $currency_code = 'INR';
                    $shipping_cost = 0;
                    $wallet_amount = $request->wallet_amount;
                    $pgateway_amount = $request->pgateway_amount;
                    $cod_amount = $request->cod_amount;
                    if (empty($wallet_amount)) {
                        $wallet_amount = 0;
                    }
                    if (empty($pgateway_amount)) {
                        $pgateway_amount = 0;
                    }
                    if ($cod_amount == null) {
                        Log::debug(__CLASS__ . "::" . __FUNCTION__ . " under cod is null");
                    }
                    if ($cod_amount == '' || $cod_amount == null) {
                        Log::debug(__CLASS__ . "::" . __FUNCTION__ . " under cod is or with null");
                    }
                    if ($cod_amount == 'null') {
                        $cod_amount = 0;
                    }
                    if ($cod_amount == '') {
                        $cod_amount = 0;
                    }
                    if ($pgateway_amount == 'null') {
                        $pgateway_amount = 0;
                    }
                    if ($pgateway_amount == '') {
                        $pgateway_amount = 0;
                    }
                    if ($wallet_amount == 'null') {
                        $wallet_amount = 0;
                    }
                    if ($wallet_amount == '') {
                        $wallet_amount = 0;
                    }
                    $total_quantity = $request->total_quantity;
                    $net_amount = $request->net_amount;

                    $orders_status = '5';
                    $orders_date_finished = $request->orders_date_finished;
                    $comments = 'New Order Recieved';

                    //additional fields
                    $delivery_phone = auth()->user()->phone;
                    $billing_phone = auth()->user()->phone;

                    $settings = $authController->getSetting();
                    $currency_value = '1';

                    //tax info
                    $total_tax = $request->total_tax;

                    $products_tax = 1;
                    //check and validated shipping charge


                    if (!empty($settings['shipping_charge']) && $settings['min_order_amount_for_shipping_free'] >= $order_price) {
                        $shipping_cost = $settings['shipping_charge'];
                    }



                    //online payment discount check and validate
                    //coupon info
                    $is_coupon_applied = $request->is_coupon_applied;

                    if ($is_coupon_applied == 1) {

                        $code = array();
                        $coupon_amount = 0;
                        $exclude_product_ids = array();
                        $product_categories = array();
                        $excluded_product_categories = array();
                        $exclude_product_ids = array();

                        $coupon_amount = $request->coupon_amount;

                        //convert price to default currency price
                        $coupon_amount = Orders::converttodefaultprice($coupon_amount, $currency_code);

                        foreach ($request->coupons as $coupons_data) {

                            //update coupans
                            $coupon_id = DB::statement("UPDATE `coupons` SET `used_by`= CONCAT(used_by,',$customers_id') WHERE `code` = '" . $coupons_data['code'] . "'");
                        }
                        $code = json_encode($request->coupons);
                    } else {
                        $code = '';
                        $coupon_amount = 0;
                    }
                    $shipping_method = "Shiprocket";
                    $status = 'PENDING';
                    //payment methods
                    Log::debug('payment method ' . $payment_method);
                    $paymentMethodName = '';
                    if ($payment_method == 'cod') {
                        Log::debug('in cod' . $payment_method);
                        $payments_setting = Orders::payments_setting_for_cod($request);
                        $paymentMethodName = 'Cash on Delivery';
                        $payment_method = 'Cash on Delivery';
                        $payment_status = 'PENDING';
                        $status = 'ORDERED';
                        $pgateway_amount = 0;
                    } else if ($payment_method == 'razorpay') {
                        Log::debug('in razror pay' . $payment_method);
                        $payments_setting = Orders::payments_setting_for_razorpay($request);
                        Log::debug('in razror pay settings' . json_encode($payments_setting));
                        $paymentMethodName = 'Razor Pay';
                        $payment_status = 'PENDING';
                        $status = 'PENDING';
                        $orders_status = '1';
                        if ($pgateway_amount < 1) {
                            Log::error("Error Occured please try again ! Payment Gateway Amount is less than 1. i.e. $pgateway_amount");
                            return returnResponse("Error Occured please try again ! Payment is less than 1.");
                        }
                    }
                    else if ($payment_method == 'cash_free') {
                        Log::debug('in Cash free' . $payment_method);
                        $payments_setting = Orders::payments_setting_for_cashfree($request);
                        Log::debug('in Cash Free settings' . json_encode($payments_setting));
                        $paymentMethodName = 'Cash Free';
                        $payment_status = 'PENDING';
                        $status = 'PENDING';
                        $orders_status = '1';
                        if ($pgateway_amount < 1) {
                            Log::error("Error Occured please try again ! Payment Gateway Amount is less than 1. i.e. $pgateway_amount");
                            return returnResponse("Error Occured please try again ! Payment is less than 1.");
                        }
                    }
                    else if ($payment_method == 'wallet') {
                        Log::debug('in Wallet ' . $payment_method);
                        $payments_setting = Orders::payments_setting_for_wallet($request);
                        Log::debug('in Wallet settings' . json_encode($payments_setting));
                        $paymentMethodName = 'WALLET';
                        if ($wallet_amount == $net_amount) {
                            $payment_status = 'SUCCESS';
                            $status = 'ORDERED';
                        } else {
                            $payment_status = 'PENDING';
                            $status = 'PENDING';
                            $orders_status = '1';
                        }
                    } else {
                        Log::error("Unknown payment method i.e. $payment_method ");
                        return returnResponse("Error Occured please try again ! Unknown payment method.");
                    }

                    $order_id = generateOrderId();
                    if (empty($paymentMethodName)) {
                        //$paymentMethodName = "Razorpay";
                        return returnResponse("Error Occured please try again ! Unknown payment method.");
                    }
                    Log::debug("Order Id : $order_id");
                    if (empty($order_id)) {
                        Log::error("Order Id Not Generated ");
                        return returnResponse("Error Occured please try again!");
                    }
                    Log::debug("Order Id : $order_id");


                    if ($wallet_amount > 0) {
                        if ($cust_info->wallet_balance >= $wallet_amount) {
                            $payable_amount = $net_amount - $wallet_amount;
                            Log::debug("Payable Amount $payable_amount ");
                            Log::debug("Form Wallet Amount $wallet_amount ");
                            $balance_after = $cust_info->wallet_balance - $wallet_amount;
                            $order_type = "PRODUCT ORDER";
                            $txn_desc = "Product Order";
                            if ($balance_after >= 0) {
                                if (!WalletModel::debitFromMainWallet($customers_id, $wallet_amount, $balance_after, $txn_desc, $order_id, $order_type)) {
                                    Log::error("error while debiting Wallet !!!");
                                    return returnResponse("Order failed ! Wallet debiting failed");
                                }

                                $wallet_balance_block = $cust_info->wallet_block;
                                Log::debug(__CLASS__ . " :: " . __FUNCTION__ . " wallet_balance_block amount found as $wallet_balance_block");
                                $wallet_balance_block_after = $wallet_balance_block - $wallet_amount;
                                if ($wallet_balance_block_after < 0) {
                                    $wallet_balance_block_after = 0;
                                }
                                Log::debug(__CLASS__ . " :: " . __FUNCTION__ . " wallet_balance_block amount updated as $wallet_balance_block_after");
                                $update_block_amount = DB::table('users')->where('id', $cust_info->id)->update(['wallet_balance_block' => $wallet_balance_block_after]);
                                if (!$update_block_amount) {
                                    Log::error(__CLASS__ . " :: " . __FUNCTION__ . " error while updating the block wallet balance! ");
                                    return returnResponse("Payment updating failed !");
                                }
                            } else {
                                return returnResponse("MWallet balance is low");
                            }
                        } else {
                            Log::error("Wallet balance " . $cust_info->wallet_balance . " is less than wallet $wallet_amount used !!");
                            return returnResponse("Wallet can be used up to " . $cust_info->wallet_balance);
                        }
                    }

                    // decrementing advance wallet
                    if ($op_disc_amount > 0) {
                        if ($cust_info->advance_wallet > 0) {
                            Log::debug("Form advance Wallet Amount $op_disc_amount ");
                            if ($cust_info->advance_wallet >= $op_disc_amount) {
                                $balance_after = $cust_info->advance_wallet - $op_disc_amount;
                            } else {
                                $balance_after = $cust_info->advance_wallet;
                            }

                            $order_type = "PRODUCT ORDER";
                            $txn_desc = "Product Order";
                            if ($balance_after >= 0) {
                                if (!WalletModel::debitFromAdvanceWallet($customers_id, $op_disc_amount, $balance_after, $txn_desc, $order_id, $order_type)) {
                                    Log::error("error while debiting Wallet !!!");
                                    return returnResponse("Order failed ! Wallet debiting failed");
                                }
                            } else {
                                return returnResponse("AdvanceWallet balance is low");
                            }
                        } else {
                            Log::error(" Advance Wallet balance " . $cust_info->advance_wallet . " is less !!");
                            return returnResponse("Wallet can be used up to " . $cust_info->advance_wallet);
                        }
                    }


                    //insert order
                    $orders_id = DB::table('orders')->insertGetId(
                            ['customers_id' => $customers_id,
                                'customers_name' => $delivery_firstname . ' ' . $delivery_lastname,
                                'customers_street_address' => $delivery_street_address,
                                'customers_suburb' => $delivery_suburb,
                                'customers_city' => $delivery_city,
                                'customers_postcode' => $delivery_postcode,
                                'customers_state' => $delivery_state,
                                'customers_country' => $delivery_country,
                                'customers_telephone' => $customers_telephone,
                                'email' => $email,
                                'delivery_name' => $delivery_firstname . ' ' . $delivery_lastname,
                                'delivery_street_address' => $delivery_street_address,
                                'delivery_suburb' => $delivery_suburb,
                                'delivery_city' => $delivery_city,
                                'delivery_postcode' => $delivery_postcode,
                                'delivery_state' => $delivery_state,
                                'delivery_country' => $delivery_country,
                                'billing_name' => $billing_firstname . ' ' . $billing_lastname,
                                'billing_street_address' => $billing_street_address,
                                'billing_suburb' => $billing_suburb,
                                'billing_city' => $billing_city,
                                'billing_postcode' => $billing_postcode,
                                'billing_state' => $billing_state,
                                'billing_country' => $billing_country,
                                'payment_method' => $paymentMethodName,
                                'cc_type' => $cc_type,
                                'cc_owner' => $cc_owner,
                                'cc_number' => $cc_number,
                                'cc_expires' => $cc_expires,
                                'last_modified' => $last_modified,
                                'date_purchased' => $date_purchased,
                                'total_mrp' => $total_mrp,
                                'order_price' => $order_price,
                                'op_disc_type' => $op_disc_type,
                                'op_disc_value' => $op_disc_val,
                                'op_disc_amount' => $op_disc_amount,
                                'wallet_amount' => $wallet_amount,
                                'pgateway_amount' => $pgateway_amount,
                                'cod_amount' => $cod_amount,
                                'total_quantity' => $total_quantity,
                                'net_amount' => $net_amount,
                                'shipping_cost' => $shipping_cost,
                                'shipping_method' => $shipping_method,
                                'currency' => $currency_code,
                                'currency_value' => $currency_value,
                                'order_information' => json_encode($order_information),
                                'coupon_code' => $code,
                                'coupon_amount' => $coupon_amount,
                                'total_tax' => $total_tax,
                                'ordered_source' => '2', // App
                                'delivery_phone' => $delivery_phone,
                                'billing_phone' => $billing_phone,
                                'order_id' => $order_id,
                                'payment_status' => $payment_status,
                                'status' => $status,
                    ]);


                    //orders status history
                    $orders_history_id = DB::table('orders_status_history')->insertGetId(
                            ['orders_id' => $orders_id,
                                'orders_status_id' => $orders_status,
                                'date_added' => $date_added,
                                'customer_notified' => '1',
                                'comments' => $comments
                    ]);

                    foreach ($produstsArray as $product) {
                        $products = json_decode($product, true);
                        //dd($products['price'], $currency_code);
                        $c_price = str_replace(',', '', $products['price']);
                        $c_final_price = str_replace(',', '', $products['final_price']);
                        $price = $c_price;
                        $final_price = $c_final_price * $products['customers_basket_quantity'];


                        $orders_products_id = DB::table('orders_products')->insertGetId(
                                [
                                    'orders_id' => $orders_id,
                                    'products_id' => $products['products_id'],
                                    'products_name' => $products['products_name'],
                                    'products_price' => $price,
                                    'final_price' => $final_price,
                                    'products_tax' => $products_tax,
                                    'products_quantity' => $products['customers_basket_quantity'],
                        ]);

                        $inventory_ref_id = DB::table('inventory')->insertGetId([
                            'products_id' => $products['products_id'],
                            'reference_code' => '',
                            'stock' => $products['customers_basket_quantity'],
                            'admin_id' => 0,
                            'added_date' => time(),
                            'purchase_price' => 0,
                            'stock_type' => 'out',
                        ]);

                        //marking cart item as ordered
                        CartModel::where('customers_basket_id', $products['cart_id'])->where('customers_id', $customers_id)->update(['is_order' => '1']);


                        if (!empty($products['attributes'])) {
                            Log::debug('attributes' . json_encode($products['attributes']));
                            foreach ($products['attributes'] as $attribute) {
                                Log::debug('attributes foreach' . json_encode($attribute));
                                Log::debug('product option id' . $attribute['products_options']["id"]);
                                Log::debug('product option values id' . $attribute['products_options_values']["products_attributes_id"]);
                                DB::table('orders_products_attributes')->insert(
                                        [
                                            'orders_id' => $orders_id,
                                            'products_id' => $products['products_id'],
                                            'orders_products_id' => $orders_products_id,
                                            'products_options' => $attribute['products_options']["id"],
                                            'products_options_values' => $attribute['products_options_values']["value"],
                                            'products_options_values_name' => $attribute['products_options_values']["name"],
                                            'products_options_name' => $attribute['products_options']["name"],
                                            'options_values_price' => $attribute['products_options_values']['price'],
                                            'swatch_type' => $attribute['products_options']['swatch_type'],
                                            'price_prefix' => $attribute['products_options_values']['price_prefix']
                                ]);

                                $products_attributes = DB::table('products_attributes')->where([
                                            ['options_id', '=', $attribute['products_options']["id"]],
                                            ['products_attributes_id', '=', $attribute['products_options_values']["products_attributes_id"]],
                                        ])->get();

                                DB::table('inventory_detail')->insert([
                                    'inventory_ref_id' => $inventory_ref_id,
                                    'products_id' => $products['products_id'],
                                    'attribute_id' => $products_attributes[0]->products_attributes_id,
                                ]);
                            }
                        }
                    }


                    //send order email to user
                    $order = DB::table('orders')
                                    ->LeftJoin('orders_status_history', 'orders_status_history.orders_id', '=', 'orders.orders_id')
                                    ->LeftJoin('orders_status', 'orders_status.orders_status_id', '=', 'orders_status_history.orders_status_id')
                                    ->where('orders.orders_id', '=', $orders_id)->orderby('orders_status_history.date_added', 'DESC')->get();

                    //foreach
                    foreach ($order as $data) {
                        $orders_id = $data->orders_id;

                        $orders_products = DB::table('orders_products')
                                        ->join('products', 'products.products_id', '=', 'orders_products.products_id')
                                        ->select('orders_products.*', 'products.products_image as image')
                                        ->where('orders_products.orders_id', '=', $orders_id)->get();
                        $i = 0;
                        $total_price = 0;
                        $product = array();
                        $subtotal = 0;
                        foreach ($orders_products as $orders_products_data) {
                            $product_attribute = DB::table('orders_products_attributes')
                                    ->where([
                                        ['orders_products_id', '=', $orders_products_data->orders_products_id],
                                        ['orders_id', '=', $orders_products_data->orders_id],
                                    ])
                                    ->get();

                            $orders_products_data->attribute = $product_attribute;
                            $product[$i] = $orders_products_data;
                            //$total_tax	 = $total_tax+$orders_products_data->products_tax;
                            $total_price = $total_price + $orders_products[$i]->final_price;

                            $subtotal += $orders_products[$i]->final_price;

                            $i++;
                        }

                        $data->data = $product;
                        $orders_data[] = $data;
                    }

                    $orders_status_history = DB::table('orders_status_history')
                                    ->LeftJoin('orders_status', 'orders_status.orders_status_id', '=', 'orders_status_history.orders_status_id')
                                    ->orderBy('orders_status_history.date_added', 'desc')
                                    ->where('orders_id', '=', $orders_id)->get();

                    $orders_status = DB::table('orders_status')->get();

                    $ordersData['orders_data'] = $orders_data;
                    $ordersData['total_price'] = $total_price;
                    $ordersData['orders_status'] = $orders_status;
                    $ordersData['orders_status_history'] = $orders_status_history;
                    $ordersData['subtotal'] = $subtotal;
                    Log::debug(__CLASS__ . " :: " . __FUNCTION__ . " Wallet amount : $wallet_amount, cod amount : $cod_amount, P Gateway : $pgateway_amount , Op disc Amount : $op_disc_amount , Net Amount : $net_amount");
                    if ($wallet_amount > 0 && $pgateway_amount > 0 && $op_disc_amount > 0) {
                        if ($net_amount != ($wallet_amount + $pgateway_amount + $op_disc_amount)) {
                            Log::error(__CLASS__ . " :: " . __FUNCTION__ . " Order placing failed ! Wallet amount : $wallet_amount + P Gateway : $pgateway_amount + Op disc Amount : $op_disc_amount != Net Amount : $net_amount");
                            return returnResponse("Order failed !!!");
                        }
                    } else if ($pgateway_amount > 0 && $op_disc_amount > 0 && $pgateway_amount + $op_disc_amount != $net_amount) {
                        Log::error(__CLASS__ . " :: " . __FUNCTION__ . " Order placing failed ! P Gateway : $pgateway_amount + Op disc Amount : $op_disc_amount != Net Amount : $net_amount");
                        return returnResponse("Order failed !!!");
                    } else if ($wallet_amount > 0 && ($wallet_amount + $cod_amount) != $net_amount) {
                        Log::error(__CLASS__ . " :: " . __FUNCTION__ . " Order placing failed ! Wallet Amount : $wallet_amount + COD Amount : $cod_amount != Net Amount : $net_amount");
                        return returnResponse("Order failed !!!");
                    }
                    if ($payment_method == "razorpay") {
                        $txn_id = generateGatewayTxnId();
                        Log::debug(__CLASS__ . " :: " . __FUNCTION__ . " pgateway_amount = $pgateway_amount");
                        $payment_data = PaymentGatewayModel::initiatePayment($customers_id, $customers_id, $orders_id, $order_id, $txn_id, $name, $mobile_no, auth()->user()->email, $pgateway_amount, $platform);
                        if ($payment_data) {
                            if (isset($payment_data["order_id"])) {
                                $data = array(
                                    'razorpay_order_id' => $payment_data["order_id"],
                                    'order_id' => $order_id,
                                    'txn_id' => $txn_id,
                                );
                                DB::commit();
                                Log::info(__CLASS__ . " :: " . __FUNCTION__ . " Order placing success with razorpay order id " . $payment_data["order_id"]);
                                return returnResponse("Order has been placed successfully.", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $data);
                            } else {
                                Log::error(__CLASS__ . " :: " . __FUNCTION__ . " Order placing failed ! razorpay_order_id not set .");
                                return returnResponse("Order failed !!!");
                            }
                        } else {
                            Log::error(__CLASS__ . " :: " . __FUNCTION__ . " error while placing order !!!!!");
                            return returnResponse("Order failed !");
                        }
                    }
                    
                    if ($payment_method == "cash_free") {
                        $txn_id = generateGatewayTxnId();
                        Log::debug(__CLASS__ . " :: " . __FUNCTION__ . " pgateway_amount = $pgateway_amount");
                        $payment_data = PaymentGatewayModel::chashFreeInitiatePayment($customers_id, $customers_id, $orders_id, $order_id, $txn_id, $name, $mobile_no, auth()->user()->email, $pgateway_amount, $platform);
                        if ($payment_data) {
                            if (isset($payment_data["cftoken"])) {
                                $data = array(
                                    'cf_token' => $payment_data["cftoken"],
                                    'order_id' => $order_id,
                                    'txn_id' => $txn_id,
                                    'currency' => 'INR',
                                    'amount' => $pgateway_amount,
                                );
                                DB::commit();
                                Log::info(__CLASS__ . " :: " . __FUNCTION__ . " Order placing success with cash Free token " . $payment_data["cftoken"]);
                                return returnResponse("Order has been placed successfully.", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $data);
                            } else {
                                Log::error(__CLASS__ . " :: " . __FUNCTION__ . " Order placing failed ! cash Freetoken not set .");
                                return returnResponse("Order failed !!!");
                            }
                        } else {
                            Log::error(__CLASS__ . " :: " . __FUNCTION__ . " error while placing order !!!!!");
                            return returnResponse("Order failed !");
                        }
                    }



                    //notification/email
                    //$myVar = new AlertController();
                    //$alertSetting = $myVar->orderAlert($ordersData);
                    //$responseData = array('success'=>'1', 'data'=>array(), 'customer_id' => $customers_id,'message'=>"Order has been placed successfully.");
                    DB::commit();
                    return returnResponse("Order has been placed successfully.", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, array('order_id' => $order_id));
                } else {
                    return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
                }
            } catch (\Exception $e) {
                Log::error(__CLASS__ . "::" . __FUNCTION__ . " Exception Occured " . $e->getMessage());
                return returnResponse("Order failed ! Please try again");
            }
        }
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
    }

}
