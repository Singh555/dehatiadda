<?php
namespace App\Http\Controllers\Web;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Lang;
use App\Models\Web\Index;
use Session;
use Illuminate\Support\Facades\Log;
use App\Models\Web\PaymentGatewayModel;
use Illuminate\Support\Facades\Response;

class RazorpayController  extends Controller
{
	public function __construct(Index $index)
	{
		$this->index = $index;

	}
		public function pay() {
			$title = array('pageTitle' => Lang::get("website.Home"));
			$this->theme = new ThemeController();
			$final_theme = $this->theme->theme();
			$result['commonContent'] = $this->index->commonContent();
                        $data = session()->get('data');
                        if(!empty($data)){
                            session()->put('data', $data);
                        }
                        $txnDetail = PaymentGatewayModel::get_customer_id_by_txn_id($data['txn_id']);
                        $razorpayKey = PaymentGatewayModel::get_payment_methods_detail_value_by_key(2, "RAZORPAY_KEY")->value;
			return view('web.pay',['title' => $title,'final_theme' => $final_theme])->with('result', $result)
                                ->with('txnData', $data)
                                ->with('txnDetail', $txnDetail)
                                ->with('razorpayKey', $razorpayKey)
                                ;
	}

	public function dopayment(Request $request) {
            
            $result = PaymentGatewayModel::validatePayment($request);
                if($result){
                   // return 'thankyou';
                  return Response::json(array('status'=>'success'));
                }	
                return  Response::json(array('status'=>'error'));
	}

}
