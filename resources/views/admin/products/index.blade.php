@extends('admin.layout')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1> {{ trans('labels.Products') }} <small>{{ trans('labels.ListingAllProducts') }}...</small> </h1>
            <ol class="breadcrumb">
                <li><a href="{{ URL::to('admin/dashboard/this_month') }}"><i class="fa fa-dashboard"></i> {{ trans('labels.breadcrumb_dashboard') }}</a></li>
                <li class="active"> {{ trans('labels.Products') }}</li>
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

                            <div CLASS="col-lg-12"> <h7 style="font-weight: bold; padding:0px 16px; float: left;">{{ trans('labels.FilterByCategory/Products') }}:</h7>

                                <br>
                           <div class="col-lg-10 form-inline">

                                <form  name='registration' id="registration" class="registration" method="get">
                                    <input type="hidden" name="_token" value="{{csrf_token()}}">

                                    <div class="input-group-form search-panel ">
                                        <select id="FilterBy" type="button" class="btn btn-default dropdown-toggle form-control input-group-form " data-toggle="dropdown" name="categories_id">

                                            <option value="" selected disabled hidden>{{trans('labels.ChooseCategory')}}</option>
                                            <option value="ALL" @if(isset($_REQUEST['categories_id']) and $_REQUEST['categories_id'] == 'ALL')selected='' @endif>All</option>
                                            @foreach ($results['subCategories'] as  $key=>$subCategories)
                                                <option value="{{ $subCategories->id }}"
                                                        @if(isset($_REQUEST['categories_id']) and !empty($_REQUEST['categories_id']))
                                                          @if( $subCategories->id == $_REQUEST['categories_id'])
                                                            selected
                                                          @endif
                                                        @endif
                                                >{{ $subCategories->name }}</option>
                                            @endforeach
                                        </select>
                                        <input type="text" class="form-control input-group-form " name="product" placeholder="Search term..." id="parameter"  @if(isset($product)) value="{{$product}}" @endif />
                                        <button class="btn btn-primary " id="submit" type="submit"><span class="glyphicon glyphicon-search"></span></button>
                                        @if(isset($product,$categories_id))  <a class="btn btn-danger " href="{{url('admin/products/display')}}"><i class="fa fa-ban" aria-hidden="true"></i> </a>@endif
                                    </div>
                                </form>
                                <div class="col-lg-4 form-inline" id="contact-form12"></div>
                            </div>
                            <div class="box-tools pull-right">
                                <a href="{{ URL::to('admin/products/add') }}" type="button" class="btn btn-block btn-primary">{{ trans('labels.AddNew') }}</a>
                            </div>
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
                                    @include('admin.common.feedback')
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-12">
                                    <table id="example1" class="table table-bordered table-striped">
                                        <thead>
                                        <tr>
                                            <th>S.No.</th>
                                            <th>{{ trans('labels.Image') }}</th>
                                            <th>@sortablelink('categories_name', trans('labels.Category') )</th>
                                            <th>@sortablelink('products_name', trans('labels.Name') )</th>
                                            <th>{{ trans('labels.Additional info') }}</th>
                                            <th>@sortablelink('created_at', trans('labels.ModifiedDate') )</th>
                                            <th>{{ trans('labels.Status') }}</th>
                                            <th>{{ trans('labels.Update') }}</th>
                                            <th>{{ trans('labels.Action') }}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @if(count($results['products'])>0)
                                            @php  $resultsProduct = $results['products']->unique('products_id')->keyBy('products_id');
                                             $count = 0;
                                            @endphp
                                            @foreach ($resultsProduct as  $key=>$product)
                                            @php $count++; @endphp

                                            @php $categoryArray = DB::table('products_to_categories')->leftJoin('categories', 'categories.categories_id', '=', 'products_to_categories.categories_id')
                ->leftJoin('categories_description', 'categories.categories_id', '=', 'categories_description.categories_id')->where('products_to_categories.products_id',$product->products_id)->pluck('categories_description.categories_name'); 
                
                @endphp
                                                <tr>
                                                    <td>{{ $count }}</td>
                                                    <td><img src="{{asset($product->products_image_url)}}" alt="" height="50px"></td>
                                                    <td>
                                                       {{ $categoryArray->implode(' / ') }}
                                                    </td>
                                                    <td>
                                                        {{ $product->products_name }} @if(!empty($product->products_model)) ( {{ $product->products_model }} ) @endif
                                                    </td>
                                                    <td>
                                                        {{ $product->first_name }} {{ $product->last_name }}
                                                    </td>
                                                    <td>
                                                        <strong>{{ trans('labels.Product Type') }}:</strong>
                                                        @if($product->products_type==0)
                                                            {{ trans('labels.Simple') }}
                                                        @elseif($product->products_type==1)
                                                            {{ trans('labels.Variable') }}
                                                        @elseif($product->products_type==2)
                                                            {{ trans('labels.External') }}
                                                        @endif
                                                        <br>
                                                        @if(!empty($product->manufacturers_name))
                                                            <strong>{{ trans('labels.Manufacturer') }}:</strong> {{ $product->manufacturers_name }}<br>
                                                        @endif
                                                        <strong>{{ trans('labels.Price') }}: </strong>   
                                                        @if(!empty($result['commonContent']['currency']->symbol_left)) {{$result['commonContent']['currency']->symbol_left}} @endif {{ $product->products_price }} @if(!empty($result['commonContent']['currency']->symbol_right)) {{$result['commonContent']['currency']->symbol_right}} @endif
                                                        <br>
                                                        <strong>{{ trans('labels.Weight') }}: </strong>  {{ $product->products_weight }}{{ $product->products_weight_unit }}<br>
                                                        <strong>{{ trans('labels.Viewed') }}: </strong>  {{ $product->products_viewed }}<br>
                                                        @if(!empty($product->specials_id))
                                                            <strong class="badge bg-light-blue">{{ trans('labels.Special Product') }}</strong><br>
                                                            <strong>{{ trans('labels.SpecialPrice') }}: </strong>  {{ $product->specials_products_price }}<br>

                                                            @if(($product->specials_id) !== null)
                                                                @php  $mytime = Carbon\Carbon::now()  @endphp
                                                                <strong>{{ trans('labels.ExpiryDate') }}: </strong>

                                                                {{-- @if($product->expires_date > $mytime->toDateTimeString()) --}}
                                                                    {{  date('d-m-Y', $product->expires_date) }}
                                                                {{-- @else
                                                                    <strong class="badge bg-red">{{ trans('labels.Expired') }}</strong>
                                                                @endif --}}
                                                                <br>
                                                            @endif
                                                        @endif
                                                    </td>
                                                    <td>
                                                    @if($product->products_status==0)
                                                    <span class="label label-warning"><i class="fa fa-ban"></i> Inactive</span>
                                                        @elseif($product->products_status==1)
                                                        <span class="label label-success"><i class="fa fa-check-square"></i> Active</span>
                                                        @else
                                                        @endif
                                                    </td>
                                                    <td>
                                                        {{ $product->productupdate }}
                                                    </td>

                                                    <td>
                                                        @if($product->products_status==0)
                                                        <button class="btn btn-success" onclick="changeProductStatus('{{$product->products_id}}','1','Active Product');" style="width: 100%; margin-bottom: 5px;"><i class="fa fa-check-square"></i> Active</button>
                                                        @elseif($product->products_status==1)
                                                   <button class="btn btn-warning" onclick="changeProductStatus('{{$product->products_id}}','0','Inactive Product');" style="width: 100%; margin-bottom: 5px;"><i class="fa fa-ban"></i> Inactive</button>
                                                        @else
                                                        @endif
                                                      
                                                      <a class="btn btn-primary" style="width: 100%; margin-bottom: 5px;" href="{{url('admin/products/edit')}}/{{ $product->products_id }}">{{ trans('labels.EditProduct') }}</a>
                                                      </br>
                                                      @if($product->products_type==1)
                                                          <a class="btn btn-info" style="width: 100%;  margin-bottom: 5px;" href="{{url('admin/products/attach/attribute/display')}}/{{ $product->products_id }}">{{ trans('labels.ProductAttributes') }}</a>

                                                          </br>
                                                      @endif
                                                      <a class="btn btn-warning" style="width: 100%;  margin-bottom: 5px;" href="{{url('admin/products/images/display/'. $product->products_id) }}">{{ trans('labels.ProductImages') }}</a>
                                                      </br>
                                                      <a class="btn btn-danger" style="width: 100%;  margin-bottom: 5px;" id="deleteProductId" products_id="{{ $product->products_id }}">{{ trans('labels.DeleteProduct') }}</a>
                                                      </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                        </tbody>
                                    </table>

                                </div>


                            </div>
                                <div class="col-xs-12" style="background: #eee;">


                                  @php
                                    if($results['products']->total()>0){
                                      $fromrecord = ($results['products']->currentpage()-1)*$results['products']->perpage()+1;
                                    }else{
                                      $fromrecord = 0;
                                    }
                                    if($results['products']->total() < $results['products']->currentpage()*$results['products']->perpage()){
                                      $torecord = $results['products']->total();
                                    }else{
                                      $torecord = $results['products']->currentpage()*$results['products']->perpage();
                                    }

                                  @endphp
                                  <div class="col-xs-12 col-md-6" style="padding:30px 15px; border-radius:5px;">
                                    <div>Showing {{$fromrecord}} to {{$torecord}}
                                        of  {{$results['products']->total()}} entries
                                    </div>
                                  </div>
					<div class="col-xs-12 col-md-6 text-right">
						{{$results['products']->appends(Request::all())->links()}}
					</div>
                              </div>
                        </div>
                        <!-- /.box-body -->
                    </div>
                    <!-- /.box -->
                </div>
                <!-- /.col -->
            </div>

            <!-- deleteProductModal -->
            <div class="modal fade" id="deleteproductmodal" tabindex="-1" role="dialog" aria-labelledby="deleteProductModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="deleteProductModalLabel">{{ trans('labels.DeleteProduct') }}</h4>
                        </div>
                        {!! Form::open(array('url' =>'admin/products/delete', 'name'=>'deleteProduct', 'id'=>'deleteProduct', 'method'=>'post', 'class' => 'form-horizontal', 'enctype'=>'multipart/form-data')) !!}
                        {!! Form::hidden('action',  'delete', array('class'=>'form-control')) !!}
                        {!! Form::hidden('products_id',  '', array('class'=>'form-control', 'id'=>'products_id')) !!}
                        <div class="modal-body">
                            <p>{{ trans('labels.DeleteThisProductDiloge') }}?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('labels.Close') }}</button>
                            <button type="submit" class="btn btn-primary" id="deleteProduct">{{ trans('labels.DeleteProduct') }}</button>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
            <!-- /.row -->
<div class="modal fade" id="changeProductStatusModal" tabindex="-1" role="dialog" aria-labelledby="changeProductStatusModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="productStatusModalLabel"></h4>
                        </div>
                        {!! Form::open(array('url' =>'admin/products/updateStatus', 'method'=>'post', 'class' => 'form-horizontal', 'enctype'=>'multipart/form-data')) !!}
                        {!! Form::hidden('status', '', array('class'=>'form-control','id'=>'product_status')) !!}
                        {!! Form::hidden('id',  '', array('class'=>'form-control', 'id'=>'change_product_id')) !!}
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
            <!-- Main row -->

            <!-- /.row -->
        </section>
        <!-- /.content -->
    </div>
@endsection
@section('js')
<script>
function changeProductStatus(product_id,status,title){
            $('#change_product_id').val(product_id);
            $('#productStatusModalLabel').text(title);
            $('#product_status').val(status);
		$("#changeProductStatusModal").modal('show');
        }
</script>
@endsection