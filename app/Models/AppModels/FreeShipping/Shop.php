<?php

namespace App\Models\AppModels\FreeShipping;

use App\Http\Controllers\App\AlertController;
use App\Http\Controllers\App\AppSettingController;
use Auth;
use DB;
use Log;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\HttpStatus;
use Validator;
use Carbon\Carbon;
use Illuminate\Support\Str;
class Shop extends Model
{

    public static function getShopList($request)
    {
        $consumer_data = getallheaders();
        $consumer_data['consumer_ip'] = $request->ip();
        $consumer_data['consumer_url'] = __FUNCTION__;
        $authController = new AppSettingController();
        $authenticate = $authController->apiAuthenticate($consumer_data);
        if ($authenticate == 1) {
            if (auth()->user()->id) {
                try {
                   $data = DB::table('shops')->where('status', '=', 'ACTIVE')->get();
                   return returnResponse(HttpStatus::$text[HttpStatus::HTTP_OK], HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $data);
               }
                catch (Exception $exc) {
                    Log::error(__CLASS__."::".__FUNCTION__." Exception : ".$exc->getMessage());
                    return returnResponse(HttpStatus::$text[HttpStatus::HTTP_EXPECTATION_FAILED], HttpStatus::HTTP_ERROR);
                }
            } else {
                return returnResponse("Your account has been deactivated.", HttpStatus::HTTP_UNAUTHORIZED);
            }
        } 
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_ERROR);
    }
    
    public static function searchShop($request)
    {
        $consumer_data = getallheaders();
        $consumer_data['consumer_ip'] = $request->ip();
        $consumer_data['consumer_url'] = __FUNCTION__;
        $authController = new AppSettingController();
        $authenticate = $authController->apiAuthenticate($consumer_data);
        $validator = Validator::make($request->all(), [
            'term' => 'required|min:2',
        ]);
        
        if ($validator->fails()) {
            Log::error(__CLASS__."::".__FUNCTION__." Term is required !");
            return returnResponse(readRequiredField($validator->errors()), HttpStatus::HTTP_UNPROCESSABLE_ENTITY);
        }
        $term = $request->term;
        Log::debug(__CLASS__."::".__FUNCTION__." Term : $term");
        
        if ($authenticate == 1) {
            if (auth()->user()->id) {
                try {
                   $data = DB::table('shops')->where('shop_name', 'like', $term.'%')->where('status', '=', 'ACTIVE')->get();
                   return returnResponse("Search data found !", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $data);
                }
                catch (Exception $exc) {
                    Log::error(__CLASS__."::".__FUNCTION__." Exception : ".$exc->getMessage());
                    return returnResponse(HttpStatus::$text[HttpStatus::HTTP_EXPECTATION_FAILED], HttpStatus::HTTP_ERROR);
                }
            } else {
                return returnResponse("Your account has been deactivated.", HttpStatus::HTTP_UNAUTHORIZED);
            }
        } 
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_ERROR);
    }
    
    public static function validateQrCode($request)
    {
        $consumer_data = getallheaders();
        $consumer_data['consumer_ip'] = $request->ip();
        $consumer_data['consumer_url'] = __FUNCTION__;
        $authController = new AppSettingController();
        $authenticate = $authController->apiAuthenticate($consumer_data);
        $validator = Validator::make($request->all(), [
            'qr_code' => 'required|min:4',
        ]);
        
        if ($validator->fails()) {
            Log::error(__CLASS__."::".__FUNCTION__." QR Code is required !");
            return returnResponse(readRequiredField($validator->errors()), HttpStatus::HTTP_UNPROCESSABLE_ENTITY);
        }
        $shop_code = $request->qr_code;
        Log::debug(__CLASS__."::".__FUNCTION__." QR Code : $shop_code");
        
        if ($authenticate == 1) {
            if (auth()->user()->id) {
                try {
                   $data = DB::table('shops')->where('shop_code', '=', $shop_code)->where('status', '=', 'ACTIVE')->first();
                   if(isset($data->id)){
                       Log::debug(__CLASS__."::".__FUNCTION__." QR Code found !");
                       return returnResponse(HttpStatus::$text[HttpStatus::HTTP_OK], HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $data);
                   } else {
                       Log::error(__CLASS__."::".__FUNCTION__." QR Code not found !");
                   }
                   return returnResponse("QR Code not found !");
               }
                catch (Exception $exc) {
                    Log::error(__CLASS__."::".__FUNCTION__." Exception : ".$exc->getMessage());
                    return returnResponse(HttpStatus::$text[HttpStatus::HTTP_EXPECTATION_FAILED]);
                }
            } else {
                return returnResponse("Your account has been deactivated.", HttpStatus::HTTP_UNAUTHORIZED);
            }
        } 
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
    }
    
    public static function confirmQrPayment($request)
    {
        $consumer_data = getallheaders();
        $consumer_data['consumer_ip'] = $request->ip();
        $consumer_data['consumer_url'] = __FUNCTION__;
        $authController = new AppSettingController();
        $authenticate = $authController->apiAuthenticate($consumer_data);
        $validator = Validator::make($request->all(), [
            'qr_code' => 'required|min:4',
            'shop_id' => 'required|min:1',
            'amount' => 'required|min:1',
            'description' => 'nullable',
        ]);
        
        if ($validator->fails()) {
            Log::error(__CLASS__."::".__FUNCTION__." QR Code is required !");
            return returnResponse(readRequiredField($validator->errors()), HttpStatus::HTTP_UNPROCESSABLE_ENTITY);
        }
        $shop_code = $request->qr_code;
        $shop_id = $request->shop_id;
        $amount = $request->amount;
        $description = $request->description;
        Log::debug(__CLASS__."::".__FUNCTION__." Parameter received as");
        Log::debug(__CLASS__."::".__FUNCTION__." QR Code : $shop_code, Shop Id : $shop_id, Amount : $amount, Desc : $description");
        if($amount < 1){
            Log::error(__CLASS__."::".__FUNCTION__." Amount is less than 1");
            return returnResponse("Amount should be positive !", HttpStatus::HTTP_EXPECTATION_FAILED, HttpStatus::HTTP_ERROR);
        }
        if(empty($description)){
            $description = "";
        }
        
        if ($authenticate == 1) {
            if (auth()->user()->id) {
                try {
                    DB::beginTransaction();
                    $data = DB::table('shops')->where('shop_code', '=', $shop_code)->where('id', '=', $shop_id)->where('status', '=', 'ACTIVE')->first();
                    if(isset($data->id)){
                        $user = DB::table('users')->where('id', '=', auth()->user()->id)->where('status', '=', '1')->first();
                        if(isset($user->id)){
                            if($user->s_wallet >= $amount){
                                $order_id = self::generateOrderId();
                                $order_type = "PAY_TO_SHOP";
                                $balance_after_user = $user->s_wallet - $amount;
                                if(!\App\Models\Core\WalletModel::debitFromShoppingWallet(auth()->user()->id, $amount, $balance_after_user, $description, $order_id, $order_type, $shop_id)){
                                    return returnResponse("Payment Error !", HttpStatus::HTTP_EXPECTATION_FAILED, HttpStatus::HTTP_ERROR);
                                }

                                // Pay to Shop
                                $balance_after = $data->wallet_balance + $amount;
                                if(\App\Models\Core\WalletModel::creditInShopWallet($shop_id, $amount, $balance_after, $description, $order_id, $order_type, auth()->user()->id)){
                                    DB::commit();
                                    Log::debug(__CLASS__."::".__FUNCTION__." Payment success ");
                                    return returnResponse("Payment Success !", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS);
                                }
                            } else {
                                Log::error(__CLASS__."::".__FUNCTION__." Insufficient balance i. e. ".$user->s_wallet);
                                return returnResponse("Insufficient balance !", HttpStatus::HTTP_EXPECTATION_FAILED, HttpStatus::HTTP_ERROR);
                            }
                        } else {
                            Log::error(__CLASS__."::".__FUNCTION__." Customer not found ");
                            return returnResponse("Customer not found !", HttpStatus::HTTP_EXPECTATION_FAILED, HttpStatus::HTTP_ERROR);
                        }
                    } else {
                        Log::error(__CLASS__."::".__FUNCTION__." QR Code not found !");
                    }
                   return returnResponse("QR Code not found !", HttpStatus::HTTP_EXPECTATION_FAILED, HttpStatus::HTTP_ERROR);
               }
                catch (Exception $exc) {
                    Log::error(__CLASS__."::".__FUNCTION__." Exception : ".$exc->getMessage());
                    return returnResponse(HttpStatus::$text[HttpStatus::HTTP_EXPECTATION_FAILED], HttpStatus::HTTP_ERROR);
                }
            } else {
                return returnResponse("Your account has been deactivated.", HttpStatus::HTTP_UNAUTHORIZED);
            }
        } 
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
    }
    
    private static function generateOrderId() {
        $orderId = "";
        do {
            $time = time();
            $orderId = $time.substr(uniqid(mt_rand(), true) , 0, 6);
            Log::debug('Order Id generated as : '.$orderId);
            $data = DB::table('shops_wallet_txn')->where('order_id', $orderId)->get();
        } while ($data->count() > 0);
        
        Log::debug('Order Id returning as : '.$orderId);
        return $orderId;
    }
}
