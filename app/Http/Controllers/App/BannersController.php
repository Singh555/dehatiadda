<?php
namespace App\Http\Controllers\App;

use Validator;
use DB;
use DateTime;
use Hash;
use Auth;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\AppModels\Banner;
use App\Helpers\HttpStatus;
class BannersController extends Controller
{

	//getbanners
	public function getbanners(Request $request){
		return Banner::getbanners($request);
		
	}

}
