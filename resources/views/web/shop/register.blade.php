@extends('web.layout')
@section('css')
@endsection
@section('content')
<div class="container-fuild">
    <nav aria-label="breadcrumb">
        <div class="container">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ URL::to('/')}}">@lang('website.Home')</a></li>
                <li class="breadcrumb-item active" aria-current="page">@lang('website.Shop')</li>

            </ol>
        </div>
    </nav>
</div> 
 
<section class="page-area pro-content">
    <div class="container">


        <div class="row">

            <div class="col-12 col-sm-12 col-md-12">
                <div class="col-12"><h4 class="heading login-heading">New Shop Registration</h4></div>
                @include('web.common.feedback')
                <div class="col-12 registration-process">

                    <form id="regForm" action="{{ route('shop.register') }}" method="post" enctype="multipart/form-data">
                        @csrf
                       <div class="form-row">
                            <div class="col-md-12">
                           <h3>Shop Details</h3>
                           <hr>
                          </div>
                           <div class="form-group col-md-6">
                                    <label for="inputaddress"><span class="star">*</span> Shop Name</label>
                                    <input type="text" name="shop_name" value="{{old('shop_name')}}" required="" class="form-control field-validate" >
                                    <span class="help-block error-content7" hidden>@lang('website.Please enter your Shop Name')</span>
                                    @if($errors->has('shop_name'))
                                    <p class="text-danger">{{ $errors->first('shop_name')}}<p>
                                    @endif
                            </div>
                           <div class="form-group col-md-6">
                                    <label for="inputaddress"><span class="star">*</span> Shop Gst No</label>
                                    <input type="text" name="shop_gst_no" value="{{old('shop_gst_no')}}" class="form-control" >
                                    <span class="help-block error-content7" hidden>@lang('website.Please enter your Shop Gst Number')</span>
                                    @if($errors->has('shop_gst_no'))
                                    <p class="text-danger">{{ $errors->first('shop_gst_no')}}<p>
                                    @endif
                            </div>
                           <div class="form-group col-md-6">
                                    <label for="inputaddress"><span class="star">*</span> Gst Image</label>
                                    <input type="file" name="gst_image"  class="form-control" >
                                    @if ($errors->has('gst_image'))
                                        <span class="errormsg text-danger">{{ $errors->first('gst_image') }}</span>
                                      @endif
                            </div>
                           <div class="form-group col-md-6">
                                    <label for="inputaddress"><span class="star"></span> Shop Logo</label>
                                    <input type="file" name="shop_logo"  class="form-control" >
                                    @if ($errors->has('shop_logo'))
                                        <span class="errormsg text-danger">{{ $errors->first('shop_logo') }}</span>
                                      @endif
                            </div>
                           <div class="form-group col-md-6">
                                    <label for="inputaddress"><span class="star">*</span> Shop front Image</label>
                                    <input type="file" name="shop_image" required="" class="form-control" >
                                    @if ($errors->has('shop_image'))
                                        <span class="errormsg text-danger">{{ $errors->first('shop_image') }}</span>
                                      @endif
                            </div>
                           <div class="clearfix">&nbsp;</div>
                           <div class="form-group col-md-6">
                                    <label for="inputcomapnyname"><span class="star">*</span>Address</label>
                                    <input type="text" name="address" value="{{old('address')}}" class="form-control field-validate" id="entry1_street_address">
                                    <span class="help-block error-content7" hidden>@lang('website.Please enter your address')</span>
                                </div>
                               <div class="form-group select-control col-md-6">
                                    <label for="inputState"><span class="star">*</span> Country</label>
                                    <select name="country"  onChange="getStates();" id="entry_country_id" class="form-control field-validate">
                                        <option value="">@lang('website.select Country')</option>
                                        @foreach($result['countries'] as $countries)
                                        <option data-counrty_id="{{$countries->countries_id}}" value="{{$countries->countries_name}}" >{{$countries->countries_name}}</option>
                                        @endforeach
                                    </select>
                                    <span class="help-block error-content1" hidden>@lang('website.Please select your country')</span>
                                    @if($errors->has('country'))
                                    <p class="text-danger">{{ $errors->first('country')}}<p>
                                    @endif
                                </div>
                              <div class="form-group col-md-6">
                                    <label for="inputState">State</label>
                                    <select type="text" name="state" required='' class="form-control field-validate" id="entry_zone_id">
                                        <option value="">Select State</option>
                                    </select>
                                    <span class="help-block error-content6" hidden>@lang('website.Please select your state')</span>
                                    @if($errors->has('state'))
                                    <p class="text-danger">{{ $errors->first('state')}}<p>
                                    @endif
                              </div>
                              <div class="form-group col-md-6">
                                    <label for="inputState"><span class="star">*</span> City</label>
                                    <input type="text" name="city" value="{{old('city')}}" class="form-control field-validate" id="entry_city1">
                                    <span class="help-block error-content7" hidden>@lang('website.Please enter your city')</span>
                                    @if($errors->has('city'))
                                    <p class="text-danger">{{ $errors->first('city')}}<p>
                                    @endif
                              </div>
                                <div class="form-group col-md-6">
                                    <label for="inputaddress"><span class="star">*</span> Zip/Postal Code</label>
                                    <input type="text" name="pin_code" value="{{old('pin_code')}}" class="form-control field-validate" id="entry_postcode1">
                                    <span class="help-block error-content7" hidden>@lang('website.Please enter your Zip/Postal Code')</span>
                                    @if($errors->has('pin_code'))
                                    <p class="text-danger">{{ $errors->first('pin_code')}}<p>
                                    @endif
                                </div>
                           <div class="col-md-12">
                               &nbsp;
                            </div>
                           <div class="form-group col-md-6">
                                    <label for="inputaddress"><span class="star"></span> Email</label>
                                    <input type="email" name="email" value="{{old('email')}}" class="form-control">
                                    <span class="help-block error-content7" hidden>@lang('website.Please enter your Shop Email')</span>
                                    @if($errors->has('email'))
                                    <p class="text-danger">{{ $errors->first('email')}}<p>
                                    @endif
                                </div>
                           <div class="form-group col-md-6">
                                    <label for="inputaddress"><span class="star">*</span> Phone</label>
                                    <input type="tel" name="phone" required="" value="{{old('phone')}}" class="form-control field-validate">
                                    <span class="help-block error-content7" hidden>@lang('website.Please enter your Shop Phone Number')</span>
                                    @if($errors->has('phone'))
                                    <p class="text-danger">{{ $errors->first('phone')}}<p>
                                    @endif
                                </div>
                           <div class="form-group col-md-6">
                                    <label for="inputaddress"><span class="star">*</span> Contact Person Name</label>
                                    <input type="text" name="contact_person_name" required="" value="{{old('contact_person_name')}}"  class="form-control field-validate">
                                    <span class="help-block error-content7" hidden>@lang('website.Please enter your Contact person name')</span>
                                </div>
                           <div class="form-group col-md-6">
                                    <label for="inputaddress"><span class="star">*</span>Contact Person Phone</label>
                                    <input type="tel" name="contact_person_phone" value="{{old('contact_person_phone')}}" required="" class="form-control field-validate">
                                    <span class="help-block error-content7" hidden>@lang('website.Please enter your Contact person Phone Number')</span>
                                </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-secondary mailchimp-btn processing_btn">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</section>
@endsection
@section('js')
<script>
function getStates() {
		jQuery(function ($) {
			jQuery('#loader').show();
			var country_id = $('#entry_country_id').find('option:selected').attr("data-counrty_id");
			jQuery.ajax({
				beforeSend: function (xhr) { // Add this line
								xhr.setRequestHeader('X-CSRF-Token', $('[name="_csrfToken"]').val());
				 },
				url: '{{ URL::to("/ajaxZones")}}',
				type: "POST",
				//data: '&country_id='+country_id,
				 data: {'country_id': country_id,"_token": "{{ csrf_token() }}"},

				success: function (res) {
					var i;
					var showData = [];
					for (i = 0; i < res.length; ++i) {
						var j = i + 1;
						showData[i] = "<option value='"+res[i].zone_name+"'>"+res[i].zone_name+"</option>";
					}
					showData.push("<option value='-1'>Other</option>");
					jQuery("#entry_zone_id").html(showData);
					jQuery('#loader').hide();
				},
			});
		});
};

</script>
@endsection