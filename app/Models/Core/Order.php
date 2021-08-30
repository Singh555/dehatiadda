<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Core\User;
class Order extends Model
{
    public function paginator($query=null)
    {

        $language_id = '1';
        if(!empty($query)){
            $orders = DB::table('orders')->where('order_id', $query)
            ->where('customers_id', '!=', '')->orderBy('orders_id', 'DESC')->paginate(20);
        }else{
           $orders = DB::table('orders')->orderBy('orders_id', 'DESC')
            ->where('customers_id', '!=', '')->paginate(20); 
        }
        

        $index = 0;
        $total_price = array();

        foreach ($orders as $orders_data) {
            $orders_products = DB::table('orders_products')->sum('final_price');

            $orders[$index]->total_price = $orders_products;

            $orders_status_history = DB::table('orders_status_history')
                ->LeftJoin('orders_status', 'orders_status.orders_status_id', '=', 'orders_status_history.orders_status_id')
                ->LeftJoin('orders_status_description', 'orders_status_description.orders_status_id', '=', 'orders_status.orders_status_id')
                ->select('orders_status_description.orders_status_name', 'orders_status_description.orders_status_id')
                ->where('orders_status_description.language_id', '=', $language_id)
                ->where('orders_id', '=', $orders_data->orders_id)
                ->where('role_id', '<=', 2)
                ->orderby('orders_status_history.orders_status_history_id', 'DESC')->limit(1)->get();

            $orders[$index]->orders_status_id = $orders_status_history[0]->orders_status_id;
            $orders[$index]->orders_status = $orders_status_history[0]->orders_status_name;
            $index++;

        }
        return $orders;
    }

public function ordered($query=null){

        $language_id = '1';
        if(!empty($query)){
           $orders = DB::table('orders')
            ->where('customers_id', '!=', '')
                 ->where('order_id',$query)
                 ->where('status','ORDERED')
                ->orderBy('orders_id', 'DESC')
                ->paginate(20); 
        }else{
           $orders = DB::table('orders')
            ->where('customers_id', '!=', '')
                 ->where('status','ORDERED')
                ->orderBy('orders_id', 'DESC')
                ->paginate(20); 
        }

        $index = 0;
        $total_price = array();

        foreach ($orders as $orders_data) {
            $orders_products = DB::table('orders_products')->sum('final_price');

            $orders[$index]->total_price = $orders_products;

            $orders_status_history = DB::table('orders_status_history')
                ->LeftJoin('orders_status', 'orders_status.orders_status_id', '=', 'orders_status_history.orders_status_id')
                ->LeftJoin('orders_status_description', 'orders_status_description.orders_status_id', '=', 'orders_status.orders_status_id')
                ->select('orders_status_description.orders_status_name', 'orders_status_description.orders_status_id')
                ->where('orders_status_description.language_id', '=', $language_id)
                ->where('orders_id', '=', $orders_data->orders_id)
                ->where('role_id', '<=', 2)
                ->orderby('orders_status_history.orders_status_history_id', 'DESC')
                    ->limit(1)->get();

            $orders[$index]->orders_status_id = $orders_status_history[0]->orders_status_id;
            $orders[$index]->orders_status = $orders_status_history[0]->orders_status_name;
            $index++;

        }
        return $orders;
    }
    
    public function pending($query=null){

        $language_id = '1';
         if(!empty($query)){
             $orders = DB::table('orders')
            ->where('customers_id', '!=', '')
                 ->where('order_id',$query)
                 ->where('status','PENDING')
                ->orderBy('orders_id', 'DESC')
                ->paginate(20);
         }else{
            $orders = DB::table('orders')
            ->where('customers_id', '!=', '')
                 ->where('status','PENDING')
                ->orderBy('orders_id', 'DESC')
                ->paginate(20); 
         }
         

        $index = 0;
        $total_price = array();

        foreach ($orders as $orders_data) {
            $orders_products = DB::table('orders_products')->sum('final_price');

            $orders[$index]->total_price = $orders_products;

            $orders_status_history = DB::table('orders_status_history')
                ->LeftJoin('orders_status', 'orders_status.orders_status_id', '=', 'orders_status_history.orders_status_id')
                ->LeftJoin('orders_status_description', 'orders_status_description.orders_status_id', '=', 'orders_status.orders_status_id')
                ->select('orders_status_description.orders_status_name', 'orders_status_description.orders_status_id')
                ->where('orders_status_description.language_id', '=', $language_id)
                ->where('orders_id', '=', $orders_data->orders_id)
                ->where('role_id', '<=', 2)
                ->orderby('orders_status_history.orders_status_history_id', 'DESC')
                    ->limit(1)->get();

            $orders[$index]->orders_status_id = $orders_status_history[0]->orders_status_id;
            $orders[$index]->orders_status = $orders_status_history[0]->orders_status_name;
            $index++;

        }
        return $orders;
    }
    public function cancelled($query=null)
    {

        $language_id = '1';
        if(!empty($query)){
            $orders = DB::table('orders')
            ->where('customers_id', '!=', '')
                 ->where('order_id',$query)
                 ->where('status','CANCEL')
                ->orderBy('orders_id', 'DESC')
                ->paginate(20);
        }else{
            $orders = DB::table('orders')
            ->where('customers_id', '!=', '')
                 ->where('status','CANCEL')
                ->orderBy('orders_id', 'DESC')
                ->paginate(20);
        }

        $index = 0;
        $total_price = array();

        foreach ($orders as $orders_data) {
            $orders_products = DB::table('orders_products')->sum('final_price');

            $orders[$index]->total_price = $orders_products;

            $orders_status_history = DB::table('orders_status_history')
                ->LeftJoin('orders_status', 'orders_status.orders_status_id', '=', 'orders_status_history.orders_status_id')
                ->LeftJoin('orders_status_description', 'orders_status_description.orders_status_id', '=', 'orders_status.orders_status_id')
                ->select('orders_status_description.orders_status_name', 'orders_status_description.orders_status_id')
                ->where('orders_status_description.language_id', '=', $language_id)
                ->where('orders_id', '=', $orders_data->orders_id)
                ->where('role_id', '<=', 2)
                ->orderby('orders_status_history.orders_status_history_id', 'DESC')
                    ->limit(1)->get();

            $orders[$index]->orders_status_id = $orders_status_history[0]->orders_status_id;
            $orders[$index]->orders_status = $orders_status_history[0]->orders_status_name;
            $index++;

        }
        return $orders;
    }
    public function completed($query=null)
    {

        $language_id = '1';
        if(!empty($query)){
            $orders = DB::table('orders')
            ->where('customers_id', '!=', '')
                 ->where('order_id',$query)
                 ->where('status','COMPLETED')
                ->orderBy('orders_id', 'DESC')
                ->paginate(20);
        }else{
           $orders = DB::table('orders')
            ->where('customers_id', '!=', '')
                 ->where('status','COMPLETED')
                ->orderBy('orders_id', 'DESC')
                ->paginate(20); 
        }

        $index = 0;
        $total_price = array();

        foreach ($orders as $orders_data) {
            $orders_products = DB::table('orders_products')->sum('final_price');

            $orders[$index]->total_price = $orders_products;

            $orders_status_history = DB::table('orders_status_history')
                ->LeftJoin('orders_status', 'orders_status.orders_status_id', '=', 'orders_status_history.orders_status_id')
                ->LeftJoin('orders_status_description', 'orders_status_description.orders_status_id', '=', 'orders_status.orders_status_id')
                ->select('orders_status_description.orders_status_name', 'orders_status_description.orders_status_id')
                ->where('orders_status_description.language_id', '=', $language_id)
                ->where('orders_id', '=', $orders_data->orders_id)
                ->where('role_id', '<=', 2)
                ->orderby('orders_status_history.orders_status_history_id', 'DESC')
                    ->limit(1)->get();

            $orders[$index]->orders_status_id = $orders_status_history[0]->orders_status_id;
            $orders[$index]->orders_status = $orders_status_history[0]->orders_status_name;
            $index++;

        }
        return $orders;
    }
    public function failed($query=null){

        $language_id = '1';
        if(!empty($query)){
           $orders = DB::table('orders')
            ->where('customers_id', '!=', '')
                 ->where('order_id',$query)
                 ->where('status','FAILED')
                ->orderBy('orders_id', 'DESC')
                ->paginate(20); 
        }else{
           $orders = DB::table('orders')
            ->where('customers_id', '!=', '')
                 ->where('status','FAILED')
                ->orderBy('orders_id', 'DESC')
                ->paginate(20); 
        }
        

        $index = 0;
        $total_price = array();

        foreach ($orders as $orders_data) {
            $orders_products = DB::table('orders_products')->sum('final_price');

            $orders[$index]->total_price = $orders_products;

            $orders_status_history = DB::table('orders_status_history')
                ->LeftJoin('orders_status', 'orders_status.orders_status_id', '=', 'orders_status_history.orders_status_id')
                ->LeftJoin('orders_status_description', 'orders_status_description.orders_status_id', '=', 'orders_status.orders_status_id')
                ->select('orders_status_description.orders_status_name', 'orders_status_description.orders_status_id')
                ->where('orders_status_description.language_id', '=', $language_id)
                ->where('orders_id', '=', $orders_data->orders_id)
                ->where('role_id', '<=', 2)
                ->orderby('orders_status_history.orders_status_history_id', 'DESC')
                    ->limit(1)->get();

            $orders[$index]->orders_status_id = $orders_status_history[0]->orders_status_id;
            $orders[$index]->orders_status = $orders_status_history[0]->orders_status_name;
            $index++;

        }
        return $orders;
    }
    public function processing($query=null){

        $language_id = '1';
         if(!empty($query)){
             $orders = DB::table('orders')
            ->where('customers_id', '!=', '')
                 ->where('order_id',$query)
                 ->where('status','PROCESSING')
                ->orderBy('orders_id', 'DESC')
                ->paginate(20);
         }else{
             $orders = DB::table('orders')
            ->where('customers_id', '!=', '')
                 ->where('status','PROCESSING')
                ->orderBy('orders_id', 'DESC')
                ->paginate(20);
         }

        $index = 0;
        $total_price = array();

        foreach ($orders as $orders_data) {
            $orders_products = DB::table('orders_products')->sum('final_price');

            $orders[$index]->total_price = $orders_products;

            $orders_status_history = DB::table('orders_status_history')
                ->LeftJoin('orders_status', 'orders_status.orders_status_id', '=', 'orders_status_history.orders_status_id')
                ->LeftJoin('orders_status_description', 'orders_status_description.orders_status_id', '=', 'orders_status.orders_status_id')
                ->select('orders_status_description.orders_status_name', 'orders_status_description.orders_status_id')
                ->where('orders_status_description.language_id', '=', $language_id)
                ->where('orders_id', '=', $orders_data->orders_id)
                ->where('role_id', '<=', 2)
                ->orderby('orders_status_history.orders_status_history_id', 'DESC')
                    ->limit(1)->get();

            $orders[$index]->orders_status_id = $orders_status_history[0]->orders_status_id;
            $orders[$index]->orders_status = $orders_status_history[0]->orders_status_name;
            $index++;

        }
        return $orders;
    }
    public function shipped($query=null){

        $language_id = '1';
        if(!empty($query)){
            $orders = DB::table('orders')
            ->where('customers_id', '!=', '')
                 ->where('order_id',$query)
                 ->where('status','SHIPPED')
                ->orderBy('orders_id', 'DESC')
                ->paginate(20);
        }else{
          $orders = DB::table('orders')
            ->where('customers_id', '!=', '')
                 ->where('status','SHIPPED')
                ->orderBy('orders_id', 'DESC')
                ->paginate(20);  
        }

        $index = 0;
        $total_price = array();

        foreach ($orders as $orders_data) {
            $orders_products = DB::table('orders_products')->sum('final_price');

            $orders[$index]->total_price = $orders_products;

            $orders_status_history = DB::table('orders_status_history')
                ->LeftJoin('orders_status', 'orders_status.orders_status_id', '=', 'orders_status_history.orders_status_id')
                ->LeftJoin('orders_status_description', 'orders_status_description.orders_status_id', '=', 'orders_status.orders_status_id')
                ->select('orders_status_description.orders_status_name', 'orders_status_description.orders_status_id')
                ->where('orders_status_description.language_id', '=', $language_id)
                ->where('orders_id', '=', $orders_data->orders_id)
                ->where('role_id', '<=', 2)
                ->orderby('orders_status_history.orders_status_history_id', 'DESC')
                    ->limit(1)->get();

            $orders[$index]->orders_status_id = $orders_status_history[0]->orders_status_id;
            $orders[$index]->orders_status = $orders_status_history[0]->orders_status_name;
            $index++;

        }
        return $orders;
    }
    

    public function detail($request){

        $language_id = '1';
        $orders_id = $request->id; 
        $ordersData = array();       
        $subtotal  = 0;
        DB::table('orders')->where('orders_id', '=', $orders_id)
            ->where('customers_id', '!=', '')->update(['is_seen' => 1]);

        $order = DB::table('orders')
            ->LeftJoin('orders_status_history', 'orders_status_history.orders_id', '=', 'orders.orders_id')
            ->LeftJoin('orders_status', 'orders_status.orders_status_id', '=', 'orders_status_history.orders_status_id')
            ->LeftJoin('orders_status_description', 'orders_status_description.orders_status_id', '=', 'orders_status.orders_status_id')
            ->where('orders_status_description.language_id', '=', $language_id)
            ->where('role_id', '<=', 2)
            ->where('orders.orders_id', '=', $orders_id)->orderby('orders_status_history.orders_status_history_id', 'DESC')->get();

        foreach ($order as $data) {
            $orders_id = $data->orders_id;

            $orders_products = DB::table('orders_products')
                ->join('products', 'products.products_id', '=', 'orders_products.products_id')
                
                ->select('orders_products.*', 'products.products_image_url as image','products.products_model as products_model')
                ->where('orders_products.orders_id', '=', $orders_id)->get();
            $i = 0;
            $total_price = 0;
            $total_tax = 0;
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
                $total_price = $total_price + $orders_products[$i]->final_price;

                $subtotal += $orders_products[$i]->final_price;

                $i++;
            }
            $data->data = $product;
            $orders_data[] = $data;
        }

        $ordersData['orders_data'] = $orders_data;
        $ordersData['total_price'] = $total_price;
        $ordersData['subtotal'] = $subtotal;

        return $ordersData;
    }

    public function currentOrderStatus($request){
        $language_id = 1;
        $status = DB::table('orders_status_history')
            ->LeftJoin('orders_status', 'orders_status.orders_status_id', '=', 'orders_status_history.orders_status_id')
            ->LeftJoin('orders_status_description', 'orders_status_description.orders_status_id', '=', 'orders_status.orders_status_id')
            ->where('orders_status_description.language_id', '=', $language_id)
            ->where('role_id', '<=', 2)
            ->orderBy('orders_status_history.orders_status_history_id', 'desc')
            ->where('orders_id', '=', $request->id)->get();
            return $status;
    }

    public function orderStatuses(){
        $language_id = 1;
        $status = DB::table('orders_status')
                ->LeftJoin('orders_status_description', 'orders_status_description.orders_status_id', '=', 'orders_status.orders_status_id')
                ->where('orders_status_description.language_id', '=', $language_id)->where('role_id', '<=', 2)->get();
        return $status;
    }

    public function updateRecord($request){
        $date_added = date('Y-m-d h:i:s');
        $orders_status = $request->orders_status;
        $old_orders_status = $request->old_orders_status;

        $comments = $request->comments;
        $orders_id = $request->orders_id;
         Log::debug(__CLASS__."::".__FUNCTION__."Called with order id $orders_id");
       try{
           DB::beginTransaction();
           $orders = DB::table('orders')->where('orders_id', '=', $orders_id)
            ->where('customers_id', '!=', '')->get();
           if(count($orders) <= 0){
               Log::debug(__CLASS__."::".__FUNCTION__."Order not found with order id $orders_id");
               session()->flash('error', "Order not found with order id $orders_id");
               return false;
           }
           
           $currentOrderStatusData = DB::table('orders_status_history')->where('orders_id', '=', $orders_id)
            ->orderBy('orders_status_history_id', 'desc')->first();
           if(isset($currentOrderStatusData->orders_status_id)){
               if(!self::validateNextOrderStatus($currentOrderStatusData->orders_status_id, $orders_status)){
                  session()->flash('error', "Order status updation not allowed with order id $orders_id");
                  return false; 
               }
           }else{
             session()->flash('error', "Old Order status not found with order id $orders_id");  
             return false; 
           }
           
          $status = DB::table('orders_status')->LeftJoin('orders_status_description', 'orders_status_description.orders_status_id', '=', 'orders_status.orders_status_id')
            ->where('orders_status_description.language_id', '=', 1)->where('role_id', '<=', 2)->where('orders_status_description.orders_status_id', '=', $orders_status)->get();
       if(count($status) <= 0){
               Log::debug(__CLASS__."::".__FUNCTION__."status not found with order status id $orders_status");
               session()->flash('error', "status not found with order status id $orders_status");
               return false;
           }
        //orders status history
        $orders_history_id = DB::table('orders_status_history')->insertGetId(
            ['orders_id' => $orders_id,
                'orders_status_id' => $orders_status,
                'date_added' => $date_added,
                'customer_notified' => '1',
                'comments' => $comments,
            ]);
        if(!$orders_history_id){
           Log::debug(__CLASS__."::".__FUNCTION__."Unable to insert order history ");
           session()->flash('error', "Unable to insert order history ");
               return false; 
        }
        // marking order as completed
        if ($orders_status == '2'){

            $orders_products = DB::table('orders_products')->where('orders_id', '=', $orders_id)->get();
            if(count($orders_products) > 0){
                foreach ($orders_products as $products_data) {
                   $updateOrderProduct = DB::table('products')->where('products_id', $products_data->products_id)->update([
                        'products_quantity' => DB::raw('products_quantity - "' . $products_data->products_quantity . '"'),
                        'products_ordered' => DB::raw('products_ordered + 1'),
                    ]);
                    if(!$updateOrderProduct){
                        Log::debug(__CLASS__."::".__FUNCTION__."Unable to update ordered product quantity");
                        session()->flash('error', 'Unable to update ordered product quantity');
                        return false; 
                    }
                }
            }
            
            Log::debug(__CLASS__." :: ".__FUNCTION__." validating for level income update by child id with order net amount ".$orders[0]->net_amount);
            if($orders[0]->net_amount >= 1000){
                Log::debug(__CLASS__." :: ".__FUNCTION__." order amount is ".$orders[0]->net_amount);
                Log::debug(__CLASS__." :: ".__FUNCTION__." updating status in  income table ");
                if(!self::updateLevelIncomeStatusToActive($orders_id, $orders[0]->customers_id)){
                      Log::debug(__CLASS__."::".__FUNCTION__."Unable to Mark customers parents level income as active");
                      session()->flash('error', 'Unable to mark customers  level income as active');
                  return false; 
                }
                Log::debug(__CLASS__." :: ".__FUNCTION__." fetching customer data found !!");
                $customer_data = User::find($orders[0]->customers_id);
                if($customer_data->id != $orders[0]->customers_id ){
                    Log::error(__CLASS__." :: ".__FUNCTION__." customer data fetching failed !!");
                    return false;
                }
                Log::debug(__CLASS__." :: ".__FUNCTION__." customer data found !!");
                Log::debug(__CLASS__." :: ".__FUNCTION__." lets check the customer status to be active after this order");
                Log::debug(__CLASS__." :: ".__FUNCTION__." customer status found as ".$customer_data->is_active);
                if($customer_data->is_active !='Y'){
                    Log::debug(__CLASS__." :: ".__FUNCTION__." lets udpate the customers status to Y");
                    $update_is_active = DB::table('users')->where('id', $customer_data->id)->update(['is_active' => 'Y']);
                    if(!$update_is_active){
                        Log::error(__CLASS__." :: ".__FUNCTION__." error while updating the is active");
                        return false;
                    }
                }

                //check for product id prebook and refund the amount in wallet 
                Log::debug(__CLASS__." :: ".__FUNCTION__." lets validate for prebook prodcut and paid by payment gateway to refund the amount");
                if($orders_products[0]->products_id == '85' && $orders[0]->payment_method=='Razor Pay' && $orders[0]->payment_status=='SUCCESS' && $orders[0]->pgateway_amount > 0){
                    Log::debug(__CLASS__." :: ".__FUNCTION__." condition matched, lets refund the amount in main wallet");
                    Log::debug(__CLASS__." :: ".__FUNCTION__." refund validation done, lets refund the amount ".$orders[0]->pgateway_amount);
                    
                    Log::debug(__CLASS__." :: ".__FUNCTION__." fetching balance !!");
                    $balance = $customer_data->m_wallet;
                    Log::debug(__CLASS__." :: ".__FUNCTION__." balance found as $balance !!");
                    $balance_after = $orders[0]->pgateway_amount + $balance;
                    Log::debug(__CLASS__." :: ".__FUNCTION__." balance after updated as $balance_after !!");
                    if(!WalletModel::creditInMainWallet($customer_data->id, $orders[0]->pgateway_amount, $balance_after, "Prebooking Refund for order id ".$orders[0]->order_id, $orders_id, 'ORDER')){
                        Log::error(__CLASS__." :: ".__FUNCTION__." error while refunding the amount ");
                        return false;
                    }
                    Log::debug(__CLASS__." :: ".__FUNCTION__." fetching block balance ");
                    $m_wallet_block = $customer_data->m_wallet_block;
                    Log::debug(__CLASS__." :: ".__FUNCTION__." m_wallet_block amount found as $m_wallet_block");
                    $m_wallet_block_after = $m_wallet_block + $orders[0]->pgateway_amount+$orders[0]->wallet_amount;
                    Log::debug(__CLASS__." :: ".__FUNCTION__." m_wallet_block amount updated as $m_wallet_block_after");
                    $update_block_amount = DB::table('users')->where('id', $customer_data->id)->update(['m_wallet_block' => $m_wallet_block_after]);
                    if(!$update_block_amount){
                        Log::error(__CLASS__." :: ".__FUNCTION__." error while updating the balance after refund! ");
                        return false;
                    }

                }
            }
           
        }

        //making it as failed 
        if ($orders_status == '3') {
             Log::debug(__CLASS__."::".__FUNCTION__."Called fetching order products data to cancel order with id $orders_id");
            $orders_products = DB::table('orders_products')->where('orders_id', '=', $orders_id)->get();
            if(count($orders_products) > 0){
                
                Log::debug(__CLASS__." :: ".__FUNCTION__." validating for refund of amount in main wallet if product ordered as preook ");
                Log::debug(__CLASS__." :: ".__FUNCTION__." payment method ".$orders[0]->payment_method);
                Log::debug(__CLASS__." :: ".__FUNCTION__." Payment Status ".$orders[0]->payment_status);
                $amount=0;
                if($orders[0]->payment_method=='Razor Pay' && $orders[0]->payment_status=='SUCCESS' && $orders[0]->pgateway_amount > 0){
                    // refund the net amount in main wallet if payment done by razor pay
                    $amount = $orders[0]->pgateway_amount;
                    Log::debug(__CLASS__." :: ".__FUNCTION__." refund validation done for razaorpay, lets refund the amount ".$amount);
                    if($orders[0]->wallet_amount > 0){
                        $amount +=$orders[0]->wallet_amount;
                        Log::debug(__CLASS__." :: ".__FUNCTION__." refund validation done for razaorpay and wallet, lets refund the net amount ".$amount);
                    }
                    if(!self::refundOrderAmount($orders,$orders_id,$amount)){
                        Log::error(__CLASS__." :: ".__FUNCTION__." error while refunding the amount for razorpay with amount! ".$amount);
                        return false;
                    }
                    
                }if($orders[0]->payment_method=='WALLET' && $orders[0]->payment_status=='SUCCESS' && $orders[0]->wallet_amount > 0){
                    // refund the net amount in main wallet if payment done by razor pay
                    $amount = $orders[0]->wallet_amount;
                    Log::debug(__CLASS__." :: ".__FUNCTION__." refund validation done for wallet, lets refund the amount ".$amount);
                    if(!self::refundOrderAmount($orders,$orders_id,$amount)){
                        Log::error(__CLASS__." :: ".__FUNCTION__." error while refunding the amount for wallet with amount! ".$amount);
                        return false;
                    }
                    
                }
                if($orders[0]->payment_method=='Cash on Delivery' && $orders[0]->cod_amount > 0 && $orders[0]->wallet_amount > 0){
                    // refund the net amount in main wallet if payment done by razor pay
                    $amount = $orders[0]->wallet_amount;
                    Log::debug(__CLASS__." :: ".__FUNCTION__." refund validation done for wallet with Cash on Delivery, lets refund the wallet amount ".$amount);
                    if(!self::refundOrderAmount($orders,$orders_id,$amount)){
                        Log::error(__CLASS__." :: ".__FUNCTION__." error while refunding the amount for wallet and Cash on Delivery with amount! ".$amount);
                        return false;
                    }
                    
                }
                
            foreach ($orders_products as $products_data) {

                $product_detail = DB::table('products')->where('products_id', $products_data->products_id)->first();
                
                $inventory_ref_id = DB::table('inventory')->insertGetId([
                    'products_id' => $products_data->products_id,
                    'stock' => $products_data->products_quantity,
                    'admin_id' => auth()->user()->id,
                    'created_at' => $date_added,
                    'stock_type' => 'in',

                ]);
                //dd($product_detail);
                if ($product_detail->products_type == 1) {
                    $product_attribute = DB::table('orders_products_attributes')
                        ->where([
                            ['orders_products_id', '=', $products_data->orders_products_id],
                            ['orders_id', '=', $products_data->orders_id],
                        ])
                        ->get();
                    if(count($product_attribute) > 0){
                        foreach ($product_attribute as $attribute) {
                            //dd($attribute->products_options,$attribute->products_options_values);
                            $prodocuts_attributes = DB::table('products_attributes')
                                ->join('products_options_descriptions', 'products_options_descriptions.products_options_id', '=', 'products_attributes.options_id')
                                ->join('products_options_values_descriptions', 'products_options_values_descriptions.products_options_values_id', '=', 'products_attributes.options_values_id')
                                ->where('products_attributes.products_id', $attribute->products_id)
                                ->where('products_options_values_descriptions.options_values', $attribute->products_options_values)
                                ->where('products_attributes.options_id', $attribute->products_options)
                                ->select('products_attributes.products_attributes_id')
                                ->first();

                            DB::table('inventory_detail')->insert([
                                'inventory_ref_id' => $inventory_ref_id,
                                'products_id' => $products_data->products_id,
                                'attribute_id' => $prodocuts_attributes->products_attributes_id,
                            ]);
                        }
                    }
                }
            }
            
           }else{
               Log::debug(__CLASS__."::".__FUNCTION__."Order product not found with order id $orders_id");
               session()->flash('error', "Order product not found with order id $orders_id ");
               return false;
           }
        }

        
        $updateStatus = DB::table('orders')->where('orders_id', '=', $orders_id)
            ->update(['status'=>$status[0]->orders_status_name,'updated_by'=>auth()->user()->id,'updated_at'=>$date_added]);
        
        if($updateStatus){
            Log::debug(__CLASS__."::".__FUNCTION__."Order status updated with order id $orders_id");
            DB::commit();
            session()->flash('success', "Order status updated with order id $orders_id ");
            return true;
        }

       } catch (\Exception $e){
           Log::debug(__CLASS__."::".__FUNCTION__."Exception occured".$e->getMessage());
           session()->flash('error', "Exception occured ".$e->getMessage());
           return false;
       }
        return false;
    }    


    //
    public function fetchorder($request)
    {
        $reportBase = $request->reportBase;
        $language_id = '1';
        $orders = DB::table('orders')
            ->LeftJoin('currencies', 'currencies.code', '=', 'orders.currency')
            ->get();

        $index = 0;
        $total_price = array();
        foreach ($orders as $orders_data) {
            $orders_products = DB::table('orders_products')
                ->select('final_price', DB::raw('SUM(final_price) as total_price'))
                ->where('orders_id', '=', $orders_data->orders_id)
                ->groupBy('final_price')
                ->get();

            $orders[$index]->total_price = $orders_products[0]->total_price;

            $orders_status_history = DB::table('orders_status_history')
                ->LeftJoin('orders_status', 'orders_status.orders_status_id', '=', 'orders_status_history.orders_status_id')
                ->LeftJoin('orders_status_description', 'orders_status_description.orders_status_id', '=', 'orders_status.orders_status_id')
                ->select('orders_status_description.orders_status_name', 'orders_status_description.orders_status_id')
                ->where('orders_id', '=', $orders_data->orders_id)
                ->where('orders_status_description.language_id', '=', $language_id)
                ->where('role_id', '<=', 2)
                ->orderby('orders_status_history.orders_status_history_id', 'DESC')->limit(1)->get();

            $orders[$index]->orders_status_id = $orders_status_history[0]->orders_status_id;
            $orders[$index]->orders_status = $orders_status_history[0]->orders_status_name;

            $index++;
        }

        $compeleted_orders = 0;
        $pending_orders = 0;
        foreach ($orders as $orders_data) {

            if ($orders_data->orders_status_id == '2') {
                $compeleted_orders++;
            }
            if ($orders_data->orders_status_id == '1') {
                $pending_orders++;
            }
        }

        $result['orders'] = $orders->chunk(10);
        $result['pending_orders'] = $pending_orders;
        $result['compeleted_orders'] = $compeleted_orders;
        $result['total_orders'] = count($orders);

        $result['inprocess'] = count($orders) - $pending_orders - $compeleted_orders;
        //add to cart orders
        $cart = DB::table('customers_basket')->get();

        $result['cart'] = count($cart);

        //Rencently added products
        $recentProducts = DB::table('products')
            ->leftJoin('products_description', 'products_description.products_id', '=', 'products.products_id')
            ->where('products_description.language_id', '=', $language_id)
            ->orderBy('products.products_id', 'DESC')
            ->paginate(8);

        $result['recentProducts'] = $recentProducts;

        //products
        $products = DB::table('products')
            ->leftJoin('products_description', 'products_description.products_id', '=', 'products.products_id')
            ->where('products_description.language_id', '=', $language_id)
            ->orderBy('products.products_id', 'DESC')
            ->get();

        //low products & out of stock
        $lowLimit = 0;
        $outOfStock = 0;
        foreach ($products as $products_data) {
            $currentStocks = DB::table('inventory')->where('products_id', $products_data->products_id)->get();
            if (count($currentStocks) > 0) {
                if ($products_data->products_type == 1) {


                } else {
                    $stockIn = 0;

                    foreach ($currentStocks as $currentStock) {
                        $stockIn += $currentStock->stock;
                    }
                    /*print $stocks;
                    print '<br>';*/
                    $orders_products = DB::table('orders_products')
                        ->select(DB::raw('count(orders_products.products_quantity) as stockout'))
                        ->where('products_id', $products_data->products_id)->get();
                    //print($product->products_id);
                    //print '<br>';
                    $stocks = $stockIn - $orders_products[0]->stockout;

                    $manageLevel = DB::table('manage_min_max')->where('products_id', $products_data->products_id)->get();
                    $min_level = 0;
                    $max_level = 0;
                    if (count($manageLevel) > 0) {
                        $min_level = $manageLevel[0]->min_level;
                        $max_level = $manageLevel[0]->max_level;
                    }

                    /*print 'min level'.$min_level;
                    print '<br>';
                    print 'max level'.$max_level;
                    print '<br>';*/

                    if ($stocks >= $min_level) {
                        $lowLimit++;
                    }
                    $stocks = $stockIn - $orders_products[0]->stockout;
                    if ($stocks == 0) {
                        $outOfStock++;
                    }

                }
            } else {
                $outOfStock++;
            }
        }

        $result['lowLimit'] = $lowLimit;
        $result['outOfStock'] = $outOfStock;
        $result['totalProducts'] = count($products);

        $customers = DB::table('customers')
            ->LeftJoin('customers_info', 'customers_info.customers_info_id', '=', 'customers.customers_id')
            ->leftJoin('images', 'images.id', '=', 'customers.customers_picture')
            ->leftJoin('image_categories', 'image_categories.image_id', '=', 'customers.customers_picture')
            ->where('image_categories.image_type', '=', 'THUMBNAIL')
            ->select('customers.created_at', 'customers_id', 'customers_firstname', 'customers_lastname', 'customers_dob', 'email', 'user_name', 'customers_default_address_id', 'customers_telephone', 'customers_fax'
                , 'password', 'customers_picture', 'path')
            ->orderBy('customers.created_at', 'DESC')
            ->get();

        $result['recentCustomers'] = $customers->take(6);
        $result['totalCustomers'] = count($customers);
        $result['reportBase'] = $reportBase;

    //  get function from other controller
    //  $myVar = new AdminSiteSettingController();
    //  $currency = $myVar->getSetting();
    //  $result['currency'] = $currency;

        return $result;
    }

    public function deleteRecord($request){
        DB::table('orders')->where('orders_id', $request->orders_id)->delete();
        DB::table('orders_products')->where('orders_id', $request->orders_id)->delete();
        return 'success';
    }

    public function reverseStock($request){
        $orders_products = DB::table('orders_products')->where('orders_id', '=', $request->orders_id)->get();

        foreach ($orders_products as $products_data) {

            $product_detail = DB::table('products')->where('products_id', $products_data->products_id)->first();
            //dd($product_detail);
            $date_added = date('Y-m-d h:i:s');
            $inventory_ref_id = DB::table('inventory')->insertGetId([
                'products_id' => $products_data->products_id,
                'stock' => $products_data->products_quantity,
                'admin_id' => auth()->user()->id,
                'created_at' => $date_added,
                'stock_type' => 'in',

            ]);
            //dd($product_detail);
            if ($product_detail->products_type == 1) {
                $product_attribute = DB::table('orders_products_attributes')
                    ->where([
                        ['orders_products_id', '=', $products_data->orders_products_id],
                        ['orders_id', '=', $products_data->orders_id],
                    ])
                    ->get();

                foreach ($product_attribute as $attribute) {
                    $prodocuts_attributes = DB::table('products_attributes')
                                ->join('products_options_descriptions', 'products_options_descriptions.products_options_id', '=', 'products_attributes.options_id')
                                ->join('products_options_values_descriptions', 'products_options_values_descriptions.products_options_values_id', '=', 'products_attributes.options_values_id')
                                ->where('products_attributes.products_id', $attribute->products_id)
                                ->where('products_options_values_descriptions.options_values', $attribute->products_options_values)
                                ->where('products_attributes.options_id', $attribute->products_options)
                                ->select('products_attributes.products_attributes_id')
                                ->first();

                    DB::table('inventory_detail')->insert([
                        'inventory_ref_id' => $inventory_ref_id,
                        'products_id' => $products_data->products_id,
                        'attribute_id' => $prodocuts_attributes->products_attributes_id,
                    ]);

                }

            }
        }
        return 'success';
    }
    
    public static function updateLevelIncomeStatusToActive($orders_id, $child_id) {
        Log::debug(__CLASS__."::".__FUNCTION__."Called with orders id $orders_id and child id $child_id");
        $updateData = DB::table('user_level_incomes')->where('child_id', '=', $child_id)->where('status', '=', 'REGISTERED')->get();
        Log::debug(__CLASS__." :: ".__FUNCTION__." data found from database with count ".count($updateData));
        
        if(count($updateData) > 0){
            $updateStatus = DB::table('user_level_incomes')->where('child_id', '=', $child_id)
                  ->where('status', '=', 'REGISTERED')->update(['status'=>'ACTIVE','updated_at'=>date('Y-m-d h:i:s'), 'actual_payemnt_date'=>date('Y-m-d h:i:s')]);
            if($updateStatus){
                Log::debug(__CLASS__." :: ".__FUNCTION__." Returning as true ");
                return true;
            }
        }
        else{
            Log::debug(__CLASS__." :: ".__FUNCTION__." inside else as data count is  ".count($updateData));
            Log::debug(__CLASS__." :: ".__FUNCTION__." returning true as data count is 0 ");
            return true;
        }
        Log::debug(__CLASS__." :: ".__FUNCTION__." Returning false in last moment");
        return false;   
    }
    
    public static function validateNextOrderStatus($oldStatus,$newStatus) {
        Log::debug(__CLASS__."::".__FUNCTION__."Called with old status id $oldStatus and  new status id $newStatus");
        if($oldStatus == '5'){
            if($newStatus == '3' || $newStatus=='7'){
                return true;
            }
            return false;
        }
        if($oldStatus == '7'){
            if($newStatus == '2' || $newStatus=='8'){
                return true;
            }
            return false;
        }
        return false;
    }
    
    protected function refundOrderAmount($orders,$orders_id,$amount) {
        $customer_data = User::find($orders[0]->customers_id);
                    if($customer_data->id != $orders[0]->customers_id ){
                        Log::error(__CLASS__." :: ".__FUNCTION__." customer data fetching failed !!");
                        session()->flash('error', "customer data fetching failed");
                        return false;
                    }
                    Log::debug(__CLASS__." :: ".__FUNCTION__." customer data found, fetching balance !!");
                    $balance = $customer_data->m_wallet;
                    Log::debug(__CLASS__." :: ".__FUNCTION__." balance found as $balance !!");
                    $balance_after = $amount + $balance;
                    Log::debug(__CLASS__." :: ".__FUNCTION__." balance after updated as $balance_after !!");
                    if(!WalletModel::creditInMainWallet($customer_data->id, $amount, $balance_after, "Order cancellation refund for order id ".$orders[0]->order_id, $orders_id, 'ORDER')){
                        Log::error(__CLASS__." :: ".__FUNCTION__." error while refunding the amount ");
                         session()->flash('error', "error while refunding the amount ");
                        return false;
                    }
                    Log::debug(__CLASS__." :: ".__FUNCTION__." fetching block balance ");
                    $m_wallet_block = $customer_data->m_wallet_block;
                    Log::debug(__CLASS__." :: ".__FUNCTION__." m_wallet_block amount found as $m_wallet_block");
                    $m_wallet_block_after = $m_wallet_block + $amount;
                    Log::debug(__CLASS__." :: ".__FUNCTION__." m_wallet_block amount updated as $m_wallet_block_after");
                    $update_block_amount = DB::table('users')->where('id', $customer_data->id)->update(['m_wallet_block' => $m_wallet_block_after]);
                    if($update_block_amount){
                        Log::debug(__CLASS__." :: ".__FUNCTION__." Success updating the balance after refund! ");
                        return true;
                    }else{
                        Log::error(__CLASS__." :: ".__FUNCTION__." error while updating the balance after refund! ");
                         session()->flash('error', "error while updating the balance after refund! ");
                        return false;
                    }
    }
    
}
