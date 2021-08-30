@extends('admin.layout')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1> Edit Home Section</h1>
            <ol class="breadcrumb">
                <li><a href="{{ URL::to('admin/dashboard/this_month') }}"><i class="fa fa-dashboard"></i> {{ trans('labels.breadcrumb_dashboard') }}</a></li>
                <li><a href="{{ URL::to('admin/homeSections')}}"><i class="fa fa-bars"></i> List All Home Section</a></li>
                <li class="active">Edit Home Section</li>
            </ol>
        </section>
<section class="content">
        <div class="row">
                <div class="col-md-12">
        
                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">Edit Home Section </h3>
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
                                    <!--<div class="box-header with-border">
                          <h3 class="box-title">Edit category</h3>
                        </div>-->
                                        <!-- /.box-header -->
                                        <!-- form start -->
                                        

                                            {!! Form::open(array('url' =>'admin/homeSections/update', 'method'=>'post', 'class' => 'form-horizontal', 'enctype'=>'multipart/form-data')) !!}

                                            {!! Form::hidden('id',  $result['banners'][0]->id , array('class'=>'form-control', 'id'=>'id')) !!}
                                            <div class="form-group">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Title') }} </label>
                                                <div class="col-sm-10 col-md-4">
                                                    {!! Form::text('title', $result['banners'][0]->title, array('class'=>'form-control','id'=>'banners_title')) !!}
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">{{ trans('labels.BannerTitletext') }}</span>
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


                                            <div class="form-group">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Type') }}</label>
                                                <div class="col-sm-10 col-md-4">
                                                    <select class="form-control select2" style="width: 100%;" name="type" id="bannerType">
                                                        <option value="category" @if($result['banners'][0]->type=='category') selected @endif>
                                                            {{ trans('labels.ChooseSubCategory') }}</option>
                                                        <option value="product" @if($result['banners'][0]->type=='product') selected @endif>Product</option>
                                                    </select>
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                                {{ trans('labels.AddBannerText') }}</span>
                                                </div>
                                            </div>

                                            
                                              @php
                                              $ids = explode(",",$result['banners'][0]->ids);
                                              @endphp


                                            <div class="form-group categoryContent" @if($result['banners'][0]->type!='category') style="display: none" @endif >
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Categories') }}</label>
                                                <div class="col-sm-10 col-md-4">
                                                    <select class="form-control select2" style="width: 100%;" name="categories_id[]" multiple='' id="categories_id">
                                                        @foreach($result['categories'] as $category)
                                                            <option value="{{ $category->id}}" @if(!empty($ids) && in_array($category->id,$ids)) selected='' @endif>@if($category->parent_name) {{ $category->parent_name}} / @endif{{ $category->name}}</option>
                                                        @endforeach
                                                    </select>
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                                    {{ trans('labels.CategoriesbannerText') }}</span>
                                                </div>
                                            </div>

                                            <div class="form-group productContent" @if($result['banners'][0]->type!='product') style="display: none" @endif>
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Products') }}</label>
                                                <div class="col-sm-10 col-md-4">
                                                    <select class="form-control select2" style="width: 100%;" name="products_id[]" id="products_id" multiple=''>
                                                        @foreach($result['products'] as $products_data)
                                                            <option value="{{ $products_data->products_id }}" @if(!empty($ids) && in_array($products_data->products_id,$ids)) selected='' @endif>{{ $products_data->products_name }}/ {{ $products_data->products_model }}</option>
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
                                                        <option value="1" @if($result['banners'][0]->status==1) selected @endif>{{ trans('labels.Active') }}</option>
                                                        <option value="0" @if($result['banners'][0]->status==0) selected @endif>{{ trans('labels.Inactive') }}</option>
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
