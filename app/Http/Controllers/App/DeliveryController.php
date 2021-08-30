<?php
namespace App\Http\Controllers\App;

use Auth;
use DB;
use Log;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Carbon\Carbon;
use App\Models\AppModels\Account;
use App\Models\Core\User;
use Validator;
use App\Helpers\HttpStatus;


use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\AppModels\Delivery;

class DeliveryController extends Controller
{

    public function checkDeliveryPinCode(Request $request){
        $validator = Validator::make($request->all(), [
            'pincode' => 'required',
        ]);
        if($validator->fails()){
            return returnResponse($validator->errors(), HttpStatus::HTTP_UNPROCESSABLE_ENTITY);
        }
        return Delivery::checkPinCodeAvailabilityDelhivery($request);
    }
}
