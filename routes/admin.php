<?php

Route::get('/clear-cache', function () {
    $exitCode = Artisan::call('cache:clear');
    $exitCode = Artisan::call('config:clear');
    // $exitCode = Artisan::call('config:cache');
});

Route::get('/phpinfo', function () {
    phpinfo();
});

// Routes for Cron Job to be called 
Route::group(['namespace' => 'CronJob', 'prefix' => 'cronJob'], function () {
    Route::get('/levelIncomeClosing', 'CronJobController@levelIncomeClosing');
    Route::post('/secondCronJob', 'CronJobController@secondCronJob');
    // Routes for RazorPayCall back
    Route::get('/razorPayWebHookHandler', 'CronJobController@razorPayWebHookHandler');
    Route::get('/checkForPendingOrders', 'CronJobController@checkForPendingOrders');
});

Route::group(['middleware' => ['installer']], function () {
    Route::get('/not_allowed', function () {
        return view('errors.not_found');
    });
    Route::group(['namespace' => 'AdminControllers','middleware' => ['assign.guard:admin'], 'prefix' => 'admin'], function () {
        Route::get('/login', 'AdminController@login');
        Route::post('/checkLogin', 'AdminController@checkLogin');
    });

    Route::get('/home', function () {
        return redirect('/admin/languages/display');
    });
    Route::group(['namespace' => 'AdminControllers', 'middleware' => 'auth:admin', 'prefix' => 'admin'], function () {
        Route::post('webPagesSettings/changestatus', 'ThemeController@changestatus');
        Route::post('webPagesSettings/setting/set', 'ThemeController@set');
        Route::post('webPagesSettings/reorder', 'ThemeController@reorder');
        Route::get('/home', function () {
            return redirect('/dashboard/{reportBase}');
        });
        Route::get('/generateKey', 'SiteSettingController@generateKey');

        //log out
        Route::get('/logout', 'AdminController@logout');
        Route::get('/webPagesSettings/{id}', 'ThemeController@index2');

        Route::get('/topoffer/display', 'ThemeController@topoffer');
        Route::post('/topoffer/update', 'ThemeController@updateTopOffer');
        

        Route::get('/dashboard/{reportBase}', 'AdminController@dashboard');
        //add adddresses against customers
        Route::get('/addaddress/{id}/', 'CustomersController@addaddress')->middleware('add_customer');
        Route::post('/addNewCustomerAddress', 'CustomersController@addNewCustomerAddress')->middleware('add_customer');
        Route::post('/editAddress', 'CustomersController@editAddress')->middleware('edit_customer');
        Route::post('/updateAddress', 'CustomersController@updateAddress')->middleware('edit_customer');
        Route::post('/deleteAddress', 'CustomersController@deleteAddress')->middleware('delete_customer');
        
        Route::post('/getZones', 'AddressController@getzones');

        //sliders
        Route::get('/sliders', 'AdminSlidersController@sliders')->middleware('website_routes');
        Route::get('/addsliderimage', 'AdminSlidersController@addsliderimage')->middleware('website_routes');
        Route::post('/addNewSlide', 'AdminSlidersController@addNewSlide')->middleware('website_routes');
        Route::get('/editslide/{id}', 'AdminSlidersController@editslide')->middleware('website_routes');
        Route::post('/updateSlide', 'AdminSlidersController@updateSlide')->middleware('website_routes');
        Route::post('/deleteSlider/', 'AdminSlidersController@deleteSlider')->middleware('website_routes');

        //constant banners
        Route::get('/constantbanners', 'AdminConstantController@constantBanners')->middleware('website_routes');
        Route::get('/addconstantbanner', 'AdminConstantController@addconstantBanner')->middleware('website_routes');
        Route::post('/addNewConstantBanner', 'AdminConstantController@addNewconstantBanner')->middleware('website_routes');
        Route::get('/editconstantbanner/{id}', 'AdminConstantController@editconstantbanner')->middleware('website_routes');
        Route::post('/updateconstantBanner', 'AdminConstantController@updateconstantBanner')->middleware('website_routes');
        Route::post('/deleteconstantBanner/', 'AdminConstantController@deleteconstantBanner')->middleware('website_routes');
        
        //filemanager
        Route::get('/medias', 'AdminController@media');
        
    });

    Route::group(['prefix' => 'admin/languages', 'middleware' => 'auth:admin', 'namespace' => 'AdminControllers'], function () {
        Route::get('/display', 'LanguageController@display')->middleware('view_language');
        Route::post('/default', 'LanguageController@default')->middleware('edit_language');
        Route::get('/add', 'LanguageController@add')->middleware('add_language');
        Route::post('/add', 'LanguageController@insert')->middleware('add_language');
        Route::get('/edit/{id}', 'LanguageController@edit')->middleware('edit_language');
        Route::post('/update', 'LanguageController@update')->middleware('edit_language');
        Route::post('/delete', 'LanguageController@delete')->middleware('delete_language');
        Route::get('/filter', 'LanguageController@filter')->middleware('view_language');

    });
   // media

    Route::group(['prefix' => 'admin/media', 'middleware' => 'auth:admin', 'namespace' => 'AdminControllers'], function () {
        Route::get('/display', 'MediaController@display')->middleware('view_media');
        Route::get('/add', 'MediaController@add')->middleware('add_media');
        Route::post('/updatemediasetting', 'MediaController@updatemediasetting')->middleware('edit_media');
        Route::post('/uploadimage', 'MediaController@fileUpload')->middleware('add_media');
        Route::post('/delete', 'MediaController@deleteimage')->middleware('delete_media');
        Route::get('/detail/{id}', 'MediaController@detailimage')->middleware('view_media');
        Route::get('/refresh', 'MediaController@refresh');
        Route::post('/regenerateimage', 'MediaController@regenerateimage')->middleware('add_media');
    });

    Route::group(['prefix' => 'admin/theme', 'middleware' => 'auth:admin', 'namespace' => 'AdminControllers'], function () {
        Route::get('/setting', 'ThemeController@index');
        Route::get('/setting/{id}', 'ThemeController@moveToBanners');
        Route::get('/setting/carousals/{id}', 'ThemeController@moveToSliders');
        Route::post('setting/set', 'ThemeController@set');
        Route::post('setting/setPages', 'ThemeController@setPages');
        Route::post('/setting/updatebanner', 'ThemeController@updatebanner');
        Route::post('/setting/carousals/updateslider', 'ThemeController@updateslider');
        Route::post('/setting/addbanner', 'ThemeController@addbanner');
        Route::post('/reorder', 'ThemeController@reorder');
        Route::post('/setting/changestatus', 'ThemeController@changestatus');
        Route::post('/setting/fetchlanguages', 'LanguageController@fetchlanguages')->middleware('view_language');

    });

    Route::group(['prefix' => 'admin/manufacturers', 'middleware' => 'auth:admin', 'namespace' => 'AdminControllers'], function () {
        Route::get('/display', 'ManufacturerController@display')->middleware('view_manufacturer');
        Route::get('/add', 'ManufacturerController@add')->middleware('add_manufacturer');
        Route::post('/add', 'ManufacturerController@insert')->middleware('add_manufacturer');
        Route::get('/edit/{id}', 'ManufacturerController@edit')->middleware('edit_manufacturer');
        Route::post('/update', 'ManufacturerController@update')->middleware('edit_manufacturer');
        Route::post('/delete', 'ManufacturerController@delete')->middleware('delete_manufacturer');
        Route::get('/filter', 'ManufacturerController@filter')->middleware('view_manufacturer');
    });

    Route::group(['prefix' => 'admin/newscategories', 'middleware' => 'auth:admin', 'namespace' => 'AdminControllers'], function () {
        Route::get('/display', 'NewsCategoriesController@display')->middleware('view_news');
        Route::get('/add', 'NewsCategoriesController@add')->middleware('add_news');
        Route::post('/add', 'NewsCategoriesController@insert')->middleware('add_news');
        Route::get('/edit/{id}', 'NewsCategoriesController@edit')->middleware('edit_news');
        Route::post('/update', 'NewsCategoriesController@update')->middleware('edit_news');
        Route::post('/delete', 'NewsCategoriesController@delete')->middleware('delete_news');
        Route::get('/filter', 'NewsCategoriesController@filter')->middleware('view_news');
    });

    Route::group(['prefix' => 'admin/news', 'middleware' => 'auth:admin', 'namespace' => 'AdminControllers'], function () {
        Route::get('/display', 'NewsController@display')->middleware('view_news');
        Route::get('/add', 'NewsController@add')->middleware('add_news');
        Route::post('/add', 'NewsController@insert')->middleware('add_news');
        Route::get('/edit/{id}', 'NewsController@edit')->middleware('edit_news');
        Route::post('/update', 'NewsController@update')->middleware('edit_news');
        Route::post('/delete', 'NewsController@delete')->middleware('delete_news');
        Route::get('/filter', 'NewsController@filter')->middleware('view_news');
    });

    Route::group(['prefix' => 'admin/categories', 'middleware' => 'auth:admin', 'namespace' => 'AdminControllers'], function () {
        Route::get('/display', 'CategoriesController@display')->middleware('view_categories');;
        Route::get('/add', 'CategoriesController@add')->middleware('add_categories');;
        Route::post('/add', 'CategoriesController@insert')->middleware('add_categories');;
        Route::get('/edit/{id}', 'CategoriesController@edit')->middleware('edit_categories');;
        Route::post('/update', 'CategoriesController@update')->middleware('edit_categories');;
        Route::post('/delete', 'CategoriesController@delete')->middleware('delete_categories');;
        Route::get('/filter', 'CategoriesController@filter')->middleware('view_categories');;
    });

    Route::group(['prefix' => 'admin/currencies', 'middleware' => 'auth:admin', 'namespace' => 'AdminControllers'], function () {
        Route::get('/display', 'CurrencyController@display')->middleware('view_general_setting');
        Route::get('/add', 'CurrencyController@add')->middleware('edit_general_setting');
        Route::post('/add', 'CurrencyController@insert')->middleware('edit_general_setting');
        Route::get('/edit/{id}', 'CurrencyController@edit')->middleware('edit_general_setting');
        Route::get('/edit/warning/{id}', 'CurrencyController@warningedit')->middleware('edit_general_setting');
        Route::post('/update', 'CurrencyController@update')->middleware('edit_general_setting');
        Route::post('/delete', 'CurrencyController@delete')->middleware('edit_general_setting');

        
    });

    Route::group(['prefix' => 'admin/products', 'middleware' => 'auth:admin', 'namespace' => 'AdminControllers'], function () {
        Route::get('/display', 'ProductController@display')->middleware('view_product');
        Route::get('/add', 'ProductController@add')->middleware('add_product');
        Route::post('/add', 'ProductController@insert')->middleware('add_product');
        Route::get('/edit/{id}', 'ProductController@edit')->middleware('edit_product');
        Route::post('/update', 'ProductController@update')->middleware('edit_product');
        Route::post('/delete', 'ProductController@delete')->middleware('delete_product');
        Route::get('/filter', 'ProductController@filter')->middleware('view_product');
        //update Product Status
         Route::post('/updateStatus', 'ProductController@updateProductStatus')->middleware('edit_product');        

            Route::group(['prefix' => 'inventory'], function () {
            Route::get('/display', 'ProductController@addinventoryfromsidebar')->middleware('view_product');
            // Route::post('/addnewstock', 'ProductController@addinventory')->middleware('view_product');
            Route::get('/add_bulk', 'ProductController@addinventoryBulk')->middleware('edit_product');
            Route::post('/update_bulk', 'ProductController@updateInventoryBulk')->middleware('edit_product');
            Route::get('/ajax_min_max/{id}/', 'ProductController@ajax_min_max')->middleware('view_product');
            Route::get('/ajax_attr/{id}/', 'ProductController@ajax_attr')->middleware('view_product');
            Route::post('/addnewstock', 'ProductController@addnewstock')->middleware('add_product');
            Route::post('/addminmax', 'ProductController@addminmax')->middleware('add_product');
            Route::get('/addproductimages/{id}/', 'ProductController@addproductimages')->middleware('add_product');
        });
        Route::group(['prefix' => 'images'], function () {
            Route::get('/display/{id}/', 'ProductController@displayProductImages')->middleware('view_product');
            Route::get('/add/{id}/', 'ProductController@addProductImages')->middleware('add_product');
            Route::post('/insertproductimage', 'ProductController@insertProductImages')->middleware('add_product');
            Route::get('/editproductimage/{id}', 'ProductController@editProductImages')->middleware('edit_product');
            Route::post('/updateproductimage', 'ProductController@updateproductimage')->middleware('edit_product');
            Route::post('/deleteproductimagemodal', 'ProductController@deleteproductimagemodal')->middleware('edit_product');
            Route::post('/deleteproductimage', 'ProductController@deleteproductimage')->middleware('edit_product');
        });
        Route::group(['prefix' => 'attach/attribute'], function () {
            Route::get('/display/{id}', 'ProductController@addproductattribute')->middleware('view_product');
            Route::group(['prefix' => '/default'], function () {
                Route::post('/', 'ProductController@addnewdefaultattribute')->middleware('view_product');
                Route::post('/edit', 'ProductController@editdefaultattribute')->middleware('edit_product');
                Route::post('/update', 'ProductController@updatedefaultattribute')->middleware('edit_product');
                Route::post('/deletedefaultattributemodal', 'ProductController@deletedefaultattributemodal')->middleware('edit_product');
                Route::post('/delete', 'ProductController@deletedefaultattribute')->middleware('edit_product');
                Route::group(['prefix' => '/options'], function () {
                    Route::post('/add', 'ProductController@showoptions')->middleware('view_product');
                    Route::post('/edit', 'ProductController@editoptionform')->middleware('edit_product');
                    Route::post('/update', 'ProductController@updateoption')->middleware('edit_product');
                    Route::post('/showdeletemodal', 'ProductController@showdeletemodal')->middleware('edit_product');
                    Route::post('/delete', 'ProductController@deleteoption')->middleware('edit_product');
                    Route::post('/getOptionsValue', 'ProductController@getOptionsValue')->middleware('edit_product');
                    Route::post('/currentstock', 'ProductController@currentstock')->middleware('view_product');

                });

            });

        });

    });

    Route::group(['prefix' => 'admin/products/attributes', 'middleware' => 'auth:admin', 'namespace' => 'AdminControllers'], function () {
        Route::get('/display', 'ProductAttributesController@display')->middleware('view_product');
        Route::get('/add', 'ProductAttributesController@add')->middleware('view_product');
        Route::post('/insert', 'ProductAttributesController@insert')->middleware('view_product');
        Route::get('/edit/{id}', 'ProductAttributesController@edit')->middleware('view_product');
        Route::post('/update', 'ProductAttributesController@update')->middleware('view_product');
        Route::post('/delete', 'ProductAttributesController@delete')->middleware('view_product');

        Route::group(['prefix' => 'options/values'], function () {
            Route::get('/display/{id}', 'ProductAttributesController@displayoptionsvalues')->middleware('view_product');
            Route::post('/insert', 'ProductAttributesController@insertoptionsvalues')->middleware('edit_product');
            Route::get('/edit/{id}', 'ProductAttributesController@editoptionsvalues')->middleware('edit_product');
            Route::post('/update', 'ProductAttributesController@updateoptionsvalues')->middleware('edit_product');
            Route::post('/delete', 'ProductAttributesController@deleteoptionsvalues')->middleware('edit_product');
            Route::post('/addattributevalue', 'ProductAttributesController@addattributevalue')->middleware('edit_product');
            Route::post('/updateattributevalue', 'ProductAttributesController@updateattributevalue')->middleware('edit_product');
            Route::post('/checkattributeassociate', 'ProductAttributesController@checkattributeassociate')->middleware('edit_product');
            Route::post('/checkvalueassociate', 'ProductAttributesController@checkvalueassociate')->middleware('edit_product');
        });
    });

    Route::group(['prefix' => 'admin/admin', 'middleware' => 'auth:admin', 'namespace' => 'AdminControllers'], function () {
        Route::get('/profile', 'AdminController@profile');
        Route::post('/update', 'AdminController@update');
        Route::post('/updatepassword', 'AdminController@updatepassword');
    });

    Route::group(['prefix' => 'admin/reviews', 'middleware' => 'auth:admin', 'namespace' => 'AdminControllers'], function () {
        Route::get('/display', 'ProductController@reviews')->middleware('view_reviews');
        Route::get('/edit/{id}/{status}', 'ProductController@editreviews')->middleware('edit_reviews');

    });
//customers
    Route::group(['prefix' => 'admin/customers', 'middleware' => 'auth:admin', 'namespace' => 'AdminControllers'], function () {
        Route::get('/display', 'CustomersController@display')->middleware('view_customer');
        Route::get('/add', 'CustomersController@add')->middleware('add_customer');
        Route::post('/add', 'CustomersController@insert')->middleware('add_customer');
        Route::get('/edit/{id}', 'CustomersController@edit')->middleware('edit_customer');
        Route::post('/update', 'CustomersController@update')->middleware('edit_customer');
        Route::post('/delete', 'CustomersController@delete')->middleware('delete_customer');
        Route::get('/filter', 'CustomersController@filter')->middleware('view_customer');
        Route::get('/prime', 'CustomersController@displayPrime')->middleware('view_customer');
        Route::get('/nonprime', 'CustomersController@displayNonPrime')->middleware('view_customer');
        //customer withdrawal request
        Route::get('/withdrawal', 'CustomersController@withdrawRequest')->middleware('view_customer');
        Route::post('/withdrawal/reject', 'CustomersController@rejectWithdrawRequest')->middleware('edit_customer');
        Route::post('/withdrawal/pay', 'CustomersController@payWithdrawRequest')->middleware('edit_customer');
        
        //customer kyc request
         Route::get('/kyc', 'CustomersController@kycRequest')->middleware('view_customer');
         Route::post('/kyc/display', 'CustomersController@kycDisplay')->middleware('view_customer');
         Route::post('/kyc/updatestatus', 'CustomersController@kycUpdate')->middleware('edit_customer');
        //view customer details by mobile number
        Route::any('/view', 'CustomersController@viewDetails')->middleware('view_customer');
        Route::any('/mwalletview', 'CustomersController@mwalletTxn')->middleware('view_customer');
        Route::any('/swalletview', 'CustomersController@swalletTxn')->middleware('view_customer');
        Route::get('/orders/{customer_id}', 'CustomersController@orders')->middleware('view_customer');
        Route::post('/ajaxsearch', 'CustomersController@getCustomerListAjax')->middleware('view_customer');
        
        //customer mlm details view
        Route::any('/primeReferral', 'CustomersController@viewPrimeReferral')->middleware('view_customer');
        Route::any('/nonPrimeReferral', 'CustomersController@viewNonPrimeReferral')->middleware('view_customer');
        Route::any('/teamList', 'CustomersController@viewTeamListByLevel')->middleware('view_customer');
        //add adddresses against customers
        Route::get('/address/display/{id}/', 'CustomersController@diplayaddress')->middleware('add_customer');
        Route::post('/addcustomeraddress', 'CustomersController@addcustomeraddress')->middleware('add_customer');
        Route::post('/editaddress', 'CustomersController@editaddress')->middleware('edit_customer');
        Route::post('/updateaddress', 'CustomersController@updateaddress')->middleware('edit_customer');
        Route::post('/deleteAddress', 'CustomersController@deleteAddress')->middleware('edit_customer');
    });

    Route::group(['prefix' => 'admin/countries', 'middleware' => 'auth:admin', 'namespace' => 'AdminControllers'], function () {
        Route::get('/filter', 'CountriesController@filter')->middleware('view_tax');
        Route::get('/display', 'CountriesController@index')->middleware('view_tax');
        Route::get('/add', 'CountriesController@add')->middleware('add_tax');
        Route::post('/add', 'CountriesController@insert')->middleware('add_tax');
        Route::get('/edit/{id}', 'CountriesController@edit')->middleware('edit_tax');
        Route::post('/update', 'CountriesController@update')->middleware('edit_tax');
        Route::post('/delete', 'CountriesController@delete')->middleware('delete_tax');
    });

    Route::group(['prefix' => 'admin/zones', 'middleware' => 'auth:admin', 'namespace' => 'AdminControllers'], function () {
        Route::get('/display', 'ZonesController@index')->middleware('view_tax');
        Route::get('/filter', 'ZonesController@filter')->middleware('view_tax');
        Route::get('/add', 'ZonesController@add')->middleware('add_tax');
        Route::post('/add', 'ZonesController@insert')->middleware('add_tax');
        Route::get('/edit/{id}', 'ZonesController@edit')->middleware('edit_tax');
        Route::post('/update', 'ZonesController@update')->middleware('edit_tax');
        Route::post('/delete', 'ZonesController@delete')->middleware('delete_tax');
    });

    Route::group(['prefix' => 'admin/tax', 'middleware' => 'auth:admin', 'namespace' => 'AdminControllers'], function () {

        Route::group(['prefix' => '/taxclass'], function () {
            Route::get('/filter', 'TaxController@filtertaxclass')->middleware('view_tax');
            Route::get('/display', 'TaxController@taxindex')->middleware('view_tax');
            Route::get('/add', 'TaxController@addtaxclass')->middleware('add_tax');
            Route::post('/add', 'TaxController@inserttaxclass')->middleware('add_tax');
            Route::get('/edit/{id}', 'TaxController@edittaxclass')->middleware('edit_tax');
            Route::post('/update', 'TaxController@updatetaxclass')->middleware('edit_tax');
            Route::post('/delete', 'TaxController@deletetaxclass')->middleware('delete_tax');
        });

        Route::group(['prefix' => '/taxrates'], function () {
            Route::get('/display', 'TaxController@displaytaxrates')->middleware('view_tax');
            Route::get('/filter', 'TaxController@filtertaxrates')->middleware('view_tax');
            Route::get('/add', 'TaxController@addtaxrate')->middleware('add_tax');
            Route::post('/add', 'TaxController@inserttaxrate')->middleware('add_tax');
            Route::get('/edit/{id}', 'TaxController@edittaxrate')->middleware('edit_tax');
            Route::post('/update', 'TaxController@updatetaxrate')->middleware('edit_tax');
            Route::post('/delete', 'TaxController@deletetaxrate')->middleware('delete_tax');
        });

    });

    Route::group(['prefix' => 'admin/shippingmethods', 'middleware' => 'auth:admin', 'namespace' => 'AdminControllers'], function () {
        //shipping setting
        Route::get('/display', 'ShippingMethodsController@display')->middleware('view_shipping');
        Route::get('/upsShipping', 'ShippingMethodsController@upsShipping')->middleware('view_shipping');
        Route::post('/updateupsshipping', 'ShippingMethodsController@updateupsshipping')->middleware('edit_shipping');
        Route::get('/flateRate', 'ShippingMethodsController@flateRate')->middleware('view_shipping');
        Route::post('/updateflaterate', 'ShippingMethodsController@updateflaterate')->middleware('edit_shipping');
        Route::post('/defaultShippingMethod', 'ShippingMethodsController@defaultShippingMethod')->middleware('edit_shipping');
        Route::get('/detail/{table_name}', 'ShippingMethodsController@detail')->middleware('edit_shipping');
        Route::post('/update', 'ShippingMethodsController@update')->middleware('edit_shipping');

        Route::get('/shppingbyweight', 'ShippingByWeightController@shppingbyweight')->middleware('view_shipping');
        Route::post('/updateShppingWeightPrice', 'ShippingByWeightController@updateShppingWeightPrice')->middleware('edit_shipping');

    });

    Route::group(['prefix' => 'admin/paymentmethods', 'middleware' => 'auth:admin', 'namespace' => 'AdminControllers'], function () {
        Route::get('/index', 'PaymentMethodsController@index')->middleware('view_payment');
        Route::get('/display/{id}', 'PaymentMethodsController@display')->middleware('view_payment');
        Route::post('/update', 'PaymentMethodsController@update')->middleware('edit_payment');
        Route::post('/active', 'PaymentMethodsController@active')->middleware('edit_payment');
    });

    Route::group(['prefix' => 'admin/coupons', 'middleware' => 'auth:admin', 'namespace' => 'AdminControllers'], function () {
        Route::get('/display', 'CouponsController@display')->middleware('view_coupon');
        Route::get('/add', 'CouponsController@add')->middleware('add_coupon');
        Route::post('/insert', 'CouponsController@insert')->middleware('add_coupon');
        Route::get('/edit/{id}', 'CouponsController@edit')->middleware('edit_coupon');
        Route::post('/update', 'CouponsController@update')->middleware('edit_coupon');
        Route::post('/delete', 'CouponsController@delete')->middleware('delete_coupon');
        Route::get('/filter', 'CouponsController@filter')->middleware('view_coupon');
    });
    Route::group(['prefix' => 'admin/devices', 'middleware' => 'auth:admin', 'namespace' => 'AdminControllers'], function () {
        Route::get('/display', 'NotificationController@devices')->middleware('view_notification');
        Route::get('/viewdevices/{id}', 'NotificationController@viewdevices')->middleware('view_notification');
        Route::post('/notifyUser/', 'NotificationController@notifyUser')->middleware('edit_notification');
        Route::get('/notifications/', 'NotificationController@notifications')->middleware('view_notification');
        Route::post('/sendNotifications/', 'NotificationController@sendNotifications')->middleware('edit_notification');
        Route::post('/customerNotification/', 'NotificationController@customerNotification')->middleware('view_notification');
        Route::post('/singleUserNotification/', 'NotificationController@singleUserNotification')->middleware('edit_notification');
        Route::post('/deletedevice/', 'NotificationController@deletedevice')->middleware('view_notification');
    });

    Route::group(['prefix' => 'admin/devices', 'middleware' => 'auth:admin', 'namespace' => 'AdminControllers'], function () {
        Route::get('/', 'NotificationController@devices')->middleware('view_notification');
        Route::get('/viewdevices/{id}', 'NotificationController@viewdevices')->middleware('view_notification');
        Route::post('/notifyUser/', 'NotificationController@notifyUser')->middleware('edit_notification');
        Route::get('/notifications/', 'NotificationController@notifications')->middleware('view_notification');
        Route::post('/sendNotifications/', 'NotificationController@sendNotifications')->middleware('edit_notification');
        Route::post('/customerNotification/', 'NotificationController@customerNotification')->middleware('view_notification');
        Route::post('/singleUserNotification/', 'NotificationController@singleUserNotification')->middleware('edit_notification');
        Route::post('/deletedevice/', 'NotificationController@deletedevice')->middleware('view_notification');
    });

    Route::group(['prefix' => 'admin/orders', 'middleware' => 'auth:admin', 'namespace' => 'AdminControllers'], function () {
        Route::get('/display', 'OrdersController@display')->middleware('view_order');
        Route::get('/vieworder/{id}', 'OrdersController@vieworder')->middleware('view_order');
        Route::post('/updateOrder', 'OrdersController@updateOrder')->middleware('edit_order');
        Route::post('/deleteOrder', 'OrdersController@deleteOrder')->middleware('edit_order');
        Route::get('/invoiceprint/{id}', 'OrdersController@invoiceprint')->middleware('view_order');
        Route::get('/orderstatus', 'SiteSettingController@orderstatus')->middleware('view_order');
        Route::get('/addorderstatus', 'SiteSettingController@addorderstatus')->middleware('edit_order');
        Route::post('/addNewOrderStatus', 'SiteSettingController@addNewOrderStatus')->middleware('edit_order');
        Route::get('/editorderstatus/{id}', 'SiteSettingController@editorderstatus')->middleware('edit_order');
        Route::post('/updateOrderStatus', 'SiteSettingController@updateOrderStatus')->middleware('edit_order');
        Route::post('/deleteOrderStatus', 'SiteSettingController@deleteOrderStatus')->middleware('edit_order');
        
        //exter orders view by status
        Route::get('/new', 'OrdersController@newOrders')->middleware('view_order');
        Route::get('/cancelled', 'OrdersController@cancelOrders')->middleware('view_order');
        Route::get('/pending', 'OrdersController@pendingOrders')->middleware('view_order');
        Route::get('/complete', 'OrdersController@completeOrders')->middleware('view_order');
        Route::get('/failed', 'OrdersController@failedOrders')->middleware('view_order');
        Route::get('/processing', 'OrdersController@processingOrders')->middleware('view_order');
        Route::get('/shipped', 'OrdersController@shippedOrders')->middleware('view_order');
    });

    Route::group(['prefix' => 'admin/banners', 'middleware' => 'auth:admin', 'namespace' => 'AdminControllers'], function () {
        Route::get('/', 'BannersController@banners')->middleware('view_app_setting');
        Route::get('/add', 'BannersController@addbanner')->middleware('edit_app_setting');
        Route::post('/insert', 'BannersController@addNewBanner')->middleware('edit_app_setting');
        Route::get('/edit/{id}', 'BannersController@editbanner')->middleware('edit_app_setting');
        Route::post('/update', 'BannersController@updateBanner')->middleware('edit_app_setting');
        Route::post('/delete', 'BannersController@deleteBanner')->middleware('edit_app_setting');
        Route::get('/filter', 'BannersController@filterbanners')->middleware('edit_app_setting');

    });
    
    // admin home sections
    Route::group(['prefix' => 'admin/homeSections', 'middleware' => 'auth:admin', 'namespace' => 'AdminControllers'], function () {
        Route::get('/', 'BannersController@homeSection')->middleware('view_app_setting');
        Route::get('/add', 'BannersController@addHomeSection')->middleware('edit_app_setting');
        Route::post('/insert', 'BannersController@addNewHomeSection')->middleware('edit_app_setting');
        Route::get('/edit/{id}', 'BannersController@editHomeSection')->middleware('edit_app_setting');
        Route::post('/update', 'BannersController@updateHomeSection')->middleware('edit_app_setting');
        Route::post('/delete', 'BannersController@deleteHomeSection')->middleware('edit_app_setting');
         #####################################
        # App Home Sections
        #####################################
        Route::get('/fourBox', 'AppHomeSectionsController@fourBox')->middleware('edit_app_setting');
        Route::post('/fourBox/insert', 'AppHomeSectionsController@fourBoxInsert')->middleware('edit_app_setting');
        Route::get('/fourBox/edit/{id}', 'AppHomeSectionsController@editfourBox')->middleware('edit_app_setting');
        Route::post('/fourBox/update', 'AppHomeSectionsController@fourBoxUpdate')->middleware('edit_app_setting');
        
        #######################
        # Two Box app Section
        #######################
        Route::get('/twoBox', 'AppHomeSectionsController@twoBox')->middleware('edit_app_setting');
        Route::post('/twoBox/insert', 'AppHomeSectionsController@twoBoxInsert')->middleware('edit_app_setting');
        Route::get('/twoBox/edit/{id}', 'AppHomeSectionsController@editTwoBox')->middleware('edit_app_setting');
        Route::post('/twoBox/update', 'AppHomeSectionsController@twoBoxUpdate')->middleware('edit_app_setting');
        #######################
        # bannerPlainLarge app Section
        #######################
        Route::get('/bannerPlainLarge', 'AppHomeSectionsController@bannerPlainLarge')->middleware('edit_app_setting');
        Route::post('/bannerPlainLarge/insert', 'AppHomeSectionsController@bannerPlainLargeInsert')->middleware('edit_app_setting');
        Route::get('/bannerPlainLarge/edit/{id}', 'AppHomeSectionsController@editBannerPlainLarge')->middleware('edit_app_setting');
        Route::post('/bannerPlainLarge/update', 'AppHomeSectionsController@bannerPlainLargeUpdate')->middleware('edit_app_setting');
        
        #######################
        # bannerPlainThin app Section
        #######################
        Route::get('/bannerPlainThin', 'AppHomeSectionsController@bannerPlainThin')->middleware('edit_app_setting');
        Route::post('/bannerPlainThin/insert', 'AppHomeSectionsController@bannerPlainThinInsert')->middleware('edit_app_setting');
        Route::get('/bannerPlainThin/edit/{id}', 'AppHomeSectionsController@editBannerPlainThin')->middleware('edit_app_setting');
        Route::post('/bannerPlainThin/update', 'AppHomeSectionsController@bannerPlainThinUpdate')->middleware('edit_app_setting');
        
        #######################
        # verticle slider with bg app Section
        #######################
        Route::get('/verticleSliderWithBg', 'AppHomeSectionsController@verticleSliderWithBg')->middleware('edit_app_setting');
        Route::post('/verticleSliderWithBg/insert', 'AppHomeSectionsController@verticleSliderWithBgInsert')->middleware('edit_app_setting');
        Route::get('/verticleSliderWithBg/edit/{id}', 'AppHomeSectionsController@editVerticleSliderWithBg')->middleware('edit_app_setting');
        Route::post('/verticleSliderWithBg/update', 'AppHomeSectionsController@verticleSliderWithBgUpdate')->middleware('edit_app_setting');
        
        #######################
        # video carasoul app Section
        #######################
        Route::get('/videoCarasoul', 'AppHomeSectionsController@videoCarasoul')->middleware('edit_app_setting');
        Route::post('/videoCarasoul/insert', 'AppHomeSectionsController@videoCarasoulInsert')->middleware('edit_app_setting');
        Route::get('/videoCarasoul/edit/{id}', 'AppHomeSectionsController@editVideoCarasoul')->middleware('edit_app_setting');
        Route::post('/videoCarasoul/update', 'AppHomeSectionsController@videoCarasoulUpdate')->middleware('edit_app_setting');
        
        #######################
        # productMarqee app Section
        #######################
        Route::get('/productMarqee', 'AppHomeSectionsController@productMarqee')->middleware('edit_app_setting');
        Route::post('/productMarqee/insert', 'AppHomeSectionsController@productMarqeeInsert')->middleware('edit_app_setting');
        Route::get('/productMarqee/edit/{id}', 'AppHomeSectionsController@editProductMarqee')->middleware('edit_app_setting');
        Route::post('/productMarqee/update', 'AppHomeSectionsController@productMarqeeUpdate')->middleware('edit_app_setting');
        
        ############################################################################################################################################################
        
        Route::post('/section/update', 'AppHomeSectionsController@updateSection')->middleware('edit_app_setting');
        Route::post('/section/data/delete', 'AppHomeSectionsController@deleteSectionData')->middleware('edit_app_setting');

    });
    

    Route::group(['prefix' => 'admin', 'middleware' => 'auth:admin', 'namespace' => 'AdminControllers'], function () {

        Route::get('/customers-orders-report', 'ReportsController@statsCustomers')->middleware('report');
        Route::get('/customer-orders-print', 'ReportsController@customerOrdersPrint')->middleware('report');
        Route::get('/statscustomers', 'ReportsController@statsCustomers')->middleware('report');
        Route::get('/statsproductspurchased', 'ReportsController@statsProductsPurchased')->middleware('report');
        Route::get('/statsproductsliked', 'ReportsController@statsProductsLiked')->middleware('report');
        Route::get('/outofstock', 'ReportsController@outofstock')->middleware('report');
        Route::get('/outofstockprint', 'ReportsController@outofstockprint')->middleware('report');
        Route::get('/lowinstock', 'ReportsController@lowinstock')->middleware('report');
        Route::get('/stockin', 'ReportsController@stockin')->middleware('report');
        Route::post('/productSaleReport', 'ReportsController@productSaleReport')->middleware('report');
        Route::get('/couponreport', 'ReportsController@couponReport')->middleware('report');
        Route::get('/couponreport-print', 'ReportsController@couponReportPrint')->middleware('report');

        
        Route::get('/salesreport', 'ReportsController@salesreport')->middleware('report');
        // Route::get('/customer-orders-print', 'ReportsController@customerOrdersPrint')->middleware('report');
        
        Route::get('/inventoryreport', 'ReportsController@inventoryreport')->middleware('report');
        Route::get('/inventoryreportprint', 'ReportsController@inventoryreportprint')->middleware('report');

        
        Route::get('/minstock', 'ReportsController@minstock')->middleware('report');
        Route::get('/minstockprint', 'ReportsController@minstockprint')->middleware('report');
        
        Route::get('/maxstock', 'ReportsController@maxstock')->middleware('report');
        Route::get('/maxstockprint', 'ReportsController@maxstockprint')->middleware('report');

////////////////////////////////////////////////////////////////////////////////////
        //////////////     APP ROUTES
        ////////////////////////////////////////////////////////////////////////////////////
        //app pages controller
        Route::get('/pages', 'PagesController@pages')->middleware('view_app_setting', 'application_routes');
        Route::get('/addpage', 'PagesController@addpage')->middleware('edit_app_setting', 'application_routes');
        Route::post('/addnewpage', 'PagesController@addnewpage')->middleware('edit_app_setting', 'application_routes');
        Route::get('/editpage/{id}', 'PagesController@editpage')->middleware('edit_app_setting', 'application_routes');
        Route::post('/updatepage', 'PagesController@updatepage')->middleware('edit_app_setting', 'application_routes');
        Route::get('/pageStatus', 'PagesController@pageStatus')->middleware('edit_app_setting', 'application_routes');
        Route::get('/filterpages', 'PagesController@filterpages')->middleware('view_app_setting', 'application_routes');
        //manageAppLabel
        Route::get('/listingAppLabels', 'AppLabelsController@listingAppLabels')->middleware('view_app_setting', 'application_routes');
        Route::get('/addappkey', 'AppLabelsController@addappkey')->middleware('edit_app_setting', 'application_routes');
        Route::post('/addNewAppLabel', 'AppLabelsController@addNewAppLabel')->middleware('edit_app_setting', 'application_routes');
        Route::get('/editAppLabel/{id}', 'AppLabelsController@editAppLabel')->middleware('edit_app_setting', 'application_routes');
        Route::post('/updateAppLabel/', 'AppLabelsController@updateAppLabel')->middleware('edit_app_setting', 'application_routes');
        Route::get('/applabel', 'AppLabelsController@listingAppLabels')->middleware('view_app_setting', 'application_routes');
         //Route::get('/applabel', 'AppLabelsController@manageAppLabel')->middleware('view_app_setting', 'application_routes');

        Route::get('/admobSettings', 'SiteSettingController@admobSettings')->middleware('view_app_setting', 'application_routes');
        Route::get('/applicationapi', 'SiteSettingController@applicationApi')->middleware('view_app_setting', 'application_routes');
        Route::get('/appsettings', 'SiteSettingController@appSettings')->middleware('view_app_setting', 'application_routes');
        
         Route::get('/videolinks', 'SiteSettingController@videolinks')->middleware('view_app_setting', 'application_routes');
         Route::get('/addvideo', 'SiteSettingController@addvideolinks')->middleware('view_app_setting', 'application_routes');
         Route::get('/editVideoLink/{id}', 'SiteSettingController@editVideoLink')->middleware('view_app_setting', 'application_routes');
         Route::post('/videolinks/insert', 'SiteSettingController@insertvideolinks')->middleware('view_app_setting', 'application_routes');
         Route::post('/videolinks/update', 'SiteSettingController@updateVideolink')->middleware('view_app_setting', 'application_routes');
        
 //login
 Route::get('/loginsetting', 'SiteSettingController@loginsetting')->middleware('view_general_setting');

////////////////////////////////////////////////////////////////////////////////////
        //////////////     SITE ROUTES
        ////////////////////////////////////////////////////////////////////////////////////
        
        // home page banners
        Route::get('/homebanners', 'HomeBannersController@display')->middleware('view_web_setting', 'website_routes');
        Route::post('/homebanners/insert', 'HomeBannersController@insert')->middleware('view_web_setting', 'website_routes');
        
        Route::get('/menus', 'MenusController@menus')->middleware('view_web_setting', 'website_routes');
        Route::get('/addmenus', 'MenusController@addmenus')->middleware('edit_web_setting', 'website_routes');
        Route::post('/addnewmenu', 'MenusController@addnewmenu')->middleware('edit_web_setting', 'website_routes');
        Route::get('/editmenu/{id}', 'MenusController@editmenu')->middleware('edit_web_setting', 'website_routes');
        Route::post('/updatemenu', 'MenusController@updatemenu')->middleware('edit_web_setting', 'website_routes');
        Route::get('/deletemenu/{id}', 'MenusController@deletemenu')->middleware('edit_web_setting', 'website_routes');
        Route::post('/deletemenu', 'MenusController@deletemenu')->middleware('edit_web_setting', 'website_routes');
        Route::post('/menuposition', 'MenusController@menuposition')->middleware('edit_web_setting', 'website_routes');
        Route::get('/catalogmenu', 'MenusController@catalogmenu')->middleware('edit_web_setting', 'website_routes');

        

        //site pages controller
        Route::get('/webpages', 'PagesController@webpages')->middleware('view_web_setting', 'website_routes');
        Route::get('/addwebpage', 'PagesController@addwebpage')->middleware('edit_web_setting', 'website_routes');
        Route::post('/addnewwebpage', 'PagesController@addnewwebpage')->middleware('edit_web_setting', 'website_routes');
        Route::get('/editwebpage/{id}', 'PagesController@editwebpage')->middleware('edit_web_setting', 'website_routes');
        Route::post('/updatewebpage', 'PagesController@updatewebpage')->middleware('edit_web_setting', 'website_routes');
        Route::get('/pageWebStatus', 'PagesController@pageWebStatus')->middleware('view_web_setting', 'website_routes');

        Route::get('/webthemes', 'SiteSettingController@webThemes')->middleware('view_web_setting', 'website_routes');
        Route::get('/themeSettings', 'SiteSettingController@themeSettings')->middleware('edit_web_setting', 'website_routes');

        Route::get('/seo', 'SiteSettingController@seo')->middleware('view_web_setting', 'website_routes');
        Route::get('/customstyle', 'SiteSettingController@customstyle')->middleware('view_web_setting', 'website_routes');
        Route::post('/updateWebTheme', 'SiteSettingController@updateWebTheme')->middleware('edit_web_setting', 'website_routes');
        Route::get('/websettings', 'SiteSettingController@webSettings')->middleware('view_web_setting', 'website_routes');
        Route::get('/instafeed', 'SiteSettingController@instafeed')->middleware('view_web_setting', 'website_routes');
        Route::get('/newsletter', 'SiteSettingController@newsletter')->middleware('view_web_setting', 'website_routes');

/////////////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////////////////
        //////////////     GENERAL ROUTES
        ////////////////////////////////////////////////////////////////////////////////////

//units
        Route::get('/units', 'SiteSettingController@units')->middleware('view_general_setting');
        Route::get('/addunit', 'SiteSettingController@addunit')->middleware('edit_general_setting');
        Route::post('/addnewunit', 'SiteSettingController@addnewunit')->middleware('edit_general_setting');
        Route::get('/editunit/{id}', 'SiteSettingController@editunit')->middleware('edit_general_setting');
        Route::post('/updateunit', 'SiteSettingController@updateunit')->middleware('edit_general_setting');
        Route::post('/deleteunit', 'SiteSettingController@deleteunit')->middleware('edit_general_setting');

        Route::get('/orderstatus', 'SiteSettingController@orderstatus')->middleware('view_general_setting');
        Route::get('/addorderstatus', 'SiteSettingController@addorderstatus')->middleware('edit_general_setting');
        Route::post('/addNewOrderStatus', 'SiteSettingController@addNewOrderStatus')->middleware('edit_general_setting');
        Route::get('/editorderstatus/{id}', 'SiteSettingController@editorderstatus')->middleware('edit_general_setting');
        Route::post('/updateOrderStatus', 'SiteSettingController@updateOrderStatus')->middleware('edit_general_setting');
        Route::post('/deleteOrderStatus', 'SiteSettingController@deleteOrderStatus')->middleware('edit_general_setting');

        Route::get('/facebooksettings', 'SiteSettingController@facebookSettings')->middleware('view_general_setting');
        Route::get('/instasettings', 'SiteSettingController@instasettings')->middleware('view_general_setting');
        Route::get('/googlesettings', 'SiteSettingController@googleSettings')->middleware('view_general_setting');
        //pushNotification
        Route::get('/pushnotification', 'SiteSettingController@pushNotification')->middleware('view_general_setting');
        Route::get('/alertsetting', 'SiteSettingController@alertSetting')->middleware('view_general_setting');
        Route::post('/updateAlertSetting', 'SiteSettingController@updateAlertSetting');
        Route::get('/setting', 'SiteSettingController@setting')->middleware('edit_general_setting');
        Route::get('/setting/edit/{id}', 'SiteSettingController@editSettings')->middleware('edit_general_setting');
        Route::post('/updateSetting', 'SiteSettingController@updateSetting')->middleware('edit_general_setting');

        //admin managements
        Route::get('/admins', 'AdminController@admins')->middleware('view_manage_admin');
        Route::get('/addadmins', 'AdminController@addadmins')->middleware('add_manage_admin');
        Route::post('/addnewadmin', 'AdminController@addnewadmin')->middleware('add_manage_admin');
        Route::get('/editadmin/{id}', 'AdminController@editadmin')->middleware('edit_manage_admin');
        Route::post('/updateadmin', 'AdminController@updateadmin')->middleware('edit_manage_admin');
        Route::post('/deleteadmin', 'AdminController@deleteadmin')->middleware('delete_manage_admin');

        //admin managements
        Route::get('/manageroles', 'AdminController@manageroles')->middleware('manage_role');
        Route::get('/addrole/{id}', 'AdminController@addrole')->middleware('manage_role');
        Route::post('/addnewroles', 'AdminController@addnewroles')->middleware('manage_role');
        Route::get('/addadmintype', 'AdminController@addadmintype')->middleware('add_admin_type');
        Route::post('/addnewtype', 'AdminController@addnewtype')->middleware('add_admin_type');
        Route::get('/editadmintype/{id}', 'AdminController@editadmintype')->middleware('edit_admin_type');
        Route::post('/updatetype', 'AdminController@updatetype')->middleware('edit_admin_type');
        Route::post('/deleteadmintype', 'AdminController@deleteadmintype')->middleware('delete_admin_type');

        Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');
    });

    Route::group(['prefix' => 'admin/managements', 'middleware' => 'auth:admin', 'namespace' => 'AdminControllers'], function () {
        Route::get('/merge', 'ManagementsController@merge')->middleware('edit_management');
        Route::get('/backup', 'ManagementsController@backup')->middleware('edit_management');
        Route::post('/take_backup', 'ManagementsController@take_backup')->middleware('edit_management');
        Route::get('/import', 'ManagementsController@import')->middleware('edit_management');
        Route::post('/importdata', 'ManagementsController@importdata')->middleware('edit_management');
        Route::post('/mergecontent', 'ManagementsController@mergecontent')->middleware('edit_management');
        Route::get('/updater', 'ManagementsController@updater')->middleware('edit_management');
        Route::post('/checkpassword', 'ManagementsController@checkpassword')->middleware('edit_management');
        Route::post('/updatercontent', 'ManagementsController@updatercontent')->middleware('edit_management');
    });
    
    //manage Shops
    Route::group(['prefix' => 'admin/shop', 'middleware' => 'auth:admin', 'namespace' => 'AdminControllers'], function () {
        Route::get('/', 'ShopController@index')->middleware('view_shop');
        //Route::get('/add', 'ShopController@add')->middleware('add_shop');
        //Route::post('/insert', 'ShopController@addNewBanner')->middleware('add_shop');
        Route::get('/detail/{id}', 'ShopController@details')->middleware('view_shop');
        Route::get('/edit/{id}', 'ShopController@edit')->middleware('edit_shop');
        Route::post('/update', 'ShopController@update')->middleware('edit_shop');
        Route::post('/approve_reject', 'ShopController@approveReject')->middleware('edit_shop');
        Route::post('/delete', 'ShopController@delete')->middleware('delete_shop');
        Route::post('/updateStatus', 'ShopController@updateStatus')->middleware('edit_shop');
        //shop List by status
        Route::get('/new', 'ShopController@newShops')->middleware('view_shop');
        Route::get('/rejected', 'ShopController@rejectedShops')->middleware('view_shop');
        Route::get('/approved', 'ShopController@approvedShops')->middleware('view_shop');

    });
    
    //manage Vendors
    Route::group(['prefix' => 'admin/vendor', 'middleware' => 'auth:admin', 'namespace' => 'AdminControllers'], function () {
        Route::get('/', 'VendorController@index')->middleware('view_vendor');
        //Route::get('/add', 'VendorController@add')->middleware('add_shop');
        //Route::post('/insert', 'VendorController@addNewBanner')->middleware('add_shop');
        Route::get('/detail/{id}', 'VendorController@details')->middleware('view_vendor');
        Route::get('/edit/{id}', 'VendorController@edit')->middleware('edit_vendor');
        Route::post('/update', 'VendorController@update')->middleware('edit_vendor');
        Route::post('/approve_reject', 'VendorController@approveReject')->middleware('edit_vendor');
        Route::post('/delete', 'VendorController@delete')->middleware('delete_vendor');
        Route::post('/updateStatus', 'VendorController@updateStatus')->middleware('edit_vendor');
        //shop List by status
        Route::get('/new', 'VendorController@newVendors')->middleware('view_vendor');
        Route::get('/rejected', 'VendorController@rejectedVendors')->middleware('view_vendor');
        Route::get('/approved', 'VendorController@approvedVendors')->middleware('view_vendor');

    });

});
