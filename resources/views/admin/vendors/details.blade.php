@extends('admin.layout')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1> Vendor <small>Details...</small> </h1>
        <ol class="breadcrumb">
            <li><a href="{{ URL::to('admin/dashboard/this_month')}}"><i class="fa fa-dashboard"></i> {{ trans('labels.breadcrumb_dashboard') }}</a></li>
            <li class="active">Vendor</li>
        </ol>
    </section>

    <!-- Main content -->
   <section class="content">
    <!-- Main content -->
    
                <div class="row">
            <div class="col-md-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Vendor Detail</h3>
                           
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
                             @if(!empty($result['vendor']->id))
                            <div class="col-md-12">
                <!-- /.box -->
                
                <div class="box card-profile">
                    
                    <div class="box-body">
                        <div class="col-md-12 table-responsive">
                            <table class="table table-striped table-condensed">
                                <tbody>
                                    <tr>
                                        <td><b>Vendor Name:</b></td>
                                        <td>{{$result['vendor']->shopfname}} {{$result['vendor']->shoplname}}</td>
                                        <td><b>{{ trans('labels.Mobile No') }}:</b></td>
                                        <td>{{$result['vendor']->phone}}</td>
                                        <td><b>Email:</b></td>
                                        <td>{{$result['vendor']->email}}</td>
                                        
                                    </tr>
                                    <tr>
                                       
                                        <td><b>Gst No:</b></td>
                                        <td>{{$result['vendor']->gst_no}}</td>
                                        <td><b>Contact Person:</b></td>
                                        <td>{{$result['vendor']->first_name}} {{$result['vendor']->last_name}}</td>
                                    </tr>
                                    <tr>
                                        
                                        <td><b>Address:</b></td>
                                        <td>{{$result['vendor']->address}}</td>
                                        <td><b>Pin- {{$result['vendor']->pin_code}}</b></td>
                                        <td>{{$result['vendor']->city}} {{$result['vendor']->state}} {{$result['vendor']->country->Countries_name}}</td>
                                    </tr>
                                    <tr>
                                        
                                        <td><b>Status:</b></td>
                                        <td> {!!getStatusLabel($result['vendor']->status)!!}</td>
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
                             @if(count($result['vendor']->bankAccount) > 0)
                             <div class="col-md-12">
                                <h3 class="text-center">Bank details</h3>
                                  <hr> 
                                  <div class="box-body table-responsive">
                                      <table class="table table-striped">
                                          <thead>
                                              <tr>
                                                  <th>
                                                      @sortablelink('bank_name','Bank Name')
                                                  </th>
                                                  <th>
                                                      @sortablelink('account_no','Account No')
                                                  </th>
                                                  <th>
                                                      @sortablelink('holder_name','Account Holder')
                                                  </th>
                                                  <th>
                                                      @sortablelink('ifsc_code','IFSC')
                                                  </th>
                                              </tr> 
                                          </thead> 
                                          <tbody>
                                              @foreach($result['vendor']->bankAccount as $value)
                                               <tr>
                                                  <td>
                                                      {{$value->bank_name}}
                                                  </td>
                                                  <td>
                                                      {{$value->account_no}}
                                                  </td>
                                                  <td>
                                                      {{$value->holder_name}}
                                                  </td>
                                                  <td>
                                                      {{$value->ifsc_code}}
                                                  </td>
                                              </tr> 
                                              @endforeach
                                          </tbody>
                                      </table>
                                  </div>
                             </div>
                             @endif
                              @if(!empty($result['vendor']->status == 'PENDING'))
                              <div class="col-md-12">
                                  <h3 class="text-center">Approve Or Reject Vendor</h3>
                                  <hr>
                              <div class="box-body">
                              {!! Form::open(array('url' =>'admin/vendor/approve_reject', 'method'=>'post', 'class' => 'form-horizontal', 'enctype'=>'multipart/form-data')) !!}
                              {!! Form::hidden('id',$result['vendor']->id, array('class'=>'form-control')) !!}
                                  <div class="col-md-6">
                                  <div class="form-group">
                                      <label>Status <span class="text-danger">*</span></label>
                                      <select class="form-control" required='' name="status">
                                          <option value="">Select Status</option>
                                          <option value="ACTIVE">Active</option>
                                          <option value="REJECTED">Reject</option>
                                      </select>
                                  </div>
                                  </div>
                              <div class="col-md-1"></div>
                                 <div class="col-md-5">
                                  <div class="form-group">
                                      <label>Reason</label>
                                      <textarea name="reason" class="form-control"></textarea>
                                  </div>
                                  </div>
                              <div class="box-footer col-md-12 text-center">
                                  <a href="{{url('admin/vendor')}}" class="btn btn-primary"><i class="fa fa-backward"></i> Back</a>
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