<?php
namespace App\Models\AppModels;

require_once app_path() . '/razorpay/Razorpay.php';
use Razorpay\Api\Api;
use App\Helpers\HttpStatus;
use Log;
use DB;
use Auth;
use Validator;
use App\Models\AppModels\Orders;
use App\Http\Controllers\App\AppSettingController;
use App\Models\Eloquent\CartModel;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PaymentGatewayModel
 *
 * @author Apple
 */
class PaymentGatewayModel {
    
    public static function generateTxnId($con){
        Log::info(__CLASS__." :: ".__FUNCTION__." started...");
        $txn_id = false;
        for($i=0; $i<50; $i++){
            $txn_id = self::generateAndValidateTxnId($con);
            if($txn_id){
                break;
            }
        }
        return $txn_id;
    }
    private static function generateAndValidateTxnId(){
        Log::info(__CLASS__." :: ".__FUNCTION__." started...");
        $txn_id = time().rand(100, 999);
        try {
            $data = DB::table('pgateway_txn')->select('id')->where('txn_id', '=', $txn_id)->first();
            if(isset($data->id)){
                return false;
            }
        } catch (PDOException $exc) {
            Log::error(__CLASS__." :: ".__FUNCTION__." Exception :: ".$exc->getMessage());
            return false;
        }
        return $txn_id;
    }
    public static function get_payment_history($student_id){
        Log::debug(__CLASS__." :: ".__FUNCTION__." started...");
        $data = DB::table('pgateway_txn')->where('student_id', '=', $student_id)->where('status', "SUCCESS")->first();
        return $data;
    }
    public static function get_payment_details($customer_id){
        Log::debug(__CLASS__." :: ".__FUNCTION__." started...");
        $data = DB::table('pgateway_txn')->where('customer_id', '=', $customer_id)->where('status', "SUCCESS")->orderBy('id', 'desc')->limit(1)->get();
        return $data;
    }
    public static function get_success_payment_txn_id($customer_id){
        Log::debug(__CLASS__." :: ".__FUNCTION__." started...");
        $data = DB::table('pgateway_txn')->where('customer_id', '=', $customer_id)->where('status', "SUCCESS")->first();
        return $data;
    }
    public static function get_customer_id_by_txn_id($txn_id){
        Log::debug(__CLASS__." :: ".__FUNCTION__." started...");
        $data = DB::table('pgateway_txn')->where('txn_id', '=', $txn_id)->first();
        return $data;
    }
    public static function get_pending_transaction_by_txn_id($txn_id, $customer_id){
        Log::debug(__CLASS__." :: ".__FUNCTION__." started... with $txn_id, $customer_id");
        $data = DB::table('pgateway_txn')->where('status', '=', "PENDING")->where('txn_id', '=', $txn_id)->where('customer_id', '=', $customer_id)->first();
        return $data;
    }
    public static function get_pending_transaction_by_txn_id_and_order_id($txn_id, $order_id, $customer_id){
        Log::debug(__CLASS__." :: ".__FUNCTION__." started...");
        $data = DB::table('pgateway_txn')->where('status', '=', "PENDING")->where('razorpay_order_id', '=', $order_id)->where('txn_id', '=', $txn_id)->where('customer_id', '=', $customer_id)->limit(1)->get();
        return $data;
    }
    private static function update_transaction($customer_id, $user_id, $order_id, $txn_id, $status, $desc, $razorpay_payment_id){
        Log::debug(__CLASS__." :: ".__FUNCTION__." Called on upgrade txn with Customer Id $customer_id and Txn id $txn_id, status $status");
        try
        {
            if($razorpay_payment_id){
                return DB::table('pgateway_txn')->where('txn_id', '=', $txn_id)->where('customer_id', '=', $customer_id)->where('order_id', '=', $order_id)
                ->update([
                  'status' => $status,
                  'razorpay_payment_id' => $razorpay_payment_id,
                  'description' => $desc,
                  'updated_by' => $user_id,
                ]);
            }
            else{
                return DB::table('pgateway_txn')->where('txn_id', '=', $txn_id)->where('customer_id', '=', $customer_id)->where('order_id', '=', $order_id)
                ->update([
                  'status' => $status,
                  'description' => $desc,
                  'updated_by' => $user_id,
                ]);
            }
            

        }catch (\PDOException $e){
            Log::error(__CLASS__." :: ".__FUNCTION__." Exception :: ".$e->getMessage());
        }
        return false;
    }
    private static function update_transaction_canceled($customer_id, $user_id, $order_id, $txn_id, $status, $desc, $platform){
        Log::debug(__CLASS__." :: ".__FUNCTION__." Called on upgrade txn with Customer Id $customer_id and Txn id $txn_id, status $status, Platform $platform");
        try
        {
            return DB::table('pgateway_txn')->where('txn_id', '=', $txn_id)->where('customer_id', '=', $customer_id)
            ->where('razorpay_payment_id', '=', $order_id)
            ->update([
              'status' => $status,
              'description' => $desc,
              'updated_by' => $user_id,
            ]);

        }catch (\PDOException $e){
            Log::error(__CLASS__." :: ".__FUNCTION__." Exception :: ".$e->getMessage());
        }
        return false;
    }
    private static function update_order_payment($customer_id, $order_no, $status, $gateway){
        Log::debug(__CLASS__." :: ".__FUNCTION__." Called on upgrade txn with Customer Id $customer_id, status $status");
        try
        {
            return DB::table('orders')->where('order_id', '=', $order_no)->where('customer_id', '=', $customer_id)
            ->update([
              'payment_status' => $status,
              'payment_method' => $gateway,
            ]);

        }catch (\PDOException $e){
            Log::error(__CLASS__." :: ".__FUNCTION__." Exception :: ".$e->getMessage());
        }
        return false;
    }
    
    private static function initiatePaymentDB($customer_id, $user_id, $orders_id, $order_id, $txn_id, $razorpay_orderid, $amount, $name, $mobile, $email, $platform){
        Log::debug(__CLASS__." :: ".__FUNCTION__." Called with Customer Id $customer_id and Txn id $txn_id and razorpay order id $razorpay_orderid");
        try
        {
            $ip = get_client_ip();
            $user_agent = "";
            return DB::table('pgateway_txn')->insert([
              'txn_id' => $txn_id,
              'customer_id' => $customer_id,
              'orders_id' => $orders_id,
              'order_id' => $order_id,
              'razorpay_order_id' => $razorpay_orderid,
              'name' => $name,
              'mobile' => $mobile,
              'email' => $email,
              'amount' => $amount,
              'platform' => $platform,
              'ip' => $ip,
              'user_agent' => $user_agent,
              'created_by' => $user_id,
              'created_at' => date('Y-m-d h:m:s'),
          ]);

        } catch (\PDOException $e){
            Log::error(__CLASS__."::".__FUNCTION__." Exception :: ".$e->getMessage());
        }
        return false;
    }
     public static function get_payment_methods_detail_value_by_key($payment_method_id, $key){
        Log::debug(__CLASS__." :: ".__FUNCTION__." started...");
        $data = DB::table('payment_methods_detail')->select('value')
        ->where('key', '=', $key)
        ->where('payment_methods_id', '=', $payment_method_id)->first();
        
        return $data;
    }
    
    public static function get_payment_methods_detail_by_id($payment_method_id){
        Log::debug(__CLASS__." :: ".__FUNCTION__." started...");
        $data = DB::table('payment_methods')
        ->where('payment_methods_id', '=', $payment_method_id)->first();
        
        return $data;
    }
    public static function initiatePayment($customer_id, $user_id, $orders_id, $order_id, $txn_id, $name, $mobile, $email_id, $amount, $platform){
        Log::debug(__CLASS__." :: ".__FUNCTION__." started with customer $customer_id, User Id $user_id");
        if(empty($amount)){
            Log::debug(__CLASS__." :: ".__FUNCTION__." Amount is empty for customer id $customer_id");
            return false;
        }
        Log::debug(__CLASS__." :: ".__FUNCTION__." started with customer id $customer_id");
        
        try
        {
            Log::debug(__CLASS__." :: ".__FUNCTION__." Txn Id : $txn_id ");
            $apiKey = "";
            $apiSecret = "";
            $razorpayKey = self::get_payment_methods_detail_value_by_key(2, "RAZORPAY_KEY");
            if(isset($razorpayKey->value)){
                $apiKey = $razorpayKey->value;
            }
            $razorpaySecret = self::get_payment_methods_detail_value_by_key(2, "RAZORPAY_SECRET");
            if(isset($razorpaySecret->value)){
                $apiSecret = $razorpaySecret->value;
            }
            Log::debug(__CLASS__." :: ".__FUNCTION__." Key : $apiKey, Secret : $apiSecret ");
            $api = new Api($apiKey, $apiSecret);
            //
            // We create an razorpay order using orders api
            // Docs: https://docs.razorpay.com/docs/orders
            //
            $orderData = [
                'receipt'         => $order_id,
                'amount'          => $amount*100,
                'currency'        => 'INR',
                'payment_capture' => 1 // auto capture
            ];

            $razorpayOrder = $api->order->create($orderData);
            $razorpayOrderId = $razorpayOrder['id'];
            
            Log::debug(__CLASS__." :: ".__FUNCTION__." Razorpay Order Id : $razorpayOrderId ");
            if(self::initiatePaymentDB($customer_id, $user_id, $orders_id, $order_id, $txn_id, $razorpayOrderId, $amount, $name, $mobile, $email_id, $platform)){
                $data = array(
                    'order_id' => $razorpayOrderId
                );
                return $data;
            }

        }catch (\Exception $e){
            Log::error(__CLASS__." :: ".__FUNCTION__." Exception :: ".$e->getMessage());
        }
        Log::error(__CLASS__." :: ".__FUNCTION__." Payment initiate failed  for customer id $customer_id ");
        return false;
    }
    
    public static function validatePayment($request){
        Log::debug(__CLASS__." :: ".__FUNCTION__." Called");
        $success = false;
        $status = "FAILED";
        $desc = "Payment failed";
        $razorpay_orderId = htmlspecialchars(strip_tags($request->input('razorpay_order_id')));
        $razorpay_payment_id = htmlspecialchars(strip_tags($request->input('razorpay_payment_id')));
        $razorpay_signature = htmlspecialchars(strip_tags($request->input('razorpay_signature')));
        $txnId = htmlspecialchars(strip_tags($request->input('txn_id')));
        $platform = htmlspecialchars(strip_tags($request->input('platform')));
        Log::debug(__CLASS__." :: ".__FUNCTION__." Order Id Received as $razorpay_orderId");
        $customer_id = auth()->user()->id;
        Log::debug(__CLASS__." :: ".__FUNCTION__." Txn Id $txnId,  Customer Id : $customer_id");
        $txnData = self::get_pending_transaction_by_txn_id($txnId, $customer_id);
        
        if(!isset($txnData->id)){
            Log::error(__CLASS__." :: ".__FUNCTION__." Txn Data getting failed");
            return returnResponse("Payment validation failed !!", HttpStatus::HTTP_ERROR);
        }
        $amount = "";
        $razorpay_order_id = ""; $order_id = "";
        $name = ""; $email = ""; $mobile = ""; $txn_status = "";
        if(isset($txnData->razorpay_order_id)){
            $razorpay_order_id = $txnData->razorpay_order_id; 
        }
        if(isset($txnData->order_id)){
            $order_id = $txnData->order_id; 
        }
        if(isset($txnData->amount)){
            $amount = $txnData->amount; 
        }
        if(isset($txnData->name)){
            $name = $txnData->name; 
        }
        if(isset($txnData->email)){
            $email = $txnData->email; 
        }
        if(isset($txnData->mobile)){
            $mobile = $txnData->mobile; 
        }
        if(isset($txnData->status)){
            $txn_status = $txnData->status; 
        }
         Log::debug(__CLASS__." :: ".__FUNCTION__." Razorpay Order Id $razorpay_order_id,  Order Id : $order_id, Txn Status : $txn_status");
        if($txn_status != "PENDING"){
            Log::error(__CLASS__." :: ".__FUNCTION__." Payment status is not pending !");
            return returnResponse("Payment status is not pending !!", HttpStatus::HTTP_ERROR);
        }
        if (empty($razorpay_payment_id) === false)
        {
            $apiKey = "";
            $apiSecret = "";
            $razorpayKey = self::get_payment_methods_detail_value_by_key(2, "RAZORPAY_KEY");
            if(isset($razorpayKey->value)){
                $apiKey = $razorpayKey->value;
            }
            $razorpaySecret = self::get_payment_methods_detail_value_by_key(2, "RAZORPAY_SECRET");
            if(isset($razorpaySecret->value)){
                $apiSecret = $razorpaySecret->value;
            }
            $api = new Api($apiKey, $apiSecret);
            try
            {
                // Please note that the razorpay order ID must
                // come from a trusted source (session here, but
                // could be database or something else)
                $attributes = array(
                    'razorpay_order_id' => $razorpay_order_id,
                    'razorpay_payment_id' => $razorpay_payment_id,
                    'razorpay_signature' => $razorpay_signature
                );

                $api->utility->verifyPaymentSignature($attributes);
                $success = true;
            }
            catch(SignatureVerificationError $e)
            {
                $desc = 'Razorpay Error : ' . $e->getMessage();
            }
        }
        if($success){
            $payment_status = "SUCCESS";
            $desc = "Payment Success";
            if(!self::update_transaction($customer_id, $customer_id, $order_id, $txnId, $payment_status, $desc, $razorpay_payment_id)){
                Log::error(__CLASS__." :: ".__FUNCTION__." Payment validation failed  for customer id $customer_id !");
                return returnResponse("Payment validation failed !!", HttpStatus::HTTP_ERROR);
            }
            $status = "ORDERED";
            if(!Orders::updateOrderStatus($customer_id, $customer_id, $order_id, $status, $payment_status)){
                Log::error(__CLASS__." :: ".__FUNCTION__." Payment validation failed ! status updating failed  for customer id $customer_id !");
                return returnResponse("Payment validation failed !!", HttpStatus::HTTP_ERROR);
            }
            $notify = "N";
            $comment = "Order Placed";
//            OrderModel::addOrderHistory($order_no, $customer_id, $user_id, $comment, $notify, $status, $platform);
//            $message_text = OrderModel::getOrderSMSText($name, $amount, $order_no);
//            Log::error(__CLASS__." :: ".__FUNCTION__." Message Text : $message_text");
//            if(!SMSModel::scheduleNewSMS($mobile, $message_text, 'PRODUCT ORDER', 'AUTO', $con))
//            {
//                Log::error(__CLASS__." :: ".__FUNCTION__." Error while sending SMS");
//            }
            $ordersData = DB::table('orders')->where('order_id', '=', $order_id)
                                    ->where('customers_id', '=', $customer_id)->first();
            $orders_history_id = DB::table('orders_status_history')->insertGetId(
                            ['orders_id' => $ordersData->orders_id,
                                'orders_status_id' => '5',
                                'date_added' => date('Y-m-d h:i:s'),
                                'customer_notified' => '1',
                                'comments' => $comment,
                            ]);
            return returnResponse("Your placed successfully !", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS);
        }
        
        $payment_status = $status;
        self::update_transaction($customer_id, $customer_id, $order_id, $txnId, $status, $desc, $razorpay_payment_id);
        Orders::updateOrderStatus($customer_id, $customer_id, $order_id, $status, $payment_status);
        $orders_history_id = DB::table('orders_status_history')->insertGetId(
                            ['orders_id' => $ordersData->orders_id,
                                'orders_status_id' => '6',
                                'date_added' => date('Y-m-d h:i:s'),
                                'customer_notified' => '1',
                                'comments' => $desc,
                            ]);
        Log::error(__CLASS__." :: ".__FUNCTION__." Payment validation failed  for customer id $customer_id ");
        return returnResponse("Payment validation failed !!", HttpStatus::HTTP_ERROR);
    }
    
     
    // payment gateway cancel order
    public static function updatePaymentCanceled($request)
    {

        Log::debug(__CLASS__."::".__FUNCTION__." called");	
        $consumer_data = getallheaders();
        $consumer_data['consumer_ip'] = $request->ip();
        $consumer_data['consumer_url']  	  =  __FUNCTION__;
        $authController = new AppSettingController();
        $authenticate = $authController->apiAuthenticate($consumer_data);

        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
            'txn_id' => 'required',
            'products.*' => 'required',
          ]);

        if ($validator->fails()) {
            Log::error(__CLASS__ . "::" . __FUNCTION__ . " Validation failed !");
            return returnResponse($validator->errors(), HttpStatus::HTTP_UNPROCESSABLE_ENTITY);
        }
        $razorpay_order_id =  $request->order_id;
        $txn_id =  $request->txn_id;
        $desc =  $request->desc;
        Log::debug(__CLASS__."::".__FUNCTION__." Txn Id : $txn_id, Razorpay Order Id : $razorpay_order_id");
        Log::debug(__CLASS__."::".__FUNCTION__."started with Razorpay order id $razorpay_order_id, Description : $desc");
        if($authenticate==1 && auth()->user()->id){
            Log::debug(__CLASS__."::".__FUNCTION__." Authenticated");
            $cust_info = DB::table('customers')->where('id', auth()->user()->id)->where('status', '1')->first();
            Log::debug('Cust Info >>');
            $produstsArray = json_decode($request->products, true);
            if(isset($cust_info->id)){
                try{
                    DB::beginTransaction();
                    $date_added = date('Y-m-d h:i:s');
                    $data = DB::table('pgateway_txn')
                          ->LeftJoin('orders', 'orders.order_id', '=', 'pgateway_txn.order_id')
                          ->where('orders.order_id', $razorpay_order_id)
                          ->where('pgateway_txn.txn_id', $txn_id)
                          ->where('pgateway_txn.customer_id', auth()->user()->id)
                          ->where('pgateway_txn.status', 'PENDING')
                          ->select('pgateway_txn.*', 'orders.orders_id as orders_id','orders.pgateway_amount as pgateway_amount', 'orders.wallet_amount as wallet_amount','orders.op_disc_amount as op_disc_amount', 'orders.net_amount as net_amount')
                          ->first();


                    if(empty($data->id)){
                        Log::error(__CLASS__."::".__FUNCTION__." Order Details not found.");
                        return returnResponse("Order Details not found.");
                    }

                    $status = DB::table('orders_status')->LeftJoin('orders_status_description', 'orders_status_description.orders_status_id', '=', 'orders_status.orders_status_id')
                    ->where('orders_status_description.language_id', '=', 1)->where('role_id', '<=', 2)->where('orders_status_description.orders_status_id', '=', '6')->first();

                    //orders status history
                    $orders_history_id = DB::table('orders_status_history')->insertGetId(
                        ['orders_id' => $data->orders_id,
                            'orders_status_id' => '6',
                            'date_added' => $date_added,
                            'customer_notified' => '1',
                            'comments' => 'Payment failed from gateway : '.$desc,
                        ]);
                    Log::debug(__CLASS__."::".__FUNCTION__." Order History Insert Id : $orders_history_id");
                    $reverseStock = Orders::reverseStock($data->orders_id);
                    if(!$reverseStock){
                        Log::debug(__CLASS__."::".__FUNCTION__." Reverse Stock updating failed");
                        return returnResponse("Error at updating order quantity please try again.");
                    }
                    $updateStatus = DB::table('orders')->where('orders_id', '=', $data->orders_id)
                        ->where('customers_id', '=', auth()->user()->id)->update(['status'=>$status->orders_status_name,'payment_status'=>$status->orders_status_name,'updated_by'=>auth()->user()->id,'updated_at'=>$date_added]);

                    if(!$updateStatus){
                        Log::error(__CLASS__."::".__FUNCTION__." Error at updating order status please try again");
                        return returnResponse("Error at updating order status please try again.");
                    }

                    $updatePaymentStatus = DB::table('pgateway_txn')->where('order_id', '=', $data->order_id)
                        ->where('customer_id', '=', auth()->user()->id)->where('txn_id', $txn_id)->update(['status'=>$status->orders_status_name, 'description' => $desc,'updated_by'=>auth()->user()->id,'updated_at'=>$date_added]);

                    if(!$updatePaymentStatus){
                        Log::error(__CLASS__."::".__FUNCTION__." Error at updating Payment status please try again");
                        return returnResponse("Error at updating Payment status please try again.");
                    }
                    if($data->wallet_amount > 0 && $data->wallet_amount <= $data->net_amount){
                        $balance_after = $cust_info->m_wallet + $data->wallet_amount;
                        $order_type = "PRODUCT ORDER";
                        $txn_desc = "Credit By Order Cancel";
                        if(!\App\Models\Core\WalletModel::creditInMainWallet(auth()->user()->id, $data->wallet_amount, $balance_after, $txn_desc, $data->order_id, $order_type)){
                            Log::error("error while credit in Wallet !!!");
                            return returnResponse("Payment error updating failed ! Wallet credit failed");
                        }
                    }
                    
                    if($data->op_disc_amount > 0 && $data->pgateway_amount > 0){
                        $balance_after = $cust_info->advance_wallet + $data->op_disc_amount;
                        $order_type = "PRODUCT ORDER";
                        $txn_desc = "Credit By Order Cancel";
                        if(!\App\Models\Core\WalletModel::creditInAdvanceWallet(auth()->user()->id, $data->op_disc_amount, $balance_after, $txn_desc, $data->order_id, $order_type)){
                            Log::error("error while credit in Advance Wallet !!!");
                            return returnResponse("Payment error updating failed ! Advance Wallet credit failed");
                        }
                    }
                    
                    foreach ($produstsArray as $product) {
                    $products = json_decode($product, true);
                    Log::debug(__CLASS__."::".__FUNCTION__." Got cart id as ".$products['cart_id']);
                    //dd($products['price'], $currency_code);
                    if(!CartModel::where('customers_basket_id', $products['cart_id'])->where('customers_id', auth()->user()->id)->update(['is_order' => '0'])){
                        return returnResponse("Cart data update failed");
                    }

                    
                }
                    

                    DB::commit();
                    return returnResponse("Order payment error updated succefully.", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS); 

                } catch (\Exception $exc){
                    Log::error(__CLASS__."::".__FUNCTION__." Exception : ".$exc->getMessage());
                   return returnResponse("Oops Error occured please try again !");
                }
            } 
        }
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
    }   
    


    // validate payment status with razorpay using order id
    public static function validatePaymentStatusWithRazorPay($razorpay_order_id){
        Log::debug(__CLASS__." :: ".__FUNCTION__." started with razor pay order id $razorpay_order_id");
        try {
            Log::debug(__CLASS__." :: ".__FUNCTION__." lets call curl ");
            $url = "https://api.razorpay.com/v1/orders/".$razorpay_order_id;
            $razorpayKey = self::get_payment_methods_detail_value_by_key(2, "RAZORPAY_KEY");
            if(!isset($razorpayKey->value)){
                return false;
            }
            $razorpaySecret = self::get_payment_methods_detail_value_by_key(2, "RAZORPAY_SECRET");
            if(!isset($razorpaySecret->value)){
                return false;
            }
            $api_key = $razorpayKey->value;
            $api_secret = $razorpaySecret->value;
            //$api_key = "rzp_live_n0jFPMmcF3l5Xg";
            //$api_secret = "uA96lE7fAAduDCAZbirpDc8o";
            //curl_setopt($ch, CURLOPT_USERPWD, "$api_key: $api_secret");
            $options = array (CURLOPT_RETURNTRANSFER => true, // return web page
                CURLOPT_HEADER => false, // don't return headers
                CURLOPT_FOLLOWLOCATION => false, // follow redirects
                CURLOPT_ENCODING => "", // handle compressed
                CURLOPT_USERAGENT => "test", // who am i
                CURLOPT_AUTOREFERER => true, // set referer on redirect
                CURLOPT_CONNECTTIMEOUT => 120, // timeout on connect
                CURLOPT_TIMEOUT => 120, // timeout on response
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_USERPWD => "$api_key:$api_secret" ); // stop after 10 redirects
            $ch = curl_init ( $url );
            curl_setopt_array ( $ch, $options );
            $content = curl_exec ( $ch );
            $err = curl_errno ( $ch );
            $errmsg = curl_error ( $ch );
            $header = curl_getinfo ( $ch );
            $httpCode = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
            Log::debug(__CLASS__." :: ".__FUNCTION__." http code recived as $httpCode");
            curl_close ( $ch );

            $header ['errno'] = $err;
            $header ['errmsg'] = $errmsg;
            $header ['content'] = $content;
            if(!empty($header ['content'])){
                $jsonDecodeResp = json_decode($header ['content'] ,TRUE);
                return $jsonDecodeResp['status'];
            }
            Log::warning(__CLASS__." :: ".__FUNCTION__." no repsonse found from http request !!");

        } catch (Exception $e) {
            Log::error(__CLASS__."::".__FUNCTION__." Exception : ".$exc->getMessage());
            
        }
        return false;
    }
    
    
    //function to set order status as success
    public static function updateOrderPaymentStatusByCron($ordersData,$pGatewayData,$paymentStatus) {
        Log::debug(__CLASS__." :: ".__FUNCTION__." Called");
        $razorpay_order_id = $pGatewayData->razorpay_order_id;
        $txn_status = $pGatewayData->status;
        $order_id = $ordersData->order_id;
        $customer_id = $ordersData->customers_id;
        $txnId = $pGatewayData->txn_id;
        $razorpay_payment_id = '';
        $status = "FAILED";
        $desc = "Payment Failed";
        Log::debug(__CLASS__." :: ".__FUNCTION__." Razorpay Order Id $razorpay_order_id,  Order Id : $order_id, Txn Status : $txn_status and payment status from razor pay api $paymentStatus");
        if($txn_status != "PENDING"){
            Log::error(__CLASS__." :: ".__FUNCTION__." Payment status is not pending !");
            return false;
        }
        try{
            Log::debug(__CLASS__." :: ".__FUNCTION__." payment status found from razorpay as $paymentStatus");

            if ($paymentStatus == 'paid') {
                $payment_status = "SUCCESS";
                $desc = "Payment Success";
                $status = "ORDERED";
                if (!self::update_transaction($customer_id, $customer_id, $order_id, $txnId, $payment_status, $desc, $razorpay_payment_id)) {
                    Log::error(__CLASS__ . " :: " . __FUNCTION__ . " Payment updation failed  for customer id $customer_id and order id  $order_id!");
                    return false;
                }
                if (!Orders::updateOrderStatus($customer_id, $customer_id, $order_id, $status, $payment_status)) {
                    Log::error(__CLASS__ . " :: " . __FUNCTION__ . "Order status updating failed  for customer id $customer_id and order id  $order_id!");
                    return false;
                }
                $comment = "Order Placed";
                $orders_history_id = DB::table('orders_status_history')->insertGetId(
                        ['orders_id' => $ordersData->orders_id,
                            'orders_status_id' => '5',
                            'date_added' => date('Y-m-d h:i:s'),
                            'customer_notified' => '1',
                            'comments' => $comment,
                ]);
                if ($orders_history_id) {
                    Log::debug(__CLASS__ . " :: " . __FUNCTION__ . "Order status updating sussess  for customer id $customer_id and order id  $order_id!");
                    return true;
                } else {
                    Log::error(__CLASS__ . " :: " . __FUNCTION__ . "Order status history insertion failed  for customer id $customer_id and order id  $order_id!");
                    return false;
                }
            }
            else if($paymentStatus != 'created' && $paymentStatus != 'attempted')
            {
                $payment_status = "FAILED";
                $desc = "Payment Failed";
                $status = "FAILED";
                if (!self::update_transaction($customer_id, $customer_id, $order_id, $txnId, $payment_status, $desc, $razorpay_payment_id)) {
                    Log::error(__CLASS__ . " :: " . __FUNCTION__ . " Payment updation failed  for customer id $customer_id and order id  $order_id!");
                    return false;
                }
                if (!Orders::updateOrderStatus($customer_id, $customer_id, $order_id, $status, $payment_status)) {
                    Log::error(__CLASS__ . " :: " . __FUNCTION__ . "Order status updating failed  for customer id $customer_id and order id  $order_id!");
                    return false;
                }
                $comment = "Order Failed due to payment failed";
                $orders_history_id = DB::table('orders_status_history')->insertGetId(
                        ['orders_id' => $ordersData->orders_id,
                            'orders_status_id' => '6',
                            'date_added' => date('Y-m-d h:i:s'),
                            'customer_notified' => '1',
                            'comments' => $comment,
                ]);
                if ($orders_history_id) {
                    Log::debug(__CLASS__ . " :: " . __FUNCTION__ . "Order status updating sussess  for customer id $customer_id and order id  $order_id!");
                    return true;
                } else {
                    Log::error(__CLASS__ . " :: " . __FUNCTION__ . "Order status history insertion failed  for customer id $customer_id and order id  $order_id!");
                    return false;
                }
            }
        }catch (Exception $e) {
            Log::error(__CLASS__." :: ".__FUNCTION__." error while processing ".$e->getTraceAsString());
        }
       return false; 
    }
    
    
    #################################################################################################################
    # Cashfree payment gateway
    ###############################################################################################################
    
    
    public static function chashFreeInitiatePayment($customer_id, $user_id, $orders_id, $order_id, $txn_id, $name, $mobile, $email_id, $amount, $platform){
        Log::debug(__CLASS__." :: ".__FUNCTION__." started with customer $customer_id, User Id $user_id");
        if(empty($amount)){
            Log::debug(__CLASS__." :: ".__FUNCTION__." Amount is empty for customer id $customer_id");
            return false;
        }
        Log::debug(__CLASS__." :: ".__FUNCTION__." started with customer id $customer_id");
        
        try
        {
            Log::debug(__CLASS__." :: ".__FUNCTION__." Txn Id : $txn_id ");
            $apiKey = "";
            $apiSecret = "";
            $cashfreeKey = self::get_payment_methods_detail_value_by_key(4, "CASHFREE_KEY");
            if(isset($cashfreeKey->value)){
                $apiKey = $cashfreeKey->value;
            }
            $cashfreeSecret = self::get_payment_methods_detail_value_by_key(4, "CASHFREE_SECRET");
            if(isset($cashfreeSecret->value)){
                $apiSecret = $cashfreeSecret->value;
            }
            Log::debug(__CLASS__." :: ".__FUNCTION__." Key : $apiKey, Secret : $apiSecret ");
           
            $paymentMethodData = self::get_payment_methods_detail_by_id(4);
            
            $url = 'https://test.cashfree.com/api/v2/cftoken/order';
            if($paymentMethodData->environment ==1){
                $url = 'https://api.cashfree.com/api/v2/cftoken/order';
            }
            
            //
            // We create an razorpay order using orders api
            // Docs: https://docs.razorpay.com/docs/orders
            //
            $orderData = [
                'orderId'         => $order_id,
                'orderAmount'          => $amount,
                'orderCurrency'        => 'INR',
                
            ];

            $headers = array("Content-Type: application/json","x-client-id: $apiKey","x-client-secret: $apiSecret");
            Log::debug(__CLASS__."::".__FUNCTION__." called url as ".$url);
           $response = callUrlWithHeader($url, $headers, json_encode($orderData));
           
           Log::debug(__CLASS__."::".__FUNCTION__." Got response as".json_encode($response));
           
            $razorpayOrderId = '';
            
            Log::debug(__CLASS__." :: ".__FUNCTION__." Razorpay Order Id : $razorpayOrderId ");
            if(self::initiatePaymentDB($customer_id, $user_id, $orders_id, $order_id, $txn_id, $razorpayOrderId, $amount, $name, $mobile, $email_id, $platform)){
               if(isset($response['cftoken']) && $response['status'] == 'OK'){
                $data = array(
                    'cftoken' => $response['cftoken']
                );
                return $data;
               }
            }

        }catch (\Exception $e){
            Log::error(__CLASS__." :: ".__FUNCTION__." Exception :: ".$e->getMessage());
        }
        Log::error(__CLASS__." :: ".__FUNCTION__." Cash free Payment initiate failed  for customer id $customer_id ");
        return false;
    }
    
    
    
    public static function validatechashFreePayment($request){
        Log::debug(__CLASS__." :: ".__FUNCTION__." Called");
        $success = false;
        $status = "FAILED";
        $desc = "Payment failed";
        
        $orderId = $request->input('orderId');
        $orderAmount = $request->input('orderAmount');
        $referenceId = $request->input('referenceId');
        $txStatus = $request->input('txStatus');
        $paymentMode = $request->input('paymentMode');
        $desc =$txMsg = $request->input('txMsg');
        $txTime = $request->input('txTime');
        $signature = $request->input('signature');
        $dataPrevious = $orderId.$orderAmount.$referenceId.$txStatus.$paymentMode.$txMsg.$txTime;
        
        Log::debug(__CLASS__."::".__FUNCTION__." orderId got as $orderId");
        Log::debug(__CLASS__."::".__FUNCTION__." OrderAmt got as $orderAmount");
        Log::debug(__CLASS__."::".__FUNCTION__." txStatus got as $txStatus");
        Log::debug(__CLASS__."::".__FUNCTION__." Paymentmode got as $paymentMode");
        Log::debug(__CLASS__."::".__FUNCTION__." TxMsg got as $txMsg");
        Log::debug(__CLASS__."::".__FUNCTION__." txnTime got as $txTime");
        Log::debug(__CLASS__."::".__FUNCTION__." signature got as $signature");
        Log::debug(__CLASS__."::".__FUNCTION__." Data got as $dataPrevious");
        $razorpay_payment_id = $referenceId;
        $txnId = htmlspecialchars(strip_tags($request->input('txn_id')));
        
        Log::debug(__CLASS__." :: ".__FUNCTION__." Order Id Received as $orderId");
        $customer_id = auth()->user()->id;
        Log::debug(__CLASS__." :: ".__FUNCTION__." Txn Id $txnId,  Customer Id : $customer_id");
        $txnData = self::get_pending_transaction_by_txn_id($txnId, $customer_id);
        
        if(!isset($txnData->id)){
            Log::error(__CLASS__." :: ".__FUNCTION__." Txn Data getting failed");
            return returnResponse("Payment validation failed !!", HttpStatus::HTTP_ERROR);
        }
        $amount = "";
        $razorpay_order_id = ""; $order_id = "";
        $name = ""; $email = ""; $mobile = ""; $txn_status = "";
        if(isset($txnData->razorpay_order_id)){
            $razorpay_order_id = $txnData->razorpay_order_id; 
        }
        if(isset($txnData->order_id)){
            $order_id = $txnData->order_id; 
        }
        if(isset($txnData->amount)){
            $amount = $txnData->amount; 
        }
        if(isset($txnData->name)){
            $name = $txnData->name; 
        }
        if(isset($txnData->email)){
            $email = $txnData->email; 
        }
        if(isset($txnData->mobile)){
            $mobile = $txnData->mobile; 
        }
        if(isset($txnData->status)){
            $txn_status = $txnData->status; 
        }
         Log::debug(__CLASS__." :: ".__FUNCTION__." Order Id : $order_id, Txn Status : $txn_status");
        if($txn_status != "PENDING"){
            Log::error(__CLASS__." :: ".__FUNCTION__." Payment status is not pending !");
            return returnResponse("Payment status is not pending !!", HttpStatus::HTTP_ERROR);
        }
        $ordersData = DB::table('orders')->where('order_id', '=', $order_id)
                                    ->where('customers_id', '=', $customer_id)->first();
        if (!empty($razorpay_payment_id))
        {
            $apiKey = "";
            $apiSecret = "";
            $razorpayKey = self::get_payment_methods_detail_value_by_key(4, "CASHFREE_KEY");
            if(isset($razorpayKey->value)){
                $apiKey = $razorpayKey->value;
            }
            $razorpaySecret = self::get_payment_methods_detail_value_by_key(4, "CASHFREE_SECRET");
            if(isset($razorpaySecret->value)){
                $apiSecret = $razorpaySecret->value;
            }
            
            try
            {
                $hash_hmac = hash_hmac('sha256', $dataPrevious, $apiSecret, true) ;
                $computedSignature = base64_encode($hash_hmac);
                Log::debug(__CLASS__."::".__FUNCTION__." Signature got $signature");
                Log::debug(__CLASS__."::".__FUNCTION__." Computed Signature got $computedSignature");
                if ($signature == $computedSignature) {
                   $success = true;
                   Log::debug(__CLASS__."::".__FUNCTION__." Signature validated");
                 } 
            }
            catch(\Exception $e)
            {
                $desc = 'Cash Free Error : ' . $e->getMessage();
            }
        }
        
        try{
            if($success && $txStatus=='SUCCESS'){
            $payment_status = "SUCCESS";
            $desc = "Payment Success";
            if(!self::update_transaction($customer_id, $customer_id, $order_id, $txnId, $payment_status, $desc, $razorpay_payment_id)){
                Log::error(__CLASS__." :: ".__FUNCTION__." Payment validation failed  for customer id $customer_id !");
                return returnResponse("Payment validation failed !!", HttpStatus::HTTP_ERROR);
            }
            $status = "ORDERED";
            if(!Orders::updateOrderStatus($customer_id, $customer_id, $order_id, $status, $payment_status)){
                Log::error(__CLASS__." :: ".__FUNCTION__." Payment validation failed ! status updating failed  for customer id $customer_id !");
                return returnResponse("Payment validation failed !!", HttpStatus::HTTP_ERROR);
            }
            $notify = "N";
            $comment = "Order Placed";
//            OrderModel::addOrderHistory($order_no, $customer_id, $user_id, $comment, $notify, $status, $platform);
//            $message_text = OrderModel::getOrderSMSText($name, $amount, $order_no);
//            Log::error(__CLASS__." :: ".__FUNCTION__." Message Text : $message_text");
//            if(!SMSModel::scheduleNewSMS($mobile, $message_text, 'PRODUCT ORDER', 'AUTO', $con))
//            {
//                Log::error(__CLASS__." :: ".__FUNCTION__." Error while sending SMS");
//            }
            
            $orders_history_id = DB::table('orders_status_history')->insertGetId(
                            ['orders_id' => $ordersData->orders_id,
                                'orders_status_id' => '5',
                                'date_added' => date('Y-m-d h:i:s'),
                                'customer_notified' => '1',
                                'comments' => $comment,
                            ]);
            return returnResponse("Your Order placed successfully !", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS);
        }
        $payment_status = $txStatus;
        self::update_transaction($customer_id, $customer_id, $order_id, $txnId, $payment_status, $desc, $razorpay_payment_id);
        Orders::updateOrderStatus($customer_id, $customer_id, $order_id, $status, $payment_status);
        $orders_history_id = DB::table('orders_status_history')->insertGetId(
                            ['orders_id' => $ordersData->orders_id,
                                'orders_status_id' => '6',
                                'date_added' => date('Y-m-d h:i:s'),
                                'customer_notified' => '1',
                                'comments' => $desc,
                            ]);
        
    }catch(\Exception $e)
            {
        Log::error(__CLASS__." :: ".__FUNCTION__." Exception occured ". $e->getMessage());
        return returnResponse("Exception occured !!", HttpStatus::HTTP_ERROR);
            }
        
        Log::error(__CLASS__." :: ".__FUNCTION__." Payment validation failed  for customer id $customer_id ");
        return returnResponse("Payment validation failed !!", HttpStatus::HTTP_ERROR);
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}
