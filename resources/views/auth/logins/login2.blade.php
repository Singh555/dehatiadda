<!-- login Content -->
<div class="container-fuild">
	<nav aria-label="breadcrumb">
		<div class="container">
			<ol class="breadcrumb">
			  <li class="breadcrumb-item"><a href="{{ URL::to('/')}}">@lang('website.Home')</a></li>
			  <li class="breadcrumb-item active" aria-current="page">@lang('website.Login')</li>

			</ol>
		</div>
	  </nav>
  </div> 

<section class="page-area pro-content">
	<div class="container"> 

		<div class="row justify-content-center">
			@include('web.common.feedback')
		</div>
	  
	  
		<div class="row justify-content-center">	   
		  
		
		  <div class="col-12 col-sm-12 col-md-6">
			  
			<div class="col-12 my-5 px-0">
				
				<ul class="nav nav-tabs" id="registerTab" role="tablist">
					<li class="nav-item">
					  <a class="nav-link active" id="login-tab" data-toggle="tab" href="#login" role="tab" aria-controls="login" aria-selected="true">@lang('website.Login')</a>
					</li>
					<li class="nav-item">
					  <a class="nav-link" id="signup-tab" data-toggle="tab" href="#signup" role="tab" aria-controls="signup" aria-selected="false">@lang('website.Signup')</a>
					</li>
					
				  </ul>
				  <div class="tab-content" id="registerTabContent">
					<div class="tab-pane fade show active" id="login" role="tabpanel" aria-labelledby="login-tab">
                                            <div id="responseMessage">
                                                
                                            </div>
						 <div class="registration-process" id="sendOptDiv">

								<div class="from-group mb-3">
									<div class="col-12"> <label for="inlineFormInputGroup">@lang('website.Phone')</label></div>
									<div class="input-group col-12">
										<input type="tel" name="mobile_no" autocomplete="off" id="sendOtp_mobile" placeholder="Enter your phone no" class="form-control email-validate-login">
								 </div>
								</div>

									<div class="col-12 col-sm-12">
										<button type="submit" onclick="sendOtp(this);" class="btn btn-secondary processing_btn">Send Otp</button>
								</div>
					</div>
                                    {{-- verify otp --}}
                                    <div class="registration-process" id="verifyOptDiv" style="display: none;">

                                        <form  enctype="multipart/form-data"  class="" action="{{ URL::to('/login/verifyOtp')}}" method="post">
							{{csrf_field()}}
								<div class="from-group mb-3">
									<div class="col-12"> <label for="inlineFormInputGroup">Otp</label></div>
									<div class="input-group col-12">
										<input type="text" name="otp" autocomplete="off" required='' id="otp" placeholder="Enter Otp" class="form-control">
                                                                                <input type="hidden" name="mobile_no" required='' id="verifyMobile">
                                                                                @if($errors->has('otp'))
                                                                                <p class="text-danger">{{ $errors->first('otp')}}<p>
                                                                                @endif
                                                                        </div>
								</div>
									<div class="col-12 col-sm-12">
										<button type="submit" class="btn btn-secondary processing_btn">@lang('website.Login')</button>
								</div>
						</form>
                                            <div class="col-12 col-sm-12">
                                                <div class="col-sm-6 offset-sm-8">
                                                    <button class="btn btn-primary btn-sm" onclick="sendOtp(this);">Resend Otp</button>
                                                </div>
                                            </div>
					</div>
						
					</div>
					<div class="tab-pane fade" id="signup" role="tabpanel" aria-labelledby="signup-tab">
						<div class="col-12">
                                                    <h4 class="heading login-heading">@lang('website.NEW CUSTOMER')</h4>
                                                    <button class="btn btn-primary" onclick="myNormalSignupShow()">Normal</button>
                                                    <button class="btn btn-secondary" onclick="myPrimeSignupShow()">Prime</button>
                                                </div>
                                    {{-- Normal Signup Form --}}
                                    <div class="registration-process mt-3" id="normalSignup" style="display:none;">
                                    <h4 class="heading login-heading">Normal Signup</h4>
							<form enctype="multipart/form-data" action="{{ URL::to('/normalSignup')}}" method="post">
								{{csrf_field()}}
								<div class="from-group mb-3">
									<div class="col-12"> <label for="inlineFormInputGroup"><strong style="color: red;">*</strong>Name</label></div>
									<div class="input-group col-12">
										<input  name="name" type="text" required='' class="form-control"  placeholder="@lang('website.Please enter your name')" value="{{ old('name') }}">
									@if($errors->has('name'))
                                                                                <p class="text-danger">{{ $errors->first('name')}}<p>
                                                                                @endif
                                                                        </div>
								</div>
                                                                <div class="from-group mb-3">
                                                                    <div class="col-12"> <label for="inlineFormInputGroup"><strong style="color: red;">*</strong>@lang('website.Phone Number')</label></div>
                                                                    <div class="input-group col-12">
                                                                        <input  name="mobile_no" type="text" required='' class="form-control " placeholder="@lang('website.Please enter your valid phidone number')" value="{{ old('mobile_no') }}">
                                                                        @if($errors->has('mobile_no'))
                                                                        <p class="text-danger">{{ $errors->first('mobile_no')}}<p>
                                                                            @endif
                                                                    </div>
                                                                </div>

                                                                <div class="from-group mb-3">
                                                                    <div class="col-12"> <label for="inlineFormInputGroup">@lang('website.Email Adrress')</label></div>
                                                                    <div class="input-group col-12">
                                                                        <input  name="email" type="text" class="form-control"  placeholder="Enter Your Email" value="{{ old('email') }}">
                                                                        @if($errors->has('email'))
                                                                        <p class="text-danger">{{ $errors->first('email')}}<p>
                                                                            @endif
                                                                    </div>
                                                                </div>
                                                                <div class="from-group mb-3">
                                                                    <div class="col-12"> <label for="inlineFormInputGroup"><strong style="color: red;">*</strong>@lang('website.Password')</label></div>
                                                                    <div class="input-group col-12">
                                                                        <input name="password" type="password" class="form-control"  placeholder="@lang('website.Please enter your password')">
                                                                        <span class="form-text text-muted error-content" hidden>@lang('website.Please enter your password')</span>
                                                                        @if($errors->has('password'))
                                                                        <p class="text-danger">{{ $errors->first('password')}}<p>
                                                                            @endif
                                                                    </div>
                                                                </div>
                                                                <div class="from-group mb-3">
                                                                    <div class="col-12"> <label for="inlineFormInputGroup"><strong style="color: red;">*</strong>@lang('website.Confirm Password')</label></div>
                                                                    <div class="input-group col-12">
                                                                        <input type="password" class="form-control" name="password_confirmation" placeholder="Enter Your Password">
                                                                        @if($errors->has('password_confirmation'))
                                                                        <p class="text-danger">{{ $errors->first('password_confirmation')}}<p>
                                                                            @endif
                                                                    </div>
                                                                </div>
											
                                                                <div class="from-group mb-3">
                                                                    <div class="input-group col-12">
                                                                        @lang('website.Creating an account means you are okay with our')  @if(!empty($result['commonContent']['pages'][3]->slug))&nbsp;<a href="{{ URL::to('/page?name='.$result['commonContent']['pages'][3]->slug)}}">@endif @lang('website.Terms and Services')@if(!empty($result['commonContent']['pages'][3]->slug))</a>@endif, @if(!empty($result['commonContent']['pages'][1]->slug))<a href="{{ URL::to('/page?name='.$result['commonContent']['pages'][1]->slug)}}">@endif @lang('website.Privacy Policy')@if(!empty($result['commonContent']['pages'][1]->slug))</a> @endif &nbsp; and &nbsp; @if(!empty($result['commonContent']['pages'][2]->slug))<a href="{{ URL::to('/page?name='.$result['commonContent']['pages'][2]->slug)}}">@endif @lang('website.Refund Policy') @if(!empty($result['commonContent']['pages'][3]->slug))</a>@endif.
                                                                        <span class="form-text text-muted error-content" hidden>@lang('website.Please accept our terms and conditions')</span>
                                                                    </div>
                                                                </div>
										<div class="col-12 col-sm-12">
												<button type="submit" class="btn btn-light processing_btn">@lang('website.Create an Account')</button>

										</div>
							</form>
						</div>
                                    
                                    {{-- Prime Signup Form --}}
                                    
				   <div class="registration-process mt-3" id="primeSignup" style="display:none;">
                                             <h4 class="heading login-heading">Prime Signup</h4>
                                             <div id="responseReferralMessage">
                                                
                                            </div>
                                             {{-- validate Referral code --}}
                                             <div class="from-group mb-3">
									<div class="col-12"> <label for="inlineFormInputGroup">Referral code</label></div>
									<div class="input-group col-12">
										<input type="text" id="EntryreferralCode" autocomplete="off" placeholder="Enter Prime Referral code" class="form-control">
								 </div>
								</div>

									<div class="col-12 col-sm-12">
										<button type="submit" onclick="verifyReferralCode(this);" class="btn btn-secondary">Continue</button>
								</div>
                                   </div>
                                             {{-- primeSignupDiv --}}
                                             <div class="registration-process mt-3" id="primeSignupDiv" style="display:none;">
                                                 <h4 class="heading login-heading">Prime Signup</h4>
							<form name="signup" enctype="multipart/form-data" class="form-validate" action="{{ URL::to('/primeSignup')}}" method="post">
								{{csrf_field()}}
								<div class="from-group mb-3">
									<div class="input-group col-12">
                                                                            <input  name="referral_code" type="hidden" class="form-control field-validate" id="verifyReferralcode" value="{{ old('referral_code') }}">
                                                                            <div id="verifyCodeMessage"></div>
                                                                        </div>
								</div>
								<div class="from-group mb-3">
									<div class="col-12"> <label for="inlineFormInputGroup"><strong style="color: red;">*</strong>Name</label></div>
									<div class="input-group col-12">
										<input  name="name" type="text" class="form-control" required="" placeholder="@lang('website.Please enter your name')" value="{{ old('name') }}">
									@if($errors->has('name'))
                                                                                                <p class="text-danger">{{ $errors->first('name')}}<p>
                                                                                                    @endif
                                                                        </div>
								</div>

								<div class="from-group mb-3">
									<div class="col-12"> <label for="inlineFormInputGroup">@lang('website.Date of Birth')</label></div>
									<div class="input-group col-12 date">
										<input  name="dob" type="text" class="form-control" autocomplete="off" data-provide="datepicker" id="customers_dob" placeholder="@lang('website.Please enter your date of birth')" value="{{ old('dob') }}">
									@if($errors->has('dob'))
                                                                                                <p class="text-danger">{{ $errors->first('dob')}}<p>
                                                                                                    @endif
                                                                        </div>
								</div>

									<div class="from-group mb-3">
										<div class="col-12"> <label for="inlineFormInputGroup"><strong style="color: red;">*</strong>@lang('website.Email Adrress')</label></div>
										<div class="input-group col-12">
                                                                                    <input  name="email" type="email" class="form-control" placeholder="Enter Your Email" value="{{ old('email') }}">
										@if($errors->has('email'))
                                                                                                <p class="text-danger">{{ $errors->first('email')}}<p>
                                                                                                    @endif
                                                                                </div>
									</div>
                                                                <div class="from-group mb-3">
												<div class="col-12"> <label for="inlineFormInputGroup"><strong style="color: red;">*</strong>@lang('website.Phone Number')</label></div>
												<div class="input-group col-12">
													<input  name="mobile_no" type="tel" class="form-control" required="" placeholder="@lang('website.Please enter your valid phone number')" value="{{ old('mobile_no') }}">
												@if($errors->has('mobile_no'))
                                                                                                <p class="text-danger">{{ $errors->first('mobile_no')}}<p>
                                                                                                    @endif
                                                                                                </div>
											</div>
                                                                <div class="from-group mb-3">
										<div class="col-12"> <label for="inlineFormInputGroup"><strong style="color: red;">*</strong>City</label></div>
										<div class="input-group col-12">
                                                                                    <input  name="city" type="text" class="form-control" required="" placeholder="Enter Your City" value="{{ old('city') }}">
										@if($errors->has('city'))
                                                                                                <p class="text-danger">{{ $errors->first('city')}}<p>
                                                                                                    @endif
                                                                                </div>
									</div>
                                                                <div class="from-group mb-3">
										<div class="col-12"> <label for="inlineFormInputGroup"><strong style="color: red;">*</strong>Pin code</label></div>
										<div class="input-group col-12">
                                                                                    <input  name="pin_code" type="text" class="form-control" required="" placeholder="Enter Your Pin code" value="{{ old('pin_code') }}">
										@if($errors->has('pin_code'))
                                                                                                <p class="text-danger">{{ $errors->first('pin_code')}}<p>
                                                                                                    @endif
                                                                                </div>
									</div>
									<div class="from-group mb-3">
											<div class="col-12"> <label for="inlineFormInputGroup"><strong style="color: red;">*</strong>@lang('website.Password')</label></div>
											<div class="input-group col-12">
												<input name="password" type="password" class="form-control" required=""  placeholder="@lang('website.Please enter your password')">
                                                                                        @if($errors->has('password'))
                                                                                                <p class="text-danger">{{ $errors->first('password')}}<p>
                                                                                                    @endif
											</div>
										</div>
										<div class="from-group mb-3">
												<div class="col-12"> <label for="inlineFormInputGroup"><strong style="color: red;">*</strong>@lang('website.Confirm Password')</label></div>
												<div class="input-group col-12">
													<input type="password" class="form-control" name="password_confirmation" required="" placeholder="Enter Your Password">
												@if($errors->has('password_confirmation'))
                                                                                                <p class="text-danger">{{ $errors->first('password_confirmation')}}<p>
                                                                                                    @endif
                                                                                                </div>
											</div>
											
											<div class="from-group mb-3">
													<div class="input-group col-12">
														@lang('website.Creating an account means you are okay with our')  @if(!empty($result['commonContent']['pages'][3]->slug))&nbsp;<a href="{{ URL::to('/page?name='.$result['commonContent']['pages'][3]->slug)}}">@endif @lang('website.Terms and Services')@if(!empty($result['commonContent']['pages'][3]->slug))</a>@endif, @if(!empty($result['commonContent']['pages'][1]->slug))<a href="{{ URL::to('/page?name='.$result['commonContent']['pages'][1]->slug)}}">@endif @lang('website.Privacy Policy')@if(!empty($result['commonContent']['pages'][1]->slug))</a> @endif &nbsp; and &nbsp; @if(!empty($result['commonContent']['pages'][2]->slug))<a href="{{ URL::to('/page?name='.$result['commonContent']['pages'][2]->slug)}}">@endif @lang('website.Refund Policy') @if(!empty($result['commonContent']['pages'][3]->slug))</a>@endif.
														<span class="form-text text-muted error-content" hidden>@lang('website.Please accept our terms and conditions')</span>
													</div>
												</div>
										<div class="col-12 col-sm-12">
												<button type="submit" class="btn btn-light swipe-to-top">@lang('website.Create an Account')</button>

										</div>
							</form>
						</div>
					</div>
				  </div>
			</div>
		  </div>

		</div>
	</div>
  </section>


<script>
function myNormalSignupShow() {
  var x = document.getElementById("normalSignup");
  var y = document.getElementById("primeSignup");
  if (y.style.display === "block") {
    y.style.display = "none";
  }
  if (x.style.display === "none") {
    x.style.display = "block";
  } else {
    x.style.display = "none";
  }
}
function myPrimeSignupShow() {
  var x = document.getElementById("primeSignup");
  var y = document.getElementById("normalSignup");
  if (y.style.display === "block") {
    y.style.display = "none";
  }
  if (x.style.display === "none") {
    x.style.display = "block";
  } else {
    x.style.display = "none";
  }
  
}

//sendOpt Ajax Form
$.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        }
    });
function sendOtp(e){
    var mobile_no = $('#sendOtp_mobile').val();
    $.ajax({
				url: '{{ URL::to("/login/sendOtp")}}',
				type: "POST",
                                dataType: "json",
                                  data: {   
                                     "mobile_no": mobile_no
                                 },
                                cache: false,
				success: function (res) {
				if(res.status === "success")
                                    {
                                        $("#verifyMobile").val(mobile_no);
                                        var response = '<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close">X</button><span><i class="fa fa-ban text-white"></i><b> Success - </b> !'+res.message+'</span></div>';
                                    $("#responseMessage").html(response);
                                     $("#sendOptDiv").css("display", "none");
                                    $("#verifyOptDiv").css("display", "block");
                                   
                                    
                                    }else{
                                       var response = '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close">X</button><span><i class="fa fa-ban text-white"></i><b> Oops - </b> !'+res.message+'</span></div>';
                                    $("#responseMessage").html(response); 
                                    }
				},
                                error: function (XMLHttpRequest, textStatus, errorThrown) {
                                if(textStatus == "timeout")
                                {
                                    var response = '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close">X</button><span><i class="fa fa-ban text-white"></i><b> Timeout - </b> We couldnt connect to the server !</span></div>';
                                    $("#responseMessage").html(response);
                                }
                                else
                                {
                                      var response = '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close">X</button><span><i class="fa fa-ban text-white"></i><b> Oops - </b> We couldnt connect to the server !'+errorThrown+'</span></div>';
                                    $("#responseMessage").html(response);
                                }
                              }
                                
});
    
}
function verifyReferralCode(e){
    var referralcode = $('#EntryreferralCode').val();
    var type = 'PRIME';
    $.ajax({
				url: '{{ URL::to("/referralCode/validate")}}',
				type: "POST",
                                dataType: "json",
                                  data: {   
                                     "referral_code": referralcode,
                                     "type": type
                                 },
                                cache: false,
				success: function (res) {
				if(res.status === "success")
                                    {
                                        $("#verifyReferralcode").val(referralcode);
                                        var response = '<span class="text-success"> !'+res.data.name+'</span>';
                                    $("#verifyCodeMessage").html(response);
                                     $("#primeSignup").css("display", "none");
                                    $("#primeSignupDiv").css("display", "block");
                                   
                                    
                                    }else{
                                       var response = '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close">X</button><span><i class="fa fa-ban text-white"></i><b> Oops - </b> !'+res.message+'</span></div>';
                                    $("#responseReferralMessage").html(response); 
                                    $("#primeSignupDiv").css("display", "none");
                                    }
				},
                                error: function (XMLHttpRequest, textStatus, errorThrown) {
                                if(textStatus == "timeout")
                                {
                                    var response = '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close">X</button><span><i class="fa fa-ban text-white"></i><b> Timeout - </b> We couldnt connect to the server !</span></div>';
                                    $("#responseReferralMessage").html(response);
                                }
                                else
                                {
                                      var response = '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-label="Close">X</button><span><i class="fa fa-ban text-white"></i><b> Oops - </b> We couldnt connect to the server !'+errorThrown+'</span></div>';
                                    $("#responseReferralMessage").html(response);
                                }
                              }
                                
});
    
}


</script>