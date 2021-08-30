<?php
namespace App\Models\Cron;

use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\App\AppSettingController;
use App\Http\Controllers\App\AlertController;
use DB;
use Lang;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Validator;
use Mail;
use DateTime;
use Auth;
//use Carbon;
use App\Helpers\HttpStatus;
use Log;
use App\Models\Core\WalletModel;
use Carbon\Carbon;
use App\Models\AppModels\PaymentGatewayModel;
//use Razorpay\Api\Api;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CronJobModel
 *
 * @author ajit
 */
class CronJobModel extends Model {
    //put your code here
    
    public static function levelIncomeClosing()
    {
    	Log::debug(__CLASS__." :: ".__FUNCTION__." started ");
    	ini_set('max_execution_time', 0); //900 seconds = 15 minutes
    	Log::debug(__CLASS__." :: ".__FUNCTION__." max_execution_time updated as unlimited (0)");
    	try {
            $date = date('Y-m-d h:i:s');
            Log::debug(__CLASS__." :: ".__FUNCTION__." starting DB transaction ");
            DB::beginTransaction();
            Log::debug(__CLASS__." :: ".__FUNCTION__." fetching settings from database");
            $authController = new AppSettingController();
            $settings = $authController->getSetting();
            Log::debug(__CLASS__." :: ".__FUNCTION__." setting loaded, validating ");
            if(!isset($settings['tds_per'])){
                Log::error(__CLASS__." :: ".__FUNCTION__." setting validation failed!! ");
                return dd("Setting loading failed !!");
            }
            $tds_per = $settings['tds_per'];
            $service_per = $settings['service_per'];
            $shopping_per = $settings['shopping_per'];
            Log::debug(__CLASS__." :: ".__FUNCTION__." TDS Per $tds_per ");
            Log::debug(__CLASS__." :: ".__FUNCTION__." Service Per $service_per ");
            Log::debug(__CLASS__." :: ".__FUNCTION__." shopping Per $shopping_per ");
            Log::debug(__CLASS__." :: ".__FUNCTION__." calling method to get closing date ");
            $closing_date = self::validateAndGetLevelIncomeClosingDate();
            if(!$closing_date){
                Log::error(__CLASS__."  :: ".__FUNCTION__." Closing date not found as today !!!");
                return dd("Closing date not found as today !!");
            }
            Log::debug(__CLASS__." :: ".__FUNCTION__." closing date found as $closing_date ");
            Log::debug(__CLASS__." :: ".__FUNCTION__." fetching previous closing date");
            $previous_closing_date = self::getPreviousLevelClosingDate();
            Log::debug(__CLASS__." :: ".__FUNCTION__." previous closing date found as $previous_closing_date");
            if(!$previous_closing_date)
            {
                Log::warning(__CLASS__." :: ".__FUNCTION__." Previous Closing date error !!!");
                return dd("Previous Closing date getting failed !!");
            }
            Log::debug(__CLASS__." :: ".__FUNCTION__." fetching members data for level income closing ");
            $user_list = DB::table('users')->where('is_active', 'Y')->get();
            if(isset($user_list) and count($user_list) > 0){
                Log::debug(__CLASS__." :: ".__FUNCTION__." user cound found as ".count($user_list));
                Log::debug(__CLASS__." :: ".__FUNCTION__." starting loop ");
                $l=0;
                foreach ($user_list as $user) {
                    $l++;
                    Log::debug(__CLASS__." :: ".__FUNCTION__." fetching data from array for loop count ".$l);
                    $user_core_id = $user->id; $user_member_code = $user->member_code; $m_wallet= $user->m_wallet; $s_wallet = $user->s_wallet;
                    $level_income = 0;
                    Log::debug(__CLASS__." :: ".__FUNCTION__." fetching level income of member");
                    $level_income_new = DB::table('user_level_incomes')->where('member_code', $user_member_code)->where('is_paid', 'N')->where('status', 'ACTIVE')
                            //->where('actual_payemnt_date','<', Carbon::parse($closing_date)->format('Y-m-d')->addDays(1))->sum('amount')->amount;
                            ->where('actual_payemnt_date','<', (new Carbon($closing_date))->addDays(1))->sum('amount');
                    Log::debug(__CLASS__." :: ".__FUNCTION__." level income new calcualted as $level_income_new");
                    if($level_income_new > 0){
                        Log::debug(__CLASS__." :: ".__FUNCTION__." lets update the level income with new level income which is $level_income_new");
                        $level_income = $level_income_new;
                        Log::debug(__CLASS__." :: ".__FUNCTION__." level income updated as $level_income");
                        Log::debug(__CLASS__." :: ".__FUNCTION__." Lets update the level income table is paid and closing date values");
                        $level_update_status = DB::table('user_level_incomes')->where('member_code', $user_member_code)->where('is_paid', 'N')->where('status', 'ACTIVE')
                                ->where('actual_payemnt_date','<', (new Carbon($closing_date))->addDays(1))->update(['is_paid'=>'Y', 'closing_date'=>$closing_date, 'updated_at'=>$date]);
                        if(!$level_update_status){
                            Log::error(__CLASS__." :: ".__FUNCTION__." error while updating level income status to closed for user core id $user_core_id");
                            return dd("Level income update failed !!");
                        }
                        Log::debug(__CLASS__." :: ".__FUNCTION__." level income updated to closed for user core id $user_core_id !!");
                    }
                    Log::debug(__CLASS__." :: ".__FUNCTION__." lets calculate the total income !!");
                    $total_income = $level_income;
                    Log::debug(__CLASS__." :: ".__FUNCTION__." total income updated as $total_income!!");
                    if($total_income > 0){
                        $tds_amount = (($total_income * $tds_per)/ 100);
                        Log::debug(__CLASS__." :: ".__FUNCTION__." TDS amount calculate as $tds_amount with total amount $total_income and tds per $tds_per!!");
                        $service_amount = (($total_income * $service_per)/ 100);
                        Log::debug(__CLASS__." :: ".__FUNCTION__." Service amount calculate as $service_amount with total amount $total_income and service per $service_per!!");
                        $shopping_amount = (($total_income * $shopping_per)/ 100);
                        Log::debug(__CLASS__." :: ".__FUNCTION__." Shopping amount calculate as $shopping_amount with total amount $total_income and shopping per $shopping_per !!");
                        Log::debug(__CLASS__." :: ".__FUNCTION__." calculating net amount!!");
                        $net_amount = $total_income - $tds_amount - $service_amount -$shopping_amount;
                        Log::debug(__CLASS__." :: ".__FUNCTION__." net amount calculate as $net_amount with total amount $total_income and tds amount $tds_amount and service amount $service_amount and shopping amount $shopping_amount!!");
                        Log::debug(__CLASS__." :: ".__FUNCTION__." lets make entry in closing txn info table ");
                        $closing_txn_id = self::insertInClosingTxnInfo($user_core_id, $level_income, $tds_per, $tds_amount, $service_per, $service_amount, $shopping_per, $shopping_amount, $total_income, $net_amount, $closing_date);
                        Log::debug(__CLASS__." :: ".__FUNCTION__." closing txn entry id found as $closing_txn_id");
                        if(!$closing_txn_id){
                            Log::error(__CLASS__." :: ".__FUNCTION__." error while making entry in closing txn info for user core id $user_core_id");
                            return dd("Error while making entry in Closing TXN info table");
                        }
                        Log::debug(__CLASS__." :: ".__FUNCTION__." Closing TXN info entry completed !!");
                        Log::debug(__CLASS__." :: ".__FUNCTION__." lets credit in M wallet with amount $net_amount");
                        $balance_after_m_wallet= $m_wallet + $net_amount;
                        Log::debug(__CLASS__." :: ".__FUNCTION__." balance after for M wallet updated as $balance_after_m_wallet with previous balane $m_wallet and net amount $net_amount");
                        if(!WalletModel::creditInMainWallet($user_core_id, $net_amount, $balance_after_m_wallet, "Closing Credit of ".$closing_date, $closing_txn_id, 'CLOSING')){
                            Log::error(__CLASS__." :: ".__FUNCTION__." main wallet credting failed ");
                            return dd("Main Wallet crediting failed !!");
                        }
                        Log::debug(__CLASS__." :: ".__FUNCTION__." M wallet credit completed !!");
                        Log::debug(__CLASS__." :: ".__FUNCTION__." lets credit in S wallet with amount $net_amount");
                        $balance_after_s_wallet= $s_wallet + $shopping_amount;
                        Log::debug(__CLASS__." :: ".__FUNCTION__." balance after for S wallet updated as $balance_after_s_wallet with previous balane $s_wallet and shopping wallet $shopping_amount");
                        if(!WalletModel::creditInShoppingWallet($user_core_id, $shopping_amount, $balance_after_s_wallet, "Closing Credit of ".$closing_date, $closing_txn_id, 'CLOSING')){
                            Log::error(__CLASS__." :: ".__FUNCTION__." Shopping wallet credting failed ");
                            return dd("Shopping Wallet crediting failed !!");
                        }
                        Log::debug(__CLASS__." :: ".__FUNCTION__." S wallet credit completed !!");
                    }
                }
                Log::debug(__CLASS__." :: ".__FUNCTION__." for each loop completed ");
            }
            else{
                Log::info(__CLASS__." :: ".__FUNCTION__." no members found for closing !!");
            }
            // need to write the  code for closing info table
            Log::debug(__CLASS__." :: ".__FUNCTION__." closing completed lets get the next closing date for update !!!");
            //$next_closing_date = Carbon::parse($closing_date)->format('Y-m-d')->addDays(1);
            $next_closing_date = (new Carbon($closing_date))->addDays(1);
            Log::debug(__CLASS__." :: ".__FUNCTION__." next closing date found as $next_closing_date !!!");
            if(!DB::table('closing_info')->insert(['closing_date'=>$closing_date, 'next_closing_date'=>$next_closing_date, 'created_at'=>$date, 'created_by'=>'admin'])){
                Log::error(__CLASS__." :: ".__FUNCTION__." Closing info update failed ");
                return dd("Closing info update failed  !!");
            }
            Log::debug(__CLASS__." :: ".__FUNCTION__." Closing info updated !!");
            Log::debug(__CLASS__." :: ".__FUNCTION__." commiting data  !!");
            DB::commit();
            Log::info(__CLASS__." :: ".__FUNCTION__." data committed !!");
            return dd("Closing Success at closing date $closing_date");
            
    	} catch (Exception $e) {
    		Log::error(__CLASS__." :: ".__FUNCTION__." error while processing ".$e->getTraceAsString());
    	}
    	return dd("returning last false");
    }

    /*
     * Validate and get Level Income Closing date 
     */
    protected static function validateAndGetLevelIncomeClosingDate()
    {
    	Log::debug(__CLASS__." :: ".__FUNCTION__." started ");
    	try {
    		Log::debug(__CLASS__." :: ".__FUNCTION__." preparing query !!");
    		$data = DB::table('closing_info')->select(DB::raw('next_closing_date, CURDATE() as today'))->orderBy('id', 'desc')->limit(1)->first();
    		Log::debug(__CLASS__." :: ".__FUNCTION__." data fetched !!");
    		if(isset($data->next_closing_date)){
                    Log::debug(__CLASS__." :: ".__FUNCTION__." next closing date found as $data->next_closing_date !!");
                    Log::debug(__CLASS__." :: ".__FUNCTION__." current date found as $data->today !!");
                    $next_closing_date = $data->next_closing_date;
                    $cur_date = $data->today;
                    Log::debug(__CLASS__." :: ".__FUNCTION__." Validating the next closing date ($next_closing_date) with current date $cur_date");
                    if(strtotime($next_closing_date) <= strtotime($cur_date))
                    {
                        Log::debug(__CLASS__." :: ".__FUNCTION__." closing to be started, returning closing date");
                        return $next_closing_date;
                    }
    		}

    	} catch (Exception $e) {
    		Log::error(__CLASS__." :: ".__FUNCTION__." error while processing ".$e->getTraceAsString());
    	}
    	return false;
    }
    
    /*
     * Get Previoud level closing date
     */
    protected static function getPreviousLevelClosingDate()
    {
    	Log::debug(__CLASS__." :: ".__FUNCTION__." started ");
    	try {
    		Log::debug(__CLASS__." :: ".__FUNCTION__." preparing query !!");
    		$data = DB::table('closing_info')->select(DB::raw('closing_date'))->orderBy('id', 'desc')->limit(1)->first();
    		Log::debug(__CLASS__." :: ".__FUNCTION__." data fetched !!");
    		if(isset($data->closing_date)){
                    Log::debug(__CLASS__." :: ".__FUNCTION__." closing date found as $data->closing_date !!");
                    return $data->closing_date;
    		}

    	} catch (Exception $e) {
    		Log::error(__CLASS__." :: ".__FUNCTION__." error while processing ".$e->getTraceAsString());
    	}
    	return false;
    }
    
    /*
     * Entry in closing txn info table
     */
    protected static function insertInClosingTxnInfo($user_core_id, $level_income, $tds_per, $tds_amount, $service_per, $service_amount, $shopping_per, $shopping_amount, $total_income, $net_amount, $closing_date){
        Log::debug(__CLASS__." :: ".__FUNCTION__." started ");
        try {
            Log::debug(__CLASS__." :: ".__FUNCTION__." preparing query !!");
            $date = date('Y-m-d h:i:s');
            return DB::table('closing_txn_info')->insertGetId(['user_id'=>$user_core_id, 'level_income'=>$level_income, 'shopping_per'=>$shopping_per, 'shopping_amount'=>$shopping_amount, 
                    'tds_per'=>$tds_per, 'tds_amount'=>$tds_amount, 'service_per'=>$service_per, 'service_amount'=>$service_amount, 'net_amount'=>$net_amount, 'created_at'=>$date, 'closing_date' => $closing_date ]);
    	} catch (Exception $e) {
    		Log::error(__CLASS__." :: ".__FUNCTION__." error while processing ".$e->getTraceAsString());
    	}
    	return false;
    }


    // check for pending payments
    public static function checkForPendingOrders(){
        Log::debug(__CLASS__." :: ".__FUNCTION__." started..");
        try {
            Log::debug(__CLASS__." :: ".__FUNCTION__." fetching order list in pending status");
            $ordersData = DB::table('orders')->where('status', '=', 'PENDING')->where('payment_status', '=', 'PENDING')->where('pgateway_amount', '>', 0)->whereRaw('date_purchased < date_sub(now(), interval 1 hour)')->orderBy('date_purchased')->limit(50)->get();
            //$ordersData = DB::table('orders')->where('status', '=', 'PENDING')->where('payment_status', '=', 'PENDING')->where('pgateway_amount', '>', 0)->orderBy('date_purchased')->limit(50)->get();
            Log::debug(__CLASS__." :: ".__FUNCTION__." no of orders found as ".count($ordersData));
            if(count($ordersData) > 0){
                Log::debug(__CLASS__." :: ".__FUNCTION__." starting the loop !!");
                DB::beginTransaction();
                foreach ($ordersData as $orders) {
                    $payment_status=null;
                    Log::debug(__CLASS__." :: ".__FUNCTION__." lets check for the payment gateway used for payment ");
                    switch ($orders->payment_method) {
                        case 'Razor Pay':
                            // Call razor pay payment validation method from here
                            Log::debug(__CLASS__." :: ".__FUNCTION__." lets fetch the razor pay order id for status !!");
                            $pGatewayData = DB::table('pgateway_txn')->where('order_id', '=', $orders->order_id)->first();
                            if(isset($pGatewayData) and !empty($pGatewayData)){
                                Log::debug(__CLASS__." :: ".__FUNCTION__." lets fetch the razor pay order id ");
                                $razorpay_order_id = $pGatewayData->razorpay_order_id;
                                if(isset($razorpay_order_id) and empty($razorpay_order_id)){
                                    Log::warning(__CLASS__." :: ".__FUNCTION__." razor pay order id found as null, nothing to do for order id $orders->order_id");
                                }
                                else{
                                    Log::debug(__CLASS__." :: ".__FUNCTION__." fetching payment status from razor pay for order id $orders->order_id and razor pay order id $razorpay_order_id");
                                    $payment_status = PaymentGatewayModel::validatePaymentStatusWithRazorPay($razorpay_order_id);
                                    //if(!empty($payment_status) and $payment_status=='paid'){
                                    if(!empty($payment_status)){
                                        Log::debug(__CLASS__." :: ".__FUNCTION__." payment status found as $payment_status for order id $orders->order_id and core id $orders->orders_id");
                                        if(!PaymentGatewayModel::updateOrderPaymentStatusByCron($orders,$pGatewayData,$payment_status)){
                                            Log::error(__CLASS__." :: ".__FUNCTION__." Error while updating order payment status for order id $orders->order_id with razor pay order id $razorpay_order_id");
                                        }
                                    }
                                    else{
                                        Log::warning(__CLASS__." :: ".__FUNCTION__." payment status found as $payment_status for order_id $orders->order_id");
                                    }
                                }
                            }
                            else{
                                Log::warning(__CLASS__." :: ".__FUNCTION__." data not found from payment gateway transaction table for order id $orders->order_id");
                            }
                            
                            
                            break;
                        
                        default:
                            Log::warning(__CLASS__." :: ".__FUNCTION__." Payment method ($orders->payment_method) is not specified in switch for order id $orders->orders_id");
                            break;
                    }
                    Log::debug(__CLASS__." :: ".__FUNCTION__." switch completed !!");
                }
                Log::debug(__CLASS__." :: ".__FUNCTION__." foreach completed !!");
                Log::debug(__CLASS__." :: ".__FUNCTION__." committing transaction !!");
                DB::commit();
            }
            //return dd($ordersData);
            return true;

        } catch (Exception $e) {
            Log::error(__CLASS__." :: ".__FUNCTION__." error while processing ".$e->getTraceAsString());
        }
        return false;
    }



    // this is web hook handler for updating order payment status 
    public static function razorPayWebHookHandler(){
        return dd("I am working");
    }
    
}
