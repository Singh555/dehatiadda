<?php

namespace App\Models\AppModels;

use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Admin\AdminSiteSettingController;
use App\Http\Controllers\Admin\AdminCategoriesController;
use App\Http\Controllers\Admin\AdminProductsController;
use App\Http\Controllers\App\AppSettingController;
use App\Http\Controllers\App\AlertController;
use DB;
use Lang;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Support\Facades\Validator;
use Mail;
use DateTime;
use Auth;
use Carbon;
use App\Helpers\HttpStatus;
use Illuminate\Support\Facades\Log;

class Reviews extends Model {

    public static function givereview($request) {

        $consumer_data = getallheaders();

        $consumer_data['consumer_ip'] = $request->ip();
        $consumer_data['consumer_url'] = __FUNCTION__;
        $authController = new AppSettingController();
        $authenticate = $authController->apiAuthenticate($consumer_data);

        if ($authenticate == 1) {
            $validator = Validator::make($request->all(), [
                        'products_id' => 'required',
                        'reviews_rating' => 'required',
                        'reviews_text' => 'nullable',
            ]);

            if ($validator->fails()) {
                return returnResponse($validator->errors(), HttpStatus::HTTP_UNPROCESSABLE_ENTITY);
            }
            try {
                $products_id = $request->products_id;
                $customers_id = auth()->user()->id;
                $customers_name = auth()->user()->first_name . '' . auth()->user()->last_name;
                $reviews_rating = $request->reviews_rating;

                $languages_id = 1;

                if ($request->reviews_text) {
                    $reviews_text = $request->reviews_text;
                } else {
                    $reviews_text = '';
                }
                DB::beginTransaction();
                //check already reviewed by this customer for this product
                $reviews = DB::table('reviews')->where(
                                ['products_id' => $products_id,
                                    'customers_id' => $customers_id,]
                        )->get();

                if (count($reviews) == 0) {

                    $reviews_id = DB::table('reviews')->insertGetId([
                        'products_id' => $products_id,
                        'customers_id' => $customers_id,
                        'customers_name' => $customers_name,
                        'reviews_rating' => $reviews_rating,
                        'created_at' => date('Y-m-d H:i:s'),
                        'reviews_status' => 1,
                        'reviews_read' => 0
                    ]);
                    if (!empty($reviews_id)) {
                        $reviewed = DB::table('reviews_description')->insertGetId([
                            'reviews_text' => $reviews_text,
                            'language_id' => $languages_id,
                            'review_id' => $reviews_id
                        ]);
                        if (!empty($reviewed)) {
                            DB::commit();
                            return returnResponse("Product is reviewed successfully!", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS);
                        }
                    } else {
                        Log::error(__CLASS__ . "::" . __FUNCTION__ . " review data insertion failed");
                        return returnResponse("Some error occured please try again !");
                    }
                } else {
                    Log::debug(__CLASS__ . "::" . __FUNCTION__ . "Product $products_id has been already reviewed");
                    return returnResponse("You have already given the review for this product.", HttpStatus::HTTP_NOT_ACCEPTABLE, HttpStatus::HTTP_WARNING);
                }
            } catch (\Exception $e) {
                Log::error(__CLASS__ . "::" . __FUNCTION__ . " Exception occured " . $e->getMessage());
                return returnResponse("Some error occured please try again !");
            }
        }
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
    }

    public static function updatereview($request) {

        $consumer_data = getallheaders();

        $consumer_data['consumer_ip'] = $request->ip();
        $consumer_data['consumer_url'] = __FUNCTION__;
        $authController = new AppSettingController();
        $authenticate = $authController->apiAuthenticate($consumer_data);

        if ($authenticate == 1) {

            $validator = Validator::make($request->all(), [
                        'products_id' => 'required',
                        'reviews_rating' => 'required',
                        'reviews_id' => 'required',
                        'reviews_text' => 'nullable',
            ]);

            if ($validator->fails()) {
                return returnResponse($validator->errors(), HttpStatus::HTTP_UNPROCESSABLE_ENTITY);
            }
            try {
                $products_id = $request->products_id;
                $customers_id = auth()->user()->id;
                $customers_name = auth()->user()->first_name . '' . auth()->user()->last_name;
                $reviews_rating = $request->reviews_rating;

                $languages_id = 1;

                if ($request->reviews_text) {
                    $reviews_text = $request->reviews_text;
                } else {
                    $reviews_text = '';
                }
                DB::beginTransaction();
                //check already reviewed by this customer for this product
                $reviews = DB::table('reviews')
                                ->where('reviews_id', $request->reviews_id)
                                ->where(
                                        ['products_id' => $products_id,
                                            'customers_id' => $customers_id,]
                                )->first();

                if (isset($reviews->products_id)) {

                    $reviews_update = DB::table('reviews')
                            ->where('reviews_id', $request->reviews_id)
                            ->where('products_id', $products_id)
                            ->where('customers_id', $customers_id)
                            ->update([
                        'reviews_rating' => $reviews_rating,
                        'updated_at' => date('Y-m-d H:i:s'),
                        'reviews_read' => 0
                    ]);

                    $reviewed = DB::table('reviews_description')
                            ->where('review_id', $request->reviews_id)
                            ->where('language_id', $languages_id)
                            ->update([
                        'reviews_text' => $reviews_text
                    ]);

                if($reviews_update){ 
                    if ($reviewed) {
                            DB::commit();
                            return returnResponse("Your review has been updated successfully!", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS);
                        }else{
                          Log::error(__CLASS__ . "::" . __FUNCTION__ . "Review updating failed ");
                          return returnResponse("Some error occured please try again !");  
                        }
                }else{
                    Log::error(__CLASS__ . "::" . __FUNCTION__ . "Review updating failed ");
                          return returnResponse("Some error occured please try again !");  
                }     
                    
                } else {
                    Log::error("Review Data not exist for this product id - $products_id");
                    return returnResponse("You dont have any review for this product.", HttpStatus::HTTP_NOT_ACCEPTABLE, HttpStatus::HTTP_WARNING);
                }
            } catch (\Exception $e) {
                Log::error(__CLASS__ . "::" . __FUNCTION__ . " Exception occured " . $e->getMessage());
                return returnResponse("Some error occured please try again !");
            }
        }
        
       return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED); 
        
    }
    
    public static function checkreview($request){
        $consumer_data = getallheaders();
        $consumer_data['consumer_ip'] = $request->ip();
        // $consumer_data['consumer_ip'] = request()->header('consumer-ip');
        $consumer_data['consumer_url'] = __FUNCTION__;
        Log::debug($consumer_data);
        $authController = new AppSettingController();
        $authenticate = $authController->apiAuthenticate($consumer_data);

    if($authenticate==1){
        $languages_id = 1;
        $products_id = $request->products_id;
        
        //check already reviewed by this customer for this product
       $check = DB::table('orders_products')
        ->join('orders','orders_products.orders_id','=','orders.orders_id')
        ->join('users','users.id','=','orders.customers_id')
        ->select('orders.orders_id')
        ->where('orders_products.products_id', $products_id)
        ->whereIn('orders.status', array('DELIVERED', 'COMPLETED','RETURN REQUESTED'))
        ->where('orders.customers_id', auth()->user()->id)->first();
        if(isset($check->orders_id) && !empty($check->orders_id)){
            $reviews = DB::table('reviews')
            ->join('reviews_description','reviews_description.review_id','=','reviews.reviews_id')
            ->join('users','users.id','=','reviews.customers_id')
            ->select('reviews.reviews_id', 'reviews.products_id', 'reviews.reviews_rating as rating', 'reviews.created_at', 'reviews_description.reviews_text as comments', 'users.first_name',
            'users.last_name', 'users.email')
            ->where('reviews.products_id', $products_id)
            ->where('reviews.reviews_status', 1)
            ->where('reviews.customers_id', auth()->user()->id)
            ->where('reviews_description.language_id', $languages_id)->first();
            $data = 'true';
            if(isset($reviews->reviews_id)){
                $data = $reviews;
            }
            
            return returnResponse("Review data found !", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $data);
        } else {
            return returnResponse("You have not purchased this product yet !", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, 'false');
        }


        }
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
    }

    public static function getreviews($request) {

        $consumer_data = getallheaders();

        $consumer_data['consumer_ip'] = $request->ip();
        $consumer_data['consumer_url'] = __FUNCTION__;
        $authController = new AppSettingController();
        $authenticate = $authController->apiAuthenticate($consumer_data);

        $results = array();

        if ($authenticate == 1) {

            $products_id = $request->products_id;
            $languages_id = 1;

            $reviews = DB::table('reviews')
                            ->join('reviews_description', 'reviews_description.review_id', '=', 'reviews.reviews_id')
                            ->join('users', 'users.id', '=', 'reviews.customers_id')
                            ->select('reviews.reviews_id', 'reviews.products_id', 'reviews.reviews_rating as rating', 'reviews.created_at', 'reviews_description.reviews_text as comments', 'users.first_name', 'users.last_name', 'users.email')
                            ->where('reviews.products_id', $products_id)
                            ->where('reviews.reviews_status', 1)
                            ->where('reviews_description.language_id', $languages_id)->get();

            if ($reviews) {
                return returnResponse("Product rating data found !", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $reviews);
            } else {
                return returnResponse("Product is not rated yet.", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS);
            }
        }
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
    }

}
