<?php

namespace App\Http\Controllers\App;

//validator is builtin class in laravel
use Validator;
use DB;
use DateTime;
use Hash;
use Auth;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\AppModels\Product;
use App\Models\AppModels\ProductModel;
use Carbon\Carbon;

class MyProductController extends Controller
{

	//get allcategories
	public function allcategories(Request $request){
            
           return Product::allcategories($request);
		
	}

	//getallproducts
	public function getallproducts(Request $request){
            
         return  Product::getallproducts($request);
		
	}
    
    //get Product List // Updated with Eloquent
    public function getProductList(Request $request){
            return ProductModel::getProductList($request);
    }
    
    //getfilters
    public function getfilters(Request $request){
            return ProductModel::getfilters($request);
        }
        

	// likeproduct
	public function likeproduct(Request $request){
           return Product::likeproduct($request);
	}

        //getallproducts
	public function getlikedproducts(Request $request){
            
         return  Product::getlikedproducts($request);
		
	}
        
	// likeProduct
	public function unlikeproduct(Request $request){
             return Product::unlikeproduct($request);
	}

	//getfilterproducts
	public function getfilterproducts(Request $request){
           return Product::getfilterproducts($request);
			
		}

	//getsearchdata
	public function getsearchdata(Request $request){
          return Product::getsearchdata($request);
		
	}
	//getsearchsuggestions
	public function getsearchsuggestions(Request $request){
          return Product::getsearchsuggestions($request);
		
	}
    //gethomesections
    public function getHomeSections(Request $request){
          return Product::getHomeSections($request);
        
    }
	//getquantity
	public function getquantity(Request $request){
           return Product::getquantity($request);
		
	}

	//shippingMethods
	public function shppingbyweight(Request $request){
          return Product::shppingbyweight($request);
		

	}
        /*
         * if ($products_data->products_type == '0') {
                    $stocks = DB::table('inventory')->where('products_id', $products_data->products_id)->where('stock_type', 'in')->sum('stock');
                    $stockOut = DB::table('inventory')->where('products_id', $products_data->products_id)->where('stock_type', 'out')->sum('stock');
                }

                $result[$index]->defaultStock = $stocks - $stockOut;
         */
        

}
