<style>
    
    .ribbon2 {
 width: 40px;
 padding: 10px 0;
 position: absolute;
 top: -6px;
 left:4px;
 text-align: center;
 border-top-left-radius: 3px;
 background: #001c5c;
 color: #fff;
}
.ribbon2:before {
 height: 0;
 width: 0;
 right: -5.5px;
 top: 0.1px;
 border-bottom: 6px solid #001c5c;
 border-right: 6px solid transparent;
}
.ribbon2:before, .ribbon2:after {
  content: "";
  position: absolute;
}
.ribbon2:after {
  height: 0;
  width: 0;
  bottom: -29.5px;
  left: 0;
  border-left: 20px solid #001c5c;
  border-right: 20px solid #001c5c;
  border-bottom: 31px solid transparent;
}
</style>



<div class="product2 product10">
  <article>
    <div class="pro-thumb">
    <a href="{{ URL::to('/product-detail/'.$products->products_slug)}}">
    <img class="img-fluid" src="{{asset('').$products->image_path}}" alt="{{$products->products_name}}">
    </a>
        <div class="">
          <?php
            $current_date = date("Y-m-d", strtotime("now"));

            $string = substr($products->products_date_added, 0, strpos($products->products_date_added, ' '));
            $date = date_create($string);
            date_add($date, date_interval_create_from_date_string($web_setting[20]->value . " days"));
            $after_date = date_format($date, "Y-m-d");
            if ($after_date >= $current_date) {
                print '<span class="badge badge-info">';
                print __('website.New');
                print '</span>';
            }
          ?>
          <?php
              if (!empty($products->discount_price)) {
                  $discount_price = $products->discount_price * session('currency_value');
              }
              $orignal_price = $products->products_price * session('currency_value');

              if (!empty($products->discount_price)) {

                  if (($orignal_price + 0) > 0) {
                      $discounted_price = $orignal_price - $discount_price;
                      $discount_percentage = $discounted_price / $orignal_price * 100;
                  } else {
                      $discount_percentage = 0;
                      $discounted_price = 0;
                  }
        ?>

        <span class=" ribbon2"  data-toggle="tooltip" data-placement="bottom" title="<?php echo (int) $discount_percentage; ?>% @lang('website.off')"><?php echo (int) $discount_percentage; ?>%</span>
        <?php }?>


      @if($products->is_feature == 1)
        <span class="badge badge-success">@lang('website.Featured')</span>
      @endif
          </div>


    </div>

    <div class="content">
       <span class="tag">
        <?php 
          
          $cat_name = '';
          foreach($products->categories as $key=>$category){
              $cat_name = $category->categories_name;
          }              
                
          echo $cat_name;
        ?>  
        </span>

        <h5 class="title"><a href="{{ URL::to('/product-detail/'.$products->products_slug)}}">{{$products->products_name}}</a></h5>

        <div class="pricetag">

            <div class="price">
              @if(!empty($products->discount_price))
                {{Session::get('symbol_left')}}&nbsp;{{$discount_price+0}}&nbsp;{{Session::get('symbol_right')}}
              <span> {{Session::get('symbol_left')}}{{$orignal_price+0}}{{Session::get('symbol_right')}}</span>
              @else
                {{Session::get('symbol_left')}}&nbsp;{{$orignal_price+0}}&nbsp;{{Session::get('symbol_right')}}
              @endif
            </div>


              @if($products->products_type==0)
                @if(!in_array($products->products_id,$result['cartArray']))
                    @if($products->defaultStock==0)

                        <button type="button" class="icon btn-danger swipe-to-top" products_id="{{$products->products_id}}" data-toggle="tooltip" data-placement="bottom" title="@lang('website.Out of Stock')"><i class="fas fa-shopping-bag"></i> </button>
                    @elseif($products->products_min_order>1)
                    <a class="icon btn-secondary swipe-to-top" href="{{ URL::to('/product-detail/'.$products->products_slug)}}" data-toggle="tooltip" data-placement="bottom" title="@lang('website.View Detail')"><i class="fas fa-shopping-bag"></i></a>
                    @else
                        <button type="button" class="icon btn-secondary cart swipe-to-top" products_id="{{$products->products_id}}" data-toggle="tooltip" data-placement="bottom" title="@lang('website.Add to Cart')"><i class="fas fa-shopping-bag"></i> </button>
                    @endif
                @else
                    <button type="button" class="icon btn-secondary active swipe-to-top" data-toggle="tooltip" data-placement="bottom" title="@lang('website.Added')"><i class="fas fa-shopping-bag"></i> </button>
                @endif
            @elseif($products->products_type==1)
                <a class="icon btn-secondary swipe-to-top" href="{{ URL::to('/product-detail/'.$products->products_slug)}}" data-toggle="tooltip" data-placement="bottom" title="@lang('website.View Detail')"><i class="fas fa-shopping-bag"></i></a>
            @elseif($products->products_type==2)
                <a href="{{$products->products_url}}" target="_blank" class="icon btn-secondary" data-toggle="tooltip" data-placement="bottom" title="@lang('website.External Link')"><i class="fas fa-shopping-bag"></i></a>
            @endif
            
        </div>


    </div>
  </article>
</div>


