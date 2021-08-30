@extends('admin.layout')
@section('css')
<link rel="stylesheet" href="{{ asset('vendor/file-manager/css/file-manager.css') }}">
@endsection

@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1> {{ trans('labels.AddNewImage') }} <small>{{ trans('labels.ListingAllImage') }}...</small> </h1>
        <ol class="breadcrumb">
            <li><a href="{{ URL::to('admin/dashboard/this_month') }}"><i class="fa fa-dashboard"></i>
                    {{ trans('labels.breadcrumb_dashboard') }}</a></li>
            <li class="active">{{ trans('labels.AddNewImage') }}</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        @include('admin.common.feedback')
      <div class="row">
        <div class="col-md-12">
          <div class="box">
              <div class="box-header box-header-rose box-header-text">
                  <div class="box-text">
                    <h4 class="box-title">{{__('Add')}} {{__('Media')}}</h4>
                  </div>
              </div>
              <!-- /.box-header -->
              <div class="box-body">
                 
               <div class="col-md-12">
                    <div id="fm"></div>
                </div>
              </div>
        </div>
        <!-- /.col-->
      </div>
      </div>
      <!-- ./row -->
    </section>
    
 </div>
@endsection
@section('js')
<script src="{{ asset('vendor/file-manager/js/file-manager.js') }}"></script>
@endsection