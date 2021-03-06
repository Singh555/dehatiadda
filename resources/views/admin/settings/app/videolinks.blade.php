@extends('admin.layout')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>  <small>{{ trans('labels.ListingAllVideos') }}...</small> </h1>
            <ol class="breadcrumb">
                <li><a href="{{ URL::to('admin/dashboard/this_month') }}"><i class="fa fa-dashboard"></i> {{ trans('labels.breadcrumb_dashboard') }}</a></li>
                <li class="active">{{ trans('labels.videolinks') }}</li>
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
                            <h3 class="box-title">{{ trans('labels.ListingAllVideos') }} </h3>
                            <div class="box-tools pull-right">
                                <a href="{{ URL::to('admin/addvideo')}}" type="button" class="btn btn-block btn-primary">{{ trans('labels.AddNewVideo') }}</a>
                            </div>
                        </div>

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
                                <div class="col-xs-12 table-responsive">
                                    <table id="example1" class="table table-bordered table-striped">
                                        <thead>
                                        <tr>
                                            <!--<th>ID</th>-->
                                            <th>{{ trans('labels.Title') }}</th>
                                            <th>{{ trans('labels.Link') }}</th>
                                            <th class="notexport">{{ trans('labels.Action') }}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @if(count($result['data'])>0)
                                            @foreach ($result['data'] as $label)
                                                <tr>
                                                <!--<td>{{ $label->id }}</td>-->
                                                    <td>{{ $label->title }}</td>
                                                    <td>{{ $label->link }}</td>
                                                    <td><a data-toggle="tooltip" data-placement="bottom" title="Edit" href="editVideoLink/{{ $label->id }}" class="badge-info badge-pill"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>

                                                    <!-- <a data-toggle="tooltip" data-placement="bottom" title="Delete" id="deleteLabelId" label_id ="{{ $label->id }}" class="badge bg-red"><i class="fa fa-trash" aria-hidden="true"></i></a>-->
                                                </tr>
                                            @endforeach
                                        
                                        @endif
                                        </tbody>
                                    </table>
                                    <div class="col-xs-12 text-right">
                                        {{$result['data']->links()}}
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

            <!-- deleteLabelModal -->
            <div class="modal fade" id="deleteLabelModal" tabindex="-1" role="dialog" aria-labelledby="deleteLabelModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="deleteLabelModalLabel">{{ trans('labels.DeleteLabel') }}</h4>
                        </div>
                        {!! Form::open(array('url' =>'admin/deleteLabel', 'name'=>'deleteLabel', 'id'=>'deleteLabel', 'method'=>'post', 'class' => 'form-horizontal', 'enctype'=>'multipart/form-data')) !!}
                        {!! Form::hidden('action',  'delete', array('class'=>'form-control')) !!}
                        {!! Form::hidden('label_id',  '', array('class'=>'form-control', 'id'=>'label_id')) !!}
                        <div class="modal-body">
                            <p>{{ trans('labels.DeleteLabelText') }}</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('labels.Close') }}</button>
                            <button type="submit" class="btn btn-primary" id="deleteLabel">{{ trans('labels.Delete') }}</button>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>

            <!-- /.row -->
        </section>
        <!-- /.content -->
    </div>
@endsection