@extends('admin.layout')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1> Vendors <small>Listing All Vendors...</small> </h1>
            <ol class="breadcrumb">
                <li><a href="{{ URL::to('admin/dashboard/this_month') }}"><i class="fa fa-dashboard"></i> {{ trans('labels.breadcrumb_dashboard') }}</a></li>
                <li class="active">Vendors</li>
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


                            
                        </div>

                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="row">
                                <div class="col-xs-12">
                                    @include('admin.common.feedback')

                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-12">
                                    <table id="example1" class="table table-bordered table-striped">
                                        <thead>
                                        <tr>
                                            <th>S.No.</th>
                                            <th>@sortablelink('shopfname', trans('labels.Name') )</th>
                                            <th>Gst No</th>
                                            <th>Additional Info</th>
                                            <th>@sortablelink('created_at', trans('labels.AddedModifiedDate') )</th>
                                            <th class="notexport">{{ trans('labels.Status') }}</th>
                                            <th class="notexport">{{ trans('labels.Action') }}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @if(count($result['vendors'])>0)
                                        @php
                                        $count = 0;
                                        @endphp
                                            @foreach ($result['vendors'] as $vendors)
                                            @php $count++; @endphp
                                            <tr>
                                                <td>{{ $count }}</td>
                                                    <td>{{ $vendors->shopfname }} {{ $vendors->shoplname }}</td>
                                                    <td>{{ $vendors->gst_no }}</td>
                                                    <td>Phone- {{ $vendors->phone }},Email- {{ $vendors->email }} ,<br>Address- {{ $vendors->address }},{{ $vendors->pin_code }} {{ $vendors->city }} {{ $vendors->state }} {{ $vendors->country->Countries_name }}</td>
                                                    <td><strong>{{ trans('labels.AddedDate') }}: </strong> {{ date('d M, Y', strtotime($vendors->created_at)) }}<br>
                                                        <strong>{{ trans('labels.ModifiedDate') }}: </strong>@if(!empty($vendors->updated_at)) {{ date('d M, Y', strtotime($vendors->updated_at)) }}  @endif<br>
                                                     <td>{!! getstatusLabel($vendors->status) !!}</td>
                                                    <td><ul class="nav table-nav">
                                                    <li class="dropdown">
                                                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                                                            {{ trans('labels.Action') }} <span class="caret"></span>
                                                        </a>
                                                        <ul class="dropdown-menu">
                                                            <li role="presentation"><a data-toggle="tooltip" data-placement="bottom" title="{{ trans('labels.View') }}" href="{{url('admin/vendor/detail')}}/{{ $vendors->id }}" class=""><i class="fa fa-eye" aria-hidden="true"></i> view</a>
                                                            </li>
                                                            <div class="dropdown-divider"></div>
                                                            @if($vendors->status == 'ACTIVE')
                                                            <li role="presentation"><a href="#" onclick="changeVendorStatus('{{ $vendors->id }}','INACTIVE')" data-toggle="tooltip" data-placement="bottom" title="Inactive" class=""> Inactive</a>
                                                            </li>
                                                            @endif
                                                            @if($vendors->status == 'INACTIVE')
                                                            <li role="presentation"><a href="#" onclick="changeVendorStatus('{{ $vendors->id }}','ACTIVE')" data-toggle="tooltip" data-placement="bottom" title="Inactive" class=""> Active</a>
                                                            </li>
                                                            @endif
                                                                <div class="dropdown-divider"></div>
                                                        </ul>
                                                    </li>
                                                </ul>
                                                        
                                                        
                                                </tr>
                                            @endforeach
                                        @endif
                                        </tbody>
                                    </table>
                                    <div class="col-xs-12 text-right">

                                        {!! $result['vendors']->appends(\Request::except('page'))->render() !!}
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

            <!-- deleteBannerModal -->
            <div class="modal fade" id="InactiveModal" tabindex="-1" role="dialog" aria-labelledby="InactiveModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="vendorModalLabel"></h4>
                        </div>
                        {!! Form::open(array('url' =>'admin/vendor/updateStatus', 'method'=>'post', 'class' => 'form-horizontal', 'enctype'=>'multipart/form-data')) !!}
                        {!! Form::hidden('status', '', array('class'=>'form-control','id'=>'vendors_status')) !!}
                        {!! Form::hidden('vendors_id',  '', array('class'=>'form-control', 'id'=>'vendors_id')) !!}
                        <div class="modal-body">
                            <p>Are You Sure You want to perform this acrion ?</p>
                        </div>
                        <div class="modal-footer">
                            @csrf
                            <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('labels.Close') }}</button>
                            <button type="submit" class="btn btn-primary processing_btn">Yes</button>
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

@section('js')
<script>
//deleteTaxClassModal
	
        
        function changeVendorStatus(vendor_id,status){
            $('#vendors_id').val(vendor_id);
            $('#vendorModalLabel').text(status);
            $('#vendors_status').val(status);
		$("#InactiveModal").modal('show');
        }
        
</script>
@endsection
