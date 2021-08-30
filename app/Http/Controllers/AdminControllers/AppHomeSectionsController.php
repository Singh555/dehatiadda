<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\AdminControllers\SiteSettingController;
use App\Http\Controllers\Controller;
use App\Models\Core\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;

use App\Models\Eloquent\AppSection;
use App\Models\Eloquent\AppSectionData;

class AppHomeSectionsController extends Controller
{
    //

    public function __construct(Setting $setting)
    {
        $this->myVarsetting = new SiteSettingController($setting);
        $this->Setting = $setting;
    }

    #######################
    # Update Section Data
    #####################
    public function updateSection(Request $request) {
        $this->validate(request(), [
            'section_id' => 'bail|required',
            'image_id' => 'nullable|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        $section_title = $request->input('section_title');
        $section_id = $request->input('section_id');
        $oldImage = '';$uploadImage = '';
        try{
            
            if($request->hasFile('image_id')){
                $uploadImage = uploadImage($request->file('image_id'), 'appSection','VerticleSliderBg');
            }
            
            $section = AppSection::find($section_id);
            $section->title = $section_title;
            if(!empty($uploadImage)){
                $oldImage = $section->image;
            $section->image = $uploadImage;
            }
            $section->updated_by = auth()->user()->first_name;
            if($section->save()){
                session()->flash('success', " Section Title :: $section_title Updated. ");
                $unlink = public_path().'/'.$oldImage;
                    if(!empty($oldImage) && file_exists($unlink)){
                        unlink($unlink);
                    }
            }else{
                session()->flash('error', " Some Error Occured !! Please try again.");
            }
            
        } catch (\Exception $e){
            Log::error(__CLASS__."::".__FUNCTION__." Exception occured ".$e->getTraceAsString());
            $unlink = public_path().'/'.$uploadImage;
                    if(!empty($uploadImage) && file_exists($unlink)){
                        unlink($unlink);
                    }
            session()->flash('error', " Exception Occured !! ".$e->getMessage());
        }
        
        return redirect()->back();
    }
    
    ###########################
    # Delete Section Data common
    ############################
    public function deleteSectionData(Request $request) {
        $this->validate(request(), [
            'id' => 'bail|required',
        ]);
        $id = $request->input('id');
        try{
            $section = AppSectionData::find($id);
            $uploadImage = $section->image;
            $uploadVideo = $section->video;
            $section->updated_by = auth()->user()->first_name;
            if($section->delete()){
                $unlink = public_path().'/'.$uploadImage;
                $unlink2 = public_path().'/'.$uploadVideo;
                    if(!empty($uploadImage) && file_exists($unlink)){
                        unlink($unlink);
                    }
                    if(!empty($uploadVideo) && file_exists($unlink2)){
                        unlink($unlink2);
                    }
                session()->flash('success', " Section Data Deleted. ");
            }else{
                session()->flash('error', " Some Error Occured !! Please try again.");
            }
            
        } catch (\Exception $e){
            Log::error(__CLASS__."::".__FUNCTION__." Exception occured ".$e->getTraceAsString());
            session()->flash('error', " Exception Occured !! ".$e->getMessage());
        }
        
        return redirect()->back();
    }
    
     
    ###########################
    # view four box section
    ###########################
    public function fourBox(Request $request)
    {
        $title = "4 Box";
        $language_id = 1;
        $result = array();
        $message = array();
        $Section = '';$SectionData = array();$EditSectionData='';
         if($request->filled('position')){   
        $Section = AppSection::where('view_type','4_BOX')->where('position',$request->input('position'))->first();
        $SectionData = AppSectionData::where('section_id',$Section->id)->where('status','!=','DELETED')->orderBy('sort_order','desc')->get();
         }
        $categories = DB::table('categories')
            ->leftJoin('categories_description', 'categories_description.categories_id', '=', 'categories.categories_id')
            ->LeftJoin('categories_description as parent_description', function ($join) {
                $join->on('parent_description.categories_id', '=', 'categories.parent_id')
                    ->where(function ($query) {
                        $query->where('parent_description.language_id', '=', 1)->limit(1);
                    });
            })
            ->select('categories.categories_id as id', 'categories.categories_image as image', 'categories.created_at as date_added', 'categories.updated_at as last_modified', 'categories_description.categories_name as name', 'categories.categories_slug as slug', 'parent_description.categories_name as parent_name')
            ->where('categories_description.language_id', '=', $language_id)->get();

        $products_id = null;
        $product = DB::table('products')
            ->leftJoin('products_description', 'products_description.products_id', '=', 'products.products_id')
            ->leftJoin('manufacturers', 'manufacturers.manufacturers_id', '=', 'products.manufacturers_id')
            ->leftJoin('manufacturers_info', 'manufacturers.manufacturers_id', '=', 'manufacturers_info.manufacturers_id')
            ->LeftJoin('specials', function ($join) {
                $join->on('specials.products_id', '=', 'products.products_id')->where('status', '=', '1');
            })
            ->select('products.*', 'products_description.*', 'manufacturers.*', 'manufacturers_info.manufacturers_url', 'specials.specials_id', 'specials.products_id as special_products_id', 'specials.specials_new_products_price as specials_products_price', 'specials.specials_date_added as specials_date_added', 'specials.specials_last_modified as specials_last_modified', 'specials.expires_date')
            ->where('products_description.language_id', '=', $language_id);
        if ($products_id != null) {
            $product->where('products.products_id', '=', $products_id);
        } else {
            $product->orderBy('products.products_id', 'DESC');
        }
        $products = $product->get();

        

        $result['message'] = $message;
        $result['categories'] = $categories;
        $result['products'] = $products;
        $result['editSectionData'] = $EditSectionData;
        $result['section'] = $Section;
        $result['sectionData'] = $SectionData;
        $result['commonContent'] = $this->Setting->commonContent();

        return view("admin.AppHomeSections.fourBox", ["title"=>$title])->with('result', $result);
    }
    
    #################################
    # Insert Data in four box section
    ################################
    public function fourBoxInsert(Request $request) {
        $this->validate(request(), [
            'section_id' => 'bail|required',
            'title' => 'bail|required',
            'data_type' => 'bail|required',
            'sort_order' => 'bail|required',
            'status' => 'bail|required',
            'image_id' => 'bail|required|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'filter_value' => 'nullable|numeric', 
            'thresold_value' => 'nullable|numeric', 
        ]);
        $uploadImage = '';
        $title = $request->title;
        $type = $request->data_type;
        $sort_order = $request->sort_order;
        $status = $request->status;
        $urls = '';
        $filter_type = $request->filter_type;
        $filter_condition = $request->condition;
        $filter_value = $request->filter_value;
        $filter_thresold_value = $request->thresold_value;
        $section_id = $request->input('section_id');
        if ($type == 'CATEGORY' && $request->filled('categories_id')) {
            $urls = $request->categories_id;
        }
        else if ($type == 'PRODUCT' && $request->filled('products_id')) {
            $urls = $request->products_id;
        }else{
            session()->flash('error', 'Please Choose a data type');
                return redirect()->back();
        } 
            if (!empty($filter_type) && $type == 'CATEGORY') {
            if(empty($filter_condition)){
                session()->flash('error', 'Filter Condition is required');
                return redirect()->back();
            }
            if(empty($filter_value)){
                session()->flash('error', 'Filter Value is required');
                return redirect()->back();
            }
            if($filter_condition !="=" && empty($filter_thresold_value)){
                session()->flash('error', 'Filter thresold value is required');
                return redirect()->back();
            }
        } 
        try{
            if($request->hasFile('image_id')){
                $uploadImage = uploadImage($request->file('image_id'), 'appSectionData','fourBox');
            }
            $section = new AppSectionData;
            $section->section_id = $section_id;
            $section->title = $title;
            $section->data_type = $type;
            $section->urls = $urls;
            $section->filter_type = $filter_type;
            $section->condition = $filter_condition;
            $section->filter_value = $filter_value;
            $section->thresold_value = $filter_thresold_value;
            $section->image = $uploadImage;
            $section->sort_order = $sort_order;
            $section->status = $status;
            $section->created_by = auth()->user()->first_name;
            if($section->save()){
                session()->flash('success', " Section Data :: Saved. ");
            }else{
                session()->flash('error', " Some Error Occured !! Please try again.");
            }
            
        } catch (\Exception $e){
            Log::error(__CLASS__."::".__FUNCTION__." Exception occured ".$e->getTraceAsString());
            $unlink = public_path().'/'.$uploadImage;
                    if(!empty($uploadImage) && file_exists($unlink)){
                        unlink($unlink);
                    }
            session()->flash('error', " Exception Occured !! ".$e->getMessage());
        }
        
        return redirect()->back();
    }

    
    public function editfourBox($id)
    {
        
        $title = "4 Box Edit";
        $language_id = 1;
        $result = array();
        $message = array();
        $Section = '';$SectionData = array();
        $EditSectionData = AppSectionData::find($id);
         if(isset($EditSectionData->id)){   
        $Section = AppSection::where('view_type','4_BOX')->where('id',$EditSectionData->section_id)->first();
        $SectionData = AppSectionData::where('section_id',$EditSectionData->section_id)->where('status','!=','DELETED')->orderBy('sort_order','desc')->get();
        
         }else{
             return redirect()->to('admin/homeSections/fourBox');
         }
        $categories = DB::table('categories')
            ->leftJoin('categories_description', 'categories_description.categories_id', '=', 'categories.categories_id')
            ->LeftJoin('categories_description as parent_description', function ($join) {
                $join->on('parent_description.categories_id', '=', 'categories.parent_id')
                    ->where(function ($query) {
                        $query->where('parent_description.language_id', '=', 1)->limit(1);
                    });
            })
            ->select('categories.categories_id as id', 'categories.categories_image as image', 'categories.created_at as date_added', 'categories.updated_at as last_modified', 'categories_description.categories_name as name', 'categories.categories_slug as slug', 'parent_description.categories_name as parent_name')
            ->where('categories_description.language_id', '=', $language_id)->get();

        $products_id = null;
        $product = DB::table('products')
            ->leftJoin('products_description', 'products_description.products_id', '=', 'products.products_id')
            ->leftJoin('manufacturers', 'manufacturers.manufacturers_id', '=', 'products.manufacturers_id')
            ->leftJoin('manufacturers_info', 'manufacturers.manufacturers_id', '=', 'manufacturers_info.manufacturers_id')
            ->LeftJoin('specials', function ($join) {
                $join->on('specials.products_id', '=', 'products.products_id')->where('status', '=', '1');
            })
            ->select('products.*', 'products_description.*', 'manufacturers.*', 'manufacturers_info.manufacturers_url', 'specials.specials_id', 'specials.products_id as special_products_id', 'specials.specials_new_products_price as specials_products_price', 'specials.specials_date_added as specials_date_added', 'specials.specials_last_modified as specials_last_modified', 'specials.expires_date')
            ->where('products_description.language_id', '=', $language_id);
        if ($products_id != null) {
            $product->where('products.products_id', '=', $products_id);
        } else {
            $product->orderBy('products.products_id', 'DESC');
        }
        $products = $product->get();

        

        $result['message'] = $message;
        $result['categories'] = $categories;
        $result['products'] = $products;
        
        $result['section'] = $Section;
        $result['sectionData'] = $SectionData;
        $result['editSectionData'] = $EditSectionData;
        $result['commonContent'] = $this->Setting->commonContent();

        return view("admin.AppHomeSections.fourBox", ["title"=>$title])->with('result', $result);
    }
    
    #################################
    # Update Data in four box section
    ################################
    public function fourBoxUpdate(Request $request) {
        $this->validate(request(), [
            'id' => 'bail|required',
            'title' => 'bail|required',
            'data_type' => 'bail|required',
            'sort_order' => 'bail|required',
            'status' => 'bail|required',
            'image_id' => 'nullable|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'filter_value' => 'nullable|numeric', 
            'thresold_value' => 'nullable|numeric', 
        ]);
        $uploadImage = '';
        $title = $request->title;
        $type = $request->data_type;
        $sort_order = $request->sort_order;
        $status = $request->status;
        $urls = '';
        $filter_type = $request->filter_type;
        $filter_condition = $request->condition;
        $filter_value = $request->filter_value;
        $filter_thresold_value = $request->thresold_value;
        $id = $request->input('id');
        if ($type == 'CATEGORY' && $request->filled('categories_id')) {
            $urls = $request->categories_id;
            if(empty($filter_type)){
            $filter_type = '';
            $filter_condition = '';
            $filter_value = '';
            $filter_thresold_value = '';
            }
        } else if ($type == 'PRODUCT' && $request->filled('products_id')) {
            $urls = $request->products_id;
            $filter_type = '';
            $filter_condition = '';
            $filter_value = '';
            $filter_thresold_value = '';
        } 
        if (!empty($filter_type) && $type == 'CATEGORY') {
            if(empty($filter_condition)){
                session()->flash('error', 'Filter Condition is required');
                return redirect()->back();
            }
            if(empty($filter_value)){
                session()->flash('error', 'Filter Value is required');
                return redirect()->back();
            }
            if($filter_condition !="=" && empty($filter_thresold_value)){
                session()->flash('error', 'Filter thresold value is required');
                return redirect()->back();
            }
        }
        $oldImage = '';
        try{
            if($request->hasFile('image_id')){
                $uploadImage = uploadImage($request->file('image_id'), 'appSectionData','fourBox');
            }
            $section = AppSectionData::find($id);
            $section->title = $title;
            $section->data_type = $type;
            $section->urls = $urls;
            $section->filter_type = $filter_type;
            $section->condition = $filter_condition;
            $section->filter_value = $filter_value;
            $section->thresold_value = $filter_thresold_value;
            if(!empty($uploadImage)){
                $oldImage = $section->image;
            $section->image = $uploadImage;
            }
            $section->sort_order = $sort_order;
            $section->status = $status;
            $section->created_by = auth()->user()->first_name;
            if($section->save()){
                session()->flash('success', " Section Data :: Updated. ");
                $unlink = public_path().'/'.$oldImage;
                    if(!empty($oldImage) && file_exists($unlink)){
                        unlink($unlink);
                    }
            }else{
                session()->flash('error', " Some Error Occured !! Please try again.");
            }
            
        } catch (\Exception $e){
            Log::error(__CLASS__."::".__FUNCTION__." Exception occured ".$e->getTraceAsString());
            $unlink = public_path().'/'.$uploadImage;
                    if(!empty($uploadImage) && file_exists($unlink)){
                        unlink($unlink);
                    }
            session()->flash('error', " Exception Occured !! ".$e->getMessage());
        }
        
        return redirect()->back();
    }
    
    #######################
    # twoBox
    #######################
    public function twoBox(Request $request) 
    {
         $title = "2 Box";
        $language_id = 1;
        $result = array();
        $message = array();
        $Section = '';$SectionData = array();$EditSectionData='';
          
        $Section = AppSection::where('view_type','2_BOX')->first();
        $SectionData = AppSectionData::where('section_id',$Section->id)->where('status','!=','DELETED')->orderBy('sort_order','desc')->get();
         
        $categories = DB::table('categories')
            ->leftJoin('categories_description', 'categories_description.categories_id', '=', 'categories.categories_id')
            ->LeftJoin('categories_description as parent_description', function ($join) {
                $join->on('parent_description.categories_id', '=', 'categories.parent_id')
                    ->where(function ($query) {
                        $query->where('parent_description.language_id', '=', 1)->limit(1);
                    });
            })
            ->select('categories.categories_id as id', 'categories.categories_image as image', 'categories.created_at as date_added', 'categories.updated_at as last_modified', 'categories_description.categories_name as name', 'categories.categories_slug as slug', 'parent_description.categories_name as parent_name')
            ->where('categories_description.language_id', '=', $language_id)->get();

        $products_id = null;
        $product = DB::table('products')
            ->leftJoin('products_description', 'products_description.products_id', '=', 'products.products_id')
            ->leftJoin('manufacturers', 'manufacturers.manufacturers_id', '=', 'products.manufacturers_id')
            ->leftJoin('manufacturers_info', 'manufacturers.manufacturers_id', '=', 'manufacturers_info.manufacturers_id')
            ->LeftJoin('specials', function ($join) {
                $join->on('specials.products_id', '=', 'products.products_id')->where('status', '=', '1');
            })
            ->select('products.*', 'products_description.*', 'manufacturers.*', 'manufacturers_info.manufacturers_url', 'specials.specials_id', 'specials.products_id as special_products_id', 'specials.specials_new_products_price as specials_products_price', 'specials.specials_date_added as specials_date_added', 'specials.specials_last_modified as specials_last_modified', 'specials.expires_date')
            ->where('products_description.language_id', '=', $language_id);
        if ($products_id != null) {
            $product->where('products.products_id', '=', $products_id);
        } else {
            $product->orderBy('products.products_id', 'DESC');
        }
        $products = $product->get();

        

        $result['message'] = $message;
        $result['categories'] = $categories;
        $result['products'] = $products;
        $result['editSectionData'] = $EditSectionData;
        $result['section'] = $Section;
        $result['sectionData'] = $SectionData;
        $result['commonContent'] = $this->Setting->commonContent();

        return view("admin.AppHomeSections.twoBox", ["title"=>$title])->with('result', $result);
    }
    
    
    #################################
    # Insert Data in two box section
    ################################
    public function twoBoxInsert(Request $request) {
        $this->validate(request(), [
            'section_id' => 'bail|required',
            'title' => 'bail|required',
            'data_type' => 'bail|required',
            'sort_order' => 'bail|required',
            'status' => 'bail|required',
            'image_id' => 'bail|required|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'filter_value' => 'nullable|numeric', 
            'thresold_value' => 'nullable|numeric', 
        ]);
        $uploadImage = '';
        $title = $request->title;
        $type = $request->data_type;
        $sort_order = $request->sort_order;
        $status = $request->status;
        $urls = '';
        $filter_type = $request->filter_type;
        $filter_condition = $request->condition;
        $filter_value = $request->filter_value;
        $filter_thresold_value = $request->thresold_value;
        $section_id = $request->input('section_id');
        if ($type == 'CATEGORY' && $request->filled('categories_id')) {
            $urls = $request->categories_id;
        }
        else if ($type == 'PRODUCT' && $request->filled('products_id')) {
            $urls = $request->products_id;
        }else{
            session()->flash('error', 'Please Choose a data type');
                return redirect()->back();
        } 
            if (!empty($filter_type) && $type == 'CATEGORY') {
            if(empty($filter_condition)){
                session()->flash('error', 'Filter Condition is required');
                return redirect()->back();
            }
            if(empty($filter_value)){
                session()->flash('error', 'Filter Value is required');
                return redirect()->back();
            }
            if($filter_condition !="=" && empty($filter_thresold_value)){
                session()->flash('error', 'Filter thresold value is required');
                return redirect()->back();
            }
        } 
        try{
            if($request->hasFile('image_id')){
                $uploadImage = uploadImage($request->file('image_id'), 'appSectionData','twoBox');
            }
            $section = new AppSectionData;
            $section->section_id = $section_id;
            $section->title = $title;
            $section->data_type = $type;
            $section->urls = $urls;
            $section->filter_type = $filter_type;
            $section->condition = $filter_condition;
            $section->filter_value = $filter_value;
            $section->thresold_value = $filter_thresold_value;
            $section->image = $uploadImage;
            $section->sort_order = $sort_order;
            $section->status = $status;
            $section->created_by = auth()->user()->first_name;
            if($section->save()){
                session()->flash('success', " Section Data :: Saved. ");
            }else{
                session()->flash('error', " Some Error Occured !! Please try again.");
            }
            
        } catch (\Exception $e){
            Log::error(__CLASS__."::".__FUNCTION__." Exception occured ".$e->getTraceAsString());
            $unlink = public_path().'/'.$uploadImage;
                    if(!empty($uploadImage) && file_exists($unlink)){
                        unlink($unlink);
                    }
            session()->flash('error', " Exception Occured !! ".$e->getMessage());
        }
        
        return redirect()->back();
    }
    
    ###########################
    # Edit two box
    ##########################
    public function editTwoBox($id)
    {
        
        $title = "2 Box Edit";
        $language_id = 1;
        $result = array();
        $message = array();
        $Section = '';$SectionData = array();
        $EditSectionData = AppSectionData::find($id);
         if(isset($EditSectionData->id)){   
        $Section = AppSection::where('id',$EditSectionData->section_id)->first();
        $SectionData = AppSectionData::where('section_id',$EditSectionData->section_id)->where('status','!=','DELETED')->orderBy('sort_order','desc')->get();
        
         }else{
             return redirect()->to('admin/homeSections/twoBox');
         }
        $categories = DB::table('categories')
            ->leftJoin('categories_description', 'categories_description.categories_id', '=', 'categories.categories_id')
            ->LeftJoin('categories_description as parent_description', function ($join) {
                $join->on('parent_description.categories_id', '=', 'categories.parent_id')
                    ->where(function ($query) {
                        $query->where('parent_description.language_id', '=', 1)->limit(1);
                    });
            })
            ->select('categories.categories_id as id', 'categories.categories_image as image', 'categories.created_at as date_added', 'categories.updated_at as last_modified', 'categories_description.categories_name as name', 'categories.categories_slug as slug', 'parent_description.categories_name as parent_name')
            ->where('categories_description.language_id', '=', $language_id)->get();

        $products_id = null;
        $product = DB::table('products')
            ->leftJoin('products_description', 'products_description.products_id', '=', 'products.products_id')
            ->leftJoin('manufacturers', 'manufacturers.manufacturers_id', '=', 'products.manufacturers_id')
            ->leftJoin('manufacturers_info', 'manufacturers.manufacturers_id', '=', 'manufacturers_info.manufacturers_id')
            ->LeftJoin('specials', function ($join) {
                $join->on('specials.products_id', '=', 'products.products_id')->where('status', '=', '1');
            })
            ->select('products.*', 'products_description.*', 'manufacturers.*', 'manufacturers_info.manufacturers_url', 'specials.specials_id', 'specials.products_id as special_products_id', 'specials.specials_new_products_price as specials_products_price', 'specials.specials_date_added as specials_date_added', 'specials.specials_last_modified as specials_last_modified', 'specials.expires_date')
            ->where('products_description.language_id', '=', $language_id);
        if ($products_id != null) {
            $product->where('products.products_id', '=', $products_id);
        } else {
            $product->orderBy('products.products_id', 'DESC');
        }
        $products = $product->get();

        

        $result['message'] = $message;
        $result['categories'] = $categories;
        $result['products'] = $products;
        
        $result['section'] = $Section;
        $result['sectionData'] = $SectionData;
        $result['editSectionData'] = $EditSectionData;
        $result['commonContent'] = $this->Setting->commonContent();

        return view("admin.AppHomeSections.twoBox", ["title"=>$title])->with('result', $result);
    }
    
    #################################
    # Update Data in two box section
    ################################
    public function twoBoxUpdate(Request $request) {
        $this->validate(request(), [
            'id' => 'bail|required',
            'title' => 'bail|required',
            'data_type' => 'bail|required',
            'sort_order' => 'bail|required',
            'status' => 'bail|required',
            'image_id' => 'nullable|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'filter_value' => 'nullable|numeric', 
            'thresold_value' => 'nullable|numeric', 
        ]);
        $uploadImage = '';
        $title = $request->title;
        $type = $request->data_type;
        $sort_order = $request->sort_order;
        $status = $request->status;
        $urls = '';
        $filter_type = $request->filter_type;
        $filter_condition = $request->condition;
        $filter_value = $request->filter_value;
        $filter_thresold_value = $request->thresold_value;
        $id = $request->input('id');
        if ($type == 'CATEGORY' && $request->filled('categories_id')) {
            $urls = $request->categories_id;
            if(empty($filter_type)){
            $filter_type = '';
            $filter_condition = '';
            $filter_value = '';
            $filter_thresold_value = '';
            }
        } else if ($type == 'PRODUCT' && $request->filled('products_id')) {
            $urls = $request->products_id;
            $filter_type = '';
            $filter_condition = '';
            $filter_value = '';
            $filter_thresold_value = '';
        } 
        if (!empty($filter_type) && $type == 'CATEGORY') {
            if(empty($filter_condition)){
                session()->flash('error', 'Filter Condition is required');
                return redirect()->back();
            }
            if(empty($filter_value)){
                session()->flash('error', 'Filter Value is required');
                return redirect()->back();
            }
            if($filter_condition !="=" && empty($filter_thresold_value)){
                session()->flash('error', 'Filter thresold value is required');
                return redirect()->back();
            }
        }
        $oldImage = '';
        try{
            if($request->hasFile('image_id')){
                $uploadImage = uploadImage($request->file('image_id'), 'appSectionData','twoBox');
            }
            $section = AppSectionData::find($id);
            $section->title = $title;
            $section->data_type = $type;
            $section->urls = $urls;
            $section->filter_type = $filter_type;
            $section->condition = $filter_condition;
            $section->filter_value = $filter_value;
            $section->thresold_value = $filter_thresold_value;
            if(!empty($uploadImage)){
                $oldImage = $section->image;
            $section->image = $uploadImage;
            }
            $section->sort_order = $sort_order;
            $section->status = $status;
            $section->created_by = auth()->user()->first_name;
            if($section->save()){
                session()->flash('success', " Section Data :: Updated. ");
                $unlink = public_path().'/'.$oldImage;
                    if(!empty($oldImage) && file_exists($unlink)){
                        unlink($unlink);
                    }
            }else{
                session()->flash('error', " Some Error Occured !! Please try again.");
            }
            
        } catch (\Exception $e){
            Log::error(__CLASS__."::".__FUNCTION__." Exception occured ".$e->getTraceAsString());
            $unlink = public_path().'/'.$uploadImage;
                    if(!empty($uploadImage) && file_exists($unlink)){
                        unlink($unlink);
                    }
            session()->flash('error', " Exception Occured !! ".$e->getMessage());
        }
        
        return redirect()->back();
    }
    
    #######################################################################################################################
    #######################
    # Banner Plain large
    #######################
    public function bannerPlainLarge(Request $request) 
    {
         $title = "Banner Plain Large";
        $language_id = 1;
        $result = array();
        $message = array();
        $Section = '';$SectionData = array();$EditSectionData='';
          
        $Section = AppSection::where('view_type','BANNER_PLAIN_LARGE')->first();
        $SectionData = AppSectionData::where('section_id',$Section->id)->where('status','!=','DELETED')->orderBy('sort_order','desc')->get();
         
        $categories = DB::table('categories')
            ->leftJoin('categories_description', 'categories_description.categories_id', '=', 'categories.categories_id')
            ->LeftJoin('categories_description as parent_description', function ($join) {
                $join->on('parent_description.categories_id', '=', 'categories.parent_id')
                    ->where(function ($query) {
                        $query->where('parent_description.language_id', '=', 1)->limit(1);
                    });
            })
            ->select('categories.categories_id as id', 'categories.categories_image as image', 'categories.created_at as date_added', 'categories.updated_at as last_modified', 'categories_description.categories_name as name', 'categories.categories_slug as slug', 'parent_description.categories_name as parent_name')
            ->where('categories_description.language_id', '=', $language_id)->get();

        $products_id = null;
        $product = DB::table('products')
            ->leftJoin('products_description', 'products_description.products_id', '=', 'products.products_id')
            ->leftJoin('manufacturers', 'manufacturers.manufacturers_id', '=', 'products.manufacturers_id')
            ->leftJoin('manufacturers_info', 'manufacturers.manufacturers_id', '=', 'manufacturers_info.manufacturers_id')
            ->LeftJoin('specials', function ($join) {
                $join->on('specials.products_id', '=', 'products.products_id')->where('status', '=', '1');
            })
            ->select('products.*', 'products_description.*', 'manufacturers.*', 'manufacturers_info.manufacturers_url', 'specials.specials_id', 'specials.products_id as special_products_id', 'specials.specials_new_products_price as specials_products_price', 'specials.specials_date_added as specials_date_added', 'specials.specials_last_modified as specials_last_modified', 'specials.expires_date')
            ->where('products_description.language_id', '=', $language_id);
        if ($products_id != null) {
            $product->where('products.products_id', '=', $products_id);
        } else {
            $product->orderBy('products.products_id', 'DESC');
        }
        $products = $product->get();

        

        $result['message'] = $message;
        $result['categories'] = $categories;
        $result['products'] = $products;
        $result['editSectionData'] = $EditSectionData;
        $result['section'] = $Section;
        $result['sectionData'] = $SectionData;
        $result['commonContent'] = $this->Setting->commonContent();

        return view("admin.AppHomeSections.imagePlainLarge", ["title"=>$title])->with('result', $result);
    }
    
    
    #################################
    # Insert Data in bannerPlainLarge section
    ################################
    public function bannerPlainLargeInsert(Request $request) {
        $this->validate(request(), [
            'section_id' => 'bail|required',
            'title' => 'bail|required',
            'data_type' => 'bail|required',
            'sort_order' => 'bail|required',
            'status' => 'bail|required',
            'image_id' => 'bail|required|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'filter_value' => 'nullable|numeric', 
            'thresold_value' => 'nullable|numeric', 
        ]);
        $uploadImage = '';
        $title = $request->title;
        $type = $request->data_type;
        $sort_order = $request->sort_order;
        $status = $request->status;
        $urls = '';
        $filter_type = $request->filter_type;
        $filter_condition = $request->condition;
        $filter_value = $request->filter_value;
        $filter_thresold_value = $request->thresold_value;
        $section_id = $request->input('section_id');
        if ($type == 'CATEGORY' && $request->filled('categories_id')) {
            $urls = $request->categories_id;
        }
        else if ($type == 'PRODUCT' && $request->filled('products_id')) {
            $urls = $request->products_id;
        }else{
            session()->flash('error', 'Please Choose a data type');
                return redirect()->back();
        } 
            if (!empty($filter_type) && $type == 'CATEGORY') {
            if(empty($filter_condition)){
                session()->flash('error', 'Filter Condition is required');
                return redirect()->back();
            }
            if(empty($filter_value)){
                session()->flash('error', 'Filter Value is required');
                return redirect()->back();
            }
            if($filter_condition !="=" && empty($filter_thresold_value)){
                session()->flash('error', 'Filter thresold value is required');
                return redirect()->back();
            }
        } 
        try{
            if($request->hasFile('image_id')){
                $uploadImage = uploadImage($request->file('image_id'), 'appSectionData','bannerPlaineLarge');
            }
            $section = new AppSectionData;
            $section->section_id = $section_id;
            $section->title = $title;
            $section->data_type = $type;
            $section->urls = $urls;
            $section->filter_type = $filter_type;
            $section->condition = $filter_condition;
            $section->filter_value = $filter_value;
            $section->thresold_value = $filter_thresold_value;
            $section->image = $uploadImage;
            $section->sort_order = $sort_order;
            $section->status = $status;
            $section->created_by = auth()->user()->first_name;
            if($section->save()){
                session()->flash('success', " Section Data :: Saved. ");
            }else{
                session()->flash('error', " Some Error Occured !! Please try again.");
            }
            
        } catch (\Exception $e){
            Log::error(__CLASS__."::".__FUNCTION__." Exception occured ".$e->getTraceAsString());
            $unlink = public_path().'/'.$uploadImage;
                    if(!empty($uploadImage) && file_exists($unlink)){
                        unlink($unlink);
                    }
            session()->flash('error', " Exception Occured !! ".$e->getMessage());
        }
        
        return redirect()->back();
    }
    
    ###########################
    # Edit bannerPlainLarge
    ##########################
    public function editBannerPlainLarge($id)
    {
        
        $title = "Banner Plain Large Edit";
        $language_id = 1;
        $result = array();
        $message = array();
        $Section = '';$SectionData = array();
        $EditSectionData = AppSectionData::find($id);
         if(isset($EditSectionData->id)){   
        $Section = AppSection::where('id',$EditSectionData->section_id)->first();
        $SectionData = AppSectionData::where('section_id',$EditSectionData->section_id)->where('status','!=','DELETED')->orderBy('sort_order','desc')->get();
        
         }else{
             return redirect()->to('admin/homeSections/bannerPlainLarge');
         }
        $categories = DB::table('categories')
            ->leftJoin('categories_description', 'categories_description.categories_id', '=', 'categories.categories_id')
            ->LeftJoin('categories_description as parent_description', function ($join) {
                $join->on('parent_description.categories_id', '=', 'categories.parent_id')
                    ->where(function ($query) {
                        $query->where('parent_description.language_id', '=', 1)->limit(1);
                    });
            })
            ->select('categories.categories_id as id', 'categories.categories_image as image', 'categories.created_at as date_added', 'categories.updated_at as last_modified', 'categories_description.categories_name as name', 'categories.categories_slug as slug', 'parent_description.categories_name as parent_name')
            ->where('categories_description.language_id', '=', $language_id)->get();

        $products_id = null;
        $product = DB::table('products')
            ->leftJoin('products_description', 'products_description.products_id', '=', 'products.products_id')
            ->leftJoin('manufacturers', 'manufacturers.manufacturers_id', '=', 'products.manufacturers_id')
            ->leftJoin('manufacturers_info', 'manufacturers.manufacturers_id', '=', 'manufacturers_info.manufacturers_id')
            ->LeftJoin('specials', function ($join) {
                $join->on('specials.products_id', '=', 'products.products_id')->where('status', '=', '1');
            })
            ->select('products.*', 'products_description.*', 'manufacturers.*', 'manufacturers_info.manufacturers_url', 'specials.specials_id', 'specials.products_id as special_products_id', 'specials.specials_new_products_price as specials_products_price', 'specials.specials_date_added as specials_date_added', 'specials.specials_last_modified as specials_last_modified', 'specials.expires_date')
            ->where('products_description.language_id', '=', $language_id);
        if ($products_id != null) {
            $product->where('products.products_id', '=', $products_id);
        } else {
            $product->orderBy('products.products_id', 'DESC');
        }
        $products = $product->get();

        

        $result['message'] = $message;
        $result['categories'] = $categories;
        $result['products'] = $products;
        
        $result['section'] = $Section;
        $result['sectionData'] = $SectionData;
        $result['editSectionData'] = $EditSectionData;
        $result['commonContent'] = $this->Setting->commonContent();

        return view("admin.AppHomeSections.imagePlainLarge", ["title"=>$title])->with('result', $result);
    }
    
    #################################
    # Update Data in bannerPlainLarge section
    ################################
    public function bannerPlainLargeUpdate(Request $request) {
        $this->validate(request(), [
            'id' => 'bail|required',
            'title' => 'bail|required',
            'data_type' => 'bail|required',
            'sort_order' => 'bail|required',
            'status' => 'bail|required',
            'image_id' => 'nullable|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'filter_value' => 'nullable|numeric', 
            'thresold_value' => 'nullable|numeric', 
        ]);
        $uploadImage = '';
        $title = $request->title;
        $type = $request->data_type;
        $sort_order = $request->sort_order;
        $status = $request->status;
        $urls = '';
        $filter_type = $request->filter_type;
        $filter_condition = $request->condition;
        $filter_value = $request->filter_value;
        $filter_thresold_value = $request->thresold_value;
        $id = $request->input('id');
        if ($type == 'CATEGORY' && $request->filled('categories_id')) {
            $urls = $request->categories_id;
            if(empty($filter_type)){
            $filter_type = '';
            $filter_condition = '';
            $filter_value = '';
            $filter_thresold_value = '';
            }
        } else if ($type == 'PRODUCT' && $request->filled('products_id')) {
            $urls = $request->products_id;
            $filter_type = '';
            $filter_condition = '';
            $filter_value = '';
            $filter_thresold_value = '';
        } 
        if (!empty($filter_type) && $type == 'CATEGORY') {
            if(empty($filter_condition)){
                session()->flash('error', 'Filter Condition is required');
                return redirect()->back();
            }
            if(empty($filter_value)){
                session()->flash('error', 'Filter Value is required');
                return redirect()->back();
            }
            if($filter_condition !="=" && empty($filter_thresold_value)){
                session()->flash('error', 'Filter thresold value is required');
                return redirect()->back();
            }
        }
        $oldImage = '';
        try{
            if($request->hasFile('image_id')){
                $uploadImage = uploadImage($request->file('image_id'), 'appSectionData','bannerPlainLarge');
            }
            $section = AppSectionData::find($id);
            $section->title = $title;
            $section->data_type = $type;
            $section->urls = $urls;
            $section->filter_type = $filter_type;
            $section->condition = $filter_condition;
            $section->filter_value = $filter_value;
            $section->thresold_value = $filter_thresold_value;
            if(!empty($uploadImage)){
                $oldImage = $section->image;
            $section->image = $uploadImage;
            }
            $section->sort_order = $sort_order;
            $section->status = $status;
            $section->created_by = auth()->user()->first_name;
            if($section->save()){
                session()->flash('success', " Section Data :: Updated. ");
                $unlink = public_path().'/'.$oldImage;
                    if(!empty($oldImage) && file_exists($unlink)){
                        unlink($unlink);
                    }
            }else{
                session()->flash('error', " Some Error Occured !! Please try again.");
            }
            
        } catch (\Exception $e){
            Log::error(__CLASS__."::".__FUNCTION__." Exception occured ".$e->getTraceAsString());
            $unlink = public_path().'/'.$uploadImage;
                    if(!empty($uploadImage) && file_exists($unlink)){
                        unlink($unlink);
                    }
            session()->flash('error', " Exception Occured !! ".$e->getMessage());
        }
        
        return redirect()->back();
    }
    
    ####################################################################################################################
    
    #######################
    # Banner Plain Thin
    #######################
    public function bannerPlainThin(Request $request) 
    {
         $title = "Banner Plain Thin";
        $language_id = 1;
        $result = array();
        $message = array();
        $Section = '';$SectionData = array();$EditSectionData='';
          
        $Section = AppSection::where('view_type','BANNER_PLAIN_THIN')->first();
        $SectionData = AppSectionData::where('section_id',$Section->id)->where('status','!=','DELETED')->orderBy('sort_order','desc')->get();
         
        $categories = DB::table('categories')
            ->leftJoin('categories_description', 'categories_description.categories_id', '=', 'categories.categories_id')
            ->LeftJoin('categories_description as parent_description', function ($join) {
                $join->on('parent_description.categories_id', '=', 'categories.parent_id')
                    ->where(function ($query) {
                        $query->where('parent_description.language_id', '=', 1)->limit(1);
                    });
            })
            ->select('categories.categories_id as id', 'categories.categories_image as image', 'categories.created_at as date_added', 'categories.updated_at as last_modified', 'categories_description.categories_name as name', 'categories.categories_slug as slug', 'parent_description.categories_name as parent_name')
            ->where('categories_description.language_id', '=', $language_id)->get();

        $products_id = null;
        $product = DB::table('products')
            ->leftJoin('products_description', 'products_description.products_id', '=', 'products.products_id')
            ->leftJoin('manufacturers', 'manufacturers.manufacturers_id', '=', 'products.manufacturers_id')
            ->leftJoin('manufacturers_info', 'manufacturers.manufacturers_id', '=', 'manufacturers_info.manufacturers_id')
            ->LeftJoin('specials', function ($join) {
                $join->on('specials.products_id', '=', 'products.products_id')->where('status', '=', '1');
            })
            ->select('products.*', 'products_description.*', 'manufacturers.*', 'manufacturers_info.manufacturers_url', 'specials.specials_id', 'specials.products_id as special_products_id', 'specials.specials_new_products_price as specials_products_price', 'specials.specials_date_added as specials_date_added', 'specials.specials_last_modified as specials_last_modified', 'specials.expires_date')
            ->where('products_description.language_id', '=', $language_id);
        if ($products_id != null) {
            $product->where('products.products_id', '=', $products_id);
        } else {
            $product->orderBy('products.products_id', 'DESC');
        }
        $products = $product->get();

        

        $result['message'] = $message;
        $result['categories'] = $categories;
        $result['products'] = $products;
        $result['editSectionData'] = $EditSectionData;
        $result['section'] = $Section;
        $result['sectionData'] = $SectionData;
        $result['commonContent'] = $this->Setting->commonContent();

        return view("admin.AppHomeSections.bannerPlainThin", ["title"=>$title])->with('result', $result);
    }
    
    
    #################################
    # Insert Data in bannerPlain Thin section
    ################################
    public function bannerPlainThinInsert(Request $request) {
        $this->validate(request(), [
            'section_id' => 'bail|required',
            'title' => 'bail|required',
            'data_type' => 'bail|required',
            'sort_order' => 'bail|required',
            'status' => 'bail|required',
            'image_id' => 'bail|required|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'filter_value' => 'nullable|numeric', 
            'thresold_value' => 'nullable|numeric', 
        ]);
        $uploadImage = '';
        $title = $request->title;
        $type = $request->data_type;
        $sort_order = $request->sort_order;
        $status = $request->status;
        $urls = '';
        $filter_type = $request->filter_type;
        $filter_condition = $request->condition;
        $filter_value = $request->filter_value;
        $filter_thresold_value = $request->thresold_value;
        $section_id = $request->input('section_id');
        if ($type == 'CATEGORY' && $request->filled('categories_id')) {
            $urls = $request->categories_id;
        }
        else if ($type == 'PRODUCT' && $request->filled('products_id')) {
            $urls = $request->products_id;
        }else{
            session()->flash('error', 'Please Choose a data type');
                return redirect()->back();
        } 
            if (!empty($filter_type) && $type == 'CATEGORY') {
            if(empty($filter_condition)){
                session()->flash('error', 'Filter Condition is required');
                return redirect()->back();
            }
            if(empty($filter_value)){
                session()->flash('error', 'Filter Value is required');
                return redirect()->back();
            }
            if($filter_condition !="=" && empty($filter_thresold_value)){
                session()->flash('error', 'Filter thresold value is required');
                return redirect()->back();
            }
        } 
        try{
            if($request->hasFile('image_id')){
                $uploadImage = uploadImage($request->file('image_id'), 'appSectionData','bannerPlaineThin');
            }
            $section = new AppSectionData;
            $section->section_id = $section_id;
            $section->title = $title;
            $section->data_type = $type;
            $section->urls = $urls;
            $section->filter_type = $filter_type;
            $section->condition = $filter_condition;
            $section->filter_value = $filter_value;
            $section->thresold_value = $filter_thresold_value;
            $section->image = $uploadImage;
            $section->sort_order = $sort_order;
            $section->status = $status;
            $section->created_by = auth()->user()->first_name;
            if($section->save()){
                session()->flash('success', " Section Data :: Saved. ");
            }else{
                session()->flash('error', " Some Error Occured !! Please try again.");
            }
            
        } catch (\Exception $e){
            Log::error(__CLASS__."::".__FUNCTION__." Exception occured ".$e->getTraceAsString());
            $unlink = public_path().'/'.$uploadImage;
                    if(!empty($uploadImage) && file_exists($unlink)){
                        unlink($unlink);
                    }
            session()->flash('error', " Exception Occured !! ".$e->getMessage());
        }
        
        return redirect()->back();
    }
    
    ###########################
    # Edit bannerPlain Thin
    ##########################
    public function editBannerPlainThin($id)
    {
        
        $title = "Banner Plain Thin Edit";
        $language_id = 1;
        $result = array();
        $message = array();
        $Section = '';$SectionData = array();
        $EditSectionData = AppSectionData::find($id);
         if(isset($EditSectionData->id)){   
        $Section = AppSection::where('id',$EditSectionData->section_id)->first();
        $SectionData = AppSectionData::where('section_id',$EditSectionData->section_id)->where('status','!=','DELETED')->orderBy('sort_order','desc')->get();
        
         }else{
             return redirect()->to('admin/homeSections/bannerPlainThin');
         }
        $categories = DB::table('categories')
            ->leftJoin('categories_description', 'categories_description.categories_id', '=', 'categories.categories_id')
            ->LeftJoin('categories_description as parent_description', function ($join) {
                $join->on('parent_description.categories_id', '=', 'categories.parent_id')
                    ->where(function ($query) {
                        $query->where('parent_description.language_id', '=', 1)->limit(1);
                    });
            })
            ->select('categories.categories_id as id', 'categories.categories_image as image', 'categories.created_at as date_added', 'categories.updated_at as last_modified', 'categories_description.categories_name as name', 'categories.categories_slug as slug', 'parent_description.categories_name as parent_name')
            ->where('categories_description.language_id', '=', $language_id)->get();

        $products_id = null;
        $product = DB::table('products')
            ->leftJoin('products_description', 'products_description.products_id', '=', 'products.products_id')
            ->leftJoin('manufacturers', 'manufacturers.manufacturers_id', '=', 'products.manufacturers_id')
            ->leftJoin('manufacturers_info', 'manufacturers.manufacturers_id', '=', 'manufacturers_info.manufacturers_id')
            ->LeftJoin('specials', function ($join) {
                $join->on('specials.products_id', '=', 'products.products_id')->where('status', '=', '1');
            })
            ->select('products.*', 'products_description.*', 'manufacturers.*', 'manufacturers_info.manufacturers_url', 'specials.specials_id', 'specials.products_id as special_products_id', 'specials.specials_new_products_price as specials_products_price', 'specials.specials_date_added as specials_date_added', 'specials.specials_last_modified as specials_last_modified', 'specials.expires_date')
            ->where('products_description.language_id', '=', $language_id);
        if ($products_id != null) {
            $product->where('products.products_id', '=', $products_id);
        } else {
            $product->orderBy('products.products_id', 'DESC');
        }
        $products = $product->get();

        

        $result['message'] = $message;
        $result['categories'] = $categories;
        $result['products'] = $products;
        
        $result['section'] = $Section;
        $result['sectionData'] = $SectionData;
        $result['editSectionData'] = $EditSectionData;
        $result['commonContent'] = $this->Setting->commonContent();

        return view("admin.AppHomeSections.bannerPlainThin", ["title"=>$title])->with('result', $result);
    }
    
    #################################
    # Update Data in bannerPlain Thin section
    ################################
    public function bannerPlainThinUpdate(Request $request) {
        $this->validate(request(), [
            'id' => 'bail|required',
            'title' => 'bail|required',
            'data_type' => 'bail|required',
            'sort_order' => 'bail|required',
            'status' => 'bail|required',
            'image_id' => 'nullable|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'filter_value' => 'nullable|numeric', 
            'thresold_value' => 'nullable|numeric', 
        ]);
        $uploadImage = '';
        $title = $request->title;
        $type = $request->data_type;
        $sort_order = $request->sort_order;
        $status = $request->status;
        $urls = '';
        $filter_type = $request->filter_type;
        $filter_condition = $request->condition;
        $filter_value = $request->filter_value;
        $filter_thresold_value = $request->thresold_value;
        $id = $request->input('id');
        if ($type == 'CATEGORY' && $request->filled('categories_id')) {
            $urls = $request->categories_id;
            if(empty($filter_type)){
            $filter_type = '';
            $filter_condition = '';
            $filter_value = '';
            $filter_thresold_value = '';
            }
        } else if ($type == 'PRODUCT' && $request->filled('products_id')) {
            $urls = $request->products_id;
            $filter_type = '';
            $filter_condition = '';
            $filter_value = '';
            $filter_thresold_value = '';
        } 
        if (!empty($filter_type) && $type == 'CATEGORY') {
            if(empty($filter_condition)){
                session()->flash('error', 'Filter Condition is required');
                return redirect()->back();
            }
            if(empty($filter_value)){
                session()->flash('error', 'Filter Value is required');
                return redirect()->back();
            }
            if($filter_condition !="=" && empty($filter_thresold_value)){
                session()->flash('error', 'Filter thresold value is required');
                return redirect()->back();
            }
        }
        $oldImage = '';
        try{
            if($request->hasFile('image_id')){
                $uploadImage = uploadImage($request->file('image_id'), 'appSectionData','bannerPlainThin');
            }
            $section = AppSectionData::find($id);
            $section->title = $title;
            $section->data_type = $type;
            $section->urls = $urls;
            $section->filter_type = $filter_type;
            $section->condition = $filter_condition;
            $section->filter_value = $filter_value;
            $section->thresold_value = $filter_thresold_value;
            if(!empty($uploadImage)){
                $oldImage = $section->image;
            $section->image = $uploadImage;
            }
            $section->sort_order = $sort_order;
            $section->status = $status;
            $section->created_by = auth()->user()->first_name;
            if($section->save()){
                session()->flash('success', " Section Data :: Updated. ");
                $unlink = public_path().'/'.$oldImage;
                    if(!empty($oldImage) && file_exists($unlink)){
                        unlink($unlink);
                    }
            }else{
                session()->flash('error', " Some Error Occured !! Please try again.");
            }
            
        } catch (\Exception $e){
            Log::error(__CLASS__."::".__FUNCTION__." Exception occured ".$e->getTraceAsString());
            $unlink = public_path().'/'.$uploadImage;
                    if(!empty($uploadImage) && file_exists($unlink)){
                        unlink($unlink);
                    }
            session()->flash('error', " Exception Occured !! ".$e->getMessage());
        }
        
        return redirect()->back();
    }
    
    ####################################################################################################################################
    #######################
    # verticleSliderWithBg
    #######################
    public function verticleSliderWithBg(Request $request) 
    {
         $title = "Verticle Slider With Bg";
        $language_id = 1;
        $result = array();
        $message = array();
        $Section = '';$SectionData = array();$EditSectionData='';
          
        $Section = AppSection::where('view_type','VERTICLE_SLIDER_WITH_BG')->first();
        $SectionData = AppSectionData::where('section_id',$Section->id)->where('status','!=','DELETED')->orderBy('sort_order','desc')->get();
         
        $categories = DB::table('categories')
            ->leftJoin('categories_description', 'categories_description.categories_id', '=', 'categories.categories_id')
            ->LeftJoin('categories_description as parent_description', function ($join) {
                $join->on('parent_description.categories_id', '=', 'categories.parent_id')
                    ->where(function ($query) {
                        $query->where('parent_description.language_id', '=', 1)->limit(1);
                    });
            })
            ->select('categories.categories_id as id', 'categories.categories_image as image', 'categories.created_at as date_added', 'categories.updated_at as last_modified', 'categories_description.categories_name as name', 'categories.categories_slug as slug', 'parent_description.categories_name as parent_name')
            ->where('categories_description.language_id', '=', $language_id)->get();

        $products_id = null;
        $product = DB::table('products')
            ->leftJoin('products_description', 'products_description.products_id', '=', 'products.products_id')
            ->leftJoin('manufacturers', 'manufacturers.manufacturers_id', '=', 'products.manufacturers_id')
            ->leftJoin('manufacturers_info', 'manufacturers.manufacturers_id', '=', 'manufacturers_info.manufacturers_id')
            ->LeftJoin('specials', function ($join) {
                $join->on('specials.products_id', '=', 'products.products_id')->where('status', '=', '1');
            })
            ->select('products.*', 'products_description.*', 'manufacturers.*', 'manufacturers_info.manufacturers_url', 'specials.specials_id', 'specials.products_id as special_products_id', 'specials.specials_new_products_price as specials_products_price', 'specials.specials_date_added as specials_date_added', 'specials.specials_last_modified as specials_last_modified', 'specials.expires_date')
            ->where('products_description.language_id', '=', $language_id);
        if ($products_id != null) {
            $product->where('products.products_id', '=', $products_id);
        } else {
            $product->orderBy('products.products_id', 'DESC');
        }
        $products = $product->get();

        

        $result['message'] = $message;
        $result['categories'] = $categories;
        $result['products'] = $products;
        $result['editSectionData'] = $EditSectionData;
        $result['section'] = $Section;
        $result['sectionData'] = $SectionData;
        $result['commonContent'] = $this->Setting->commonContent();

        return view("admin.AppHomeSections.verticleSliderWithBg", ["title"=>$title])->with('result', $result);
    }
    
    
    #################################
    # Insert Data in verticleSliderWithBg section
    ################################
    public function verticleSliderWithBgInsert(Request $request) {
        $this->validate(request(), [
            'section_id' => 'bail|required',
            'title' => 'bail|required',
            'data_type' => 'bail|required',
            'sort_order' => 'bail|required',
            'status' => 'bail|required',
            'image_id' => 'bail|required|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'filter_value' => 'nullable|numeric', 
            'thresold_value' => 'nullable|numeric', 
        ]);
        $uploadImage = '';
        $title = $request->title;
        $type = $request->data_type;
        $sort_order = $request->sort_order;
        $status = $request->status;
        $urls = '';
        $filter_type = $request->filter_type;
        $filter_condition = $request->condition;
        $filter_value = $request->filter_value;
        $filter_thresold_value = $request->thresold_value;
        $section_id = $request->input('section_id');
        if ($type == 'CATEGORY' && $request->filled('categories_id')) {
            $urls = $request->categories_id;
        }
        else if ($type == 'PRODUCT' && $request->filled('products_id')) {
            $urls = $request->products_id;
        }else{
            session()->flash('error', 'Please Choose a data type');
                return redirect()->back();
        } 
            if (!empty($filter_type) && $type == 'CATEGORY') {
            if(empty($filter_condition)){
                session()->flash('error', 'Filter Condition is required');
                return redirect()->back();
            }
            if(empty($filter_value)){
                session()->flash('error', 'Filter Value is required');
                return redirect()->back();
            }
            if($filter_condition !="=" && empty($filter_thresold_value)){
                session()->flash('error', 'Filter thresold value is required');
                return redirect()->back();
            }
        }  
        try{
            if($request->hasFile('image_id')){
                $uploadImage = uploadImage($request->file('image_id'), 'appSectionData','verticleSliderWithBg');
            }
            $section = new AppSectionData;
            $section->title = $title;
            $section->section_id = $section_id;
            $section->data_type = $type;
            $section->urls = $urls;
            $section->filter_type = $filter_type;
            $section->condition = $filter_condition;
            $section->filter_value = $filter_value;
            $section->thresold_value = $filter_thresold_value;
            $section->image = $uploadImage;
            $section->sort_order = $sort_order;
            $section->status = $status;
            $section->created_by = auth()->user()->first_name;
            if($section->save()){
                session()->flash('success', " Section Data :: Saved. ");
            }else{
                session()->flash('error', " Some Error Occured !! Please try again.");
            }
            
        } catch (\Exception $e){
            Log::error(__CLASS__."::".__FUNCTION__." Exception occured ".$e->getTraceAsString());
            $unlink = public_path().'/'.$uploadImage;
                    if(!empty($uploadImage) && file_exists($unlink)){
                        unlink($unlink);
                    }
            session()->flash('error', " Exception Occured !! ".$e->getMessage());
        }
        
        return redirect()->back();
    }
    
    ###########################
    # Edit verticleSliderWithBg Thin
    ##########################
    public function editVerticleSliderWithBg($id)
    {
        
        $title = "Verticle Slider With Bg Edit";
        $language_id = 1;
        $result = array();
        $message = array();
        $Section = '';$SectionData = array();
        $EditSectionData = AppSectionData::find($id);
         if(isset($EditSectionData->id)){   
        $Section = AppSection::where('id',$EditSectionData->section_id)->first();
        $SectionData = AppSectionData::where('section_id',$EditSectionData->section_id)->where('status','!=','DELETED')->orderBy('sort_order','desc')->get();
        
         }else{
             return redirect()->to('admin/homeSections/verticleSliderWithBg');
         }
        $categories = DB::table('categories')
            ->leftJoin('categories_description', 'categories_description.categories_id', '=', 'categories.categories_id')
            ->LeftJoin('categories_description as parent_description', function ($join) {
                $join->on('parent_description.categories_id', '=', 'categories.parent_id')
                    ->where(function ($query) {
                        $query->where('parent_description.language_id', '=', 1)->limit(1);
                    });
            })
            ->select('categories.categories_id as id', 'categories.categories_image as image', 'categories.created_at as date_added', 'categories.updated_at as last_modified', 'categories_description.categories_name as name', 'categories.categories_slug as slug', 'parent_description.categories_name as parent_name')
            ->where('categories_description.language_id', '=', $language_id)->get();

        $products_id = null;
        $product = DB::table('products')
            ->leftJoin('products_description', 'products_description.products_id', '=', 'products.products_id')
            ->leftJoin('manufacturers', 'manufacturers.manufacturers_id', '=', 'products.manufacturers_id')
            ->leftJoin('manufacturers_info', 'manufacturers.manufacturers_id', '=', 'manufacturers_info.manufacturers_id')
            ->LeftJoin('specials', function ($join) {
                $join->on('specials.products_id', '=', 'products.products_id')->where('status', '=', '1');
            })
            ->select('products.*', 'products_description.*', 'manufacturers.*', 'manufacturers_info.manufacturers_url', 'specials.specials_id', 'specials.products_id as special_products_id', 'specials.specials_new_products_price as specials_products_price', 'specials.specials_date_added as specials_date_added', 'specials.specials_last_modified as specials_last_modified', 'specials.expires_date')
            ->where('products_description.language_id', '=', $language_id);
        if ($products_id != null) {
            $product->where('products.products_id', '=', $products_id);
        } else {
            $product->orderBy('products.products_id', 'DESC');
        }
        $products = $product->get();

        

        $result['message'] = $message;
        $result['categories'] = $categories;
        $result['products'] = $products;
        
        $result['section'] = $Section;
        $result['sectionData'] = $SectionData;
        $result['editSectionData'] = $EditSectionData;
        $result['commonContent'] = $this->Setting->commonContent();

        return view("admin.AppHomeSections.verticleSliderWithBg", ["title"=>$title])->with('result', $result);
    }
    
    #################################
    # Update Data in verticleSliderWithBg section
    ################################
    public function verticleSliderWithBgUpdate(Request $request) {
        $this->validate(request(), [
            'id' => 'bail|required',
            'title' => 'bail|required',
            'data_type' => 'bail|required',
            'sort_order' => 'bail|required',
            'status' => 'bail|required',
            'image_id' => 'nullable|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'filter_value' => 'nullable|numeric', 
            'thresold_value' => 'nullable|numeric', 
        ]);
        $uploadImage = '';
        $title = $request->title;
        $type = $request->data_type;
        $sort_order = $request->sort_order;
        $status = $request->status;
        $urls = '';
        $filter_type = $request->filter_type;
        $filter_condition = $request->condition;
        $filter_value = $request->filter_value;
        $filter_thresold_value = $request->thresold_value;
        $id = $request->input('id');
        if ($type == 'CATEGORY' && $request->filled('categories_id')) {
            $urls = $request->categories_id;
            if(empty($filter_type)){
            $filter_type = '';
            $filter_condition = '';
            $filter_value = '';
            $filter_thresold_value = '';
            }
        } else if ($type == 'PRODUCT' && $request->filled('products_id')) {
            $urls = $request->products_id;
            $filter_type = '';
            $filter_condition = '';
            $filter_value = '';
            $filter_thresold_value = '';
        } 
        if (!empty($filter_type) && $type == 'CATEGORY') {
            if(empty($filter_condition)){
                session()->flash('error', 'Filter Condition is required');
                return redirect()->back();
            }
            if(empty($filter_value)){
                session()->flash('error', 'Filter Value is required');
                return redirect()->back();
            }
            if($filter_condition !="=" && empty($filter_thresold_value)){
                session()->flash('error', 'Filter thresold value is required');
                return redirect()->back();
            }
        }
        $oldImage = '';
        try{
            if($request->hasFile('image_id')){
                $uploadImage = uploadImage($request->file('image_id'), 'appSectionData','verticleSliderWithBg');
            }
            $section = AppSectionData::find($id);
            $section->title = $title;
            $section->data_type = $type;
            $section->urls = $urls;
            $section->filter_type = $filter_type;
            $section->condition = $filter_condition;
            $section->filter_value = $filter_value;
            $section->thresold_value = $filter_thresold_value;
            if(!empty($uploadImage)){
                $oldImage = $section->image;
            $section->image = $uploadImage;
            }
            $section->sort_order = $sort_order;
            $section->status = $status;
            $section->created_by = auth()->user()->first_name;
            if($section->save()){
                session()->flash('success', " Section Data :: Updated. ");
                $unlink = public_path().'/'.$oldImage;
                    if(!empty($oldImage) && file_exists($unlink)){
                        unlink($unlink);
                    }
            }else{
                session()->flash('error', " Some Error Occured !! Please try again.");
            }
            
        } catch (\Exception $e){
            Log::error(__CLASS__."::".__FUNCTION__." Exception occured ".$e->getTraceAsString());
            $unlink = public_path().'/'.$uploadImage;
                    if(!empty($uploadImage) && file_exists($unlink)){
                        unlink($unlink);
                    }
            session()->flash('error', " Exception Occured !! ".$e->getMessage());
        }
        
        return redirect()->back();
    }
    
    #########################################################################################################################################
    
    #######################
    # videoCarasoul
    #######################
    public function videoCarasoul(Request $request) 
    {
         $title = "Video carasoul";
        $language_id = 1;
        $result = array();
        $message = array();
        $Section = '';$SectionData = array();$EditSectionData='';
          
        $Section = AppSection::where('view_type','VIDEO_CAROUSEL')->first();
        $SectionData = AppSectionData::where('section_id',$Section->id)->where('status','!=','DELETED')->orderBy('sort_order','desc')->get();
         
        

        

        $result['message'] = $message;
       
        $result['editSectionData'] = $EditSectionData;
        $result['section'] = $Section;
        $result['sectionData'] = $SectionData;
        $result['commonContent'] = $this->Setting->commonContent();

        return view("admin.AppHomeSections.videoCarasoul", ["title"=>$title])->with('result', $result);
    }
    
    #################################
    # Insert Data in videoCarasoul section
    ################################
    public function videoCarasoulInsert(Request $request) {
        $this->validate(request(), [
            'section_id' => 'bail|required',
            'sort_order' => 'bail|required',
            'status' => 'bail|required',
            'video' => 'bail|required|mimes:mpeg,ogg,mp4,webm,3gp,mov,flv,avi,wmv,ts|max:100040',
        ]);
        $uploadVideo = '';
        $type = '';
        $sort_order = $request->sort_order;
        $status = $request->status;
        $urls = '';
        $filter_type = '';
        $filter_condition = '';
        $filter_value = '';
        $filter_thresold_value = '';
        $section_id = $request->input('section_id');
        
        try{
            if($request->hasFile('video')){
                $uploadVideo = uploadImage($request->file('video'), 'videoCarasoul','videoCarasoul');
            }
            $section = new AppSectionData;
            $section->section_id = $section_id;
            $section->data_type = $type;
            $section->urls = $urls;
            $section->filter_type = $filter_type;
            $section->condition = $filter_condition;
            $section->filter_value = $filter_value;
            $section->thresold_value = $filter_thresold_value;
            $section->video = $uploadVideo;
            $section->sort_order = $sort_order;
            $section->status = $status;
            $section->created_by = auth()->user()->first_name;
            if($section->save()){
                session()->flash('success', " Section Data :: Saved. ");
            }else{
                session()->flash('error', " Some Error Occured !! Please try again.");
            }
            
        } catch (\Exception $e){
            Log::error(__CLASS__."::".__FUNCTION__." Exception occured ".$e->getTraceAsString());
            $unlink = public_path().'/'.$uploadVideo;
                    if(!empty($uploadVideo) && file_exists($unlink)){
                        unlink($unlink);
                    }
            session()->flash('error', " Exception Occured !! ".$e->getMessage());
        }
        
        return redirect()->back();
    }
    
    ###########################
    # Edit videoCarasoul
    ##########################
    public function editVideoCarasoul($id)
    {
        
        $title = "Video carasoul";
        $language_id = 1;
        $result = array();
        $message = array();
        $Section = '';$SectionData = array();
        $EditSectionData = AppSectionData::find($id);
         if(isset($EditSectionData->id)){   
        $Section = AppSection::where('id',$EditSectionData->section_id)->first();
        $SectionData = AppSectionData::where('section_id',$EditSectionData->section_id)->where('status','!=','DELETED')->orderBy('sort_order','desc')->get();
        
         }else{
             return redirect()->to('admin/homeSections/videoCarasoul');
         }
        

        

        $result['message'] = $message;
        
        
        $result['section'] = $Section;
        $result['sectionData'] = $SectionData;
        $result['editSectionData'] = $EditSectionData;
        $result['commonContent'] = $this->Setting->commonContent();

        return view("admin.AppHomeSections.videoCarasoul", ["title"=>$title])->with('result', $result);
    }
    
    #################################
    # Update Data in videoCarasoul section
    ################################
    public function videoCarasoulUpdate(Request $request) {
        
        $this->validate(request(), [
            'id' => 'bail|required',
            'sort_order' => 'bail|required',
            'status' => 'bail|required',
            'video' => 'nullable|mimes:mpeg,ogg,mp4,webm,3gp,mov,flv,avi,wmv,ts|max:100040',
        ]);
        $uploadVideo = '';
        $oldVideo = '';
        $type = '';
        $sort_order = $request->sort_order;
        $status = $request->status;
        $urls = '';
        $filter_type = '';
        $filter_condition = '';
        $filter_value = '';
        $filter_thresold_value = '';
        $id = $request->input('id');
        
        try{
            if($request->hasFile('video')){
                $uploadVideo = uploadImage($request->file('video'), 'videoCarasoul','videoCarasoul');
            }
            $section = AppSectionData::find($id);
            $section->data_type = $type;
            $section->urls = $urls;
            $section->filter_type = $filter_type;
            $section->condition = $filter_condition;
            $section->filter_value = $filter_value;
            $section->thresold_value = $filter_thresold_value;
            if(!empty($uploadVideo)){
            $oldVideo = $section->video;
            $section->video = $uploadVideo;
            }
            $section->sort_order = $sort_order;
            $section->status = $status;
            $section->created_by = auth()->user()->first_name;
            if($section->save()){
                session()->flash('success', " Section Data :: Saved. ");
                
                  $unlink = public_path().'/'.$oldVideo;
                    if(!empty($oldVideo) && file_exists($unlink)){
                        unlink($unlink);
                    }
            }else{
                session()->flash('error', " Some Error Occured !! Please try again.");
            }
            
        } catch (\Exception $e){
            Log::error(__CLASS__."::".__FUNCTION__." Exception occured ".$e->getTraceAsString());
            $unlink = public_path().'/'.$uploadVideo;
                    if(!empty($uploadVideo) && file_exists($unlink)){
                        unlink($unlink);
                    }
            session()->flash('error', " Exception Occured !! ".$e->getMessage());
        }
        
        return redirect()->back();
    }
    
    ##########################################################################################################################################
    
    #######################
    # videoCarasoul
    #######################
    public function productMarqee(Request $request) 
    {
         $title = "Product Marquee";
        $language_id = 1;
        $result = array();
        $message = array();
        $Section = '';$SectionData = array();$EditSectionData='';
          
        $Section = AppSection::where('view_type','PRODUCT_MARQUEE')->first();
        $SectionData = AppSectionData::where('section_id',$Section->id)->where('status','!=','DELETED')->orderBy('sort_order','desc')->get();
         

        $products_id = null;
        $product = DB::table('products')
            ->leftJoin('products_description', 'products_description.products_id', '=', 'products.products_id')
            ->leftJoin('manufacturers', 'manufacturers.manufacturers_id', '=', 'products.manufacturers_id')
            ->leftJoin('manufacturers_info', 'manufacturers.manufacturers_id', '=', 'manufacturers_info.manufacturers_id')
            ->LeftJoin('specials', function ($join) {
                $join->on('specials.products_id', '=', 'products.products_id')->where('status', '=', '1');
            })
            ->select('products.*', 'products_description.*', 'manufacturers.*', 'manufacturers_info.manufacturers_url', 'specials.specials_id', 'specials.products_id as special_products_id', 'specials.specials_new_products_price as specials_products_price', 'specials.specials_date_added as specials_date_added', 'specials.specials_last_modified as specials_last_modified', 'specials.expires_date')
            ->where('products_description.language_id', '=', $language_id);
        if ($products_id != null) {
            $product->where('products.products_id', '=', $products_id);
        } else {
            $product->orderBy('products.products_id', 'DESC');
        }
        $products = $product->get();

        

        $result['message'] = $message;
        $result['products'] = $products;
        $result['editSectionData'] = $EditSectionData;
        $result['section'] = $Section;
        $result['sectionData'] = $SectionData;
        $result['commonContent'] = $this->Setting->commonContent();

        return view("admin.AppHomeSections.productMarqee", ["title"=>$title])->with('result', $result);
    }
    
    #################################
    # Insert Data in videoCarasoul section
    ################################
    public function productMarqeeInsert(Request $request) {
        $this->validate(request(), [
            'section_id' => 'bail|required',
            'products_id' => 'bail|required',
            'status' => 'bail|required',
        ]);
        $uploadVideo = '';
        $type = 'PRODUCT';
        $sort_order = '1';
        $status = $request->status;
        $urls = '';
        $filter_type = '';
        $filter_condition = '';
        $filter_value = '';
        $filter_thresold_value = '';
        $section_id = $request->input('section_id');
        if ($type == 'PRODUCT' && $request->filled('products_id')) {
            $urls = implode(",",$request->products_id);
        }
        try{
            $section = new AppSectionData;
            $section->section_id = $section_id;
            $section->data_type = $type;
            $section->urls = $urls;
            $section->filter_type = $filter_type;
            $section->condition = $filter_condition;
            $section->filter_value = $filter_value;
            $section->thresold_value = $filter_thresold_value;
            
            $section->sort_order = $sort_order;
            $section->status = $status;
            $section->created_by = auth()->user()->first_name;
            if($section->save()){
                session()->flash('success', " Section Data :: Saved. ");
            }else{
                session()->flash('error', " Some Error Occured !! Please try again.");
            }
            
        } catch (\Exception $e){
            Log::error(__CLASS__."::".__FUNCTION__." Exception occured ".$e->getTraceAsString());
            
            session()->flash('error', " Exception Occured !! ".$e->getMessage());
        }
        
        return redirect()->back();
    }
    
    ###########################
    # Edit videoCarasoul
    ##########################
    public function editProductMarqee($id)
    {
        
        $title = "Product Marquee Edit";
        $language_id = 1;
        $result = array();
        $message = array();
        $Section = '';$SectionData = array();
        $EditSectionData = AppSectionData::find($id);
         if(isset($EditSectionData->id)){   
        $Section = AppSection::where('id',$EditSectionData->section_id)->first();
        $SectionData = AppSectionData::where('section_id',$EditSectionData->section_id)->where('status','!=','DELETED')->orderBy('sort_order','desc')->get();
        
         }else{
             return redirect()->to('admin/homeSections/productMarqee');
         }
       

        $products_id = null;
        $product = DB::table('products')
            ->leftJoin('products_description', 'products_description.products_id', '=', 'products.products_id')
            ->leftJoin('manufacturers', 'manufacturers.manufacturers_id', '=', 'products.manufacturers_id')
            ->leftJoin('manufacturers_info', 'manufacturers.manufacturers_id', '=', 'manufacturers_info.manufacturers_id')
            ->LeftJoin('specials', function ($join) {
                $join->on('specials.products_id', '=', 'products.products_id')->where('status', '=', '1');
            })
            ->select('products.*', 'products_description.*', 'manufacturers.*', 'manufacturers_info.manufacturers_url', 'specials.specials_id', 'specials.products_id as special_products_id', 'specials.specials_new_products_price as specials_products_price', 'specials.specials_date_added as specials_date_added', 'specials.specials_last_modified as specials_last_modified', 'specials.expires_date')
            ->where('products_description.language_id', '=', $language_id);
        if ($products_id != null) {
            $product->where('products.products_id', '=', $products_id);
        } else {
            $product->orderBy('products.products_id', 'DESC');
        }
        $products = $product->get();

        

        $result['message'] = $message;
       
        $result['products'] = $products;
        
        $result['section'] = $Section;
        $result['sectionData'] = $SectionData;
        $result['editSectionData'] = $EditSectionData;
        $result['commonContent'] = $this->Setting->commonContent();

        return view("admin.AppHomeSections.productMarqee", ["title"=>$title])->with('result', $result);
    }
    
    #################################
    # Update Data in videoCarasoul section
    ################################
    public function productMarqeeUpdate(Request $request) {
        
        $this->validate(request(), [
            'id' => 'bail|required',
            'products_id' => 'bail|required',
            'status' => 'bail|required',
        ]);
        $type = 'PRODUCT';
        $sort_order = '1';
        $status = $request->status;
        $urls = '';
        $filter_type = '';
        $filter_condition = '';
        $filter_value = '';
        $filter_thresold_value = '';
        $id = $request->input('id');
        if ($type == 'PRODUCT' && $request->filled('products_id')) {
            $urls = implode(",",$request->products_id);
        }
        
        try{
            
            $section = AppSectionData::find($id);
            $section->data_type = $type;
            $section->urls = $urls;
            $section->filter_type = $filter_type;
            $section->condition = $filter_condition;
            $section->filter_value = $filter_value;
            $section->thresold_value = $filter_thresold_value;
            $section->sort_order = $sort_order;
            $section->status = $status;
            $section->created_by = auth()->user()->first_name;
            if($section->save()){
                session()->flash('success', " Section Data :: Saved. ");
                
                 
            }else{
                session()->flash('error', " Some Error Occured !! Please try again.");
            }
            
        } catch (\Exception $e){
            Log::error(__CLASS__."::".__FUNCTION__." Exception occured ".$e->getTraceAsString());
            
            session()->flash('error', " Exception Occured !! ".$e->getMessage());
        }
        
        return redirect()->back();
    }
    

}
