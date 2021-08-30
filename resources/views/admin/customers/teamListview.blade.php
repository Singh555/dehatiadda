@extends('admin.layout')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1> <small>{{ trans('labels.TeamList') }}...</small> </h1>
        <ol class="breadcrumb">
            <li><a href="{{ URL::to('admin/dashboard/this_month')}}"><i class="fa fa-dashboard"></i> {{ trans('labels.breadcrumb_dashboard') }}</a></li>
            <li><a href="{{ URL::to('admin/customers/display')}}"><i class="fa fa-users"></i> {{ trans('labels.Customers') }}</a></li>
            <li class="active"> {{ trans('labels.TeamList') }}</li>
        </ol>
        <a href="{{ URL::to('admin/customers/display')}}" class="btn btn-primary pull-right">{{ trans('labels.back') }}</a>
    </section>
   <section class="content">
    <div class="row">
        <div class="col-md-4">
                <div class="box">

                    <!-- /.box-header -->
                    <div class="box-body">
                        
                                    @if (session('update'))
                                    <div class="alert alert-success alert-dismissable custom-success-box" style="margin: 15px;">
                                        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                        <strong> {{ session('update') }} </strong>
                                    </div>
                                    @endif

                                    @if (count($errors) > 0)
                                    @if($errors->any())
                                    <div class="alert alert-danger alert-dismissible" role="alert">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                        {{$errors->first()}}
                                    </div>
                                    @endif
                                    @endif

                                    
                                        {!! Form::open(array('url' =>'admin/customers/teamList', 'method'=>'post', 'class' => 'form-horizontal form-validate', 'enctype'=>'multipart/form-data')) !!}
                                                                        <div class="form-group col-md-12">
                                                                        <label> Enter Customer Name/Mobile no./Referral Code</label>
                                                                        <input type="text" name="user_id" class="form-control" required='' id="search-box" @if(!empty($phone)) value="{{$phone}}" @endif autocomplete="off">
                                                                        <div id="suggesstion-box"></div>
                                                                        </div>
                                                                        <div class="form-group col-md-12">
                                                                        <label> Select Level</label>
                                                                        <select name="level" class="form-control select2" required='' style="width: 100%;">
                                                                            <option value="">Select</option>
                                                                            <option value="0" @if(isset($level) && $level == '0') selected='' @endif>All</option>
                                                                            <option value="1" @if(!empty($level) && $level == '1') selected='' @endif>Level 1</option>
                                                                            <option value="2" @if(!empty($level) && $level == '2') selected='' @endif>Level 2</option>
                                                                            <option value="3" @if(!empty($level) && $level == '3') selected='' @endif>Level 3</option>
                                                                            <option value="4" @if(!empty($level) && $level == '4') selected='' @endif>Level 4</option>
                                                                            <option value="5" @if(!empty($level) && $level == '5') selected='' @endif>Level 5</option>
                                                                            <option value="6" @if(!empty($level) && $level == '6') selected='' @endif>Level 6</option>
                                                                            <option value="7" @if(!empty($level) && $level == '7') selected='' @endif>Level 7</option>
                                                                            <option value="8" @if(!empty($level) && $level == '8') selected='' @endif>Level 8</option>
                                                                            
                                                                        </select>
                                                                        </div>
                                                                            
                                        
                                                                        
                                        
                                        
                                        
                                        <div class="box-footer text-center">
                                           <button type="submit" class="btn btn-primary">{{ trans('labels.Submit') }}</button>

                                        </div>
                                    {!! Form::close() !!}

                    </div>
                    <!-- /.box-body -->
                </div>
                </div>
        @include('admin.customers.profile')
        
        <div class="col-md-12">
            <div class="box">
            <div class="box-header">
                <h4 class="box-title text-center">{{ trans('labels.TeamList') }}</h4>
            </div>
            <div class="box-body">
            <div class="col-xs-12 table-responsive">
                                <table class="table table-bordered table-striped example1">
                                    <thead>
                                        <tr>
                                            <th>@sortablelink('id', trans('labels.ID') )</th>
                                            <th>@sortablelink('first_name', trans('labels.Full Name')) </th>
                                            <th>@sortablelink('email', trans('labels.Email')) </th>
                                            <th>{{ trans('labels.Additional info') }} </th>
                                            <th>{{ trans('labels.Prime') }} </th>
                                            <th class="notexport">{{ trans('labels.Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (!empty($customers['childs']) && count($customers['childs'])>0)
                                        @foreach ($customers['childs'] as $listingCustomers)
                                        <tr><td>{{ $listingCustomers->id }}</td>
                                            <td>{{ $listingCustomers->first_name }} {{ $listingCustomers->last_name }}</td>
                                            <td>{{ $listingCustomers->email }}</td>
                                            <td>                                               
                                                <strong>{{ trans('labels.Phone') }}: </strong> {{ $listingCustomers->phone }}
                                                
                                            </td>
                                            <td>{{ $listingCustomers->is_prime }}</td>
                                            <td>
                                                <ul class="nav table-nav">
                                                    <li class="dropdown">
                                                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                                                            {{ trans('labels.Action') }} <span class="caret"></span>
                                                        </a>
                                                        <ul class="dropdown-menu">
                                                            <li role="presentation"><a role="menuitem" tabindex="-1" href="{{url('admin/customers/edit') }}/{{$listingCustomers->id}}">{{ trans('labels.EditCustomers') }}</a></li>
                                                            <div class="dropdown-divider"></div>
                                                            <li role="presentation"><a role="menuitem" tabindex="-1" href="{{url('admin/customers/address/display/'.$listingCustomers->id) }}">{{ trans('labels.ViewAddress') }}</a></li>
                                                            <div class="dropdown-divider"></div>
                                                            <li role="presentation"><a role="menuitem" tabindex="-1" href="{{url('admin/customers/orders/'.$listingCustomers->id) }}">{{ trans('labels.Orders') }}</a></li>
                                                            <div class="dropdown-divider"></div>
                                                        </ul>
                                                    </li>
                                                </ul>
                                            </td>
                                        </tr>
                                        @endforeach
                                        
                                        @endif
                                    </tbody>
                                </table>
                            </div>
            </div>
                    <!-- /.box-body -->
                </div>
        </div>
       
       </div>
    <!-- /.content -->
    </section>
</div>
@endsection
@section('js')
<script>
$(document).ready(function(){
    $("#search-box").keyup(function(){
        $.ajax({
            type: "POST",
            url: '{{URL::to("admin/customers/ajaxsearch")}}',
            data:'str='+$(this).val(),
            success: function(data){
                $("#suggesstion-box").show();
                $("#search-box").css("background","#FFF");
                if(data === "no_data")
                {
                    $("#suggesstion-box").html("<div class='text-danger'>Customer not found</div>");
                }
                else
                {
                    $("#suggesstion-box").html(data);
                }
            }
        });
    });
});

function selectMember(val) {
    $("#search-box").val(val);
    $("#search-box").focus();
    $("#suggesstion-box").hide();
}
</script>
@endsection