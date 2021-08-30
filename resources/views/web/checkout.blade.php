@extends('web.layout')
@section('content')

<!-- checkout Content -->
<section class="checkout-area">

@if(session::get('paytm') == 'success')
@php Session(['paytm' => 'sasa']); @endphp
<script>
jQuery(document).ready(function() {
 // executes when HTML-Document is loaded and DOM is ready
 jQuery("#update_cart_form").submit();
});

</script>
@endif

<div class="container-fuild">
  <nav aria-label="breadcrumb">
      <div class="container">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ URL::to('/')}}">@lang('website.Home')</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0)">@lang('website.Checkout')</a></li>
            <li class="breadcrumb-item">
              <a href="javascript:void(0)">
                @if(session('step')==0)
                      @lang('website.Shipping Address')
                    @elseif(session('step')==1)
                      @lang('website.Billing Address')
                    @elseif(session('step')==2)
                      @lang('website.Shipping Methods')
                    @elseif(session('step')==3)
                      @lang('website.Order Detail')
                    @endif
              </a>
            </li>
          </ol>
      </div>
    </nav>
</div> 
<section class="pro-content">

  <div class="container">
    <div class="page-heading-title">
      <h2> @lang('website.Checkout') </h2>

      </div>
  </div>
 <!-- checkout Content -->
 <section class="checkout-area">
 <div class="container">
   <div class="row">
     
     <div class="col-12 col-xl-9 checkout-left">
       <input type="hidden" id="hyperpayresponse" value="@if(!empty(session('paymentResponse'))) @if(session('paymentResponse')=='success') {{session('paymentResponse')}} @else {{session('paymentResponse')}}  @endif @endif">
       
       <div class="alert alert-danger alert-dismissible" id="paymentError" role="alert" style="display:none;">
           <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
           @if(!empty(session('paymentResponse')) and session('paymentResponse')=='error') {{session('paymentResponseData') }} @endif
       </div>
       @include('web.common.feedback')
         <div class="row">
           <div class="checkout-module">
             <ul class="nav nav-pills mb-3 checkoutd-nav d-none d-lg-flex" id="pills-tab" role="tablist">
                 <li class="nav-item">
                   <a class="nav-link @if(session('step')==0) active @elseif(session('step')>0)  @endif" id="pills-shipping-tab" data-toggle="pill" href="#pills-shipping" role="tab" aria-controls="pills-shipping" aria-selected="true">
                    <span class="d-flex d-lg-none">1</span>
                    <span class="d-none d-lg-flex">@lang('website.Shipping Address')</span></a>
                 </li>
                 <li class="nav-item">
                   <a class="nav-link @if(session('step')==1) active @elseif(session('step')>1) @endif" @if(session('step')>=1) id="pills-billing-tab" data-toggle="pill" href="#pills-billing" role="tab" aria-controls="pills-billing" aria-selected="false"  @endif >@lang('website.Billing Address')</a>
                 </li>
                 
                 <li class="nav-item">
                     <a class="nav-link @if(session('step')==2) active @elseif(session('step')>2) @endif"  @if(session('step')>=2) id="pills-order-tab" data-toggle="pill" href="#pills-order" role="tab" aria-controls="pills-order" aria-selected="false"@endif>@lang('website.Order Detail')</a>
                   </li>
               </ul>
               <ul class="nav nav-pills mb-3 checkoutd-nav d-flex d-lg-none" id="pills-tab" role="tablist">
                 <li class="nav-item">
                   <a class="nav-link @if(session('step')==0) active @elseif(session('step')>0) active-check @endif" id="pills-shipping-tab" data-toggle="pill" href="#pills-shipping" role="tab" aria-controls="pills-shipping" aria-selected="true">1</a>
                 </li>
                 <li class="nav-item second">
                   <a class="nav-link @if(session('step')==1) active @elseif(session('step')>1) active-check @endif" @if(session('step')>=1) id="pills-billing-tab" data-toggle="pill" href="#pills-billing" role="tab" aria-controls="pills-billing" aria-selected="false"  @endif >2</a>
                 </li>
                 
                 <li class="nav-item fourth">
                   <a class="nav-link @if(session('step')==2) active @elseif(session('step')>2) active-check @endif"  @if(session('step')>=2) id="pills-order-tab" data-toggle="pill" href="#pills-order" role="tab" aria-controls="pills-order" aria-selected="false"@endif>4</a>
                   </li>
               </ul>
               <div class="tab-content" id="pills-tabContent">
                 <div class="tab-pane fade @if(session('step') == 0) show active @endif" id="pills-shipping" role="tabpanel" aria-labelledby="pills-shipping-tab">
                   <form name="signup" enctype="multipart/form-data" class="form-validate"  action="{{ URL::to('/checkout_shipping_address')}}" method="post">
                     <input type="hidden" required name="_token" id="csrf-token" value="{{ Session::token() }}" />
                     <div class="form-row">
                     @if(count($result['all_address'])>0)
                      @foreach($result['all_address'] as $address)
                      <div class="form-group">
                          <label>
                            <input class="radio-inline" type="radio" name="shipping_address" required='' @if($address->default_address =='1') checked=''@endif value="{{$address->address_id}}">
                            {{$address->firstname.', '.$address->street.', '.$address->city.', '.$address->zone_name.', '.$address->country_name.', '.$address->postcode}}
                          </label>
                      </div>
                      @endforeach
                     @endif
                     
                     
                 </div>
                     
                      <div class="form-row">
                        <div class="form-group">
                          <button type="submit"  class="btn swipe-to-top btn-secondary">@lang('website.Continue')</button>
                        </div>
                      </div>
                   </form>
                 </div>
                 <div class="tab-pane fade @if(session('step') == 1) show active @endif"  id="pills-billing" role="tabpanel" aria-labelledby="pills-billing-tab">
                     <form name="signup" enctype="multipart/form-data" action="{{ URL::to('/checkout_billing_address')}}" method="post">
                       <input type="hidden" name="_token" id="csrf-token" value="{{ Session::token() }}" />
                       <div class="form-row">
                         <div class="form-group">
                            <label for=""> @lang('website.First Name')</label>
                             <input type="text" class="form-control same_address" @if(!empty(session('billing_address'))) @if(session('billing_address')->same_billing_address==1) readonly @endif @else readonly @endif  id="billing_firstname" name="billing_firstname" value="@if(!empty(session('billing_address'))){{session('billing_address')->billing_firstname}}@endif" aria-describedby="NameHelp1" placeholder="Enter Your Name">
                             <span class="help-block error-content" hidden>@lang('website.Please enter your first name')</span>
                           </div>
                           <div class="form-group">
                            <label for=""> @lang('website.Last Name')</label>
                             <input type="text" class="form-control same_address" id="exampleInputName2" aria-describedby="NameHelp2" placeholder="Enter Your Name" @if(!empty(session('billing_address'))>0) @if(session('billing_address')->same_billing_address==1) readonly @endif @else readonly @endif  id="billing_lastname" name="billing_lastname" value="@if(!empty(session('billing_address'))>0){{session('billing_address')->billing_lastname}}@endif">
                             <span class="help-block error-content" hidden>@lang('website.Please enter your last name')</span>
                           </div>

                           <div class="form-group" style="display: none;">
                            <label for=""> @lang('website.Company')</label>
                             <input type="text" class="form-control same_address" @if(!empty(session('billing_address'))) @if(session('billing_address')->same_billing_address==1) readonly @endif @else readonly @endif  id="billing_company" name="billing_company" value="@if(!empty(session('billing_address'))){{session('billing_address')->billing_company}}@endif" id="exampleInputCompany1" aria-describedby="companyHelp" placeholder="Enter Your Company Name">
                             <span class="help-block error-content" hidden>@lang('website.Please enter your company name')</span>
                           </div>

                           <div class="form-group">
                            <label for=""> @lang('website.Address')</label>
                             <input type="text" class="form-control same_address" id="exampleInputAddress1" aria-describedby="addressHelp" placeholder="Enter Your Address" @if(!empty(session('22'))>0) @if(session('billing_address')->same_billing_address==1) readonly @endif @else readonly @endif  id="billing_street" name="billing_street" value="@if(!empty(session('billing_address'))>0){{session('billing_address')->billing_street}}@endif">
                             <span class="help-block error-content" hidden>@lang('website.Please enter your address')</span>
                           </div>
                           <div class="form-group">
                            <label for=""> @lang('website.Country')</label>
                             <div class="input-group select-control">
                                 <select required class="form-control same_address_select" id="billing_countries_id" aria-describedby="countryHelp" onChange="getBillingZones();" name="billing_countries_id" @if(!empty(session('billing_address'))) @if(session('billing_address')->same_billing_address==1) disabled @endif @else disabled @endif>
                                   <option value=""  >@lang('website.Select Country')</option>
                                   @if(!empty($result['countries']))
                                     @foreach($result['countries'] as $countries)
                                         <option value="{{$countries->countries_id}}" @if(!empty(session('billing_address'))) @if(session('billing_address')->billing_countries_id == $countries->countries_id) selected @endif @endif >{{$countries->countries_name}}</option>
                                     @endforeach
                                   @endif
                                   </select>
                             </div>
                             <span class="help-block error-content" hidden>@lang('website.Please select your country')</span>
                           </div>
                           <div class="form-group">
                            <label for=""> @lang('website.State')</label>
                             <div class="input-group select-control">
                                 <select required class="form-control same_address_select" name="billing_zone_id" @if(!empty(session('billing_address'))) @if(session('billing_address')->same_billing_address==1) disabled @endif @else disabled @endif id="billing_zone_id" aria-describedby="stateHelp">
                                   <option value="" >@lang('website.Select State')</option>
                                   @if(!empty($result['zones']))
                                     @foreach($result['zones'] as $key=>$zones)
                                         <option value="{{$zones->zone_id}}" @if(!empty(session('billing_address'))) @if(session('billing_address')->billing_zone_id == $zones->zone_id) selected @endif @endif >{{$zones->zone_name}}</option>
                                     @endforeach
                                   @endif
                                     <option value="-1" @if(!empty(session('billing_address'))) @if(session('billing_address')->billing_zone_id == 'Other') selected @endif @endif>@lang('website.Other')</option>
                                   </select>
                             </div>
                             <span class="help-block error-content" hidden>@lang('website.Please select your state')</span>
                           </div>
                           <div class="form-group">
                            <label for=""> @lang('website.City')</label>
                               <input type="text" class="form-control same_address" @if(!empty(session('billing_address'))) @if(session('billing_address')->same_billing_address==1) readonly @endif @else readonly @endif  id="billing_city" name="billing_city" value="@if(!empty(session('billing_address'))){{session('billing_address')->billing_city}}@endif" placeholder="Enter Your City">
                               <span class="help-block error-content" hidden>@lang('website.Please enter your city')</span>
                           </div>
                             <div class="form-group">
                              <label for=""> @lang('website.Zip/Postal Code')</label>
                               <input type="text" class="form-control same_address" @if(!empty(session('billing_address'))) @if(session('billing_address')->same_billing_address==1) readonly @endif @else readonly @endif  id="billing_zip" name="billing_zip" value="@if(!empty(session('billing_address'))){{session('billing_address')->billing_zip}}@endif" aria-describedby="zpcodeHelp" placeholder="Enter Your Zip / Postal Code">
                               <small id="zpcodeHelp" class="form-text text-muted"></small>
                             </div>
                             <div class="form-group">
                              <label for=""> @lang('website.Phone')</label>
                               <input type="text" class="form-control same_address" @if(!empty(session('billing_address'))) @if(session('billing_address')->same_billing_address==1) readonly @endif @else readonly @endif  id="billing_phone" name="billing_phone" value="@if(!empty(session('billing_address'))){{session('billing_address')->billing_phone}}@endif" aria-describedby="numberHelp" placeholder="Enter Your Phone Number">
                               <span class="help-block error-content" hidden>@lang('website.Please enter your valid phone number')</span>
                             </div>
                            </div>
                             <div class="form-row">
                             <div class="form-group">
                                 <div class="form-check">
                                     <input class="form-check-input" type="checkbox" id="same_billing_address" value="1" name="same_billing_address" @if(!empty(session('billing_address'))) @if(session('billing_address')->same_billing_address==1) checked @endif @else checked  @endif > @lang('website.Same shipping and billing address')
                                     <small id="checkboxHelp" class="form-text text-muted"></small>
                                   </div>
                             </div>
                             </div>
                             <div class="form-row">
                              <div class="form-group">
                               <button type="submit"  class="btn swipe-to-top btn-secondary"><span>@lang('website.Continue')</span></button>
                              </div>
                             </div>
                       </form>
                 </div>
               
                 <div class="tab-pane fade @if(session('step') == 2) show active @endif" id="pills-order" role="tabpanel" aria-labelledby="pills-method-order">
                               <?php
                                   $price = 0;
                               ?>
                               <form method='POST' id="update_cart_form" action='{{ URL::to('/place_order')}}' >
                                 {!! csrf_field() !!}

                                       <table class="table top-table">
                                           
                                           @foreach( $result['cart'] as $products)
                                           <?php
                                              $orignal_price = $products->final_price * session('currency_value');
                                              $price+= $orignal_price * $products->customers_basket_quantity;
                                           ?>

                                           <tbody>

                                            <tr class="d-flex">
                                              <td class="col-12 col-md-2" >
                                                <input type="hidden" name="cart[]" value="{{$products->customers_basket_id}}">
                                                <a href="{{ URL::to('/product-detail/'.$products->products_slug)}}" class="cart-thumb">
                                                    <img class="img-fluid" src="{{asset('').$products->image_path}}" alt="{{$products->products_name}}" alt="">
                                                </a>
                                              </td>
                                              <td class="col-12 col-md-5 justify-content-start">
                                                  <div class="item-detail">
                                                      <span class="pro-info">
                                                        @foreach($products->categories as $key=>$category)
                                                            {{$category->categories_name}}@if(++$key === count($products->categories)) @else, @endif
                                                        @endforeach 
                                                      </span>
                                                      <h5 class="pro-title">
                                                          
                                                        <a href="{{ URL::to('/product-detail/'.$products->products_slug)}}">
                                                          {{$products->products_name}}
                                                        </a>
                                                       
                                                      </h5>
                                                      
                                                      <div class="item-attributes">
                                                        @if(isset($products->attributes))
                                                          @foreach($products->attributes as $attributes)
                                                            <small>{{$attributes->attribute_name}} : {{$attributes->attribute_value}}</small>
                                                          @endforeach
                                                        @endif
                                                      </div>
                                                     
                                                    </div>
                                                </td>
                                                <?php                                                      
                                                    $orignal_price = $products->final_price * session('currency_value');
                                                ?>
                                              <td class="item-price col-12 col-md-2"><span>{{Session::get('symbol_left')}}{{$orignal_price+0}}{{Session::get('symbol_right')}}</span></td>
                                              <td class="col-12 col-md-1">
                                                  <div class="input-group item-quantity">                                                      
                                                    <input type="text" id="quantity" readonly name="quantity" class="form-control input-number" value="{{$products->customers_basket_quantity}}">                    
                                                  </div>
                                              </td>
                                              <td class="align-middle item-total col-12 col-md-2 ">{{Session::get('symbol_left')}}{{$orignal_price*$products->customers_basket_quantity}}{{Session::get('symbol_right')}}</td>
                                            </tr>

                                           </tbody>
                                           @endforeach
                                       </table>
                                                   <?php
                                                   $shipping_price = 0;
                                                      if(!empty(session('coupon_discount'))){
                                                        $coupon_amount = session('currency_value') * session('coupon_discount');  
                                                      }else{
                                                        $coupon_amount = 0;
                                                      }

                                                      if(!empty(session('tax_rate'))){
                                                        $tax_rate = session('currency_value') * session('tax_rate');  
                                                      }else{
                                                        $tax_rate = 0;
                                                      }
                                                      if(!empty($result['commonContent']['settings']['shipping_charge']) && $result['commonContent']['settings']['min_order_amount_for_shipping_free'] >= $price){
                                                                $shipping_price = $result['commonContent']['settings']['shipping_charge'];
                                                                $shipping_name = '';
                                                            }
                                                       

                                                      // dd($price,$tax_rate,$shipping_price);
                                                       $tax_rate = number_format((float)$tax_rate, 2, '.', '');
                                                       $coupon_discount = number_format((float)$coupon_amount, 2, '.', '');
                                                       $total_price = ($price+$tax_rate+($shipping_price*session('currency_value')))-$coupon_discount;
                                                       session(['total_price'=>($total_price)]);
                                                       $wallet_balance = auth()->guard('customer')->user()->m_wallet ;
                                                    ?>
                                 
                                 <input type="hidden" name="shipping_cost" value="{{$shipping_price}}">

                                   <div class="col-12 col-sm-12 mb-3">
                                       <div class="row">
                                         <div class="heading">
                                           <h2>@lang('website.Payment Methods')</h2>
                                           <hr>
                                         </div>

                                         <div class="alert alert-danger error_payment" style="display:none" role="alert">
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            @lang('website.Please select your payment method')
                                         </div>

                                         
                                      
                                         <div class="form-group" style="width:100%; padding:0;">
                                          <label for="exampleFormControlTextarea1" style="width:100%; margin-bottom:30px;">@lang('website.Please select a prefered payment method to use on this order')</label>
                                          <input id="payment_currency" type="hidden" onClick="paymentMethods();" name="payment_currency" value="{{session('currency_code')}}">
                                          @foreach($result['payment_methods'] as $payment_methods)
                                          
                                            @if($payment_methods['active']==1)
                                              @if($payment_methods['payment_method']=='wallet' && $total_price > $wallet_balance)

                                                  <input id="{{$payment_methods['payment_method']}}_public_key" type="hidden" name="public_key" value="{{$payment_methods['public_key']}}">
                                                  <input id="{{$payment_methods['payment_method']}}_environment" type="hidden" name="{{$payment_methods['payment_method']}}_environment" value="{{$payment_methods['environment']}}">
                                          
                                          
                                                <div class="form-check form-check-inline">
                                                    <input id="{{$payment_methods['payment_method']}}_label" type="radio" onClick="paymentMethods();" name="payment_method" class="form-check-input payment_method" value="{{$payment_methods['payment_method']}}" @if(!empty(session('payment_method'))) @if(session('payment_method')==$payment_methods['payment_method']) checked @endif @endif>
                                                    <label class="form-check-label" for="{{$payment_methods['payment_method']}}_label">
                                                        @if(file_exists( 'web/images/miscellaneous/'.$payment_methods['payment_method'].'.png'))
                                                        <img src="{{asset('web/images/miscellaneous').'/'.$payment_methods['payment_method'].'.png'}}" alt="{{$payment_methods['name']}}">
                                                        @else
                                                        {{$payment_methods['name']}}
                                                        @endif
                                                    </label>
                                                </div>
                                              @else
                                              
                                                  <input id="{{$payment_methods['payment_method']}}_public_key" type="hidden" name="public_key" value="{{$payment_methods['public_key']}}">
                                                  <input id="{{$payment_methods['payment_method']}}_environment" type="hidden" name="{{$payment_methods['payment_method']}}_environment" value="{{$payment_methods['environment']}}">
                                                
                                                  
                                                  <div class="form-check form-check-inline">
                                                    <input onClick="paymentMethods();" id="{{$payment_methods['payment_method']}}_label" type="radio" name="payment_method" class="form-check-input payment_method" value="{{$payment_methods['payment_method']}}" @if(!empty(session('payment_method'))) @if(session('payment_method')==$payment_methods['payment_method']) checked @endif @endif>
                                                    <label class="form-check-label" for="{{$payment_methods['payment_method']}}_label">
                                                      @if(file_exists( 'web/images/miscellaneous/'.$payment_methods['payment_method'].'.png'))
                                                        <img width="100px" src="{{asset('web/images/miscellaneous/').'/'.$payment_methods['payment_method'].'.png'}}" alt="{{$payment_methods['name']}}">
                                                      @else
                                                      {{$payment_methods['name']}}
                                                      @endif
                                                    </label>
                                                  </div>
                                              @endif  
                                            @endif

                                          @endforeach 
                                                                                 
                                        </div>
                                           <button type="submit" class="btn btn-sm btn-secondary">Order</button>
                                         </form>
                                          
                                           
                                       </div>
                                      
                                      

                                   </div>

                 </div>
               </div>
         </div>
         </div>
     </div>
     
     <div class="col-12 col-xl-3 checkout-right cart-page-one cart-area">
      <table class="table right-table">
        <thead>
          <tr>
            <th scope="col" colspan="2" align="center">@lang('website.Order Summary')</th>                    
          </tr>
        </thead>
        <tbody>
          <tr>
            <th scope="row">@lang('website.SubTotal')</th>
            <td align="right">{{Session::get('symbol_left')}}{{$price+0}}{{Session::get('symbol_right')}}</td>

          </tr>
          <tr>
            <th scope="row">@lang('website.Discount')</th>
            <td align="right">{{Session::get('symbol_left')}}{{number_format((float)$coupon_discount, 2, '.', '')+0*session('currency_value')}}{{Session::get('symbol_right')}}</td>

          </tr>
          <tr>
              <th scope="row">@lang('website.Tax')</th>
              <td align="right">{{Session::get('symbol_left')}}{{$tax_rate*session('currency_value')}}{{Session::get('symbol_right')}}</td>

            </tr>
            <tr>
                <th scope="row">@lang('website.Shipping Cost')</th>
                <td align="right">{{Session::get('symbol_left')}}{{$shipping_price*session('currency_value')}}{{Session::get('symbol_right')}}</td>

              </tr>
          <tr class="item-price">
            <th scope="row">@lang('website.Total')</th>
            <td align="right" >{{Session::get('symbol_left')}}{{number_format((float)$total_price+0, 2, '.', '')+0*session('currency_value')}}{{Session::get('symbol_right')}}</td>

          </tr>
      
        </tbody>
        
      </table>

       </div>
   </div>
 </div>
</section>
</section>

<script>
jQuery(document).on('click', '#cash_on_delivery_button, #banktransfer_button', function(e){
	jQuery("#update_cart_form").submit();
});
</script>
<script>
    $('#rzp-footer-form').submit(function (e) {
        var button = $(this).find('button');
        var parent = $(this);
        button.attr('disabled', 'true').html('Please Wait...');
        $.ajax({
            method: 'get',
            url: this.action,
            data: $(this).serialize(),
            complete: function (r) {
                jQuery("#update_cart_form").submit();
                console.log(r);
            }
        })
        return false;
    })
</script>

<script>
    function padStart(str) {
        return ('0' + str).slice(-2)
    }

    function demoSuccessHandler(transaction) {
        // You can write success code here. If you want to store some data in database.
        jQuery("#paymentDetail").removeAttr('style');
        jQuery('#paymentID').text(transaction.razorpay_payment_id);
        var paymentDate = new Date();
        jQuery('#paymentDate').text(
                padStart(paymentDate.getDate()) + '.' + padStart(paymentDate.getMonth() + 1) + '.' + paymentDate.getFullYear() + ' ' + padStart(paymentDate.getHours()) + ':' + padStart(paymentDate.getMinutes())
                );

        jQuery.ajax({
            method: 'post',
            url: "{!!route('dopayment')!!}",
            data: {
                "_token": "{{ csrf_token() }}",
                "razorpay_payment_id": transaction.razorpay_payment_id
            },
            complete: function (r) {
                jQuery("#update_cart_form").submit();
                console.log(r);
            }
        })
    }
</script>
<?php

if(!empty($result['payment_methods'][2]) and $result['payment_methods'][2]['active'] == 1){

$rezorpay_key =  $result['payment_methods'][2]['RAZORPAY_KEY'];

if(!empty($result['commonContent']['setting'][79]->value)){
  $name = $result['commonContent']['setting'][79]->value;
}else{
  $name = Lang::get('website.Ecommerce');
}

$logo = $result['commonContent']['setting'][15]->value;
 ?>
<script>
    var options = {
        key: "{{ $rezorpay_key }}",
        amount: '<?php echo (float) round($total_price, 2)*100;?>',
        name: '{{$name}}',
        image: '{{$logo}}',
        handler: demoSuccessHandler
    }
</script>
<script>
    window.r = new Razorpay(options);
    document.getElementById('razor_pay_button').onclick = function () {
        r.open()
    }
</script>

<?php
}

foreach($result['payment_methods'] as $payment_methods){
  if($payment_methods['active']==1 and $payment_methods['payment_method']=='midtrans'){
    if($payment_methods['environment'] == 'Live'){
      print '<script src="https://app.midtrans.com/snap/snap.js" data-client-key="'.$payment_methods['public_key'].'"></script>';
    }else{
      print '<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="'.$payment_methods['public_key'].'"></script>';

    }
  }
}
                                          
                                            

?>

<script>
jQuery( document ).ready( function () {
  var midtrans_environment = jQuery('#midtrans_environment').val();
  if(midtrans_environment !== undefined){
    midtrans_environment = midtrans_environment;
  }else{
    midtrans_environment = ';'
  }
});

</script>


<script type="text/javascript">
  document.getElementById('midtrans_button').onclick = function(){
    var tokken = jQuery('#midtransToken').val();
      // SnapToken acquired from previous step
      snap.pay(tokken, {
          // Optional
          onSuccess: function(result){
           // alert('onSuccess');
              // /* You may add your own js here, this is just example */ document.getElementById('result-json').innerHTML += JSON.stringify(result, null, 2);
              paymentSuccess(JSON.stringify(result, null, 2));
          },
          // Optional
          onPending: function(result){
           // alert('onPending');
              /* You may add your own js here, this is just example */ document.getElementById('result-json').innerHTML += JSON.stringify(result, null, 2);
          },
          // Optional
          onError: function(result){
            jQuery('#payment_error').show();
            var response = JSON.stringify(result, null, 2);
           // alert('error');
              /* You may add your own js here, this is just example */ document.getElementById('payment_error-error-text').innerHTML += result.status_message;
          }
      });
  };
</script>

@endsection
