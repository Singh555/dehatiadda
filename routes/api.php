<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api', 'cors')->get('/user', function (Request $request) {
    return $request->user();
});




/*
	|--------------------------------------------------------------------------
	| App Controller Routes
	|--------------------------------------------------------------------------
	|
	| This section contains all Routes of application
	|
	|
*/

Route::group(['namespace' => 'App','middleware' => ['assign.guard:api_customer']], function () {

	//Route::post('/uploadimage', 'AppSettingController@uploadimage');
    
    Route::any('/test/customer_subscription', 'CustomersController@customerSubscriptionTest');
    Route::any('/test/insert', 'CustomersController@customerInsertTest');
    
     // Check User
    Route::post('/google_auth', 'CustomersController@googleAuth');
    Route::post('/google_auth/register', 'CustomersController@googleAuthRegister');
    Route::post('/become_prime', 'AccountController@becomePrime');
    Route::post('/validate_prime', 'AccountController@validatePrime');
    
    //logout
    Route::post('/logout', 'CustomersController@logout'); 
	
	Route::post('/getcategories', 'CategoriesController@getcategories');

	//registration url
	Route::post('/registerdevices', 'CustomersController@registerdevices');
        
        //get Videos
        Route::post('/get_videos', 'VideoController@getVideoList');

	//processregistration url
	Route::post('/processregistration', 'CustomersController@processregistration');
    
        // login url
	Route::post('/processlogin', 'CustomersController@processlogin');
        Route::post('/send_otp_for_mobile', 'CustomersController@sendOtpForMobile');
	Route::post('/verify_mobile', 'CustomersController@verifyOtpForMobile');
        // login url
	Route::post('/normal_login', 'CustomersController@normalLogin');
	Route::post('/verify_login', 'CustomersController@verifyLogin');
        
        //Normal Signup
        Route::post('/normal_signup', 'CustomersController@normalSignup');
        //Normal Signup
        Route::post('/normal_signup_verify', 'CustomersController@normalSignupVerify');
        //prime signup
        
        Route::post('/prime_signup', 'CustomersController@primeSignup');
        //prime signup
        
        Route::post('/prime_signup_verify', 'CustomersController@primeSignupVerify');
        
        //Check membercode
	Route::post('/validate_referral_code', 'CustomersController@validateReferralCode');

	//social login
	Route::post('/facebookregistration', 'CustomersController@facebookregistration');
	Route::post('/googleregistration', 'CustomersController@googleregistration');

	//push notification setting
	Route::post('/notify_me', 'CustomersController@notify_me');

    // Check Delivery Pincode
    Route::post('/delivery/pincode/check', 'DeliveryController@checkDeliveryPinCode');
             
             
	// forgot password url
	Route::post('/processforgotpassword', 'CustomersController@processforgotpassword');
        
        ########################
        # Login Protected Routes
        ########################
        
   Route::group(['middleware' => ['jwt.verify:api_customer']], function(){
       
       
       
       // Update referral url
       Route::post('/update_referral_url', 'CustomersController@updateReferralUrl');
                
       Route::post('/update_fcm_token', 'CustomersController@updateFcmToken');
       
       //user Profile
       
       Route::post('/user_profile', 'CustomersController@userProfile');
       
	//update customer info url
	
	Route::post('/updatecustomerinfo', 'CustomersController@updatecustomerinfo');
	Route::post('/updatepassword', 'CustomersController@updatepassword');
        
        //get all address url
	Route::post('/getalladdress', 'LocationController@getalladdress');
       
	//address url
	Route::post('/addshippingaddress', 'LocationController@addshippingaddress');

	//update address url
	Route::post('/updateshippingaddress', 'LocationController@updateshippingaddress');

	//update default address url
	Route::post('/updatedefaultaddress', 'LocationController@updatedefaultaddress');
        //get default address
	Route::post('/getdefaultaddress', 'LocationController@getdefaultaddress');

	//delete address url
	Route::post('/deleteshippingaddress', 'LocationController@deleteshippingaddress');
        
        //like products
	Route::post('/likeproduct', 'MyProductController@likeproduct');
        
        //
        Route::post('/getlikedproducts', 'MyProductController@getlikedproducts');

	//unlike products
	Route::post('/unlikeproduct', 'MyProductController@unlikeproduct');
                
    
        //getwallettxn
    Route::post('/account/get_wallet_txn', 'AccountController@getWalletTxnHistory');
    //makewithdrwalrequest
    Route::post('/account/withdraw_request', 'AccountController@makeWithdrawRequest');
    
    //withdrwal_request_history
    Route::post('/account/withdraw_request_history', 'AccountController@withdrawRequestHistory');
                
    //makeKycRequest
    Route::post('/account/upload_kyc', 'AccountController@makeKycRequest');
    
    //Upload Avatar
    Route::post('/account/upload_avatar', 'AccountController@uploadAvatar');
                
    //get commission details
    Route::post('/account/get_commission', 'AccountController@getCommissionDetails');
    
    
    //get commission details
    Route::post('/account/get_referral_list', 'AccountController@getReferralList');
    
     Route::post('/account/get_prime_packages', 'AccountController@getPrimePackages');
    
    //check ifsc
    Route::post('/account/check_ifsc', 'AccountController@checkIfscCode');
    Route::post('/account/create_m_pin', 'AccountController@createMPin');
    Route::post('/account/update_m_pin', 'AccountController@updateMPin');
    Route::post('/update_fcm_token', 'AccountController@updateFcmToken');
    
     //check ifsc
    Route::post('/account/validate_referral', 'AccountController@validateReferralCode');
    Route::post('/account/update_referral', 'AccountController@updateReferralCode');
    
        
        /*
	|--------------------------------------------------------------------------
	| Cart Controller Routes
	|--------------------------------------------------------------------------
	|
	| This section contains customer orders
	|
	*/
        
        // cart
        
        //hyperpaytoken
	
	Route::post('/add_to_cart', 'CartController@addToCart');
        
         // add to cart in db
	Route::post('/addtocart', 'CartController@addToCartDb');
        
        //get my cart data 
        Route::post('/my_cart', 'CartController@myCart');
        
        //remove single cart item
        Route::post('/cart_remove_single_item', 'CartController@removeSingleCartItem');
        
        //cart decrement single cart item 
        Route::post('/cart_update_single_item', 'CartController@updateSingleCartItem');
        
        //cart decrement single cart item 
        Route::post('/clear_cart', 'CartController@clearAllCartItem');
        
       //cart decrement single cart item 
        Route::post('/cart_summary', 'CartController@cartSummary');
        
        ################################################################################################################################

	//hyperpaytoken
	Route::post('/hyperpaytoken', 'OrderController@hyperpaytoken');

	//hyperpaytoken
	Route::post('/hyperpaypaymentstatus', 'OrderController@hyperpaypaymentstatus');

	//paymentsuccess
	Route::post('/paymentsuccess', 'OrderController@paymentsuccess');

	//paymenterror
	Route::post('/paymenterror', 'OrderController@paymenterror');
        
        //order cancel
	Route::post('/cancelorder', 'OrderController@cancelOrder');
        
        //get order details
	Route::post('/getorderdetails', 'OrderController@getorderdetails');

	//generateBraintreeToken
	Route::post('/generatebraintreetoken', 'OrderController@generatebraintreetoken');

	//generateBraintreeToken
	Route::post('/instamojotoken', 'OrderController@instamojotoken');

	//add To order
	Route::post('/addtoorder', 'OrderController@addtoorder');

        //place order new 6/7/2021
	Route::post('/place_order', 'OrderController@placeOrder');

	//updatestatus
	Route::post('/updatestatus/', 'OrderController@updatestatus');

	//get all orders
	Route::post('/getorders', 'OrderController@getorders');

	//get default payment method
	Route::post('/getpaymentmethods', 'OrderController@getpaymentmethods');
    
        //validate payment gateway payment
	Route::post('/validategatewaypayment', 'PaymentgatewayController@validatePayment');
	Route::post('/validategate/cashfree/payment', 'PaymentgatewayController@validatechashFreePayment');
        //payment error 
        
          Route::post('/update_order_payment_error', 'PaymentgatewayController@paymenterror');
          

	//get shipping / tax Rate
	Route::post('/getrate', 'OrderController@getrate');

	//get Coupon
	Route::post('/getcoupon', 'OrderController@getcoupon');
        //apply Coupon
	Route::post('/applycoupon', 'CartController@applyCoupon');

	//paytm hash key
	Route::post('/generatpaytmhashes', 'OrderController@generatpaytmhashes');
        
        
         /*
	|--------------------------------------------------------------------------
	| reviews Controller Routes
	|--------------------------------------------------------------------------
        */

        Route::post('/givereview', 'ReviewsController@givereview');
        Route::post('/updatereview', 'ReviewsController@updatereview');
        Route::post('/checkreview', 'ReviewsController@checkreview');
        Route::post('/getreviews', 'ReviewsController@getreviews');
        
}); // end jwt.verify:api_customer
	

	/*
	|--------------------------------------------------------------------------
	| Location Controller Routes
	|--------------------------------------------------------------------------
	|
	| This section contains countries shipping detail
	| This section contains links of affiliated to address
	|
	*/

        Route::post('/attribute_details', 'CartController@attributeDetails');
        
	//get country url
	Route::post('/getcountries', 'LocationController@getcountries');

	//get zone url
	Route::post('/getzones', 'LocationController@getzones');

	

	/*
	|--------------------------------------------------------------------------
	| Product Controller Routes
	|--------------------------------------------------------------------------
	|
	| This section contains product data
	| Such as:
	| top seller, Deals, Liked, categroy wise or category individually and detail of every product.
	*/


	//get categories
	Route::post('/allcategories', 'MyProductController@allcategories');

	//getAllProducts
	Route::post('/getallproducts', 'MyProductController@getallproducts');

     //get Product List New
     Route::post('/get_product_list', 'MyProductController@getProductList');
             
	//get filters
	Route::post('/getfilters', 'MyProductController@getfilters');

	//get getFilterproducts
	Route::post('/getfilterproducts', 'MyProductController@getfilterproducts');

	Route::post('/getproductsearch', 'MyProductController@getsearchdata');
             
        Route::post('/getproductsearchsuggestions', 'MyProductController@getsearchsuggestions');

	//getquantity
	Route::post('/getquantity', 'MyProductController@getquantity');


	/*
	|--------------------------------------------------------------------------
	| News Controller Routes
	|--------------------------------------------------------------------------
	|
	| This section contains news data
	| Such as:
	| top news or category individually and detail of every news.

	*/


	//get categories
	Route::post('/allnewscategories', 'NewsController@allnewscategories');

	//getAllProducts
	Route::post('/getallnews', 'NewsController@getallnews');

	

	/*
	|--------------------------------------------------------------------------
	| Banner Controller Routes
	|--------------------------------------------------------------------------
	|
	| This section contains banners, banner history
	|

	*/

	//get banners
	Route::post('/getbanners', 'BannersController@getbanners');

        Route::post('/get_home_sections', 'MyProductController@getHomeSections');
       
         //get Home 
        Route::post('/home_sections', 'HomeController@getSections');
             
	//banners history
	Route::post('/bannerhistory', 'BannersController@bannerhistory');

	/*
	|--------------------------------------------------------------------------
	| App setting Controller Routes
	|--------------------------------------------------------------------------
	|
	| This section contains app  languages
	|

	*/
	Route::post('/appsetting', 'AppSettingController@sitesetting');

        Route::post('/app_common_setting_list', 'AppSettingController@appCommonSettingList');

	//old app label
	Route::post('/applabels', 'AppSettingController@applabels');
	// app video links
	Route::post('/videolinks', 'AppSettingController@videolinks');

	//new app label
	Route::post('/applabels3', 'AppSettingController@applabels3');
	Route::post('/contactus', 'AppSettingController@contactus');
	Route::post('/getlanguages', 'AppSettingController@getlanguages');


	/*
	|--------------------------------------------------------------------------
	| Page Controller Routes
	|--------------------------------------------------------------------------
	|
	| This section contains news data
	| Such as:
	| top Page individually and detail of every Page.

	*/

	//getAllPages
	Route::post('/getallpages', 'PagesController@getallpages');

     //getprivacypolicy
     Route::get('/getprivacypolicy', 'PagesController@getprivacypolicy');
 

  /*
  |--------------------------------------------------------------------------
  | current location Controller Routes
  |--------------------------------------------------------------------------
  */

  Route::post('/getlocation', 'AppSettingController@getlocation');
  
  /*
  |--------------------------------------------------------------------------
  | currency location Controller Routes
  |--------------------------------------------------------------------------
  */

  Route::post('/getcurrencies', 'AppSettingController@getcurrencies');

});

Route::group(['prefix' => 'freeshipping', 'namespace' => 'App','middleware' => ['assign.guard:api_customer_free_shopping']], function(){
    include 'free_shipping_api.php';
});


