<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Controllers\AdminControllers;
use App\Http\Controllers\Controller;
use App\Models\Core\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\AdminControllers\SiteSettingController;
use App\Models\Core\ShopModel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
/**
 * Description of ShopController
 *
 * @author singh
 */
class ShopController extends Controller
{
    //put your code here
    public function __construct( Setting $setting)
    {
        $this->myVarsetting = new SiteSettingController($setting);
        $this->Setting = $setting;
    }
    
    //banners
    public function index(Request $request)
    {
        $title = 'Shop List';

        $result = array();
        $message = array();

        $banners = ShopModel::sortable()
                 ->paginate(20);

        $result['message'] = $message;
        $result['shops'] = $banners;
        $result['commonContent'] = $this->Setting->commonContent();

        return view("admin.shops.index", ["title"=>$title])->with('result', $result);
    }
    
    //new shop lists
    
    public function newShops(Request $request)
    {
        $title = 'New Shop Lists';

        $result = array();
        $message = array();

        $banners = ShopModel::where('status','PENDING')->sortable()
                 ->paginate(20);

        $result['message'] = $message;
        $result['shops'] = $banners;
        $result['commonContent'] = $this->Setting->commonContent();

        return view("admin.shops.new", ["title"=>$title])->with('result', $result);
    }
    
    //new shop lists
    
    public function rejectedShops(Request $request)
    {
        $title = 'Rejected Shop Lists';

        $result = array();
        $message = array();

        $banners = ShopModel::where('status','REJECTED')->sortable()
                 ->paginate(20);

        $result['message'] = $message;
        $result['shops'] = $banners;
        $result['commonContent'] = $this->Setting->commonContent();

        return view("admin.shops.rejected", ["title"=>$title])->with('result', $result);
    }
    public function approvedShops(Request $request)
    {
        $title = 'Approved Shop Lists';

        $result = array();
        $message = array();

        $banners = ShopModel::where('status','ACTIVE')->sortable()
                 ->paginate(20);

        $result['message'] = $message;
        $result['shops'] = $banners;
        $result['commonContent'] = $this->Setting->commonContent();

        return view("admin.shops.approved", ["title"=>$title])->with('result', $result);
    }
    
    public function details(Request $request)
    {
        $title = 'Shop Detail';
         $id = $request->id;
        $result = array();
        $message = array();

        $details = ShopModel::find($id);
                 

        $result['message'] = $message;
        $result['shop'] = $details;
        $result['commonContent'] = $this->Setting->commonContent();

        return view("admin.shops.details", ["title"=>$title])->with('result', $result);
    }
    
    
    //update kyc status
    public function approveReject(Request $request) {
        Log::debug(__CLASS__."::".__FUNCTION__."started");
         try{
                 DB::beginTransaction();
        $id  =	$request->id;
        $status  =	$request->status;
        $reason  = htmlspecialchars(strip_tags($request->reason));
        $data = ShopModel::where('id',$id)->where('status','PENDING')->first();
        
         if(isset($data->id)){
                  $data->status = $status;
                  $data->reason = $reason;
                  $data->updated_by = auth()->user()->email;
                  $data->updated_at = Carbon::now();
                
                    if($status =='ACTIVE'){
                        $qr_image = 'images/shop/qr/img-' . time() . '.png';
                        QrCode::format('png')->size(200)->generate($data->shop_code,public_path($qr_image));
                         $data->qr_code_img = $qr_image;
                         
                    }
                    
                    if ($data->save()) {
                        DB::commit();
                        session()->flash('success', "Shop has been $status successfully!");
                    } else{
                        Log::error("Error Occured at shop status $status update ");
                        session()->flash('error', 'Some Error Occured please try again !');
                    }
                
                
         }else{
             session()->flash('error', 'Error Occured!');
         }
         
         }catch(\Exception $e){
                session()->flash('error', "Error Occured".$e->getMessage());
                Log::error("Error Occured".$e->getMessage());
            }
            return redirect('admin/shop');
    }
    
    public function updateStatus(Request $request) {
        Log::debug(__CLASS__."::".__FUNCTION__."started");
         try{
                 DB::beginTransaction();
        $id  =	$request->id;
        $status  =	$request->status;
        $reason  = htmlspecialchars(strip_tags($request->reason));
        $data = ShopModel::where('id',$id)->first();
        
         if(isset($data->id)){
                  $data->status = $status;
                  $data->reason = $reason;
                  $data->updated_by = auth()->user()->email;
                  $data->updated_at = Carbon::now();
                
                    if ($data->save()) {
                        DB::commit();
                        session()->flash('success', "Shop has been $status successfully!");
                    } else{
                        Log::error("Error Occured at shop status $status update ");
                        session()->flash('error', 'Some Error Occured please try again !');
                    }
                
                
         }else{
             session()->flash('error', 'Error Occured!');
         }
         
         }catch(\Exception $e){
                session()->flash('error', "Error Occured".$e->getMessage());
                Log::error("Error Occured".$e->getMessage());
            }
            return redirect('admin/shop');
    }
}
