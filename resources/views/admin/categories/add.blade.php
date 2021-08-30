@extends('admin.layout')
@section('css')
<link rel="stylesheet" href="{{ asset('vendor/file-manager/css/file-manager.css') }}">
@endsection
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1> {{ trans('labels.Categories') }} <small>{{ trans('labels.AddCategories') }}...</small> </h1>
        <ol class="breadcrumb">
            <li><a href="{{ URL::to('admin/dashboard/this_month') }}"><i class="fa fa-dashboard"></i> {{ trans('labels.breadcrumb_dashboard') }}</a></li>
            <li><a href="{{ URL::to('admin/categories/display')}}"><i class="fa fa-database"></i>{{ trans('labels.Categories') }}</a></li>
            <li class="active">{{ trans('labels.AddCategory') }}</li>
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
                        <h3 class="box-title">{{ trans('labels.AddCategories') }} </h3>
                    </div>

                    <!-- /.box-header -->
                    <div class="box-body">
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="box box-info">
                                    <!-- form start -->
                                    <br>
                                    @if (count($errors) > 0)
                                    @if($errors->any())
                                    <div class="alert alert-success alert-dismissible" role="alert">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                        {{$errors->first()}}
                                    </div>
                                    @endif
                                    @endif
                                    <div class="box-body">

                                        {!! Form::open(array('url' =>'admin/categories/add', 'method'=>'post', 'class' => 'form-horizontal form-validate', 'enctype'=>'multipart/form-data')) !!}
                                        <div class="form-group">
                                            <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Category') }}</label>
                                            <div class="col-sm-10 col-md-4">
                                                <select class="form-control" name="parent_id">
                                                    {{print_r($result['categories'])}}
                                                </select>
                                                <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                                    {{ trans('labels.ChooseMainCategory') }}</span>
                                            </div>
                                        </div>

                                        @foreach($result['languages'] as $languages)
                                        <div class="form-group">
                                            <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Name') }}<span style="color:red;">*</span> ({{ $languages->name }})</label>
                                            <div class="col-sm-10 col-md-4">
                                                <input name="categoryName_<?=$languages->languages_id?>" class="form-control field-validate">
                                                <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                                    {{ trans('labels.SubCategoryName') }} ({{ $languages->name }}).</span>
                                                <span class="help-block hidden">{{ trans('labels.textRequiredFieldMessage') }}</span>
                                            </div>
                                        </div>
                                        @endforeach
                                         <div class="form-group" id="imageselected">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Image') }}</label>
                                                <div class="col-sm-10 col-md-4">

                                                        <input type="file" autocomplete="off" class="form-control" onchange="previewImage(this);" name="image_id" required="">
                                                    
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">{{ trans('labels.CategoryImageText') }}</span>
                                                    <br>
                                                </div>
                                                
                                                <div class="col-sm-10 col-md-4 hidden previewImage">
                                                </div>
                                                
                                            </div>
                                        
                                       <div class="form-group" id="imageIcone">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Icon') }}</label>
                                                <div class="col-sm-10 col-md-4">

                                                        <input type="file" autocomplete="off" class="form-control" onchange="previewImage(this);" name="image_icone" required="">
                                                    
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">{{ trans('labels.UploadSubCategoryIcon') }}</span>
                                                    <br>
                                                </div>
                                                <div class="col-sm-10 col-md-4 hidden previewImage">
                                                </div>
                                                
                                            </div>
                                        

                                        <div class="form-group">
                                          <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Status') }} </label>
                                          <div class="col-sm-10 col-md-4">
                                            <select class="form-control" name="categories_status">
                                                  <option value="1">{{ trans('labels.Active') }}</option>
                                                  <option value="0">{{ trans('labels.Inactive') }}</option>
                                            </select>
                                          <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                          {{ trans('labels.GeneralStatusText') }}</span>
                                          </div>
                                        </div>
                                        <div class="form-group">
                                          <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.apphome') }} </label>
                                          <div class="col-sm-10 col-md-4">
                                              <label class=" control-label">
                                                        <input type="radio" name="app_home" value="N" class="flat-red"  checked="" > &nbsp;{{ trans('labels.No') }}
                                                    </label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                    <label class=" control-label">
                                                        <input type="radio" name="app_home" value="Y" class="flat-red" >  &nbsp; {{ trans('labels.Yes') }}
                                                    </label>
                                          </div>
                                        </div>
                                        <!-- /.box-body -->
                                        <div class="box-footer text-center">
                                            <button type="submit" class="btn btn-primary">{{ trans('labels.Submit') }}</button>
                                            <a href="{{ URL::to('admin/categories/display')}}" type="button" class="btn btn-default">{{ trans('labels.back') }}</a>
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
@section('js')
<script>
                                    
                                    function previewImage(elem){
                                       var inputId = elem;
                                    // set file link
                                        var res = window.URL.createObjectURL(elem.files[0]);
                                        $(inputId).parent().parent().find('.previewImage').removeClass('hidden');
                                                var image = '<img class="img img-responsive img-thumbnail" width="200px" src='+res+'>';
                                        $(inputId).parent().parent().find('.previewImage').html(image);
                                    }

                                    
                                                                    </script>

@endsection
