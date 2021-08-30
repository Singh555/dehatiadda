@extends('admin.layout')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1> {{ trans('labels.Customers') }} <small>{{ trans('labels.ListingWithdrawals') }}...</small> </h1>
        <ol class="breadcrumb">
            <li><a href="{{ URL::to('admin/dashboard/this_month')}}"><i class="fa fa-dashboard"></i> {{ trans('labels.breadcrumb_dashboard') }}</a></li>
            <li class="active">{{ trans('labels.ListingWithdrawals') }}</li>
        </ol>
    </section>
    <section class="content">
    <!-- Main content -->
    
                <div class="row">
            <div class="col-md-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">{{ trans('labels.ListingWithdrawals') }}</h3>
                           
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                @if (count($errors) > 0)
                                  @if($errors->any())
                                  <div class="alert alert-success alert-dismissible" role="alert">
                                      <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                      {{$errors->first()}}
                                  </div>
                                  @endif
                                @endif
                                @if(session()->has('message'))
                                          <div class="alert alert-success alert-dismissible" role="alert">
                                              <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                              {{ session()->get('message') }}
                                          </div>
                                      @endif
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12 table-responsive">
                                <table id="example1" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>@sortablelink('id', trans('labels.ID') )</th>
                                            <th>@sortablelink('amount', trans('labels.Amount')) </th>
                                            <th>{{trans('labels.Description')}} </th>
                                            <th>{{ trans('labels.Additional info') }} </th>
                                            <th>{{ trans('labels.Status') }} </th>
                                            <th class="notexport">{{ trans('labels.Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (isset($customers['result']))
                                        @foreach ($customers['result'] as $listingCustomers)
                                        <tr>
                                            <td>{{ $listingCustomers->id }} </td>
                                            <td>{{ $listingCustomers->amount }}</td>
                                            <td>{{ $listingCustomers->description }}</td>
                                            <td>                                               
                                                <strong>{{ trans('labels.Phone') }}: </strong> {{ $listingCustomers->phone }},&nbsp;
                                                <strong>{{ trans('labels.Name') }}: </strong> {{ $listingCustomers->first_name }}
                                                
                                            </td>
                                            <td>{!! getStatusLabel($listingCustomers->status) !!}</td>
                                            <td>
                                                <ul class="nav table-nav">
                                                    <li class="dropdown">
                                                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                                                            {{ trans('labels.Action') }} <span class="caret"></span>
                                                        </a>
                                                        <ul class="dropdown-menu">
                                                            @if($listingCustomers->status == 'REQUESTED')
                                                            <li role="presentation"><a data-toggle="tooltip" data-placement="bottom" title="{{ trans('labels.Paid') }}" id="payWithdrawalForm"
                                                                                       withdrawal_id="{{ $listingCustomers->id }}" class="btn btn-success text-white"><i class='fa fa-check-square'></i> &nbsp;{{ trans('labels.Paid') }}</a></li>
                                                            <div class="dropdown-divider"></div>
                                                            <li role="presentation"><a data-toggle="tooltip" data-placement="bottom" title="{{ trans('labels.Reject') }}" id="rejectWithdrawalForm"
                                                                                       withdrawal_id="{{ $listingCustomers->id }}" class="btn btn-danger"><i class='fa fa-ban'></i> &nbsp;{{ trans('labels.Reject') }}</a></li>
                                                                  @endif
                                                        </ul>
                                                    </li>
                                                </ul>
                                            </td>
                                        </tr>
                                        @endforeach
                                        @else
                                        <tr>
                                            <td colspan="4">{{ trans('labels.NoRecordFound') }}</td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                                @if (count($customers['result']) > 0)
                                <div class="col-xs-12 text-right">
                                    {{$customers['result']->links()}}
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <!-- /.box-body -->
                </div>
                </div>
                </div>
                <!-- /.box -->
            

        <!-- rejectWithdrawalModal -->
        <div class="modal fade" id="rejectWithdrawalModal" tabindex="-1" role="dialog" aria-labelledby="rejectWithdrawalModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content modal-lg">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="deleteCustomerModalLabel">{{ trans('labels.Reject') }}</h4>
                    </div>
                    {!! Form::open(array('url' =>'admin/customers/withdrawal/reject', 'method'=>'post', 'class' => 'form-horizontal', 'enctype'=>'multipart/form-data')) !!}
                    {!! Form::hidden('id','', array('class'=>'form-control', 'id'=>'withdrawal_id')) !!}
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Reason <span class="text-danger">*</span></label>
                        <input type="text" name="description" required='' class="form-control">
                         </div>
                        <p>{{ trans('labels.RejectWithdrawalText') }}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('labels.Close') }}</button>&nbsp;
                        <button type="submit" class="btn btn-danger">{{ trans('labels.Reject') }}</button>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>

        {{-- pay withdrawal modal --}}
        
        <div class="modal fade" id="payWithdrawalModal" tabindex="-1" role="dialog" aria-labelledby="payWithdrawalModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content modal-lg">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="deleteCustomerModalLabel">{{ trans('labels.Pay') }}</h4>
                    </div>
                    {!! Form::open(array('url' =>'admin/customers/withdrawal/pay', 'method'=>'post', 'class' => 'form-horizontal', 'enctype'=>'multipart/form-data')) !!}
                    {!! Form::hidden('id','', array('class'=>'form-control', 'id'=>'paywithdrawal_id')) !!}
                    <div class="modal-body">
                        <p>{{ trans('labels.PayWithdrawalText') }}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('labels.Close') }}</button>&nbsp;
                        <button type="submit" class="btn btn-success">{{ trans('labels.Pay') }}</button>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
        
        <div class="modal fade" id="notificationModal" tabindex="-1" role="dialog" aria-labelledby="notificationModalLabel">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content notificationContent">

                </div>
            </div>
        </div>
</section>
</div>

@endsection
@section('js')
<script>
   //deleteCustomerModal
	$(document).on('click', '#rejectWithdrawalForm', function(){
		var withdrawal_id = $(this).attr('withdrawal_id');
		$('#withdrawal_id').val(withdrawal_id);
		$("#rejectWithdrawalModal").modal('show');
	});
	$(document).on('click', '#payWithdrawalForm', function(){
		var withdrawal_id = $(this).attr('withdrawal_id');
		$('#paywithdrawal_id').val(withdrawal_id);
		$("#payWithdrawalModal").modal('show');
	});
 
</script>

@endsection