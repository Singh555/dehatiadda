@extends('admin.layout')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1> <small>{{ trans('labels.Customer') }}...</small> </h1>
        <ol class="breadcrumb">
            <li><a href="{{ URL::to('admin/dashboard/this_month')}}"><i class="fa fa-dashboard"></i> {{ trans('labels.breadcrumb_dashboard') }}</a></li>
            <li><a href="{{ URL::to('admin/customers/display')}}"><i class="fa fa-users"></i> {{ trans('labels.Customers') }}</a></li>
            <li class="active"> {{ trans('labels.Customer') }}</li>
        </ol>
        <a href="{{ URL::to('admin/customers/display')}}" class="btn btn-primary pull-right">{{ trans('labels.back') }}</a>
    </section>
    <!-- Main content -->
    <section class="content">
    <div class="row">
            <div class="col-md-4">
                <div class="box">
                    <div class="box-header">
                        
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <div class="row">
                            
                            <div class="col-xs-12">
                        
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

                                    
                                        {!! Form::open(array('url' =>'admin/customers/view', 'method'=>'post', 'class' => 'form-horizontal form-validate', 'enctype'=>'multipart/form-data')) !!}
                                                                        <div class="form-group col-md-12">
                                                                        <label> Enter Customer Name/Email./Referral Code</label>
                                                                        <input type="text" name="user_id" required='' class="form-control" id="search-box" @if(!empty($phone)) value="{{$phone}}" @endif autocomplete="off">
                                                                        <div id="suggesstion-box"></div>
                                                                        </div>
                                                                            
                                        
                                                                        
                                        
                                        
                                        
                                        <div class="box-footer text-center">
                                           <button type="submit" class="btn btn-primary">{{ trans('labels.Submit') }}</button>

                                        </div>
                                    {!! Form::close() !!}

                    </div>
                            
                    </div>
                    </div>
                    <!-- /.box-body -->
                </div>
                </div>
        @include('admin.customers.profile')
    
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