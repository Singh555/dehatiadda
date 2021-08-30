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
use Illuminate\Support\Facades\File;
use App\Models\Web\Shop;

class ShopController extends Controller
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

    public function viewForm(Request $request)
    {
        $final_theme = $this->theme->theme();
        
            $title = array('pageTitle' => Lang::get("website.Sign Up"));
            $result = array();
            $result['commonContent'] = $this->index->commonContent();
            $result['countries'] = $this->shipping->countries();
            
            return view("web.shop.register", ['title' => $title, 'final_theme' => $final_theme])->with('result', $result);
    }


    public function save(Request $request)
    {
        Log::info(__CLASS__.'::'.__FUNCTION__.' Called.');
        
        Log::info('Input Data Validation Started');
        $this->validate($request, [
            'shop_name' => 'bail|required',
            'shop_gst_no' => 'bail|required|unique:shops,gst_no',
            'address' => 'bail|required',
            'country' => 'bail|required',
            'state' => 'bail|required',
            'city' => 'bail|required',
            'pin_code' => 'bail|required|numeric',
            'phone' => 'bail|required|numeric|unique:shops,phone',
            'contact_person_name' => 'bail|required',
            'shop_image' => 'required|mimes:jpg,jpeg,png,bmp,tiff|max:5000000',
            'gst_image' => 'required|mimes:jpg,jpeg,png,bmp,tiff|max:5000000',
            'shop_logo' => 'nullable|mimes:jpg,jpeg,png,bmp,tiff|max:5000000',
            'contact_person_phone' => 'bail|required|numeric',
        ]);
        Log::info('All Input Data Validation Success Successfully Now recieving them.');
        
        $shop_name = htmlspecialchars(strip_tags($request->input('shop_name')));
        $gst_no = htmlspecialchars(strip_tags($request->input('shop_gst_no')));
        $address = htmlspecialchars(strip_tags($request->input('address')));
        $country = htmlspecialchars(strip_tags($request->input('country')));
        $state = htmlspecialchars(strip_tags($request->input('state')));
        $city = htmlspecialchars(strip_tags($request->input('city')));
        $pin_code = htmlspecialchars(strip_tags($request->input('pin_code')));
        $email = htmlspecialchars(strip_tags($request->input('email')));
        $phone = htmlspecialchars(strip_tags($request->input('phone')));
        $cperson_name = htmlspecialchars(strip_tags($request->input('contact_person_name')));
        $cperson_phone = htmlspecialchars(strip_tags($request->input('contact_person_phone')));
        $shop_image = $request->file('shop_image');
        $gst_image = $request->file('gst_image');
        Log::debug('image found as'.$shop_image->path());
        $shop_logo = '';
         if($request->hasFile('shop_logo')){
        $shop_logo = $request->file('shop_logo');
         }
        
        
        Shop::registration($shop_name, $gst_no, $address, $country, $state, $city, $pin_code, $email, $phone, $cperson_name, $cperson_phone,$shop_image,$gst_image,$shop_logo);
        return redirect('list-shop');
    }
}
