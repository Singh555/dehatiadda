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

class Banner extends Model
{

 public static function getbanners($request){
  $consumer_data 		 				  =  array();
  $consumer_data = getallheaders();
      /*
      $consumer_data['consumer_key'] = $request->header('consumer_key');
      $consumer_data['consumer_secret'] = $request->header('consumer_secret');
      $consumer_data['consumer_nonce'] = $request->header('consumer_nonce');
      $consumer_data['consumer_device_id'] = $request->header('consumer_device_id');
      */
      $consumer_data['consumer_ip'] = $request->ip();
  $consumer_data['consumer_url']  	  =  __FUNCTION__;


  $authController = new AppSettingController();
  $authenticate = $authController->apiAuthenticate($consumer_data);

  if($authenticate==1){
    //current time
    $currentDate = Carbon\Carbon::now();
    $currentDate = $currentDate->toDateTimeString();

    $banners = DB::table('banners')
          ->select('banners_id as id', 'banners_title as title', 'banners_url as url', 'banners_image_url as image', 'type', 'view_type','view_position')
          ->where('status', '=', '1')
          ->where('expires_date', '>', $currentDate)
          ->get();

    if(count($banners)>0){
      return returnResponse("Banners are returned successfull.", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS,$banners);
    }else{
      $banners = array();
      return returnResponse("No Banners found.", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS,$banners);
    }
  }
  
return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);

}

//banners history
public function bannerhistory(Request $request){
  $consumer_data 		 				  =  array();
  $consumer_data['consumer_key'] 	 	  =  request()->header('consumer-key');
  $consumer_data['consumer_secret']	  =  request()->header('consumer-secret');
  $consumer_data['consumer_nonce']	  =  request()->header('consumer-nonce');
  $consumer_data['consumer_device_id']  =  request()->header('consumer-device-id');
  $consumer_data['consumer_ip']  	  = request()->header('consumer-ip');
  $consumer_data['consumer_url']  	  =  __FUNCTION__;
  $authController = new AppSettingController();
  $authenticate = $authController->apiAuthenticate($consumer_data);

  if($authenticate==1){

    $banners_id = $request->banners_id;
    $banners_history_date = date('Y-m-d H:i:s');

    $bannerHistory = DB::table('banners_history')
           ->where('banners_id', '=', $banners_id)
           ->get();

    //if already clicked by other user
    if(count($bannerHistory)){
      $addBanner = DB::table('banners_history')->insert([
                  'banners_clicked' => '1',
                  'banners_history_date' => '$banners_history_date',
                  'banners_id' => '$banners_id'
                ]);
    }else{
      $updateBanner = DB::table('banners_history')->update([
                  'banners_clicked' => '1',
                  'banners_history_date' => '$banners_history_date',
                ])
                ->where('banners_id', '=', '$banners_id');
    }
    $data = array();
    $responseData = array('success'=>'1', 'data'=>$data, 'message'=>"banner history has been added.");

  }else{
    $responseData = array('success'=>'0', 'data'=>array(),  'message'=>"Unauthenticated call.");
  }

  $response = json_encode($responseData);
  print $response;  
}




}
