@extends('admin.layout')
@section('css')
<link rel="stylesheet" href="{{ asset('vendor/file-manager/css/file-manager.css') }}">
@endsection
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1> {{ trans('labels.AddProductImages') }} <small>{{ trans('labels.AddProductImages') }}...</small> </h1>
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

                                        {!! Form::open(array('url' =>'admin/products/images/insertproductimage', 'name'=>'addImageFrom', 'id'=>'addImageFrom', 'method'=>'post', 'class' => 'form-horizontal', 'enctype'=>'multipart/form-data')) !!}
                                        {!! Form::hidden('products_id',  $result['data']['products_id'], array('class'=>'form-control', 'id'=>'products_id')) !!}

                                        {!! Form::hidden('sort_order',  count($result['products_images'])+1, array('class'=>'form-control', 'id'=>'sort_order')) !!}
                                         <div class="clearfix">&nbsp;</div>
                                        <div class="col-sm-12">
                                            <label>Product Gallery</label>
                                            <button type="button" class="delete-row1 btn btn-danger pull-right">Delete Image</button> <input type="button" class="add-row1 btn btn-primary pull-right" value="Add Image">
                                           </div>
                                         <div class="clearfix">&nbsp;</div>
                                            <div class="col-sm-12" id="pimage_gallery">
                                               <div class="col-sm-3 image-row pull-left">
                                              <input type='checkbox' name='record1[]'>
                                                <div class="form-group">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Image') }}</label>
                                                <div class="col-sm-10 col-md-8">

                                                        <input type="file" autocomplete="off" class="form-control" onchange="previewImage(this);" name="image_id[]" required="">
                                                    
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">{{ trans('labels.UploadProductImageText') }}</span>
                                                    <br>
                                                </div>
                                                <label for="name" class="col-sm-2 col-md-3 control-label">&nbsp;</label>
                                                <div class="col-sm-10 col-md-4 hidden previewImage">
                                                </div>
                                                
                                            </div>
                                                
                                          </div> 
                                           </div>   
                                        
                                        <div class="form-group">
                                            <label for="name" class="col-sm-2 col-md-4 control-label"> </label>
                                            <div class="col-sm-10 col-md-8 float-right">
                                            <a type="button" class="btn btn-default float-right" href="{{url('admin/products/images/display')}}/{{$products_id}}" >{{ trans('labels.back') }} </a>
                                            <button type="submit" class="btn btn-primary float-right" >{{ trans('labels.AddNew') }}</button>
                                        </div>
                                            <br><br><br><br><br>

                                        {!! Form::close() !!}
                                        </div>
                                         
                                             <div class="clearfix">&nbsp;</div>
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
    $(".add-row1").click(function()
        {  
            var html  = "";
            html     += "<div class=\"col-sm-3 image-row pull-left\"><input type='checkbox' name='record1[]'>"; 
            html     += "<div class=\"form-group\">";
              html     += " <label for=\"name\" class=\"col-sm-2 col-md-3 control-label\">{{ trans('labels.Image') }}</label>";
               html     += "<div class=\"col-sm-10 col-md-8\">";

               html     += "<input type=\"file\" autocomplete=\"off\" class=\"form-control\"  onchange=\"previewImage(this);\" name=\"image_id[]\" required=\"\">";
                                                    
               html     += "<span class=\"help-block\" style=\"font-weight: normal;font-size: 11px;margin-bottom: 0;\">{{ trans('labels.UploadProductImageText') }}</span>";
                                                 html     += "<br>";
               html     += " </div>";
               html     += "<label for=\"name\" class=\"col-sm-2 col-md-3 control-label\">&nbsp;</label>";
               html     += "<div class=\"col-sm-10 col-md-4 hidden previewImage\">";
               html     += "</div>";
                                                
                                html     += " </div>  </div>";
                   
            $("div #pimage_gallery").append(html);
        });
    
    // Find and remove selected table rows
        $(".delete-row1").click(function()
        {
            var row_count         = $("#pimage_gallery").find('input[name="record1[]"]').length;
            var checked_row_count = $('[name="record1[]"]:checked').length;

            if(row_count != checked_row_count)
            {
                $("#pimage_gallery").find('input[name="record1[]"]').each(function()
                {
                    if($(this).is(":checked"))
                    {
                        $(this).parents("#pimage_gallery .image-row").remove();
                    }
                });
            }
            else
            {
                alert("All rows can't be deleted");
                return false;
            }
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

