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
use App\Models\Core\VendorsModel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Core\User;
use App\Admin;
/**
 * Description of VendorController
 *
 * @author singh
 */
class VendorController extends Controller
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
        $title = 'Vendor List';

        $result = array();
        $message = array();

        $banners = VendorsModel::sortable()
                 ->paginate(20);

        $result['message'] = $message;
        $result['vendors'] = $banners;
        $result['commonContent'] = $this->Setting->commonContent();

        return view("admin.vendors.index", ["title"=>$title])->with('result', $result);
    }
    
    //new vendor lists
    
    public function newVendors(Request $request)
    {
        $title = 'New Vendor Lists';

        $result = array();
        $message = array();

        $banners = VendorsModel::where('status','PENDING')->sortable()
                 ->paginate(20);

        $result['message'] = $message;
        $result['vendors'] = $banners;
        $result['commonContent'] = $this->Setting->commonContent();

        return view("admin.vendors.new", ["title"=>$title])->with('result', $result);
    }
    
    //new vendor lists
    
    public function rejectedVendors(Request $request)
    {
        $title = 'Rejected Vendor Lists';

        $result = array();
        $message = array();

        $banners = VendorsModel::where('status','REJECTED')->sortable()
                 ->paginate(20);

        $result['message'] = $message;
        $result['vendors'] = $banners;
        $result['commonContent'] = $this->Setting->commonContent();

        return view("admin.vendors.rejected", ["title"=>$title])->with('result', $result);
    }
    public function approvedVendors(Request $request)
    {
        $title = 'Approved Vendor Lists';

        $result = array();
        $message = array();

        $banners = VendorsModel::where('status','ACTIVE')->sortable()
                 ->paginate(20);

        $result['message'] = $message;
        $result['vendors'] = $banners;
        $result['commonContent'] = $this->Setting->commonContent();

        return view("admin.vendors.approved", ["title"=>$title])->with('result', $result);
    }
    
    public function details(Request $request)
    {
        $title = 'Vendor Detail';
         $id = $request->id;
        $result = array();
        $message = array();

        $details = VendorsModel::find($id);
                 

        $result['message'] = $message;
        $result['vendor'] = $details;
        $result['commonContent'] = $this->Setting->commonContent();

        return view("admin.vendors.details", ["title"=>$title])->with('result', $result);
    }
    
    
    //update kyc status
    public function approveReject(Request $request) {
        Log::debug(__CLASS__."::".__FUNCTION__."started");
         try{
                 DB::beginTransaction();
        $id  =	$request->id;
        $status  =	$request->status;
        $reason  = htmlspecialchars(strip_tags($request->reason));
        $data = VendorsModel::where('id',$id)->where('status','PENDING')->first();
        
         if(isset($data->id)){
                  $data->status = $status;
                  $data->reason = $reason;
                  $data->updated_by = auth()->user()->email;
                  $data->updated_at = Carbon::now();
                
                    if($status =='ACTIVE'){
                        if(!Admin::where('id',$data->user_login_core_id)->update(['status'=>1])){
                            session()->flash('error', 'Error Occured while marking vendor login account as Active!');
                        }  
                    }
                    
                    if ($data->save()) {
                        DB::commit();
                        session()->flash('success', "Vendor has been $status successfully!");
                    } else{
                        Log::error("Error Occured at vendor status $status update ");
                        session()->flash('error', 'Some Error Occured please try again !');
                    }
                
                
         }else{
             session()->flash('error', 'Error Occured!');
         }
         
         }catch(\Exception $e){
                session()->flash('error', "Error Occured".$e->getMessage());
                Log::error("Error Occured".$e->getMessage());
            }
            return redirect('admin/vendor');
    }
    //update kyc status
    public function updateStatus(Request $request) {
        Log::debug(__CLASS__."::".__FUNCTION__."started");
         try{
                 DB::beginTransaction();
        $id  =	$request->vendors_id;
        $status  =	$request->status;
        $data = VendorsModel::where('id',$id)->first();
        
         if(isset($data->id)){
                  $data->status = $status;
                  $data->updated_by = auth()->user()->email;
                  $data->updated_at = Carbon::now();
                
                    if($status =='ACTIVE'){
                        if(!Admin::where('id',$data->user_login_core_id)->update(['status'=>1])){
                            session()->flash('error', 'Error Occured while marking vendor login account as Active!');
                        }  
                    }
                    
                    if($status =='INACTIVE'){
                        if(!Admin::where('id',$data->user_login_core_id)->update(['status'=>0])){
                            session()->flash('error', 'Error Occured while marking vendor login account as Active!');
                        }  
                    }
                    
                    if ($data->save()) {
                        DB::commit();
                        session()->flash('success', "Vendor has been $status successfully!");
                    } else{
                        Log::error("Error Occured at vendor status $status update ");
                        session()->flash('error', 'Some Error Occured please try again !');
                    }
                
                
         }else{
             session()->flash('error', 'Error Occured!');
         }
         
         }catch(\Exception $e){
                session()->flash('error', "Error Occured".$e->getMessage());
                Log::error("Error Occured".$e->getMessage());
            }
            return redirect('admin/vendor');
    }
    
    
}
