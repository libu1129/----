<?php
/**
 * Popup Renderer
 * Finds the Popup assigned to current page and renders it.
**/

add_action('wp_head', 'bravepop_render_popup', 9);
function bravepop_render_popup() {
   $brave_popupID = filter_input(INPUT_GET, 'brave_popup');
   $brave_popupStep = filter_input(INPUT_GET, 'popup_step');

   do_action( 'bravepop_before_render' );
   //Popup Preview
   if($brave_popupID && is_user_logged_in()){ 
      return new BravePop_Popup( $brave_popupID , true, $brave_popupStep  ? absint($brave_popupStep) : false); 
   }

   //Bail if is Customizing the Website from Appearance > Customize
   if(is_customize_preview()){
      return;
   }

   $filtered_popups = bravepop_get_current_page_popups();
   if($filtered_popups && count($filtered_popups) > 0){
      foreach($filtered_popups as $key=>$value) {
         $popupID = $value->id;
         $popupType = $value->type;
         if(get_post_status( $popupID ) === 'publish' ){
            //Check if Popup has active ABTest. If does, display a variation randomly
            $post_abtest = json_decode(get_post_meta( $popupID, 'popup_abtest', true ));
            if(isset($post_abtest->active) && $post_abtest->active === true && count($post_abtest->items) > 0){
               $popupVariations = array();
               foreach ($post_abtest->items as $index => $popItem) {
                  $popupVariations[] = $popItem->id;
               }
               $popupID = $popupVariations[array_rand($popupVariations)];
            }



            new BravePop_Popup($popupID);
         }

         //Handle Popup Schedule
         if(get_post_status( $popupID ) === 'draft' ){
            $post_schedule = json_decode(get_post_meta( $popupID, 'popup_schedule', true ));
            if(!empty($post_schedule->active) && !empty($post_schedule->type)){
               if(($post_schedule->type === 'days' && count($post_schedule->days) > 0) || ($post_schedule->type === 'dates' && count($post_schedule->dates) > 0)){
                  return new BravePop_Popup($popupID);
               }     
            }
         }

      }
   }
   
   do_action( 'bravepop_after_render', array($filtered_popups) );
}



function bravepop_get_current_page_popups(){
   $fit_popups = array();
   $currentSettings = get_option('_bravepopup_settings');
   $currentPopups = $currentSettings['visibility'] ? $currentSettings['visibility'] : null;
   $pageInfo = bravepop_get_current_pageInfo();

   //echo json_encode($pageInfo);
   $currentPageType = $pageInfo->type;
   $currentPageID = $pageInfo->pageID;
   $currentSingleType = $pageInfo->singleType;

   //If User selected Woocommerce Shop page and the current page is the shop Archive, the current pagetype from archive to single
   if($currentPageType==='archive' && $currentSingleType === 'product' && get_option( 'woocommerce_shop_page_id' )){
      $currentPageType = 'single';
      $currentSingleType = 'page';
      $currentPageID = get_option( 'woocommerce_shop_page_id' );
   }

   if($currentPopups && is_array($currentPopups)){

      //echo json_encode($currentPopups);
      foreach($currentPopups as $key=>$value) {
         $popupID = $value->id;
         $itemType = !empty($value->type) ? $value->type : 'popup' ;
         $placement = $value->placement;
         $placementType = $placement && isset($placement->placementType) ? $placement->placementType : 'sitewide';
         $popupData = new stdClass(); $popupData->type = $itemType; $popupData->id = $popupID;
         //error_log( json_encode($placementType));
         if($placementType === 'sitewide'){
            $fit_popups[] = $popupData;
         }elseif ($currentPageType === 'front' && $placementType === 'front'){
            $fit_popups[] = $popupData;
         }elseif (isset($placement) && ($placementType === 'selected')){
               //IF is Page
               if($currentPageType === 'front' && isset($placement->pages) && is_array($placement->pages) && in_array( 'front', $placement->pages)){
                     $fit_popups[] = $popupData;
               }elseif($currentPageType === 'search'&& isset($placement->pages) && is_array($placement->pages) && in_array( 'search', $placement->pages)){
                  $fit_popups[] = $popupData;
               }elseif($currentPageType === 'notfound'&& isset($placement->pages) && is_array($placement->pages) && in_array( '404', $placement->pages)){
                  $fit_popups[] = $popupData;
               }elseif($currentPageType === 'single' && $currentSingleType === 'page'&& isset($placement->pages) && is_array($placement->pages) && isset($placement->pages)){
                  if(isset($placement->pages[0]) && $placement->pages[0] === 'all'){
                     $fit_popups[] = $popupData;
                  }elseif($currentPageID && isset($placement->pages) && is_array($placement->pages) && in_array( $currentPageID, $placement->pages)){
                     $fit_popups[] = $popupData;
                  }
               //IF is Post
               }elseif($currentPageType === 'single' && $currentSingleType === 'post' && isset($placement->posts) && is_array($placement->posts)){
                  if(isset($placement->posts[0]) && $placement->posts[0] === 'all'){
                     global $post;
                     if(isset($post->ID) && !empty($placement->postags)){
                        $currentPostTags = get_the_tags($post->ID); $userTags = explode(',', $placement->postags); $userTagsClean = array_map('trim', $userTags); 
                        $postTagsArray = array();
                        $hasTags = is_array($currentPostTags) && count($currentPostTags) > 0 ? true : false;
                        if($hasTags){   foreach($currentPostTags as $tag) {   $postTagsArray[] = $tag->name;  }    }
                        $matchedTags = $hasTags ? array_intersect($postTagsArray, $userTagsClean) : array();
                        if(count($matchedTags) > 0){
                           $fit_popups[] = $popupData;
                        }
                     }else{
                        $fit_popups[] = $popupData;
                     }
                  }elseif($currentPageID && in_array( $currentPageID, $placement->posts)){
                     $fit_popups[] = $popupData;
                  }
               //IF is Product
               }elseif($currentPageType === 'single' && $currentSingleType === 'product' && isset($placement->products) && is_array( $placement->products)){
                  if(isset($placement->products[0]) && $placement->products[0] === 'all'){
                     global $post;
                     if(isset($post->ID) && !empty($placement->productags)){
                        $productTerms = get_the_terms( $post->ID, 'product_tag' );
                        $currentProductTags = array();
                        if ( ! empty( $productTerms ) && ! is_wp_error( $productTerms ) ){  foreach ( $productTerms as $term ) {  $currentProductTags[] = $term->slug;  }  }
                        $userTags = explode(',', $placement->productags); $userTagsClean = array_map('trim', $userTags);
                        $hasTags = is_array($currentProductTags) && count($currentProductTags) > 0 ? true : false;
                        $matchedTags = $hasTags ? array_intersect($currentProductTags, $userTagsClean) : array();
                        if(count($matchedTags) > 0){
                           $fit_popups[] = $popupData;
                        }
                     }else{
                        $fit_popups[] = $popupData;
                     }
                  }elseif($currentPageID && isset($placement->products) && in_array( $currentPageID, $placement->products)){
                     $fit_popups[] = $popupData;
                  }
               //IF is Category
               }elseif($currentPageType === 'category' && isset($placement->categories) && is_array($placement->categories)){
                  if(isset($placement->categories[0]) && $placement->categories[0] === 'all'){
                     $fit_popups[] = $popupData;
                  }elseif($currentPageID && in_array( $currentPageID, $placement->categories)){
                     $fit_popups[] = $popupData;
                  }
               //IF is Product Category
               }elseif($currentPageType === 'tax' && $currentSingleType === 'product_cat' && isset($placement->product_categories) && is_array($placement->product_categories)){
                  if(isset($placement->product_categories[0]) && $placement->product_categories[0] === 'all'){
                     $fit_popups[] = $popupData;
                  }elseif($currentPageID && in_array( $currentPageID, $placement->product_categories)){
                     $fit_popups[] = $popupData;
                  }
               //IF is Custom Post Type
               }elseif(isset($placement->post_types) && is_array($placement->post_types) && count($placement->post_types) > 0 && is_singular($placement->post_types)){
                  $fit_popups[] = $popupData;
               }
         }elseif($placementType === 'custom' ){
            $current_page_link = (isset($_SERVER['HTTPS']) ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            //error_log( json_encode($current_page_link));
            if($current_page_link && $placement->urls && count($placement->urls) > 0){
               foreach($placement->urls as $key=>$urlItem) {
                  if(isset($urlItem->link) && ($current_page_link === $urlItem->link)){
                     $fit_popups[] = $popupData;
                  }
               }
            }
         }
      }

   }

   //If the Popup is Hidden from a certain page, do not show it.
   $filtered_popups = $fit_popups;
   if(is_singular() && get_the_ID()){
      $brave_hidden_popups = get_post_meta( get_the_ID(), 'brave_hidden_popups', true ) ? get_post_meta( get_the_ID(), 'brave_hidden_popups', true ) : array();
      if($filtered_popups && count($filtered_popups) > 0){
         foreach($filtered_popups as $key=>$popupData) {
            if(in_array( $popupData->id, $brave_hidden_popups )){
               unset($filtered_popups[$key]);
            }
         }
      }
   }
   //error_log(json_encode($filtered_popups));

   return $filtered_popups;
}

function bravepop_get_current_pageInfo(){
   
   $pageInfo = new stdClass();
   $currentPageType = '';
   $currentSingleID = '';
   $currentSingleType = '';

   global $wp_query;

   if ( $wp_query->is_page ) {
      if(is_front_page()){
         $currentPageType = 'front';
      }else{
         $currentPageType = 'single';
         if(isset($wp_query->post)){
            $currentSingleID = $wp_query->post->ID;
            $currentSingleType = $wp_query->post->post_type;
         }
      }
   } elseif ( $wp_query->is_home ) {
       $currentPageType = 'front';
   } elseif ( $wp_query->is_single ) {
      if(( $wp_query->is_attachment )){
         $currentPageType = 'attachment';
      }else{
         $currentPageType = 'single';
         if(isset($wp_query->post)){
            $currentSingleID = $wp_query->post->ID;
            $currentSingleType = $wp_query->post->post_type;
         }
      }

   } elseif ( $wp_query->is_category ) {
       $currentPageType = 'category';
       $currentSingleID = $wp_query->queried_object_id;
   } elseif ( $wp_query->is_tag ) {
       $currentPageType = 'tag';
   } elseif ( $wp_query->is_tax ) {
       $currentPageType = 'tax';
       if($wp_query->queried_object->taxonomy){
         $currentSingleType = $wp_query->queried_object->taxonomy;
       }
       if(isset($wp_query->queried_object->term_id)){
         $currentSingleID = $wp_query->queried_object->term_id;
       }

   } elseif ( $wp_query->is_archive ) {
      $currentPageType = 'archive';
      $currentSingleType = $wp_query->query['post_type'];
   } elseif ( $wp_query->is_search ) {
       $currentPageType = 'search';
   } elseif ( $wp_query->is_404 ) {
       $currentPageType = 'notfound';
   }

   $pageInfo->type = $currentPageType;
   $pageInfo->pageID = $currentSingleID;
   $pageInfo->singleType = $currentSingleType;
   
   return $pageInfo;
}