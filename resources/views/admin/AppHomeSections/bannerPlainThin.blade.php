@extends('admin.layout')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1> {{ trans('labels.Section') }} <small>{{ trans('labels.ListingHomeSection') }}...</small> </h1>
            <ol class="breadcrumb">
                <li><a href="{{ URL::to('admin/dashboard/this_month') }}"><i class="fa fa-dashboard"></i> {{ trans('labels.breadcrumb_dashboard') }}</a></li>
                <li class="active"> {{ trans('labels.Banners') }}</li>
            </ol>
        </section>
<section class="content">
            <!-- Info boxes -->

@if(!empty($result['section']))            <!-- /.row -->
<div class="row">
                <div class="col-md-12">
        <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">Section</h3>
                        </div>

                        <!-- /.box-header -->
                        <div class="box-body">
                             <br>
                                        @include('admin.common.feedback')

                                    <!-- form start -->
                                    <form action="{{url('admin/homeSections/section/update')}}" method="post" enctype="multipart/form-data">
                                        @csrf
                                        <div class="col-xs-12 col-md-6 ">
                                        <div class="form-group">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">Section Title </label>
                                                <div class="col-sm-10 col-md-8">
                                                    <input class="form-control" type="text" name="section_title" value="{{$result['section']->title}}">
                                                    <input class="form-control" type="hidden" name="section_id" value="{{$result['section']->id}}">
                                                </div>
                                            </div>
                                            </div>
                                        <div class="col-xs-12 col-md-6 ">
                                            <button type="submit" class="btn btn-sm btn-primary processing_btn">Update</button>
                                        </div>
                                    </form>  
                                    <div class="col-md-12">&nbsp;</div>
                        </div>
        </div>
                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">Banner Plain Thin</h3>
                        </div>

                        <!-- /.box-header -->
                        <div class="box-body">
                            
                                    
                                @if(!empty($result['editSectionData']->id))
                                      <div class="col-xs-12 col-md-12 ">
                                        <legend>
                                            Edit Banner Plain Thin Section Data
                                        </legend>
                                    </div>
                                
                                {!! Form::open(array('url' =>'admin/homeSections/bannerPlainThin/update', 'method'=>'post', 'class' => 'form-horizontal form-validate', 'enctype'=>'multipart/form-data')) !!}
                                            
                                            <div class="col-xs-12 col-md-6 ">
                                            <div class="form-group">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">Title</label>
                                                <div class="col-sm-10 col-md-8">
                                                    <input class="form-control" type="text" name="title" value="{{$result['editSectionData']->title}}" required=''>
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                                        Enter Title</span>
                                                </div>
                                            </div>
                                            </div>     
                                           <div class="col-xs-12 col-md-6 ">
                                            <div class="form-group">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Type') }}</label>
                                                <div class="col-sm-10 col-md-8">
                                                    <select class="form-control select2" style="width: 100%;" name="data_type" id="Section_data_type">
                                                        <option value="">Select data Type</option>
                                                        <option value="CATEGORY" @if($result['editSectionData']->data_type =="CATEGORY") selected='' @endif>{{ trans('labels.ChooseSubCategory') }}</option>
                                                        <option value="PRODUCT" @if($result['editSectionData']->data_type =="PRODUCT") selected='' @endif>{{ trans('labels.Product') }}</option>
                                                    </select>
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                                        With whome do you want to associate this section?</span>
                                                </div>
                                            </div>
                                            </div>

                                        <input class="form-control" type="hidden" name="id" value="{{$result['editSectionData']->id}}">
                                          
                                        @php
                                              $ids = explode(",",$result['editSectionData']->urls);
                                              @endphp
                                        <div class="col-xs-12 col-md-6 ">
                                            <div class="form-group categoryContent" @if($result['editSectionData']->data_type!='CATEGORY') style="display: none" @endif>
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Categories') }}</label>
                                                <div class="col-sm-10 col-md-8">
                                                    <select class="form-control select2" style="width: 100%;" name="categories_id" id="categories_id">
                                                        <option value="">Select Category</option>
                                                        @foreach($result['categories'] as $category)
                                                            <option value="{{ $category->id}}" @if(!empty($ids) && in_array($category->id,$ids)) selected='' @endif>@if($category->parent_name) {{ $category->parent_name}} / @endif{{ $category->name}}</option>
                                                        @endforeach
                                                    </select>
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                      {{ trans('labels.CategoriesbannerText') }}</span>
                                                </div>
                                            </div>
                                            
                                            <div class="form-group productContent" @if($result['editSectionData']->data_type!='PRODUCT') style="display: none" @endif>
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Products') }}</label>
                                                <div class="col-sm-10 col-md-8">
                                                    <select class="form-control select2" style="width: 100%;" name="products_id" id="products_id">
                                                        <option value="">Select Product</option>
                                                        @foreach($result['products'] as $products_data)
                                                            <option value="{{ $products_data->products_id }}" @if(!empty($ids) && in_array($products_data->products_id,$ids)) selected='' @endif>{{ $products_data->products_name }}/ {{ $products_data->products_model }}</option>
                                                        @endforeach
                                                    </select>
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                      {{ trans('labels.ProductsBannerText') }}</span>
                                                </div>
                                            </div>
                                            </div>
                                            <div class="productFilterContent" @if($result['editSectionData']->data_type!='CATEGORY') style="display: none" @endif>
                                                <div class="col-xs-12 col-md-6 ">
                                                    <div class="form-group">
                                                        <label for="name" class="col-sm-2 col-md-3 control-label">Filter Type</label>
                                                        <div class="col-sm-10 col-md-8">
                                                        <select class="form-control select2" name="filter_type" style="width: 100%;">
                                                            <option value="">Select Filter Type</option>
                                                            <option value="AMT" @if($result['editSectionData']->filter_type == "AMT") selected=''@endif>Amount</option>
                                                            <option value="PER" @if($result['editSectionData']->filter_type == "PER") selected=''@endif>Percentage</option>
                                                        </select>
                                                    </div>
                                                    </div>
                                                    
                                                </div>
                                                <div class="col-xs-12 col-md-6 ">
                                                    <div class="form-group">
                                                        <label for="name" class="col-sm-2 col-md-3 control-label"> Filter Condition</label>
                                                        <div class="col-sm-10 col-md-8">
                                                        <select class="form-control select2" name="condition" style="width: 100%;">
                                                            <option value="">Select Condition</option>
                                                            <option value=">" @if($result['editSectionData']->condition == ">") selected=''@endif>(>) grater than</option>
                                                            <option value="<" @if($result['editSectionData']->condition == "<") selected=''@endif>(<) less than</option>
                                                            <option value="=" @if($result['editSectionData']->condition == "=") selected=''@endif>(=) Equal to</option>
                                                        </select>
                                                    </div>
                                                    </div>
                                                    
                                                </div>
                                                <div class="col-xs-12 col-md-6 ">
                                                    <div class="form-group">
                                                        <label for="name" class="col-sm-2 col-md-3 control-label">Filter Value</label>
                                                        <div class="col-sm-10 col-md-8">
                                                            <input type="number" class="form-control" name="filter_value" min='1' value="{{$result['editSectionData']->filter_value}}">
                                                    </div>
                                                    </div>
                                                    
                                                </div>
                                                <div class="col-xs-12 col-md-6 ">
                                                    <div class="form-group">
                                                        <label for="name" class="col-sm-2 col-md-3 control-label">Thresold Value</label>
                                                        <div class="col-sm-10 col-md-8">
                                                        <input type="number" class="form-control" name="thresold_value" min='0' value="{{$result['editSectionData']->thresold_value}}">
                                                    </div>
                                                    </div>
                                                    
                                                </div>
                                            </div>

                                           <div class="col-xs-12 col-md-6 ">
                                                <div class="form-group" id="imageselected">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Image') }}</label>
                                                <div class="col-sm-10 col-md-8">

                                                    <input type="file" autocomplete="off" class="form-control" onchange="previewImage(this)" name="image_id">
                                                    
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">{{ trans('labels.UploadProductImageText') }}</span>
                                                    <br>
                                                </div>
                                                <label for="name" class="col-sm-2 col-md-3 control-label">&nbsp;</label>
                                                <div class="col-sm-10 col-md-4 hidden previewImage">
                                                </div>
                                                
                                            </div>
                                               <div class="form-group">
                                                    <label for="name" class="col-sm-2 col-md-3 control-label">Old</label>
                                                    <div class="col-sm-10 col-md-4">
                                                        <input type="hidden" name="oldImage" value="{{$result['editSectionData']->image}}">
                                                        <img src="{{asset($result['editSectionData']->image)}}" alt="" width=" 100px">
                                                    </div>
                                                </div>
                                                
                                            </div>
                                           <div class="col-xs-12 col-md-6 ">

                                            <div class="form-group">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">Sort Order</label>
                                                <div class="col-sm-10 col-md-8">
                                                    <input type="number" name="sort_order" min='0' class="form-control" value="{{$result['editSectionData']->sort_order}}" required=''>
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                      Sort Order for this data</span>
                                                </div>
                                            </div>
                                           </div>
                                           <div class="col-xs-12 col-md-6 ">

                                            <div class="form-group">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Status') }}</label>
                                                <div class="col-sm-10 col-md-8">
                                                    <select class="form-control select2" style="width: 100%;" name="status">
                                                        <option value="ACTIVE" @if($result['editSectionData']->status == "ACTIVE") selected='' @endif>{{ trans('labels.Active') }}</option>
                                                        <option value="INACTIVE" @if($result['editSectionData']->status == "INACTIVE") selected='' @endif>{{ trans('labels.InActive') }}</option>
                                                    </select>
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                      {{ trans('labels.StatusBannerText') }}</span>
                                                </div>
                                            </div>
                                           </div>
                                            <div class="clearfix">&nbsp;</div>
                                            <!-- /.box-body -->
                                            <div class="box-footer text-center">
                                                <button type="submit" class="btn btn-primary processing_btn">Update</button>
                                            </div>
                                            <!-- /.box-footer -->
                                            {!! Form::close() !!}
                                
                                @else
                                    @if(count($result['sectionData'])<1)
                                    <div class="col-xs-12 col-md-12 ">
                                        <legend>
                                            Add Banner Pain Thin Section Data
                                        </legend>
                                    </div>

                                            {!! Form::open(array('url' =>'admin/homeSections/bannerPlainThin/insert', 'method'=>'post', 'class' => 'form-horizontal form-validate', 'enctype'=>'multipart/form-data')) !!}
                                            
                                           <div class="col-xs-12 col-md-6 ">
                                            <div class="form-group">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">Title</label>
                                                <div class="col-sm-10 col-md-8">
                                                    <input class="form-control" type="text" name="title" value="{{old('title')}}" required=''>
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                                        Enter Title</span>
                                                </div>
                                            </div>
                                            </div>    
                                           <div class="col-xs-12 col-md-6 ">
                                            <div class="form-group">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Type') }}</label>
                                                <div class="col-sm-10 col-md-8">
                                                    <select class="form-control select2" style="width: 100%;" name="data_type" onchange="urlDataTypes(this);">
                                                        <option value="">Select data Type</option>
                                                        <option value="CATEGORY">{{ trans('labels.ChooseSubCategory') }}</option>
                                                        <option value="PRODUCT">{{ trans('labels.Product') }}</option>
                                                    </select>
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                                        With whome do you want to associate this section?</span>
                                                </div>
                                            </div>
                                            </div>

                                        <input class="form-control" type="hidden" name="section_id" value="{{$result['section']->id}}">
                                          
                                        <div class="col-xs-12 col-md-6 ">
                                            <div class="form-group categoryContent">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Categories') }}</label>
                                                <div class="col-sm-10 col-md-8">
                                                    <select class="form-control select2" style="width: 100%;" name="categories_id" id="categories_id">
                                                        <option value="">Select Category</option>
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
                                                <div class="col-sm-10 col-md-8">
                                                    <select class="form-control select2" style="width: 100%;" name="products_id" id="products_id">
                                                        <option value="">Select Product</option>
                                                        @foreach($result['products'] as $products_data)
                                                            <option value="{{ $products_data->products_id }}">{{ $products_data->products_name }}/ {{ $products_data->products_model }}</option>
                                                        @endforeach
                                                    </select>
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                      {{ trans('labels.ProductsBannerText') }}</span>
                                                </div>
                                            </div>
                                            </div>
                                            <div class="productFilterContent" style="display: none">
                                                <div class="col-xs-12 col-md-6 ">
                                                    <div class="form-group">
                                                        <label for="name" class="col-sm-2 col-md-3 control-label">Filter Type</label>
                                                        <div class="col-sm-10 col-md-8">
                                                        <select class="form-control select2" name="filter_type" style="width: 100%;">
                                                            <option value="">Select Filter Type</option>
                                                            <option value="AMT">Amount</option>
                                                            <option value="PER">Percentage</option>
                                                        </select>
                                                    </div>
                                                    </div>
                                                    
                                                </div>
                                                <div class="col-xs-12 col-md-6 ">
                                                    <div class="form-group">
                                                        <label for="name" class="col-sm-2 col-md-3 control-label"> Filter Condition</label>
                                                        <div class="col-sm-10 col-md-8">
                                                        <select class="form-control select2" name="condition" style="width: 100%;">
                                                            <option value="">Select Condition</option>
                                                            <option value=">">(>) grater than</option>
                                                            <option value="<">(<) less than</option>
                                                            <option value="=">(=) Equal to</option>
                                                        </select>
                                                    </div>
                                                    </div>
                                                    
                                                </div>
                                                <div class="col-xs-12 col-md-6 ">
                                                    <div class="form-group">
                                                        <label for="name" class="col-sm-2 col-md-3 control-label">Filter Value</label>
                                                        <div class="col-sm-10 col-md-8">
                                                        <input type="number" class="form-control" name="filter_value" min='1'>
                                                    </div>
                                                    </div>
                                                    
                                                </div>
                                                <div class="col-xs-12 col-md-6 ">
                                                    <div class="form-group">
                                                        <label for="name" class="col-sm-2 col-md-3 control-label">Thresold Value</label>
                                                        <div class="col-sm-10 col-md-8">
                                                        <input type="number" class="form-control" name="thresold_value" min='0'>
                                                    </div>
                                                    </div>
                                                    
                                                </div>
                                            </div>

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
                                           <div class="col-xs-12 col-md-6 ">

                                            <div class="form-group">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">Sort Order</label>
                                                <div class="col-sm-10 col-md-8">
                                                    <input type="number" name="sort_order" min='0' class="form-control" value="{{old('sort_order')}}" required=''>
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                      Sort Order for this data</span>
                                                </div>
                                            </div>
                                           </div>
                                           <div class="col-xs-12 col-md-6 ">

                                            <div class="form-group">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Status') }}</label>
                                                <div class="col-sm-10 col-md-8">
                                                    <select class="form-control select2" style="width: 100%;" name="status">
                                                        <option value="ACTIVE" selected>{{ trans('labels.Active') }}</option>
                                                        <option value="INACTIVE">{{ trans('labels.InActive') }}</option>
                                                    </select>
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                      {{ trans('labels.StatusBannerText') }}</span>
                                                </div>
                                            </div>
                                           </div>
                                            <div class="clearfix">&nbsp;</div>
                                            <!-- /.box-body -->
                                            <div class="box-footer text-center">
                                                <button type="submit" class="btn btn-primary processing_btn">{{ trans('labels.Submit') }}</button>
                                            </div>
                                            <!-- /.box-footer -->
                                            {!! Form::close() !!}
                                       
                                    @endif
                                @endif

                        </div>
                        <!-- /.box-body -->
                    </div>
                     </div>
             </div>

@else

@endif           
            
            
            @if(count($result['sectionData'])>0)
            <div class="row">
                <div class="col-md-12">
        
                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">Data </h3>
                            <div class="col-lg-6">

                                &nbsp;
                            </div>

                        </div>

                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="row">
                                <div class="col-xs-12 table-responsive">
                                    <table id="example1" class="table table-bordered table-striped">
                                        <thead>
                                        <tr>
                                            <th>Sr. No</th>
                                            <th>Title</th>
                                            <th>Image/Video</th>
                                            <th>Type</th>
                                            <th>Urls</th>
                                            <th>sort_order</th>
                                            <th>{{ trans('labels.Action') }}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @if(count($result['sectionData'])>0)
                                          @php($i=0)
                                            @foreach ($result['sectionData'] as $key=>$banners)
                                            @php($i++)
                                                <tr>
                                                    <td>{{ $i }}</td>
                                                    <td>{{ $banners->title }}</td>
                                                    <td>@if(!empty($banners->image)) <img width="70px" src="{{asset($banners->image)}}"> @endif 
                                                        @if(!empty($banners->video)) <video width="100" height="100" controls>
                                                                <source src="{{asset($banners->video)}}" type="video/mp4">
                                                                    Your browser does not support the video tag.
                                                            </video>
                                                      @endif</td>
                                                    <td>{{ $banners->data_type }}</td>
                                                    <td>{{ $banners->urls }}</td>
                                                     <td>{{ $banners->sort_order }}</td>
                                                     <td><a data-toggle="tooltip" data-placement="bottom" title="{{ trans('labels.Edit') }}" href="{{url('admin/homeSections/bannerPlainThin/edit')}}/{{ $banners->id }}" class="text-info"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>&nbsp;

                                                        <a data-toggle="tooltip" data-placement="bottom" title="{{ trans('labels.Delete') }}" section_data_id ="{{ $banners->id }}" class="deleteAppSection text-danger"><i class="fa fa-trash" aria-hidden="true"></i></a>
                                                </tr>
                                            @endforeach
                                        @endif
                                        </tbody>
                                    </table>
                                    
                                </div>
                            </div>
                        </div>
                        <!-- /.box-body -->
                    </div>
                    <!-- /.box -->
                </div>
                    
            </div>
            <!-- Main row -->
            @endif
            <!-- deleteBannerModal -->
            <div class="modal fade" id="deleteSectionDataModal" tabindex="-1" role="dialog" aria-labelledby="deleteSectionDataModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title text-danger" id="deleteBannerModalLabel">Delete Section data</h4>
                        </div>
                        {!! Form::open(array('url' =>'admin/homeSections/section/data/delete', 'name'=>'deleteBanner', 'id'=>'deleteBanner', 'method'=>'post', 'class' => 'form-horizontal', 'enctype'=>'multipart/form-data')) !!}
                        {!! Form::hidden('action',  'delete', array('class'=>'form-control')) !!}
                        {!! Form::hidden('id',  '', array('class'=>'form-control', 'id'=>'section_data_id')) !!}
                        <div class="modal-body">
                            <p>{{ trans('labels.DeleteBannerText') }}</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('labels.Close') }}</button>
                            <button type="submit" class="btn btn-primary processing_btn" id="deleteBanner">{{ trans('labels.Delete') }}</button>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>

          <!-- /.row -->
        </section>
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
     function urlDataTypes(elem){
		 var type = $(elem).val();
		 

		 if(type=='CATEGORY'){
			$('.categoryContent').show();
			$('.productContent').hide();
                        $('.productFilterContent').show();
		}else if(type=='PRODUCT'){
			$('.categoryContent').hide();
			$('.productContent').show();
                        $('.productFilterContent').hide();
		}else{
			$('.categoryContent').hide();
			$('.productContent').hide();
                        $('.productFilterContent').hide();
		}
	}
     
        $(document).ready(function(e) {
	$('#Section_data_type').change(urlDataTypesEdit);
	urlDataTypesEdit();
	function urlDataTypesEdit(){
		 var type = $("#Section_data_type").val();
		 

		if(type=='CATEGORY'){
			$('.categoryContent').show();
			$('.productContent').hide();
                        $('.productFilterContent').show();
		}else if(type=='PRODUCT'){
			$('.categoryContent').hide();
			$('.productContent').show();
                        $('.productFilterContent').hide();
		}else{
			$('.categoryContent').hide();
			$('.productContent').hide();
                        $('.productFilterContent').hide();
		}
	}
});

           
           $(document).on('click', '.deleteAppSection', function(){
		var section_data_id = $(this).attr('section_data_id');
		$('#section_data_id').val(section_data_id);
		$("#deleteSectionDataModal").modal('show');
	});
           
                                                                    </script>

@endsection