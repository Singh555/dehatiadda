@if ($message = Session::get('success'))
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        X
                    </button>
    <span><i class="fa fa-check text-white"></i> <b>{{__('Success')}} - </b> {{ __($message) }}</span>
    
    {{ session()->forget('success') }}
</div>
@endif
  
@if ($message = Session::get('error'))
<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                       X
                    </button>
    <span><i class="fa fa-ban text-white"></i><b> {{__('Error')}} - </b> {{ __($message) }}</span>
    {{ session()->forget('error') }}
</div>
@endif
   
@if ($message = Session::get('warning'))
<div class="alert alert-warning alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                       X
                    </button>
    <span><i class="fa fa-exclamation-triangle text-white"></i> <b>{{__('Warning')}} - </b> {{ __($message) }}</span>
    
    {{ session()->forget('warning') }}
</div>
@endif
   
@if ($message = Session::get('info'))
<div class="alert alert-info alert-dismissible">
   <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                       X
                    </button>
    <span><i class="fa fa-info text-white"></i> <b>{{__('Info')}} - </b> {{ __($message) }}</span>
    
    {{ session()->forget('info') }}
</div>
@endif
  
@if ($errors->any())
<div class="alert alert-danger alert-dismissible">
   <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                       X
                    </button>
    <span><i class="fa fa-ban text-white"></i> <b>{{__('Danger')}} - </b>{{__('Please check below errors')}}</span>
    <ul>
    @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
    @endforeach
    </ul>
</div>
@endif