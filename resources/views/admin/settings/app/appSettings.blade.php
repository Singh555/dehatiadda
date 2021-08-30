@extends('admin.layout')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1> {{ trans('labels.application_settings') }} <small>{{ trans('labels.application_settings') }}...</small> </h1>
            <ol class="breadcrumb">
                <li><a href="{{ URL::to('admin/dashboard/this_month') }}"><i class="fa fa-dashboard"></i> {{ trans('labels.breadcrumb_dashboard') }}</a></li>
                <li class="active">{{ trans('labels.application_settings') }}</li>
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
                            <h3 class="box-title">{{ trans('labels.application_settings') }} </h3>
                        </div>

                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="row">
                                <div class="col-xs-12">
                                            @if( count($errors) > 0)
                                                @foreach($errors->all() as $error)
                                                    <div class="alert alert-success" role="alert">
                                                        <span class="icon fa fa-check" aria-hidden="true"></span>
                                                        <span class="sr-only">{{ trans('labels.Setting') }}:</span>
                                                        {{ $error }}
                                                    </div>
                                                @endforeach
                                            @endif
                                            @include('admin.common.feedback')
                                </div>  
 <div class="col-xs-12 table-responsive">
                                    <table class="table table-bordered table-striped example1">
                                        <thead>
                                        <tr>
                                            <!--<th>ID</th>-->
                                            <th>{{ trans('labels.Name') }}</th>
                                            <th>{{ trans('labels.Value') }}</th>
                                            <th>{{ trans('labels.Action') }}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @if(count($result['settings'])>0)
                                            @foreach ($result['settings'] as $label)
                                                <tr>
                                                    <td>{{ $label->title }}</td>
                                                    <td>{{ $label->value }}</td>
                                                    <td><a data-toggle="tooltip" data-placement="bottom" title="Edit" href="{{url('admin/setting/edit')}}/{{ $label->id }}" class="badge bg-light-blue"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>

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
            <!-- /.col -->

    <!-- /.row -->

    <!-- Main row -->

    <!-- /.row -->
    </section>
    <!-- /.content -->
    </div>
@endsection
