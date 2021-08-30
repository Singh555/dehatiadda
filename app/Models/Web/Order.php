<?php

namespace App\Models\Web;

use App\Http\Controllers\Web\AlertController;
use App\Models\Web\Cart;
use App\Models\Web\Currency;
use App\Models\Web\Index;
use App\Models\Web\Products;
use Auth;
use Hash;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Lang;
use Session;
use Log;

class Order extends Model
{

    //place_order
    public function place_order($request)
    {
        $cart = new Cart();
        $result = array();
        $cart_items = $cart->myCart($result);
        $result['cart'] = $cart_items;
        $total_quantity = 0;
        if (count($result['cart']) > 0) {
            foreach ($result['cart'] as $products) {
                $req = array();
                $attr = array();
                $req['products_id'] = $products->products_id;
                if (isset($products->attributes)) {
                    foreach ($products->attributes as $key => $value) {
                        $attr[$key] = $value->products_attributes_id;
                    }
                    $req['attributes'] = $attr;

                }
                $check = Products::getquantity($req);
                if ($products->customers_basket_quantity > $check['stock']) {
                    session(['out_of_stock' => 1]);
                    session(['out_of_stock_product' => $products->products_id]);
                    return redirect('viewcart');
                }else{
                    $total_quantity += $products->customers_basket_quantity;
                }
            }
        }

        $date_added = date('Y-m-d h:i:s');
        if (Session::get('guest_checkout') == 1) {
            $email = session('shipping_address')->email;
            $check = DB::table('users')->where('role_id', 2)->where('email', $email)->first();
            if ($check == null) {
                $customers_id = DB::table('users')
                    ->insertGetId([
                        'role_id' => 2,
                        'email' => $email = session('shipping_address')->email,
                        'password' => Hash::make('123456dfdfdf'),
                        'first_name' => session('shipping_address')->firstname,
                        'last_name' => session('shipping_address')->lastname,
                        'phone' => session('billing_address')->billing_phone,
                    ]);
                session(['customers_id' => $customers_id]);
            } else {
                $customers_id = $check->id;
                $email = $check->email;
                session(['customers_id' => $customers_id]);
            }
        } else {
            $customers_id = auth()->guard('customer')->user()->id;
            $email = auth()->guard('customer')->user()->email;
        }
        $delivery_company = session('shipping_address')->company;
        $delivery_firstname = session('shipping_address')->firstname;

        $delivery_lastname = session('shipping_address')->lastname;
        $delivery_street_address = session('shipping_address')->street;
        $delivery_suburb = '';
        $delivery_city = session('shipping_address')->city;
        $delivery_postcode = session('shipping_address')->postcode;
        $delivery_phone = session('shipping_address')->delivery_phone;

        $delivery = DB::table('zones')->where('zone_id', '=', session('shipping_address')->zone_id)->get();

        if (count($delivery) > 0) {
            $delivery_state = $delivery[0]->zone_code;
        } else {
            $delivery_state = 'other';
        }

        $country = DB::table('countries')->where('countries_id', '=', session('shipping_address')->countries_id)->get();

        $delivery_country = $country[0]->countries_name;

        $billing_firstname = session('billing_address')->billing_firstname;
        $billing_lastname = session('billing_address')->billing_lastname;
        $billing_street_address = session('billing_address')->billing_street;
        $billing_suburb = '';
        $billing_city = session('billing_address')->billing_city;
        $billing_postcode = session('billing_address')->billing_zip;
        $billing_phone = session('billing_address')->billing_phone;

        if (!empty(session('billing_company')->company)) {
            $billing_company = session('billing_address')->company;
        }

        $billing = DB::table('zones')->where('zone_id', '=', session('billing_address')->billing_zone_id)->get();

        if (count($billing) > 0) {
            $billing_state = $billing[0]->zone_code;
        } else {
            $billing_state = 'other';
        }

        $country = DB::table('countries')->where('countries_id', '=', session('billing_address')->billing_countries_id)->get();

        $billing_country = $country[0]->countries_name;

        $payment_method = $request->payment_method;
        $order_information = array();

        if (!empty($request->cc_type)) {
            $cc_type = $request->cc_type;
            $cc_owner = $request->cc_owner;
            $cc_number = $request->cc_number;
            $cc_expires = $request->cc_expires;
        } else {
            $cc_type = '';
            $cc_owner = '';
            $cc_number = '';
            $cc_expires = '';
        }

        $last_modified = date('Y-m-d H:i:s');
        $date_purchased = date('Y-m-d H:i:s');

        $shipping_cost = $request->shipping_cost;
        $shipping_method = "Shiprocket";
        $tax_rate = number_format((float) session('tax_rate'), 2, '.', '');
        $coupon_discount = number_format((float) session('coupon_discount'), 2, '.', '');
        $order_price = session('products_price');
        $net_amount = (session('products_price') + $tax_rate + $shipping_cost) - $coupon_discount;

        
        //dd($shipping_method);
        $orders_status = '5';
        //$orders_date_finished                =   $request->orders_date_finished;

        if (!empty(session('order_comments'))) {
            $comments = session('order_comments');
        } else {
            $comments = '';
        }

        
        $total_tax = number_format((float) session('tax_rate'), 2, '.', '');
        $products_tax = 1;

        $coupon_amount = 0;
        if (!empty(session('coupon')) and count(session('coupon')) > 0) {

            $code = array();
            $exclude_product_ids = array();
            $product_categories = array();
            $excluded_product_categories = array();
            $exclude_product_ids = array();

            $coupon_amount = number_format((float) session('coupon_discount'), 2, '.', '') + 0;

            foreach (session('coupon') as $coupons_data) {

                //update coupans
                $coupon_id = DB::statement("UPDATE `coupons` SET `used_by`= CONCAT(used_by,',$customers_id') WHERE `code` = '" . $coupons_data->code . "'");

            }
            $code = json_encode(session('coupon'));

        } else {
            $code = '';
            $coupon_amount = '';
        }

        //payment methods
         DB::beginTransaction();
         try{
          $cust_info = DB::table('users')->where('id', $customers_id)->where('status', '1')->first();
          $name                                           =   $delivery_firstname;
          $mobile_no                                      =   $cust_info->phone;
          $wallet_amount                                  =   0;
          $pgateway_amount                                =   0;
          $cod_amount                                     =   0;
          if($payment_method == 'cash_on_delivery'){
              $cod_amount = $net_amount;
          }else if($payment_method == 'razorpay'){
              $pgateway_amount = $net_amount;
          }else if($payment_method == 'wallet'){
              $wallet_amount = $net_amount;
          }
          
          
        if($payment_method == 'cash_on_delivery'){
                                  Log::debug('in cod'.$payment_method);
                                  $payments_setting = $this->payments_setting_for_cod();
                                  $payment_method_name =  'Cash on Delivery';
                                  $payment_method = 'Cash on Delivery';
                                  $payment_status='PENDING';
                                  $status = 'ORDERED';
                                  $pgateway_amount = 0;

                              } else if($payment_method == 'razorpay'){
                                  Log::debug('in razror pay'.$payment_method);
                                  $payments_setting = $this->payments_setting_for_razorpay();
                                  Log::debug('in razror pay settings'.json_encode($payments_setting));
                                  $payment_method_name = 'Razor Pay';
                                  $payment_status='PENDING';
                                  $status='PENDING';
                                  $orders_status = '1';
                                  if($pgateway_amount < 1){
                                      Log::error("Error Occured please try again ! Payment Gateway Amount is less than 1. i.e. $pgateway_amount");
                                      session()->flash('error', "Error Occured please try again ! Payment is less than 1.");
                                      return false;
                                  }

                              } else if($payment_method == 'wallet'){
                                             Log::debug('in Wallet '.$payment_method);
                                             $payments_setting = $this->payments_setting_for_wallet();
                                             Log::debug('in Wallet settings'.json_encode($payments_setting));
                                             $payment_method_name = 'WALLET';
                                             if($wallet_amount == $net_amount){
                                                 $payment_status = 'SUCCESS';
                                                 $status='ORDERED';
                                             } else {
                                                 $payment_status = 'PENDING';
                                                 $status='PENDING';
                                                 $orders_status = '1';
                                             }
                                             

                             }  else {
                                  Log::error("Unknown payment method i.e. $payment_method ");
                                  session()->flash('error', "Error Occured please try again ! Unknown payment method.");
                                  return false;
                              }
        
        if ($payment_method == 'banktransfer') {
            session(['banktransfer' => 'yes']);
        }else{
            session(['banktransfer' => 'no']);
        }
          $order_id = generateOrderId();
                                  if(empty($payment_method_name)){
                                      //$paymentMethodName = "Razorpay";
                                       session()->flash('error', "Error Occured please try again ! Unknown payment method.");
                                  return false;
                                  }
                                  Log::debug("Order Id : $order_id");
                                  if(empty($order_id )){
                                      Log::error("Order Id Not Generated ");
                                     session()->flash('error', "Error Occured please try again !");
                                  return false;
                                  }
                                  Log::debug("Order Id : $order_id");
                  
                                  
                                  if($wallet_amount > 0){
                              if($cust_info->m_wallet >= $wallet_amount){
                                  $payable_amount = $net_amount - $wallet_amount;
                                  Log::debug("Payable Amount $payable_amount ");
                                  Log::debug("Form Wallet Amount $wallet_amount ");
                                  $balance_after = $cust_info->m_wallet - $wallet_amount;
                                  $order_type = "PRODUCT ORDER";
                                  $txn_desc = "Product Order";
                                  if(!\App\Models\Core\WalletModel::debitFromMainWallet($customers_id, $wallet_amount, $balance_after, $txn_desc, $order_id, $order_type)){
                                      Log::error("error while debiting Wallet !!!");
                                       session()->flash('error', "Order failed ! Wallet debiting failed");
                                  return false;
                                  }
                              } else {
                                  Log::error("Wallet balance ".$cust_info->m_wallet." is less than wallet $wallet_amount used !!");
                                  session()->flash('error', "Wallet can be used up to ".$cust_info->m_wallet);
                                  return false;
                              }
                          }
                                  

            $orders_id = DB::table('orders')->insertGetId(
                ['customers_id' => $customers_id,
                    'customers_name' => $delivery_firstname . ' ' . $delivery_lastname,
                    'customers_street_address' => $delivery_street_address,
                    'customers_suburb' => $delivery_suburb,
                    'customers_city' => $delivery_city,
                    'customers_postcode' => $delivery_postcode,
                    'customers_state' => $delivery_state,
                    'customers_country' => $delivery_country,
                    //'customers_telephone' => $customers_telephone,
                    'email' => $email,
                    // 'customers_address_format_id' => $delivery_address_format_id,

                    'delivery_name' => $delivery_firstname . ' ' . $delivery_lastname,
                    'delivery_street_address' => $delivery_street_address,
                    'delivery_suburb' => $delivery_suburb,
                    'delivery_city' => $delivery_city,
                    'delivery_postcode' => $delivery_postcode,
                    'delivery_state' => $delivery_state,
                    'delivery_country' => $delivery_country,
                    // 'delivery_address_format_id' => $delivery_address_format_id,

                    'billing_name' => $billing_firstname . ' ' . $billing_lastname,
                    'billing_street_address' => $billing_street_address,
                    'billing_suburb' => $billing_suburb,
                    'billing_city' => $billing_city,
                    'billing_postcode' => $billing_postcode,
                    'billing_state' => $billing_state,
                    'billing_country' => $billing_country,
                    //'billing_address_format_id' => $billing_address_format_id,

                    'payment_method' => $payment_method_name,
                    'cc_type' => $cc_type,
                    'cc_owner' => $cc_owner,
                    'cc_number' => $cc_number,
                    'cc_expires' => $cc_expires,
                    'last_modified' => $last_modified,
                    'date_purchased' => $date_purchased,
                    'order_price' => $order_price,
                    'wallet_amount'  => $wallet_amount,
                    'pgateway_amount'  => $pgateway_amount,
                    'cod_amount'  => $cod_amount,
                    'total_quantity'  => $total_quantity,
                    'net_amount'  => $net_amount,
                    'shipping_cost' => $shipping_cost,
                    'shipping_method' => $shipping_method,
                    // 'orders_status' => $orders_status,
                    //'orders_date_finished'  => $orders_date_finished,
                    'currency' => 'INR',
                    'currency_value' => '1',
                    'order_information' => json_encode($order_information),
                    'coupon_code' => $code,
                    'coupon_amount' => $coupon_amount,
                    'total_tax' => $total_tax,
                    'ordered_source' => '1',
                    'delivery_phone' => $delivery_phone,
                    'billing_phone' => $billing_phone,
                    'order_id'              => $order_id,
                    'payment_status'	 => $payment_status,
                    'status'	 => $status,
                ]);

            //orders status history
            $orders_history_id = DB::table('orders_status_history')->insertGetId(
                ['orders_id' => $orders_id,
                    'orders_status_id' => $orders_status,
                    'date_added' => $date_added,
                    'customer_notified' => '1',
                    'comments' => $comments,
                ]);
                
            foreach ($cart_items as $products) {
                //get products info
                $orders_products_id = DB::table('orders_products')->insertGetId(
                    [
                        'orders_id' => $orders_id,
                        'products_id' => $products->products_id,
                        'products_name' => $products->products_name,
                        'products_price' => $products->price,
                        'final_price' => $products->final_price * $products->customers_basket_quantity,
                        'products_tax' => $products_tax,
                        'products_quantity' => $products->customers_basket_quantity,
                    ]);
                $inventory_ref_id = DB::table('inventory')->insertGetId([
                    'products_id' => $products->products_id,
                    'reference_code' => '',
                    'stock' => $products->customers_basket_quantity,
                    'admin_id' => 0,
                    'added_date' => time(),
                    'purchase_price' => 0,
                    'stock_type' => 'out',
                ]);

                if (Session::get('guest_checkout') == 1) {
                    DB::table('customers_basket')->where('session_id', Session::getId())->where('products_id', $products->products_id)->update(['is_order' => '1']);

                } else {
                    DB::table('customers_basket')->where('customers_id', $customers_id)->where('products_id', $products->products_id)->update(['is_order' => '1']);

                }

                if (!empty($products->attributes) and count($products->attributes)>0) {

                    foreach ($products->attributes as $attribute) {
                        DB::table('orders_products_attributes')->insert(
                            [
                                'orders_id' => $orders_id,
                                'products_id' => $products->products_id,
                                'orders_products_id' => $orders_products_id,
                                'products_options' => $attribute->attribute_name,
                                'products_options_values' => $attribute->attribute_value,
                                'options_values_price' => $attribute->values_price,
                                'price_prefix' => $attribute->prefix,
                            ]);

                        DB::table('inventory_detail')->insert([
                            'inventory_ref_id' => $inventory_ref_id,
                            'products_id' => $products->products_id,
                            'attribute_id' => $attribute->products_attributes_id,
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
                    //$total_tax     = $total_tax+$orders_products_data->products_tax;
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

            //notification/email
            /*
            $myVar = new AlertController();
            $alertSetting = $myVar->orderAlert($ordersData);
           */
            if (session('step') == '3') {
                session(['step' => array()]);
            }

            session(['orders_id' => $orders_id]);
            session(['paymentResponseData' => '']); 
            
            session(['paymentResponse'=>'']);
            session(['payment_json','']);

            //change status of cart products
            if (Session::get('guest_checkout') == 1) {
                DB::table('customers_basket')->where('session_id', Session::getId())->update(['customers_id' => Session::get('customers_id')]);
                DB::table('customers_basket')->where('session_id', Session::getId())->update(['is_order' => '1']);
            } else {
                DB::table('customers_basket')->where('customers_id', auth()->guard('customer')->user()->id)->update(['is_order' => '1']);
            }   
            
            if($wallet_amount > 0 && $pgateway_amount > 0){
                          if($net_amount != ($wallet_amount + $pgateway_amount)){
                              Log::error(__CLASS__." :: ".__FUNCTION__." Order placing failed ! Wallet amount : $wallet_amount + P Gateway : $pgateway_amount != Net Amount : $net_amount");
                              session()->flash("error","Order failed !");
                                              return false;
                          }
                      } else if($pgateway_amount > 0 && $pgateway_amount != $net_amount){
                          Log::error(__CLASS__." :: ".__FUNCTION__." Order placing failed ! P Gateway : $pgateway_amount != Net Amount : $net_amount");
                         session()->flash("error","Order failed !");
                                              return false;
                      } else if($wallet_amount > 0 && ($wallet_amount + $cod_amount) != $net_amount){
                          Log::error(__CLASS__." :: ".__FUNCTION__." Order placing failed ! Wallet Amount : $wallet_amount + COD Amount : $cod_amount != Net Amount : $net_amount");
                          session()->flash("error","Order failed !");
                                              return false;
                      }
                                      if($payment_method == "razorpay"){
                                          $txn_id = generateGatewayTxnId();
                                          Log::debug(__CLASS__." :: ".__FUNCTION__." pgateway_amount = $pgateway_amount");
                                          $payment_data = PaymentGatewayModel::initiatePayment($customers_id, $customers_id, $orders_id, $order_id, $txn_id, $name, $mobile_no, $cust_info->email, $pgateway_amount, "Website");
                                          if($payment_data){
                                              if(isset($payment_data["order_id"])){
                                                  $data = array(
                                                      'razorpay_order_id' => $payment_data["order_id"],
                                                      'order_id' => $order_id,
                                                      'txn_id' => $txn_id,
                                                      'amount' => $pgateway_amount,
                                                  );
                                                  DB::commit();
                                                  Log::info(__CLASS__." :: ".__FUNCTION__." Order placing success with razorpay order id ".$payment_data["order_id"]);
                                                  
                                                 return $data;
                                              } else {
                                                  Log::error(__CLASS__." :: ".__FUNCTION__." Order placing failed ! razorpay_order_id not set .");
                                                   session()->flash("error","Order failed !");
                                              return false;
                                              }
                                          } else {
                                              Log::error(__CLASS__." :: ".__FUNCTION__." error while placing order !!!!!");
                                              session()->flash("error","Order failed !");
                                              return false;
                                          }
                                      }



                                     
                              DB::commit();         
                              session()->flash("success","Order has been placed successfully.");

            return true;
        }catch(\Exception $e){
            Log::error(__CLASS__."::".__FUNCTION__." Exception occured ".$e->getTraceAsString());
        }
       return false;
    }

    public function orders($request)
    {
        $index = new Index();
        $result = array();

        $result['commonContent'] = $index->commonContent();

        //orders
        if (Session::get('guest_checkout') == 1) {
            $orders = DB::table('orders')->orderBy('date_purchased', 'DESC')->where('customers_id', '=', Session::get('customers_id'))->get();
        } else {
            $orders = DB::table('orders')->orderBy('date_purchased', 'DESC')->where('customers_id', '=', auth()->guard('customer')->user()->id)->get();
        }
        $index = 0;
        $total_price = array();

        foreach ($orders as $orders_data) {
            $orders_products = DB::table('orders_products')
                ->select('final_price', DB::raw('SUM(final_price) as total_price'))
                ->where('orders_id', '=', $orders_data->orders_id)
                ->get();

            $orders[$index]->total_price = $orders_products[0]->total_price;

            $orders_status_history = DB::table('orders_status_history')
                ->LeftJoin('orders_status', 'orders_status.orders_status_id', '=', 'orders_status_history.orders_status_id')
                ->LeftJoin('orders_status_description', 'orders_status_description.orders_status_id', '=', 'orders_status.orders_status_id')
                ->select('orders_status_description.orders_status_name', 'orders_status.orders_status_id')
                ->where('orders_id', '=', $orders_data->orders_id)->where('orders_status_description.language_id', session('language_id'))->orderby('orders_status_history.orders_status_history_id', 'DESC')->limit(1)->get();

            $orders[$index]->orders_status_id = $orders_status_history[0]->orders_status_id;
            $orders[$index]->orders_status = $orders_status_history[0]->orders_status_name;
            $index++;

        }

             
        $result['orders'] = $orders;
        return $result;
    }

    public function viewOrder($request, $id)
    {
        $index = new Index();

        $title = array('pageTitle' => Lang::get("website.View Order"));
        $result = array();
        $result['commonContent'] = $index->commonContent();

        //orders
        if (Session::get('guest_checkout') == 1) {
            $orders = DB::table('orders')
                ->orderBy('date_purchased', 'DESC')
                ->where('orders_id', '=', $id)->where('customers_id', '=', Session::get('customers_id'))->get();
        } else {
            $orders = DB::table('orders')
                ->orderBy('date_purchased', 'DESC')
                ->where('orders_id', '=', $id)->where('customers_id', auth()->guard('customer')->user()->id)->get();
        }
        if (count($orders) > 0) {
            $index = 0;
            foreach ($orders as $orders_data) {

                $orders_status_history = DB::table('orders_status_history')
                    ->LeftJoin('orders_status', 'orders_status.orders_status_id', '=', 'orders_status_history.orders_status_id')
                    ->LeftJoin('orders_status_description', 'orders_status_description.orders_status_id', '=', 'orders_status.orders_status_id')
                    ->select('orders_status_description.orders_status_name', 'orders_status.orders_status_id')
                    ->where('orders_id', '=', $orders_data->orders_id)->where('orders_status_description.language_id', session('language_id'))->orderby('orders_status_history.orders_status_history_id', 'DESC')->limit(1)->get();

                $products_array = array();
                $index2 = 0;
                $order_products = DB::table('orders_products')
                    ->join('products', 'products.products_id', '=', 'orders_products.products_id')
                    ->select('products.products_image_url as image', 'products.products_model as model', 'orders_products.*')
                    ->where('orders_products.orders_id', $orders_data->orders_id)->groupBy('products.products_id')->get();

                foreach ($order_products as $products) {
                    array_push($products_array, $products);
                    $attributes = DB::table('orders_products_attributes')->where([['orders_id', $products->orders_id], ['orders_products_id', $products->orders_products_id]])->get();
                    if (count($attributes) == 0) {
                        $attributes = $attributes;
                    }

                    $products_array[$index2]->attributes = $attributes;
                    $index2++;

                }

                $orders_status_history = DB::table('orders_status_history')
                    ->LeftJoin('orders_status', 'orders_status.orders_status_id', '=', 'orders_status_history.orders_status_id')
                    ->LeftJoin('orders_status_description', 'orders_status_description.orders_status_id', '=', 'orders_status.orders_status_id')
                    ->select('orders_status_description.orders_status_name', 'orders_status.orders_status_id')
                    ->where('orders_id', '=', $orders_data->orders_id)->where('orders_status_description.language_id', session('language_id'))->orderby('orders_status_history.orders_status_history_id', 'DESC')->limit(1)->get();

                $orders[$index]->statusess = $orders_status_history;
                $orders[$index]->products = $products_array;
                $orders[$index]->orders_status_id = $orders_status_history[0]->orders_status_id;
                $orders[$index]->orders_status = $orders_status_history[0]->orders_status_name;
                $index++;

            }

            $result['orders'] = $orders;

             //check if payment type direck bank
            $bankdetail = array();

            if($orders[0]->payment_method == 'banktransfer'){
                $payments_setting = $this->payments_setting_for_directbank();    
                
                $bankdetail = array(
                    'account_name' => $payments_setting['account_name']->value,
                    'account_number' => $payments_setting['account_number']->value,
                    'payment_method' => $payments_setting['account_name']->payment_method,
                    'bank_name' => $payments_setting['bank_name']->value,
                    'short_code' => $payments_setting['short_code']->value,
                    'iban' => $payments_setting['iban']->value,
                    'swift' => $payments_setting['swift']->value,
                );
            }
        

            $result['bankdetail'] = $bankdetail;  

            $result['res'] = "view-order";
            return $result;
        } else {
            $result['res'] = "order";
            return "order";
        }
    }

    public function calculateTax($tax_zone_id)
    {
        $cart = new Cart();

        $result = array();

        if ($tax_zone_id == -1) {
            $tax = 0;
        } else {

            $cart = $cart->myCart($result);

            $index = '0';
            $total_tax = '0';

            foreach ($cart as $products_data) {

                $final_price = $products_data->final_price;

                $products = DB::table('products')
                    ->LeftJoin('tax_rates', 'tax_rates.tax_class_id', '=', 'products.products_tax_class_id')
                    ->where('tax_rates.tax_zone_id', $tax_zone_id)
                    ->where('products_id', $products_data->products_id)->get();

                if (count($products) > 0) {
                    $tax_value = $products[0]->tax_rate / 100 * $final_price;
                    $total_tax = $total_tax + $tax_value;
                    $index++;
                }

            }

            if ($total_tax > 0) {
                $tax = $total_tax;
            } else {
                $tax = '0';
            }
        }

        return $tax;

    }

    public function getOrders()
    {
        $orders = DB::select(DB::raw('SELECT orders_id FROM orders WHERE YEARWEEK(CURDATE()) BETWEEN YEARWEEK(date_purchased) AND YEARWEEK(date_purchased)'));
        return $orders;
    }

    public function allOrders()
    {
        $allOrders = DB::table('orders')->get();
        return $allOrders;
    }

    public function mostOrders($orders_data)
    {
        $mostOrdered = DB::table('orders_products')
            ->select('orders_products.products_id')
            ->where('orders_id', $orders_data->orders_id)
            ->get();
        return $mostOrdered;
    }

    public function countCompare()
    {
        $count = DB::table('compare')->where('customer_id', auth()->guard('customer')->user()->id)->count();
        return $count;
    }

    public function getPages($request)
    {
        $pages = DB::table('pages')
            ->leftJoin('pages_description', 'pages_description.page_id', '=', 'pages.page_id')
            ->where([['pages.status', '1'], ['type', 2], ['pages_description.language_id', session('language_id')], ['pages.slug', $request->name]])
            ->get();
            
        return $pages;
    }

    public function payments_setting_for_brain_tree()
    {
        $payments_setting = DB::table('payment_methods_detail')
            ->leftjoin('payment_description', 'payment_description.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
            ->leftjoin('payment_methods', 'payment_methods.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
            ->select('payment_methods_detail.*', 'payment_description.name', 'payment_methods.environment', 'payment_methods.status', 'payment_methods.payment_method')
            ->where('language_id', session('language_id'))
            ->where('payment_description.payment_methods_id', 1)
            ->orwhere('language_id', 1)
            ->where('payment_description.payment_methods_id', 1)
            ->get()->keyBy('key');
        return $payments_setting;
    }

    public function payments_setting_for_stripe()
    {
        $payments_setting = DB::table('payment_methods_detail')
            ->leftjoin('payment_description', 'payment_description.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
            ->leftjoin('payment_methods', 'payment_methods.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
            ->select('payment_methods_detail.*', 'payment_description.name', 'payment_methods.environment', 'payment_methods.status', 'payment_methods.payment_method')
            ->where('language_id', session('language_id'))
            ->where('payment_description.payment_methods_id', 2)
            ->orwhere('language_id', 1)
            ->where('payment_description.payment_methods_id', 2)
            ->get()->keyBy('key');
        return $payments_setting;
    }

    public function payments_setting_for_cod()
    {
        $payments_setting = DB::table('payment_description')
            ->leftjoin('payment_methods', 'payment_methods.payment_methods_id', '=', 'payment_description.payment_methods_id')
            ->select('payment_description.name', 'payment_methods.environment', 'payment_methods.status', 'payment_methods.payment_method')
            ->where('language_id', 1)
            ->where('payment_description.payment_methods_id', 1)
            ->first();
        return $payments_setting;
    }


    public function payments_setting_for_instamojo()
    {
        $payments_setting = DB::table('payment_methods_detail')
            ->leftjoin('payment_description', 'payment_description.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
            ->leftjoin('payment_methods', 'payment_methods.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
            ->select('payment_methods_detail.*', 'payment_description.name', 'payment_methods.environment', 'payment_methods.status', 'payment_methods.payment_method')
            ->where('language_id', session('language_id'))
            ->where('payment_description.payment_methods_id', 5)
            ->orwhere('language_id', 1)
            ->where('payment_description.payment_methods_id', 5)
            ->get()->keyBy('key');
        return $payments_setting;
    }

    public function payments_setting_for_hyperpay()
    {
        $payments_setting = DB::table('payment_methods_detail')
            ->leftjoin('payment_description', 'payment_description.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
            ->leftjoin('payment_methods', 'payment_methods.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
            ->select('payment_methods_detail.*', 'payment_description.name', 'payment_methods.environment', 'payment_methods.status', 'payment_methods.payment_method')
            ->where('language_id', session('language_id'))
            ->where('payment_description.payment_methods_id', 6)
            ->orwhere('language_id', 1)
            ->where('payment_description.payment_methods_id', 6)
            ->get()->keyBy('key');
        return $payments_setting;
    }

    public function payments_setting_for_razorpay()
    {
        $payments_setting = DB::table('payment_methods_detail')
            ->leftjoin('payment_description', 'payment_description.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
            ->leftjoin('payment_methods', 'payment_methods.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
            ->select('payment_methods_detail.*', 'payment_description.name', 'payment_methods.environment', 'payment_methods.status', 'payment_methods.payment_method')
            ->where('language_id', 1)
            ->where('payment_description.payment_methods_id', 2)
            ->get()->keyBy('key');
        return $payments_setting;
    }
    public function payments_setting_for_wallet(){
      $payments_setting = DB::table('payment_description')
        ->leftjoin('payment_methods', 'payment_methods.payment_methods_id', '=', 'payment_description.payment_methods_id')
        ->select('payment_description.name', 'payment_methods.environment', 'payment_methods.status', 'payment_methods.payment_method')
        ->where('language_id', 1)
        ->where('payment_description.payment_methods_id',3)->first();

      if(empty($payments_setting->name)){
        $payments_setting = DB::table('payment_description')
        ->leftjoin('payment_methods', 'payment_methods.payment_methods_id', '=', 'payment_description.payment_methods_id')
        ->select('payment_description.name', 'payment_methods.environment', 'payment_methods.status', 'payment_methods.payment_method')
        ->where('language_id', 1)
        ->where('payment_description.payment_methods_id',1)->first();
      }
      return $payments_setting;
    }

    public function payments_setting_for_paytm()
    {
        $payments_setting = DB::table('payment_methods_detail')
            ->leftjoin('payment_description', 'payment_description.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
            ->leftjoin('payment_methods', 'payment_methods.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
            ->select('payment_methods_detail.*', 'payment_description.name', 'payment_methods.environment', 'payment_methods.status', 'payment_methods.payment_method')
            ->where('language_id', session('language_id'))
            ->where('payment_description.payment_methods_id', 8)
            ->orwhere('language_id', 1)
            ->where('payment_description.payment_methods_id', 8)
            ->get()->keyBy('key');
        return $payments_setting;
    }
    
    public function payments_setting_for_directbank()
    {
        $payments_setting = DB::table('payment_methods_detail')
            ->leftjoin('payment_description', 'payment_description.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
            ->leftjoin('payment_methods', 'payment_methods.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
            ->select('payment_methods_detail.*', 'payment_description.name', 'payment_methods.environment', 'payment_methods.status', 
            'payment_methods.payment_method', 'payment_description.sub_name_1')
            ->where('language_id', session('language_id'))
            ->where('payment_description.payment_methods_id', 9)
            ->orwhere('language_id', 1)
            ->where('payment_description.payment_methods_id', 9)
            ->get()->keyBy('key');
        return $payments_setting;
    }

    public function payments_setting_for_paystack()
    {
        $payments_setting = DB::table('payment_methods_detail')
            ->leftjoin('payment_description', 'payment_description.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
            ->leftjoin('payment_methods', 'payment_methods.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
            ->select('payment_methods_detail.*', 'payment_description.name', 'payment_methods.environment', 'payment_methods.status', 
            'payment_methods.payment_method')
            ->where('language_id', session('language_id'))
            ->where('payment_description.payment_methods_id', 10)
            ->orwhere('language_id', 1)
            ->where('payment_description.payment_methods_id', 10)
            ->get()->keyBy('key');
        return $payments_setting;
    }

    public function payments_setting_for_midtrans()
    {
        $payments_setting = DB::table('payment_methods_detail')
            ->leftjoin('payment_description', 'payment_description.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
            ->leftjoin('payment_methods', 'payment_methods.payment_methods_id', '=', 'payment_methods_detail.payment_methods_id')
            ->select('payment_methods_detail.*', 'payment_description.name', 'payment_methods.environment', 'payment_methods.status', 
            'payment_methods.payment_method')
            ->where('language_id', session('language_id'))
            ->where('payment_description.payment_methods_id', 11)
            ->orwhere('language_id', 1)
            ->where('payment_description.payment_methods_id', 11)
            ->get()->keyBy('key');
        return $payments_setting;
    }

    public function getCountries($countries_id)
    {
        $countries = DB::table('countries')->where('countries_id', '=', $countries_id)->get();
        return $countries;
    }

    public function getZones($zone_id)
    {
        $zones = DB::table('zones')->where('zone_id', '=', $zone_id)->get();
        return $zones;
    }

    public function getShippingMethods()
    {
        $shippings = DB::table('shipping_methods')->get();
        return $shippings;
    }

    public function getShippingDetail($shipping_methods)
    {
        $shippings_detail = DB::table('shipping_description')->where('language_id', Session::get('language_id'))->where('table_name', $shipping_methods->table_name)
            ->orwhere('language_id', 1)->where('table_name', $shipping_methods->table_name)->get();
        return $shippings_detail;
    }

    public function getUpsShipping()
    {
        $ups_shipping = DB::table('ups_shipping')->where('ups_id', '=', '1')->get();
        return $ups_shipping;
    }

    public function getUpsShippingRate()
    {
        $ups_shipping = DB::table('flate_rate')->where('id', '=', '1')->get();
        return $ups_shipping;
    }

    public function priceByWeight($weight)
    {
        $priceByWeight = DB::table('products_shipping_rates')->where('weight_from', '<=', $weight)->where('weight_to', '>=', $weight)->get();
        return $priceByWeight;
    }

    public function braintreeDescription()
    {
        $braintree_description = DB::table('payment_description')->where([['payment_name', 'Braintree'], ['language_id', Session::get('language_id')]])
            ->orwhere([['payment_name', 'Braintree'], ['language_id', 1]])->get();
        return $braintree_description;
    }

    public function stripeDescription()
    {
        $stripe_description = DB::table('payment_description')->where([['payment_name', 'Stripe'], ['language_id', Session::get('language_id')]])
            ->orwhere([['payment_name', 'Stripe'], ['language_id', 1]])->get();
        return $stripe_description;
    }

    public function codDescription()
    {
        $cod_description = DB::table('payment_description')->where([['payment_name', 'Cash On Delivery'], ['language_id', Session::get('language_id')]])
            ->orwhere([['payment_name', 'Cash On Delivery'], ['language_id', 1]])->get();
        return $cod_description;
    }

 
    public function instamojoDescription()
    {
        $instamojo_description = DB::table('payment_description')->where([['payment_name', 'Instamojo'], ['language_id', Session::get('language_id')]])
            ->orwhere([['payment_name', 'Instamojo'], ['language_id', 1]])->get();
        return $instamojo_description;
    }

    public function hyperpayDescription()
    {
        $hyperpay_description = DB::table('payment_description')->where([['payment_name', 'hyperpay'], ['language_id', Session::get('language_id')]])
            ->orwhere([['payment_name', 'hyperpay'], ['language_id', 1]])->get();
        return $hyperpay_description;
    }

    public function ordersCheck($request)
    {
        if (Session::get('guest_checkout') == 1) {
            $ordersCheck = DB::table('orders')->where(['customers_id' => Session::get('customers_id')], ['orders_id' => $request->orders_id])->get();
        } else {
            $ordersCheck = DB::table('orders')->where(['customers_id' => auth()->guard('customer')->user()->id], ['orders_id' => $request->orders_id])->get();
        }
        return $ordersCheck;
    }

    public function InsertOrdersCheck($request, $date_added, $comments)
    {
        $orders_history_id = DB::table('orders_status_history')->insertGetId(
            ['orders_id' => $request->orders_id,
                'orders_status_id' => $request->orders_status_id,
                'date_added' => $date_added,
                'customer_notified' => '1',
                'comments' => $comments,
            ]);
        return $orders_history_id;
    }

}
