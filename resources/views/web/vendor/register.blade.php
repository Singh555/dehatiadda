@extends('web.layout')
@section('css')
<style>
    #regForm {
  background-color: #ffffff;
  margin: 100px auto;
  font-family: Raleway;
  padding: 40px;
  width: 70%;
  min-width: 300px;
}

h1 {
  text-align: center;  
}

input {
  padding: 10px;
  width: 100%;
  font-size: 17px;
  font-family: Raleway;
  border: 1px solid #aaaaaa;
}

/* Mark input boxes that gets an error on validation: */
input.invalid {
  background-color: #ffdddd;
}
    /* Hide all steps by default: */
    
.tab {
  display: none;
}

button {
  background-color: #4CAF50;
  color: #ffffff;
  border: none;
  padding: 10px 20px;
  font-size: 17px;
  font-family: Raleway;
  cursor: pointer;
}

button:hover {
  opacity: 0.8;
}

#prevBtn {
  background-color: #bbbbbb;
}

/* Make circles that indicate the steps of the form: */
.step {
  height: 15px;
  width: 15px;
  margin: 0 2px;
  background-color: #bbbbbb;
  border: none;  
  border-radius: 50%;
  display: inline-block;
  opacity: 0.5;
}

.step.active {
  opacity: 1;
}

/* Mark the steps that are finished and valid: */
.step.finish {
  background-color: #4CAF50;
}
</style>
@endsection
@section('content')
<div class="container-fuild">
    <nav aria-label="breadcrumb">
        <div class="container">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ URL::to('/')}}">@lang('website.Home')</a></li>
                <li class="breadcrumb-item active" aria-current="page">@lang('website.Vendor')</li>

            </ol>
        </div>
    </nav>
</div> 
 
<section class="page-area pro-content">
    <div class="container">


        <div class="row">

            <div class="col-12 col-sm-12 col-md-12">
                <div class="col-12"><h4 class="heading login-heading">New Vendor Registration</h4></div>
                @include('web.common.feedback')
                <div class="registration-process">
                    @if( count($errors) > 0)
                    @foreach($errors->all() as $error)
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                        <span class="sr-only">@lang('website.Error'):</span>
                        {{ $error }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    @endforeach
                    @endif

                    @if(Session::has('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                        <span class="sr-only">@lang('website.Error'):</span>
                        {!! session('error') !!}

                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    @endif

                    <form id="regForm" action="{{ route('vendor.register') }}" method="post">
                        @csrf
                        <h1>Register Details:</h1>
                        <!-- One "tab" for each step in the form: -->
                        <div class="tab">
                            Shop Info:<hr><br/>
                            <div class="from-row">
                                <div class="form-group col-md-6" style="float: left;">
                                    <label for="inputshopname"><span class="star">*</span>Shop Name</label>
                                    <input class="form-control" placeholder="Shop name..." oninput="this.className = ''" name="shopfname">
                                </div>
                                <div class="form-group col-md-6" style="float: right;">
                                    <label for="inputshopname2"><span class="star">*</span>Last Name</label>
                                    <input class="form-control" placeholder="Last name..." oninput="this.className = ''" name="shoplname">
                                </div>
                                <div class="form-group col-md-6" style="float: left;">
                                    <label for="inputgstno"><span class="star">*</span>GST No</label>
                                    <input class="form-control" placeholder="GST No..." oninput="this.className = ''" name="gst_no">
                                </div>
                            </div>
                        </div>
                        <div class="tab">
                            Name & Address Info:<hr><br/>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="inputcomapnyname"><span class="star">*</span>First Name</label>
                                    <input type="text" name="first_name"  class="form-control field-validate" id="entry1_street_address">
                                    <span class="help-block error-content7" hidden>@lang('website.Please enter your First Name')</span>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="inputcomapnyname"><span class="star">*</span>Last Name</label>
                                    <input type="text" name="last_name"  class="form-control field-validate" id="entry1_street_address">
                                    <span class="help-block error-content7" hidden>@lang('website.Please enter your Last Name')</span>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="inputcomapnyname"><span class="star">*</span>Address</label>
                                    <input type="text" name="address"  class="form-control field-validate" id="entry1_street_address">
                                    <span class="help-block error-content7" hidden>@lang('website.Please enter your address')</span>
                                </div>
                                <div class="form-group select-control col-md-6">
                                    <label for="inputState"><span class="star">*</span> Country</label>
                                    <select name="country_id"  onChange="getZones();" id="entry_country_id" class="form-control field-validate">
                                        <option value="">@lang('website.select Country')</option>
                                        @foreach($result['countries'] as $countries)
                                        <option value="{{$countries->countries_id}}" >{{$countries->countries_name}}</option>
                                        @endforeach
                                    </select>
                                    <span class="help-block error-content1" hidden>@lang('website.Please select your country')</span>
                                </div>
                            </div>
                            <div class="form-row">

                              <div class="form-group col-md-6">
                                    <label for="inputState">State</label>
                                    <input type="text" name="state"  class="form-control field-validate" id="state">
                                    <span class="help-block error-content6" hidden>@lang('website.Please select your state')</span>
                              </div>
                              <div class="form-group col-md-6">
                                    <label for="inputState"><span class="star">*</span> City</label>
                                    <input type="text" name="city"  class="form-control field-validate" id="entry_city1">
                                    <span class="help-block error-content7" hidden>@lang('website.Please enter your city')</span>
                              </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="inputaddress"><span class="star">*</span> Zip/Postal Code</label>
                                    <input type="text" name="pin_code"  class="form-control field-validate" id="entry_postcode1">
                                    <span class="help-block error-content7" hidden>@lang('website.Please enter your Zip/Postal Code')</span>
                                </div>
                            </div>
                        </div>
                        <div class="tab">
                            Bank Details:<hr/><br/>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="inputaccount"><span class="star">*</span> Bank Name</label>
                                    <input type="text" placeholder="Bank Name" oninput="this.className = ''" name="bank_name">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="inputaccount"><span class="star">*</span> Account Number</label>
                                    <input type="password" placeholder="Account Number" oninput="this.className = ''" name="account_number">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="inputconfirmaccount"><span class="star">*</span> Confirm Account Number</label>
                                    <input placeholder="Confirm Account Number" oninput="this.className = ''" name="confirm_account_number">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="inputholdername"><span class="star">*</span> Account Holder Name</label>
                                    <input placeholder="Account Holder Name" oninput="this.className = ''" name="holder_name">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="inputifsccode"><span class="star">*</span> IFSC Code</label>
                                    <input placeholder="IFSC Code" oninput="this.className = ''" name="ifsc_code">
                                </div>
                            </div>
                        </div>
                        <div class="tab">
                            Login Info:<hr/><br/>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="inputemail"><span class="star">*</span> Phone</label>
                                    <input placeholder="Phone..." type="text" oninput="this.className = ''" name="phone">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="inputemail"><span class="star">*</span> Email Id</label>
                                    <input placeholder="Email..." type="email" oninput="this.className = ''" name="email">
                                </div>
                                <div class="clearfix">&nbsp;</div>
                                <div class="form-group col-md-6">
                                    <label for="inputpassword"><span class="star">*</span> Password</label>
                                    <input placeholder="Password..." oninput="this.className = ''" name="pword" type="password">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="inputconfpass"><span class="star">*</span> Confirm Password</label>
                                    <input placeholder="Confirm Password..." oninput="this.className = ''" name="conf_pword" type="password">
                                </div>
                            </div>
                        </div>
                        <div class="clearfix">&nbsp;</div>
                        <div style="overflow:auto;">
                            <div style="float:right;">
                                <button type="button" id="prevBtn" onclick="nextPrev(-1)">Previous</button>
                                <button type="button" id="nextBtn" onclick="nextPrev(1)">Next</button>
                            </div>
                        </div>
                        <!-- Circles which indicates the steps of the form: -->
                        <div style="text-align:center;margin-top:40px;">
                            <span class="step"></span>
                            <span class="step"></span>
                            <span class="step"></span>
                            <span class="step"></span>
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
var currentTab = 0; // Current tab is set to be the first tab (0)
showTab(currentTab); // Display the current tab

function showTab(n) {
  // This function will display the specified tab of the form...
  var x = document.getElementsByClassName("tab");
  x[n].style.display = "block";
  //... and fix the Previous/Next buttons:
  if (n == 0) {
    document.getElementById("prevBtn").style.display = "none";
  } else {
    document.getElementById("prevBtn").style.display = "inline";
  }
  if (n == (x.length - 1)) {
    document.getElementById("nextBtn").innerHTML = "Submit";
  } else {
    document.getElementById("nextBtn").innerHTML = "Next";
  }
  //... and run a function that will display the correct step indicator:
  fixStepIndicator(n)
}

function nextPrev(n) {
  // This function will figure out which tab to display
  var x = document.getElementsByClassName("tab");
  // Exit the function if any field in the current tab is invalid:
  if (n == 1 && !validateForm()) return false;
  // Hide the current tab:
  x[currentTab].style.display = "none";
  // Increase or decrease the current tab by 1:
  currentTab = currentTab + n;
  // if you have reached the end of the form...
  if (currentTab >= x.length) {
    // ... the form gets submitted:
    document.getElementById("regForm").submit();
    return false;
  }
  // Otherwise, display the correct tab:
  showTab(currentTab);
}

function validateForm() {
  // This function deals with validation of the form fields
  var x, y, i, valid = true;
  x = document.getElementsByClassName("tab");
  y = x[currentTab].getElementsByTagName("input");
  // A loop that checks every input field in the current tab:
  for (i = 0; i < y.length; i++) {
    // If a field is empty...
    if (y[i].value == "") {
      // add an "invalid" class to the field:
      y[i].className += " invalid";
      // and set the current valid status to false
      valid = false;
    }
  }
  // If the valid status is true, mark the step as finished and valid:
  if (valid) {
    document.getElementsByClassName("step")[currentTab].className += " finish";
  }
  return valid; // return the valid status
}

function fixStepIndicator(n) {
  // This function removes the "active" class of all steps...
  var i, x = document.getElementsByClassName("step");
  for (i = 0; i < x.length; i++) {
    x[i].className = x[i].className.replace(" active", "");
  }
  //... and adds the "active" class on the current step:
  x[n].className += " active";
}
</script>
@endsection