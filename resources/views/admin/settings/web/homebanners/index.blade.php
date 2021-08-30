@extends('admin.layout')
@section('css')
<link rel="stylesheet" href="{{ asset('vendor/file-manager/css/file-manager.css') }}">
@endsection
@section('content')
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1> {{ trans('labels.Home Banners') }} <small>{{ trans('labels.Listing The Home Banners') }}...</small> </h1>
    <ol class="breadcrumb">
       <li><a href="{{ URL::to('admin/dashboard/this_month') }}"><i class="fa fa-dashboard"></i> {{ trans('labels.breadcrumb_dashboard') }}</a></li>
      <li class="active">{{ trans('labels.Banners') }}</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <!-- Info boxes -->

    <!-- /.row -->

    <div class="row">
      <div class="col-md-12">
        <div class="box">

          <!-- /.box-header -->
          <div class="box-body">
            <div class="row">
              <div class="col-xs-12">
              		  @if (count($errors) > 0)
                          @if($errors->any())
                            <div class="alert alert-success alert-dismissible" role="alert">
                              <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                              {{$errors->first()}}
                            </div>
                          @endif
                      @endif

              </div>
            </div>
            <div class="row">
              <div class="col-xs-12">
                <div class="nav-tabs-custom">
                  <ul class="nav nav-tabs">

                    @foreach($result['languages'] as $key=>$languages)
                    <li class="@if($key==0) active @endif"><a href="#banners_<?=$languages->languages_id?>" data-toggle="tab"><?=$languages->name?><span style="color:red;">*</span></a></li>
                    @endforeach

                  </ul>
                  {!! Form::open(array('url' =>'admin/homebanners/insert', 'method'=>'post', 'class' => 'form-horizontal form-validate', 'enctype'=>'multipart/form-data')) !!}
                    <div class="tab-content">
                    @php 
                    $i =0;
                    @endphp
                      @foreach($result['banners'] as $key=>$banners_content)                 
                      
                      <div style="margin-top: 15px;" class="tab-pane @if($i==0) active @endif" id="banners_<?=$key?>">
                        @foreach($banners_content as $key=>$banner)
                        
                        <div class="">     
                          
                          <div class="row">
                            <div class="col-xs-12">
                                <div class="form-group" id="imageselected">
                                                <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Banner') }}</label>
                                                <div class="col-sm-10 col-md-4">

                                                        <input type="file" autocomplete="off" class="form-control"  onchange="previewImage(this)" placeholder="Click to choose Image" name="image_id_<?=$banner['language_id']?>_<?=$banner['banner_name']?>" >
                                                    
                                                    <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">{{ trans('labels.Choose Banner') }}</span>
                                                    <br>
                                                </div>
                                                <div class="col-sm-10 col-md-4 previewImage">
                                                </div>
                               </div>

                            @if(!empty($banner['path']))
                            <div class="form-group">
                                <label for="name" class="col-sm-2 col-md-3 control-label"></label>
                                <div class="col-sm-10 col-md-4">
                                    <img src="{{asset($banner['path'])}}" alt="" width=" 100px">
                                </div>
                            </div>
                            @endif
                           </div>
                          </div>

                        <div class="row">
                          <div class="col-xs-12">
                            <div class="form-group">
                              <label for="name" class="col-sm-2 col-md-3 control-label">{{ trans('labels.Text') }} </label>
                              <div class="col-sm-10 col-md-8">
                                  <textarea name="text_<?=$banner['language_id']?>_<?=$banner['banner_name']?>" class="form-control"
                                    rows="5">{{stripslashes($banner['text'])}}</textarea>
                                  <span class="help-block" style="font-weight: normal;font-size: 11px;margin-bottom: 0;">
                                      {{ trans('labels.Enter Detail') }}</span> 
                              </div>
                            </div>
                          </div>
                        </div>

                        </div>

                        @endforeach
                      </div>
                      @php 
                        $i++;
                      @endphp
                      @endforeach
                      <!-- /.tab-pane -->
                    </div>
                    <div class="box-footer text-center">
                      <button type="submit" class="btn btn-primary pull-right" id="normal-btn">{{ trans('labels.Submit') }}</button>
                  </div>
                  </form>
                  <!-- /.tab-content -->
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
