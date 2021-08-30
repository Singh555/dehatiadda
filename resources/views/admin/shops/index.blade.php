@extends('admin.layout')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1> Shops <small>Listing All Shops...</small> </h1>
            <ol class="breadcrumb">
                <li><a href="{{ URL::to('admin/dashboard/this_month') }}"><i class="fa fa-dashboard"></i> {{ trans('labels.breadcrumb_dashboard') }}</a></li>
                <li class="active">Shops</li>
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
                                            <th>@sortablelink('shop_name', trans('labels.Name') )</th>
                                            <th>Gst No</th>
                                            <th>Additional Info</th>
                                            <th class="notexport">{{ trans('labels.Image') }}</th>
                                            <th>@sortablelink('created_at', trans('labels.AddedModifiedDate') )</th>
                                            <th class="notexport">{{ trans('labels.Status') }}</th>
                                            <th class="notexport">{{ trans('labels.Action') }}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @if(count($result['shops'])>0)
                                        @php
                                        $count = 0;
                                        @endphp
                                            @foreach ($result['shops'] as $shops)
                                          @php $count++; @endphp
                                           <tr>
                                           <td>{{ $count }}</td>
                                                    <td>{{ $shops->shop_name }}</td>
                                                    <td>{{ $shops->gst_no }}</td>
                                                    <td>Phone- {{ $shops->phone }},Email- {{ $shops->email }} ,<br>Address- {{ $shops->address }},{{ $shops->pin_code }} {{ $shops->city }} {{ $shops->state }} {{ $shops->country }}</td>
                                                    <td><img src="{{asset($shops->image )}}" alt="" width=" 100px"></td>
                                                    <td><strong>{{ trans('labels.AddedDate') }}: </strong> {{ date('d M, Y', strtotime($shops->created_at)) }}<br>
                                                        <strong>{{ trans('labels.ModifiedDate') }}: </strong>@if(!empty($shops->updated_at)) {{ date('d M, Y', strtotime($shops->updated_at)) }}  @endif<br>
                                                     <td>{!! getstatusLabel($shops->status) !!}</td>
                                                    <td><ul class="nav table-nav">
                                                    <li class="dropdown">
                                                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                                                            {{ trans('labels.Action') }} <span class="caret"></span>
                                                        </a>
                                                        <ul class="dropdown-menu">
                                                            <li role="presentation"><a data-toggle="tooltip" data-placement="bottom" title="{{ trans('labels.View') }}" href="{{url('admin/shop/detail')}}/{{ $shops->id }}" class=""><i class="fa fa-eye" aria-hidden="true"></i> view</a>
                                                            </li>
                                                            <div class="dropdown-divider"></div>
                                                            @if($shops->status == 'ACTIVE')
                                                            <li role="presentation"><a href="#" onclick="changeShopStatus('{{ $shops->id }}','INACTIVE')" data-toggle="tooltip" data-placement="bottom" title="Inactive" class=""> Inactive</a>
                                                            </li>
                                                            @endif
                                                            @if($shops->status == 'INACTIVE')
                                                            <li role="presentation"><a href="#" onclick="changeShopStatus('{{ $shops->id }}','ACTIVE')" data-toggle="tooltip" data-placement="bottom" title="Inactive" class=""> Active</a>
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

                                        {!! $result['shops']->appends(\Request::except('page'))->render() !!}
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
                            <h4 class="modal-title" id="shopModalLabel"></h4>
                        </div>
                        {!! Form::open(array('url' =>'admin/shop/updateStatus', 'method'=>'post', 'class' => 'form-horizontal', 'enctype'=>'multipart/form-data')) !!}
                        {!! Form::hidden('status', '', array('class'=>'form-control','id'=>'shop_status')) !!}
                        {!! Form::hidden('id',  '', array('class'=>'form-control', 'id'=>'shop_id')) !!}
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
function changeShopStatus(shop_id,status){
            $('#shop_id').val(shop_id);
            $('#shopModalLabel').text(status);
            $('#shop_status').val(status);
		$("#InactiveModal").modal('show');
        }
</script>
@endsection
