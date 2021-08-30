<?php
namespace App\Http\Controllers\App\FreeShipping;

use Log;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\AppModels\FreeShipping\Shop;

class ShopController extends Controller
{

    public function getShopList(Request $request){
        Log::debug(__CLASS__." :: ".__FUNCTION__." called");
        return Shop::getShopList($request);
    }
    
    public function searchShop(Request $request){
        Log::debug(__CLASS__." :: ".__FUNCTION__." called");
        return Shop::searchShop($request);
    }
    
    public function validateQrCode(Request $request){
        Log::debug(__CLASS__." :: ".__FUNCTION__." called");
        return Shop::validateQrCode($request);
    }
    public function confirmQrPayment(Request $request){
        Log::debug(__CLASS__." :: ".__FUNCTION__." called");
        return Shop::confirmQrPayment($request);
    }
}
