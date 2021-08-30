<!DOCTYPE html>
<html class="no-js" lang="en">
  <head>
      
@include('web.common.meta')
@yield('css')
<style>
    @charset "utf-8";
    /* CSS Document */


    .swiper-slide{
        width:100px !important;
        cursor:pointer;
        /* Center slide text vertically */
        display: -webkit-box;
        display: -ms-flexbox;
        display: -webkit-flex;
        display: flex;
        -webkit-box-pack: center;
        -ms-flex-pack: center;
        -webkit-justify-content: center;
        justify-content: center;
        -webkit-box-align: center;
        -ms-flex-align: center;
        -webkit-align-items: center;
        align-items: center;
    }

    .slider-box{
        margin:10px;
        height:80px;
        width:80px;
        position:relative;
        background-color: rgb(174 173 173 / 9%);
        border-radius:50%;
        display:flex;
        flex-direction:column;
        justify-content:center;
        align-items:center;
        text-align:center;
        overflow: hidden;
        
        border:2px solid rgba(187,187,187,0.40);
    }
    .slider-box a{
        text-decoration:none;
        text-align:center;}
    .img-box{
        height:125px;
    }
    .img-box img{
        border-radius:50%;
        height:auto;
        max-width:100%;
        max-height:100%;}


    .slider-box:hover{
        box-shadow:2px 2px 12px rgba(47,47,47,0.40);
    }
    .slider-box:hover .img-box img{
        transform:scale(1.07);
        transition:all ease 0.3s;}
    .cart:hover{
        transform:scale(1.1);
        background-color:#fe9048;
        box-shadow:2px 2px 12px rgba(47,47,47,0.40);
        transition:all ease 0.1s;}					
							
							
</style>
  </head>
    <!-- dir="rtl" -->
    <body class="animation-s<?php  echo $final_theme['transitions']; if(!empty(session('direction')) and session('direction')=='rtl') print ' bodyrtl';?> ">
      
      <div class="se-pre-con" id="loader" style="display: block">
        <div class="pre-loader">
          <div class="la-line-scale">
            <div></div>
            <div></div>
            <div></div>
            <div></div>
          </div>
          <p>@lang('website.Loading')..</p>
        </div>
     
      </div>

      @if (count($errors) > 0)
          @if($errors->any())
           <script>swal("Congrates!", "Thanks For Shopping!", "success");</script>
          @endif
      @endif
      
      <!-- Header Sections -->

        <!-- Top Offer -->
        <div class="header-area">
          <?php  echo $final_theme['top_offer']; ?>
        </div>

        
        <!-- End Top Offer -->
        
        <!-- Header Content -->
        <?php echo $final_theme['header']; ?>        
      
<!--swiper  slider end-->
        <!-- End Header Content -->
        <?php  echo $final_theme['mobile_header']; ?>
      <!-- End of Header Sections -->
        <!--- circular categories---->
        @include('web.common.CircleCategories')
        @php    circleCategories(); @endphp
        {{--
        <div class="container">
        <div class="swiper-container">
            <div class="swiper-wrapper">
                <!--slide 1-------------------------------------->
                
                <div class="swiper-slide">
                    <a href="https://www.w3schools.com/css/css3_borders.asp">
                        <div class="slider-box">

                            <div class="img-box">
                                <img src="{{asset('web/images/products/bag1.png') }}">
                            </div>


                        </div>
                    </a>
                </div>

            </div>
        </div>
        </div> 
        --}}
       <!-- NOTIFICATION CONTENT -->
         @include('web.common.notifications')
      <!-- END NOTIFICATION CONTENT -->
         @yield('content')



      <!-- Footer content -->
      <div class="notifications" id="notificationWishlist"></div>
      <?php  echo $final_theme['footer']; ?>

      <!-- End Footer content -->
      <?php  echo $final_theme['mobile_footer']; ?>
      @if(!empty($result['commonContent']['setting'][119]) and $result['commonContent']['setting'][119]->value==1)
      
        @if(empty(Cookie::get('cookies_data')))        

        <div class="alert alert-warning alert-dismissible alert-cookie fade show" role="alert">
          <div class="container">
              <div class="row align-items-center">
                  <div class="col-12 col-md-8 col-lg-9">
                      <div class="pro-description">
                          @lang('website.This site uses cookies. By continuing to browse the site you are agreeing to our use of cookies. Review our')
                          <a target="_blank" href="{{ URL::to('/page?name=cookies')}}" class="btn-link">@lang('website.cookies information')</a> 
                          
                          @lang('website.for more details').
                      </div>
                  </div>
                  <div class="col-12 col-md-4 col-lg-3">
                      <button type="button" class="btn btn-secondary swipe-to-top" id="allow-cookies">
                        @lang('website.OK, I agree')
                          </button>
                  </div>
              </div>
          </div>
        </div>
        @endif
      @endif

      <!-- Button trigger modal -->
      {{-- and empty(session('newsletter') --}}
      @if(!empty($result['commonContent']['setting'][118]) and $result['commonContent']['setting'][118]->value==1 and Request::path() == '/' ) 
      
    
       <!-- Newsletter Modal -->
       <div class="modal fade show" id="newsletterModal" tabindex="-1" role="dialog" aria-hidden="false">
       
       <div class="modal-dialog modal-dialog-centered modal-lg newsletter" role="document">
         <div class="modal-content">
             <div class="modal-body">

                 <div class="container">
                     <div class="row align-items-center">                   
                  
                     <div class="col-12 col-md-6" >
                        <div class="pro-image">
                          @if($result['commonContent']['setting'][124]->value)
                          <img class="img-fluid" src="{{asset('').$result['commonContent']['setting'][124]->value }}" alt="blogImage">  
                          @endif                        
                        </div>
                     </div>
                     <div class="col-12 col-md-6" style="padding-left: 0;">
                      <div class="promo-box">
                          <h2 class="text-01">                            
                            @lang('website.Sign Up for Our Newsletter')
                          </h2>
                          <p class="text-03">                            
                            @lang('website.Be the first to learn about our latest trends and get exclusive offers')
                          </p>
                            <form class=" mailchimp-form" action="{{url('subscribeMail')}}" >
                            <div class="form-group">
                              <input type="email" value="" name="email" class="required email form-control" placeholder="@lang('website.Enter Your Email Address')...">
                            </div>
                            <button type="submit" value="Subscribe" name="subscribe" id="mc-embedded-subscribe" class="btn btn-secondary swipe-to-top newsletter">@lang('website.Subscribe')</button>
                          </form>
                      </div>
                   </div>
                   </div>
                   <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                       <span aria-hidden="true">×</span>
                     </button>
                 </div>
               </div>
         </div>
       </div>
       </div>
       @endif


      <div class="mobile-overlay"></div>
      <!-- Product Modal -->


      <a href="web/#" id="back-to-top" class="btn-secondary swipe-to-top" title="@lang('website.back_to_top')">&uarr;</a>


      <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
      
        <div class="modal-dialog modal-lg" role="document">
          <div class="modal-content">
              <div class="modal-body">
                  
                  <div class="container" id="products-detail">
                    
                  </div>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                </div>
          </div>
        </div>
    </div>

      <!-- Include js plugin -->
       @include('web.common.scripts')
       @yield('js')

       <script>
       
       // JavaScript Document
       //swiper script

      var swiper = new Swiper('.swiper-container', {
      slidesPerView: "auto",
      //centeredSlides:true,
      spaceBetween: 30,
      //loop:true,
      pagination: {
        el: '.swiper-pagination',
        clickable: true,
      },
      
    });

       
       
       </script>
       
    </body>
</html>
