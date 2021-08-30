<?php
namespace App\Http\Controllers\App;

use Illuminate\Routing\Controller;
use App\Models\AppModels\PaymentGatewayModel;
use Illuminate\Http\Request;

class PaymentgatewayController extends Controller
{
    
    public function validatePayment(Request $request)
    {
        return PaymentGatewayModel::validatePayment($request);
    }
    public function validatechashFreePayment(Request $request)
    {
        return PaymentGatewayModel::validatechashFreePayment($request);
    }
    
    //paymenterror
    public function paymenterror(Request $request){
      return PaymentGatewayModel::updatePaymentCanceled($request);
    }
    
}
