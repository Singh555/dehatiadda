<?php
namespace App\Http\Controllers\App;

use Auth;
use DB;
use Log;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Carbon\Carbon;
use App\Models\Core\User;
use Validator;
use App\Helpers\HttpStatus;


use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\AppModels\Reviews;


class ReviewsController extends Controller
{

	public function givereview(Request $request){
        return Reviews::givereview($request);
	}


	public function updatereview(Request $request){
        return Reviews::updatereview($request);
	}
    
    public function checkreview(Request $request){
        return Reviews::checkreview($request);
    }

	public function getreviews(Request $request){
        return Reviews::getreviews($request);
	}


}
