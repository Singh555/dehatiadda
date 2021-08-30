@extends('admin.layout')
@section('css')
<link rel="stylesheet" href="{{ asset('vendor/file-manager/css/file-manager.css') }}">
@endsection
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1> {{ trans('labels.AddProduct') }} <small>{{ trans('labels.AddProduct') }}...</small> </h1>
        <ol class="breadcrumb">
            <li><a href="{{ URL::to('admin/dashboard/this_month') }}"><i class="fa fa-dashboard"></i> {{ trans('labels.breadcrumb_dashboard') }}</a></li>
            <li><a href="{{ URL::to('admin/products/display')}}"><i class="fa fa-database"></i> {{ trans('labels.ListingAllProducts') }}</a></li>
            <li class="active">{{ trans('labels.AddProduct') }}</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">{{ trans('labels.AddNewProduct') }} </h3>
                    </div>

                    <!-- /.box-header -->
                    <div class="box-body">
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="box box-info">
                                    <!-- form start -->
                                    <div class="box-body">
                                        @if( count($errors) > 0)
                                        @foreach($errors->all() as $error)
                                        <div class="alert alert-danger" role="alert">
                                            <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                                            <span class="sr-only">{{ trans('labels.Error') }}:</span>
                                            {{ $error }}
                                        </div>
                                        @endforeach
                                        @endif

                                        {!! Form::open(array('url' =>'admin/products/add', 'method'=>'post', 'class' => 'form-horizontal form-validate', 'enctype'=>'multipart/form-data')) !!}

                                        <div class="row">
                                            <div class="col-xs-12 col-md-6">
                                                <div class="form-group">
                                                    <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Product Type') }}<span style="color:red;">*</span></label>
                                                    <div class="col-sm-10 col-md-8">
                                                        <select class="form-control field-validate prodcust-type" name="products_type" onChange="prodcust_type();">
                                                            <option value="">{{ trans('labels.Choose Type') }}</option>
                                                            <option value="0">{{ trans('labels.Simple') }}</option>
                                                            <option value="1">{{ trans('labels.Variable') }}</option>
                                                        </select><span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                                            {{ trans('labels.Product Type Text') }}.</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-md-6">
                                                <div class="form-group">
                                                    <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Manufacturers') }} </label>
                                                    <div class="col-sm-10 col-md-8">
                                                        <select class="form-control" name="manufacturers_id">
                                                            <option value="">{{ trans('labels.ChooseManufacturers') }}</option>
                                                            @foreach ($result['manufacturer'] as $manufacturer)
                                                            <option value="{{ $manufacturer->id }}">{{ $manufacturer->name }}</option>
                                                            @endforeach
                                                        </select><span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                                            {{ trans('labels.ChooseManufacturerText') }}.</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-12">
                                                <div class="form-group">
                                                    <label for="name" class="col-sm-2 col-md-2 control-label">{{ trans('labels.Category') }}<span style="color:red;">*</span></label>
                                                    <div class="col-sm-10 col-md-9">
                                                        <?php print_r($result['categories']); ?>
                                                        <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                                            {{ trans('labels.ChooseCatgoryText') }}.</span>
                                                        <span class="help-block hidden">{{ trans('labels.textRequiredFieldMessage') }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-xs-12 col-md-6">
                                                <div class="form-group">
                                                    <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.IsFeature') }} </label>
                                                    <div class="col-sm-10 col-md-8">
                                                        <select class="form-control" name="is_feature">
                                                            <option value="0">{{ trans('labels.No') }}</option>
                                                            <option value="1">{{ trans('labels.Yes') }}</option>
                                                        </select>
                                                        <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                                            {{ trans('labels.IsFeatureProuctsText') }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-md-6">
                                                <div class="form-group">
                                                    <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Status') }} </label>
                                                    <div class="col-sm-10 col-md-8">
                                                        <select class="form-control" name="products_status">
                                                            <option value="1">{{ trans('labels.Active') }}</option>
                                                            <option value="0">{{ trans('labels.Inactive') }}</option>
                                                        </select>
                                                        <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                                            {{ trans('labels.SelectStatus') }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-xs-12 col-md-6">
                                                <div class="form-group">
                                                    <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.ProductsPrice') }}<span style="color:red;">*</span></label>
                                                    <div class="col-sm-10 col-md-8">
                                                        {!! Form::text('products_price', '', array('class'=>'form-control number-validate', 'id'=>'products_price')) !!}
                                                        <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                                            {{ trans('labels.ProductPriceText') }}
                                                        </span>
                                                        <span class="help-block hidden">{{ trans('labels.ProductPriceText') }}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-md-6">
                                                <div class="form-group" id="tax-class">
                                                    <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.TaxClass') }} </label>
                                                    <div class="col-sm-10 col-md-8">
                                                        <select class="form-control field-validate" name="tax_class_id">
                                                            <option selected>{{ trans('labels.SelectTaxClass') }}</option>
                                                            @foreach ($result['taxClass'] as $taxClass)
                                                            <option value="{{ $taxClass->tax_class_id }}">{{ $taxClass->tax_class_title }}</option>
                                                            @endforeach
                                                        </select>
                                                        <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                                            {{ trans('labels.ChooseTaxClassForProductText') }}
                                                        </span>
                                                        <span class="help-block hidden">{{ trans('labels.SelectProductTaxClass') }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-xs-12 col-md-6">
                                                <div class="form-group">
                                                    <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Min Order Limit') }}</label>
                                                    <div class="col-sm-10 col-md-8">
                                                        {!! Form::text('products_min_order', '1', array('class'=>'form-control field-validate number-validate', 'id'=>'products_min_order')) !!}
                                                        <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                                            {{ trans('labels.Min Order Limit Text') }}
                                                        </span>
                                                        <span class="help-block hidden">{{ trans('labels.Min Order Limit Text') }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-md-6">
                                                <div class="form-group">
                                                    <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Max Order Limit') }}</label>
                                                    <div class="col-sm-10 col-md-8">
                                                        {!! Form::text('products_max_stock', '', array('class'=>'form-control ', 'id'=>'products_max_stock')) !!}
                                                        <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                                            {{ trans('labels.Max Order Limit Text') }}
                                                        </span>
                                                        <span class="help-block hidden">{{ trans('labels.Max Order Limit Text') }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-12 col-md-6">
                                                <div class="form-group">
                                                    <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.ProductsWeight') }}</label>
                                                    <div class="col-sm-10 col-md-4">
                                                        {!! Form::text('products_weight', '', array('class'=>'form-control', 'id'=>'products_weight')) !!}
                                                        <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                                            {{ trans('labels.RequiredTextForWeight') }}
                                                        </span>
                                                    </div>
                                                    <div class="col-sm-10 col-md-4" style="padding-left: 0;">
                                                        <select class="form-control" name="products_weight_unit">
                                                            @if($result['units'] !== null)
                                                            @foreach($result['units'] as $unit)
                                                            <option value="{{$unit->units_name}}">{{$unit->units_name}}</option>
                                                            @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-md-6">
                                                <div class="form-group">
                                                    <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.ProductsModel') }}</label>
                                                    <div class="col-sm-10 col-md-8">
                                                        {!! Form::text('products_model', '', array('class'=>'form-control', 'id'=>'products_model')) !!}
                                                        <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                                            {{ trans('labels.ProductsModelText') }}
                                                        </span>
                                                        <span class="help-block hidden">{{ trans('labels.textRequiredFieldMessage') }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-md-6">
                                                <div class="form-group">
                                                    <label for="name" class="col-sm-2 col-md-3 control-label">SKU</label>
                                                    <div class="col-sm-10 col-md-8">
                                                        {!! Form::text('sku', '', array('class'=>'form-control', 'id'=>'sku')) !!}
                                                        <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                                            Enter product sku if exist (optional).
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-xs-12 col-md-6 ">
                                                <div class="form-group" id="imageselected">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Image') }}</label>
                                                <div class="col-sm-10 col-md-8">

                                                    <input type="file" autocomplete="off" class="form-control" onchange="previewImage(this)" name="image_id" required="">
                                                    
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">{{ trans('labels.UploadProductImageText') }}</span>
                                                    <br>
                                                </div>
                                                <label for="name" class="col-sm-2 col-md-3 control-label">&nbsp;</label>
                                                <div class="col-sm-10 col-md-4 hidden previewImage">
                                                </div>
                                                
                                            </div>
                                                
                                            </div>

                                            <div class="col-xs-12 col-md-6">
                                                <div class="form-group">
                                                    <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.VideoEmbedCodeLink') }}</label>
                                                    <div class="col-sm-10 col-md-8">
                                                        {!! Form::textarea('products_video_link', '', array('class'=>'form-control', 'id'=>'products_video_link', 'rows'=>4)) !!}
                                                        <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                                            {{ trans('labels.VideoEmbedCodeLinkText') }}
                                                        </span>
                                                        <span class="help-block hidden">{{ trans('labels.textRequiredFieldMessage') }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-md-6">
                                                <div class="form-group">
                                                    <label for="name" class="col-sm-2 col-md-3 control-label">Is Cod</label>
                                                    <div class="col-sm-10 col-md-8">
                                                        <select name="is_cod" class="form-control">
                                                            <option value="Y">Yes</option>
                                                            <option value="N">No</option>
                                                        </select>
                                                        <span class="help-block">Select Yes to enable Cod option for this product</span>
                                                    </div>
                                                </div>
                                            </div>
                                           
                                        </div>

                                        <hr>


                                        <div class="row">
                                            <div class="col-xs-12 col-md-6">
                                                <div class="form-group flash-sale-link">
                                                    <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.FlashSale') }}</label>
                                                    <div class="col-sm-10 col-md-8">
                                                        <select class="form-control" onChange="showFlash();" name="isFlash" id="isFlash">
                                                            <option selected="" value="no">{{ trans('labels.No') }}</option>
                                                            <option value="yes">{{ trans('labels.Yes') }}</option>
                                                        </select>
                                                        <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                                            {{ trans('labels.FlashSaleText') }}</span>
                                                    </div>
                                                </div>

                                                <div class="flash-container" style="display: none;">
                                                    <div class="form-group">
                                                        <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.FlashSalePrice') }}<span style="color:red;">*</span></label>
                                                        <div class="col-sm-10 col-md-8">
                                                            <input class="form-control" type="text" name="flash_sale_products_price" id="flash_sale_products_price" value="">
                                                            <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                                                {{ trans('labels.FlashSalePriceText') }}</span>
                                                            <span class="help-block hidden">{{ trans('labels.FlashSalePriceText') }}</span>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.FlashSaleDate') }}<span style="color:red;">*</span></label>
                                                        <div class="col-sm-10 col-md-4">
                                                            <input class="form-control datepicker" readonly type="text" name="flash_start_date" id="flash_start_date" readonly value="">
                                                            <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                                                {{ trans('labels.FlashSaleDateText') }}</span>
                                                            <span class="help-block hidden">{{ trans('labels.textRequiredFieldMessage') }}</span>
                                                        </div>
                                                        <div class="col-sm-10 col-md-4 bootstrap-timepicker">
                                                            <input type="text" class="form-control timepicker" name="flash_start_time" readonly id="flash_start_time" value="">
                                                            <span class="help-block hidden">{{ trans('labels.textRequiredFieldMessage') }}</span>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.FlashExpireDate') }}<span style="color:red;">*</span></label>
                                                        <div class="col-sm-10 col-md-4">
                                                            <input class="form-control datepicker" readonly type="text" readonly name="flash_expires_date" id="flash_expires_date" value="">
                                                            <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                                                {{ trans('labels.FlashExpireDateText') }}</span>
                                                            <span class="help-block hidden">{{ trans('labels.textRequiredFieldMessage') }}</span>
                                                        </div>
                                                        <div class="col-sm-10 col-md-4 bootstrap-timepicker">
                                                            <input type="text" class="form-control timepicker" readonly name="flash_end_time" id="flash_end_time" value="">
                                                            <span class="help-block hidden">{{ trans('labels.textRequiredFieldMessage') }}</span>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Status') }}</label>
                                                        <div class="col-sm-10 col-md-8">
                                                            <select class="form-control" name="flash_status">
                                                                <option value="1">{{ trans('labels.Active') }}</option>
                                                                <option value="0">{{ trans('labels.Inactive') }}</option>
                                                            </select>
                                                            <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                                                {{ trans('labels.ActiveFlashSaleProductText') }}</span>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>

                                            <div class="col-xs-12 col-md-6">
                                                <div class="form-group special-link">
                                                    <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Special') }}</label>
                                                    <div class="col-sm-10 col-md-8">
                                                        <select class="form-control" onChange="showSpecial();" name="isSpecial" id="isSpecial">
                                                            <option value="no">{{ trans('labels.No') }}</option>
                                                            <option value="yes">{{ trans('labels.Yes') }}</option>
                                                        </select>
                                                        <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                                            {{ trans('labels.SpecialProductText') }}.</span>
                                                    </div>
                                                </div>

                                                <div class="special-container" style="display: none;">
                                                    <div class="form-group">
                                                        <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.SpecialPrice') }}<span style="color:red;">*</span></label>
                                                        <div class="col-sm-10 col-md-8">
                                                            <input class="form-control" type="text" name="specials_new_products_price" id="special-price" value="">
                                                            <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                                                {{ trans('labels.SpecialPriceTxt') }}.</span>
                                                            <span class="help-block hidden">{{ trans('labels.SpecialPriceNote') }}.</span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.ExpiryDate') }}<span style="color:red;">*</span></label>
                                                        <div class="col-sm-10 col-md-8">
                                                            <input class="form-control datepicker" readonly readonly type="text" name="expires_date" id="expiry-date" value="">
                                                            <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                                                {{ trans('labels.SpecialExpiryDateTxt') }}
                                                            </span>
                                                            <span class="help-block hidden">{{ trans('labels.textRequiredFieldMessage') }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Status') }}<span style="color:red;">*</span></label>
                                                        <div class="col-sm-10 col-md-8">
                                                            <select class="form-control" name="status">
                                                                <option value="1">{{ trans('labels.Active') }}</option>
                                                                <option value="0">{{ trans('labels.Inactive') }}</option>
                                                            </select>
                                                            <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                                                {{ trans('labels.ActiveSpecialProductText') }}.</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                        </div>

                                        <hr>

                                        <div class="row">
                                            <div class="col-xs-12">
                                                <div class="tabbable tabs-left">
                                                    <ul class="nav nav-tabs">
                                                        @foreach($result['languages'] as $key=>$languages)
                                                        <li class="@if($key==0) active @endif"><a href="#product_<?=$languages->languages_id?>" data-toggle="tab"><?=$languages->name?><span style="color:red;">*</span></a></li>
                                                        @endforeach
                                                    </ul>
                                                    <div class="tab-content">
                                                        @foreach($result['languages'] as $key=>$languages)

                                                        <div style="margin-top: 15px;" class="tab-pane @if($key==0) active @endif" id="product_<?=$languages->languages_id?>">
                                                            <div class="">
                                                                <div class="form-group">
                                                                    <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.ProductName') }}<span style="color:red;">*</span> ({{ $languages->name }})</label>
                                                                    <div class="col-sm-10 col-md-8">
                                                                        <input type="text" name="products_name_<?=$languages->languages_id?>" class="form-control field-validate">
                                                                        <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                                                            {{ trans('labels.EnterProductNameIn') }} {{ $languages->name }} </span>
                                                                        <span class="help-block hidden">{{ trans('labels.textRequiredFieldMessage') }}</span>
                                                                    </div>
                                                                </div>

                                                                <div class="form-group external_link" style="display: none">
                                                                    <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.External URL') }} ({{ $languages->name }})</label>
                                                                    <div class="col-sm-10 col-md-8">
                                                                        <input type="text" name="products_url_<?=$languages->languages_id?>" class="form-control products_url">
                                                                        <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                                                            {{ trans('labels.External URL Text') }} {{ $languages->name }} </span>
                                                                        <span class="help-block hidden">{{ trans('labels.textRequiredFieldMessage') }}</span>
                                                                    </div>
                                                                </div>

                                                                <div class="form-group">
                                                                    <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Description') }}<span style="color:red;">*</span> ({{ $languages->name }})</label>
                                                                    <div class="col-sm-10 col-md-8">
                                                                        <textarea id="editor<?=$languages->languages_id?>" name="products_description_<?=$languages->languages_id?>" class="form-control" rows="5"></textarea>
                                                                        <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                                                            {{ trans('labels.EnterProductDetailIn') }} {{ $languages->name }}</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @endforeach

                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- /.box-body -->
                                        <div class="box-footer text-center">
                                            <button type="submit" class="btn btn-primary pull-right processing_btn">
                                                <span>{{ trans('labels.Save_And_Continue') }}</span>
                                                <i class="fa fa-angle-right 2x"></i>
                                            </button>
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
    $(function() {

        //for multiple languages
        @foreach($result['languages'] as $languages)
        // Replace the <textarea id="editor1"> with a CKEditor
        // instance, using default configuration.
        CKEDITOR.replace('editor{{$languages->languages_id}}');

        @endforeach

        //bootstrap WYSIHTML5 - text editor
        $(".textarea").wysihtml5();

    });
                                    
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
