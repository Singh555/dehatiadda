@extends('admin.layout')
@section('css')
<link rel="stylesheet" href="{{ asset('vendor/file-manager/css/file-manager.css') }}">
@endsection
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1> {{ trans('labels.EditCategories') }} <small>{{ trans('labels.EditCategories') }}...</small> </h1>
        <ol class="breadcrumb">
            <li><a href="{{ URL::to('admin/dashboard/this_month') }}"><i class="fa fa-dashboard"></i> {{ trans('labels.breadcrumb_dashboard') }}</a></li>
            <li><a href="{{ URL::to('admin/categories/display')}}"><i class="fa fa-database"></i> {{ trans('labels.Categories') }}</a></li>
            <li class="active">{{ trans('labels.EditCategories') }}</li>
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
                        <h3 class="box-title">{{ trans('labels.EditCategories') }} </h3>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="box box-info">
                                    <br>
                                    @if (count($errors) > 0)
                                      @if($errors->any())
                                      <div class="alert alert-success alert-dismissible" role="alert">
                                          <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                          {{$errors->first()}}
                                      </div>
                                      @endif
                                    @endif
                                    <!-- /.box-header -->
                                    <!-- form start -->
                                    <div class="box-body">

                                        {!! Form::open(array('url' =>'admin/categories/update', 'method'=>'post', 'class' => 'form-horizontal form-validate', 'enctype'=>'multipart/form-data')) !!}

                                        {!! Form::hidden('id', $result['editSubCategory'][0]->id , array('class'=>'form-control', 'id'=>'id')) !!}
                                        {!! Form::hidden('oldImage', $result['editSubCategory'][0]->categories_image_url , array('id'=>'oldImage')) !!}
                                        {!! Form::hidden('oldIcon', $result['editSubCategory'][0]->categories_icon_url , array('id'=>'oldIcon')) !!}

                                        @if($result['editSubCategory'][0]->id >0 )
                                        <div class="form-group">
                                            <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Category') }}</label>
                                            <div class="col-sm-10 col-md-4">
                                                <select class="form-control" name="parent_id">
                                                    {{print_r($result['categories'])}}
                                                </select>
                                                <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">{{ trans('labels.ChooseMainCategory') }}</span>
                                            </div>
                                        </div>
                                        @else
                                        <select hidden name="parent_id">
                                            <option value="0">Leave As Parent</option>1
                                        </select>

                                        @endif

                                        @foreach($result['description'] as $description_data)
                                        <div class="form-group">
                                            <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Name') }} ({{ $description_data['language_name'] }})</label>
                                            <div class="col-sm-10 col-md-4">
                                                <input type="text" name="category_name_<?=$description_data['languages_id']?>" class="form-control field-validate" value="{{$description_data['name']}}">
                                                <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">{{ trans('labels.CategoryName') }} ({{ $description_data['language_name'] }}).</span>
                                                <span class="help-block hidden">{{ trans('labels.textRequiredFieldMessage') }}</span>
                                            </div>
                                        </div>
                                        @endforeach
                                        <div class="form-group">
                                            <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.slug') }} </label>
                                            <div class="col-sm-10 col-md-4">
                                                <input type="hidden" name="old_slug" value="{{$result['editSubCategory'][0]->slug}}">
                                                <input type="text" name="slug" class="form-control field-validate" value="{{$result['editSubCategory'][0]->slug}}">
                                                <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;margin-top: 0;">{{ trans('labels.slugText') }}</span>
                                                <span class="help-block hidden">{{ trans('labels.textRequiredFieldMessage') }}</span>
                                            </div>
                                        </div>

                                        
                                         <div class="form-group" id="imageselected">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Image') }}</label>
                                                <div class="col-sm-10 col-md-4">

                                                        <input type="file" autocomplete="off" class="form-control" onchange="previewImage(this);" name="image_id" >
                                                    
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">{{ trans('labels.CategoryImageText') }}</span>
                                                    <br>
                                                </div>
                                                <div class="col-sm-10 col-md-4 previewImage">
                                                    <img src="{{asset($result['editSubCategory'][0]->categories_image_url)}}" alt="" width=" 100px">
                                                </div>
                                                
                                            </div>
                                        
                                       <div class="form-group" id="imageIcone">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Icon') }}</label>
                                                <div class="col-sm-10 col-md-4">

                                                        <input type="file" autocomplete="off" class="form-control" onchange="previewImage(this);" name="image_icone" >
                                                    
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">{{ trans('labels.UploadSubCategoryIcon') }}</span>
                                                    <br>
                                                </div>
                                                <div class="col-sm-10 col-md-4 previewImage">
                                                     <img src="{{asset($result['editSubCategory'][0]->categories_icon_url)}}" alt="" width=" 100px">
                                                </div>
                                                
                                            </div>
                                        <div class="form-group">
                                          <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Status') }} </label>
                                          <div class="col-sm-10 col-md-4">
                                            <select class="form-control" name="categories_status">
                                                  <option value="1" @if($result['editSubCategory'][0]->categories_status=='1') selected @endif>{{ trans('labels.Active') }}</option>
                                                  <option value="0" @if($result['editSubCategory'][0]->categories_status=='0') selected @endif>{{ trans('labels.Inactive') }}</option>
                                            </select>
                                          <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                          {{ trans('labels.GeneralStatusText') }}</span>
                                          </div>
                                        </div>
                                        <div class="form-group">
                                          <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.apphome') }} </label>
                                          <div class="col-sm-10 col-md-4">
                                              <label class=" control-label">
                                                        <input type="radio" name="app_home" value="N" class="flat-red" @if($result['editSubCategory'][0]->app_home=='N') checked @endif > &nbsp;{{ trans('labels.No') }}
                                                    </label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                    <label class=" control-label">
                                                        <input type="radio" name="app_home" value="Y" class="flat-red" @if($result['editSubCategory'][0]->app_home=='Y') checked @endif >  &nbsp; {{ trans('labels.Yes') }}
                                                    </label>
                                          </div>
                                        </div>
                                    </div>
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

        <!-- /.box -->

        <!-- /.col -->

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
