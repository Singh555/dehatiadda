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
use App\Models\AppModels\Home;
use App\Helpers\HttpStatus;
class HomeController extends Controller
{

	//getbanners
	public function getSections(Request $request){
            
		return Home::getSections($request);
		
	}

}
