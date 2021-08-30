@extends('admin.layout')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1> {{ trans('labels.Customers') }} <small>{{ trans('labels.viewKyc') }}...</small> </h1>
        <ol class="breadcrumb">
            <li><a href="{{ URL::to('admin/dashboard/this_month')}}"><i class="fa fa-dashboard"></i> {{ trans('labels.breadcrumb_dashboard') }}</a></li>
            <li class="active">{{ trans('labels.viewKyc') }}</li>
        </ol>
    </section>

    <!-- Main content -->
   <section class="content">
    <!-- Main content -->
    
                <div class="row">
            <div class="col-md-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">{{ trans('labels.viewKyc') }}</h3>
                           
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
                             @if(!empty($customers['result']->id))
                             @php $listingCustomers = $customers['result']; @endphp
                            <div class="col-md-4">
                                <div class="card">
                                <div class="card-body">
                                    <table class="table table-responsive table-striped">
                                        <tbody>
                                            
                                            <tr>
                                                <td> <strong>{{trans('labels.Pan')}}:</strong></td>
                                                <td>{{$customers['result']->pan_no}}</td>
                                                </tr><tr>
                                                <td> <strong>{{trans('labels.Adhar')}}:</strong></td>
                                                <td>{{$customers['result']->adhar_no}}</td>
                                                
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                </div>
                                
                            </div>
                             @endif
                             @if(!empty($customers['result']->first_name))
                            <div class="col-md-8">
                <!-- /.box -->
                
                <div class="box card-profile">
                    
                    <div class="box-body">
                        <div class="col-md-12 table-responsive">
                            <table class="table table-striped table-condensed">
                                <tbody>
                                    <tr>
                                        <td><b>Name:</b></td>
                                        <td>{{$customers['result']->first_name}}</td>
                                        <td><b>{{ trans('labels.Mobile No') }}:</b></td>
                                        <td>{{$customers['result']->phone}}</td>
                                        <td><b>Email:</b></td>
                                        <td>{{$customers['result']->email}}</td>
                                        
                                        
                                    </tr>
                                    <tr>
                                       
                                        <td><b>Parent Referral code:</b></td>
                                        <td>{{$customers['result']->parent_id}}</td>
                                        
                                    </tr>
                                    <tr>
                                        
                                        <td><b>Main Wallet:</b></td>
                                        <td>₹ {{$customers['result']->m_wallet}}</td>
                                        <td><b>Shopping Wallet:</b></td>
                                        <td>₹ {{$customers['result']->s_wallet}}</td>
                                        <td><b>Kyc Status:</b></td>
                                        <td> {!!getStatusLabel($customers['result']->kyc)!!}</td>
                                    </tr>
                                    <tr>
                                        
                                        <td><b>Temp Income:</b></td>
                                        <td>₹ {!!getTemporaryIncome($customers['result']->member_code)!!}</td>
                                        <td><b>Temp Closing Income:</b></td>
                                        <td>₹ {!!getTemporaryClosingIncome($customers['result']->member_code)!!}</td>
                                    </tr>
                                    
                                </tbody>  
                            </table>
                            <hr>
                        </div>
                        
                        <div class="clearfix">&nbsp;</div>
                        

                    </div>
                </div>
                
                
                </div>
                             @endif
                             @if(!empty($customers['result']->adhar_front_file))
                             <div class="col-md-4">
                                 <img class="img img-responsive" src="{{asset($customers['result']->adhar_front_file)}}">
                             </div>
                             @endif
                             @if(!empty($customers['result']->adhar_back_file))
                             <div class="col-md-4">
                                 <img class="img img-responsive" src="{{asset($customers['result']->adhar_back_file)}}">
                             </div>
                             @endif
                             @if(!empty($customers['result']->pan_front_file))
                             <div class="col-md-4">
                                 <img class="img img-responsive" src="{{asset($customers['result']->pan_front_file)}}">
                             </div>
                             @endif
                              <div class="clearfix">&nbsp;</div>
                              @if(!empty($customers['result']->status == 'PENDING'))
                              <div class="col-md-12">
                                  <h3 class="text-center">Approve Or Reject Kyc</h3>
                                  <hr>
                              <div class="box-body">
                              {!! Form::open(array('url' =>'admin/customers/kyc/updatestatus', 'method'=>'post', 'class' => 'form-horizontal', 'enctype'=>'multipart/form-data')) !!}
                              {!! Form::hidden('id',$customers['result']->id, array('class'=>'form-control')) !!}
                                  <div class="col-md-6">
                                  <div class="form-group">
                                      <label>Status <span class="text-danger">*</span></label>
                                      <select class="form-control" required='' name="status">
                                          <option value="">Select Status</option>
                                          <option value="APPROVED">Approve</option>
                                          <option value="REJECTED">Reject</option>
                                      </select>
                                  </div>
                                  </div>
                              <div class="clearfix">&nbsp;</div>
                                  <div class="col-md-12">
                                  <div class="form-group">
                                      <label>Reason <span class="text-danger">*</span></label>
                                      <textarea name="description" required='' class="form-control"> </textarea>
                                  </div>
                                  </div>
                              <div class="box-footer col-md-12 text-center">
                                  <button type="submit" class="btn btn-danger processing">{{ trans('labels.Submit') }}</button>
                              </div>
                              {!! Form::close() !!}
                              </div>
                        </div>
                  @endif
                        </div>
                    </div>
                    <!-- /.box-body -->
                </div>
                </div>
                </div>
                <!-- /.box -->
            

       
</section>
</div>

@endsection
@section('js')


@endsection