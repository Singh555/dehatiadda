<?php
namespace App\Http\Controllers\App\FreeShipping;

use Validator;
use Auth;
use DB;
use Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Helpers\HttpStatus;
use App\Http\Controllers\App\AppSettingController;

class CustomerController extends Controller
{

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProfile(Request $request) {
        $consumer_data = getallheaders();
        $consumer_data['consumer_ip'] = $request->ip();
        $consumer_data['consumer_url'] = __FUNCTION__;
        $authController = new AppSettingController();
        $authenticate = $authController->apiAuthenticate($consumer_data);

        if ($authenticate == 1 && auth()->user()->id) {
            $user = DB::table('users')
                ->where('status', '=', 1)
                ->where('id', '=', auth()->user()->id)
                ->first();
         return returnResponse("Get Profile data !", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $user);
            
        }
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
    }
    
    public function getWalletTxn(Request $request) {
        $consumer_data = getallheaders();
        $consumer_data['consumer_ip'] = $request->ip();
        $consumer_data['consumer_url'] = __FUNCTION__;
        $authController = new AppSettingController();
        $authenticate = $authController->apiAuthenticate($consumer_data);

        if ($authenticate == 1 && auth()->user()->id) {
            $offset = 0;
            $dataLimit = 20;
            if(isset($request->offset)){
                $offset = $request->offset;
            }
            $data = DB::table("swallet_txn")
                    ->where('user_id', '=', auth()->user()->id)
                    ->where('status', '=', "ACTIVE")
                    ->offset($offset)
                    ->limit($dataLimit)
                    ->orderBy('id', 'desc')
                    ->get();

            return returnResponse("Txn data !", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $data);
            
        }
        return returnResponse(HttpStatus::$text[HttpStatus::HTTP_UNAUTHORIZED], HttpStatus::HTTP_UNAUTHORIZED);
    }
}
