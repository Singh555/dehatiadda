<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Kyslik\ColumnSortable\Sortable;
use App\manufacturers_info;
use App\Models\Core\Manufacturers;
use App\Http\Controllers\AdminControllers\SiteSettingController;
use Illuminate\Support\Facades\Validator;
use Log;
class Manufacturers extends Model
{
  public function __construct()
  {
      $varsetting = new SiteSettingController();
      $this->varsetting = $varsetting;
  }
    //
    use Sortable;
    public function manufacturers_info(){
        return $this->hasOne('App\manufacturers_info');
    }

    public function images(){
        return $this->belongsTo('App\Images');
    }

    public $sortableAs = ['manufacturers_url'];
    public $sortable = ['manufacturers_id', 'manufacturer_name', 'manufacturer_image','manufacturers_slug','created_at','updated_at'];

    public function paginator(){
         $manufacturers =  Manufacturers::sortable(['manufacturers_id'=>'desc'])
                ->leftJoin('manufacturers_info','manufacturers_info.manufacturers_id', '=', 'manufacturers.manufacturers_id')
                                   

                ->select('manufacturers.manufacturers_id as id', 'manufacturers.manufacturer_image as image',  
                'manufacturers.manufacturer_name as name', 'manufacturers_info.manufacturers_url as url', 'manufacturers_info.url_clicked', 
                'manufacturers_info.date_last_click as clik_date','manufacturers.manufacturer_image_url')
                ->paginate(50);


        return $manufacturers;
    }

    public function getter($language_id){
         if($language_id == null){
           $language_id = '1';
         }
         $manufacturers =  Manufacturers::sortable(['manufacturers_id'=>'desc'])
            ->leftJoin('manufacturers_info','manufacturers_info.manufacturers_id', '=', 'manufacturers.manufacturers_id')
            ->select('manufacturers.manufacturers_id as id', 'manufacturers.manufacturer_image as image',  'manufacturers.manufacturer_name as name', 'manufacturers_info.manufacturers_url as url', 'manufacturers_info.url_clicked', 'manufacturers_info.date_last_click as clik_date')
            ->where('manufacturers_info.languages_id', $language_id)
            ->get();
        return $manufacturers;
    }

    public function insert($request){

          $slug = $request->name;
          $date_added	= date('y-m-d h:i:s');
          $languages_id 	=  '1';
          $slug_count = 0;$imageDb = '';
try {
    DB::beginTransaction();
          do{
              if($slug_count==0){
                  $currentSlug = $this->varsetting->slugify($request->name);
              }else{
                  $currentSlug = $this->varsetting->slugify($request->name.'-'.$slug_count);
              }
              $slug = $currentSlug;

              $checkSlug = $this->slug($currentSlug);

              $slug_count++;
          }

          while(count($checkSlug)>0);
          
          if($request->hasFile('image_id')){
                $imageDb = uploadImage($request->file('image_id'), 'manufacturer','Manufacturer');
            }

          $manufacturers_id = DB::table('manufacturers')->insertGetId([
              'manufacturer_image_url'   =>   $imageDb,
              'created_at'			=>   $date_added,
              'manufacturer_name' 	=>   $request->name,
              'manufacturers_slug'	=>	 $slug
          ]);

          DB::table('manufacturers_info')->insert([
              'manufacturers_id'  	=>     $manufacturers_id,
              'manufacturers_url'     =>     $request->manufacturers_url,
              'languages_id'			=>	   $languages_id,
              //'url_clickeded'			=>	   $request->url_clickeded
          ]);
          DB::commit();
             session()->put('success',"Data Saved Successfully .");
             return true;
        }catch(\Exception $e){
                    DB::rollback();
                    Log::error(__CLASS__."::".__FUNCTION__."Exception occured :: ".$e->getMessage());
                    $unlink = public_path().'/'.$imageDb;
                    if(!empty($imageDb) && file_exists($unlink)){
                        unlink($unlink);
                    }
                    session()->put('error',"Exception While Data Storing For Manufacturer . Please try again !");
                    
                } 
                return false;

    }

    public function edit($manufacturers_id){

        $editManufacturer = DB::table('manufacturers')
            ->leftJoin('manufacturers_info','manufacturers_info.manufacturers_id', '=', 'manufacturers.manufacturers_id')
            
            ->select('manufacturers.manufacturers_id as id','manufacturers.manufacturer_image_url', 'manufacturer_image as image',  'manufacturers.manufacturer_name as name', 'manufacturers_info.manufacturers_url as url', 'manufacturers_info.url_clicked', 'manufacturers_info.date_last_click as clik_date', 'manufacturers.manufacturers_slug as slug')
            ->where( 'manufacturers.manufacturers_id', $manufacturers_id )
            ->first();

         return $editManufacturer;
    }

    public function filter($name,$param){
      switch ( $name )
      {
          case 'Name':
              $manufacturers = Manufacturers::sortable(['manufacturers_id'=>'desc'])
                  ->leftJoin('manufacturers_info','manufacturers_info.manufacturers_id', '=', 'manufacturers.manufacturers_id')
                  ->select('manufacturers.manufacturers_id as id','manufacturers.manufacturer_image_url', 'manufacturers.manufacturer_image as image',  'manufacturers.manufacturer_name as name', 'manufacturers_info.manufacturers_url as url', 'manufacturers_info.url_clicked', 'manufacturers_info.date_last_click as clik_date')
                  ->where('manufacturers.manufacturer_name', 'LIKE', '%' . $param . '%')->paginate('10');
              break;

          case 'URL':
              $manufacturers = Manufacturers::sortable(['manufacturers_id'=>'desc'])
                  ->leftJoin('manufacturers_info','manufacturers_info.manufacturers_id', '=', 'manufacturers.manufacturers_id')
                  ->select('manufacturers.manufacturers_id as id','manufacturers.manufacturer_image_url', 'manufacturers.manufacturer_image as image',  'manufacturers.manufacturer_name as name', 'manufacturers_info.manufacturers_url as url', 'manufacturers_info.url_clicked', 'manufacturers_info.date_last_click as clik_date')
                  ->where('manufacturers_info.manufacturers_url', 'LIKE', '%' . $param . '%')->paginate('10');
              break;


          default:
              $manufacturers = Manufacturers::sortable(['manufacturers_id'=>'desc'])
                  ->leftJoin('manufacturers_info','manufacturers_info.manufacturers_id', '=', 'manufacturers.manufacturers_id')
                  ->select('manufacturers.manufacturers_id as id','manufacturers.manufacturer_image_url', 'manufacturers.manufacturer_image as image',  'manufacturers.manufacturer_name as name', 'manufacturers_info.manufacturers_url as url', 'manufacturers_info.url_clicked', 'manufacturers_info.date_last_click as clik_date')
                  ->where('manufacturers_info.languages_id', '1')->paginate('10');
      }
        return $manufacturers;
    }

    public function fetchAllmanufacturers($language_id){

        $getManufacturers = DB::table('manufacturers')
            ->leftJoin('manufacturers_info','manufacturers_info.manufacturers_id', '=', 'manufacturers.manufacturers_id')
            ->select('manufacturers.manufacturers_id as id','manufacturers.manufacturer_image_url', 'manufacturers.manufacturer_image as image',  'manufacturers.manufacturer_name as name', 'manufacturers_info.manufacturers_url as url', 'manufacturers_info.url_clicked', 'manufacturers_info.date_last_click as clik_date')
            ->where('manufacturers_info.languages_id', $language_id)->get();
        return $getManufacturers;
    }

    public function fetchmanufacturers(){

        $manufacturers = DB::table('manufacturers')
            ->leftJoin('manufacturers_info','manufacturers_info.manufacturers_id', '=', 'manufacturers.manufacturers_id')
            
            
            ->select('manufacturers.manufacturers_id as id','manufacturers.manufacturer_image_url', 'manufacturers.manufacturer_image as image',  'manufacturers.manufacturer_name as name', 'manufacturers_info.manufacturers_url as url', 'manufacturers_info.url_clicked', 'manufacturers_info.date_last_click as clik_date')
            ->where('manufacturers_info.languages_id', '1');


        return $manufacturers;


    }



    public function slug($currentSlug){

        $checkSlug = DB::table('manufacturers')->where('manufacturers_slug',$currentSlug)->get();

        return $checkSlug;
    }



    public function updaterecord($request){

                  $last_modified 	=   date('y-m-d h:i:s');
                  $languages_id = '1';
          $uploadImage = '';$oldImage = '';
          try{
                  //check slug
                  if($request->old_slug!=$request->slug ){
                      $slug = $request->slug;
                      $slug_count = 0;
                      do{
                          if($slug_count==0){
                              $currentSlug = $this->varsetting->slugify($request->slug);
                          }else{
                              $currentSlug = $this->varsetting->slugify($request->slug.'-'.$slug_count);
                          }
                          $slug = $currentSlug;

                          $checkSlug = $this->slug($currentSlug);
                          $slug_count++;
                      }

                      while(count($checkSlug)>0);

                  }else{
                      $slug = $request->slug;
                  }

                  if($request->hasFile('image_id')){

                      $oldImage = $request->oldImage;
                     $uploadImage = uploadImage($request->file('image_id'), 'manufacturer','Manufacturer');
                  }else{
                      $uploadImage = $request->oldImage;
                  }

                DB::table('manufacturers')->where('manufacturers_id', $request->id)->update([
                    'manufacturer_image_url'  =>   $uploadImage,
                    'updated_at'			=>   $last_modified,
                    'manufacturer_name' 	=>   $request->name,
                    'manufacturers_slug'	=>	 $slug
                ]);
                DB::table('manufacturers_info')->where('manufacturers_id', $request->id)->update([
                    'manufacturers_url'     =>     $request->manufacturers_url,
                    'languages_id'			=>	   $languages_id,
                    //'url_clickeded'			=>	   $request->url_clickeded
                ]);

                $unlink = public_path().'/'.$oldImage;
                    if(!empty($oldImage) && file_exists($unlink)){
                        unlink($unlink);
                    }
                
               session()->put('success',"Data Updated Successfully !"); 
        return true;
        }catch(\Exception $e){
                    Log::error(__CLASS__."::".__FUNCTION__."Exception occured :: ".$e->getMessage());
                    $unlink = public_path().'/'.$uploadImage;
                    if(!empty($uploadImage) && file_exists($unlink)){
                        unlink($unlink);
                    }
                    session()->put('error',"Exception While Data Updating For Manufacturer . Please try again !");
                    
                } 
        return false;
    }


    //delete Manufacturers

    public function destroyrecord($request){

        DB::table('manufacturers')->where('manufacturers_id', $request->manufacturers_id)->delete();
        DB::table('manufacturers_info')->where('manufacturers_id', $request->manufacturers_id)->delete();

    }
    public function fetchsortmanufacturers($name, $param){

        switch ( $name )
        {
            case 'Name':
                $manufacturers = DB::table('manufacturers')
                ->leftJoin('manufacturers_info','manufacturers_info.manufacturers_id', '=', 'manufacturers.manufacturers_id')
                
                
                ->select('manufacturers.manufacturers_id as id','manufacturers.manufacturer_image_url', 'manufacturers.manufacturer_image as image',  'manufacturers.manufacturer_name as name', 'manufacturers_info.manufacturers_url as url', 'manufacturers_info.url_clicked', 'manufacturers_info.date_last_click as clik_date')
                ->where('manufacturers.manufacturer_name', 'LIKE', '%' . $param . '%')->paginate('10');
                  break;

            case 'URL':
                $manufacturers = DB::table('manufacturers')
                    ->leftJoin('manufacturers_info','manufacturers_info.manufacturers_id', '=', 'manufacturers.manufacturers_id')
                    
                    
                    ->select('manufacturers.manufacturers_id as id','manufacturers.manufacturer_image_url', 'manufacturers.manufacturer_image as image',  'manufacturers.manufacturer_name as name', 'manufacturers_info.manufacturers_url as url', 'manufacturers_info.url_clicked', 'manufacturers_info.date_last_click as clik_date')
                    ->where('manufacturers_info.manufacturers_url', 'LIKE', '%' . $param . '%')->paginate('10');
                break;


            default:
            $manufacturers = DB::table('manufacturers')
                ->leftJoin('manufacturers_info','manufacturers_info.manufacturers_id', '=', 'manufacturers.manufacturers_id')
                
                
                ->select('manufacturers.manufacturers_id as id','manufacturers.manufacturer_image_url', 'manufacturers.manufacturer_image as image',  'manufacturers.manufacturer_name as name', 'manufacturers_info.manufacturers_url as url', 'manufacturers_info.url_clicked', 'manufacturers_info.date_last_click as clik_date')
                ->where('manufacturers_info.languages_id', '1')->paginate('10');
        }


        return $manufacturers;


    }

}
