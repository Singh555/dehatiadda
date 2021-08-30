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

//logout
Route::post('/logout', 'FreeShipping\LoginController@logout'); 
 // login url
Route::post('/login', 'FreeShipping\LoginController@processLogin');

Route::post('/login_with_otp', 'FreeShipping\LoginController@sendLoginOtp');

Route::post('/verify_login_with_otp', 'FreeShipping\LoginController@verifyLoginOtp');

Route::group(['middleware' => ['jwt.verify:api_customer_free_shopping']], function(){
       
    Route::post('/get_shop_list', 'FreeShipping\ShopController@getShopList');
    
    Route::post('/get_profile', 'FreeShipping\CustomerController@getProfile');
       
    Route::post('/validate_qr_code', 'FreeShipping\ShopController@validateQrCode');
    
    Route::post('/confirm_qr_payment', 'FreeShipping\ShopController@confirmQrPayment');
    
    Route::post('/get_wallet_txn', 'FreeShipping\CustomerController@getWalletTxn');
    
    Route::post('/search_shop', 'FreeShipping\ShopController@searchShop');
       
        
});