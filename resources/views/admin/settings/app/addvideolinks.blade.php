@extends('admin.layout')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1> {{ trans('labels.AddNewVideo') }} <small>{{ trans('labels.AddNewVideo') }}...</small> </h1>
            <ol class="breadcrumb">
                <li><a href="{{ URL::to('admin/dashboard/this_month') }}"><i class="fa fa-dashboard"></i> {{ trans('labels.breadcrumb_dashboard') }}</a></li>
                <li><a href="{{ URL::to('admin/banners')}}"><i class="fa fa-bars"></i> List All Banners</a></li>
                <li class="active"> {{ trans('labels.AddNewVideo') }}</li>
            </ol>
        </section>
<!-- Main content -->
        <section class="content">
        <div class="row">
                <div class="col-md-12">
                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">{{ trans('labels.AddNewVideo') }}</h3>
                        </div>

                        <!-- /.box-header -->
                        <div class="box-body">
                            
                                        <br>
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

                                    <!-- form start -->
                                        

                                            {!! Form::open(array('url' =>'admin/videolinks/insert', 'method'=>'post', 'class' => 'form-horizontal form-validate', 'enctype'=>'multipart/form-data')) !!}
                                            <div class="col-12">
                                            <div class="form-group">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Title') }} </label>
                                                <div class="col-sm-10 col-md-4">
                                                    {!! Form::text('title', '', array('class'=>'form-control field-validate','id'=>'title')) !!}
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">{{ trans('labels.Titletext') }}</span>
                                                </div>
                                            </div>
                                                

                                           <div class="form-group">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Link') }}</label>
                                                <div class="col-sm-10 col-md-4">
                                                    <input class="form-control field-validate" type="text" name="link" required=''>
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                    {{ trans('labels.VideoEmbedLink') }}</span>
                                                </div>
                                            </div>


                                            <div class="form-group">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.PublishDate') }}</label>
                                                <div class="col-sm-10 col-md-4">
                                                    <input class="form-control datepicker" type="text" name="publish_date" value="">
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                    {{ trans('labels.PublishDateVideo') }}</span>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Status') }}</label>
                                                <div class="col-sm-10 col-md-4">
                                                    <select class="form-control select2" style="width: 100%;" name="status">
                                                        <option value="1">{{ trans('labels.Active') }}</option>
                                                        <option value="0">{{ trans('labels.InActive') }}</option>
                                                    </select>
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                      {{ trans('labels.StatusBannerText') }}</span>
                                                </div>
                                            </div>
                                            </div>
                                            <!-- /.box-body -->
                                            <div class="box-footer text-center">
                                                <a href="{{ URL::to('admin/videolinks')}}" type="button" class="btn btn-default">{{ trans('labels.back') }}</a>
                                                <button type="submit" class="btn btn-primary">{{ trans('labels.Submit') }}</button>
                                            </div>
                                            <!-- /.box-footer -->
                                            {!! Form::close() !!}
                                       
                                    

                        </div>
                        <!-- /.box-body -->
                    </div>
                    </div>
                    </div>
        </section>            
    </div>
@endsection

@section('js')

<script>
    function viewtypes(elm){
       var type = $(elm).val();
       if(type=='banner'){
           $('.ViewType').text('Select Horrizontal Image for best View');
       }else{
           $('.ViewType').text('Select Verticle Image for best View');
       }
        
    }
    </script>

@endsection
