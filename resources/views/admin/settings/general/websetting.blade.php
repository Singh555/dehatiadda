@extends('admin.layout')
@section('css')
<link rel="stylesheet" href="{{ asset('vendor/file-manager/css/file-manager.css') }}">
@endsection
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1> {{ trans('labels.website_settings') }} <small>{{ trans('labels.website_settings') }}...</small> </h1>
            <ol class="breadcrumb">
                <li><a href="{{ URL::to('admin/dashboard/this_month') }}"><i class="fa fa-dashboard"></i> {{ trans('labels.breadcrumb_dashboard') }}</a></li>
                <li class="active">{{ trans('labels.website_settings') }}</li>
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
                            <h3 class="box-title">{{ trans('labels.website_settings') }} </h3>
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
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.sitename logo') }}</label>
                                                <div class="col-sm-10 col-md-4">
                                                    <select name="{{$result['settings'][79]->name}}" class="form-control">
                                                        <option @if($result['settings'][79]->value == 'name')
                                                                selected
                                                                @endif
                                                                value="name"> {{ trans('labels.Name') }}</option>
                                                        <option @if($result['settings'][79]->value == 'logo')
                                                                selected
                                                                @endif
                                                                value="logo"> {{ trans('labels.Logo') }}</option>
                                                    </select>

                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;margin-top: 0;">{{ trans('labels.sitename logo Text') }}</span>
                                                </div>
                                            </div>


                                            <div class="form-group">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.website name') }}</label>
                                                <div class="col-sm-10 col-md-4">
                                                    <input type="text" id="{{$result['settings'][80]->name}}" name="{{$result['settings'][80]->name}}" class="form-control" value="<?=stripslashes($result['settings'][80]->value)?>">
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;margin-top: 0;">{{ trans('labels.website name text') }}</span>
                                                </div>
                                            </div>


                                            <div class="form-group" id="imageIcone">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.WebLogo') }}</label>
                                                <div class="col-sm-10 col-md-4">

                                                        <input type="text" autocomplete="off" class="form-control"  onclick="selectFileWithCKFinder(this)" placeholder="Click to choose Favicon" name="{{$result['settings'][16]->name}}">
                                                    
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">{{ trans('labels.WebLogoText') }}</span>
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
                                                    {!! Form::hidden('oldImage',  $result['settings'][16]->value , array('id'=>$result['settings'][16]->name)) !!}
                                                    <img src="{{asset($result['settings'][16]->value)}}" alt="" width="80px">
                                                </div>
                                            </div>

                                            
                                           <div class="form-group" id="imageIcone">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.FavIcon') }}</label>
                                                <div class="col-sm-10 col-md-4">

                                                        <input type="text" autocomplete="off" class="form-control"  onclick="selectFileWithCKFinder(this)" placeholder="Click to choose Favicon" name="{{$result['settings'][87]->name}}">
                                                    
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">{{ trans('labels.Choose Favicon for website') }}</span>
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
                                                    {!! Form::hidden('oldImage',  $result['settings'][87]->value , array('id'=>$result['settings'][87]->name)) !!}
                                                    <img src="{{asset($result['settings'][87]->value)}}" alt="" width="80px">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.facebookLink') }}</label>
                                                <div class="col-sm-10 col-md-4">
                                                    {!! Form::text($result['settings'][51]->name,  $result['settings'][51]->value, array('class'=>'form-control', 'id'=>$result['settings'][51]->name)) !!}
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;margin-top: 0;">{{ trans('labels.facebookLinkText') }}</span>
                                                </div>
                                            </div>


                                            <div class="form-group">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.googleLink') }}</label>
                                                <div class="col-sm-10 col-md-4">
                                                    {!! Form::text($result['settings'][52]->name,  $result['settings'][52]->value, array('class'=>'form-control', 'id'=>$result['settings'][52]->name)) !!}
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;margin-top: 0;">{{ trans('labels.googleLinkText') }}</span>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.twitterLink') }}</label>
                                                <div class="col-sm-10 col-md-4">
                                                    {!! Form::text($result['settings'][53]->name,  $result['settings'][53]->value, array('class'=>'form-control', 'id'=>$result['settings'][53]->name)) !!}
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;margin-top: 0;">{{ trans('labels.twitterLinkText') }}</span>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.linkedLink') }}</label>
                                                <div class="col-sm-10 col-md-4">
                                                    {!! Form::text($result['settings'][54]->name,  $result['settings'][54]->value, array('class'=>'form-control', 'id'=>$result['settings'][54]->name)) !!}
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;margin-top: 0;">{{ trans('labels.linkedLinkText') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.About Store') }}</label>
                                                <div class="col-sm-10 col-md-4">
                                                    {!! Form::text($result['settings'][112]->name,  $result['settings'][112]->value, array('class'=>'form-control', 'id'=>$result['settings'][112]->name)) !!}
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;margin-top: 0;">{{ trans('labels.linkedLinkText') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Contact Us Descrition') }}</label>
                                                <div class="col-sm-10 col-md-4">
                                                    {!! Form::text($result['settings'][113]->name,  $result['settings'][113]->value, array('class'=>'form-control', 'id'=>$result['settings'][113]->name)) !!}
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;margin-top: 0;">{{ trans('labels.linkedLinkText') }}</span>
                                                </div>
                                            </div>


                                            <div class="form-group">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Allow Cookies') }}</label>
                                                <div class="col-sm-10 col-md-4">
                                                    <select name="{{$result['settings'][120]->name}}" class="form-control">
                                                        <option @if($result['settings'][120]->value == '1')
                                                                selected
                                                                @endif
                                                                value="1"> {{ trans('labels.Yes') }}</option>
                                                        <option @if($result['settings'][120]->value == '0')
                                                                selected
                                                                @endif
                                                                value="0"> {{ trans('labels.No') }}</option>
                                                    </select>

                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;margin-top: 0;">{{ trans('labels.Allow Cookies Text') }}</span>
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
                                                var image = '<img class="img img-responsive img-thumbnail" width="200px" src='+$url+'>';
                                        $(inputId).parent().parent().find('.previewImage').html(image);
                                    //document.getElementById(inputId).value = $url;
                                    $(inputId).val(res);
                                    }

                                    
                                                                    </script>

@endsection