<?php
function bravepop_get_wpdata( $type ) {

   $wpData = new stdClass();

   if($type == 'all'){

       //GET ALL PAGES
       $pages = get_pages(); 
       $posts = get_posts(array('post_type' => 'post', 'numberposts' => 100));
       $categories = get_terms( 'category', 'orderby=name&order=ASC&hide_empty=0' ); 
       $tags = get_terms( 'post_tag', 'orderby=name&order=ASC&hide_empty=0' ); 
       $attachments = get_posts( array( 'post_type' => 'attachment', 'numberposts' => 100,'post_status' => null,'post_parent' => null) );
       $allPages = [];
       $allPosts = [];
       $allMedia = [];
       $allCategories = [];
       $allTags = [];
       $allPostTypes = [];

       foreach ( get_post_types( array('public'=> true), 'objects' ) as $post_type ) {
         if($post_type->name !== 'post' && $post_type->name !== 'page' && $post_type->name !== 'product' && $post_type->name !== 'popup' ){
            $postType = new stdClass();
            $postType->ID = $post_type->name;
            $postType->title = $post_type->label;
            $allPostTypes[] = $postType;
         }
      }
       
       foreach ( $pages as $page ) {
           $object = new stdClass();
           $object->ID = $page->ID ;
           $object->title = $page->post_title ;
           $object->link = esc_url(get_page_link( $page->ID )) ;
           $object->slug = $page->post_name ;
           $allPages[] = $object;
       }

       foreach ( $posts as $post ) {
           $object = new stdClass();
           $object->ID = $post->ID ;
           $object->title = $post->post_title ;
           $object->link = esc_url(get_permalink( $post->ID )) ;
           $object->slug = $post->post_name ;
           $allPosts[] = $object;
       }
       foreach ( $categories as $category) {
           $object = new stdClass();
           $object->ID = $category->term_id ;
           $object->title = $category->name ;
           $object->link = esc_url( get_term_link( $category ) ) ;
           $object->slug = $category->slug ;
           $allCategories[] = $object;
       }
       foreach ( $tags as $tag) {
           $object = new stdClass();
           $object->ID = $tag->term_id ;
           $object->title = $tag->name ;
           $object->link = esc_url( get_term_link( $tag ) ) ;
           $object->slug = $tag->slug ;
           $allTags[] = $object;
       }
       foreach ( $attachments as $attachment ) {
           $object = new stdClass();
           $object->ID = $attachment->ID ;
           $object->title = $attachment->post_title;
           $object->type = $attachment->post_mime_type;
           $object->image = esc_url($attachment->guid);
           $object->thumbnail = wp_get_attachment_thumb_url( $attachment->ID );
           $object->width = wp_get_attachment_metadata( $attachment->ID ) && wp_get_attachment_metadata( $attachment->ID )['width'] ? wp_get_attachment_metadata( $attachment->ID )['width'] : '';
           $object->height = wp_get_attachment_metadata( $attachment->ID ) && wp_get_attachment_metadata( $attachment->ID )['height'] ? wp_get_attachment_metadata( $attachment->ID )['height'] : '';
           $object->last_modified = $attachment->post_modified;
           $allMedia[] = $object;
       }


       $wpData->pages = $allPages;
       $wpData->posts = $allPosts;
       $wpData->categories = $allCategories;
       $wpData->tags = $allTags;
       $wpData->media = $allMedia;
       $wpData->post_types = $allPostTypes;

       //PRODUCTS
       if ( BRAVEPOP_WOO_ACTIVE) {

           $products = wc_get_products( array( 'numberposts' => -1));
           $productCategories = get_terms( 'product_cat', 'orderby=name&order=ASC&hide_empty=0' ); 
           $productTags = get_terms( 'product_tag', 'orderby=name&order=ASC&hide_empty=0' ); 
           $allProducts = [];
           $allProductCategories = [];
           $allProductTags = [];

           foreach ( $products as $product ) {
               $object = new stdClass();
               $object->ID = $product->get_id() ;
               $object->title = $product->get_name();
               $object->link = get_permalink( $product->get_id() );
               $object->price = $product->get_price();
               $allProducts[] = $object;
           }
           foreach ( $productCategories as $productCat) {
               $object = new stdClass();
               $object->ID = $productCat->term_id ;
               $object->title = $productCat->name ;
               $object->link = esc_url( get_term_link( $productCat ) ) ;
               $object->slug = $productCat->slug ;
               $allProductCategories[] = $object;
           }
           foreach ( $productTags as $productTag) {
               $object = new stdClass();
               $object->ID = $productTag->term_id ;
               $object->title = $productTag->name ;
               $object->link = esc_url( get_term_link( $productTag ) ) ;
               $object->slug = $productTag->slug ;
               $allProductTags[] = $object;
           }

           $wpData->products = $allProducts;
           $wpData->product_categories = $allProductCategories;
           $wpData->product_tags = $allProductTags;

       }

       //error_log(json_encode($wpData));
       
   }

   if($type == 'media'){
       $allMedia = [];
       $attachments = get_posts( array( 'post_type' => 'attachment', 'numberposts' => -1,'post_status' => null,'post_parent' => null) );
       foreach ( $attachments as $attachment ) {
           $object = new stdClass();
           $object->ID = $attachment->ID ;
           $object->title = $attachment->post_title;
           $object->type = $attachment->post_mime_type;
           $object->image = esc_url($attachment->guid);
           $object->thumbnail = wp_get_attachment_thumb_url( $attachment->ID );
           $object->width = wp_get_attachment_metadata( $attachment->ID ) && wp_get_attachment_metadata( $attachment->ID )['width'] ? wp_get_attachment_metadata( $attachment->ID )['width'] : '';
           $object->height = wp_get_attachment_metadata( $attachment->ID ) && wp_get_attachment_metadata( $attachment->ID )['height'] ? wp_get_attachment_metadata( $attachment->ID )['height'] : '';
           $object->last_modified = $attachment->post_modified;
           $allMedia[] = $object;
       }
       $wpData->media = $allMedia;
   }

   return $wpData;
}


function bravepop_get_wpPosts( $type='', $postType='', $filterType='', $count=3, $categories='', $tags='', $postIDs='', $postID='' ) {

   $allPosts= [];

   $wpData = new stdClass();
   $args = array( 'post_type' => 'post', 'numberposts' => $count) ;

   if($type === 'popular'){
       $args = array( 'post_type' => 'post', 'numberposts' => $count, 'orderby' => 'comment_count');
   }
   if($type === 'related'){
       $args = array( 'post_type' => 'post', 'numberposts' => $count);
   }

   if($type === 'multiple'){
       if($filterType === 'categories' && is_array($categories)){
           $args = array( 'post_type' => 'post', 'numberposts' => $count, 'category__in' => $categories );
       }else if($filterType === 'tags' && is_array($tags)){
           $args = array( 'post_type' => 'post', 'numberposts' => $count, 'tag__in' => $tags );
       }else if($filterType === 'custom' && is_array($postIDs)){
           $args = array( 'post_type' => 'post', 'numberposts' => 99, 'post__in' => $postIDs );
       }
   }
   if($postType === 'post'){
       $args = array( 'post_type' => 'post', 'post__in' => $postID);
   }
   if($postType === 'page'){
       $args = array( 'post_type' => 'page', 'post__in' => $postID );
   }
   //error_log(json_encode( $args ));
   //error_log(json_encode(get_posts( $args )));

   $posts = get_posts( $args );
   foreach ( $posts as $post ) {
       $object = new stdClass();

       $theContent = '';
       if ($postType) {
           $blocks = parse_blocks( $post->post_content );
           foreach ($blocks as $block) {
               //error_log(json_encode($block));
               if ($block['blockName']) {
                   $theContent .= $block['innerHTML'];
               }
           }
           //error_log(json_encode($theContent));
       }
       
       $object->ID = $post->ID ;
       $object->title = $post->post_title ;
       $object->date = $post->post_date ;
       $object->link = esc_url(get_permalink( $post->ID )) ;
       $object->slug = $post->post_name ;
       $object->contentHTML = $theContent;
       $object->content = $postType ? parse_blocks($post->post_content) : '' ;
       $object->excerpt = get_the_excerpt($post->ID);
       $object->image =  bravepop_prepareImageData($post->ID);
       $object->categories = get_the_category($post->ID);
       $object->tags = get_the_tags($post->ID);
       $allPosts[] = $object;
   }

   $wpData->posts = $allPosts;
   return new WP_REST_Response($wpData);

}

function bravepop_prepareImageData( $postID ){
   $object = new stdClass();
   $imgDataArray = wp_get_attachment_image_src( get_post_thumbnail_id( $postID ), 'large', false );
   $object->url = $imgDataArray[0] ? $imgDataArray[0] : '';
   $object->width = $imgDataArray[1] ? $imgDataArray[1] : '';
   $object->height = $imgDataArray[2] ? $imgDataArray[2] : '';
   return $object;
}