@extends('admin.layout')
@section('css')
<link rel="stylesheet" href="{{ asset('vendor/file-manager/css/file-manager.css') }}">
@endsection
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1> {{ trans('labels.mailchimp') }} <small>{{ trans('labels.mailchimp_setting') }}...</small> </h1>
            <ol class="breadcrumb">
                <li><a href="{{ URL::to('admin/dashboard/this_month') }}"><i class="fa fa-dashboard"></i> {{ trans('labels.breadcrumb_dashboard') }}</a></li>
                <li class="active">{{ trans('labels.mailchimp_setting') }}</li>
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
                            <h3 class="box-title">{{ trans('labels.mailchimp_setting') }} </h3>
                        </div>

                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="row">
                                <div class="col-xs-12">
                                    <div class="box box-info">
                                        <!--<div class="box-header with-border">
                                          <h3 class="box-title">Setting</h3>
                                        </div>-->
                                        <!-- /.box-header -->
                                        <!-- form start -->
                                        <div class="box-body">
                                            @if( count($errors) > 0)
                                                @foreach($errors->all() as $error)
                                                    <div class="alert alert-success" role="alert">
                                                        <span class="icon fa fa-check" aria-hidden="true"></span>
                                                        <span class="sr-only">{{ trans('labels.Setting') }}Error:</span>
                                                        {{ $error }}
                                                    </div>
                                                @endforeach
                                            @endif

                                            {!! Form::open(array('url' =>'admin/updateSetting', 'method'=>'post', 'class' => 'form-horizontal', 'enctype'=>'multipart/form-data')) !!}
                                            <br>                                      
                                                                               


                                            <div class="form-group">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.MailChimp') }}</label>
                                                <div class="col-sm-10 col-md-4">
                                                    <select name="{{$result['settings'][119]->name}}" class="form-control">
                                                        <option @if($result['settings'][119]->value == '1')
                                                                selected
                                                                @endif
                                                                value="1"> {{ trans('labels.enable') }}</option>
                                                        <option @if($result['settings'][119]->value == '0')
                                                                selected
                                                                @endif
                                                                value="0"> {{ trans('labels.Disable') }}</option>
                                                    </select>

                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;margin-top: 0;">{{ trans('labels.Newsletter Text') }}</span>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.MailChimp API') }}</label>
                                                <div class="col-sm-10 col-md-4">
                                                    {!! Form::text($result['settings'][123]->name,  $result['settings'][123]->value, array('class'=>'form-control', 'id'=>$result['settings'][123]->name)) !!}
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;margin-top: 0;">{{ trans('labels.MailChimp API') }}</span>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.MailChimp LIST ID') }}</label>
                                                <div class="col-sm-10 col-md-4">
                                                    {!! Form::text($result['settings'][124]->name,  $result['settings'][124]->value, array('class'=>'form-control', 'id'=>$result['settings'][124]->name)) !!}
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;margin-top: 0;">{{ trans('labels.MailChimp LIST ID') }}</span>
                                                </div>
                                            </div>

                                            
                                            <div class="form-group" id="imageselected">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Newsletter Image') }}</label>
                                                <div class="col-sm-10 col-md-4">

                                                        <input type="text" autocomplete="off" class="form-control"  onclick="selectFileWithCKFinder(this)" placeholder="Click to choose Image" name="{{$result['settings'][125]->name}}" >
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px; text-align: left">{{ trans('labels.Newsletter Image') }}</span>

                                                    <br>
                                                </div>
                                                <div class="col-sm-10 col-md-4 previewImage">
                                                </div>
                                              </div>
                                            <div class="form-group">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">  </label>
                                                <div class="col-sm-10 col-md-4">
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">{{ trans('labels.OldImage') }}</span>
                                                    <br>
                                                    {!! Form::hidden('oldImage',  $result['settings'][125]->value , array('id'=>$result['settings'][125]->name)) !!}
                                                    <img src="{{asset($result['settings'][125]->value)}}" alt="" width="80px">
                                                </div>
                                            </div>
                                        </div>

                                        

                                        <!-- /.box-body -->
                                        <div class="box-footer text-center">
                                            <button type="submit" class="btn btn-primary">{{ trans('labels.Submit') }} </button>
                                            <a href="{{ URL::to('admin/dashboard/this_month')}}" type="button" class="btn btn-default">{{ trans('labels.back') }}</a>
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

            <!-- /.row -->
            <!-- Main row -->

            <!-- /.row -->
        </section>
        <!-- /.content -->
    </div>
@endsection
@section('js')
<script src="{{ asset('vendor/file-manager/js/file-manager.js') }}"></script>
<script>
                                    
                                    function selectFileWithCKFinder(elem){
                                       inputId = elem;
                                       window.open('/file-manager/fm-button', 'fm', 'width=1400,height=800');
                                     }
                                    // input
                                    let inputId = '';
                                    // set file link
                                    function fmSetLink($url) {
                                        var mainurl = '{{env('APP_URL')}}/';
                                        var res = $url.replace(""+mainurl+"", "");
                                                var image = '<img class="img img-responsive img-thumbnail" width="100px" src='+$url+'>';
                                        $(inputId).parent().parent().find('.previewImage').html(image);
                                    //document.getElementById(inputId).value = $url;
                                    $(inputId).val(res);
                                    }

                                    
                                                                    </script>

@endsection
