@extends('admin.layout')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1> <small>{{ trans('labels.SwalletTxn') }}...</small> </h1>
        <ol class="breadcrumb">
            <li><a href="{{ URL::to('admin/dashboard/this_month')}}"><i class="fa fa-dashboard"></i> {{ trans('labels.breadcrumb_dashboard') }}</a></li>
            <li><a href="{{ URL::to('admin/customers/display')}}"><i class="fa fa-users"></i> {{ trans('labels.Customers') }}</a></li>
            <li class="active"> {{ trans('labels.SwalletTxn') }}</li>
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

                                    
                                        {!! Form::open(array('url' =>'admin/customers/swalletview', 'method'=>'post', 'class' => 'form-horizontal form-validate', 'enctype'=>'multipart/form-data')) !!}
                                                                        <div class="form-group col-md-12">
                                                                        <label> Enter Customer Name/Mobile no./Referral Code</label>
                                                                        <input type="text" name="user_id" required='' class="form-control" id="search-box" @if(!empty($phone)) value="{{$phone}}" @endif autocomplete="off">
                                                                        <div id="suggesstion-box"></div>
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
                <h4 class="box-title text-center">Shopping Wallet Transactions</h4>
            </div>
            <div class="box-body">
            <div class="col-xs-12 table-responsive">
                                <table class="table table-bordered table-striped example1">
                                    <thead>
                                        <tr>
                                            <th>@sortablelink('id', trans('labels.ID') )</th>
                                            <th>@sortablelink('amount', trans('labels.Amount')) </th>
                                            <th>@sortablelink('txn_type', trans('labels.Type')) </th>
                                            <th>{{ trans('labels.Description') }} </th>
                                            <th>{{ trans('labels.Date') }} </th>
                                            <th>{{ trans('labels.After') }} {{ trans('labels.Balance') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (!empty($customers['swallet']) && count($customers['swallet'])>0)
                                        @foreach ($customers['swallet'] as $listingCustomers)
                                        <tr>
                                            <td>{{ $listingCustomers->id }}</td>
                                            <td>??? {{ $listingCustomers->txn_amount }}</td>
                                            <td>{!! getStatusLabel($listingCustomers->txn_type) !!}</td>
                                            <td>{{ $listingCustomers->txn_desc }}</td>
                                            <td>{{ $listingCustomers->txn_date }}</td>
                                            <td>{{ $listingCustomers->balance_after }}</td>
                                            
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