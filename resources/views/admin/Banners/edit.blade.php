@extends('admin.layout')
@section('css')
<link rel="stylesheet" href="{{ asset('vendor/file-manager/css/file-manager.css') }}">
@endsection
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1> {{ trans('labels.EditBanner') }} <small>{{ trans('labels.EditBanner') }}...</small> </h1>
            <ol class="breadcrumb">
                <li><a href="{{ URL::to('admin/dashboard/this_month') }}"><i class="fa fa-dashboard"></i> {{ trans('labels.breadcrumb_dashboard') }}</a></li>
                <li><a href="{{ URL::to('admin/banners')}}"><i class="fa fa-bars"></i> {{ trans('labels.ListingAllBanners') }}</a></li>
                <li class="active">{{ trans('labels.EditBanner') }}</li>
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
                            <h3 class="box-title">{{ trans('labels.EditBanner') }} </h3>
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
                                    <!--<div class="box-header with-border">
                          <h3 class="box-title">Edit category</h3>
                        </div>-->
                                        <!-- /.box-header -->
                                        <!-- form start -->
                                        <div class="box-body">

                                            {!! Form::open(array('url' =>'admin/banners/update', 'method'=>'post', 'class' => 'form-horizontal', 'enctype'=>'multipart/form-data')) !!}

                                            {!! Form::hidden('id',  $result['banners'][0]->banners_id , array('class'=>'form-control', 'id'=>'id')) !!}
                                            {!! Form::hidden('oldImage',  $result['banners'][0]->banners_image_url, array('id'=>'oldImage')) !!}

                                            <div class="form-group">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Title') }} </label>
                                                <div class="col-sm-10 col-md-4">
                                                    {!! Form::text('banners_title', $result['banners'][0]->banners_title, array('class'=>'form-control','id'=>'banners_title')) !!}
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">{{ trans('labels.BannerTitletext') }}</span>
                                                </div>
                                            </div>
                                           <div class="form-group">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.ViewType') }} </label>
                                                <div class="col-sm-10 col-md-4">
                                                    <select class="form-control select2" style="width: 100%;" name="view_type" id="viewType" onchange="viewtypes(this)">
                                                        <option value="banner" @if($result['banners'][0]->view_type == 'banner') selected='' @endif>{{ trans('labels.Banner') }}</option>
                                                        <option value="slider" @if($result['banners'][0]->view_type == 'slider') selected='' @endif>{{ trans('labels.Slider') }}</option>
                                                        
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
                                                        <option value="top" @if($result['banners'][0]->view_position == 'top') selected='' @endif>{{ trans('labels.Top') }}</option>
                                                        <option value="middle" @if($result['banners'][0]->view_position == 'middle') selected='' @endif>{{ trans('labels.Middle') }}</option>
                                                        <option value="bottom" @if($result['banners'][0]->view_position == 'bottom') selected='' @endif>{{ trans('labels.Bottom') }}</option>
                                                        
                                                    </select>
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                                        Select View position as top, Bottom or Middle</span>
                                                </div>
                                            </div>
                                            
                                         <div class="form-group" id="imageselected">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Image') }}</label>
                                                <div class="col-sm-10 col-md-4">

                                                        <input type="text" autocomplete="off" class="form-control"  onclick="selectFileWithCKFinder(this)" placeholder="Click to choose Image" name="image_id" >
                                                    
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">{{ trans('labels.ImageText') }}</span>
                                                    <br>
                                                </div>
                                                <div class="col-sm-10 col-md-4 previewImage">
                                                </div>
                                                
                                            </div>
                                            

                                            <div class="form-group">
                                                <label for="name" class="col-sm-2 col-md-3 control-label"></label>
                                                <div class="col-sm-10 col-md-4">

                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">{{ trans('labels.OldImage') }}</span>
                                                    <br>

                                                    <img src="{{asset($result['banners'][0]->banners_image_url)}}" alt="" width=" 100px">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Type') }}</label>
                                                <div class="col-sm-10 col-md-4">
                                                    <select class="form-control" name="type" id="bannerType">
                                                        <option value="category" @if($result['banners'][0]->type=='category') selected @endif>
                                                            {{ trans('labels.ChooseSubCategory') }}</option>
                                                        <option value="product" @if($result['banners'][0]->type=='product') selected @endif>Product</option>
                                                        <option value="top seller" @if($result['banners'][0]->type=='top seller') selected @endif>Top Seller</option>
                                                        <option value="deals" @if($result['banners'][0]->type=='deals') selected @endif>Deals</option>
                                                        <option value="most liked" @if($result['banners'][0]->type=='most liked') selected @endif>Most Liked</option>
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

                                            <div class="form-group categoryContent" @if($result['banners'][0]->type!='category') style="display: none" @endif >
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Categories') }}</label>
                                                <div class="col-sm-10 col-md-4">
                                                    <select class="form-control select2" name="categories_id" id="categories_id">
                                                        @foreach($result['categories'] as $category)
                                                            <option value="{{ $category->id}}" @if($result['banners'][0]->banners_url == $category->id) selected='' @endif>@if($category->parent_name) {{ $category->parent_name}} / @endif{{ $category->name}}</option>
                                                        @endforeach
                                                    </select>
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                                    {{ trans('labels.CategoriesbannerText') }}</span>
                                                </div>
                                            </div>

                                            <div class="form-group productContent" @if($result['banners'][0]->type!='product') style="display: none" @endif>
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Products') }}</label>
                                                <div class="col-sm-10 col-md-4">
                                                    <select class="form-control select2" name="products_id" id="products_id">
                                                        @foreach($result['products'] as $products_data)
                                                            <option value="{{ $products_data->products_id }}" @if($result['banners'][0]->banners_url == $category->id) selected='' @endif>{{ $products_data->products_name }}/ {{ $products_data->products_model }}</option>
                                                        @endforeach
                                                    </select>
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                                    {{ trans('labels.ProductsBannerText') }}</span>
                                                </div>
                                            </div>

                                            
                                            



                                        <!--<div class="form-group">
                                  <label for="name" class="col-sm-2 col-md-3 control-label">Banners URL </label>
                                  <div class="col-sm-10 col-md-4">
                                    {!! Form::text('banners_url', $result['banners'][0]->banners_url, array('class'=>'form-control','id'=>'banners_url')) !!}

                                                </div>
                                              </div>-->

                                            <div class="form-group">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.ExpiryDate') }}</label>
                                                <div class="col-sm-10 col-md-4">



                                                    @if(!empty($result['banners'][0]->expires_date))
                                                        {!! Form::text('expires_date', date('d/m/Y', strtotime($result['banners'][0]->expires_date)), array('class'=>'form-control datepicker', 'id'=>'expires_date')) !!}
                                                    @else
                                                        {!! Form::text('expires_date', '', array('class'=>'form-control datepicker', 'id'=>'expires_date')) !!}

                                                    @endif
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                    {{ trans('labels.ExpiryDateBanner') }}</span>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Status') }}</label>
                                                <div class="col-sm-10 col-md-4">
                                                    <select class="form-control" name="status">
                                                        <option value="1" @if($result['banners'][0]->status==1) selected @endif>{{ trans('labels.Active') }}</option>
                                                        <option value="0" @if($result['banners'][0]->status==0) selected @endif>{{ trans('labels.Inactive') }}</option>
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
                                                var image = '<img class="img img-responsive img-thumbnail" width="200px" src='+$url+'>';
                                        $(inputId).parent().parent().find('.previewImage').html(image);
                                    //document.getElementById(inputId).value = $url;
                                    $(inputId).val(res);
                                    }

                                    
                                                                    </script>

@endsection
