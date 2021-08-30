<?php

 function circleCategories(){
  $categories = getCategoriesData();
  if($categories){
  $option = '<div class="container">
        <div class="swiper-container">
            <div class="swiper-wrapper">';

    foreach($categories as $parents){
      

      //$option .= '<a class="dropdown-item categories-list '.$selected.'" value="'.$parents->categories_name.'" slug="'.$parents->slug.'" '.$selected.'>'.$parents->categories_name.'</a>';
     $option .= '<div class="swiper-slide">
                    <a href="' . url('shop?category=') . $parents->slug . '">
                        <div class="slider-box">

                            <div class="img-box">
                                <img src="'.asset($parents->categories_image_url).'">
                            </div>


                        </div>
                    </a>
                </div>';
        

    }
  $option .='</div>
        </div>
        </div>';  

  echo $option;
}
}
 


 function getCategoriesData(){
  $items = DB::table('categories')
      ->leftJoin('categories_description','categories_description.categories_id', '=', 'categories.categories_id')
      ->select('categories.categories_id', 'categories.categories_slug as slug','categories_description.categories_name','categories.categories_image_url', 'categories.parent_id')
      ->where('categories_description.language_id','=', Session::get('language_id'))
      ->where('categories.categories_status','=', 1)
      ->where('categories.parent_id','=', 0)
      ->get();
   if($items->isNotEmpty()){
      
      return  $items;
    }
}

 ?>
