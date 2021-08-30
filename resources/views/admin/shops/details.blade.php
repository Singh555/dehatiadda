@extends('admin.layout')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1> Shop <small>Details...</small> </h1>
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
                        <h3 class="box-title">Shop Detail</h3>
                           
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
                             @if(!empty($result['shop']->image))
                            <div class="col-md-4">
                                
                                <img class="img img-responsive" src="{{asset($result['shop']->image)}}">
                            </div>
                             @endif
                             @if(!empty($result['shop']->shop_name))
                            <div class="col-md-8">
                <!-- /.box -->
                
                <div class="box card-profile">
                    
                    <div class="box-body">
                        <div class="col-md-12 table-responsive">
                            <table class="table table-striped table-condensed">
                                <tbody>
                                    <tr>
                                        <td><b>Shop Name:</b></td>
                                        <td>{{$result['shop']->shop_name}}</td>
                                        <td><b>{{ trans('labels.Mobile No') }}:</b></td>
                                        <td>{{$result['shop']->phone}}</td>
                                        <td><b>Email:</b></td>
                                        <td>{{$result['shop']->email}}</td>
                                        
                                    </tr>
                                    <tr>
                                       
                                        <td><b>Gst No:</b></td>
                                        <td>{{$result['shop']->gst_no}}</td>
                                        <td><b>Contact Person:</b></td>
                                        <td>{{$result['shop']->contact_person_name}}</td>
                                        <td><b>Contact Phone:</b></td>
                                        <td>{{$result['shop']->contact_person_phone}}</td>
                                    </tr>
                                    <tr>
                                        
                                        <td><b>Wallet:</b></td>
                                        <td>₹ {{$result['shop']->wallet_balance}}</td>
                                        <td><b>Address:</b></td>
                                        <td>{{$result['shop']->address}}</td>
                                        <td><b>Pin- {{$result['shop']->pin_code}}</b></td>
                                        <td>{{$result['shop']->city}} {{$result['shop']->state}} {{$result['shop']->country}}</td>
                                    </tr>
                                    <tr>
                                        
                                        <td><b>Status:</b></td>
                                        <td> {!!getStatusLabel($result['shop']->status)!!}</td>
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
                             <div class="col-md-12">&nbsp;</div>
                             @if(!empty($result['shop']->logo))
                             <div class="col-md-3">
                                 <img class="img img-responsive" src="{{asset($result['shop']->logo)}}">
                             </div>
                             @endif
                             @if(!empty($result['shop']->gst_image))
                             <div class="col-md-3">
                                 <img class="img img-responsive" src="{{asset($result['shop']->gst_image)}}">
                             </div>
                             @endif
                             @if(!empty($result['shop']->qr_code_img))
                             <div class="col-md-3">
                                 <img class="img img-responsive" src="{{asset($result['shop']->qr_code_img)}}">
                             </div>
                             @endif
                             
                              @if(!empty($result['shop']->status == 'PENDING'))
                              <div class="col-md-12">
                                  <h3 class="text-center">Approve Or Reject Shop</h3>
                                  <hr>
                              <div class="box-body">
                              {!! Form::open(array('url' =>'admin/shop/approve_reject', 'method'=>'post', 'class' => 'form-horizontal', 'enctype'=>'multipart/form-data')) !!}
                              {!! Form::hidden('id',$result['shop']->id, array('class'=>'form-control')) !!}
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
                                  <a href="{{url('admin/shop')}}" class="btn btn-primary"><i class="fa fa-backward"></i> Back</a>
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