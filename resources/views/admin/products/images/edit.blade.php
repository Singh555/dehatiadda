@extends('admin.layout')
@section('css')
<link rel="stylesheet" href="{{ asset('vendor/file-manager/css/file-manager.css') }}">
@endsection
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1> {{ trans('labels.AddImages') }} <small>{{ trans('labels.AddImages') }}...</small> </h1>
            <ol class="breadcrumb">
                <li><a href="{{ URL::to('admin/dashboard/this_month') }}"><i class="fa fa-dashboard"></i> {{ trans('labels.breadcrumb_dashboard') }}</a></li>
                <li><a href="{{ URL::to('admin/products/display') }}"><i class="fa fa-database"></i>{{ trans('labels.ListingAllProducts') }}</a></li>
                <li class="active">{{ trans('labels.AddImages') }}</li>
            </ol>
        </section>

        <!-- Main content -->
        <section class="content">

            <div class="row">
                <div class="col-md-12">
                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">{{ trans('labels.AddImage') }} </h3>

                        </div>

                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="row">
                                <div class="col-xs-12">

                                    <div class="modal-content">


                                        <div class="modal-body">


                                            {!! Form::open(array('url' =>'admin/products/images/updateproductimage', 'name'=>'editImageFrom', 'id'=>'editImageFrom', 'method'=>'post', 'class' => 'form-horizontal', 'enctype'=>'multipart/form-data')) !!}
                                            {!! Form::hidden('products_id',  $products_images[0]->products_id, array('class'=>'form-control', 'id'=>'products_id')) !!}
                                            {!! Form::hidden('id',  $products_images[0]->id, array('class'=>'form-control', 'id'=>'id')) !!}
                                            {!! Form::hidden('oldImage',  $products_images[0]->image , array('id'=>'oldImage')) !!}

                                            {!! Form::hidden('sort_order',  $products_images[0]->sort_order, array('class'=>'form-control', 'id'=>'sort_order')) !!}
                                            <div class="modal-body">

                                              <div class="form-group">
                                                <label for="name" class="col-sm-2 col-md-4 control-label">{{ trans('labels.Image') }}</label>
                                                <div class="col-sm-10 col-md-4">

                                                    <input type="file" autocomplete="off" class="form-control" name="image_id" onchange="previewImage(this);" required="">
                                                    
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">{{ trans('labels.UploadProductImageText') }}</span>
                                                    <br>
                                                </div>
                                                <div class="clearfix">&nbsp;</div>
                                                <label for="name" class="col-sm-2 col-md-4 control-label">&nbsp;</label>
                                                <div class="col-sm-10 col-md-4 hidden previewImage">
                                                </div>
                                                
                                            </div>

                                                <div class="form-group">
                                                    <label for="name" class="col-sm-2 col-md-4 control-label"></label>
                                                    <div class="col-sm-10 col-md-8">

                                                        <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">{{ trans('labels.OldImage') }}</span>

                                                        <img src="{{asset($products_images[0]->image_url)}}" alt="" width=" 100px">
                                                    </div>
                                                </div>

                                            <div class="form-group">
                                          	  <label for="name" class="col-sm-2 col-md-4 control-label">Sort Order</label>
                                          	  <div class="col-sm-10 col-md-8">
                                                <div class="col-md-6 col-sm-6">
		                                           {!! Form::text('sort_order',  $products_images[0]->sort_order, array('class'=>'form-control', 'id'=>'sort_order')) !!}
                                                </div>
                                              </div>
                                                  <input type="hidden" name="htmlcontent" value="">
                                            </div>


                                        </div>
                                        <div class="form-group">
                                            <label for="name" class="col-sm-2 col-md-4 control-label"> </label>
                                            <div class="col-sm-10 col-md-8 float-right">
                                                {{--<a type="button" class="btn btn-default float-right" href="{{url('admin/products/images/display')}}/{{$products_id}}" >{{ trans('labels.back') }} </a>--}}
                                                <a type="button" class="btn btn-default"  href="{{url('admin/products/images/display')}}/{{$products_images[0]->products_id}}">{{ trans('labels.Close') }}</a>
                                                <button type="submit" class="btn btn-primary" >{{ trans('labels.Submit') }}</button>


                                            </div>
                                            <br><br><br><br><br>

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
            </div>
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
