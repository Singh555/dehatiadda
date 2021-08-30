<?php

namespace App\Models\AppModels;

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
use Carbon;
use App\Helpers\HttpStatus;
use App\Models\Eloquent\AppSection;
use App\Models\Eloquent\AppSectionData;
use App\Models\Eloquent\Banner;
use App\Models\Eloquent\Product;
use App\Models\Eloquent\RecentlyViewedProduct;
use App\Models\AppModels\Product as ProductModel;

class Home extends Model {

    public static function getSections($request) {
        $consumer_data = array();
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

            $return = array();
            $sections = AppSection::
                    where('status', "ACTIVE")
                    ->orderBy('sort_order')
                    ->get();


            if (count($sections) > 0) {
                foreach ($sections as $value) {
                    $section = array(
                        'id' => $value->id,
                        'view_type' => $value->view_type,
                        'title' => $value->title,
                        'image' => $value->image,
                        'height' => $value->height,
                    );
                    switch ($value->view_type) {
                        case 'CATEGORY_TOP_CIRCLE':
                            //$return->category_top_circle = array();
                            break;
                        case 'BANNER_CAROUSEL':
                            if ($value->position == '1') {
                                $bannerData = Banner::
                                        where('status', "1")
                                ;
                                $section['data'] = $bannerData->where('view_type', 'banner')->where('view_position', 'top')->get(['banners_id', 'banners_title', 'type', 'banners_url', 'banners_image_url']);
                            } else if ($value->position == '2') {
                                $bannerData = Banner::
                                        where('status', "1")
                                ;
                                $section['data'] = $bannerData->where('view_type', 'banner')->where('view_position', 'bottom')->get(['banners_id', 'banners_title', 'type', 'banners_url', 'banners_image_url']);
                            }
                            break;
                        case '4_BOX':
                            if ($value->position == '1') {
                                $sectionsData = AppSectionData::
                                        where('status', "ACTIVE")
                                        ->orderBy('sort_order')
                                ;
                                $section['data'] = $sectionsData->where('section_id', $value->id)->get();
                            } else if ($value->position == '2') {
                                $sectionsData = AppSectionData::
                                        where('status', "ACTIVE")
                                        ->orderBy('sort_order')
                                ;
                                $section['data'] = $sectionsData->where('section_id', $value->id)->get();
                            }
                            break;
                        case 'VIDEO_CAROUSEL':
                            if ($value->position == '1') {
                                $sectionsData = AppSectionData::
                                        where('status', "ACTIVE")
                                        ->orderBy('sort_order');

                                $section['data'] = $sectionsData->where('section_id', $value->id)->get();
                            }
                            break;
                        case 'BANNER_PLAIN_LARGE':
                            if ($value->position == '1') {
                                $sectionsData = AppSectionData::
                                        where('status', "ACTIVE")
                                        ->orderBy('sort_order');

                                $section['data'] = $sectionsData->where('section_id', $value->id)->get();
                            }
                            break;
                        case 'VERTICLE_SLIDER':
                            if ($value->position == '1') {
                                $bannerData = Banner::
                                        where('status', "1")
                                ;
                                $section['data'] = $bannerData->where('view_type', 'slider')->where('view_position', 'top')->get(['banners_id', 'banners_title', 'type', 'banners_url', 'banners_image_url']);
                            } else if ($value->position == '2') {
                                $bannerData = Banner::
                                        where('status', "1")
                                ;
                                $section['data'] = $bannerData->where('view_type', 'slider')->where('view_position', 'bottom')->get(['banners_id', 'banners_title', 'type', 'banners_url', 'banners_image_url']);
                            }
                            break;

                        case 'BANNER_PLAIN_THIN':
                            if ($value->position == '1') {
                                $sectionsData = AppSectionData::
                                        where('status', "ACTIVE")
                                        ->orderBy('sort_order')
                                        ->where('section_id', $value->id)
                                ;

                                $section['data'] = $sectionsData->get();
                            }
                            break;
                        case '2_BOX':
                            if ($value->position == '1') {
                                $sectionsData = AppSectionData::
                                        where('status', "ACTIVE")
                                        ->orderBy('sort_order')
                                        ->where('section_id', $value->id)
                                ;

                                $section['data'] = $sectionsData->get();
                            }
                            break;
                        case 'VERTICLE_SLIDER_WITH_BG':
                            if ($value->position == '1') {
                                $sectionsData = AppSectionData::
                                        where('status', "ACTIVE")
                                        ->orderBy('sort_order')
                                        ->where('section_id', $value->id)
                                ;
                                $section['data'] = $sectionsData->get();
                            }
                            break;
                        case 'PRODUCT_MARQUEE':
                            $sectionsData = AppSectionData::
                                            where('status', "ACTIVE")
                                            ->orderBy('sort_order')
                                            ->where('section_id', $value->id)->first();
                            $urls = explode(",", $sectionsData->urls);
                            $productsData = Product::
                                    where('products_status', "1")
                                    ->whereIn('products_id', $urls)
                                    ->with('description:products_id,products_name as name')
                            ;
                            $section['data'] = $productsData->get(['products_id', 'products_image_url', 'products_price', 'discounted_price', 'discount_per']);


                            break;
                        case 'RECENTLY_VIEWED':

                            if (isset(auth()->user()->id)) {
                                $productsData = ProductModel::getRecentlyViewedProducts(auth()->user()->id);

                                $section['data'] = $productsData;
                            }


                            break;
                        default:
                            break;
                    }
                    array_push($return, $section);
                }
            }

            if (!empty($return)) {
                return returnResponse("Home Sections are returned successfull.", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $return);
            } else {
                $banners = array();
                return returnResponse("No Section found.", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $banners);
            }
        }

        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
    }

}
