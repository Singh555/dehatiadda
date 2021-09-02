<?php
namespace App\Http\Controllers\App;

//validator is builtin class in laravel
use Validator;

use DB;
//for password encryption or hash protected
use Hash;

//for authenitcate login data
use Auth;
use Illuminate\Foundation\Auth\ThrottlesLogins;

//for requesting a value
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Helpers\HttpStatus;
//for Carbon a value
use Carbon\Carbon;

class PagesController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

	//getAllPages
	public function getallpages(Request $request){
		$language_id = 1;
            if($request->has('language_id')){
            $language_id = $request->language_id;
            }
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

			$data = DB::table('pages')
				->LeftJoin('pages_description', 'pages_description.page_id', '=', 'pages.page_id')
				->where('pages_description.language_id', '=', $language_id)->where('pages.type', '=', 1)->where('pages.status', '=', 1)->get();

			$result = array();
			$index = 0;
			foreach($data as $pages_data){
				array_push($result, $pages_data);

				$description =  $pages_data->description;
				$result[$index]->description = stripslashes($description);
				$index++;

			}

			//check if record exist
			if(count($data)>0){
                                        return returnResponse("Returned all Pages.", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS,$result);
				}else{
                                        return returnResponse("No Data Found.", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS,$result);
				}
		}
                return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
	}

}
