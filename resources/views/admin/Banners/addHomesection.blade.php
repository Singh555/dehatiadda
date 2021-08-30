@extends('admin.layout')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1> {{ trans('labels.AddNewHomeSection') }}</small> </h1>
            <ol class="breadcrumb">
                <li><a href="{{ URL::to('admin/dashboard/this_month') }}"><i class="fa fa-dashboard"></i> {{ trans('labels.breadcrumb_dashboard') }}</a></li>
                <li><a href="{{ URL::to('admin/homeSections')}}"><i class="fa fa-bars"></i> List All Home Sections</a></li>
                <li class="active"> {{ trans('labels.AddNewHomeSection') }}</li>
            </ol>
        </section>
 <section class="content">
        <div class="row">
                <div class="col-md-12">
        
                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">{{ trans('labels.AddNewHomeSection') }}</h3>
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

                                    <!-- form start -->
                                        

                                            {!! Form::open(array('url' =>'admin/homeSections/insert', 'method'=>'post', 'class' => 'form-horizontal form-validate', 'enctype'=>'multipart/form-data')) !!}
                                            <div class="form-group">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Title') }} </label>
                                                <div class="col-sm-10 col-md-4">
                                                    {!! Form::text('title', '', array('class'=>'form-control field-validate','id'=>'banners_title')) !!}
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">{{ trans('labels.BannerTitletext') }}</span>
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

                                            <div class="form-group">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Type') }}</label>
                                                <div class="col-sm-10 col-md-4">
                                                    <select class="form-control select2" style="width: 100%;" name="type" id="bannerType" onchange="bannerstypes()">
                                                        <option value="category">{{ trans('labels.ChooseSubCategory') }}</option>
                                                        <option value="product">{{ trans('labels.Product') }}</option>
                                                    </select>
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                                        {{ trans('labels.AddBannerText') }}</span>
                                                </div>
                                            </div>

                                        <!--<div class="form-group banner-link">
                                  <label for="name" class="col-sm-2 col-md-3 col-sm-2 col-md-3 control-label">Banners Link </label>
                                  <div class="col-sm-10 col-md-4">
                                    {!! Form::text('banners_url', '', array('class'=>'form-control','id'=>'banners_url')) !!}
                                                </div>
                                              </div>-->

                                            <div class="form-group categoryContent">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Categories') }}</label>
                                                <div class="col-sm-10 col-md-4">
                                                    <select class="form-control select2" style="width: 100%;" name="categories_id[]" multiple="" id="categories_id">
                                                        @foreach($result['categories'] as $category)
                                                            <option value="{{ $category->id}}">@if($category->parent_name) {{ $category->parent_name}} / @endif{{ $category->name}}</option>
                                                        @endforeach
                                                    </select>
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                      {{ trans('labels.CategoriesbannerText') }}</span>
                                                </div>
                                            </div>

                                            <div class="form-group productContent" style="display: none">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Products') }}</label>
                                                <div class="col-sm-10 col-md-4">
                                                    <select class="form-control select2" style="width: 100%;" name="products_id[]" multiple="" id="products_id">
                                                        @foreach($result['products'] as $products_data)
                                                            <option value="{{ $products_data->products_id }}">{{ $products_data->products_name }}/ {{ $products_data->products_model }}</option>
                                                        @endforeach
                                                    </select>
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                      {{ trans('labels.ProductsBannerText') }}</span>
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
                                            <!-- /.box-body -->
                                            <div class="box-footer text-center">
                                                <a href="{{ URL::to('admin/homeSections')}}" type="button" class="btn btn-default">{{ trans('labels.back') }}</a>
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
