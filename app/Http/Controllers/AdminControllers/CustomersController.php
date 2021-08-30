<?php
namespace App\Http\Controllers\AdminControllers;

use App\Models\Core\Customers;
use App\Models\Core\Images;
use App\Models\Core\Setting;
use App\Models\Core\Languages;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Exception;
use Kyslik\ColumnSortable\Sortable;
use App\Models\Core\User;
use Log;
use Illuminate\Support\Carbon;
use App\Models\Core\WalletModel;
use App\Models\Core\CustomerModel;
class CustomersController extends Controller
{
    //
    public function __construct(Customers $customers, Setting $setting)
    {
        $this->Customers = $customers;
        $this->myVarsetting = new SiteSettingController($setting);
        $this->Setting = $setting;
    }

    public function display()
    {
        $title = Lang::get("labels.ListingCustomers");
        $language_id = '1';

        $customers = $this->Customers->paginator();

        $result = array();
        $index = 0;
        foreach($customers as $customers_data){
            array_push($result, $customers_data);

            $devices = DB::table('devices')->where('user_id','=',$customers_data->id)->orderBy('created_at','DESC')->take(1)->get();
            $result[$index]->devices = $devices;
            $index++;
        }

        $customerData = array();
        $message = array();
        $errorMessage = array();

        $customerData['message'] = $message;
        $customerData['errorMessage'] = $errorMessage;
        $customerData['result'] = $customers;
        $result['commonContent'] = $this->Setting->commonContent();
        return view("admin.customers.index", ["title"=>$title])->with('customers', $customerData)->with('result', $result);
    }
    public function viewDetails(Request $request)
    {
        $title = Lang::get("labels.Customers");
        $language_id = '1';
        $user_id = '';$customers='';$m_walletTxn='';
        $phone = '';$customers='';$s_walletTxn='';
        if($request->filled('user_id')){
           $phone = $request->input('user_id');
            $customers = CustomerModel::sortable(['id'=>'ASC'])
          ->LeftJoin('user_to_address', 'user_to_address.user_id' ,'=', 'customers.id')
          ->LeftJoin('address_book','address_book.address_book_id','=', 'user_to_address.address_book_id')
          ->LeftJoin('countries','countries.countries_id','=', 'address_book.entry_country_id')
          ->LeftJoin('zones','zones.zone_id','=', 'address_book.entry_zone_id')
          ->where('customers.id',$phone)
          ->select('customers.*', 'address_book.entry_gender as entry_gender', 'address_book.entry_company as entry_company',
          'address_book.entry_firstname as entry_firstname', 'address_book.entry_lastname as entry_lastname',
          'address_book.entry_street_address as entry_street_address', 'address_book.entry_suburb as entry_suburb',
          'address_book.entry_postcode as entry_postcode', 'address_book.entry_city as entry_city',
          'address_book.entry_state as entry_state', 'countries.*', 'zones.*')
          ->groupby('customers.id')
          ->first();
            $title = Lang::get("labels.Customers").' | '.$customers->name;
            $m_walletTxn = DB::table('wallet_txn')->where('customer_id',$customers->id)->paginate(10);
        }
       


        $customerData = array();
        $message = array();
        $errorMessage = array();

        $customerData['message'] = $message;
        $customerData['errorMessage'] = $errorMessage;
        $customerData['result'] = $customers;
        $customerData['mwallet'] = $m_walletTxn;
        
        $result['commonContent'] = $this->Setting->commonContent();
        return view("admin.customers.view", ["title"=>$title])->with('customers', $customerData)->with('result', $result)->with('phone', $phone);
    }
    
    public function mwalletTxn(Request $request)
    {
        $title = Lang::get("labels.Customers");
        $language_id = '1';
        $phone = '';$customers='';$m_walletTxn='';
        if($request->filled('user_id')){
           $phone = $request->input('user_id');
            $customers = CustomerModel::sortable(['id'=>'ASC'])
          ->LeftJoin('user_to_address', 'user_to_address.user_id' ,'=', 'customers.id')
          ->LeftJoin('address_book','address_book.address_book_id','=', 'user_to_address.address_book_id')
          ->LeftJoin('countries','countries.countries_id','=', 'address_book.entry_country_id')
          ->LeftJoin('zones','zones.zone_id','=', 'address_book.entry_zone_id')
          ->where('customers.id',$phone)
          ->select('customers.*', 'address_book.entry_gender as entry_gender', 'address_book.entry_company as entry_company',
          'address_book.entry_firstname as entry_firstname', 'address_book.entry_lastname as entry_lastname',
          'address_book.entry_street_address as entry_street_address', 'address_book.entry_suburb as entry_suburb',
          'address_book.entry_postcode as entry_postcode', 'address_book.entry_city as entry_city',
          'address_book.entry_state as entry_state', 'countries.*', 'zones.*')
          ->groupby('customers.id')
          ->first();
            $title = Lang::get("labels.MwalletTxn").' | '.$customers->name;
            $m_walletTxn = DB::table('wallet_txn')->where('customer_id',$customers->id)->get();
        }
       


        $customerData = array();
        $message = array();
        $errorMessage = array();

        $customerData['message'] = $message;
        $customerData['errorMessage'] = $errorMessage;
        $customerData['result'] = $customers;
        $customerData['mwallet'] = $m_walletTxn;
        $result['commonContent'] = $this->Setting->commonContent();
        return view("admin.customers.mwalletview", ["title"=>$title])->with('customers', $customerData)->with('result', $result)->with('phone', $phone);
    }
    
    public function swalletTxn(Request $request)
    {
        $title = Lang::get("labels.Customers");
        $language_id = '1';
        $phone = '';$customers='';$s_walletTxn='';
        if($request->filled('user_id')){
           $phone = $request->input('user_id');
            $customers = User::sortable(['id'=>'ASC'])
          ->LeftJoin('user_to_address', 'user_to_address.user_id' ,'=', 'users.id')
          ->LeftJoin('address_book','address_book.address_book_id','=', 'user_to_address.address_book_id')
          ->LeftJoin('countries','countries.countries_id','=', 'address_book.entry_country_id')
          ->LeftJoin('zones','zones.zone_id','=', 'address_book.entry_zone_id')
          ->where('role_id',2)
          ->where('users.phone',$phone)
          ->select('users.*', 'address_book.entry_gender as entry_gender', 'address_book.entry_company as entry_company',
          'address_book.entry_firstname as entry_firstname', 'address_book.entry_lastname as entry_lastname',
          'address_book.entry_street_address as entry_street_address', 'address_book.entry_suburb as entry_suburb',
          'address_book.entry_postcode as entry_postcode', 'address_book.entry_city as entry_city',
          'address_book.entry_state as entry_state', 'countries.*', 'zones.*')
          ->groupby('users.id')
          ->first();
            $title = Lang::get("labels.SwalletTxn").' | '.$customers->name;
            $s_walletTxn = DB::table('swallet_txn')->where('user_id',$customers->id)->get();
        }
       


        $customerData = array();
        $message = array();
        $errorMessage = array();

        $customerData['message'] = $message;
        $customerData['errorMessage'] = $errorMessage;
        $customerData['result'] = $customers;
        $customerData['swallet'] = $s_walletTxn;
        $result['commonContent'] = $this->Setting->commonContent();
        return view("admin.customers.swalletview", ["title"=>$title])->with('customers', $customerData)->with('result', $result)->with('phone', $phone);
    }
    
    
    
    //withdrawal requests
    public function withdrawRequest()
    {
        $title = Lang::get("labels.ListingWithdrawals");
        $language_id = '1';

        $withdrawalData = DB::table('withdrawal_request')
                ->LeftJoin('users', 'users.id' ,'=', 'withdrawal_request.customer_id')
                 ->select('withdrawal_request.*','users.id as user_id','users.phone as phone','users.name as name','users.is_prime as is_prime','users.m_wallet as m_wallet')
                ->orderBy('withdrawal_request.created_at','DESC')
                ->paginate(20);

        

        $customerData = array();
        $message = array();
        $errorMessage = array();

        $customerData['message'] = $message;
        $customerData['errorMessage'] = $errorMessage;
        $customerData['result'] = $withdrawalData;
        $result['commonContent'] = $this->Setting->commonContent();
        return view("admin.customers.withdrawalRequests", ["title"=>$title])->with('customers', $customerData)->with('result', $result);
    }
    
    //Kyc requests
    public function kycRequest()
    {
        $title = Lang::get("labels.ListingKyc");
        $language_id = '1';

        $withdrawalData = DB::table('customers_kyc')
                ->LeftJoin('customers', 'customers.id' ,'=', 'customers_kyc.customers_id')
                 ->select('customers_kyc.*','customers.id as user_id','customers.email as email','customers.name as name')
                ->orderBy('customers_kyc.created_at','DESC')
                ->paginate(20);

        

        $customerData = array();
        $message = array();
        $errorMessage = array();

        $customerData['message'] = $message;
        $customerData['errorMessage'] = $errorMessage;
        $customerData['result'] = $withdrawalData;
        $result['commonContent'] = $this->Setting->commonContent();
        return view("admin.customers.kycRequests", ["title"=>$title])->with('customers', $customerData)->with('result', $result);
    }
    //Kyc requests
    public function kycDisplay(Request $request)
    {
        $title = Lang::get("labels.viewKyc");
        $id = $request->input('id');
        $kycData = DB::table('customers_kyc')
                ->LeftJoin('customers', 'customers.id' ,'=', 'customers_kyc.customers_id')
                 ->select('customers_kyc.*','customers.id as user_id','customers.email as email','customers.name as name')
                ->where('customers_kyc.id',$id)
                ->first();

        

        $customerData = array();
        $message = array();
        $errorMessage = array();

        $customerData['message'] = $message;
        $customerData['errorMessage'] = $errorMessage;
        $customerData['result'] = $kycData;
        $result['commonContent'] = $this->Setting->commonContent();
        return view("admin.customers.kycDisplay", ["title"=>$title])->with('customers', $customerData)->with('result', $result);
    }
    
    //update kyc status
    public function kycUpdate(Request $request) {
         try{
                 DB::beginTransaction();
        $id  =	$request->id;
        $status  =	$request->status;
        $description  = htmlspecialchars(strip_tags($request->description));
        $data =  DB::table('customers_kyc')->where('id',$id)->where('status','PENDING')->first();
        
         if(isset($data->user_id)){
             $userData = CustomerModel::find($data->customers_id);
             if(!isset($userData->id)){
                 session()->flash('error', 'Error Occured!');
             }
                  $userData->kyc = $status;
                  $userData->updated_at = Carbon::now();
                
                    if($status =='REJECTED'){
                      $updated2 = DB::table('customers_kyc')->where('id',$id)->update(['status'=>$status,'updated_at'=>Carbon::now(),'reason'=>$description,'rejected_at'=>Carbon::now()]);
                    }else{
                      $updated2 = DB::table('customers_kyc')->where('id',$id)->update(['status'=>$status,'updated_at'=>Carbon::now(),'reason'=>$description,'verified_at'=>Carbon::now()]);
                    }
                    
                    if ($userData->save() && $updated2) {
                        DB::commit();
                        session()->flash('message', "Kyc request has been $status successfully!");
                    } else{
                        Log::error("Error Occured at kyc request update ");
                        session()->flash('error', 'Some Error Occured please try again !');
                    }
                
                
         }else{
             session()->flash('error', 'Error Occured!');
         }
         
         }catch(\Exception $e){
                
                Log::error("Error Occured".$e->getMessage());
            }
            return redirect('admin/customers/kyc');
    }
    
    //reject withdrawal request
    public function rejectWithdrawRequest(Request $request){
        try{
                 DB::beginTransaction();
        $id  =	$request->id;
        $description  =	$request->description;
        $data =  DB::table('withdrawal_request')->where('id',$id)->where('status','REQUESTED')->first();
        
         if(isset($data->customer_id)){
             $userData = User::find($data->customer_id);
             if(!isset($userData->id)){
                 session()->flash('error', 'Error Occured!');
             }
             $before_balance = $userData->m_wallet;
             $amount = $data->amount;
                $after_balance = $before_balance + $amount;
                
                Log::debug("before balance $before_balance");
                Log::debug("after balance $after_balance");
                
                if(WalletModel::creditInMainWallet($userData->id, $amount, $after_balance, $description, $id, 'WITHDRAWAL')){
                    $updated2 = DB::table('withdrawal_request')->where('id',$id)->update(['status'=>'REJECTED','updated_by'=>auth()->user()->id,'updated_at'=>Carbon::now()]);
                    if ($updated2) {
                       DB::commit();
                       session()->flash('message', 'Withdrawal request has been rejected successfully!');
        
                    }else{
                       Log::error("Error Occured at wallet update ");
                        session()->flash('error', 'Some Error Occured please try again !');  
                    }
                }else{
                    Log::error("Error while crediting amount in main wallet");
                    session()->flash('error', 'Error while crediting amount in main wallet!');
                }
                
         }else{
             session()->flash('error', 'Error Occured!');
         }
         
         }catch(\Exception $e){
                
                Log::error("Error Occured".$e->getMessage());
            }
            return redirect('admin/customers/withdrawal');
        
        
    }
    
//pay withdrawal request
    public function payWithdrawRequest(Request $request){
        try{
                 DB::beginTransaction();
        $id  =	$request->id;
        $data =  DB::table('withdrawal_request')->where('id',$id)->where('status','REQUESTED')->first();
        
         if(isset($data->customer_id)){
             $userData = User::find($data->customer_id);
             if(!isset($userData->id)){
                 session()->flash('error', 'Error Occured!');
             }
            
                
                
                    $updated2 = DB::table('withdrawal_request')->where('id',$id)->update(['status'=>'PAID','updated_by'=>auth()->user()->id,'updated_at'=>Carbon::now()]);
                    if ($updated2) {
                        DB::commit();
                        session()->flash('message', 'Withdrawal request has been Paid successfully!');
                    } else{
                      Log::error("Error Occured at wallet update ");
                        session()->flash('error', 'Some Error Occured please try again !');  
                    }
                
                
         }else{
             session()->flash('error', 'Error Occured!');
         }
         
         
        
         }catch(\Exception $e){
                
                Log::error("Error Occured".$e->getMessage());
            }
            return redirect('admin/customers/withdrawal');
        
        
    }
    
    
    
    public function orders(Request $request)
    {
        $title = Lang::get("labels.ListingOrders");        
       $customer_id = $request->customer_id;
        $message = array();
        $errorMessage = array();        
        
        $ordersData['orders'] = $this->Customers->Orders($customer_id);
        $ordersData['message'] = $message;
        $ordersData['errorMessage'] = $errorMessage;
        $ordersData['currency'] = $this->myVarsetting->getSetting(); 
        $result['commonContent'] = $this->Setting->commonContent();
        return view("admin.customers.order", ["title"=>$title])->with('listingOrders', $ordersData)->with('result', $result);
    }

    public function add(Request $request)
    {
        $title =  Lang::get("labels.AddCustomer");
        $images = new Images;
        $allimage = $images->getimages();
        $language_id = '1';
        $customerData = array();
        $message = array();
        $errorMessage = array();
        $customerData['countries'] = $this->Customers->countries();
        $customerData['message'] = $message;
        $customerData['errorMessage'] = $errorMessage;
        $result['commonContent'] = $this->Setting->commonContent();
        return view("admin.customers.add", ["title"=>$title])->with('customers', $customerData)->with('allimage',$allimage)->with('result', $result);
    }


    //add addcustomers data and redirect to address
    public function insert(Request $request)
    {
        $language_id = '1';
        //get function from other controller
        $images = new Images;
        $allimage = $images->getimages();

        $customerData = array();
        $message = array();
        $errorMessage = array();

        //check email already exists
        $existEmail = $this->Customers->email($request);
        $this->validate($request, [
            'customers_firstname' => 'required',
            'customers_lastname' => 'required',
           
            'customers_telephone' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'isActive' => 'required',
        ]);


        if (count($existEmail)> 0 ) {
            $messages = Lang::get("labels.Email address already exist");
            return Redirect::back()->withErrors($messages)->withInput($request->all());
        } else {
            $customers_id = $this->Customers->insert($request);
            return redirect('admin/customers/address/display/' . $customers_id)->with('update', 'Customer has been created successfully!');
        }
    }

    public function diplayaddress(Request $request){

        $title = Lang::get("labels.AddAddress");

        $language_id   				=   $request->language_id;
        $id            				=   $request->id;

        $customerData = array();
        $message = array();
        $errorMessage = array();

        $customer_addresses = $this->Customers->addresses($id);
        $countries = $this->Customers->country();

        $customerData['message'] = $message;
        $customerData['errorMessage'] = $errorMessage;
        $customerData['customer_addresses'] = $customer_addresses;
        $customerData['countries'] = $countries;
        $customerData['user_id'] = $id;
        $result['commonContent'] = $this->Setting->commonContent();

        return view("admin.customers.address.index",["title"=>$title])->with('data', $customerData)->with('result', $result);
    }


    //add Customer address
    public function addcustomeraddress(Request $request){
      $customer_addresses = $this->Customers->addcustomeraddress($request);
      return $customer_addresses;
    }

    public function editaddress(Request $request){

      $user_id                 =   $request->user_id;
      $address_book_id         =   $request->address_book_id;

      $customer_addresses = $this->Customers->addressBook($address_book_id);
      $countries = $this->Customers->countries();;
      $zones = $this->Customers->zones($customer_addresses);
      $customers = $this->Customers->checkdefualtaddress($address_book_id);

      $customerData['user_id'] = $user_id;
      $customerData['customer_addresses'] = $customer_addresses;
      $customerData['countries'] = $countries;
      $customerData['zones'] = $zones;
      $customerData['customers'] = $customers;
      $result['commonContent'] = $this->Setting->commonContent();

      return view("admin/customers/address/editaddress")->with('data', $customerData)->with('result', $result);
    }

    //update Customers address
    public function updateaddress(Request $request){
      $customer_addresses = $this->Customers->updateaddress($request);
      return ($customer_addresses);
    }

    public function deleteAddress(Request $request){
      $customer_addresses = $this->Customers->deleteAddresses($request);
      return redirect()->back()->withErrors([Lang::get("labels.Delete Address Text")]);
    }

    //editcustomers data and redirect to address
    public function edit(Request $request){

      $images           = new Images;
      $allimage         = $images->getimages();
      $title            = Lang::get("labels.EditCustomer");
      $language_id      =   '1';
      $id               =   $request->id;

      $customerData = array();
      $message = array();
      $errorMessage = array();
      $customers = $this->Customers->edit($id);

      $customerData['message'] = $message;
      $customerData['errorMessage'] = $errorMessage;
      $customerData['countries'] = $this->Customers->countries();
      $customerData['customers'] = $customers;
      $result['commonContent'] = $this->Setting->commonContent();

      return view("admin.customers.edit",["title"=>$title])->with('data', $customerData)->with('result', $result)->with('allimage', $allimage);
    }

    //add addcustomers data and redirect to address
    public function update(Request $request){
        $language_id  =   '1';
        $user_id				  =	$request->customers_id;

        $customerData = array();
        $message = array();
        $errorMessage = array();

        //get function from other controller
        if($request->image_id!==null){
            $customers_picture = $request->image_id;
        }	else{
            $customers_picture = $request->oldImage;
        }

        if($request->image_id){
            $uploadImage = $request->image_id;
            $uploadImage = DB::table('image_categories')->where('image_id',$uploadImage)->select('path')->first();
            $customers_picture = $uploadImage->path;
        }	else{
            $customers_picture = $request->oldImage;
        }

        $user_data = array(
            'gender'   		 	=>   $request->gender,
            'name'		=>   $request->name,
            'last_name'		 	=>   $request->last_name,
            'dob'	 			 	  =>	 $request->dob,
            'phone'	 	      =>	 $request->phone,
            'status'		    =>   $request->status,
            'avatar'	 		  =>	 $customers_picture,
            'updated_at'    => date('Y-m-d H:i:s'),
        );
        $customer_data = array(
          'customers_newsletter'   		 	=>   0,
          'updated_at'    => date('Y-m-d H:i:s'),
        );

        if($request->changePassword == 'yes'){
            $user_data['password'] = Hash::make($request->password);
        }

        $this->Customers->updaterecord($customer_data,$user_id,$user_data);
        return redirect('admin/customers/display')->with('message', 'Customer details has been updated successfully!');
        
    }

    public function delete(Request $request){
      $this->Customers->destroyrecord($request->users_id);
      return redirect()->back()->withErrors([Lang::get("labels.DeleteCustomerMessage")]);
    }

    public function filter(Request $request){
      $filter    = $request->FilterBy;
      $parameter = $request->parameter;

      $title = Lang::get("labels.ListingCustomers");
      $customers  = $this->Customers->filter($request);

      $result = array();
      $index = 0;
      foreach($customers as $customers_data){
          array_push($result, $customers_data);

          $devices = DB::table('devices')->where('user_id','=',$customers_data->id)->orderBy('created_at','DESC')->take(1)->get();
          $result[$index]->devices = $devices;
          $index++;
      }

      $customerData = array();
      $message = array();
      $errorMessage = array();

      $customerData['message'] = $message;
      $customerData['errorMessage'] = $errorMessage;
      $customerData['result'] = $customers;
      $result['commonContent'] = $this->Setting->commonContent();

      return view("admin.customers.index",["title"=>$title])->with('result', $result)->with('customers', $customerData)->with('filter',$filter)->with('parameter',$parameter);
    }
    
    public function getCustomerListAjax(Request $request)
    {
        
            $str = htmlspecialchars($request->input('str'));
        
        if(strlen($str) > 3)
        {
            $dataArray = CustomerModel::where('status',1)->where('id','like','%'.$str.'%')->orWhere('member_code','like','%'.$str.'%')->orWhere('name','like','%'.$str.'%')->orWhere('email','like','%'.$str.'%')->limit(10)->get();
            
                if (count($dataArray) > 0)
                { ?>
                    <ul id="suggestion-list">
                <?php
                        foreach ($dataArray as $obj) {
                           
                        ?>
                            <li onClick="selectMember('<?php echo $obj->id; ?>');"><?php echo $obj->id." [ Name ".$obj->name.", Email. ".$obj->email.", Referral code ".$obj->member_code." ]"; ?></li>
                       <?php } ?>
                    </ul>
               <?php }
                else
                {
                    echo "no_data";
                }
            
        }
    }
    
    ########################
    #Mlm Data view Functions
    #######################
    
    //view Prime referrals 
    public function viewPrimeReferral(Request $request)
    {
        $title = Lang::get("labels.Customers");
        $phone = '';$customers='';$childData='';
        if($request->filled('user_id')){
           $phone = $request->input('user_id');
            $customers = User::sortable(['id'=>'ASC'])
          ->LeftJoin('user_to_address', 'user_to_address.user_id' ,'=', 'users.id')
          ->LeftJoin('address_book','address_book.address_book_id','=', 'user_to_address.address_book_id')
          ->LeftJoin('countries','countries.countries_id','=', 'address_book.entry_country_id')
          ->LeftJoin('zones','zones.zone_id','=', 'address_book.entry_zone_id')
          ->where('role_id',2)
          ->where('users.phone',$phone)
          ->select('users.*', 'address_book.entry_gender as entry_gender', 'address_book.entry_company as entry_company',
          'address_book.entry_firstname as entry_firstname', 'address_book.entry_lastname as entry_lastname',
          'address_book.entry_street_address as entry_street_address', 'address_book.entry_suburb as entry_suburb',
          'address_book.entry_postcode as entry_postcode', 'address_book.entry_city as entry_city',
          'address_book.entry_state as entry_state', 'countries.*', 'zones.*')
          ->groupby('users.id')
          ->first();
            $title = Lang::get("labels.PrimeReferral").' | '.$customers->name;
            $childData = User::sortable(['id'=>'ASC'])->where('prime_referral',$customers->member_code)->where('role_id',2)->where('id','!=',$customers->id)->where('is_prime','Y')->get();
        }
       


        $customerData = array();
        $message = array();
        $errorMessage = array();

        $customerData['message'] = $message;
        $customerData['errorMessage'] = $errorMessage;
        $customerData['result'] = $customers;
        $customerData['childs'] = $childData;
        $result['commonContent'] = $this->Setting->commonContent();
        return view("admin.customers.primeReferralview", ["title"=>$title])->with('customers', $customerData)->with('result', $result)->with('phone', $phone);
    }
    
    //view non prime referrals
    public function viewNonPrimeReferral(Request $request)
    {
        $title = Lang::get("labels.Customers");
        $phone = '';$customers='';$childData='';
        if($request->filled('user_id')){
           $phone = $request->input('user_id');
            $customers = User::sortable(['id'=>'ASC'])
          ->LeftJoin('user_to_address', 'user_to_address.user_id' ,'=', 'users.id')
          ->LeftJoin('address_book','address_book.address_book_id','=', 'user_to_address.address_book_id')
          ->LeftJoin('countries','countries.countries_id','=', 'address_book.entry_country_id')
          ->LeftJoin('zones','zones.zone_id','=', 'address_book.entry_zone_id')
          ->where('role_id',2)
          ->where('users.phone',$phone)
          ->select('users.*', 'address_book.entry_gender as entry_gender', 'address_book.entry_company as entry_company',
          'address_book.entry_firstname as entry_firstname', 'address_book.entry_lastname as entry_lastname',
          'address_book.entry_street_address as entry_street_address', 'address_book.entry_suburb as entry_suburb',
          'address_book.entry_postcode as entry_postcode', 'address_book.entry_city as entry_city',
          'address_book.entry_state as entry_state', 'countries.*', 'zones.*')
          ->groupby('users.id')
          ->first();
            $title = Lang::get("labels.NonPrimeReferral").' | '.$customers->name;
            $childData = User::sortable(['id'=>'ASC'])->where('normal_referral',$customers->member_code)->where('role_id',2)->where('id','!=',$customers->id)->where('is_prime','N')->get();
        }
       


        $customerData = array();
        $message = array();
        $errorMessage = array();

        $customerData['message'] = $message;
        $customerData['errorMessage'] = $errorMessage;
        $customerData['result'] = $customers;
        $customerData['childs'] = $childData;
        $result['commonContent'] = $this->Setting->commonContent();
        return view("admin.customers.nonPrimeReferralview", ["title"=>$title])->with('customers', $customerData)->with('result', $result)->with('phone', $phone);
    }
    
    //view Team List by level
    public function viewTeamListByLevel(Request $request)
    {
        $title = Lang::get("labels.Customers");
        $phone = '';$customers='';$childData='';
        $level = '';
        if($request->filled('user_id') && $request->filled('level')){
           $phone = $request->input('user_id');
           $level = $request->input('level');
            $customers = CustomerModel::sortable(['id'=>'ASC'])
          ->LeftJoin('user_to_address', 'user_to_address.user_id' ,'=', 'customers.id')
          ->LeftJoin('address_book','address_book.address_book_id','=', 'user_to_address.address_book_id')
          ->LeftJoin('countries','countries.countries_id','=', 'address_book.entry_country_id')
          ->LeftJoin('zones','zones.zone_id','=', 'address_book.entry_zone_id')
          ->where('customers.id',$phone)
          ->select('customers.*', 'address_book.entry_gender as entry_gender', 'address_book.entry_company as entry_company',
          'address_book.entry_firstname as entry_firstname', 'address_book.entry_lastname as entry_lastname',
          'address_book.entry_street_address as entry_street_address', 'address_book.entry_suburb as entry_suburb',
          'address_book.entry_postcode as entry_postcode', 'address_book.entry_city as entry_city',
          'address_book.entry_state as entry_state', 'countries.*', 'zones.*')
          ->groupby('customers.id')
          ->first();
            $title = Lang::get("labels.TeamList").' | '.$customers->name;
            
            $parentIds = array();
            $memberIds = array();
            array_push($parentIds, $customers->id);
            $max_loop_count = 8;
            if($level > 0){
                $max_loop_count=$level;
            }
            for($l=0;$l<$max_loop_count;$l++){
                $chiildIds = CustomerModel::sortable(['id'=>'ASC'])->whereIn('parent_id',$parentIds)->where('id','!=',$customers->id)->pluck('id');
                        if(count($chiildIds) > 0){
                            unset($parentIds);
                            $parentIds = array();
                            Log::debug('Parent ids after cleanng'.json_encode($parentIds));
                            Log::debug('child ids before loop'.json_encode($chiildIds));
                            for ($i=0;$i<count($chiildIds);$i++) {
                                if(!in_array($chiildIds[$i], $parentIds)){
                                array_push($parentIds, $chiildIds[$i]);
                                }
                                
                            }
                            if($level >0){
                                    if(($max_loop_count-1) == $l){
                                        for ($i=0;$i<count($chiildIds);$i++) {
                                        if(!in_array($chiildIds[$i], $memberIds)){
                                            array_push($memberIds, $chiildIds[$i]);
                                        }
                                
                                       }
                                        
                                        break;
                                    }
                                }
                                else{
                                    for ($i=0;$i<count($chiildIds);$i++) {
                                        if(!in_array($chiildIds[$i], $memberIds)){
                                            array_push($memberIds, $chiildIds[$i]);
                                        }
                                
                                       }
                                }
                        }else{
                           break; 
                        }
                        /*
                if($level > 0){
                    if(($max_loop_count-1) == $l){
                    for ($i=0;$i<count($chiildIds);$i++) {
                                if(!in_array($chiildIds[$i], $memberIds)){
                                    array_push($memberIds, $chiildIds[$i]);
                                }
                            }
                        break;
                    }
                }else{
                    for ($i=0;$i<count($chiildIds);$i++) {
                                if(!in_array($chiildIds[$i], $memberIds)){
                                array_push($memberIds, $chiildIds[$i]);
                                }
                            }
                }*/
            }
            Log::debug("parent Ids".json_encode($memberIds));
            Log::debug("level ".$level);
            $childData = CustomerModel::sortable(['id'=>'ASC'])->whereIn('id',$memberIds)->where('id','!=',$customers->id)->get();
        }
       


        $customerData = array();
        $message = array();
        $errorMessage = array();

        $customerData['message'] = $message;
        $customerData['errorMessage'] = $errorMessage;
        $customerData['result'] = $customers;
        $customerData['childs'] = $childData;
        $result['commonContent'] = $this->Setting->commonContent();
        return view("admin.customers.teamListview", ["title"=>$title])->with('customers', $customerData)->with('result', $result)->with('phone', $phone)->with('level', $level);
    }
    
    
}
