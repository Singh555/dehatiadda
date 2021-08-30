<?php
namespace App\Http\Controllers\App;

//use Mail;
//validator is builtin class in laravel
use App\Models\AppModels\Cart;
//for password encryption or hash protected
use App\Models\AppModels\Product;

//for authenitcate login data
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
//for requesting a value
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Helpers\HttpStatus;
//for Carbon a value
use DB;
use Log;

class CartController extends Controller
{


    //addToCart
    public function addToCart(Request $request)
    {
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
            
            $validator = Validator::make($request->all(), [
            'quantity' => 'required|numeric|min:1',
            'products_id' => 'required',
            'attributes.*' => 'nullable',
            
            ]);
        
            if ($validator->fails()) {
                return returnResponse($validator->errors(), HttpStatus::HTTP_UNPROCESSABLE_ENTITY);
            }
            
         $inventory_ref_id = '';
      $result = array();$stocks = 0;
      $products_id = $request->input('products_id');
      $quantity = $request->input('quantity');
      
      Log::debug(__CLASS__."::".__FUNCTION__."called with product id $products_id and quantity $quantity");
      
      $productsType = DB::table('products')->where('products_id', $products_id)->get();
      //check products type
      if ($productsType[0]->products_type == 1) {
          $attributes = array_filter($request->input('attributes'));
          $attributeid = implode(',', $attributes);

          $postAttributes = count($attributes);

          $inventories = DB::table('inventory')->where('products_id', $products_id)->get();
          $reference_ids = array();
          $stocks = 0;
          $stockIn = 0; $stockOut = 0;
          foreach ($inventories as $inventory) {

              $totalAttribute = DB::table('inventory_detail')->where('inventory_detail.inventory_ref_id', '=', $inventory->inventory_ref_id)->get();
              $totalAttributes = count($totalAttribute);

              if ($postAttributes > $totalAttributes) {
                  $count = $postAttributes;
              } elseif ($postAttributes < $totalAttributes or $postAttributes == $totalAttributes) {
                  $count = $totalAttributes;
              }

              $individualStock = DB::table('inventory')->leftjoin('inventory_detail', 'inventory_detail.inventory_ref_id', '=', 'inventory.inventory_ref_id')
                  ->selectRaw('inventory.*')
                  ->whereIn('inventory_detail.attribute_id', [$attributeid])
                  ->where(DB::raw('(select count(*) from `inventory_detail` where `inventory_detail`.`attribute_id` in (' . $attributeid . ') and `inventory_ref_id`= "' . $inventory->inventory_ref_id . '")'), '=', $count)
                  ->where('inventory.inventory_ref_id', '=', $inventory->inventory_ref_id)
                  ->groupBy('inventory_detail.inventory_ref_id')
                  ->get();

              if (count($individualStock) > 0) {
                  $inventory_ref_id = $individualStock[0]->inventory_ref_id;
                  if ($individualStock[0]->stock_type == 'in') {
                        $stockIn += $individualStock[0]->stock;
                    }

                    if ($individualStock[0]->stock_type == 'out') {
                        $stockOut += $individualStock[0]->stock;
                    }
              }

          }


          //orders products
          
          $stocks = $stockIn - $stockOut;

      } else {

          $stocks = 0;

          $stocksin = DB::table('inventory')->where('products_id', $products_id)->where('stock_type', 'in')->sum('stock');
          $stockOut = DB::table('inventory')->where('products_id', $products_id)->where('stock_type', 'out')->sum('stock');
          $stocks = $stocksin - $stockOut;
      }
      Log::debug(__CLASS__."::".__FUNCTION__."for product id $products_id stock = $stocks and quantity = $quantity");
            if($stocks >= $quantity){
                Log::debug(__CLASS__."::".__FUNCTION__."Product $products_id returning as added to cart");
               return returnResponse("Item Successfully added to cart", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, array('stocks'=>$stocks)); 
            }
            Log::debug(__CLASS__."::".__FUNCTION__."Product $products_id returning as Out of stock");
           return returnResponse("Out of stock", HttpStatus::HTTP_NOT_ACCEPTABLE, HttpStatus::HTTP_WARNING); 

        }
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
        
        
        
    }

    
    //addToCart Database
    public function addToCartDb(Request $request)
    {
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
            
            $validator = Validator::make($request->all(), [
            'quantity' => 'required|numeric|min:1',
            'products_id' => 'required',
            'attributes.*' => 'nullable',
            
            ]);
        
            if ($validator->fails()) {
                return returnResponse($validator->errors(), HttpStatus::HTTP_UNPROCESSABLE_ENTITY);
            }
            
         $inventory_ref_id = '';
      $result = array();$stocks = 0;
      $products_id = $request->input('products_id');
      $quantity = $request->input('quantity');
      
      Log::debug(__CLASS__."::".__FUNCTION__."called with product id $products_id and quantity $quantity");
      
      $productsType = DB::table('products')->where('products_id', $products_id)->get();
      //check products type
      if ($productsType[0]->products_type == 1) {
          $getDataqunatity['products_id'] = $products_id;
                    $getDataqunatity['attributes'] =  $request->input('attributes');
                    
                    Log::debug('Attributes '.json_encode($request->input('attributes')));

                    $content = Product::productQuantity($getDataqunatity);
                    //dd($content);
                    $stocks = $content['remainingStock'];
          //orders products
          

      } else {

          $stocks = 0;

          $stocksin = DB::table('inventory')->where('products_id', $products_id)->where('stock_type', 'in')->sum('stock');
          $stockOut = DB::table('inventory')->where('products_id', $products_id)->where('stock_type', 'out')->sum('stock');
          $stocks = $stocksin - $stockOut;
      }
      Log::debug(__CLASS__."::".__FUNCTION__."for product id $products_id stock = $stocks and quantity = $quantity");
            if($stocks >= $quantity){
                $return = Cart::addToCartDb($request,$stocks);
                Log::debug($return);
                if ($return) {
                    if ($return == 'exceed') {

                        Log::debug(__CLASS__ . "::" . __FUNCTION__ . "Product $products_id returning as Exceeded");
                        return returnResponse("Quantity Exceeded", HttpStatus::HTTP_NOT_ACCEPTABLE, HttpStatus::HTTP_WARNING);
                    } else {
                        Log::debug(__CLASS__ . "::" . __FUNCTION__ . "Product $products_id returning as added to cart");
                        $myCart = Cart::myCart();
                        return returnResponse("Item Successfully added to cart", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $myCart);
                    }
                } else {
                    Log::debug(__CLASS__ . "::" . __FUNCTION__ . "Product $products_id add to cart error");
                    return returnResponse("Oups Error occurred");
                }
            }
            Log::debug(__CLASS__."::".__FUNCTION__."Product $products_id returning as Out of stock");
           return returnResponse("Out of stock", HttpStatus::HTTP_NOT_ACCEPTABLE, HttpStatus::HTTP_WARNING); 

        }
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
        
        
        
    }
    
    
    //my cart data
    public function myCart(Request $request)
    {
        $consumer_data = getallheaders();
     
      $consumer_data['consumer_ip'] = $request->ip();
        $consumer_data['consumer_url'] = __FUNCTION__;
        $authController = new AppSettingController();
        $authenticate = $authController->apiAuthenticate($consumer_data);
        if ($authenticate == 1) {
            $baskit_id = null;
            if($request->has('basket_id')){
               $baskit_id = $request->basket_id;
            }
            $myCart = Cart::myCart($baskit_id);
        
         
      
            if(isset($myCart->total_quantity) && $myCart->total_quantity > 0){
                Log::debug(__CLASS__."::".__FUNCTION__." Returning cart data");
               return returnResponse("Cart data found", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $myCart); 
            }
            Log::debug(__CLASS__."::".__FUNCTION__."Cart is empty");
           return returnResponse("No item in cart", HttpStatus::HTTP_NO_CONTENT, HttpStatus::HTTP_WARNING); 

        }
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
        
        
        
    }
    
    //remove single item from cart
    public function removeSingleCartItem(Request $request)
    {
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
            
            $validator = Validator::make($request->all(), [
            'basket_id' => 'required',
            
            ]);
        $baskit_id = $request->input('basket_id');
            if ($validator->fails()) {
                return returnResponse($validator->errors(), HttpStatus::HTTP_UNPROCESSABLE_ENTITY);
            }
            Log::debug(__CLASS__."::".__FUNCTION__." Called with basket id $baskit_id");
            $check_basket = DB::table('customers_basket')->where([
            ['customers_basket_id', '=', $baskit_id],
          ['customers_id', '=', auth()->user()->id],
        ])->get();
            Log::debug(__CLASS__."::".__FUNCTION__." found data with basket id $baskit_id count ".count($check_basket));
            if(count($check_basket) > 0){
               if(!Cart::deleteSingleCartItem($request)){
                   Log::error(__CLASS__."::".__FUNCTION__." Cart data deleting error");
               return returnResponse("Oops Some error occured !!"); 
               }
                $myCart = Cart::myCart();

                if(isset($myCart->total_quantity) && $myCart->total_quantity > 0){
                    Log::debug(__CLASS__."::".__FUNCTION__." Returning cart data");
                   return returnResponse("Removed Item from Cart", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $myCart); 
                }else{
                    Log::debug(__CLASS__."::".__FUNCTION__."Cart is empty");
                   return returnResponse("No item in cart", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $myCart); 
                }
            }
            Log::debug(__CLASS__."::".__FUNCTION__."Cart is empty");
           return returnResponse("No item in cart", HttpStatus::HTTP_NO_CONTENT, HttpStatus::HTTP_WARNING); 

        }
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
        
        
        
    }
    
    //decrement single cart item
    public function updateSingleCartItem(Request $request)
    {
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
            
            $validator = Validator::make($request->all(), [
            'basket_id' => 'required',
            'products_id' => 'required',
            'quantity' => 'required|numeric|min:1',
            'attributes.*' => 'nullable',
            ]);
        $baskit_id = $request->input('basket_id');
        $quantity = $request->input('quantity');
        $customers_id = auth()->user()->id;
            if ($validator->fails()) {
                return returnResponse($validator->errors(), HttpStatus::HTTP_UNPROCESSABLE_ENTITY);
            }
            Log::debug(__CLASS__."::".__FUNCTION__." Called with basket id $baskit_id with update quantity $quantity");
            $check_basket = DB::table('customers_basket')->where([
            ['customers_basket_id', '=', $baskit_id],
          ['customers_id', '=', auth()->user()->id],
        ])->get();
            
            $basket_quantity = $check_basket[0]->customers_basket_quantity;
            
            Log::debug(__CLASS__."::".__FUNCTION__." found data with basket id $baskit_id count ".count($check_basket));
            
            $stocks = 0;
      $products_id = $request->input('products_id');
      
      Log::debug(__CLASS__."::".__FUNCTION__."called with product id $products_id and quantity $quantity");
      
      $productsType = DB::table('products')->where('products_id', $products_id)->get();
      //check products type
      if ($productsType[0]->products_type == 1) {
          $getDataqunatity['products_id'] = $products_id;
                    $getDataqunatity['attributes'] =  $request->input('attributes');
                    
                    Log::debug('Attributes '.json_encode($request->input('attributes')));

                    $content = Product::productQuantity($getDataqunatity);
                    //dd($content);
                    $stocks = $content['remainingStock'];
          //orders products
          

      } else {

          $stocks = 0;

          $stocksin = DB::table('inventory')->where('products_id', $products_id)->where('stock_type', 'in')->sum('stock');
          $stockOut = DB::table('inventory')->where('products_id', $products_id)->where('stock_type', 'out')->sum('stock');
          $stocks = $stocksin - $stockOut;
      }
      Log::debug(__CLASS__."::".__FUNCTION__."for product id $products_id stock = $stocks and quantity = $quantity");
            
            
            
            if(count($check_basket) > 0){
                Log::debug(__CLASS__."::".__FUNCTION__." found data with basket id $baskit_id basket quantity ".$basket_quantity);
                $update_quantity = $quantity;
                
                if($stocks < $quantity){
                    Log::debug(__CLASS__ . "::" . __FUNCTION__ . "Product $products_id returning as Exceeded");
                        return returnResponse("Out of Stock", HttpStatus::HTTP_NOT_ACCEPTABLE, HttpStatus::HTTP_WARNING); 
                }
                
                if($update_quantity > 0){
                    if(!Cart::updateSingleCartRecord($baskit_id,$customers_id,$quantity)){
                        Log::error(__CLASS__."::".__FUNCTION__." Cart item quantity decrementing error");
                    return returnResponse("Oops Some error occured !!"); 
                    }
                }else{
                    if(!Cart::deleteSingleCartItem($request)){
                        Log::error(__CLASS__."::".__FUNCTION__." Cart data updating error");
                    return returnResponse("Oops Some error occured !!"); 
                   }
                }
                $myCart = Cart::myCart();

                if(isset($myCart->total_quantity) && $myCart->total_quantity > 0){
                    Log::debug(__CLASS__."::".__FUNCTION__." Returning cart data");
                   return returnResponse("Item quantity decremented from Cart", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $myCart); 
                }else{
                    Log::debug(__CLASS__."::".__FUNCTION__."Cart is empty");
                   return returnResponse("No data found", HttpStatus::HTTP_NOT_FOUND, HttpStatus::HTTP_WARNING); 
                }
            }
            Log::debug(__CLASS__."::".__FUNCTION__."Cart is empty");
           return returnResponse("No item in cart", HttpStatus::HTTP_NO_CONTENT, HttpStatus::HTTP_WARNING); 

        }
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
        
        
    }
    
    
    ###### Clear All cart Item ########
    //remove single item from cart
    public function clearAllCartItem(Request $request)
    {
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
            
            $customer_id = auth()->user()->id;
            Log::debug(__CLASS__."::".__FUNCTION__." Called with customer id $customer_id");
            $check_basket = DB::table('customers_basket')->where([
          ['customers_id', '=', $customer_id],
        ])->get();
            Log::debug(__CLASS__."::".__FUNCTION__." found cart data for customer id $customer_id with cart count ".count($check_basket));
            if(count($check_basket) > 0){
               if(!Cart::clearAllCartItems($request)){
                   Log::error(__CLASS__."::".__FUNCTION__." Cart data clearing error");
               return returnResponse("Oops Some error occured !!"); 
               }
                $myCart = Cart::myCart();

                if(isset($myCart->total_quantity) && $myCart->total_quantity > 0){
                    Log::debug(__CLASS__."::".__FUNCTION__."cart not cleared");
                   return returnResponse("Oops Some error occured !!"); 
                }else{
                    Log::debug(__CLASS__."::".__FUNCTION__."Cart is Cleared");
                   return returnResponse("Cart cleared Successfully", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $myCart); 
                }
            }
            Log::debug(__CLASS__."::".__FUNCTION__."Cart is empty");
           return returnResponse("No data found", HttpStatus::HTTP_NOT_FOUND, HttpStatus::HTTP_WARNING); 

        }
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
        
        
        
    }
    
    
    //addToCartFixed
    public function attributeDetails(Request $request)
    {
        $consumer_data = getallheaders();
      /*
      $consumer_data['consumer_key'] = $request->header('consumer_key');
      $consumer_data['consumer_secret'] = $request->header('consumer_secret');
      $consumer_data['consumer_nonce'] = $request->header('consumer_nonce');
      $consumer_data['consumer_device_id'] = $request->header('consumer_device_id');
      */
        $result = array();
      $consumer_data['consumer_ip'] = $request->ip();
        $consumer_data['consumer_url'] = __FUNCTION__;
        $authController = new AppSettingController();
        $authenticate = $authController->apiAuthenticate($consumer_data);
        if ($authenticate == 1) {
            
            $validator = Validator::make($request->all(), [
            'products_id' => 'required',
            'attribute_id.*' => 'required',
            
            ]);
        
            if ($validator->fails()) {
                return returnResponse($validator->errors(), HttpStatus::HTTP_UNPROCESSABLE_ENTITY);
            }
            $currentDate = time();
            $categories = DB::table('products')
                  ->leftJoin('manufacturers', 'manufacturers.manufacturers_id', '=', 'products.manufacturers_id')
                  ->leftJoin('manufacturers_info', 'manufacturers.manufacturers_id', '=', 'manufacturers_info.manufacturers_id')
                  ->leftJoin('products_description', 'products_description.products_id', '=', 'products.products_id')
                  ;
              $categories->LeftJoin('image_categories', function ($join) {
                  $join->on('image_categories.image_id', '=', 'products.products_image')
                      ->where(function ($query) {
                          $query->where('image_categories.image_type', '=', 'THUMBNAIL')
                              ->where('image_categories.image_type', '!=', 'THUMBNAIL')
                              ->orWhere('image_categories.image_type', '=', 'ACTUAL');
                      });
              })
               
              ;
              $categories->LeftJoin('specials', function ($join) use ($currentDate) {
                $join->on('specials.products_id', '=', 'products.products_id')->where('status', '=', '1')->where('expires_date', '>', $currentDate);
            });
              $categories->LeftJoin('flash_sale', function ($join) use ($currentDate) {
                $join->on('flash_sale.products_id', '=', 'products.products_id')->where('flash_sale.flash_status', '=', '1')->where('flash_expires_date', '>', $currentDate);
            });
            
                  $categories->where('products.products_id', '=', $request->products_id);
              $data = $categories->select('products.*', 'products_description.*', 'manufacturers.*', 'manufacturers_info.*', 'specials.specials_new_products_price as discount_price','flash_sale.flash_start_date', 'flash_sale.flash_expires_date', 'flash_sale.flash_sale_products_price as flash_price', 'image_categories.path as products_image')->first();
              //multiple images
                      $products_images = DB::table('products_images')
                          ->LeftJoin('image_categories', function ($join) {
                              $join->on('image_categories.image_id', '=', 'products_images.image')
                                  ->where(function ($query) {
                                      $query->where('image_categories.image_type', '=', 'THUMBNAIL')
                                          ->where('image_categories.image_type', '!=', 'THUMBNAIL')
                                          ->orWhere('image_categories.image_type', '=', 'ACTUAL');
                                  });
                          })
                          ->select('products_images.*', 'image_categories.path as image')
                          ->where('products_id', '=', $data->products_id)->orderBy('sort_order', 'ASC')->get();
                      $data->images = $products_images;
              if (!empty($data->flash_price)) {
                    $final_price = $data->flash_price + 0;
                    $data->discount_percent = 0;
                                            $data->discounted_price = 0;
                 } elseif (!empty($data->discount_price)) {
                     $final_price = $data->discount_price + 0;
                     $discount_price = $data->discount_price;
                                        
                                        
                                            $discounted_price = $data->products_price - $discount_price;
                                            $discount_percentage = $discounted_price / $data->products_price * 100;
                                            $data->discount_percent = intval($discount_percentage);
                                            $data->discounted_price = $discount_price;
                                        
                                        $data->discount_price = $discount_price;
                 } else {
                     $data->discount_percent = 0;
                                            $data->discounted_price = 0;
                     $final_price = $data->products_price + 0;
                 }
                 
                 $attributeArray = DB::table('products_attributes')->whereIn('products_attributes_id', $request->attribute_id)->get();
                 $attribute_image = array();
                 foreach ($attributeArray as $attribute) {
                     $symbol = $attribute->price_prefix;
                    $values_price = $attribute->options_values_price;
                    if ($symbol == '+') {
                        $final_price = intval($final_price) + intval($values_price);
                    }
                    if ($symbol == '-') {
                        $final_price = intval($final_price) - intval($values_price);
                    }
                    if(!empty($attribute->image)){
                    array_push($attribute_image, array('id'=>null, 'products_id'=>$request->products_id,'image_url'=>$attribute->image,'sort_order'=>'0','html_content'=>''));
                    }
                 }
                    
                    
                    $qunatity['products_id'] = $request->products_id;
                    $qunatity['attributes'] =  $request->attribute_id;
                    
                    Log::debug('Attributes '.json_encode($request->attribute_id));

                    $content = Product::productQuantity($qunatity);
                    //dd($content);
                    $stocks = $content['remainingStock'];
         $data->stock = $stocks;
         $data->final_price = $final_price;
         $data->attribute_image = $attribute_image;
         $result['product_data'] = $data; 
         //$result['stock'] = $stocks;
         //$result['final_price'] = $final_price;
         
            
            
           return returnResponse("Product data!", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $result);

        }
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
    }
    
    public function common_apply_coupon($coupon_code)
    {
        $result = array();
        
        //current date
        $currentDate = date('Y-m-d 00:00:00', time());

        $data = DB::table('coupons')->where([
            ['code', '=', $coupon_code],
            ['expiry_date', '>', $currentDate],
        ]);

        if (session('coupon') == '' or count(session('coupon')) == 0) {
            session(['coupon' => array()]);
            session(['coupon_discount' => 0]);
        }

        $session_coupon_ids = array();
        $session_coupon_data = array();
        if (!empty(session('coupon'))) {
            foreach (session('coupon') as $session_coupon) {
                array_push($session_coupon_data, $session_coupon);
                $session_coupon_ids[] = $session_coupon->coupans_id;

            }
        }

        $coupons = $data->get();

        if (count($coupons) > 0) {

            if (!empty(auth()->guard('customer')->user()->email) and in_array(auth()->guard('customer')->user()->email, explode(',', $coupons[0]->email_restrictions))) {
                $response = array('success' => '2', 'message' => Lang::get("website.You are not allowed to use this coupon"));
            } else {
                if ($coupons[0]->usage_limit > 0 and $coupons[0]->usage_limit == $coupons[0]->usage_count) {
                    $response = array('success' => '2', 'message' => Lang::get("website.This coupon has been reached to its maximum usage limit"));
                } else {

                    $carts = Cart::myCart();

                    $total_cart_items = count($carts);
                    $price = 0;
                    $discount_price = 0;
                    $used_by_user = 0;
                    $individual_use = 0;
                    $price_of_sales_product = 0;
                    $exclude_sale_items = array();
                    $currentDate = time();
                    foreach ($carts as $cart) {

                        //check if amy coupons applied
                        if (!empty($session_coupon_ids)) {
                            $individual_use++;
                        }

                        //user limit
                        if (in_array($coupons[0]->coupans_id, $session_coupon_ids)) {
                            $used_by_user++;
                        }

                        //cart price
                        $price += $cart['final_price'] * $cart['customers_basket_quantity'];

                        //if cart items are special product
                        if ($coupons[0]->exclude_sale_items == 1) {
                            $products_id = $cart['products_id'];
                            $sales_item = DB::table('specials')->where([
                                ['status', '=', '1'],
                                ['expires_date', '>', $currentDate],
                                ['products_id', '=', $products_id]])->select('products_id', 'specials_new_products_price as specials_price')->get();

                            if (count($sales_item) > 0) {
                                $exclude_sale_items[] = $sales_item[0]->products_id;

                                //price check is remaining if already an other coupon is applied and stored in session
                                $price_of_sales_product += $sales_item[0]->specials_price;
                            }
                        }
                    }

                    $total_special_items = count($exclude_sale_items);

                    if ($coupons[0]->individual_use == '1' and $individual_use > 0) {
                        $response = array('success' => '2', 'message' => Lang::get("website.The coupon cannot be used in conjunction with other coupons"));

                    } else {

                        //check limit
                        if ($coupons[0]->usage_limit_per_user > 0 and $coupons[0]->usage_limit_per_user <= $used_by_user) {
                            $response = array('success' => '2', 'message' => Lang::get("website.coupon is used limit"));
                        } else {

                            $cart_price = $price + 0 - $discount_price;

                            if ($coupons[0]->minimum_amount > 0 and $coupons[0]->minimum_amount >= $cart_price) {
                                $response = array('success' => '2', 'message' => Lang::get("website.Coupon amount limit is low than minimum price"));
                            } elseif ($coupons[0]->maximum_amount > 0 and $coupons[0]->maximum_amount <= $cart_price) {
                                $response = array('success' => '2', 'message' => Lang::get("website.Coupon amount limit is exceeded than maximum price"));
                            } else {

                                //exclude sales item
                                //print 'price before applying sales cart price: '.$cart_price;
                                $cart_price = $cart_price - $price_of_sales_product;
                                //print 'current cart price: '.$cart_price;

                                if ($coupons[0]->exclude_sale_items == 1 and $total_special_items == $total_cart_items) {
                                    $response = array('success' => '2', 'message' => Lang::get("website.Coupon cannot be applied this product is in sale"));
                                } else {

                                    if ($coupons[0]->discount_type == 'fixed_cart') {

                                        if ($coupons[0]->amount < $cart_price) {

                                            $coupon_discount = $coupons[0]->amount;
                                            $coupon[] = $coupons[0];

                                        } else {
                                            $response = array('success' => '2', 'message' => Lang::get("website.Coupon amount is greater than total price"));
                                        }

                                    } elseif ($coupons[0]->discount_type == 'percent') {
                                        $current_discount = $coupons[0]->amount / 100 * $cart_price;
                                        $cart_price = $cart_price - $current_discount;
                                        if ($cart_price > 0) {

                                            $coupon_discount = $current_discount;
                                            $coupon[] = $coupons[0];
                                        } else {
                                            $response = array('success' => '2', 'message' => Lang::get("website.Coupon amount is greater than total price"));
                                        }

                                    } elseif ($coupons[0]->discount_type == 'fixed_product') {

                                        $product_discount_price = 0;
                                        //no of items have greater discount price than original price
                                        $items_greater_price = 0;

                                        foreach ($carts as $cart) {

                                            if (!empty($coupon[0]->product_categories)) {

                                                //get category ids
                                                $categories = BD::table('products_to_categories')->where('products_id', '=', $cart['products_id'])->get();

                                                if (in_array($categories[0]->categories_id, $coupon[0]->product_categories)) {

                                                    //if coupon is apply for specific product
                                                    if (!empty($coupons[0]->product_ids) and in_array($cart['products_id'], $coupons[0]->product_ids)) {

                                                        $product_price = $cart['final_price'];
                                                        if ($product_price > $coupons[0]->amount) {

                                                            $product_discount_price += $coupons[0]->amount * $cart['customers_basket_quantity'];
                                                        } else {
                                                            $items_greater_price++;
                                                        }

                                                        //if coupon cannot be apply for speciafic product
                                                    } elseif (!empty($coupons[0]->exclude_product_ids) and in_array($cart['products_id'], $coupons[0]->exclude_product_ids)) {

                                                    } elseif (empty($coupons[0]->exclude_product_ids) and empty($coupons[0]->product_ids)) {

                                                        $product_price = $cart['final_price'];
                                                        if ($product_price > $coupons[0]->amount) {
                                                            $product_discount_price += $coupons[0]->amount * $cart['customers_basket_quantity'];
                                                        } else {
                                                            $items_greater_price++;
                                                        }
                                                    }

                                                }

                                            } else if (!empty($coupon[0]->excluded_product_categories)) {

                                                //get category ids
                                                $categories = BD::table('products_to_categories')->where('products_id', '=', $cart['products_id'])->get();

                                                if (in_array($categories[0]->categories_id, $coupon[0]->excluded_product_categories)) {

                                                    //if coupon is apply for specific product
                                                    if (!empty($coupons[0]->product_ids) and in_array($cart['products_id'], $coupons[0]->product_ids)) {

                                                        $product_price = $cart['final_price'];
                                                        if ($product_price > $coupons[0]->amount) {
                                                            $product_discount_price += $coupons[0]->amount * $cart['customers_basket_quantity'];
                                                        } else {
                                                            $items_greater_price++;
                                                        }

                                                        //if coupon cannot be apply for speciafic product
                                                    } elseif (!empty($coupons[0]->exclude_product_ids) and in_array($cart['products_id'], $coupons[0]->exclude_product_ids)) {

                                                    } elseif (empty($coupons[0]->exclude_product_ids) and empty($coupons[0]->product_ids)) {

                                                        $product_price = $cart['final_price'];
                                                        if ($product_price > $coupons[0]->amount) {
                                                            $product_discount_price += $coupons[0]->amount * $cart['customers_basket_quantity'];
                                                        } else {
                                                            $items_greater_price++;
                                                        }
                                                    }
                                                }

                                            } else {
                                                //if coupon is apply for specific product
                                                if (!empty($coupons[0]->product_ids) and in_array($cart['products_id'], $coupons[0]->product_ids)) {

                                                    $product_price = $cart['final_price'];
                                                    if ($product_price > $coupons[0]->amount) {
                                                        $product_discount_price += $coupons[0]->amount * $cart['customers_basket_quantity'];
                                                    } else {
                                                        $items_greater_price++;
                                                    }

                                                    //if coupon cannot be apply for speciafic product
                                                } elseif (!empty($coupons[0]->exclude_product_ids) and in_array($cart['products_id'], $coupons[0]->exclude_product_ids)) {

                                                } elseif (empty($coupons[0]->exclude_product_ids) and empty($coupons[0]->product_ids)) {

                                                    $product_price = $cart['final_price'];
                                                    if ($product_price > $coupons[0]->amount) {
                                                        $product_discount_price += $coupons[0]->amount * $cart['customers_basket_quantity'];
                                                    } else {
                                                        $items_greater_price++;
                                                    }
                                                }
                                            }

                                        }

                                        //check if all cart products are equal to that product which have greater discount amount
                                        if ($total_cart_items == $items_greater_price) {
                                            $response = array('success' => '2', 'message' => Lang::get("website.Coupon amount is greater than product price"));
                                        } else {
                                            $coupon_discount = $product_discount_price;
                                            $coupon[] = $coupons[0];

                                        }

                                    } elseif ($coupons[0]->discount_type == 'percent_product') {

                                        $product_discount_price = 0;
                                        //no of items have greater discount price than original price
                                        $items_greater_price = 0;

                                        foreach ($carts as $cart) {

                                            if (!empty($coupon[0]->product_categories)) {

                                                //get category ids
                                                $categories = BD::table('products_to_categories')->where('products_id', '=', $cart['products_id'])->get();

                                                if (in_array($categories[0]->categories_id, $coupon[0]->product_categories)) {

                                                    //if coupon is apply for specific product
                                                    if (!empty($coupons[0]->product_ids) and in_array($cart['products_id'], $coupons[0]->product_ids)) {

                                                        $product_price = $cart['final_price'] - ($coupons[0]->amount / 100 * $cart['final_price']);
                                                        if ($product_price > $coupons[0]->amount) {
                                                            $product_discount_price += $coupons[0]->amount / 100 * ($cart['final_price'] * $cart['customers_basket_quantity']);
                                                        } else {
                                                            $items_greater_price++;
                                                        }

                                                        //if coupon cannot be apply for speciafic product
                                                    } elseif (!empty($coupons[0]->exclude_product_ids) and in_array($cart['products_id'], $coupons[0]->exclude_product_ids)) {

                                                    } elseif (empty($coupons[0]->exclude_product_ids) and empty($coupons[0]->product_ids)) {

                                                        $product_price = $cart['final_price'] - ($coupons[0]->amount / 100 * $cart['final_price']);
                                                        if ($product_price > $coupons[0]->amount) {
                                                            $product_discount_price += $coupons[0]->amount / 100 * ($cart['final_price'] * $cart['customers_basket_quantity']);
                                                        } else {
                                                            $items_greater_price++;
                                                        }
                                                    }

                                                }

                                            } else if (!empty($coupon[0]->excluded_product_categories)) {

                                                //get category ids
                                                $categories = BD::table('products_to_categories')->where('products_id', '=', $cart['products_id'])->get();

                                                if (in_array($categories[0]->categories_id, $coupon[0]->excluded_product_categories)) {

                                                    //if coupon is apply for specific product
                                                    if (!empty($coupons[0]->product_ids) and in_array($cart['products_id'], $coupons[0]->product_ids)) {

                                                        $product_price = $cart['final_price'] - ($coupons[0]->amount / 100 * $cart['final_price']);
                                                        if ($product_price > $coupons[0]->amount) {
                                                            $product_discount_price += $coupons[0]->amount / 100 * ($cart['final_price'] * $cart['customers_basket_quantity']);
                                                        } else {
                                                            $items_greater_price++;
                                                        }

                                                        //if coupon cannot be apply for speciafic product
                                                    } elseif (!empty($coupons[0]->exclude_product_ids) and in_array($cart['products_id'], $coupons[0]->exclude_product_ids)) {

                                                    } elseif (empty($coupons[0]->exclude_product_ids) and empty($coupons[0]->product_ids)) {

                                                        $product_price = $cart['final_price'] - ($coupons[0]->amount / 100 * $cart['final_price']);
                                                        if ($product_price > $coupons[0]->amount) {
                                                            $product_discount_price += $coupons[0]->amount / 100 * ($cart['final_price'] * $cart['customers_basket_quantity']);
                                                        } else {
                                                            $items_greater_price++;
                                                        }
                                                    }

                                                }

                                            } else {

                                                //if coupon is apply for specific product
                                                if (!empty($coupons[0]->product_ids) and in_array($cart['products_id'], $coupons[0]->product_ids)) {

                                                    $product_price = $cart['final_price'] - ($coupons[0]->amount / 100 * $cart['final_price']);
                                                    if ($product_price > $coupons[0]->amount) {
                                                        $product_discount_price += $coupons[0]->amount / 100 * ($cart['final_price'] * $cart['customers_basket_quantity']);
                                                    } else {
                                                        $items_greater_price++;
                                                    }

                                                    //if coupon cannot be apply for speciafic product
                                                } elseif (!empty($coupons[0]->exclude_product_ids) and in_array($cart['products_id'], $coupons[0]->exclude_product_ids)) {

                                                } elseif (empty($coupons[0]->exclude_product_ids) and empty($coupons[0]->product_ids)) {

                                                    $product_price = $cart['final_price'] - ($coupons[0]->amount / 100 * $cart['final_price']);
                                                    if ($product_price > $coupons[0]->amount) {
                                                        $product_discount_price += $coupons[0]->amount / 100 * ($cart['final_price'] * $cart['customers_basket_quantity']);
                                                    } else {
                                                        $items_greater_price++;
                                                    }
                                                }
                                            }

                                        }

                                        //check if all cart products are equal to that product which have greater discount amount
                                        if ($total_cart_items == $items_greater_price) {
                                            $response = array('success' => '2', 'message' => Lang::get("website.Coupon amount is greater than product price"));
                                        } else {
                                            $coupon_discount = $product_discount_price;
                                            $coupon[] = $coupons[0];
                                        }

                                    }
                                }

                            }

                        }

                    }

                }
            }
            
            if(empty($response)){
                if (!in_array($coupons[0]->coupans_id, $session_coupon_ids)) {

                    if (count($session_coupon_data) > 0) {
                        $index = count($session_coupon_data);
                    } else {
                        $index = 0;
                    }
                    $session_coupon_data[$index] = $coupons[0];
                    session(['coupon_discount' => session('coupon_discount') + $coupon_discount]);
                    $response = array('success' => '1', 'message' => Lang::get("website.Couponisappliedsuccessfully"));
    
                } else {
                    $response = array('success' => '0', 'message' => Lang::get("website.Coupon is already applied"));
                }
    
                session(['coupon' => $session_coupon_data]);
            }
           

        } else {

            $response = array('success' => '0', 'message' => Lang::get("website.Coupon does not exist"));
        }

        return $response;

    }

   public function applyCoupon(Request $request)
    {
       log::debug(__CLASS__."::".__FUNCTION__."called");
       $consumer_data = getallheaders();
      /*
      $consumer_data['consumer_key'] = $request->header('consumer_key');
      $consumer_data['consumer_secret'] = $request->header('consumer_secret');
      $consumer_data['consumer_nonce'] = $request->header('consumer_nonce');
      $consumer_data['consumer_device_id'] = $request->header('consumer_device_id');
      */
       $validator = Validator::make($request->all(), [
            
            'coupon_code' => 'required',
        ]);
        if ($validator->fails()) {
            Log::error(__CLASS__."::".__FUNCTION__." Validation failed !");
            return returnResponse($validator->errors(), HttpStatus::HTTP_UNPROCESSABLE_ENTITY);
        }
       
        $result = array();
        $coupon_code = $request->coupon_code;
      $consumer_data['consumer_ip'] = $request->ip();
        $consumer_data['consumer_url'] = __FUNCTION__;
        $authController = new AppSettingController();
        $authenticate = $authController->apiAuthenticate($consumer_data);
        if ($authenticate == 1) {
        $result = array();
        
        //current date
        $currentDate = date('Y-m-d 00:00:00', time());

        $data = DB::table('coupons')->where([
            ['code', '=', $coupon_code],
            ['expiry_date', '>', $currentDate],
        ]);

         
        log::debug(__CLASS__."::".__FUNCTION__."called with coupon code $coupon_code");

        $coupons = $data->get();
        $coupon_discount = 0;
        if (count($coupons) > 0) {

            if (!empty(auth()->user()->email) and in_array(auth()->user()->email, explode(',', $coupons[0]->email_restrictions))) {
                //$response = array('success' => '2', 'message' => "You are not allowed to use this coupon");
                return returnResponse("You are not allowed to use this coupon!");
            } else {
                if ($coupons[0]->usage_limit > 0 and $coupons[0]->usage_limit == $coupons[0]->usage_count) {
                    //$response = array('success' => '2', 'message' => Lang::get("website.This coupon has been reached to its maximum usage limit"));
                    return returnResponse("This coupon has been reached to its maximum usage limit!");
                } else {
                      $cart_items = Cart::myCart();
                    $carts = json_decode($cart_items->cart, true);

                    $total_cart_items = count($carts);
                    $price = 0;
                    $discount_price = 0;
                    $used_by_user = 0;
                    $individual_use = 0;
                    $price_of_sales_product = 0;
                    $exclude_sale_items = array();
                    $currentDate = time();
                    foreach ($carts as $cart) {
                     $cart = json_decode($cart, true);
                        
                    Log::debug("under products loop with product id".$cart['products_id']."final price ".$cart['final_price']);

                        //cart price
                        $price += $cart['final_price'] * $cart['customers_basket_quantity'];

                        //if cart items are special product
                        if ($coupons[0]->exclude_sale_items == 1) {
                            $products_id = $cart['products_id'];
                            $sales_item = DB::table('specials')->where([
                                ['status', '=', '1'],
                                ['expires_date', '>', $currentDate],
                                ['products_id', '=', $products_id]])->select('products_id', 'specials_new_products_price as specials_price')->get();

                            if (count($sales_item) > 0) {
                                $exclude_sale_items[] = $sales_item[0]->products_id;

                                //price check is remaining if already an other coupon is applied and stored in session
                                $price_of_sales_product += $sales_item[0]->specials_price;
                            }
                        }
                    }

                    $total_special_items = count($exclude_sale_items);

                    

                        //check limit
                        if($coupons[0]->usage_limit_per_user > 0){
                            //user limit
                            $used_by_user = DB::table('orders')->where('customers_id', auth()->user()->id)->where('coupon_code',$coupons[0]->code)->count();
                            $used_by_user = $used_by_user + 1;
                        }
                        //check limit
                        if ($coupons[0]->usage_limit_per_user > 0 && $used_by_user > $coupons[0]->usage_limit_per_user) {
                            return returnResponse("You have already used this coupon !");
                        } else {

                            $cart_price = $price + 0 - $discount_price;

                            if ($coupons[0]->minimum_amount > 0 and $coupons[0]->minimum_amount >= $cart_price) {
                                //$response = array('success' => '2', 'message' => Lang::get("website.Coupon amount limit is low than minimum price"));
                                return returnResponse("Coupon amount limit is low than minimum price!");
                            } elseif ($coupons[0]->maximum_amount > 0 and $coupons[0]->maximum_amount <= $cart_price) {
                                //$response = array('success' => '2', 'message' => Lang::get("website.Coupon amount limit is exceeded than maximum price"));
                                return returnResponse("Coupon amount limit is exceeded than maximum price!");
                            } else {

                                //exclude sales item
                                //print 'price before applying sales cart price: '.$cart_price;
                                $cart_price = $cart_price - $price_of_sales_product;
                                //print 'current cart price: '.$cart_price;

                                if ($coupons[0]->exclude_sale_items == 1 and $total_special_items == $total_cart_items) {
                                    //$response = array('success' => '2', 'message' => Lang::get("website.Coupon cannot be applied this product is in sale"));
                                     return returnResponse("Coupon cannot be applied this product is in sale!");
                                } else {

                                    if ($coupons[0]->discount_type == 'fixed_cart') {

                                        if ($coupons[0]->amount < $cart_price) {

                                            $coupon_discount = $coupons[0]->amount;
                                            $coupon[] = $coupons[0];

                                        } else {
                                            //$response = array('success' => '2', 'message' => Lang::get("website.Coupon amount is greater than total price"));
                                            return returnResponse("Coupon amount is greater than total price!");
                                        }

                                    } elseif ($coupons[0]->discount_type == 'percent') {
                                        $current_discount = $coupons[0]->amount / 100 * $cart_price;
                                        $cart_price = $cart_price - $current_discount;
                                        if ($cart_price > 0) {

                                            $coupon_discount = $current_discount;
                                            $coupon[] = $coupons[0];
                                        } else {
                                            //$response = array('success' => '2', 'message' => Lang::get("website.Coupon amount is greater than total price"));
                                            return returnResponse("Coupon amount is greater than total price!");
                                        }

                                    } elseif ($coupons[0]->discount_type == 'fixed_product') {

                                        $product_discount_price = 0;
                                        //no of items have greater discount price than original price
                                        $items_greater_price = 0;

                                        foreach ($carts as $cart) {
                                             $cart = json_decode($cart, true);

                                            if (!empty($coupon[0]->product_categories)) {

                                                //get category ids
                                                $categories = BD::table('products_to_categories')->where('products_id', '=', $cart['products_id'])->get();

                                                if (in_array($categories[0]->categories_id, $coupon[0]->product_categories)) {

                                                    //if coupon is apply for specific product
                                                    if (!empty($coupons[0]->product_ids) and in_array($cart['products_id'], $coupons[0]->product_ids)) {

                                                        $product_price = $cart['final_price'];
                                                        if ($product_price > $coupons[0]->amount) {

                                                            $product_discount_price += $coupons[0]->amount * $cart['customers_basket_quantity'];
                                                        } else {
                                                            $items_greater_price++;
                                                        }

                                                        //if coupon cannot be apply for speciafic product
                                                    } elseif (!empty($coupons[0]->exclude_product_ids) and in_array($cart['products_id'], $coupons[0]->exclude_product_ids)) {

                                                    } elseif (empty($coupons[0]->exclude_product_ids) and empty($coupons[0]->product_ids)) {

                                                        $product_price = $cart['final_price'];
                                                        if ($product_price > $coupons[0]->amount) {
                                                            $product_discount_price += $coupons[0]->amount * $cart['customers_basket_quantity'];
                                                        } else {
                                                            $items_greater_price++;
                                                        }
                                                    }

                                                }

                                            } else if (!empty($coupon[0]->excluded_product_categories)) {

                                                //get category ids
                                                $categories = BD::table('products_to_categories')->where('products_id', '=', $cart['products_id'])->get();

                                                if (in_array($categories[0]->categories_id, $coupon[0]->excluded_product_categories)) {

                                                    //if coupon is apply for specific product
                                                    if (!empty($coupons[0]->product_ids) and in_array($cart['products_id'], $coupons[0]->product_ids)) {

                                                        $product_price = $cart['final_price'];
                                                        if ($product_price > $coupons[0]->amount) {
                                                            $product_discount_price += $coupons[0]->amount * $cart['customers_basket_quantity'];
                                                        } else {
                                                            $items_greater_price++;
                                                        }

                                                        //if coupon cannot be apply for speciafic product
                                                    } elseif (!empty($coupons[0]->exclude_product_ids) and in_array($cart['products_id'], $coupons[0]->exclude_product_ids)) {

                                                    } elseif (empty($coupons[0]->exclude_product_ids) and empty($coupons[0]->product_ids)) {

                                                        $product_price = $cart['final_price'];
                                                        if ($product_price > $coupons[0]->amount) {
                                                            $product_discount_price += $coupons[0]->amount * $cart['customers_basket_quantity'];
                                                        } else {
                                                            $items_greater_price++;
                                                        }
                                                    }
                                                }

                                            } else {
                                                //if coupon is apply for specific product
                                                if (!empty($coupons[0]->product_ids) and in_array($cart['products_id'], $coupons[0]->product_ids)) {

                                                    $product_price = $cart['final_price'];
                                                    if ($product_price > $coupons[0]->amount) {
                                                        $product_discount_price += $coupons[0]->amount * $cart['customers_basket_quantity'];
                                                    } else {
                                                        $items_greater_price++;
                                                    }

                                                    //if coupon cannot be apply for speciafic product
                                                } elseif (!empty($coupons[0]->exclude_product_ids) and in_array($cart['products_id'], $coupons[0]->exclude_product_ids)) {

                                                } elseif (empty($coupons[0]->exclude_product_ids) and empty($coupons[0]->product_ids)) {

                                                    $product_price = $cart['final_price'];
                                                    if ($product_price > $coupons[0]->amount) {
                                                        $product_discount_price += $coupons[0]->amount * $cart['customers_basket_quantity'];
                                                    } else {
                                                        $items_greater_price++;
                                                    }
                                                }
                                            }

                                        }

                                        //check if all cart products are equal to that product which have greater discount amount
                                        if ($total_cart_items == $items_greater_price) {
                                           // $response = array('success' => '2', 'message' => Lang::get("website.Coupon amount is greater than product price"));
                                        
                                            return returnResponse("Coupon amount is greater than total price!");
                                        } else {
                                            $coupon_discount = $product_discount_price;
                                            $coupon[] = $coupons[0];

                                        }

                                    } elseif ($coupons[0]->discount_type == 'percent_product') {

                                        $product_discount_price = 0;
                                        //no of items have greater discount price than original price
                                        $items_greater_price = 0;

                                        foreach ($carts as $cart) {
                                             $cart = json_decode($cart, true);
                                            if (!empty($coupon[0]->product_categories)) {

                                                //get category ids
                                                $categories = BD::table('products_to_categories')->where('products_id', '=', $cart['products_id'])->get();

                                                if (in_array($categories[0]->categories_id, $coupon[0]->product_categories)) {

                                                    //if coupon is apply for specific product
                                                    if (!empty($coupons[0]->product_ids) and in_array($cart['products_id'], $coupons[0]->product_ids)) {

                                                        $product_price = $cart['final_price'] - ($coupons[0]->amount / 100 * $cart['final_price']);
                                                        if ($product_price > $coupons[0]->amount) {
                                                            $product_discount_price += $coupons[0]->amount / 100 * ($cart['final_price'] * $cart['customers_basket_quantity']);
                                                        } else {
                                                            $items_greater_price++;
                                                        }

                                                        //if coupon cannot be apply for speciafic product
                                                    } elseif (!empty($coupons[0]->exclude_product_ids) and in_array($cart['products_id'], $coupons[0]->exclude_product_ids)) {

                                                    } elseif (empty($coupons[0]->exclude_product_ids) and empty($coupons[0]->product_ids)) {

                                                        $product_price = $cart['final_price'] - ($coupons[0]->amount / 100 * $cart['final_price']);
                                                        if ($product_price > $coupons[0]->amount) {
                                                            $product_discount_price += $coupons[0]->amount / 100 * ($cart['final_price'] * $cart['customers_basket_quantity']);
                                                        } else {
                                                            $items_greater_price++;
                                                        }
                                                    }

                                                }

                                            } else if (!empty($coupon[0]->excluded_product_categories)) {

                                                //get category ids
                                                $categories = BD::table('products_to_categories')->where('products_id', '=', $cart['products_id'])->get();

                                                if (in_array($categories[0]->categories_id, $coupon[0]->excluded_product_categories)) {

                                                    //if coupon is apply for specific product
                                                    if (!empty($coupons[0]->product_ids) and in_array($cart['products_id'], $coupons[0]->product_ids)) {

                                                        $product_price = $cart['final_price'] - ($coupons[0]->amount / 100 * $cart['final_price']);
                                                        if ($product_price > $coupons[0]->amount) {
                                                            $product_discount_price += $coupons[0]->amount / 100 * ($cart['final_price'] * $cart['customers_basket_quantity']);
                                                        } else {
                                                            $items_greater_price++;
                                                        }

                                                        //if coupon cannot be apply for speciafic product
                                                    } elseif (!empty($coupons[0]->exclude_product_ids) and in_array($cart['products_id'], $coupons[0]->exclude_product_ids)) {

                                                    } elseif (empty($coupons[0]->exclude_product_ids) and empty($coupons[0]->product_ids)) {

                                                        $product_price = $cart['final_price'] - ($coupons[0]->amount / 100 * $cart['final_price']);
                                                        if ($product_price > $coupons[0]->amount) {
                                                            $product_discount_price += $coupons[0]->amount / 100 * ($cart['final_price'] * $cart['customers_basket_quantity']);
                                                        } else {
                                                            $items_greater_price++;
                                                        }
                                                    }

                                                }

                                            } else {
                                             
                                                Log::debug("product id ".$cart['products_id']);
                                                //if coupon is apply for specific product
                                                if (!empty($coupons[0]->product_ids) and in_array($cart['products_id'], explode(',', $coupons[0]->product_ids))) {

                                                    $product_price = $cart['final_price'] - ($coupons[0]->amount / 100 * $cart['final_price']);
                                                    if ($product_price > $coupons[0]->amount) {
                                                        $product_discount_price += $coupons[0]->amount / 100 * ($cart['final_price'] * $cart['customers_basket_quantity']);
                                                    } else {
                                                        $items_greater_price++;
                                                    }

                                                    //if coupon cannot be apply for speciafic product
                                                } elseif (!empty($coupons[0]->exclude_product_ids) and in_array($cart['products_id'], $coupons[0]->exclude_product_ids)) {

                                                } elseif (empty($coupons[0]->exclude_product_ids) and empty($coupons[0]->product_ids)) {

                                                    $product_price = $cart['final_price'] - ($coupons[0]->amount / 100 * $cart['final_price']);
                                                    if ($product_price > $coupons[0]->amount) {
                                                        $product_discount_price += $coupons[0]->amount / 100 * ($cart['final_price'] * $cart['customers_basket_quantity']);
                                                    } else {
                                                        $items_greater_price++;
                                                    }
                                                }
                                            }

                                        }

                                        //check if all cart products are equal to that product which have greater discount amount
                                        if ($total_cart_items == $items_greater_price) {
                                           // $response = array('success' => '2', 'message' => Lang::get("website.Coupon amount is greater than product price"));
                                            return returnResponse("Coupon amount is greater than total price!");
                                        } else {
                                            $coupon_discount = $product_discount_price;
                                            $coupon[] = $coupons[0];
                                        }

                                    }
                                }

                            }

                        }

                    

                }
            }
            
               
                    //session(['coupon_discount' => session('coupon_discount') + $coupon_discount]);
                    //$response = array('success' => '1', 'message' => Lang::get("website.Couponisappliedsuccessfully"));
         return returnResponse("Coupon is applied successfully", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, array('coupon_amount'=>$coupon_discount));
           

        } else {

            return returnResponse("Coupan doesn't Exists!", HttpStatus::HTTP_NOT_FOUND, HttpStatus::HTTP_ERROR);
        }

        }
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);

    }
    
    ##################################################################################################################################
    ##################################################################################################################################
    
    #############################
    # Cart Summary
    ###########################
    public function cartSummary(Request $request)
    {
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
            
            $customer_id = auth()->user()->id;
            Log::debug(__CLASS__."::".__FUNCTION__." Called with customer id $customer_id");
            $myCart = Cart::myCart();
            Log::debug(__CLASS__."::".__FUNCTION__." found cart data for customer id $customer_id with cart quantity ".$myCart->total_quantity);
            
                if(isset($myCart->total_quantity) && $myCart->total_quantity > 0){
                    $settings = $authController->getSetting();
                    $shiping_charge = 0;
                    $cart_sub_total = $myCart->sub_total;
                    $cart_total = 0;
                    if (!empty($settings['shipping_charge']) && $settings['min_order_amount_for_shipping_free'] >= $cart_sub_total) {
                    $shiping_charge = $settings['shipping_charge'];
                    }
                    $cart_total += $cart_sub_total+$shiping_charge;
                   $return = array();
                   array_push($return, array('title'=>'Delivery charge','symbol'=>'+','value'=>$shiping_charge));
                   $myCart->extra = $return;

                Log::debug(__CLASS__."::".__FUNCTION__." Returning cart summary data");
                    return returnResponse("Cart data found", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $myCart);  
                }else{
                    Log::debug(__CLASS__."::".__FUNCTION__."Cart is empty");
                    return returnResponse("No data found", HttpStatus::HTTP_NOT_FOUND, HttpStatus::HTTP_WARNING); 
                }
                
            

        }
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
        
        
        
    }
    

}
