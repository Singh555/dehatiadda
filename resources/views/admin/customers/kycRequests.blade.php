@extends('admin.layout')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1> {{ trans('labels.Customers') }} <small>{{ trans('labels.ListingKyc') }}...</small> </h1>
        <ol class="breadcrumb">
            <li><a href="{{ URL::to('admin/dashboard/this_month')}}"><i class="fa fa-dashboard"></i> {{ trans('labels.breadcrumb_dashboard') }}</a></li>
            <li class="active">{{ trans('labels.ListingKyc') }}</li>
        </ol>
    </section>

    <!-- Main content -->
    
                <section class="content">
    <!-- Main content -->
    
                <div class="row">
            <div class="col-md-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">{{ trans('labels.ListingKyc') }}</h3>
                           
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                @if (count($errors) > 0)
                                  @if($errors->any())
                                  <div class="alert alert-success alert-dismissible" role="alert">
                                      <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                      {{$errors->first()}}
                                  </div>
                                  @endif
                                @endif
                                @if(session()->has('message'))
                                          <div class="alert alert-success alert-dismissible" role="alert">
                                              <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                              {{ session()->get('message') }}
                                          </div>
                                      @endif
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12 table-responsive">
                                <table id="example1" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>@sortablelink('id', trans('labels.ID') )</th>
                                            <th>{{ trans('labels.Info') }} </th>
                                            <th>{{ trans('labels.Status') }} </th>
                                            <th class="notexport">{{ trans('labels.Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (isset($customers['result']))
                                        @foreach ($customers['result'] as $listingCustomers)
                                        <tr>
                                            <td>{{ $listingCustomers->id }} </td>
                                            <td>                                               
                                                <strong>{{ trans('labels.Adhar') }}: </strong> {{ $listingCustomers->adhar_no }},&nbsp;
                                                <strong>{{ trans('labels.Pan') }}: </strong> {{ $listingCustomers->pan_no }},&nbsp;
                                                <strong>{{ trans('labels.Name') }}: </strong> {{ $listingCustomers->name }}
                                                
                                            </td>
                                            <td>{!! getStatusLabel($listingCustomers->status) !!}</td>
                                            <td>
                                                <a class="" href="#" onclick="event.preventDefault(); document.getElementById('kyc-view-{{ $listingCustomers->id }}').submit();"><i class="fa fa-eye">&nbsp;</i> View</a>
                                          <form id="kyc-view-{{ $listingCustomers->id }}" action="{{ url('admin/customers/kyc/display') }}" method="post" style="display: none;">
                                              @csrf
                                              <input type="hidden" name="id" value="{{ $listingCustomers->id }}" />
                                          </form>
                                            </td>
                                        </tr>
                                        @endforeach
                                        @else
                                        <tr>
                                            <td colspan="4">{{ trans('labels.NoRecordFound') }}</td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                                @if (count($customers['result']) > 0)
                                <div class="col-xs-12 text-right">
                                    {{$customers['result']->links()}}
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <!-- /.box-body -->
                </div>
                </div>
                </div>
                <!-- /.box -->
            

       
</section>
</div>

@endsection
@section('js')


@endsection