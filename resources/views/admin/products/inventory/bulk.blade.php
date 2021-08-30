@extends('admin.layout')
@section('css')
<style>
    
</style>
@endsection
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1> {{ trans('labels.Inventory') }} <small>{{ trans('labels.Inventory') }}...</small> </h1>
        <ol class="breadcrumb">
            <li><a href="{{ URL::to('admin/dashboard/this_month') }}"><i class="fa fa-dashboard"></i> {{ trans('labels.breadcrumb_dashboard') }}</a></li>
            <li><a href="{{ URL::to('admin/products/display') }}"><i class="fa fa-database"></i> {{ trans('labels.ListingAllProducts') }}</a></li>
            <li class="active">{{ trans('labels.Inventory') }}</li>
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
                        <h3 class="box-title">{{ trans('labels.addinventory') }} </h3>

                    </div>
                    <div class="box-body">

                        <div class="row">
                            <div class="col-xs-12">
                                @include('admin.common.feedback')
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-xs-12">
                                <div class="box box-info">
                                    <!-- form start -->
                                    <div class="box-body">

                                        <div class="row">
                                            <!-- Left col -->
                                            <div class="col-md-6">
                                                <!-- MAP & BOX PANE -->

                                                <!-- /.box -->
                                                <div class="row">
                                                    <!-- /.col -->
                                                    <div class="col-md-12">
                                                        <!-- USERS LIST -->
                                                        <div class="box box-info">
                                                            <div class="box-header with-border">
                                                                <h3 class="box-title">{{ trans('labels.Add Stock') }} Bulk</h3>
                                                            </div>
                                                            <!-- /.box-header -->
                                                            <form action="{{url('admin/products/inventory/update_bulk')}}" method="post" enctype="multipart/form-data">
                                                            <div class="box-body">
                                                                @csrf
                                                                    <div class="form-group">
                                                                    <label for="name" class="col-sm-2 col-md-4 control-label">Excel file<span style="color:red;">*</span> </label>
                                                                    <div class="col-sm-10 col-md-8 ">
                                                                        <input class="form-control" type="file" name="select_file" />
                                                                    </div>
                                                                    
                                                                </div>
                                                                   
                                                                    
                                                                
                                                            <!-- /.box-footer -->
                                                            </div>
                                                             <div class="box-footer text-center">
                                                                    <button type="submit" id="attribute-btn" class="btn btn-primary pull-right">{{ trans('labels.Add Stock') }}</button>
                                                                </div>
                                                            </form>
                                                            
                                                        <!--/.box -->
                                                    </div>

                                                    <!-- /.col -->
                                                </div>
                                                <!-- /.row -->
                                            </div>

                                            

                                           
                                        </div>
                                            <div class="col-md-6 ">
                                                            <a class="btn btn-primary" href="{{asset('document/inventory_test.csv')}}" download=""><i class="fa fa-download"></i> Download Sample</a>
                                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>



                    </div>


                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->

        </div>


    </section>
    <!-- /.row -->

    <!-- Main row -->
</div>

<!-- /.row -->

@endsection
@section('js')
<script>

</script>
@endsection
