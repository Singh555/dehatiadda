@extends('web.layout')
@section('content')
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


			<div class="row">
                            <div class="col-12">
                            @include('web.common.feedback')
                            </div>
				<div class="col-12 col-sm-12 col-md-6 offset-md-3">
					
					<div class="col-12"><h4 class="heading login-heading">@lang('website.LOGIN')</h4></div>
					<div class="registration-process">

						<form  enctype="multipart/form-data"  class="form-validate-login" action="{{ URL::to('/normalSignup/verify')}}" method="post">
							{{csrf_field()}}
								<div class="from-group mb-3">
									<div class="col-12"> <label for="inlineFormInputGroup">Otp</label></div>
									<div class="input-group col-12">
										<input type="text" name="otp" autocomplete="off" required='' id="otp" placeholder="Enter Otp" class="form-control">
                                                                                @if($errors->has('otp'))
                                                                                <p class="text-danger">{{ $errors->first('otp')}}<p>
                                                                                @endif
                                                                        </div>
								</div>
                                                                    @foreach($fromData as $key=>$value)
                                                                    <input type="hidden" name="{{$key}}" value="{{$value}}">
                                                                    @endforeach
									<div class="col-12 col-sm-12">
										<button type="submit" class="btn btn-secondary processing_btn">@lang('website.Login')</button>
								</div>
						</form>
                                            <div class="col-12 col-sm-12 mt-3">
                                                <div class="col-sm-6 offset-sm-6">
                                                    <button class="btn btn-primary btn-sm processing_btn" onclick="resendNormalSignupOtp();">Resend Otp</button>
                                                </div>
                                            </div>
                                            
					</div>
				</div>

				
			</div>

	</div>
</section>


@endsection


@section('js')
<script>
function resendNormalSignupOtp() {
  $.ajax({
				beforeSend: function (xhr) { // Add this line
								xhr.setRequestHeader('X-CSRF-Token', $('[name="_csrfToken"]').val());
				 },
				url: '{{ URL::to("/normalSignup")}}',
				type: "POST",
				//data: '&country_id='+country_id,
				 data: {@foreach($fromData as $key=>$value)
                                        "{{$key}}":"{{$value}}",
                                                                    @endforeach
                                     "_token": "{{ csrf_token() }}"
                                 },

				success: function (res) {
					location.reload();
				}
});
}
</script>
@endsection