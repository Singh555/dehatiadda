<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Models\Web\Currency;
use App\Models\Web\Index;
use App\Models\Web\Languages;
use App\Models\Web\Shipping;
use Illuminate\Http\Request;
//use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Redirect;
use Lang;
use Log;
use App\Models\Web\VendorsModel;

class VendorsController extends Controller
{

    public function __construct(
        Index $index,
        Languages $languages,
        Currency $currency,
        Shipping $shipping
    ) {
        $this->index = $index;
        $this->languages = $languages;
        $this->currencies = $currency;
        $this->shipping = $shipping;
        $this->theme = new ThemeController();
    }

    public function signup(Request $request)
    {
        $final_theme = $this->theme->theme();
        
            $title = array('pageTitle' => Lang::get("website.Sign Up"));
            $result = array();
            $result['commonContent'] = $this->index->commonContent();
            $result['countries'] = $this->shipping->countries();
            
            return view("web.vendor.register", ['title' => $title, 'final_theme' => $final_theme])->with('result', $result);
    }


    public function signupProcess(Request $request)
    {
        Log::info(__CLASS__.'::'.__FUNCTION__.' Called.');
        $shopfname = $request->input('shopfname');
        $shoplname = $request->input('shoplname');
        $gst_no = $request->input('gst_no');
        $address = $request->input('address');
        $country_id = $request->input('country_id');
        $state = $request->input('state');
        $city = $request->input('city');
        $pin_code = $request->input('pin_code');
        $account_number = $request->input('account_number');
        $confirm_account_number = $request->input('confirm_account_number');
        $holder_name = $request->input('holder_name');
        $bank_name = $request->input('bank_name');
        $ifsc_code = $request->input('ifsc_code');
        $email = $request->input('email');
        $pword = $request->input('pword');
        $conf_pword = $request->input('conf_pword');
        $phone = $request->input('phone');
        $first_name = $request->input('first_name');
        $last_name = $request->input('last_name');
        Log::info('Input Data Validation Started');
        $this->validate($request, [
            'shopfname' => 'bail|required',
            'shoplname' => 'bail|required',
            'gst_no' => 'bail|required',
            'address' => 'bail|required',
            'country_id' => 'bail|required',
            'state' => 'bail|required',
            'city' => 'bail|required',
            'pin_code' => 'bail|required|numeric',
            'account_number' => 'bail|required|numeric',
            'confirm_account_number' => 'bail|required|numeric|same:account_number',
            'holder_name' => 'bail|required',
            'bank_name' => 'bail|required',
            'ifsc_code' => 'bail|required',
            'email' => 'bail|required|email|unique:users',
            'pword' => 'bail|required',
            'conf_pword' => 'bail|required|same:pword',
            'phone' => 'bail|required|numeric',
            'first_name' => 'bail|required',
            'last_name' => 'bail|required',
        ]);
        Log::info('All Input Data Validation Success Successfully.');
        VendorsModel::vendorRegistration($shopfname, $shoplname, $gst_no, $address, $country_id, $state, $city, $pin_code, $account_number, $confirm_account_number,$bank_name, $holder_name, $ifsc_code, $email, $pword, $conf_pword, $phone, $first_name, $last_name);
        return redirect('become-vendor');
    }
}
