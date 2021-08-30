<?php

namespace App\Models\AppModels;

use Log;
use DB;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\HttpStatus;
class Delivery extends Model
{
    public static function checkPinCodeAvailabilityDelhivery($request)
    {
        Log::debug(__CLASS__." :: ".__FUNCTION__." called");
        $pincode = $request->pincode;
        if(empty($pincode))
        {
            Log::debug(__CLASS__." :: ".__FUNCTION__." Pin Code should not be empty !!");
            return returnResponse("Pin Code should not be empty !!");
        }
        //$token = "983c31a6eaa9791a0c028382df0e39debaa64c32";
        $token = "266308ef2c31bddc66cf782f975c05032658a7d9";
        $checkPinCodeUrl = "https://track.delhivery.com/c/api/pin-codes/json/?token=$token&filter_codes=$pincode";
        //$checkPinCodeUrl = "https://staging-express.delhivery.com/c/api/pin-codes/json/?filter_codes=$pincode";
        Log::debug(__CLASS__." :: ".__FUNCTION__." Url to be triggered as ".$checkPinCodeUrl);
        $checkPinCodeResp = callUrl($checkPinCodeUrl);
        $jsonDecodeResp = json_decode($checkPinCodeResp, TRUE);
        if(json_last_error() === JSON_ERROR_NONE){
            if(isset($jsonDecodeResp["delivery_codes"])){
                //echo print_r($jsonDecodeResp["delivery_codes"]);
                if(isset($jsonDecodeResp["delivery_codes"][0]["postal_code"])){
                    $district = ""; $pincode = ""; $country_code = ""; $state_code = "";
                    $short_code = ""; $cash = ""; $pickup = ""; $cod = ""; $repl = "";
                    $prepaid = ""; $is_oda = ""; $max_amount = ""; $max_weight = "";
                    $postal_code_array_encode = json_encode($jsonDecodeResp["delivery_codes"][0]["postal_code"]);
                    $postal_code_array = json_decode($postal_code_array_encode);
                    if(isset($postal_code_array->district)){
                        Log::debug(__FUNCTION__." District name found as ".$postal_code_array->district);
                        $district = $postal_code_array->district;
                    }
                    if(isset($postal_code_array->pin)){
                        Log::debug(__FUNCTION__." Pin code found as ".$postal_code_array->pin);
                        $pincode = $postal_code_array->pin;
                    }
                    if(isset($postal_code_array->country_code)){
                        Log::debug(__FUNCTION__." Country code found as ".$postal_code_array->country_code);
                        $country_code = $postal_code_array->country_code;
                    }
                    if(isset($postal_code_array->state_code)){
                        Log::debug(__FUNCTION__." State code found as ".$postal_code_array->state_code);
                        $state_code = $postal_code_array->state_code;
                    }
                    if(isset($postal_code_array->sort_code)){
                        Log::debug(__FUNCTION__." Short code found as ".$postal_code_array->sort_code);
                        $short_code = $postal_code_array->sort_code;
                    }
                    if(isset($postal_code_array->cash)){
                        Log::debug(__FUNCTION__." Is Cash on Delivery Available : ".$postal_code_array->cash);
                        $cash = $postal_code_array->cash;
                    }
                    if(isset($postal_code_array->pickup)){
                        Log::debug(__FUNCTION__." Is Pickup Available : ".$postal_code_array->pickup);
                        $pickup = $postal_code_array->pickup;
                    }
                    if(isset($postal_code_array->cod)){
                        Log::debug(__FUNCTION__." Is COD Available : ".$postal_code_array->cod);
                        $cod = $postal_code_array->cod;
                    }
                    if(isset($postal_code_array->repl)){
                        Log::debug(__FUNCTION__." Is REPL Available : ".$postal_code_array->repl);
                        $repl = $postal_code_array->repl;
                    }
                    if(isset($postal_code_array->pre_paid)){
                        Log::debug(__FUNCTION__." Is Prepaid Available : ".$postal_code_array->pre_paid);
                        $prepaid = $postal_code_array->pre_paid;
                    }
                    if(isset($postal_code_array->max_amount)){
                        Log::debug(__FUNCTION__." Max Amount : ".$postal_code_array->max_amount);
                        $max_amount = $postal_code_array->max_amount;
                    }
                    if(isset($postal_code_array->is_oda)){
                        Log::debug(__FUNCTION__." Is ODA : ".$postal_code_array->is_oda);
                        $is_oda = $postal_code_array->is_oda;
                    }
                    if(isset($postal_code_array->max_weight)){
                        Log::debug(__FUNCTION__." Max weight : ".$postal_code_array->max_weight);
                        $max_weight = $postal_code_array->max_weight;
                    }
                    $return_array = array(
                        "district" => "$district",
                        "pin" => $pincode,
                        "max_amount" => $max_amount,
                        "pre_paid" => "$prepaid",
                        "cash" => "$cash",
                        "pickup" => "$pickup",
                        "repl" => "$repl",
                        "cod" => "$cod",
                        "country_code" => "$country_code",
                        "sort_code" => "$short_code",
                        "is_oda" => "$is_oda",
                        "state_code" => "$state_code",
                        "state" => self::getStateNameByCode($state_code),
                        "max_weight" => $max_weight
                    );
                    return returnResponse("Delivery Available !", HttpStatus::HTTP_OK, HttpStatus::HTTP_SUCCESS, $return_array);
                }else{
                    return returnResponse("Pin Code $pincode is not available for delivery !!");
                }
            }else{
                return returnResponse("Pin Code getting failed ! Response not found !!");
            }
        }else{
            return returnResponse("Pin Code getting failed !!");
        }
        return returnResponse("Pin Code should not be empty !!");
    }
    
    private static function getStateNameByCode($code)
    {
        Log::debug(__CLASS__." :: ".__FUNCTION__." called with $code");
        if(!empty($code)){
            $stateArr = DB::table('states')
            ->select('name')
            ->where('code', '=', $code)->first();
            if(isset($stateArr->name)){
                return $stateArr->name;
            }
        }
      
      return "";
    }
   
}
