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
                            <h3 class="box-title">product Marqee</h3>
                        </div>

                        <!-- /.box-header -->
                        <div class="box-body">
                            
                                         
                                    
                                @if(!empty($result['editSectionData']->id))
                                      <div class="col-xs-12 col-md-12 ">
                                        <legend>
                                            Edit product Marqee Section Data
                                        </legend>
                                    </div>
                                
                                {!! Form::open(array('url' =>'admin/homeSections/productMarqee/update', 'method'=>'post', 'class' => 'form-horizontal form-validate', 'enctype'=>'multipart/form-data')) !!}
                                            
                                                
                                           

                                        <input class="form-control" type="hidden" name="id" value="{{$result['editSectionData']->id}}">
                                          
                                        @php
                                              $ids = explode(",",$result['editSectionData']->urls);
                                              @endphp
                                        <div class="col-xs-12 col-md-6 ">
                                           
                                            
                                            <div class="form-group">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Products') }}</label>
                                                <div class="col-sm-10 col-md-8">
                                                    <select class="form-control select2" style="width: 100%;" name="products_id[]" multiple id="products_id">
                                                        @foreach($result['products'] as $products_data)
                                                            <option value="{{ $products_data->products_id }}" @if(!empty($ids) && in_array($products_data->products_id,$ids)) selected='' @endif>{{ $products_data->products_name }}/ {{ $products_data->products_model }}</option>
                                                        @endforeach
                                                    </select>
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                      {{ trans('labels.ProductsBannerText') }}</span>
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
                                            Add product Marqee Section Data
                                        </legend>
                                    </div>

                                            {!! Form::open(array('url' =>'admin/homeSections/productMarqee/insert', 'method'=>'post', 'class' => 'form-horizontal form-validate', 'enctype'=>'multipart/form-data')) !!}
                                            
                                          
                                        <input class="form-control" type="hidden" name="section_id" value="{{$result['section']->id}}">
                                          
                                        <div class="col-xs-12 col-md-6 ">
                                           
                                            
                                            <div class="form-group">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Products') }}</label>
                                                <div class="col-sm-10 col-md-8">
                                                    <select class="form-control select2" style="width: 100%;" name="products_id[]" multiple id="products_id">
                                                        @foreach($result['products'] as $products_data)
                                                            <option value="{{ $products_data->products_id }}">{{ $products_data->products_name }}/ {{ $products_data->products_model }}</option>
                                                        @endforeach
                                                    </select>
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                      {{ trans('labels.ProductsBannerText') }}</span>
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
                                            <th>Type</th>
                                            <th>Urls</th>
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
                                                    <td>{{ $banners->data_type }}</td>
                                                    <td>{{ $banners->urls }}</td>
                                                     <td><a data-toggle="tooltip" data-placement="bottom" title="{{ trans('labels.Edit') }}" href="{{url('admin/homeSections/productMarqee/edit')}}/{{ $banners->id }}" class="text-info"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>&nbsp;

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
    
           
           $(document).on('click', '.deleteAppSection', function(){
		var section_data_id = $(this).attr('section_data_id');
		$('#section_data_id').val(section_data_id);
		$("#deleteSectionDataModal").modal('show');
	});
           
                                                                    </script>

@endsection