<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\Controller;
use App\Models\Core\Images;
use App\Models\Core\Language;
use App\Models\Core\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Carbon;
class SiteSettingController extends Controller
{

    public function __construct()
    {
        $setting = new Setting();
        $this->Setting = $setting;

    }

    public function commonsetting()
    {
        $result = array('pagination' => '20');
        return $result;
    }

    public function getSetting()
    {

        $setting = $this->Setting->getSettings();
        return $setting;
    }
    public function getallsetting()
    {

        $setting = $this->Setting->getallsetting();
        return $setting;
    }

    public function imageType()
    {
        $extensions = array('gif', 'jpg', 'jpeg', 'png');
        return $extensions;
    }

    public function getlanguages()
    {

        $languages = $this->Setting->fetchLanguages();
        return $languages;
    }

    //units page
    public function getUnits()
    {

        $units = $this->Setting->Units();
        return $units;
    }
//alert Setting
    public function getAlertSetting()
    {
        $setting = $this->Setting->alterSetting();
        return $setting;
    }

// slugify method
    public function slugify($slug)
    {

        // replace non letter or digits by -
        $slug = preg_replace('~[^\pL\d]+~u', '-', $slug);

        // transliterate
        if (function_exists('iconv')) {
            $slug = iconv('utf-8', 'us-ascii//TRANSLIT', $slug);
        }

        // remove unwanted characters
        $slug = preg_replace('~[^-\w]+~', '', $slug);

        // trim
        $slug = trim($slug, '-');

        // remove duplicate -
        $slug = preg_replace('~-+~', '-', $slug);

        // lowercase
        $slug = strtolower($slug);

        if (empty($slug)) {
            return 'n-a';
        }

        return $slug;
    }
    //getsinglelanguages
    public function getSingleLanguages($language_id)
    {

        $languagesClass = new Language();

        $languages = $languagesClass->getSingleLan();
        return $languages;
    }

    //setting page
    public function setting(Request $request)
    {

        $title = Lang::get("labels.setting");

        $result = array();

        $settings = $this->Setting->getallsetting();

        $result['settings'] = $settings->unique('id')->keyBy('id');
        $result['commonContent'] = $this->Setting->commonContent();

        return view("admin.settings.general.setting", ["title"=>$title])->with('result', $result);
    }

    //update setting
    public function updateSetting(Request $request)
    {

        $languages = $this->getLanguages();
        $extensions = $this->imageType();
        foreach ($request->all() as $key => $value) {

            if ($key == 'newsletter_image') {
                if( $request->newsletter_image !== null){
                        $value = $request->newsletter_image;
                        $this->Setting->settingUpdate($key, $value);
                }
                
            }
            //website logo
            elseif ($key == 'website_logo') {
                if( $request->website_logo !== null){
                        $value = $request->website_logo;
                        $this->Setting->settingUpdate($key, $value);
                }
                
            }else{

                if ($key == 'favicon') {
                    if( $request->favicon !== null){
                       
                            $value = $request->favicon;
                            $this->Setting->settingUpdate($key, $value);
                    }
                    
                }else{
                    $this->Setting->settingUpdate($key, $value);
                }

            }

           

            
        }

        $message = Lang::get("labels.SettingUpdateMessage");
        return redirect()->back()->withErrors([$message]);
    }

    //webSettings
    public function websettings(Request $request)
    {

        $images = new Images;
        $allimage = $images->getimages();
        $title = Lang::get("labels.setting");

        $result = array();

        $settings = $this->Setting->websetting();

        $result['settings'] = $settings->unique('id')->keyBy('id');
        $result['commonContent'] = $this->Setting->commonContent();

        return view("admin.settings.general.websetting", ["title"=>$title])->with('result', $result)->with('allimage', $allimage);

    }
    public function newsletter(Request $request)
    {

        $images = new Images;
        $allimage = $images->getimages();
        $title = Lang::get("labels.setting");

        $result = array();

        $settings = $this->Setting->websetting();

        $result['settings'] = $settings->unique('id')->keyBy('id');
        $result['commonContent'] = $this->Setting->commonContent();

        return view("admin.settings.general.newsletter", ["title"=>$title])->with('result', $result)->with('allimage', $allimage);

    }

    

    //appSettings
    public function appSettings(Request $request)
    {
        $title = Lang::get("labels.application_settings");
        $result = array();
        $settings = $this->Setting->getallsetting();
        //$result['settings'] = $settings->unique('id')->keyBy('id');
        $result['settings'] = $settings->where('show_admin','Y')->where('type','app')->where('id','!=',69)->where('id','!=',70);
        $result['commonContent'] = $this->Setting->commonContent();
        return view("admin.settings.app.appSettings", ["title"=>$title])->with('result', $result);
    }
    
    //editTaxClass
    public function editSettings(Request $request)
    {
        $title = "Edit Setting";

        $result = array();
        $message = array();

        //get function from other controller
        $myVar = new SiteSettingController();
        $result['languages'] = $myVar->getLanguages();


        $data = DB::table('settings')
            ->where('id', '=', $request->id)
            ->first();

        $result['data'] = $data;  
        $result['commonContent'] = $this->Setting->commonContent();
        return view("admin.settings.app.editAppSettings", ["title"=>$title])->with('result', $result);
    }

    //admobSettings
    public function admobSettings(Request $request)
    {
        $title = Lang::get("labels.admobSettings");
        $result = array();
        $settings = $this->Setting->getallsetting();
        $result['settings'] = $settings->unique('id')->keyBy('id');
        $result['commonContent'] = $this->Setting->commonContent();
        return view("admin.settings.app.admobSettings", ["title"=>$title])->with('result', $result);
    }

    //facebookSettings
    public function facebookSettings(Request $request)
    {
        $title = Lang::get("labels.facebook_settings");
        $result = array();
        $settings = $this->Setting->getallsetting();
        $result['settings'] = $settings->unique('id')->keyBy('id');
        $result['commonContent'] = $this->Setting->commonContent();
        return view("admin.settings.general.facebookSettings", ["title"=>$title])->with('result', $result);
    }

    //googleSettings
    public function googleSettings(Request $request)
    {
        $title = Lang::get("labels.google_settings");

        $result = array();

        $settings = $this->Setting->getallsetting();

        $result['settings'] = $settings->unique('id')->keyBy('id');
        $result['commonContent'] = $this->Setting->commonContent();

        return view("admin.settings.general.googleSettings", ["title"=>$title])->with('result', $result);
    }

    //applicationApi
    public function applicationApi(Request $request)
    {
        $title = Lang::get("labels.applicationApi");

        $result = array();

        $settings = $this->Setting->getallsetting();

        $result['settings'] = $settings->unique('id')->keyBy('id');
        $result['commonContent'] = $this->Setting->commonContent();

        return view("admin.settings.app.applicationApi", ["title"=>$title])->with('result', $result);
    }

    //websiteThemes
    public function webthemes(Request $request)
    {

        $title = Lang::get("labels.themes setting");
        $result = array();
        $setting = $this->Setting->getallsetting();
        $result['settings'] = $settings->unique('id')->keyBy('id');
        $result['commonContent'] = $this->Setting->commonContent();
        return view("admin.settings.general.webthemes", ["title"=>$title])->with('result', $result);
    }

    //seo
    public function seo(Request $request)
    {

        $title = Lang::get("labels.SEO Content");

        $result = array();

        $settings = $this->Setting->getallsetting();

        $result['settings'] = $settings->unique('id')->keyBy('id');
        $result['commonContent'] = $this->Setting->commonContent();

        return view("admin.settings.general.seo", ["title"=>$title])->with('result', $result);
    }

    //customstyle
    public function customstyle(Request $request)
    {
        $title = Lang::get("labels.custom_style/js");
        $result = array();
        $settings = $this->Setting->getallsetting();
        $result['settings'] = $settings->unique('id')->keyBy('id');
        $result['commonContent'] = $this->Setting->commonContent();
        return view("admin.settings.general.customstyle", ["title"=>$title])->with('result', $result);

    }

    //update Website Theme
    public function updateWebTheme(Request $request)
    {

        $chkAlreadyApplied = $this->Setting->chkalreadyApplied($request);

        if (count($chkAlreadyApplied) == 0) {
            $setting = $this->Setting->appliedsetting($request);
            print 'success';
        } else {
            print 'already';
        }
    }

    //generateKey
    public function generateKey(Request $request)
    {
        $result = array();
        $result['consumerKey'] = $this->getKey();
        $result['consumerSecret'] = $this->getKey();

        $this->Setting->appkey($result);

        $this->Setting->consumersecret($result);

        return $result;
    }

    public function getKey()
    {
        $start = substr(md5(uniqid(mt_rand(), true)), 0, 8);
        $middle = time();
        $end = substr(md5(uniqid(mt_rand(), true)), 0, 8);
        return $start . $middle . $end;
    }

    //Units
    public function units(Request $request)
    {

        $title = Lang::get("labels.ListingUnits");

        $result = array();

        $units = $this->Setting->fetchunit();

        $result['units'] = $units;
        $result['commonContent'] = $this->Setting->commonContent();

        return view("admin.settings.general.units.index", ["title"=>$title])->with('result', $result);
    }

    //addunit
    public function addunit(Request $request)
    {
        $title = Lang::get("labels.AddUnit");
        $result = array();
        $languages = $this->Setting->fetchLanguages();
        $result['languages'] = $languages;
        $result['commonContent'] = $this->Setting->commonContent();

        return view("admin.settings.general.units.add", ["title"=>$title])->with('result', $result);
    }

    //addnewunit
    public function addnewunit(Request $request)
    {
        $unitId = $this->Setting->fetchUnitid($request);
        $languages = $this->Setting->fetchLanguages();

        foreach ($languages as $languages_data) {
            $OrdersStatus = 'UnitName_' . $languages_data->languages_id;
            $language_id = $languages_data->languages_id;
            $req_OrdersStatus = $request->$OrdersStatus;

            $statusedec_id = $this->Setting->insetunit_desc($req_OrdersStatus, $unitId, $language_id);

        }

        $message = Lang::get("labels.UnitAddedMessage");
        return redirect()->back()->withErrors([$message]);
    }

    //editunit
    public function editunit(Request $request)
    {
        $title = Lang::get("labels.EditUnit");
        $result = array();
        $languages = $this->Setting->fetchLanguages();
        $result = $this->Setting->editunit($request);
        $result['commonContent'] = $this->Setting->commonContent();
        return view("admin.settings.general.units.edit", ["title"=>$title])->with('result', $result);
    }

    //updateunit
    public function updateunit(Request $request)
    {
        $orders_status = $this->Setting->updateunit($request);

        $languages = $this->Setting->fetchLanguages();
        foreach ($languages as $languages_data) {
            $OrdersStatus = 'UnitName_' . $languages_data->languages_id;
            $language_id = $languages_data->languages_id;
            $req_OrdersStatus = $request->$OrdersStatus;
            $check = $this->Setting->existUnit($request->id, $language_id);
            if ($check) {
                $this->Setting->updateunit_des($req_OrdersStatus, $request, $language_id);
            } else {
                $this->Setting->insetunit_desc($req_OrdersStatus, $request->id, $language_id);
            }

        }

        $message = Lang::get("labels.UnitUpdatedMessage");
        return redirect()->back()->withErrors([$message]);
    }

    //deleteunit
    public function deleteunit(Request $request)
    {
        $this->Setting->deleteunits($request);
        return redirect()->back()->withErrors([Lang::get("labels.UnitDeletedMessage")]);
    }

    //pushNotification
    public function pushNotification(Request $request)
    {
        $title = Lang::get("labels.pushNotification");
        $result = array();
        $settings = $this->Setting->getallsetting();
        $result['settings'] = $settings->unique('id')->keyBy('id');
        $result['commonContent'] = $this->Setting->commonContent();
        return view("admin.settings.general.pushNotification", ["title"=>$title])->with('result', $result);
    }

    //setting page
    public function alertSetting(Request $request)
    {
        $title = Lang::get("labels.alertSetting");
        $result = array();
        $setting = $this->Setting->alterSetting();
        $result['setting'] = $setting;
        $result['commonContent'] = $this->Setting->commonContent();
        return view("admin.settings.general.alertSetting", ["title"=>$title])->with('result', $result);
    }

    //alertSetting
    public function updateAlertSetting(Request $request)
    {
        $orders_status = $this->Setting->orderstatus($request);
        $message = Lang::get("labels.alertSettingUpdateMessage");
        return redirect()->back()->withErrors([$message]);
    }

    public function orderstatus(Request $request)
    {
        $title = Lang::get("labels.ListingOrderStatus");
        $result = array();
        $orders_status = $this->Setting->orderstatuses();
        $result['orders_status'] = $orders_status;
        $result['commonContent'] = $this->Setting->commonContent();
        return view("admin.Orders.orderstatus", ["title"=>$title])->with('result', $result);
    }

    public function editorderstatus(Request $request)
    {
        $title = Lang::get("labels.EditOrderStatus");
        $result = array();
        $result = $this->Setting->editorderstatus($request);
        $result['commonContent'] = $this->Setting->commonContent();
        return view("admin.Orders.editorderstatus", ["title"=>$title])->with('result', $result);
    }

    public function updateOrderStatus(Request $request)
    {
        $languages = $this->getlanguages();
        if ($request->public_flag == 1) {
            $orders_status = $this->Setting->updateflagestatus($request);
        }

        $orders_status = $this->Setting->updateflag($request);

        foreach ($languages as $languages_data) {
            //dd($request->all());

            $OrdersStatus = 'OrdersStatus_' . $languages_data->languages_id;
            $language_id = $languages_data->languages_id;
            $req_OrdersStatus = $request->$OrdersStatus;

            //check if exist record
            $check = $this->Setting->existOrderStatus($request->id, $language_id);
            if ($check) {
                $this->Setting->updateorderstatus($request, $language_id, $req_OrdersStatus);
            } else {
                // dd($request->id, $req_OrdersStatus, $language_id);
                $this->Setting->orderstatusadd($request->id, $req_OrdersStatus, $language_id);
            }
        }

        $message = Lang::get("labels.OrderStatusUpdatedMessage");
        return redirect()->back()->withErrors([$message]);
    }

    public function deleteOrderStatus(Request $request)
    {
        $this->Setting->deleteorderstatus($request);
        return redirect()->back()->withErrors([Lang::get("labels.OrderStatusDeletedMessage")]);
    }

    //addorderstatus
    public function addorderstatus(Request $request)
    {

        $title = Lang::get("labels.AddOrderStatus");
        $result = array();

        $languages = $this->Setting->fetchLanguages();
        $result['languages'] = $languages;
        $result['commonContent'] = $this->Setting->commonContent();

        return view("admin.Orders.addorderstatus", ["title"=>$title])->with('result', $result);
    }

    //addNewOrderStatus
    public function addNewOrderStatus(Request $request)
    {

        $languagesdata = $this->getlanguages();

        //total records
        $orders_status = $this->Setting->addneworder();
        $orders_status_id = $orders_status->orders_status_id + 1;
        $role_id = $request->role_id;

        if ($request->public_flag == 1) {
            $languages = $this->Setting->addflagorderstatus();
        }

        $statuse_id = $this->Setting->getorderstatusid($orders_status_id, $request);

        foreach ($languagesdata as $languages_data) {
            $OrdersStatus = 'OrdersStatus_' . $languages_data->languages_id;
            $language_id = $languages_data->languages_id;
            $req_OrdersStatus = $request->$OrdersStatus;
            $statusedec_id = $this->Setting->orderstatusadd($statuse_id, $req_OrdersStatus, $language_id);
        }

        $message = Lang::get("labels.OrderStatusAddedMessage");
        return redirect()->back()->withErrors([$message]);
    }


    public function instafeed(Request $request)
    {
        $images = new Images;
        $title = Lang::get("labels.instagramfeed");

        $result = array();

        $settings = $this->Setting->websetting();

        $result['settings'] = $settings->unique('id')->keyBy('id');
        $result['commonContent'] = $this->Setting->commonContent();

        return view("admin.settings.general.instafeed", ["title"=>$title])->with('result', $result);

    }
    public function loginsetting(Request $request)
    {
        $images = new Images;
        $title = Lang::get("labels.Login Setting");

        $result = array();

        $settings = $this->Setting->websetting();

        $result['settings'] = $settings->unique('id')->keyBy('id');
        $result['commonContent'] = $this->Setting->commonContent();

        return view("admin.settings.general.loginsetting", ["title"=>$title])->with('result', $result);
    }

    
    public function videolinks(Request $request) {
        $title = Lang::get("labels.video links");
        $result = array();

        $settings = $this->Setting->videolinks();
       $result['data'] = $settings;
        $result['commonContent'] = $this->Setting->commonContent();
         return view("admin.settings.app.videolinks", ["title"=>$title])->with('result', $result);
    }
    public function addvideolinks(Request $request) {
        $title = Lang::get("labels.AddNewVideo");
        $result = array();

        $result['commonContent'] = $this->Setting->commonContent();
         return view("admin.settings.app.addvideolinks", ["title"=>$title])->with('result', $result);
    }
    public function editVideoLink(Request $request) {
        $title = Lang::get("labels.EditVideo");
        $id = $request->id;
        $result = array();
        $result['data'] = DB::table('video_links')->where('id',$id)->first();
        $result['commonContent'] = $this->Setting->commonContent();
         return view("admin.settings.app.editvideolinks", ["title"=>$title])->with('result', $result);
    }
    public function insertvideolinks(Request $request) {
        $published_date = '';
        if($request->filled('publish_date')){
        $published_date = Carbon::createFromFormat('d/m/Y', $request->input('publish_date'));
        }

            DB::table('video_links')->insert([
                'title' => $request->title,
                'link' => $request->link,
                'published_date' => $published_date,
                'status' => $request->status,
            ]);

            return redirect()->back()->with('message', Lang::get("Video link has been added"));

        
    }
    public function updateVideolink(Request $request) {
        $published_date = '';
        $id = $request->id;
        if($request->filled('publish_date')){
        $published_date = Carbon::createFromFormat('d/m/Y', $request->input('publish_date'));
        }

            DB::table('video_links')->where('id',$id)->update([
                'title' => $request->title,
                'link' => $request->link,
                'published_date' => $published_date,
                'status' => $request->status,
            ]);

            return redirect()->back()->with('message', Lang::get("Video link has been updated"));

        
    }
    

}
