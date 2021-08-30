@extends('admin.layout')
@section('css')
<link rel="stylesheet" href="{{ asset('vendor/file-manager/css/file-manager.css') }}">
@endsection
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1> {{ trans('labels.AddBanner') }} <small>{{ trans('labels.AddBanner') }}...</small> </h1>
            <ol class="breadcrumb">
                <li><a href="{{ URL::to('admin/dashboard/this_month') }}"><i class="fa fa-dashboard"></i> {{ trans('labels.breadcrumb_dashboard') }}</a></li>
                <li><a href="{{ URL::to('admin/banners')}}"><i class="fa fa-bars"></i> List All Banners</a></li>
                <li class="active">{{ trans('labels.AddBanner') }}</li>
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
                            <h3 class="box-title">{{ trans('labels.AddBanner') }}</h3>
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

                                    <!-- form start -->
                                        <div class="box-body">

                                            {!! Form::open(array('url' =>'admin/banners/insert', 'method'=>'post', 'class' => 'form-horizontal form-validate', 'enctype'=>'multipart/form-data')) !!}
                                            <div class="form-group">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Title') }} </label>
                                                <div class="col-sm-10 col-md-4">
                                                    {!! Form::text('banners_title', '', array('class'=>'form-control field-validate','id'=>'banners_title')) !!}
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">{{ trans('labels.BannerTitletext') }}</span>
                                                </div>
                                            </div>
                                             <div class="form-group">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.ViewType') }} </label>
                                                <div class="col-sm-10 col-md-4">
                                                    <select class="form-control select2" style="width: 100%;" name="view_type" id="viewType" onchange="viewtypes(this)">
                                                        <option value="banner">{{ trans('labels.Banner') }}</option>
                                                        <option value="slider">{{ trans('labels.Slider') }}</option>
                                                        
                                                    </select>
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                                        Select View type as banner or Slider</span>
                                                    <span class="help-block ViewType text-danger" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                                       Select Horrizontal Image for best View</span>
                                                    
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Position') }} </label>
                                                <div class="col-sm-10 col-md-4">
                                                    <select class="form-control select2" style="width: 100%;" name="view_position">
                                                        <option value="top">{{ trans('labels.Top') }}</option>
                                                        <option value="middle">{{ trans('labels.Middle') }}</option>
                                                        <option value="bottom">{{ trans('labels.Bottom') }}</option>
                                                        
                                                    </select>
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                                        Select View position as top, Bottom or Middle</span>
                                                </div>
                                            </div>
                                            
                                                <div class="form-group" id="imageselected">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Image') }}</label>
                                                <div class="col-sm-10 col-md-4">

                                                        <input type="text" autocomplete="off" class="form-control"  onclick="selectFileWithCKFinder(this)" placeholder="Click to choose Image" name="image_id" required="">
                                                    
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">{{ trans('labels.ImageText') }}</span>
                                                    <br>
                                                </div>
                                                <div class="col-sm-10 col-md-4 hidden previewImage">
                                                </div>
                                                
                                            </div>
                                                
                                            <div class="form-group">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Type') }}</label>
                                                <div class="col-sm-10 col-md-4">
                                                    <select class="form-control" name="type" id="bannerType" onchange="bannerstypes()">
                                                        <option value="category">{{ trans('labels.ChooseSubCategory') }}</option>
                                                        <option value="product">{{ trans('labels.Product') }}</option>
                                                        <option value="top seller">{{ trans('labels.TopSeller') }}</option>
                                                        <option value="deals">{{ trans('labels.Deals') }}</option>
                                                        <option value="most liked">{{ trans('labels.MostLiked') }}</option>
                                                    </select>
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                                        {{ trans('labels.AddBannerText') }}</span>
                                                </div>
                                            </div>

                                        <!--<div class="form-group banner-link">
                                  <label for="name" class="col-sm-2 col-md-3 control-label">Banners Link </label>
                                  <div class="col-sm-10 col-md-4">
                                    {!! Form::text('banners_url', '', array('class'=>'form-control','id'=>'banners_url')) !!}
                                                </div>
                                              </div>-->

                                            <div class="form-group categoryContent">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Categories') }}</label>
                                                <div class="col-sm-10 col-md-4">
                                                    <select class="form-control select2" name="categories_id" id="categories_id">
                                                        @foreach($result['categories'] as $category)
                                                            <option value="{{ $category->id}}">@if($category->parent_name) {{ $category->parent_name}} / @endif {{ $category->name}}</option>
                                                        @endforeach
                                                    </select>
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                      {{ trans('labels.CategoriesbannerText') }}</span>
                                                </div>
                                            </div>

                                            <div class="form-group productContent" style="display: none">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Products') }}</label>
                                                <div class="col-sm-10 col-md-4">
                                                    <select class="form-control select2" name="products_id" id="products_id">
                                                        @foreach($result['products'] as $products_data)
                                                            <option value="{{ $products_data->products_id }}">{{ $products_data->products_name }}/ {{ $products_data->products_model }}</option>
                                                        @endforeach
                                                    </select>
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                      {{ trans('labels.ProductsBannerText') }}</span>
                                                </div>
                                            </div>



                                            <div class="form-group">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.ExpiryDate') }}</label>
                                                <div class="col-sm-10 col-md-4">
                                                    <input readonly class="form-control datepicker field-validate" type="text" name="expires_date" value="">
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                    {{ trans('labels.ExpiryDateBanner') }}</span>
                                                </div>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Status') }}</label>
                                                <div class="col-sm-10 col-md-4">
                                                    <select class="form-control" name="status">
                                                        <option value="1">{{ trans('labels.Active') }}</option>
                                                        <option value="0">{{ trans('labels.InActive') }}</option>
                                                    </select>
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                      {{ trans('labels.StatusBannerText') }}</span>
                                                </div>
                                            </div>

                                            <!-- /.box-body -->
                                            <div class="box-footer text-center">
                                                <button type="submit" class="btn btn-primary">{{ trans('labels.Submit') }}</button>
                                                <a href="{{ URL::to('admin/banners')}}" type="button" class="btn btn-default">{{ trans('labels.back') }}</a>
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
                                        $(inputId).parent().parent().find('.previewImage').removeClass('hidden');
                                                var image = '<img class="img img-responsive img-thumbnail" width="200px" src='+$url+'>';
                                        $(inputId).parent().parent().find('.previewImage').html(image);
                                    //document.getElementById(inputId).value = $url;
                                    $(inputId).val(res);
                                    }

                                    
                                                                    </script>

@endsection