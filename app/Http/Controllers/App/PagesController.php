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
				->where('pages_description.language_id', '=', $language_id)->get();

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
					//$responseData = array('success'=>'0', 'pages_data'=>array(),  'message'=>"Empty record.");
                                        return returnResponse("No Data Found.", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS,$result);
				}
		}
                return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
	}
    //getprivacypolicy
    public function getprivacypolicy(Request $request){
        $language_id = 1;
            if($request->has('language_id')){
            $language_id = $request->language_id;
            }
        $authenticate = 1;
        if($authenticate==1){

            $data = DB::table('pages')
                ->LeftJoin('pages_description', 'pages_description.page_id', '=', 'pages.page_id')
                ->where('pages_description.language_id', '=', $language_id)->where('pages.page_id', '=', 1)->first();

        
                        ?>
                        <html lang="en">

                        <head>
                                <meta charset="UTF-8">
                                <!-- CSS FOR STYLING THE PAGE -->
                                <style>
                                        table {
                                                margin: 0 auto;
                                                font-size: large;
                                                border: 1px solid black;
                                        }

                                        h1 {
                                                text-align: left;
                                                font-size: xx-large;
                                                font-family: 'Gill Sans',
                                                        'Gill Sans MT', ' Calibri',
                                                        'Trebuchet MS', 'sans-serif';
                                        }
                                        h2 {
                                                font-size: 28px;
                                                font-weight: bold;
                                                font-family: 'Gill Sans',
                                                        'Gill Sans MT', ' Calibri',
                                                        'Trebuchet MS', 'sans-serif';
                                        }
                                        h3 {
                                                font-size: 26px;
                                                font-weight: bold;
                                                font-family: 'Gill Sans',
                                                        'Gill Sans MT', ' Calibri',
                                                        'Trebuchet MS', 'sans-serif';
                                        }

                                        p, ol li, ul li {
                                                font-size: 20px;
                                                font-weight: bold;
                                                text-align: justify;
                                                font-family: 'Gill Sans',
                                                        'Gill Sans MT', ' Calibri',
                                                        'Trebuchet MS', 'sans-serif';
                                        }
                                        


                                        td {
                                                background-color: #E4F5D4;
                                                border: 1px solid black;
                                        }

                                        th,
                                        td {
                                                font-weight: bold;
                                                border: 1px solid black;
                                                padding: 10px;
                                                text-align: center;
                                        }

                                        td {
                                                font-weight: lighter;
                                        }
                                </style>
                        </head>

                        <body>
                                <?php echo $data->description; ?>
                        </body>

                        </html>

                        <?php
                         
                }

            
    }

}
