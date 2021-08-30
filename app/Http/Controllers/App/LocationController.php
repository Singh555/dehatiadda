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
use App\Models\AppModels\Location;

class LocationController extends Controller
{

	public function getcountries(Request $request){
		return Location::countries($request);
		
	}

	public function getzones(Request $request){
		return Location::zones($request);
		
	}

	public function addshippingaddress(Request $request){
    	return  Location::addshippingaddress($request);
		
	}

	public function updateshippingaddress(Request $request){
    	return  Location::updateshippingaddress($request);
		

	}

	public function deleteshippingaddress(Request $request){
    	return  Location::deleteshippingaddress($request);
		

	}

	public function getalladdress(Request $request){
    	return  Location::getalladdress($request);
		

	}

	public function updatedefaultaddress(Request $request){
    	return Location::updatedefaultaddress($request);
		
	}
	public function getdefaultaddress(Request $request){
    	return Location::getdefaultaddress($request);
		
	}

	public function getTaxRate(Request $request){
    	return Location::getTaxRate($request);
		
	}

}
