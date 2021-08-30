<?php
namespace App\Http\Controllers\App;

use DB;
use Log;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Carbon\Carbon;
use Validator;
use App\Helpers\HttpStatus;

class VideoController extends Controller
{

    public function getVideoList(Request $request) {
        Log::debug(__CLASS__." :: ".__FUNCTION__." called");
        $consumer_data = getallheaders();
        $consumer_data['consumer_ip'] = $request->ip();
        $consumer_data['consumer_url'] = __FUNCTION__;
        $authController = new AppSettingController();
        $authenticate = $authController->apiAuthenticate($consumer_data);
        if ($authenticate == 1 && auth()->user()->id) {
            Log::debug("Id : ".auth()->user()->id);
            $offset = 0;
            $dataLimit = 20;
            if(isset($request->offset)){
                $offset = $request->offset;
            }
            Log::debug(__CLASS__." :: ".__FUNCTION__." Offset : ".$offset);
            $data = DB::table("video_links")
                ->where('status', '=', 1)
                ->offset($offset)
                ->limit($dataLimit)
                ->orderBy('id', 'desc')
                ->get();
            $message = "Video found !";
            if(count($data) == 0){
                $data = null;
                $message = "No video found !";
            }

            return returnResponse($message, HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $data);
        }
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
    }
}
