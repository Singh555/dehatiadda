<?php

namespace App\Models\AppModels;

use App\Http\Controllers\Admin\AdminSiteSettingController;
use App\Http\Controllers\App\AppSettingController;
use DB;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\HttpStatus;
use Validator;
use Log;
use App\Models\Eloquent\Product;
use App\Models\Eloquent\Inventory;
use App\Models\Eloquent\WishList;
use App\Models\Eloquent\ProductAttribute;
use App\Models\Eloquent\ProductOption;
use App\Models\Eloquent\ProductOptionValue;
use App\Models\Eloquent\ProductOptionDescription;
use App\Models\Eloquent\ProductOptionValueDescription;

class ProductModel extends Model
{

  public static function convertprice($current_price, $requested_currency)
  {
    $required_currency = DB::table('currencies')->where('is_current',1)->where('code', 'INR')->first();
    $products_price = $current_price * $required_currency->value;
    return $products_price;
  }


  public static function getProductList($request)
  {
      Log::debug(__CLASS__."::".__FUNCTION__." Called");
        $language_id = 1;
        $requested_currency = 'INR';
        if($request->has('language_id')){
            $language_id = $request->language_id;
        }
        if($request->has('offset')) {     
          $skip = $request->offset;
        }else{
            $skip = '0';
        }
      
      $currentDate = time();
      
      
      if($request->has('type')) {     
        $type = $request->type;
      }else{
        $type = "";
      }

        //filter
        if($request->has('minPrice')){
          $minPrice = $request->input('minPrice');
        }else{
            $minPrice = '';
        }
        if($request->has('maxPrice')){
          $maxPrice = $request->input('maxPrice');
        }else{
            $maxPrice = '';
        }
        $threshold_value = '';$condition='';$filter_type='';$filter_value='';
        if($request->has('filter_type')){
            $filter_type = $request->input('filter_type');
            $condition = $request->input('condition');
            $threshold_value = $request->input('threshold_value');
            $filter_value = $request->input('filter_value');
        }
        $consumer_data = getallheaders();
        $consumer_data['consumer_ip'] = $request->ip();
        $consumer_data['consumer_url'] = __FUNCTION__;

        $authController = new AppSettingController();
        $authenticate = $authController->apiAuthenticate($consumer_data);

        if ($authenticate == 1) {

          if ($type == "filter_a_z") {
              $sortby = "products_name";
              $order = "ASC";
          } elseif ($type == "filter_z_a") {
              $sortby = "products_name";
              $order = "DESC";
          } elseif ($type == "filter_price_high_low") {
              $sortby = "products_price";
              $order = "DESC";
          } elseif ($type == "filter_price_low_high") {
              $sortby = "products_price";
              $order = "ASC";
          } elseif ($type == "topseller") {
              $sortby = "products_ordered";
              $order = "DESC";
          } elseif ($type == "mostliked") {
              $sortby = "products_liked";
              $order = "DESC";
          } elseif ($type == "special") { //deals special products
              $sortby = "specials.products_id";
              $order = "desc";
          } elseif ($type == "flashsale") { //flashsale products
              $sortby = "flash_sale.flash_start_date";
              $order = "asc";
          }
          else {
              $sortby = "products.products_id";
              $order = "asc";
          }

           $products = Product::where('products_status', '=', '1');
            if ($type == "is_feature") {
                $products->where('is_feature', '=', 1);
            }
            if ($type == "topseller") {
                $products->where('products_ordered', '>', 0);
            }
            if ($type == "mostliked") {
                $products->where('products_liked', '>', 0);
            }
            //get single products
            if (!empty($request->products_id) && $request->products_id != "") {
                $products->where('products_id', '=', $request->products_id);
            }

            //for min and maximum price
            if (!empty($maxPrice)) {
                $products->whereBetween('discounted_price', [$minPrice, $maxPrice]);
            }
            if(!empty($filter_type)){
                if($filter_type == 'AMT'){
                    if($filter_value  > 0 and $threshold_value > 0){
                        $products->whereBetween('discounted_price', [$filter_value, $threshold_value]);
                    } else if($filter_value  > 0 and ($condition == ">" or $condition == "<")) {
                        $products->where('discounted_price', $condition, $filter_value);
                    }
                } else if($filter_type == "PER"){
                    if($filter_value  > 0 and $threshold_value > 0){
                        $products->whereBetween('discount_per', [$filter_value, $threshold_value]);
                    } else if($filter_value  > 0 and ($condition == ">" or $condition == "<")) {
                        $products->where('discount_per', $condition, $filter_value);
                    }
                }
            }
            if($type != "flash_sale"){
                $products->whereNotIn('products_id', function ($query) {
                    $query->select('flash_sale.products_id')->from('flash_sale');
                });

            }
            $products->with(['description' => function($q) use ($language_id){
                return $q->select(['products_id', 'products_name as name', 'products_description as description', 'products_url as url'])->where('language_id', '=', $language_id);
            }])
            ->with(['manufacturer' => function($q){
                return $q->select(['manufacturers_id', 'manufacturer_name as name', 'manufacturers_slug as slug', 'manufacturer_image_url as image']);
            }])->with(['images']);
            if ($request->has('filters')) {
                $filtersArray = json_decode($request->filters, true);
                $filter_attributes = array();
                foreach ($filtersArray as $filterO) {
                    $filter = json_decode($filterO, true);
                    if(isset($filter["key"]) && $filter["key"] == "attributes" && count($filter["options"]) > 0){
                        $filter_attributes = $filter["options"];
                        break;
                    }
                }
                if(count($filter_attributes) > 0){
                    $productOptionsIds = array();
                    $productOptionsValuesIds = array();
                    for ($j = 0; $j < count($filter_attributes); $j++) {
                        if(isset($filter_attributes[$j]["option"]["id"])){
                            array_push($productOptionsIds, $filter_attributes[$j]["option"]["id"]);
                        }
                        if(isset($filter_attributes[$j]["value"]) and count($filter_attributes[$j]["value"]) > 0){
                            for ($k = 0; $k < count($filter_attributes[$j]["value"]); $k++) {
                                array_push($productOptionsValuesIds, $filter_attributes[$j]["value"][$k]["options_values_id"]);
                            }
                        }
                    }
                    if(count($productOptionsIds) > 0 and count($productOptionsValuesIds) > 0){
                        $products->whereHas('product_attributes', function($query) use ($productOptionsIds, $productOptionsValuesIds)  {
                            if(count($productOptionsIds) == 1){
                                $query->where('options_id', '<', $productOptionsIds[0])
                                    ->orWhere('options_id', '>', $productOptionsIds[0])
                                    ->orWhere(function ($query2) use ($productOptionsIds, $productOptionsValuesIds) {
                                        return $query2->whereIn('options_id', $productOptionsIds)
                                        ->whereIn('options_values_id', $productOptionsValuesIds);
                                    });
                            } else {
                                return $query->whereIn('options_id', $productOptionsIds)
                                ->whereIn('options_values_id', $productOptionsValuesIds);
                            }
                        });
                    }
                }
            }
            
            if($request->has('categories_id') and !empty($request->categories_id)){
                $categoryId = $request->categories_id;
                $products->whereHas('categories', function($query) use ($categoryId)  {
                    $query->where('categories_id', $categoryId);
                });
                $products->with(['categories' => function($q) use ($language_id, $categoryId){
                    return $q->select(['products_id', 'categories_id'])->where('categories_id', '=', $categoryId)
                        ->with(['category' => function($q) use ($language_id, $categoryId){
                            return $q->select(['categories_id', 'categories_image_url as image', 'categories_slug as slug'])->where('categories_id', '=', $categoryId)
                                    ->with(['description' => function($q) use ($language_id){
                                    return $q->select(['categories_id', 'categories_name as name'])->where('language_id', '=', $language_id);
                                }]);
                        }]);
                }]);
            }
            if($type == "wishlist"){
                $customerId = $request->customers_id;
                $products->with(['wishlist' => function($q) use ($customerId){
                    return $q->where('liked_customers_id', '=', $customerId);
                }]);
            }
            if($type == "flash_sale"){
                $products->with(['flash_sale' => function($q) use ($currentDate){
                    return $q->where('flash_expires_date', '>', $currentDate);
                }]);
            }
            $products->orderBy($sortby, $order);
            //count
            $total_record = $products->count();

          $data = $products->skip($skip)->take(20)->get();
          $result = array();
          //check if record exist
          if (count($data) > 0) {
              $index = 0;
              $liked_customers_id = "";
                if (!empty(auth()->user()->id)) {
                    $liked_customers_id = auth()->user()->id;
                }
                
                foreach ($data as $products_data) {
                    Log::debug(__CLASS__."::".__FUNCTION__." Product Id = ".$products_data->products_id);

                  //for flashsale currency price start
                  if ($type == "flashsale"){
                    $current_price = $products_data->flash_price;
                    $flash_price = self::convertprice($current_price, $requested_currency);
                    $products_data->flash_price = $flash_price;
                  }

                  //for flashsale currency price end
                  $products_id = $products_data->products_id;

                  $reviews = $products_data->review;

                  $avarage_rate = 0;
                  $total_user_rated = 0;

                  if (count($reviews) > 0) {

                      $five_star = 0;
                      $five_count = 0;

                      $four_star = 0;
                      $four_count = 0;

                      $three_star = 0;
                      $three_count = 0;

                      $two_star = 0;
                      $two_count = 0;

                      $one_star = 0;
                      $one_count = 0;

                      foreach ($reviews as $review) {

                          //five star ratting
                          if ($review->reviews_rating == '5') {
                              $five_star += $review->reviews_rating;
                              $five_count++;
                          }

                          //four star ratting
                          if ($review->reviews_rating == '4') {
                              $four_star += $review->reviews_rating;
                              $four_count++;
                          }
                          //three star ratting
                          if ($review->reviews_rating == '3') {
                              $three_star += $review->reviews_rating;
                              $three_count++;
                          }
                          //two star ratting
                          if ($review->reviews_rating == '2') {
                              $two_star += $review->reviews_rating;
                              $two_count++;
                          }

                          //one star ratting
                          if ($review->reviews_rating == '1') {
                              $one_star += $review->reviews_rating;
                              $one_count++;
                          }

                      }

                      $five_ratio = round($five_count / count($reviews) * 100);
                      $four_ratio = round($four_count / count($reviews) * 100);
                      $three_ratio = round($three_count / count($reviews) * 100);
                      $two_ratio = round($two_count / count($reviews) * 100);
                      $one_ratio = round($one_count / count($reviews) * 100);

                      $avarage_rate = (5 * $five_star + 4 * $four_star + 3 * $three_star + 2 * $two_star + 1 * $one_star) / ($five_star + $four_star + $three_star + $two_star + $one_star);
                      $total_user_rated = count($reviews);
                      $reviewed_customers = $reviews;
                  } else {
                      $reviewed_customers = array();
                      $avarage_rate = 0;
                      $total_user_rated = 0;

                      $five_ratio = 0;
                      $four_ratio = 0;
                      $three_ratio = 0;
                      $two_ratio = 0;
                      $one_ratio = 0;
                  }

                  $products_data->rating = number_format($avarage_rate, 2);
                  $products_data->total_user_rated = $total_user_rated;

                  $products_data->five_ratio = $five_ratio;
                  $products_data->four_ratio = $four_ratio;
                  $products_data->three_ratio = $three_ratio;
                  $products_data->two_ratio = $two_ratio;
                  $products_data->one_ratio = $one_ratio;

                  //review by users
                  $products_data->reviewed_customers = $reviewed_customers;
                  array_push($result, $products_data);
                  $attr = array();

                  $stocks = 0;
                  $stockOut = 0;
                  $defaultStock = 0;
                  if ($products_data->products_type == '0') {
                      $stocks = Inventory::where('products_id', $products_data->products_id)->where('stock_type', 'in')->sum('stock');
                      $stockOut = Inventory::where('products_id', $products_data->products_id)->where('stock_type', 'out')->sum('stock');
                      $defaultStock = $stocks - $stockOut;
                  }

                  if ($products_data->products_max_stock < $defaultStock && $products_data->products_max_stock > 0) {
                      $result[$index]->defaultStock = $products_data->products_max_stock;
                  } else {
                      $result[$index]->defaultStock = $defaultStock;
                  }

                  //like product
                  if (!empty($liked_customers_id)) {
                      $categories = WishList::where('liked_products_id', '=', $products_id)->where('liked_customers_id', '=', $liked_customers_id)->get();
                      if (count($categories) > 0) {
                          $result[$index]->isLiked = '1';
                      } else {
                          $result[$index]->isLiked = '0';
                      }
                  } else {
                      $result[$index]->isLiked = '0';
                  }

                  // fetch all options add join from products_options table for option name
                  $products_attribute = ProductAttribute::where('products_id', '=', $products_id)->groupBy('options_id')->get();
                  if (count($products_attribute) > 0) {
                      $index2 = 0;
                      foreach ($products_attribute as $attribute_data) {
                            $option = ProductOption::where('products_options_id', '=', $attribute_data->options_id)
                                ->with(['description' => function($q) use ($language_id){
                                return $q->where('language_id', '=', $language_id);
                            }])->first();
                          if (isset($option->products_options_id)) {
                              $temp = array();
                              $temp_option['id'] = $attribute_data->options_id;
                              $temp_option['name'] = $option->description->options_name;
                              $temp_option['swatch_type'] = $option->swatch_type;
                              $attr[$index2]['option'] = $temp_option;

                              // fetch all attributes add join from products_options_values table for option value name
                              $attributes_value_query = ProductAttribute::where('products_id', '=', $products_id)->where('options_id', '=', $attribute_data->options_id)->get();
                              foreach ($attributes_value_query as $products_option_value) {

                                    $option_value = ProductOptionValue::where('products_options_values_id', '=', $products_option_value->options_values_id)
                                         ->with(['description' => function($q) use ($language_id){
                                        return $q->where('language_id', '=', $language_id);
                                    }])->first();

                                  $attributes = ProductAttribute::where([['products_id', '=', $products_id], ['options_id', '=', $attribute_data->options_id], ['options_values_id', '=', $products_option_value->options_values_id]])->first();
                                  $temp_i['products_attributes_id'] = $attributes->products_attributes_id;
                                  $temp_i['options_values_id'] = $products_option_value->options_values_id;
                                  if (isset($option_value->products_options_values_id) and !empty($option_value->description->options_values_name)) {
                                      $temp_i['name'] = $option_value->description->options_values_name;
                                      $temp_i['value'] = $option_value->description->options_values;
                                  } else {
                                      $temp_i['name'] = '';
                                      $temp_i['value'] = '';
                                  }

                                  //check currency start
                                  $current_price = $products_option_value->options_values_price;

                                  $attribute_price = self::convertprice($current_price, $requested_currency);


                                  //check currency end

                                  //$temp_i['price'] = $products_option_value->options_values_price;
                                  $temp_i['price'] = $attribute_price;
                                  $temp_i['price_prefix'] = $products_option_value->price_prefix;
                                  $temp_i['is_default'] = $products_option_value->is_default;
                                  array_push($temp, $temp_i);

                              }
                              $attr[$index2]['values'] = $temp;
                              $result[$index]->product_attributes = $attr;
                              $index2++;
                          }
                      }
                  } else {
                      $result[$index]->product_attributes = array();
                  }

                  if (!empty($request->products_id) && $request->products_id != "") {
                        $postCategoryId = '';
                        if (!empty($data[0]->categories) and count($data[0]->categories) > 0) {
                            $i = 0;
                            foreach ($data[0]->categories as $postCategory) {
                                if ($i == 0) {
                                    $postCategoryId = $postCategory->categories_id;
                                    $i++;
                                }
                            }
                        }
                        $data2 = array('categories_id' => $postCategoryId);
                        $simliar_products = self::similarProducts($data2);
                        $result[$index]->similar_products = $simliar_products['product_data'];
                    }
                    $index++;
              }
              return returnResponse("Returned all products.", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $result, null, $total_record);
            } else {
              return returnResponse("No products found.", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS,$result);
          }
      } 
      return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
  }

  
  //get Liked Products
  
  public static function getlikedproducts($request)
  {
      //$language_id = $request->language_id;
      $language_id = 1;
            if($request->has('language_id')){
            $language_id = $request->language_id;
            }
      if($request->has('page')) {  
          
      $skip = $request->page . '0';
      }else{
          $skip = '0';
      }
      
      
      $currentDate = time();
      
      
     
      //$maxPrice = $request->input('maxPrice');
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

          
              $sortby = "products.products_id";
              $order = "asc";
          

         

              $categories = DB::table('products')
                  ->leftJoin('manufacturers', 'manufacturers.manufacturers_id', '=', 'products.manufacturers_id')
                  ->leftJoin('manufacturers_info', 'manufacturers.manufacturers_id', '=', 'manufacturers_info.manufacturers_id')
                  ->leftJoin('products_description', 'products_description.products_id', '=', 'products.products_id');


             

              //wishlist customer id
             
                  $categories->LeftJoin('liked_products', 'liked_products.liked_products_id', '=', 'products.products_id')
                  ->select(DB::raw(time() . ' as server_time'), 'products.*', 'products_description.*', 'manufacturers.*', 'manufacturers_info.manufacturers_url', 'products.products_image_url as products_image');
            


              

             
                  $categories->where('liked_customers_id', '=', auth()->user()->id);
              

              $categories->where('products_description.language_id', '=', $language_id)
                  ->where('products.products_status', '=', '1')
                  ->orderBy($sortby, $order);

             
              //count
              $total_record = $categories->get();

              $data = $categories->skip($skip)->take(10)->get();
              $result = array();
              $result2 = array();
              //check if record exist
              if (count($data) > 0) {
                  $index = 0;
                  
                  foreach ($data as $products_data) {

                        //check currency start
                        $requested_currency = 'INR';
                        $current_price = $products_data->products_price;
                        //dd($current_price, $requested_currency);

                        $products_price = Product::convertprice($current_price, $requested_currency);
                        ////////// for discount price    /////////
                        if(!empty($products_data->discount_price)){
                            $discount_price = Product::convertprice($products_data->discount_price, $requested_currency);
                            
                            if(($products_data->products_price+0)>0){
                                $discounted_price = $products_data->products_price - $discount_price;
                                $discount_percentage = $discounted_price/$products_data->products_price*100;
                                $products_data->discount_percent = intval($discount_percentage);
                                $products_data->discounted_price = $discount_price;
                               }else{
                                 $products_data->discount_percent = 0;
                                 $products_data->discounted_price = 0;
                                                }
                              $products_data->discount_price = $discounted_price;
                        }else{
                                 $products_data->discount_percent = 0;
                                 $products_data->discounted_price = 0;
                                                }

                      $products_data->products_price = $products_price;
                      $products_data->currency = $requested_currency;
                      //check currency end


                      //for flashsale currency price end
                      $products_id = $products_data->products_id;
                      

                      

                      array_push($result, $products_data);
                      $options = array();
                      $attr = array();

                      $stocks = 0;
                      $stockOut = 0;
                      $defaultStock = 0;
                      if ($products_data->products_type == '0') {
                          $stocks = DB::table('inventory')->where('products_id', $products_data->products_id)->where('stock_type', 'in')->sum('stock');
                          $stockOut = DB::table('inventory')->where('products_id', $products_data->products_id)->where('stock_type', 'out')->sum('stock');
                          $defaultStock = $stocks - $stockOut;
                      }

                      if ($products_data->products_max_stock < $defaultStock && $products_data->products_max_stock > 0) {
                          $result[$index]->defaultStock = $products_data->products_max_stock;
                      } else {
                          $result[$index]->defaultStock = $defaultStock;
                      }

                      //like product
                      if (!empty(auth()->user()->id)) {
                          $liked_customers_id = auth()->user()->id;
                          $categories = DB::table('liked_products')->where('liked_products_id', '=', $products_id)->where('liked_customers_id', '=', $liked_customers_id)->get();
                          if (count($categories) > 0) {
                              $result[$index]->isLiked = '1';
                          } else {
                              $result[$index]->isLiked = '0';
                          }
                      } else {
                          $result[$index]->isLiked = '0';
                      }

                      
                     

                        $index++;
                  }
                // $result['total_data']->count = $total_record;
                 // $responseData = array('success' => '1', 'product_data' => $result, 'message' => "Returned all products.", 'total_record' => count($total_record));
                  return returnResponse("Returned all Liked products.", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS,$result);
                      } else {
                  //$responseData = array('success' => '0', 'product_data' => $result, 'message' => "Empty record.", 'total_record' => count($total_record));
                  return returnResponse("No Liked products found.", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS,$result);
              }
          
      } 
      return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
  }
  
  public static function likeproduct($request)
  {
      $liked_products_id = $request->liked_products_id;
      $liked_customers_id = auth()->user()->id;
      $date_liked = date('Y-m-d H:i:s');
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

          //to avoide duplicate record
          DB::table('liked_products')->where([
              'liked_products_id' => $liked_products_id,
              'liked_customers_id' => $liked_customers_id,
          ])->delete();

          DB::table('liked_products')->insert([
              'liked_products_id' => $liked_products_id,
              'liked_customers_id' => $liked_customers_id,
              'date_liked' => $date_liked,
          ]);

          $response = DB::table('liked_products')->select('liked_products_id')->where('liked_customers_id', '=', $liked_customers_id)->get();
          DB::table('products')->where('products_id', '=', $liked_products_id)->increment('products_liked');

          //$responseData = array('success' => '1', 'product_data' => $response, 'message' => "Product is liked.");
         return returnResponse("Product is liked.", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS,$response);
      } 
      return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
  }

  public static function unlikeproduct($request)
  {
      $liked_products_id = $request->liked_products_id;
      $liked_customers_id = auth()->user()->id;
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
          DB::table('liked_products')->where([
              'liked_products_id' => $liked_products_id,
              'liked_customers_id' => $liked_customers_id,
          ])->delete();

          DB::table('products')->where('products_id', '=', $liked_products_id)->decrement('products_liked');

          $response = DB::table('liked_products')->select('liked_products_id')->where('liked_customers_id', '=', $liked_customers_id)->get();
          //$responseData = array('success' => '1', 'product_data' => $response, 'message' => "Product is unliked.");
         return returnResponse("Product is unliked.", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS,$response);
      } 
      return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
  }

  public static function getfilters($request) {
        //$language_id = $request->language_id;
        $language_id = 1;
        if ($request->has('language_id')) {
            $language_id = $request->language_id;
        }
        $categories_id = $request->categories_id;
        $currentDate = time();
        $consumer_data = getallheaders();
        $consumer_data['consumer_ip'] = $request->ip();
        $consumer_data['consumer_url'] = __FUNCTION__;
        $authController = new AppSettingController();
        $authenticate = $authController->apiAuthenticate($consumer_data);

        if ($authenticate == 1) {

            $price = DB::table('products_to_categories')
                    ->leftJoin('categories', 'categories.categories_id', '=', 'products_to_categories.categories_id')
                    ->join('products', 'products.products_id', '=', 'products_to_categories.products_id');
            if (isset($categories_id) and ! empty($categories_id)) {
                $price->where('products_to_categories.categories_id', '=', $categories_id);
            }
            $price->where('categories.parent_id', '!=', '0');

            $priceContent = $price->max('products.discounted_price');

            if (!empty($priceContent)) {
                $maxPrice = $priceContent;
            } else {
                $maxPrice = '';
            }

            $product = DB::table('products')
                    ->leftJoin('products_description', 'products_description.products_id', '=', 'products.products_id')
                    ->leftJoin('manufacturers', 'manufacturers.manufacturers_id', '=', 'products.manufacturers_id')
                    ->leftJoin('manufacturers_info', 'manufacturers.manufacturers_id', '=', 'manufacturers_info.manufacturers_id')
                    ->LeftJoin('specials', function ($join) use ($currentDate) {
                $join->on('specials.products_id', '=', 'products.products_id')->where('specials.status', '=', '1')->where('expires_date', '>', $currentDate);
            });

            if (isset($categories_id) and ! empty($categories_id)) {
                $product->LeftJoin('products_to_categories', 'products.products_id', '=', 'products_to_categories.products_id')->select('products_to_categories.*', 'products.*', 'products_description.*', 'manufacturers.*', 'manufacturers_info.manufacturers_url', 'specials.specials_new_products_price as discount_price');
            } else {
                $product->select('products.*', 'products_description.*', 'manufacturers.*', 'manufacturers_info.manufacturers_url', 'specials.specials_new_products_price as discount_price');
            }
            $product->where('products_description.language_id', '=', $language_id);

            if (isset($categories_id) and ! empty($categories_id)) {
                $product->where('products_to_categories.categories_id', '=', $categories_id);
            }

            $products = $product->get();

            $index = 0;
            $optionsIdArray = array();
            $valueIdArray = array();
            foreach ($products as $products_data) {
                $option_name = DB::table('products_attributes')->where('products_id', '=', $products_data->products_id)->get();
                foreach ($option_name as $option_data) {

                    if (!in_array($option_data->options_id, $optionsIdArray)) {
                        $optionsIdArray[] = $option_data->options_id;
                    }

                    if (!in_array($option_data->options_values_id, $valueIdArray)) {
                        $valueIdArray[] = $option_data->options_values_id;
                    }
                }
            }
            $filters = array();
            $result = array();
            $attr = array();
            if (!empty($optionsIdArray)) {
                $index3 = 0;
                foreach ($optionsIdArray as $optionsIdArray) {
                    $option_name = DB::table('products_options')
                                    ->leftJoin('products_options_descriptions', 'products_options_descriptions.products_options_id', '=', 'products_options.products_options_id')
                            ->select('products_options.products_options_id', 'products_options.swatch_type as swatch_type', 'products_options_descriptions.options_name as products_options_name', 'products_options_descriptions.language_id')->where('language_id', '=', $language_id)->where('products_options.products_options_id', '=', $optionsIdArray)->get();
                    if (count($option_name) > 0) {
                        $attribute_opt_val = DB::table('products_options_values')->where('products_options_id', $optionsIdArray)->get();
                        if (count($attribute_opt_val) > 0) {
                            $temp = array();
                            $temp_name['id'] = $option_name[0]->products_options_id;
                            $temp_name['name'] = $option_name[0]->products_options_name;
                            $temp_name['swatch_type'] = $option_name[0]->swatch_type;
                            //$attr[$index3]['option'] = $temp_name;
                            foreach ($attribute_opt_val as $attribute_opt_val_data) {

                                $attribute_value = DB::table('products_options_values')
                                                ->leftJoin('products_options_values_descriptions', 'products_options_values_descriptions.products_options_values_id', '=', 'products_options_values.products_options_values_id')
                                                ->select('products_options_values.products_options_values_id', 'products_options_values_descriptions.options_values', 'products_options_values_descriptions.options_values_name as products_options_values_name', 'products_options_values_descriptions.language_id')
                                                ->where('products_options_values.products_options_values_id', $attribute_opt_val_data->products_options_values_id)->where('language_id', $language_id)->get();

                                foreach ($attribute_value as $attribute_value_data) {

                                    if (in_array($attribute_value_data->products_options_values_id, $valueIdArray)) {
                                        $temp_value['value'] = $attribute_value_data->options_values;
                                        $temp_value['name'] = $attribute_value_data->products_options_values_name;
                                        $temp_value['options_values_id'] = $attribute_value_data->products_options_values_id;
                                        array_push($temp, $temp_value);
                                    }
                                }
                                //$attr[$index3]['values'] = $temp;
                            }
                            array_push($attr, array('option' => $temp_name, "multi_select" => true,'values' => $temp));
                            $index3++;
                        }
                    }
                }
            }
            
            $delivery = array("key" => "delivery", "title" => "Delivery", "is_active" => true, "options" => array(
                    array("option" => array("id" => 0, "name" => "Shipping Options", "swatch_type" => "text"), "multi_select" => false, "values" => array(
                        array("value" => "is_cod", "name" => "Eligible for Pay On Delivery", "options_values_id" => 0, "is_selected" => false)
                    )),
                )
            );
            array_push($filters, $delivery);
            $attributes = array("key" => "attributes", "title" => "Attributes", "is_active" => false, "options" => $attr);
            array_push($filters, $attributes);
            $other_filters = array("key" => "other_filters", "title" => "Other Filters", "is_active" => false, "options" => array(
                array("option" => array("id" => 0, "name" => "Availability", "swatch_type" => "text"), "multi_select" => false, "values" => array(
                    array("value" => "include_out_of_stock", "name" => "Include Out Of Stock", "options_values_id" => 0, "is_selected" => false),
                )),
                array("option" => array("id" => 0, "name" => "New Arrivals", "swatch_type" => "text"), "multi_select" => false, "values" => array(
                    array("value" => "last_15_days", "name" => "Last 15 days", "options_values_id" => 0, "is_selected" => false),
                    array("value" => "last_30_days", "name" => "Last 30 days", "options_values_id" => 0, "is_selected" => false),
                    array("value" => "last_90_days", "name" => "Last 90 days", "options_values_id" => 0, "is_selected" => false),
                )),
            ));
            array_push($filters, $other_filters);
            $category = array("key" => "category", "title" => "Category", "is_active" => false, "options" => array(
                array("option" => array("id" => 0, "name" => "Categories", "swatch_type" => "text"), "multi_select" => false, "values" => array(
                    array("value" => "all", "name" => "All", "options_values_id" => 0, "is_selected" => true),
                )),
            ));
            array_push($filters, $category);
            $brand = array("key" => "brand", "title" => "Brand", "is_active" => false, "options" => array(
                array("option" => array("id" => 0, "name" => "Brands", "swatch_type" => "text"), "multi_select" => true, "values" => array(
                    array("value" => "all", "name" => "All", "options_values_id" => 0, "is_selected" => true),
                )),
            ));
            array_push($filters, $brand);
            $prices = array("key" => "price", "title" => "Price", "is_active" => false, "options" => array(
                    array("option" => array("id" => 0, "name" => "Prices", "swatch_type" => "text"), "multi_select" => false, "values" => array(
                        array("value" => "all", "name" => "All", "options_values_id" => 0, "is_selected" => true),
                        array("value" => "less_than_500", "name" => "Under 500", "options_values_id" => 0, "is_selected" => false),
                        array("value" => "between_500_750", "name" => "500 - 750", "options_values_id" => 0, "is_selected" => false),
                        array("value" => "gteater_than_750", "name" => "Over 750", "options_values_id" => 0, "is_selected" => false),
                    )),
            ));
            array_push($filters, $prices);
            $result['filters'] = $filters;
            $result['max_price'] = $maxPrice;
            return returnResponse("Returned all filters successfully.", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $result);
        }
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
    }

  public static function getfilterproducts($request)
  {
      $language_id = '1';
      $skip = $request->page . '0';
      $categories_id = $request->categories_id;
      $minPrice = $request->price['minPrice'];
      $maxPrice = $request->price['maxPrice'];
      $currentDate = time();

      $filterProducts = array();
      $eliminateRecord = array();
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
          if (!empty($request->filters)) {

              foreach ($request->filters as $filters_attribute) {
                  //print_r($filters_attribute);

                  $getProducts = DB::table('products_to_categories')
                      ->join('products', 'products.products_id', '=', 'products_to_categories.products_id')
                      ->leftJoin('products_description', 'products_description.products_id', '=', 'products.products_id')
                      ->leftJoin('manufacturers', 'manufacturers.manufacturers_id', '=', 'products.manufacturers_id')
                      ->LeftJoin('specials', function ($join) use ($currentDate) {
                          $join->on('specials.products_id', '=', 'products_to_categories.products_id')->where('specials.status', '=', '1')->where('expires_date', '>', $currentDate);
                      })

                      ->leftJoin('manufacturers_info', 'manufacturers.manufacturers_id', '=', 'manufacturers_info.manufacturers_id')
                      ->leftJoin('products_attributes', 'products_attributes.products_id', '=', 'products.products_id')
                      ->leftJoin('products_options', 'products_options.products_options_id', '=', 'products_attributes.options_id')
                      ->leftJoin('products_options_values', 'products_options_values.products_options_values_id', '=', 'products_attributes.options_values_id')

                      ->select('products.*')
                  //->where('products_description.language_id','=', $language_id)
                  //->where('manufacturers_info.languages_id','=', $language_id)
                      ->whereBetween('products.products_price', [$minPrice, $maxPrice])
                      ->where('products_to_categories.categories_id', '=', $categories_id)
                      ->where('products_options.products_options_name', '=', $filters_attribute['name'])
                      ->where('products_options_values.products_options_values_name', '=', $filters_attribute['value'])
                      ->where('categories.parent_id', '!=', '0')
                      ->skip($skip)->take(10)
                      ->groupBy('products.products_id')
                      ->get();

                  if (count($getProducts) > 0) {
                      foreach ($getProducts as $getProduct) {
                          if (!in_array($getProduct->products_id, $eliminateRecord)) {
                              $eliminateRecord[] = $getProduct->products_id;

                              $products = DB::table('products_to_categories')
                                  ->leftJoin('categories', 'categories.categories_id', '=', 'products_to_categories.categories_id')
                                  ->join('categories', 'categories.categories_id', '=', 'products_to_categories.categories_id')
                                  ->leftJoin('categories_description', 'categories_description.categories_id', '=', 'products_to_categories.categories_id')
                                  ->leftJoin('products', 'products.products_id', '=', 'products_to_categories.products_id')
                                  ->leftJoin('products_description', 'products_description.products_id', '=', 'products.products_id')
                                  ->leftJoin('manufacturers', 'manufacturers.manufacturers_id', '=', 'products.manufacturers_id')
                                  ->leftJoin('manufacturers_info', 'manufacturers.manufacturers_id', '=', 'manufacturers_info.manufacturers_id')
                                  ->LeftJoin('specials', function ($join) use ($currentDate) {
                                      $join->on('specials.products_id', '=', 'products_to_categories.products_id')->where('specials.status', '=', '1')->where('expires_date', '>', $currentDate);
                                  })
                                  ->select('products_to_categories.*', 'categories_description.categories_name', 'categories.*', 'products.*', 'products_description.*', 'manufacturers.*', 'manufacturers_info.manufacturers_url', 'specials.specials_new_products_price as discount_price')
                                  ->where('products.products_id', '=', $getProduct->products_id)
                                  ->where('categories.parent_id', '!=', '0')
                                  ->get();

                              $result = array();
                              $index = 0;
                              foreach ($products as $products_data) {
                                  //check currency start
                                  $requested_currency = $request->currency_code;
                                  $current_price = $products_data->products_price;

                                  if($current_currency == $request->currency){
                                    $products_price = $current_price;
                                  }else{
                                    $products_price = Product::convertprice($current_price,  $requested_currency);
                                    ////////// for discount price    /////////
                                    if (!empty($products_data->discount_price)) {
                                            $discount_price = Product::convertprice($products_data->discount_price, $requested_currency);
                                            
                                            if (($products_data->products_price + 0) > 0) {
                                                $discounted_price = $products_data->products_price - $discount_price;
                                                $discount_percentage = $discounted_price / $products_data->products_price * 100;
                                                $products_data->discount_percent = intval($discount_percentage);
                                                $products_data->discounted_price = $discount_price;
                                            } else {
                                                $products_data->discount_percent = 0;
                                                $products_data->discounted_price = 0;
                                            }
                                            $products_data->discount_price = $discount_price;
                                        }
                                    }

                                  $products_data->products_price = $products_price;
                                  $products_data->currency = $requested_currency;
                                  //check currency end

                                  $products_id = $products_data->products_id;

                                  $detail = DB::table('products_description')->where('products_id', '=', $products_id)->get();
                                  $index3 = 0;
                                  foreach ($detail as $detail_data) {

                                      //get function from other controller
                                      $myVar = new AdminSiteSettingController();
                                      $languages = $myVar->getSingleLanguages($detail_data->language_id);

                                      $result2[$languages[$index3]->code] = $detail_data;
                                      $index3++;
                                  }
                                  //multiple images
                                  $products_images = DB::table('products_images')
                                      ->select('products_images.*', 'products_images.image_url as image')
                                      ->where('products_id', '=', $products_id)->orderBy('sort_order', 'ASC')->get();

                                  $categories = DB::table('products_to_categories')
                                      ->leftjoin('categories', 'categories.categories_id', 'products_to_categories.categories_id')
                                      ->leftjoin('categories_description', 'categories_description.categories_id', 'products_to_categories.categories_id')
                                      ->select('categories.categories_id', 'categories_description.categories_name', 'categories.categories_image_url as categories_image', 'categories.categories_icon_url as .categories_icon', 'categories.parent_id')
                                      ->where('products_id', '=', $products_id)
                                      ->where('categories_description.language_id', '=', $language_id)->get();

                                  $products_data->categories = $categories;
                                  $reviews = DB::table('reviews')
                                      ->leftjoin('users', 'users.id', '=', 'reviews.customers_id')
                                      ->where('products_id', $products_data->products_id)
                                      ->where('reviews_status', '1')
                                      ->get();

                                  $avarage_rate = 0;
                                  $total_user_rated = 0;

                                  if (count($reviews) > 0) {

                                      $five_star = 0;
                                      $five_count = 0;

                                      $four_star = 0;
                                      $four_count = 0;

                                      $three_star = 0;
                                      $three_count = 0;

                                      $two_star = 0;
                                      $two_count = 0;

                                      $one_star = 0;
                                      $one_count = 0;

                                      foreach ($reviews as $review) {

                                          //five star ratting
                                          if ($review->reviews_rating == '5') {
                                              $five_star += $review->reviews_rating;
                                              $five_count++;
                                          }

                                          //four star ratting
                                          if ($review->reviews_rating == '4') {
                                              $four_star += $review->reviews_rating;
                                              $four_count++;
                                          }
                                          //three star ratting
                                          if ($review->reviews_rating == '3') {
                                              $three_star += $review->reviews_rating;
                                              $three_count++;
                                          }
                                          //two star ratting
                                          if ($review->reviews_rating == '2') {
                                              $two_star += $review->reviews_rating;
                                              $two_count++;
                                          }

                                          //one star ratting
                                          if ($review->reviews_rating == '1') {
                                              $one_star += $review->reviews_rating;
                                              $one_count++;
                                          }

                                      }

                                      $five_ratio = round($five_count / count($reviews) * 100);
                                      $four_ratio = round($four_count / count($reviews) * 100);
                                      $three_ratio = round($three_count / count($reviews) * 100);
                                      $two_ratio = round($two_count / count($reviews) * 100);
                                      $one_ratio = round($one_count / count($reviews) * 100);

                                      $avarage_rate = (5 * $five_star + 4 * $four_star + 3 * $three_star + 2 * $two_star + 1 * $one_star) / ($five_star + $four_star + $three_star + $two_star + $one_star);
                                      $total_user_rated = count($reviews);
                                      $reviewed_customers = $reviews;
                                  } else {
                                      $reviewed_customers = array();
                                      $avarage_rate = 0;
                                      $total_user_rated = 0;

                                      $five_ratio = 0;
                                      $four_ratio = 0;
                                      $three_ratio = 0;
                                      $two_ratio = 0;
                                      $one_ratio = 0;
                                  }

                                  $products_data->rating = number_format($avarage_rate, 2);
                                  $products_data->total_user_rated = $total_user_rated;

                                  $products_data->five_ratio = $five_ratio;
                                  $products_data->four_ratio = $four_ratio;
                                  $products_data->three_ratio = $three_ratio;
                                  $products_data->two_ratio = $two_ratio;
                                  $products_data->one_ratio = $one_ratio;

                                  //review by users
                                  $products_data->reviewed_customers = $reviewed_customers;

                                  array_push($result, $products_data);
                                  $options = array();
                                  $attr = array();

                                  //like product
                                  if (!empty($request->customers_id)) {
                                      $liked_customers_id = $request->customers_id;
                                      $categories = DB::table('liked_products')->where('liked_products_id', '=', $products_id)->where('liked_customers_id', '=', $liked_customers_id)->get();

                                      if (count($categories) > 0) {
                                          $result[$index]->isLiked = '1';
                                      } else {
                                          $result[$index]->isLiked = '0';
                                      }
                                  } else {
                                      $result[$index]->isLiked = '0';
                                  }

                                  $stocks = 0;
                                  $stockOut = 0;
                                  $defaultStock = 0;
                                  if ($products_data->products_type == '0') {
                                      $stocks = DB::table('inventory')->where('products_id', $products_data->products_id)->where('stock_type', 'in')->sum('stock');
                                      $stockOut = DB::table('inventory')->where('products_id', $products_data->products_id)->where('stock_type', 'out')->sum('stock');
                                      $defaultStock = $stocks - $stockOut;
                                  }

                                  if ($products_data->products_max_stock < $defaultStock && $products_data->products_max_stock > 0) {
                                      $result[$index]->defaultStock = $products_data->products_max_stock;
                                  } else {
                                      $result[$index]->defaultStock = $defaultStock;
                                  }

                                  //get function from other controller
                                  $myVar = new AdminSiteSettingController();
                                  $languages = $myVar->getLanguages();
                                  $data = array();
                                  foreach ($languages as $languages_data) {
                                      $products_attribute = DB::table('products_attributes')->where('products_id', '=', $products_id)->groupBy('options_id')->get();
                                      if (count($products_attribute) > 0) {
                                          $index2 = 0;
                                          foreach ($products_attribute as $attribute_data) {
                                              $option_name = DB::table('products_options')
                                                  ->leftJoin('products_options_descriptions', 'products_options_descriptions.products_options_id', '=', 'products_options.products_options_id')->select('products_options.products_options_id', 'products_options_descriptions.options_name as products_options_name', 'products_options_descriptions.language_id')->where('language_id', '=', $language_id)->where('products_options.products_options_id', '=', $attribute_data->options_id)->get();

                                              if (count($option_name) > 0) {
                                                  $temp = array();
                                                  $temp_option['id'] = $attribute_data->options_id;
                                                  $temp_option['name'] = $option_name[0]->products_options_name;
                                                  $attr[$index2]['option'] = $temp_option;

                                                  // fetch all attributes add join from products_options_values table for option value name
                                                  $attributes_value_query = DB::table('products_attributes')->where('products_id', '=', $products_id)->where('options_id', '=', $attribute_data->options_id)->get();
                                                  foreach ($attributes_value_query as $products_option_value) {
                                                      $option_value = DB::table('products_options_values')->leftJoin('products_options_values_descriptions', 'products_options_values_descriptions.products_options_values_id', '=', 'products_options_values.products_options_values_id')->select('products_options_values.products_options_values_id', 'products_options_values_descriptions.options_values_name as products_options_values_name','products_options_values_descriptions.options_values as options_values')->where('products_options_values_descriptions.language_id', '=', $language_id)->where('products_options_values.products_options_values_id', '=', $products_option_value->options_values_id)->get();
                                                      $attributes = DB::table('products_attributes')->where([['products_id', '=', $products_id], ['options_id', '=', $attribute_data->options_id], ['options_values_id', '=', $products_option_value->options_values_id]])->get();
                                                      $temp_i['products_attributes_id'] = $attributes[0]->products_attributes_id;
                                                      $temp_i['id'] = $products_option_value->options_values_id;
                                                      $temp_i['value'] = $option_value[0]->options_values;
                                                      $temp_i['name'] = $option_value[0]->products_options_values_name;

                                                      //check currency start
                                                      $current_price = $products_option_value->options_values_price;

                                                      $attribute_price = Product::convertprice($current_price, $requested_currency);


                                                      //check currency end
                                                      $temp_i['price'] = $attribute_price;
                                                      $temp_i['price_prefix'] = $products_option_value->price_prefix;
                                                      $temp_i['is_default'] = $products_option_value->is_default;
                                                      array_push($temp, $temp_i);

                                                  }
                                                  $attr[$index2]['values'] = $temp;
                                                  $data[$languages_data->code] = $attr;
                                                  $result[$index]->detail = $result2;
                                                  $index2++;
                                              }

                                          }
                                          $result[$index]->attributes = $data;
                                      } else {
                                          $result[$index]->attributes = array();
                                      }
                                  }
                                  $index++;
                              }
                          }
                      }
                      $responseData = array('success' => '1', 'product_data' => $filterProducts, 'message' => "Returned all products.", 'total_record' => count($index));
                  } else {
                      $total_record = array();
                      $responseData = array('success' => '0', 'product_data' => $filterProducts, 'message' => "Empty record.", 'total_record' => count($total_record));
                  }
              }
          } else {

              $total_record = DB::table('products_to_categories')
                  ->leftJoin('categories', 'categories.categories_id', '=', 'products_to_categories.categories_id')
                  ->join('products', 'products.products_id', '=', 'products_to_categories.products_id')
                  ->leftJoin('products_description', 'products_description.products_id', '=', 'products.products_id')
                  ->leftJoin('manufacturers', 'manufacturers.manufacturers_id', '=', 'products.manufacturers_id')
                  ->leftJoin('manufacturers_info', 'manufacturers.manufacturers_id', '=', 'manufacturers_info.manufacturers_id')
                  ->LeftJoin('specials', function ($join) use ($currentDate) {
                      $join->on('specials.products_id', '=', 'products_to_categories.products_id')->where('specials.status', '=', '1')->where('expires_date', '>', $currentDate);
                  })
                  ->whereBetween('products.products_price', [$minPrice, $maxPrice])
                  ->where('products_to_categories.categories_id', '=', $categories_id)
                  ->where('categories.parent_id', '!=', '0')
                  ->get();

              $products = DB::table('products_to_categories')
                  ->leftJoin('categories', 'categories.categories_id', '=', 'products_to_categories.categories_id')
                  ->join('products', 'products.products_id', '=', 'products_to_categories.products_id')
                  ->leftJoin('products_description', 'products_description.products_id', '=', 'products.products_id')
                  ->leftJoin('manufacturers', 'manufacturers.manufacturers_id', '=', 'products.manufacturers_id')
                  ->leftJoin('manufacturers_info', 'manufacturers.manufacturers_id', '=', 'manufacturers_info.manufacturers_id')
                  ->LeftJoin('specials', function ($join) use ($currentDate) {
                      $join->on('specials.products_id', '=', 'products_to_categories.products_id')->where('specials.status', '=', '1')->where('expires_date', '>', $currentDate);
                  })
                  ->select('products_to_categories.*', 'products.*', 'products_description.*', 'manufacturers.*', 'manufacturers_info.manufacturers_url', 'specials.specials_new_products_price as discount_price')
                  ->whereBetween('products.products_price', [$minPrice, $maxPrice])
                  ->where('products_to_categories.categories_id', '=', $categories_id)
                  ->where('categories.parent_id', '!=', '0')
                  ->skip($skip)->take(10)
                  ->get();

              $result = array();
              //check if record exist
              if (count($products) > 0) {
                  $index = 0;
                  foreach ($products as $products_data) {
                      //check currency start
                      $requested_currency = $request->currency_code;
                      $current_price = $products_data->products_price;

                        $products_price = Product::convertprice($current_price, $requested_currency);
                        ////////// for discount price    /////////
                        if(!empty($products_data->discount_price)){
                            $discount_price = Product::convertprice($products_data->discount_price, $requested_currency);
                            
                            if(($products_data->products_price+0)>0){
                                $discounted_price = $products_data->products_price - $discount_price;
                                $discount_percentage = $discounted_price/$products_data->products_price*100;
                                $products_data->discount_percent = intval($discount_percentage);
                                $products_data->discounted_price = $discount_price;
                               }else{
                                 $products_data->discount_percent = 0;
                                 $products_data->discounted_price = 0;
                                                }
                                $products_data->discount_price = $discount_price;
                        }else{
                                 $products_data->discount_percent = 0;
                                 $products_data->discounted_price = 0;
                                                }


                      $products_data->products_price = $products_price;
                      $products_data->currency = $requested_currency;
                      //check currency end
                      $products_id = $products_data->products_id;

                      //multiple images
                      $products_images = DB::table('products_images')
                          ->select('products_images.*', 'products_images.image_url as image')
                          ->where('products_id', '=', $products_id)->orderBy('sort_order', 'ASC')->get();

                      $categories = DB::table('products_to_categories')
                          ->leftjoin('categories', 'categories.categories_id', 'products_to_categories.categories_id')
                          ->leftjoin('categories_description', 'categories_description.categories_id', 'products_to_categories.categories_id')
                          ->select('categories.categories_id', 'categories_description.categories_name', 'categories.categories_image_url as categories_image', 'categories.categories_icon_url as categories_icon', 'categories.parent_id')
                          ->where('products_id', '=', $products_id)
                          ->where('categories_description.language_id', '=', $language_id)->get();

                      $products_data->categories = $categories;

                      $reviews = DB::table('reviews')
                          ->leftjoin('users', 'users.id', '=', 'reviews.customers_id')
                          ->select('reviews.*', 'users.avatar as image')
                          ->where('products_id', $products_data->products_id)
                          ->where('reviews_status', '1')
                          ->get();

                      $avarage_rate = 0;
                      $total_user_rated = 0;

                      if (count($reviews) > 0) {

                          $five_star = 0;
                          $five_count = 0;

                          $four_star = 0;
                          $four_count = 0;

                          $three_star = 0;
                          $three_count = 0;

                          $two_star = 0;
                          $two_count = 0;

                          $one_star = 0;
                          $one_count = 0;

                          foreach ($reviews as $review) {

                              //five star ratting
                              if ($review->reviews_rating == '5') {
                                  $five_star += $review->reviews_rating;
                                  $five_count++;
                              }

                              //four star ratting
                              if ($review->reviews_rating == '4') {
                                  $four_star += $review->reviews_rating;
                                  $four_count++;
                              }
                              //three star ratting
                              if ($review->reviews_rating == '3') {
                                  $three_star += $review->reviews_rating;
                                  $three_count++;
                              }
                              //two star ratting
                              if ($review->reviews_rating == '2') {
                                  $two_star += $review->reviews_rating;
                                  $two_count++;
                              }

                              //one star ratting
                              if ($review->reviews_rating == '1') {
                                  $one_star += $review->reviews_rating;
                                  $one_count++;
                              }

                          }

                          $five_ratio = round($five_count / count($reviews) * 100);
                          $four_ratio = round($four_count / count($reviews) * 100);
                          $three_ratio = round($three_count / count($reviews) * 100);
                          $two_ratio = round($two_count / count($reviews) * 100);
                          $one_ratio = round($one_count / count($reviews) * 100);

                          $avarage_rate = (5 * $five_star + 4 * $four_star + 3 * $three_star + 2 * $two_star + 1 * $one_star) / ($five_star + $four_star + $three_star + $two_star + $one_star);
                          $total_user_rated = count($reviews);
                          $reviewed_customers = $reviews;
                      } else {
                          $reviewed_customers = array();
                          $avarage_rate = 0;
                          $total_user_rated = 0;

                          $five_ratio = 0;
                          $four_ratio = 0;
                          $three_ratio = 0;
                          $two_ratio = 0;
                          $one_ratio = 0;
                      }

                      $products_data->rating = number_format($avarage_rate, 2);
                      $products_data->total_user_rated = $total_user_rated;

                      $products_data->five_ratio = $five_ratio;
                      $products_data->four_ratio = $four_ratio;
                      $products_data->three_ratio = $three_ratio;
                      $products_data->two_ratio = $two_ratio;
                      $products_data->one_ratio = $one_ratio;

                      //review by users
                      $products_data->reviewed_customers = $reviewed_customers;

                      array_push($result, $products_data);
                      $options = array();
                      $attr = array();

                      //like product
                      if (!empty($request->customers_id)) {
                          $liked_customers_id = $request->customers_id;
                          $categories = DB::table('liked_products')->where('liked_products_id', '=', $products_id)->where('liked_customers_id', '=', $liked_customers_id)->get();
                          //print_r($categories);
                          if (count($categories) > 0) {
                              $result[$index]->isLiked = '1';
                          } else {
                              $result[$index]->isLiked = '0';
                          }
                      } else {
                          $result[$index]->isLiked = '0';
                      }

                      $stocks = 0;
                      $stockOut = 0;
                      $defaultStock = 0;
                      if ($products_data->products_type == '0') {
                          $stocks = DB::table('inventory')->where('products_id', $products_data->products_id)->where('stock_type', 'in')->sum('stock');
                          $stockOut = DB::table('inventory')->where('products_id', $products_data->products_id)->where('stock_type', 'out')->sum('stock');
                          $defaultStock = $stocks - $stockOut;
                      }

                      if ($products_data->products_max_stock < $defaultStock && $products_data->products_max_stock > 0) {
                          $result[$index]->defaultStock = $products_data->products_max_stock;
                      } else {
                          $result[$index]->defaultStock = $defaultStock;
                      }

                      // fetch all options add join from products_options table for option name
                      $products_attribute = DB::table('products_attributes')->where('products_id', '=', $products_id)->groupBy('options_id')->get();
                      if (count($products_attribute) > 0) {
                          $index2 = 0;
                          foreach ($products_attribute as $attribute_data) {
                              $option_name = DB::table('products_options')
                                  ->leftJoin('products_options_descriptions', 'products_options_descriptions.products_options_id', '=', 'products_options.products_options_id')->select('products_options.products_options_id', 'products_options_descriptions.options_name as products_options_name', 'products_options_descriptions.language_id')->where('language_id', '=', $language_id)->where('products_options.products_options_id', '=', $attribute_data->options_id)->get();
                              $temp = array();
                              $temp_option['id'] = $attribute_data->options_id;
                              $temp_option['name'] = $option_name[0]->products_options_name;
                              $attr[$index2]['option'] = $temp_option;

                              // fetch all attributes add join from products_options_values table for option value name

                              $attributes_value_query = DB::table('products_attributes')->where('products_id', '=', $products_id)->where('options_id', '=', $attribute_data->options_id)->get();
                              foreach ($attributes_value_query as $products_option_value) {
                                  $option_value = DB::table('products_options_values')->leftJoin('products_options_values_descriptions', 'products_options_values_descriptions.products_options_values_id', '=', 'products_options_values.products_options_values_id')->select('products_options_values.products_options_values_id', 'products_options_values_descriptions.options_values_name as products_options_values_name')->where('products_options_values_descriptions.language_id', '=', $language_id)->where('products_options_values.products_options_values_id', '=', $products_option_value->options_values_id)->get();
                                  $attributes = DB::table('products_attributes')->where([['products_id', '=', $products_id], ['options_id', '=', $attribute_data->options_id], ['options_values_id', '=', $products_option_value->options_values_id]])->get();
                                  $temp_i['products_attributes_id'] = $attributes[0]->products_attributes_id;
                                  $temp_i['id'] = $products_option_value->options_values_id;
                                  $temp_i['value'] = $option_value[0]->products_options_values_name;
                                  //check currency start
                                  $current_price = $products_option_value->options_values_price;


                                 $attribute_price = Product::convertprice($current_price, $requested_currency);


                                  //check currency end

                                  $temp_i['price'] = $attribute_price;

                                  $temp_i['price_prefix'] = $products_option_value->price_prefix;
                                  array_push($temp, $temp_i);

                              }
                              $attr[$index2]['values'] = $temp;
                              $result[$index]->attributes = $attr;
                              $index2++;

                          }
                      } else {
                          $result[$index]->attributes = array();
                      }
                      $index++;
                  }
                  $responseData = array('success' => '1', 'product_data' => $result, 'message' => "Returned all products.", 'total_record' => count($total_record));
              } else {
                  $total_record = array();
                  $responseData = array('success' => '0', 'product_data' => $result, 'message' => "Empty record.", 'total_record' => count($total_record));
              }

          }
      } else {
          $responseData = array('success' => '0', 'product_data' => array(), 'message' => "Unauthenticated call.");
      }
      $categoryResponse = json_encode($responseData);

      return $categoryResponse;
  }

  public static function getsearchdata($request)
  {
       $validator = Validator::make($request->all(), [
            'searchValue' => 'required',
        ]);
        
        if ($validator->fails()) {
            Log::error(__CLASS__."::".__FUNCTION__."Validation failed !");
            return returnResponse($validator->errors(), HttpStatus::HTTP_UNPROCESSABLE_ENTITY);
        }
      
      //$language_id = $request->language_id;
      $language_id = 1;
            if($request->has('language_id')){
            $language_id = $request->language_id;
            }
      $searchValue = $request->searchValue;
      $currentDate = time();

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

          $mainCategories = DB::table('categories')
              ->leftJoin('categories_description', 'categories_description.categories_id', '=', 'categories.categories_id')
              ->select('categories.categories_id as id', 'categories.categories_image_url as image', 'categories_description.categories_name as name')
              ->where('categories_description.categories_name', 'LIKE', '%' . $searchValue . '%')
              ->where('categories_description.language_id', '=', $language_id)
              ->where('parent_id', '0')->get();

          $result['mainCategories'] = $mainCategories;

          $subCategories = DB::table('categories')
              ->leftJoin('categories_description', 'categories_description.categories_id', '=', 'categories.categories_id')
              ->select('categories.categories_id as id', 'categories.categories_image_url as image', 'categories_description.categories_name as name')
              ->where('categories_description.categories_name', 'LIKE', '%' . $searchValue . '%')
              ->where('categories_description.language_id', '=', $language_id)
              ->where('parent_id', '1')->get();

          $result['subCategories'] = $subCategories;

          $manufacturers = DB::table('manufacturers')
              ->leftJoin('manufacturers_info', 'manufacturers_info.manufacturers_id', '=', 'manufacturers.manufacturers_id')
              ->select('manufacturers.manufacturers_id as id', 'manufacturers.manufacturer_image_url as image', 'manufacturers.manufacturer_name as name')
              ->where('manufacturers.manufacturer_name', 'LIKE', '%' . $searchValue . '%')
              ->get();

          $productsAttribute = DB::table('products')
              ->leftJoin('products_description', 'products_description.products_id', '=', 'products.products_id')
              ->leftJoin('manufacturers', 'manufacturers.manufacturers_id', '=', 'products.manufacturers_id')
              ->leftJoin('manufacturers_info', 'manufacturers.manufacturers_id', '=', 'manufacturers_info.manufacturers_id')
              ->leftJoin('products_attributes', 'products_attributes.products_id', '=', 'products.products_id')
              ->leftJoin('products_options', 'products_options.products_options_id', '=', 'products_attributes.options_id')
              ->leftJoin('products_options_descriptions', 'products_options_descriptions.products_options_id', '=', 'products_options.products_options_id')
              ->leftJoin('products_options_values', 'products_options_values.products_options_values_id', '=', 'products_attributes.options_values_id')
              ->leftJoin('products_options_values_descriptions', 'products_options_values_descriptions.products_options_values_id', '=', 'products_options_values.products_options_values_id')
              ->LeftJoin('specials', function ($join) use ($currentDate) {
                  $join->on('specials.products_id', '=', 'products.products_id')->where('specials.status', '=', '1')->where('expires_date', '>', $currentDate);
              })

              ->leftJoin('flash_sale', 'flash_sale.products_id', '=', 'products.products_id')
          //->select(DB::raw(time().' as server_time'),'products.*','products_description.*', 'manufacturers.*', 'manufacturers_info.manufacturers_url', 'specials.specials_new_products_price as discount_price','image_categories.path as products_image','users.*')

              ->select(DB::raw(time() . ' as server_time'), 'products.*', 'products_description.*', 'manufacturers.*', 'manufacturers_info.manufacturers_url', 'specials.specials_new_products_price as discount_price', 'specials.specials_new_products_price as discount_price', 'products.products_image_url as products_image')

              ->orWhere('products_options_descriptions.options_name', 'LIKE', '%' . $searchValue . '%')
              ->whereNotIn('products.products_id', function ($query) {
                  $query->select('flash_sale.products_id')->from('flash_sale');
              })
              ->where('products_description.language_id', '=', $language_id)
              ->where('products.products_status', '=', 1)
              ->orWhere('products_options_values_descriptions.options_values_name', 'LIKE', '%' . $searchValue . '%')
              ->whereNotIn('products.products_id', function ($query) {
                  $query->select('flash_sale.products_id')->from('flash_sale');
              })
              ->where('products_description.language_id', '=', $language_id)
              ->where('products.products_status', '=', 1)
              ->orWhere('products_name', 'LIKE', '%' . $searchValue . '%')
              ->whereNotIn('products.products_id', function ($query) {
                  $query->select('flash_sale.products_id')->from('flash_sale');
              })
              ->where('products_description.language_id', '=', $language_id)
              ->where('products.products_status', '=', 1)
              ->orWhere('products_model', 'LIKE', '%' . $searchValue . '%')
              ->whereNotIn('products.products_id', function ($query) {
                  $query->select('flash_sale.products_id')->from('flash_sale');
              })
              ->where('products_description.language_id', '=', $language_id)
              ->where('products.products_status', '=', 1)
              ->groupBy('products.products_id')
              ->get();

          $result2 = array();
          //check if record exist
          if (count($productsAttribute) > 0) {
              $index = 0;
              foreach ($productsAttribute as $products_data) {
                  //check currency start
                  $requested_currency = $request->currency_code;
                  $current_price = $products_data->products_price;

                    $products_price = Product::convertprice($current_price, $requested_currency);
                    ////////// for discount price    /////////
                    if(!empty($products_data->discount_price)){
                            $discount_price = Product::convertprice($products_data->discount_price, $requested_currency);
                            
                            if(($products_data->products_price+0)>0){
                                $discounted_price = $products_data->products_price - $discount_price;
                                $discount_percentage = $discounted_price/$products_data->products_price*100;
                                $products_data->discount_percent = intval($discount_percentage);
                                $products_data->discounted_price = $discount_price;
                               }else{
                                 $products_data->discount_percent = 0;
                                 $products_data->discounted_price = 0;
                                                }
                            $products_data->discount_price = $discount_price;
                        }else{
                                 $products_data->discount_percent = 0;
                                 $products_data->discounted_price = 0;
                                                }


                  $products_data->products_price = $products_price;
                  $products_data->currency = $requested_currency;
                  //check currency end

                  $products_id = $products_data->products_id;

                  //multiple images
                  $products_images = DB::table('products_images')
                      ->select('products_images.*', 'products_images.image_url as image')
                      ->where('products_id', '=', $products_id)->orderBy('sort_order', 'ASC')->get();

                  $categories = DB::table('products_to_categories')
                      ->leftjoin('categories', 'categories.categories_id', 'products_to_categories.categories_id')
                      ->leftjoin('categories_description', 'categories_description.categories_id', 'products_to_categories.categories_id')
                      ->select('categories.categories_id', 'categories_description.categories_name', 'categories.categories_image_url as categories_image', 'categories.categories_icon_url as categories_icon', 'categories.parent_id')
                      ->where('products_id', '=', $products_id)
                      ->where('categories_description.language_id', '=', $language_id)->get();

                  $products_data->categories = $categories;

                  $reviews = DB::table('reviews')
                      ->leftjoin('users', 'users.id', '=', 'reviews.customers_id')
                      ->select('reviews.*', 'users.avatar as image')
                      ->where('products_id', $products_data->products_id)
                      ->where('reviews_status', '1')
                      ->get();

                  $avarage_rate = 0;
                  $total_user_rated = 0;

                  if (count($reviews) > 0) {

                      $five_star = 0;
                      $four_star = 0;
                      $three_star = 0;
                      $two_star = 0;
                      $one_star = 0;
                      foreach ($reviews as $review) {

                          //five star ratting
                          if ($review->reviews_rating == '5') {
                              $five_star += $review->reviews_rating;
                          }

                          //four star ratting
                          if ($review->reviews_rating == '4') {
                              $four_star += $review->reviews_rating;
                          }
                          //three star ratting
                          if ($review->reviews_rating == '3') {
                              $three_star += $review->reviews_rating;
                          }
                          //two star ratting
                          if ($review->reviews_rating == '2') {
                              $two_star += $review->reviews_rating;
                          }

                          //one star ratting
                          if ($review->reviews_rating == '1') {
                              $one_star += $review->reviews_rating;
                          }

                      }

                      $avarage_rate = (5 * $five_star + 4 * $four_star + 3 * $three_star + 2 * $two_star + 1 * $one_star) / ($five_star + $four_star + $three_star + $two_star + $one_star);
                      $total_user_rated = count($reviews);

                  } else {
                      $avarage_rate = 0;
                      $total_user_rated = 0;
                  }

                  $products_data->rating = number_format($avarage_rate, 2);
                  $products_data->total_user_rated = $total_user_rated;

                  array_push($result2, $products_data);
                  $options = array();
                  $attr = array();

                  //like product
                  if (!empty($request->customers_id)) {
                      $liked_customers_id = $request->customers_id;
                      $categories = DB::table('liked_products')->where('liked_products_id', '=', $products_id)->where('liked_customers_id', '=', $liked_customers_id)->get();
                      if (count($categories) > 0) {
                          $result2[$index]->isLiked = '1';
                      } else {
                          $result2[$index]->isLiked = '0';
                      }
                  } else {
                      $result2[$index]->isLiked = '0';
                  }

                  // fetch all options add join from products_options table for option name
                  $products_attribute = DB::table('products_attributes')->where('products_id', '=', $products_id)->groupBy('options_id')->get();
                  if (count($products_attribute) > 0) {
                      $index2 = 0;
                      foreach ($products_attribute as $attribute_data) {
                          $option_name = DB::table('products_options')
                              ->leftJoin('products_options_descriptions', 'products_options_descriptions.products_options_id', '=', 'products_options.products_options_id')->select('products_options.products_options_id', 'products_options_descriptions.options_name as products_options_name', 'products_options_descriptions.language_id')->where('language_id', '=', $language_id)->where('products_options.products_options_id', '=', $attribute_data->options_id)->get();
                          if (count($option_name) > 0) {
                              $temp = array();
                              $temp_option['id'] = $attribute_data->options_id;
                              $temp_option['name'] = $option_name[0]->products_options_name;
                              $attr[$index2]['option'] = $temp_option;

                              // fetch all attributes add join from products_options_values table for option value name
                              $attributes_value_query = DB::table('products_attributes')->where('products_id', '=', $products_id)->where('options_id', '=', $attribute_data->options_id)->get();
                              foreach ($attributes_value_query as $products_option_value) {
                                  $option_value = DB::table('products_options_values')->leftJoin('products_options_values_descriptions', 'products_options_values_descriptions.products_options_values_id', '=', 'products_options_values.products_options_values_id')->select('products_options_values.products_options_values_id', 'products_options_values_descriptions.options_values_name as products_options_values_name','products_options_values_descriptions.options_values as options_values')->where('products_options_values_descriptions.language_id', '=', $language_id)->where('products_options_values.products_options_values_id', '=', $products_option_value->options_values_id)->get();
                                  $attributes = DB::table('products_attributes')->where([['products_id', '=', $products_id], ['options_id', '=', $attribute_data->options_id], ['options_values_id', '=', $products_option_value->options_values_id]])->get();
                                  $temp_i['products_attributes_id'] = $attributes[0]->products_attributes_id;
                                  $temp_i['id'] = $products_option_value->options_values_id;
                                  $temp_i['name'] = $option_value[0]->products_options_values_name;
                                  $temp_i['value'] = $option_value[0]->options_values;
                                  //check currency start
                                  $current_price = $products_option_value->options_values_price;


                                  $attribute_price = Product::convertprice($current_price, $requested_currency);

                                  //check currency end

                                  $temp_i['price'] = $attribute_price;
                                  $temp_i['price_prefix'] = $products_option_value->price_prefix;
                                  $temp_i['is_default'] = $products_option_value->is_default;
                                  
                                  array_push($temp, $temp_i);

                              }
                              $attr[$index2]['values'] = $temp;
                              $result2[$index]->attributes = $attr;
                              $index2++;
                          }
                      }
                  } else {
                      $result2[$index]->attributes = array();
                  }
                  $index++;
              }

          }

          $result['products'] = $result2;
          
          $total_record = count($result['products']) + count($result['subCategories']) + count($result['mainCategories']);
          $result['total_record'] = $total_record;

          if (count($result['products']) == 0 and count($result['subCategories']) == 0 and count($result['mainCategories']) == 0) {
              //$result = new \stdClass();
              //$responseData = array('success' => '0', 'product_data' => $result, 'message' => "Search result is not found.", 'total_record' => $total_record);
              return returnResponse("Search result is not found.", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $result);
          } else {
              return returnResponse("Returned all searched products.", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $result);
              //$responseData = array('success' => '1', 'product_data' => $result, 'message' => "Returned all searched products.", 'total_record' => $total_record);
          }

      } 
      return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
  }
  
  public static function getsearchsuggestions($request)
  {
       $validator = Validator::make($request->all(), [
            'q' => 'required',
        ]);
        
        if ($validator->fails()) {
            Log::error(__CLASS__."::".__FUNCTION__."Validation failed !");
            return returnResponse($validator->errors(), HttpStatus::HTTP_UNPROCESSABLE_ENTITY);
        }
      
      //$language_id = $request->language_id;
      $language_id = 1;
            if($request->has('language_id')){
            $language_id = $request->language_id;
            }
      $query = $request->q;
      $currentDate = time();

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

          array_push($result, array('cat_id' => null, 'name' => $query, 'middle' => null, 'category' => null));
          

          return returnResponse("Returned all searched products.", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $result);

      } 
      return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
  }
    public static function getHomeSections($request)
    {
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
        $language_id = 1;
          if($request->has('language_id')){
              $language_id = $request->language_id;
          }
        if ($authenticate == 1) {
              $currentDate = time();
              $result = array();
              $homeSections = DB::table('home_sections')->where('status', '1')->get();
              if(count($homeSections) > 0){
                  foreach($homeSections as $data){
                      $ids = explode(",", $data->ids);
                      if(count($ids) > 0){
                          if(count($ids) > 0){
                              if($data->type == "category"){
                                 $catArray = array();
                                  for($i=0; $i<count($ids); $i++){
                                      $catData = DB::table('categories')
                                          ->leftJoin('categories_description', 'categories_description.categories_id', '=', 'categories.categories_id')
                                          ->select('categories.categories_id as categories_id', 'categories.parent_id as parent_id', 'categories.categories_image_url as image', 'categories_description.categories_name as categories_name', 'categories.sort_order as total_products')
                                          ->where('categories.categories_id', $ids[$i])
                                          ->first();
                                          array_push($catArray, $catData);
                                  }
                                  if(count($catArray) > 0){
                                      $data->cat_data = $catArray;
                                  }
                              } else if($data->type == "product"){
                                 $productArray = array();
                                  for($i=0; $i<count($ids); $i++){
                                      $products = DB::table('products_to_categories')
                                      ->leftJoin('categories', 'categories.categories_id', '=', 'products_to_categories.categories_id')
                                      ->leftJoin('categories_description', 'categories_description.categories_id', '=', 'products_to_categories.categories_id')
                                      ->leftJoin('products', 'products.products_id', '=', 'products_to_categories.products_id')
                                      ->leftJoin('products_description', 'products_description.products_id', '=', 'products.products_id')
                                      ->leftJoin('manufacturers', 'manufacturers.manufacturers_id', '=', 'products.manufacturers_id')
                                      ->leftJoin('manufacturers_info', 'manufacturers.manufacturers_id', '=', 'manufacturers_info.manufacturers_id')
                                      ->leftJoin('specials', function ($join) use ($currentDate) {
                                          $join->on('specials.products_id', '=', 'products_to_categories.products_id')->where('specials.status', '=', '1')->where('expires_date', '>', $currentDate);
                                      })

                                      ->select('products_to_categories.*', 'products.*', 'products.products_image_url as products_image', 'products_description.*', 'manufacturers.*', 'manufacturers_info.manufacturers_url', 'specials.specials_new_products_price as discount_price', 'products_to_categories.categories_id', 'categories_description.*')
                                      ->where('categories_description.language_id', '=', $language_id)
                                      ->where('products_description.language_id', '=', $language_id)
                                      ->where('products.products_id', '=', $ids[$i])
                                      ->where('categories.parent_id', '!=', '0')
                                      ->get();
                                  $result2 = array();
                                  $index = 0;
                                  foreach ($products as $products_data) {
                                    //check currency start
                                    $requested_currency = $request->currency_code;
                                    $current_price = $products_data->products_price;


                                      $products_price = Product::convertprice($current_price, $requested_currency);

                                      ////////// for discount price    /////////

                                      if (!empty($products_data->discount_price)) {
                                            $discount_price = Product::convertprice($products_data->discount_price, $requested_currency);

                                            if (($products_data->products_price + 0) > 0) {
                                                $discounted_price = $products_data->products_price - $discount_price;
                                                $discount_percentage = $discounted_price / $products_data->products_price * 100;
                                                $products_data->discount_percent = intval($discount_percentage);
                                                $products_data->discounted_price = $discount_price;
                                            } else {
                                                $products_data->discount_percent = 0;
                                                $products_data->discounted_price = 0;
                                            }
                                            $products_data->discount_price = $discount_price;
                                        }

                                        $products_data->products_price = $products_price;
                                    $products_data->currency = $requested_currency;
                                    //check currency end
                                      $products_id = $products_data->products_id;

                                      //multiple images
                                      $products_images = DB::table('products_images')
                                          ->select('products_images.*', 'products_images.image_url as image')
                                          ->where('products_id', '=', $products_id)->orderBy('sort_order', 'ASC')->get();

                                      $categories = DB::table('products_to_categories')
                                          ->leftjoin('categories', 'categories.categories_id', 'products_to_categories.categories_id')
                                          ->leftjoin('categories_description', 'categories_description.categories_id', 'products_to_categories.categories_id')
                                          ->select('categories.categories_id', 'categories_description.categories_name', 'categories.categories_image_url as categories_image', 'categories.categories_icon_url as categories_icon', 'categories.parent_id')
                                          ->where('products_id', '=', $products_id)
                                          ->where('categories_description.language_id', '=', $language_id)->get();

                                      $products_data->categories = $categories;

                                      array_push($result2, $products_data);
                                      $stocks = 0;
                                      $stockOut = 0;
                                      $defaultStock = 0;
                                      if ($products_data->products_type == '0') {
                                          $stocks = DB::table('inventory')->where('products_id', $products_data->products_id)->where('stock_type', 'in')->sum('stock');
                                          $stockOut = DB::table('inventory')->where('products_id', $products_data->products_id)->where('stock_type', 'out')->sum('stock');
                                          $defaultStock = $stocks - $stockOut;
                                      }

                                      if ($products_data->products_max_stock < $defaultStock && $products_data->products_max_stock > 0) {
                                          $result2[$index]->defaultStock = $products_data->products_max_stock;
                                      } else {
                                          $result2[$index]->defaultStock = $defaultStock;
                                      }

                                      $index++;
                                  }
                                  }
                                  $data->product_data = $result2;
                              }
                          }
                      }
                      array_push($result, $data);
                  }
              }

            if (isset($result) and count($result) > 0) {
                return returnResponse("Home section data found.", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $result);
            } else {
                return returnResponse("Home section data not found.", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS);
            }

        }
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
    }
  public static function getquantity($request, $products_id = null, $attributes = null)
  {
      $inventory_ref_id = '';
      $result = array();
      if(empty($products_id)){
          $products_id = $request->input('products_id');
      }
      $productsType = DB::table('products')->where('products_id', $products_id)->get();
      //check products type
      if ($productsType[0]->products_type == 1) {
          if(empty($attributes)){
            $attributes = $request->input('attributes');
          }
          //Log::debug($attributes);
          $attributeid = implode(',', $attributes);

          $postAttributes = count($attributes);

          $inventories = DB::table('inventory')->where('products_id', $products_id)->get();
          $reference_ids = array();
          $stocks = 0;
          $stockIn = 0;
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
                  $stockIn += $individualStock[0]->stock;
              }

          }

          //get option name and value
          $options_names = array();
          $options_values = array();
          foreach ($attributes as $attribute) {
              $productsAttributes = DB::table('products_attributes')
                  ->leftJoin('products_options', 'products_options.products_options_id', '=', 'products_attributes.options_id')
                  ->leftJoin('products_options_values', 'products_options_values.products_options_values_id', '=', 'products_attributes.options_values_id')
                  ->select('products_attributes.*', 'products_options.products_options_name as options_name', 'products_options_values.products_options_values_name as options_values')
                  ->where('products_attributes_id', $attribute)->get();

              $options_names[] = $productsAttributes[0]->options_name;
              $options_values[] = $productsAttributes[0]->options_values;
          }

          $options_names_count = count($options_names);
          $options_names = implode("','", $options_names);
          $options_names = "'" . $options_names . "'";
          $options_values = "'" . implode("','", $options_values) . "'";

          //orders products
          $orders_products = DB::table('orders_products')->where('products_id', $products_id)->get();
          $stockOut = 0;
          foreach ($orders_products as $orders_product) {
              $totalAttribute = DB::table('orders_products_attributes')->where('orders_products_id', '=', $orders_product->orders_products_id)->get();
              $totalAttributes = count($totalAttribute);

              if ($postAttributes > $totalAttributes) {
                  $count = $postAttributes;
              } elseif ($postAttributes < $totalAttributes or $postAttributes == $totalAttributes) {
                  $count = $totalAttributes;
              }

              $products = DB::select("select orders_products.* from `orders_products` left join `orders_products_attributes` on `orders_products_attributes`.`orders_products_id` = `orders_products`.`orders_products_id` where `orders_products`.`products_id`='" . $products_id . "' and `orders_products_attributes`.`products_options` in (" . $options_names . ") and `orders_products_attributes`.`products_options_values` in (" . $options_values . ") and (select count(*) from `orders_products_attributes` where `orders_products_attributes`.`products_id` = '" . $products_id . "' and `orders_products_attributes`.`products_options` in (" . $options_names . ") and `orders_products_attributes`.`products_options_values` in (" . $options_values . ") and `orders_products_attributes`.`orders_products_id`= '" . $orders_product->orders_products_id . "') = " . $count . " and `orders_products`.`orders_products_id` = '" . $orders_product->orders_products_id . "' group by `orders_products_attributes`.`orders_products_id`");

              if (count($products) > 0) {
                  $stockOut += $products[0]->products_quantity;
              }
          }
          $stocks = $stockIn - $stockOut;

      } else {

          $stocks = 0;

          $stocksin = DB::table('inventory')->where('products_id', $products_id)->where('stock_type', 'in')->sum('stock');
          $stockOut = DB::table('inventory')->where('products_id', $products_id)->where('stock_type', 'out')->sum('stock');
          $stocks = $stocksin - $stockOut;
      }

      $responseData = array( 'stock' => $stocks);
      if(empty($products_id)){
          return returnResponse("Product data!", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $responseData);
      }
      return $responseData;
  }

  public static function shppingbyweight($request)
  {
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
          $result = DB::table('products_shipping_rates')->where('products_shipping_status', '1')->get();
          $responseData = array('success' => '1', 'product_data' => $result, 'message' => "Returned all products.", 'total_record' => count($total_record));
      } else {
          $responseData = array('success' => '0', 'data' => $result, 'message' => "Unauthenticated call.");
      }

      $categoryResponse = json_encode($responseData);

      return $categoryResponse;
  }
  
  
  // Pruoducts Function to get filtered product data
  
   //products
    public static function products($data)
    {

        if (empty($data['page']) or $data['page'] == 0) {
            $skip = $data['page'] . '0';
        } else {
            $skip = $data['limit'] * $data['page'];
        }

        $min_price = $data['min_price'];
        $max_price = $data['max_price'];
        $take = $data['limit'];
        $currentDate = time();
        $type = $data['type'];

        if ($type == "atoz") {
            $sortby = "products_name";
            $order = "ASC";
        } elseif ($type == "ztoa") {
            $sortby = "products_name";
            $order = "DESC";
        } elseif ($type == "hightolow") {
            $sortby = "products_price";
            $order = "DESC";
        } elseif ($type == "lowtohigh") {
            $sortby = "products_price";
            $order = "ASC";
        } elseif ($type == "topseller") {
            $sortby = "products_ordered";
            $order = "DESC";
        } elseif ($type == "mostliked") {
            $sortby = "products_liked";
            $order = "DESC";

        } elseif ($type == "special") {
            $sortby = "specials.products_id";
            $order = "desc";
        } elseif ($type == "flashsale") { //flashsale products
            $sortby = "flash_sale.flash_start_date";
            $order = "asc";
        } else {
            $sortby = "products.products_id";
            $order = "desc";
        }

        $filterProducts = array();
        $eliminateRecord = array();

        $categories = DB::table('products')
            ->leftJoin('manufacturers', 'manufacturers.manufacturers_id', '=', 'products.manufacturers_id')
            ->leftJoin('manufacturers_info', 'manufacturers.manufacturers_id', '=', 'manufacturers_info.manufacturers_id')
            ->leftJoin('products_description', 'products_description.products_id', '=', 'products.products_id')
             ;
        if (!empty($data['categories_id'])) {
            $categories->LeftJoin('products_to_categories', 'products.products_id', '=', 'products_to_categories.products_id')
                ->leftJoin('categories', 'categories.categories_id', '=', 'products_to_categories.categories_id')
                ->LeftJoin('categories_description', 'categories_description.categories_id', '=', 'products_to_categories.categories_id');
        }

        if (!empty($data['filters']) and empty($data['search'])) {
            $categories->leftJoin('products_attributes', 'products_attributes.products_id', '=', 'products.products_id');
        }

        if (!empty($data['search'])) {
            $categories->leftJoin('products_attributes', 'products_attributes.products_id', '=', 'products.products_id')
                ->leftJoin('products_options', 'products_options.products_options_id', '=', 'products_attributes.options_id')
                ->leftJoin('products_options_values', 'products_options_values.products_options_values_id', '=', 'products_attributes.options_values_id');
        }
        //wishlist customer id
        if ($type == "wishlist") {
            $categories->LeftJoin('liked_products', 'liked_products.liked_products_id', '=', 'products.products_id')
                ->select('products.*', 'image_categories.path as image_path', 'products_description.*', 'manufacturers.*', 'manufacturers_info.manufacturers_url');

        }
        //parameter special
        elseif ($type == "special") {
            $categories->LeftJoin('specials', 'specials.products_id', '=', 'products.products_id')
                ->select('products.*', 'products.products_image_url as image_path', 'products_description.*', 'manufacturers.*', 'manufacturers_info.manufacturers_url', 'specials.specials_new_products_price as discount_price', 'specials.specials_new_products_price as discount_price');
        } elseif ($type == "flashsale") {
            //flash sale
            $categories->LeftJoin('flash_sale', 'flash_sale.products_id', '=', 'products.products_id')
                ->select(DB::raw(time() . ' as server_time'), 'products.*', 'products.products_image_url as image_path', 'products_description.*', 'manufacturers.*', 'manufacturers_info.manufacturers_url', 'flash_sale.flash_start_date', 'flash_sale.flash_expires_date', 'flash_sale.flash_sale_products_price as flash_price');

        } elseif ($type == "compare") {
            //flash sale
            $categories->LeftJoin('flash_sale', 'flash_sale.products_id', '=', 'products.products_id')
                ->select(DB::raw(time() . ' as server_time'), 'products.*', 'iproducts.products_image_url as image_path', 'products_description.*', 'manufacturers.*', 'manufacturers_info.manufacturers_url', 'flash_sale.flash_start_date', 'flash_sale.flash_expires_date', 'flash_sale.flash_sale_products_price as discount_price');

        } else {
            $categories->LeftJoin('specials', function ($join) use ($currentDate) {
                $join->on('specials.products_id', '=', 'products.products_id')->where('status', '=', '1')->where('expires_date', '>', $currentDate);
            })->select('products.*', 'products.products_image_url as image_path', 'products_description.*', 'manufacturers.*', 'manufacturers_info.manufacturers_url', 'specials.specials_new_products_price as discount_price');
        }

        if ($type == "special") { //deals special products
            $categories->where('specials.status', '=', '1')->where('expires_date', '>', $currentDate);
        }

        if ($type == "flashsale") { //flashsale
            $categories->where('flash_sale.flash_status', '=', '1')->where('flash_expires_date', '>', $currentDate);

        } elseif ($type != "compare") {
            $categories->whereNotIn('products.products_id', function ($query) use ($currentDate) {
                $query->select('flash_sale.products_id')->from('flash_sale')->where('flash_sale.flash_status', '=', '1');
            });

        }

        //get single products
        if (!empty($data['products_id']) && $data['products_id'] != "") {
            $categories->where('products.products_id', '=', $data['products_id']);
        }

        //for min and maximum price
        if (!empty($max_price)) {

            if (!empty($max_price)) {
                //check session contain default currency
                $current_currency = DB::table('currencies')->where('code', 'INR')->first();
                if($current_currency->is_default == 0){
                    $max_price = $max_price / $current_currency->value;
                    $min_price = $min_price / $current_currency->value;
                }
    
            }

            $categories->whereBetween('products.products_price', [$min_price, $max_price]);
        }

        if (!empty($data['search'])) {

            $searchValue = $data['search'];
            
            $categories->where('products_options.products_options_name', 'LIKE', '%' . $searchValue . '%')->where('products_status', '=', 1);

            if (!empty($data['categories_id'])) {
                $categories->where('products_to_categories.categories_id', '=', $data['categories_id']);
            }

            if (!empty($data['filters'])) {
                $temp_key = 0;
                foreach ($data['filters']['filter_attribute']['option_values'] as $option_id_temp) {

                    if ($temp_key == 0) {

                        $categories->whereIn('products_attributes.options_id', [$data['filters']['options']])
                            ->where('products_attributes.options_values_id', $option_id_temp);
                        if (count($data['filters']['filter_attribute']['options']) > 1) {

                            $categories->where(DB::raw('(select count(*) from `products_attributes` where `products_attributes`.`products_id` = `products`.`products_id` and `products_attributes`.`options_id` in (' . $data['filters']['options'] . ') and `products_attributes`.`options_values_id` in (' . $data['filters']['option_value'] . '))'), '>=', $data['filters']['options_count']);
                        }

                    } else {
                        $categories->orwhereIn('products_attributes.options_id', [$data['filters']['options']])
                            ->where('products_attributes.options_values_id', $option_id_temp);

                        if (count($data['filters']['filter_attribute']['options']) > 1) {
                            $categories->where(DB::raw('(select count(*) from `products_attributes` where `products_attributes`.`products_id` = `products`.`products_id` and `products_attributes`.`options_id` in (' . $data['filters']['options'] . ') and `products_attributes`.`options_values_id` in (' . $data['filters']['option_value'] . '))'), '>=', $data['filters']['options_count']);
                        }

                    }
                    $temp_key++;
                }

            }

            if (!empty($max_price)) {
                $categories->whereBetween('products.products_price', [$min_price, $max_price]);
            }
            $categories->whereNotIn('products.products_id', function ($query) use ($currentDate) {
                $query->select('flash_sale.products_id')->from('flash_sale')->where('flash_sale.flash_status', '=', '1');
            });
            $categories->orWhere('products_options_values.products_options_values_name', 'LIKE', '%' . $searchValue . '%')->where('products_status', '=', 1);
            if (!empty($data['categories_id'])) {
                $categories->where('products_to_categories.categories_id', '=', $data['categories_id']);
            }

            if (!empty($data['filters'])) {
                $temp_key = 0;
                foreach ($data['filters']['filter_attribute']['option_values'] as $option_id_temp) {

                    if ($temp_key == 0) {

                        $categories->whereIn('products_attributes.options_id', [$data['filters']['options']])
                            ->where('products_attributes.options_values_id', $option_id_temp);
                        if (count($data['filters']['filter_attribute']['options']) > 1) {

                            $categories->where(DB::raw('(select count(*) from `products_attributes` where `products_attributes`.`products_id` = `products`.`products_id` and `products_attributes`.`options_id` in (' . $data['filters']['options'] . ') and `products_attributes`.`options_values_id` in (' . $data['filters']['option_value'] . '))'), '>=', $data['filters']['options_count']);
                        }

                    } else {
                        $categories->orwhereIn('products_attributes.options_id', [$data['filters']['options']])
                            ->where('products_attributes.options_values_id', $option_id_temp);

                        if (count($data['filters']['filter_attribute']['options']) > 1) {
                            $categories->where(DB::raw('(select count(*) from `products_attributes` where `products_attributes`.`products_id` = `products`.`products_id` and `products_attributes`.`options_id` in (' . $data['filters']['options'] . ') and `products_attributes`.`options_values_id` in (' . $data['filters']['option_value'] . '))'), '>=', $data['filters']['options_count']);
                        }

                    }
                    $temp_key++;
                }

            }

            if (!empty($max_price)) {
                $categories->whereBetween('products.products_price', [$min_price, $max_price]);
            }

            $categories->whereNotIn('products.products_id', function ($query) use ($currentDate) {
                $query->select('flash_sale.products_id')->from('flash_sale')->where('flash_sale.flash_status', '=', '1');
            });

            $categories->orWhere('products_name', 'LIKE', '%' . $searchValue . '%')->where('products_status', '=', 1);

            if (!empty($data['categories_id'])) {
                $categories->where('products_to_categories.categories_id', '=', $data['categories_id']);
            }

            if (!empty($data['filters'])) {
                $temp_key = 0;
                foreach ($data['filters']['filter_attribute']['option_values'] as $option_id_temp) {

                    if ($temp_key == 0) {

                        $categories->whereIn('products_attributes.options_id', [$data['filters']['options']])
                            ->where('products_attributes.options_values_id', $option_id_temp);
                        if (count($data['filters']['filter_attribute']['options']) > 1) {

                            $categories->where(DB::raw('(select count(*) from `products_attributes` where `products_attributes`.`products_id` = `products`.`products_id` and `products_attributes`.`options_id` in (' . $data['filters']['options'] . ') and `products_attributes`.`options_values_id` in (' . $data['filters']['option_value'] . '))'), '>=', $data['filters']['options_count']);
                        }

                    } else {
                        $categories->orwhereIn('products_attributes.options_id', [$data['filters']['options']])
                            ->where('products_attributes.options_values_id', $option_id_temp);

                        if (count($data['filters']['filter_attribute']['options']) > 1) {
                            $categories->where(DB::raw('(select count(*) from `products_attributes` where `products_attributes`.`products_id` = `products`.`products_id` and `products_attributes`.`options_id` in (' . $data['filters']['options'] . ') and `products_attributes`.`options_values_id` in (' . $data['filters']['option_value'] . '))'), '>=', $data['filters']['options_count']);
                        }

                    }
                    $temp_key++;
                }

            }

            if (!empty($max_price)) {
                $categories->whereBetween('products.products_price', [$min_price, $max_price]);
            }

            $categories->whereNotIn('products.products_id', function ($query) use ($currentDate) {
                $query->select('flash_sale.products_id')->from('flash_sale')->where('flash_sale.flash_status', '=', '1');
            });

            $categories->orWhere('products_model', 'LIKE', '%' . $searchValue . '%')->where('products_status', '=', 1);

            if (!empty($data['categories_id'])) {
                $categories->where('products_to_categories.categories_id', '=', $data['categories_id']);
            }

            if (!empty($data['filters'])) {
                $temp_key = 0;
                foreach ($data['filters']['filter_attribute']['option_values'] as $option_id_temp) {

                    if ($temp_key == 0) {

                        $categories->whereIn('products_attributes.options_id', [$data['filters']['options']])
                            ->where('products_attributes.options_values_id', $option_id_temp);
                        if (count($data['filters']['filter_attribute']['options']) > 1) {

                            $categories->where(DB::raw('(select count(*) from `products_attributes` where `products_attributes`.`products_id` = `products`.`products_id` and `products_attributes`.`options_id` in (' . $data['filters']['options'] . ') and `products_attributes`.`options_values_id` in (' . $data['filters']['option_value'] . '))'), '>=', $data['filters']['options_count']);
                        }

                    } else {
                        $categories->orwhereIn('products_attributes.options_id', [$data['filters']['options']])
                            ->where('products_attributes.options_values_id', $option_id_temp);

                        if (count($data['filters']['filter_attribute']['options']) > 1) {
                            $categories->where(DB::raw('(select count(*) from `products_attributes` where `products_attributes`.`products_id` = `products`.`products_id` and `products_attributes`.`options_id` in (' . $data['filters']['options'] . ') and `products_attributes`.`options_values_id` in (' . $data['filters']['option_value'] . '))'), '>=', $data['filters']['options_count']);
                        }

                    }
                    $temp_key++;
                }

            }
            $categories->whereNotIn('products.products_id', function ($query) use ($currentDate) {
                $query->select('flash_sale.products_id')->from('flash_sale')->where('flash_sale.flash_status', '=', '1');
            });
        }

        if (!empty($data['filters'])) {
            $temp_key = 0;
            foreach ($data['filters']['filter_attribute']['option_values'] as $option_id_temp) {

                if ($temp_key == 0) {

                    $categories->whereIn('products_attributes.options_id', [$data['filters']['options']])
                        ->where('products_attributes.options_values_id', $option_id_temp);
                    if (count($data['filters']['filter_attribute']['options']) > 1) {

                        $categories->where(DB::raw('(select count(*) from `products_attributes` where `products_attributes`.`products_id` = `products`.`products_id` and `products_attributes`.`options_id` in (' . $data['filters']['options'] . ') and `products_attributes`.`options_values_id` in (' . $data['filters']['option_value'] . '))'), '>=', $data['filters']['options_count']);
                    }

                } else {
                    $categories->orwhereIn('products_attributes.options_id', [$data['filters']['options']])
                        ->where('products_attributes.options_values_id', $option_id_temp);

                    if (count($data['filters']['filter_attribute']['options']) > 1) {
                        $categories->where(DB::raw('(select count(*) from `products_attributes` where `products_attributes`.`products_id` = `products`.`products_id` and `products_attributes`.`options_id` in (' . $data['filters']['options'] . ') and `products_attributes`.`options_values_id` in (' . $data['filters']['option_value'] . '))'), '>=', $data['filters']['options_count']);
                    }

                }
                $temp_key++;
            }

        }

        //wishlist customer id
        if ($type == "wishlist" && isset(auth()->user()->id)) {
            $categories->where('liked_customers_id', '=', auth()->user()->id);
        }

        //wishlist customer id
        if ($type == "is_feature") {
            $categories->where('products.is_feature', '=', 1);
        }

        $categories->where('products_description.language_id', '=', '1')->where('products_status', '=', 1);

        //get single category products
        if (!empty($data['categories_id'])) {
            $categories->where('products_to_categories.categories_id', '=', $data['categories_id']);
            $categories->where('categories.categories_status', '=', 1);
            $categories->where('categories_description.language_id', '=', '1');
        }else{
            $categories->LeftJoin('products_to_categories', 'products.products_id', '=', 'products_to_categories.products_id');
                // ->leftJoin('categories', 'categories.categories_id', '=', 'products_to_categories.categories_id');
            $categories->whereIn('products_to_categories.categories_id', function ($query) use ($currentDate) {
                $query->select('categories_id')->from('categories')->where('categories.categories_status',1);
            });
        }

        if ($type == "topseller") {
            $categories->where('products.products_ordered', '>', 0);
        }
        if ($type == "mostliked") {
            $categories->where('products.products_liked', '>', 0);
        }

        $categories->orderBy($sortby, $order)->groupBy('products.products_id');

        //count
        $total_record = $categories->get();
        $products = $categories->skip($skip)->take($take)->get();

        $result = array();
        $result2 = array();

        //check if record exist
        if (count($products) > 0) {

            $index = 0;
            foreach ($products as $products_data) {

                $reviews = DB::table('reviews')
                    ->leftjoin('users', 'users.id', '=', 'reviews.customers_id')
                    ->leftjoin('reviews_description', 'reviews.reviews_id', '=', 'reviews_description.review_id')
                    ->select('reviews.*', 'reviews_description.reviews_text')
                    ->where('products_id', $products_data->products_id)
                    ->where('reviews_status', '1')
                    ->where('reviews_read', '1')
                    ->get();

                $avarage_rate = 0;
                $total_user_rated = 0;

                if (count($reviews) > 0) {
                    $five_star = 0;
                    $five_count = 0;

                    $four_star = 0;
                    $four_count = 0;

                    $three_star = 0;
                    $three_count = 0;

                    $two_star = 0;
                    $two_count = 0;

                    $one_star = 0;
                    $one_count = 0;

                    foreach ($reviews as $review) {

                        //five star ratting
                        if ($review->reviews_rating == '5') {
                            $five_star += $review->reviews_rating;
                            $five_count++;
                        }

                        //four star ratting
                        if ($review->reviews_rating == '4') {
                            $four_star += $review->reviews_rating;
                            $four_count++;
                        }
                        //three star ratting
                        if ($review->reviews_rating == '3') {
                            $three_star += $review->reviews_rating;
                            $three_count++;
                        }
                        //two star ratting
                        if ($review->reviews_rating == '2') {
                            $two_star += $review->reviews_rating;
                            $two_count++;
                        }

                        //one star ratting
                        if ($review->reviews_rating == '1') {
                            $one_star += $review->reviews_rating;
                            $one_count++;
                        }
                    }

                    $five_ratio = round($five_count / count($reviews) * 100);
                    $four_ratio = round($four_count / count($reviews) * 100);
                    $three_ratio = round($three_count / count($reviews) * 100);
                    $two_ratio = round($two_count / count($reviews) * 100);
                    $one_ratio = round($one_count / count($reviews) * 100);
                    if(($five_star + $four_star + $three_star + $two_star + $one_star) > 0){
                        $avarage_rate = (5 * $five_star + 4 * $four_star + 3 * $three_star + 2 * $two_star + 1 * $one_star) / ($five_star + $four_star + $three_star + $two_star + $one_star);
                    }else{
                        $avarage_rate = 0;
                    }
                    $total_user_rated = count($reviews);
                    $reviewed_customers = $reviews;
                } else {
                    $reviewed_customers = array();
                    $avarage_rate = 0;
                    $total_user_rated = 0;

                    $five_ratio = 0;
                    $four_ratio = 0;
                    $three_ratio = 0;
                    $two_ratio = 0;
                    $one_ratio = 0;
                }

                $products_data->rating = number_format($avarage_rate, 2);
                $products_data->total_user_rated = $total_user_rated;

                $products_data->five_ratio = $five_ratio;
                $products_data->four_ratio = $four_ratio;
                $products_data->three_ratio = $three_ratio;
                $products_data->two_ratio = $two_ratio;
                $products_data->one_ratio = $one_ratio;

                //review by users
                $products_data->reviewed_customers = $reviewed_customers;
                $products_id = $products_data->products_id;
                $products_data->image_path = $products_data->products_image_url;
                //products_image
                

                //multiple images
                $products_images = DB::table('products_images')
                    ->select('products_images.*','products_images.image_url as image_path')
                    ->where('products_id', '=', $products_id)
                    ->orderBy('sort_order', 'ASC')
                    ->get();

                $products_data->images = $products_images;

                    $products_data->default_thumb = $products_data->default_images;

                //categories
                $categories = DB::table('products_to_categories')
                    ->leftjoin('categories', 'categories.categories_id', 'products_to_categories.categories_id')
                    ->leftjoin('categories_description', 'categories_description.categories_id', 'products_to_categories.categories_id')
                    ->select('categories.categories_id', 'categories_description.categories_name', 'categories.categories_image_url as categories_image', 'categories.categories_icon_url as categories_icon', 'categories.parent_id', 'categories.categories_slug', 'categories.categories_status')
                    ->where('products_id', '=', $products_id)
                    ->where('categories_description.language_id', '=', '1')
                    ->where('categories.categories_status', 1)
                    ->orderby('parent_id', 'ASC')->get();

                $products_data->categories = $categories;
                array_push($result, $products_data);

                $options = array();
                $attr = array();

                $stocks = 0;
                $stockOut = 0;
                if ($products_data->products_type == '0') {
                    $stocks = DB::table('inventory')->where('products_id', $products_data->products_id)->where('stock_type', 'in')->sum('stock');
                    $stockOut = DB::table('inventory')->where('products_id', $products_data->products_id)->where('stock_type', 'out')->sum('stock');
                }

                $result[$index]->defaultStock = $stocks - $stockOut;

                //like product
                if (!empty(auth()->user()->id)) {
                    $liked_customers_id = auth()->user()->id;
                    $categories = DB::table('liked_products')->where('liked_products_id', '=', $products_id)->where('liked_customers_id', '=', $liked_customers_id)->get();

                    if (count($categories) > 0) {
                        $result[$index]->isLiked = '1';
                    } else {
                        $result[$index]->isLiked = '0';
                    }
                } else {
                    $result[$index]->isLiked = '0';
                }

                // fetch all options add join from products_options table for option name
                $products_attribute = DB::table('products_attributes')->where('products_id', '=', $products_id)->groupBy('options_id')->get();
                if (count($products_attribute)) {
                    $index2 = 0;
                    foreach ($products_attribute as $attribute_data) {

                        $option_name = DB::table('products_options')
                            ->leftJoin('products_options_descriptions', 'products_options_descriptions.products_options_id', '=', 'products_options.products_options_id')->select('products_options.products_options_id', 'products_options_descriptions.options_name as products_options_name', 'products_options_descriptions.language_id')->where('language_id', '=', '1')->where('products_options.products_options_id', '=', $attribute_data->options_id)->get();

                        if (count($option_name) > 0) {

                            $temp = array();
                            $temp_option['id'] = $attribute_data->options_id;
                            $temp_option['name'] = $option_name[0]->products_options_name;
                            $temp_option['is_default'] = $attribute_data->is_default;
                            $attr[$index2]['option'] = $temp_option;

                            // fetch all attributes add join from products_options_values table for option value name
                            $attributes_value_query = DB::table('products_attributes')->where('products_id', '=', $products_id)->where('options_id', '=', $attribute_data->options_id)->get();
                            $k = 0;
                            foreach ($attributes_value_query as $products_option_value) {

                                $option_value = DB::table('products_options_values')->leftJoin('products_options_values_descriptions', 'products_options_values_descriptions.products_options_values_id', '=', 'products_options_values.products_options_values_id')->select('products_options_values.products_options_values_id', 'products_options_values_descriptions.options_values_name as products_options_values_name')->where('products_options_values_descriptions.language_id', '=', '1')->where('products_options_values.products_options_values_id', '=', $products_option_value->options_values_id)->get();

                                $attributes = DB::table('products_attributes')->where([['products_id', '=', $products_id], ['options_id', '=', $attribute_data->options_id], ['options_values_id', '=', $products_option_value->options_values_id]])->get();

                                $temp_i['products_attributes_id'] = $attributes[0]->products_attributes_id;
                                $temp_i['id'] = $products_option_value->options_values_id;
                                $temp_i['value'] = $option_value[0]->products_options_values_name;
                                $temp_i['price'] = $products_option_value->options_values_price;
                                $temp_i['price_prefix'] = $products_option_value->price_prefix;
                                array_push($temp, $temp_i);

                            }
                            $attr[$index2]['values'] = $temp;
                            $result[$index]->attributes = $attr;
                            $index2++;
                        }
                    }
                } else {
                    $result[$index]->attributes = array();
                }
                $index++;
            }
            $responseData = array('success' => '1', 'product_data' => $result, 'message' => 'Returned all products', 'total_record' => count($total_record));

        } else {
            $responseData = array('success' => '0', 'product_data' => $result, 'message' => 'Empty record', 'total_record' => count($total_record));
        }

        return ($responseData);
    }
    
    
    //products
    public static function similarProducts($data)
    {

       
        $language_id = 1;
        
        $take = '10';
        $sortby = "products.products_id";
        $order = "desc";
        $products = Product::where('products_status', '=', '1');
            $products->with(['description' => function($q) use ($language_id){
                return $q->select(['products_id', 'products_name as name', 'products_description as description', 'products_url as url'])->where('language_id', '=', $language_id);
            }])
            ->with(['manufacturer' => function($q){
                return $q->select(['manufacturers_id', 'manufacturer_name as name', 'manufacturers_slug as slug', 'manufacturer_image_url as image']);
            }])->with(['images']);
            //get single category products
            if (!empty($data['categories_id'])) {
                $categoryId = $data['categories_id'];
                $products->whereHas('categories', function($query) use ($categoryId)  {
                    $query->where('categories_id', $categoryId);
                });
                $products->with(['categories' => function($q) use ($language_id, $categoryId){
                    return $q->select(['products_id', 'categories_id'])->where('categories_id', '=', $categoryId)
                        ->with(['category' => function($q) use ($language_id, $categoryId){
                            return $q->select(['categories_id', 'categories_image_url as image', 'categories_slug as slug'])->where('categories_id', '=', $categoryId)
                                    ->with(['description' => function($q) use ($language_id){
                                    return $q->select(['categories_id', 'categories_name as name'])->where('language_id', '=', $language_id);
                                }]);
                        }]);
                }]);
            } else {
                $products->with(['categories' => function($q) use ($language_id){
                    return $q->select(['products_id', 'categories_id'])
                        ->with(['category' => function($q) use ($language_id){
                            return $q->select(['categories_id', 'categories_image_url as image', 'categories_slug as slug'])
                                    ->with(['description' => function($q) use ($language_id){
                                    return $q->select(['categories_id', 'categories_name as name'])->where('language_id', '=', $language_id);
                                }]);
                        }]);
                }]);
            }
            $products->orderBy($sortby, $order);
            //count
            $total_record = $products->count();

            $data = $products->take($take)->get();
          
        $result = array();
        //check if record exist
        if (count($data) > 0) {
            $liked_customers_id = "";
            if (!empty(auth()->user()->id)) {
                $liked_customers_id = auth()->user()->id;
            }
            $index = 0;
            foreach ($data as $products_data) {
                array_push($result, $products_data);
                //like product
                if (!empty($liked_customers_id)) {
                    $likedData = WishList::where('liked_products_id', '=', $products_data->products_id)->where('liked_customers_id', '=', $liked_customers_id)->get();
                    if (count($likedData) > 0) {
                        $result[$index]->isLiked = '1';
                    } else {
                        $result[$index]->isLiked = '0';
                    }
                } else {
                    $result[$index]->isLiked = '0';
                }
                $index++;
            }
            $responseData = array('success' => '1', 'product_data' => $result, 'message' => 'Returned all products', 'total_record' => $total_record);

        } else {
            $responseData = array('success' => '0', 'product_data' => $result, 'message' => 'Empty record', 'total_record' => $total_record);
        }

        return ($responseData);
    }

    public static function cartIdArray($request)
    {

        $cart = DB::table('customers_basket')->where('customers_basket.is_order', '=', '0');

        if (!empty(auth()->user()->id)) {
          
            $cart->where('customers_basket.customers_id', '=', auth()->user()->id);
        }

        $baskit = $cart->get();

        $result = array();
        $index = 0;
        foreach ($baskit as $baskit_data) {
            $result[$index++] = $baskit_data->products_id;
        }

        return ($result);

    }
  
  
    //currentstock
    public static function productQuantity($data)
    {
        if (!empty($data['attributes'])) {
            $inventory_ref_id = '';
            $products_id = $data['products_id'];
            $attributes = array_filter($data['attributes']);
            $attributeid = implode(',', $attributes);
            $postAttributes = count($attributes);

            $inventories = DB::table('inventory')->where('products_id', $products_id)->get();
            $reference_ids = array();
            $stockIn = 0;
            $stockOut = 0;
            $inventory_ref_id = array();
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

                    if ($individualStock[0]->stock_type == 'in') {
                        $stockIn += $individualStock[0]->stock;
                    }

                    if ($individualStock[0]->stock_type == 'out') {
                        $stockOut += $individualStock[0]->stock;
                    }

                    $inventory_ref_id[] = $individualStock[0]->inventory_ref_id;
                }

            }

            //get option name and value
            $options_names = array();
            $options_values = array();
            foreach ($attributes as $attribute) {
                $productsAttributes = DB::table('products_attributes')
                    ->leftJoin('products_options', 'products_options.products_options_id', '=', 'products_attributes.options_id')
                    ->leftJoin('products_options_values', 'products_options_values.products_options_values_id', '=', 'products_attributes.options_values_id')
                    ->select('products_attributes.*', 'products_options.products_options_name as options_name', 'products_options_values.products_options_values_name as options_values')
                    ->where('products_attributes_id', $attribute)->get();

                $options_names[] = $productsAttributes[0]->options_name;
                $options_values[] = $productsAttributes[0]->options_values;
            }

            $options_names_count = count($options_names);
            $options_names = implode("','", $options_names);
            $options_names = "'" . $options_names . "'";
            $options_values = "'" . implode("','", $options_values) . "'";

            //orders products
            $orders_products = DB::table('orders_products')->where('products_id', $products_id)->get();

            $result = array();
            $result['remainingStock'] = $stockIn - $stockOut;

            if (!empty($inventory_ref_id) && isset($inventory_ref_id[0])) {
                $minMax = DB::table('manage_min_max')->where([['inventory_ref_id', $inventory_ref_id[0]], ['products_id', $products_id]])->get();
            } else {
                $minMax = '';
            }
            $result['inventory_ref_id'] = $inventory_ref_id;
            $result['minMax'] = $minMax;
            $result['minMaxLevel'] = $minMax;

        } else {
            $result['inventory_ref_id'] = 0;
            $result['minMax'] = 0;
            $result['remainingStock'] = 0;
        }

        return $result;
    }
    

}
