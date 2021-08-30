<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\AdminControllers\SiteSettingController;
use App\Http\Controllers\Controller;
use App\Models\Core\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use App\Models\Core\Order;

class OrdersController extends Controller
{
    //
    public function __construct( Setting $setting, Order $order )
    {
        $this->myVarsetting = new SiteSettingController($setting);
        $this->Setting = $setting;
        $this->Order = $order;
    }

    //add listingOrders
    public function display(Request $request)
    {
        $title = Lang::get("labels.ListingOrders");        

        $message = array();
        $errorMessage = array();        
        if($request->filled('query')){
           $ordersData['orders'] = $this->Order->paginator($request->input('query')); 
        }else{
            $ordersData['orders'] = $this->Order->paginator();
        }
        
        
        $ordersData['message'] = $message;
        $ordersData['errorMessage'] = $errorMessage;
        $ordersData['currency'] = $this->myVarsetting->getSetting(); 
        $result['commonContent'] = $this->Setting->commonContent();
        return view("admin.Orders.index", ["title"=>$title])->with('listingOrders', $ordersData)->with('result', $result);
    }
    //new orders
    public function newOrders(Request $request)
    {
        $title = Lang::get("labels.ListingOrders");        

        $message = array();
        $errorMessage = array();        
        
        if($request->filled('query')){
           $ordersData['orders'] = $this->Order->ordered($request->input('query')); 
        }else{
            $ordersData['orders'] = $this->Order->ordered();
        }
        $ordersData['message'] = $message;
        $ordersData['errorMessage'] = $errorMessage;
        $ordersData['currency'] = $this->myVarsetting->getSetting(); 
        $result['commonContent'] = $this->Setting->commonContent();
        return view("admin.Orders.neworders", ["title"=>$title])->with('listingOrders', $ordersData)->with('result', $result);
    }
    public function cancelOrders(Request $request)
    {
        $title = 'Cancel Orders';        

        $message = array();
        $errorMessage = array();        
        if($request->filled('query')){
           $ordersData['orders'] = $this->Order->cancelled($request->input('query')); 
        }else{
            $ordersData['orders'] = $this->Order->cancelled();
        }
        
        $ordersData['message'] = $message;
        $ordersData['errorMessage'] = $errorMessage;
        $ordersData['currency'] = $this->myVarsetting->getSetting(); 
        $result['commonContent'] = $this->Setting->commonContent();
        return view("admin.Orders.cancelledOrders", ["title"=>$title])->with('listingOrders', $ordersData)->with('result', $result);
    }
    public function pendingOrders(Request $request)
    {
        $title = 'Pending Orders';        

        $message = array();
        $errorMessage = array();        
        
        if($request->filled('query')){
           $ordersData['orders'] = $this->Order->pending($request->input('query')); 
        }else{
            $ordersData['orders'] = $this->Order->pending();
        }
        $ordersData['message'] = $message;
        $ordersData['errorMessage'] = $errorMessage;
        $ordersData['currency'] = $this->myVarsetting->getSetting(); 
        $result['commonContent'] = $this->Setting->commonContent();
        return view("admin.Orders.pendingOrders", ["title"=>$title])->with('listingOrders', $ordersData)->with('result', $result);
    }
    public function completeOrders(Request $request)
    {
        $title = 'Completed Orders';        

        $message = array();
        $errorMessage = array();        
        
        if($request->filled('query')){
           $ordersData['orders'] = $this->Order->completed($request->input('query')); 
        }else{
            $ordersData['orders'] = $this->Order->completed();
        }
        $ordersData['message'] = $message;
        $ordersData['errorMessage'] = $errorMessage;
        $ordersData['currency'] = $this->myVarsetting->getSetting(); 
        $result['commonContent'] = $this->Setting->commonContent();
        return view("admin.Orders.completeOrders", ["title"=>$title])->with('listingOrders', $ordersData)->with('result', $result);
    }
    public function failedOrders(Request $request)
    {
        $title = 'Failed Orders';        

        $message = array();
        $errorMessage = array();        
        
        if($request->filled('query')){
           $ordersData['orders'] = $this->Order->failed($request->input('query')); 
        }else{
            $ordersData['orders'] = $this->Order->failed();
        }
        $ordersData['message'] = $message;
        $ordersData['errorMessage'] = $errorMessage;
        $ordersData['currency'] = $this->myVarsetting->getSetting(); 
        $result['commonContent'] = $this->Setting->commonContent();
        return view("admin.Orders.failedOrders", ["title"=>$title])->with('listingOrders', $ordersData)->with('result', $result);
    }
    public function processingOrders(Request $request)
    {
        $title = 'Processing Orders';        

        $message = array();
        $errorMessage = array();        
        
        if($request->filled('query')){
           $ordersData['orders'] = $this->Order->processing($request->input('query')); 
        }else{
            $ordersData['orders'] = $this->Order->processing();
        }
        $ordersData['message'] = $message;
        $ordersData['errorMessage'] = $errorMessage;
        $ordersData['currency'] = $this->myVarsetting->getSetting(); 
        $result['commonContent'] = $this->Setting->commonContent();
        return view("admin.Orders.processingOrders", ["title"=>$title])->with('listingOrders', $ordersData)->with('result', $result);
    }
    public function shippedOrders(Request $request)
    {
        $title = 'Shipped Orders';        

        $message = array();
        $errorMessage = array();        
        
        if($request->filled('query')){
           $ordersData['orders'] = $this->Order->shipped($request->input('query')); 
        }else{
            $ordersData['orders'] = $this->Order->shipped();
        }
        $ordersData['message'] = $message;
        $ordersData['errorMessage'] = $errorMessage;
        $ordersData['currency'] = $this->myVarsetting->getSetting(); 
        $result['commonContent'] = $this->Setting->commonContent();
        return view("admin.Orders.shippedOrders", ["title"=>$title])->with('listingOrders', $ordersData)->with('result', $result);
    }

    //view order detail
    public function vieworder(Request $request)
    {

        $title = Lang::get("labels.ViewOrder");
        $message = array();
        $errorMessage = array();

        //orders data
        $ordersData = $this->Order->detail($request);        

        // current order status
        $orders_status_history = $this->Order->currentOrderStatus($request);  

        //all statuses 
        $orders_status = $this->Order->orderStatuses();  
        
        $ordersData['message'] = $message;
        $ordersData['errorMessage'] = $errorMessage;
        $ordersData['orders_status'] = $orders_status;
        $ordersData['orders_status_history'] = $orders_status_history;

        //get function from other controller
        $ordersData['currency'] = $this->myVarsetting->getSetting();
        $result['commonContent'] = $this->Setting->commonContent();

        //dd($ordersData);

        return view("admin.Orders.vieworder", ["title"=>$title])->with('data', $ordersData)->with('result', $result);
    }

    //update order
    public function updateOrder(Request $request)
    {

        $orders_status = $request->orders_status;
        $old_orders_status = $request->old_orders_status;

        $comments = $request->comments;
        $orders_id = $request->orders_id;

        //get function from other controller
        $setting = $this->myVarsetting->getSetting();       

        if ($old_orders_status == $orders_status) {
            return redirect()->back()->with('error', Lang::get("labels.StatusChangeError"));
        } else {
            //update order
            $orders_status = $this->Order->updateRecord($request);  
            if($orders_status){
            return redirect()->back()->with('message', Lang::get("labels.OrderStatusChangedMessage"));
            }
            return redirect()->back()->with('error', 'Order Status updating failed');
        }
        return redirect()->back()->with('error', 'Order Status updating failed');

    }

    //deleteorders
    public function deleteOrder(Request $request)
    {       
        //reverse stock
        $this->Order->reverseStock($request);     
        $this->Order->deleteRecord($request);
        
        return redirect()->back()->withErrors([Lang::get("labels.OrderDeletedMessage")]);
    }

    //view order detail
    public function invoiceprint(Request $request)
    {

        $title = Lang::get("labels.ViewOrder");
        $language_id = '1';
        $orders_id = $request->id;

        $message = array();
        $errorMessage = array();

        DB::table('orders')->where('orders_id', '=', $orders_id)
            ->where('customers_id', '!=', '')->update(['is_seen' => 1]);

        $order = DB::table('orders')
            ->LeftJoin('orders_status_history', 'orders_status_history.orders_id', '=', 'orders.orders_id')
            ->LeftJoin('orders_status', 'orders_status.orders_status_id', '=', 'orders_status_history.orders_status_id')
            ->LeftJoin('orders_status_description', 'orders_status_description.orders_status_id', '=', 'orders_status.orders_status_id')
            ->where('orders_status_description.language_id', '=', $language_id)->where('role_id', '<=', 2)
            ->where('orders.orders_id', '=', $orders_id)->orderby('orders_status_history.date_added', 'DESC')->get();

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

                //categories
                $categories = DB::table('products_to_categories')
                    ->leftjoin('categories', 'categories.categories_id', 'products_to_categories.categories_id')
                    ->leftjoin('categories_description', 'categories_description.categories_id', 'products_to_categories.categories_id')
                    ->select('categories.categories_id', 'categories_description.categories_name', 'categories.categories_image', 'categories.categories_icon', 'categories.parent_id')
                    ->where('products_id', '=', $orders_products_data->orders_products_id)
                    ->where('categories_description.language_id', '=', $language_id)->get();

                $orders_products_data->categories = $categories;

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

        $orders_status_history = DB::table('orders_status_history')
            ->LeftJoin('orders_status', 'orders_status.orders_status_id', '=', 'orders_status_history.orders_status_id')
            ->LeftJoin('orders_status_description', 'orders_status_description.orders_status_id', '=', 'orders_status.orders_status_id')
            ->where('orders_status_description.language_id', '=', $language_id)->where('role_id', '<=', 2)
            ->orderBy('orders_status_history.date_added', 'desc')
            ->where('orders_id', '=', $orders_id)->get();

        $orders_status = DB::table('orders_status')->LeftJoin('orders_status_description', 'orders_status_description.orders_status_id', '=', 'orders_status.orders_status_id')
            ->where('orders_status_description.language_id', '=', $language_id)->where('role_id', '<=', 2)->get();

        $ordersData['message'] = $message;
        $ordersData['errorMessage'] = $errorMessage;
        $ordersData['orders_data'] = $orders_data;
        $ordersData['total_price'] = $total_price;
        $ordersData['orders_status'] = $orders_status;
        $ordersData['orders_status_history'] = $orders_status_history;
        $ordersData['subtotal'] = $subtotal;

        //get function from other controller

        $ordersData['currency'] = $this->myVarsetting->getSetting();
        $result['commonContent'] = $this->Setting->commonContent();

        return view("admin.Orders.invoiceprint", ["title"=>$title])->with('data', $ordersData)->with('result', $result);

    }

}
