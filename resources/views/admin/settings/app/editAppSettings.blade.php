@extends('admin.layout')
@section('content')
<div class="content-wrapper"> 
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1> Edit Setting <small>Edit Setting...</small> </h1>
    <ol class="breadcrumb">
       <li><a href="{{ URL::to('admin/dashboard/this_month') }}"><i class="fa fa-dashboard"></i> {{ trans('labels.breadcrumb_dashboard') }}</a></li>
      <li><a href="{{ URL::to('admin/listingAppLabels')}}"><i class="fa fa-bars"></i> List All Labels</a></li>
      <li class="active">Edit Setting</li>
    </ol>
  </section>
  
  <!-- Main content -->
  <section class="content"> 
    <!-- Info boxes --> 
    
    <!-- /.row -->

    <div class="row">
      <div class="col-md-12">
        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Edit Setting </h3>
          </div>
          
          <!-- /.box-header -->
          <div class="box-body">
            <div class="row">
              <div class="col-xs-12">
              		<div class="box box-info">
                        <!--<div class="box-header with-border">
                          <h3 class="box-title">Edit category</h3>
                        </div>-->
                        <!-- /.box-header -->
                        <br>                       
                       @if( count($errors) > 0)
                                                @foreach($errors->all() as $error)
                                                    <div class="alert alert-success" role="alert">
                                                        <span class="icon fa fa-check" aria-hidden="true"></span>
                                                        <span class="sr-only">{{ trans('labels.Setting') }}:</span>
                                                        {{ $error }}
                                                    </div>
                                                @endforeach
                                            @endif
                        
                        <!-- form start -->                        
                         <div class="box-body">
                         
                            {!! Form::open(array('url' =>'admin/updateSetting', 'method'=>'post', 'class' => 'form-horizontal', 'enctype'=>'multipart/form-data')) !!}
                            {!! Form::hidden('id',  $result['data']->id , array('class'=>'form-control', 'id'=>'id')) !!}
                            
                                                       
                            <div class="form-group">
                              <label for="name" class="col-sm-2 col-md-3 control-label">{{$result['data']->title}}</label>
                              <div class="col-sm-10 col-md-4">
                                  @if($result['data']->value =='Y' || $result['data']->value =='N')
                                  <select class="form-control select2" name="{{$result['data']->name}}" style="width: 100%;"> 
                                                        <option @if($result['data']->value == 'Y')
                                                                selected
                                                                @endif
                                                                value="Y"> {{ trans('labels.Yes') }}</option>
                                                        <option @if($result['data']->value == 'N')
                                                                selected
                                                                @endif
                                                                value="N"> {{ trans('labels.No') }}</option>
                                                    </select>
                                  @elseif($result['data']->value =='1' || $result['data']->value =='0')
                                  <select name="{{$result['data']->name}}" class="form-control">
                                                        <option @if($result['data']->value == '1')
                                                                selected
                                                                @endif
                                                                value="1"> {{ trans('labels.Show') }}</option>
                                                        <option @if($result['data']->value == '0')
                                                                selected
                                                                @endif
                                                                value="0"> {{ trans('labels.Hide') }}</option>

                                                    </select>
                                  @else
                                  <input class="form-control" type="text" name="{{$result['data']->name}}" value="{{$result['data']->value}}">   
                                                    
                                  @endif
                              </div>
                            </div>
                            
                                
                                                             
                                               
                              <!-- /.box-body -->
                              <div class="box-footer text-center">
                                <button type="submit" class="btn btn-primary">{{ trans('labels.Submit') }}</button>
                                <a href="{{ URL::to('admin/appsettings')}}" type="button" class="btn btn-default">{{ trans('labels.back') }}</a>
                              </div>
                              <!-- /.box-footer -->
                            {!! Form::close() !!}
                        </div>
                  </div>
              </div>
            </div>
            
          </div>
          <!-- /.box-body --> 
        </div>
        <!-- /.box --> 
      </div>
      <!-- /.col --> 
    </div>
    <!-- /.row --> 
    
    <!-- Main row --> 
    
    <!-- /.row --> 
  </section>
  <!-- /.content --> 
</div>
@endsection 