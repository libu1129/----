<?php
/**
 * Popup Element.
 * Renders the Popup
 */


if ( ! class_exists( 'BravePop_Popup' ) ) {
    class BravePop_Popup {

         public $elementStyles; public $elementScripts;

        function __construct($popupID=null, $forceLoad=false, $forceStep=false, $customContent=false, $popupType='popup') {
               $this->popupID = $popupID;
               $this->popupType = $popupType;
               if($customContent === false && !get_post_meta($popupID, 'popup_data', true)) { return; }
               $this->forceLoad = $forceLoad;
               $this->forceStep = $forceStep;
               $this->elementStyles = array();
               $this->elementScripts = array();
               $this->popupData = $customContent ? json_decode($customContent) : json_decode(get_post_meta($popupID, 'popup_data', true));
               $this->popupfonts = array();
               $this->closeData = array();
               $preparedAnimation = bravepop_prepare_animation($this->popupData);
               $this->animationData = $preparedAnimation['animationData'];
               $this->advancedAnimation = $preparedAnimation['advancedAnimation'];
               $this->hasAnimation = $preparedAnimation['hasAnimation'];
               $this->hasContAnim = $preparedAnimation['hasContAnim'];
               $this->videoData = array();
               $this->hasVideo = false;
               $this->hasVimeo = false;
               $this->hasYoutube = false;
               $this->hasLoginElement = false;
               $this->hasWpPosts = false;
               $this->hasWpProducts = false;
               $this->userTypeMatch = true;
               $this->refererMatch = true;
               $this->languageMatch = true;
               $this->hasCartItems = true;
               $this->cartFilterMatch = array(true,true,true,true,true);
               $this->hasDesktopEmbed = false;
               $this->hasMobileEmbed = false;
               $this->dynamicData = $this->popup_get_dynamicData();
               $this->popup_visibility_filter();
               $this->popupData->settings = isset($this->popupData->settings) ? $this->popupData->settings : new stdClass();

               //If its a Child Popup, use the parent popups' settings except trigger and frequency settings
               $parentPopupID = json_decode(get_post_meta($popupID, 'popup_parentID', true));
               if($parentPopupID){
                  $parentPopupData =  json_decode(get_post_meta($parentPopupID, 'popup_data', true));
                  if($parentPopupData && $parentPopupData->settings){
                     $this->popupData->settings->goal = $parentPopupData->settings->goal;
                     $this->popupData->settings->goalAction = $parentPopupData->settings->goalAction;
                     $this->popupData->settings->audience = $parentPopupData->settings->audience;
                     $this->popupData->settings->placement = $parentPopupData->settings->placement;
                  }
               }

               //error_log('$popupType: '. $popupType); 
               if($this->popupID && $this->popupData) {
                  if($this->userTypeMatch && $this->refererMatch && $this->languageMatch && $this->hasCartItems && !in_array(false, $this->cartFilterMatch)) {
                     add_action('wp_footer', array( $this, 'popup_render' ), 5 );
                     add_action('wp_footer', array( $this, 'popup_inline_script' ), 12); //must render after popup_render
                     add_action('wp_footer', array( $this, 'popup_external_scripts' ) );
                     add_action('wp_footer', array( $this, 'popup_embedlock_script' ), 65 );
                  }
               }

               //trace();
         }


      public function popup_visibility_filter() {
         $userTypeMatch = true; $refererMatch = true;
         $brave_preview = filter_input(INPUT_GET, 'brave_popup');

         // User Type Filter
         if(isset($this->popupData->settings->audience->userType) && $this->popupData->settings->audience->userType === 'guest' && is_user_logged_in()){
            $userTypeMatch  = false;
         }
         if(isset($this->popupData->settings->audience->userType) && $this->popupData->settings->audience->userType === 'registered' && !is_user_logged_in()){
            $userTypeMatch  = false;
         }
         if(isset($this->popupData->settings->audience->userType) && $this->popupData->settings->audience->userType === 'admin' && !current_user_can('activate_plugins')){
            $userTypeMatch  = false;
         }

         $this->userTypeMatch = $userTypeMatch;

         //Referer match
         if(isset($this->popupData->settings->audience->referrals) && count($this->popupData->settings->audience->referrals) > 0){
            foreach ($this->popupData->settings->audience->referrals as $key => $referal) {
               //error_log('$refererMatch: '.wp_get_referer().'----'.$referal->link.'----'.json_encode(strpos(wp_get_referer(), $referal->link)));
               if(isset($referal->link) && strpos(wp_get_referer(), $referal->link) === false){
                  $refererMatch = false;
               }else{
                  $refererMatch = true;
               }
            }
         }

         $this->refererMatch = $refererMatch;

         //Woocommerce Filters Match
         $wooFilters = isset($this->popupData->settings->woocommerce) ? $this->popupData->settings->woocommerce : false;
         if(!empty($this->popupData->settings->woocommerce->has_cart_item) && function_exists('bravepop_woo_filter')){
            $cartFileterd = bravepop_woo_filter($wooFilters);
            $totalCartItems = isset($GLOBALS['bravepop_cart_data']->count) ? (int)$GLOBALS['bravepop_cart_data']->count : 0;
            if(($totalCartItems === 0)){
               $this->hasCartItems = false;
            }
            if( ($totalCartItems > 0) && !empty($wooFilters->cart_includes) || !empty($wooFilters->cart_excludes) || !empty($wooFilters->cart_value_more) || !empty($wooFilters->cart_value_less) ){
               $this->cartFilterMatch[0] =  $cartFileterd->cart_includes;
               $this->cartFilterMatch[1] =  $cartFileterd->cart_excludes;
               
            }
         }

         //Language Filter
         if(isset($this->popupData->settings->filters->language) && function_exists('pll_current_language')){
            if($this->popupData->settings->filters->language === pll_current_language()){
               $this->languageMatch  = true;
            }else{
               $this->languageMatch  = false;
            }
         }
         if(isset($this->popupData->settings->filters->language) && class_exists( 'SitePress' )){
            $currentLang = apply_filters( 'wpml_current_language', NULL );
            if($this->popupData->settings->filters->language === $currentLang ){
               $this->languageMatch  = true;
            }else{
               $this->languageMatch  = false;
            }
         }

         //If is Previewing, skip all Filtering
         if(!empty($brave_preview)){
            $this->userTypeMatch  = true; $this->refererMatch = true;
         }

      }

      
      public function popup_external_scripts() {
         if($this->advancedAnimation && $this->hasAnimation) {
            wp_enqueue_script( 'bravepop_animejs', BRAVEPOP_PLUGIN_PATH . 'assets/frontend/anime.min.js' ,'','',true);
            wp_enqueue_script( 'bravepop_animation', BRAVEPOP_PLUGIN_PATH . 'assets/frontend/animate.js' ,'','',true);
         }
         if($this->hasLoginElement){
            wp_enqueue_script( 'bravepop_loginjs', BRAVEPOP_PLUGIN_PATH . 'assets/frontend/login.js' ,'','',true);
            wp_enqueue_style('bravepop_login_element',  BRAVEPOP_PLUGIN_PATH . 'assets/css/wp_login.min.css' );
         }
         if($this->hasWpPosts){
            wp_enqueue_style('bravepop_posts_element',  BRAVEPOP_PLUGIN_PATH . 'assets/css/wp_posts.min.css');
         }
         if($this->hasWpProducts){
            wp_enqueue_style('bravepop_woocommerce_element',  BRAVEPOP_PLUGIN_PATH . 'assets/css/woocommerce.min.css');
         }
         if($this->hasDesktopEmbed || $this->hasMobileEmbed){
            wp_enqueue_script( 'bravepop_embedlock', BRAVEPOP_PLUGIN_PATH . 'assets/frontend/embedlock.js' ,'','',true);
         }

      }

      public function popup_embedlock_script() { ?>
         <script>
            <?php if($this->hasDesktopEmbed || $this->hasMobileEmbed){ ?>
               brave_lockContent(<?php print_r(absint($this->popupID));?>);
            <?php } ?>
         </script>
      <?php }


      public function popup_inline_script() {  ?>

            <style type='text/css'>
               <?php
                  //Popup Styles
                  if($this->popupData->steps){
                     foreach ($this->popupData->steps as $key => $step) {
                        if($step->desktop){
                           print_r($this->popup_generate_styles($step->desktop, absint($key), 'desktop'));
                        } 
                        if($step->mobile){
                           print_r($this->popup_generate_styles($step->mobile, absint($key), 'mobile'));
                        } 
                     }
                  }

                  //Element Styles
                  $elementStyles = implode('', $this->elementStyles);
                  print_r($elementStyles);

                  //---END----//
               ?>
            </style>

            <script>
                  <?php   
                     //Remove Sensetive Data       
                     $settingsData = $this->popupData->settings;
                     if(isset($settingsData->notification->zapierURL)){  unset($settingsData->notification->zapierURL);   }
                     if(isset($settingsData->notification->emailAddresses)){  unset($settingsData->notification->emailAddresses);   }

                     //Set Default GoalAction Data
                     if(!isset($settingsData->goalAction)){ 
                        $settingsData->goalAction = new stdClass(); 
                        $settingsData->goalAction->type = 'step';
                        $settingsData->goalAction->step = 0;
                     }
                  ?>

                  brave_popup_data[<?php print_r(absint($this->popupID));?>] = {
                     title: '<?php print_r(esc_attr(get_the_title(absint($this->popupID)))); ?>',
                     fonts: <?php print_r(json_encode($this->popupfonts)); ?>,
                     advancedAnimation:<?php print_r(json_encode($this->advancedAnimation));?>,
                     hasAnimation: <?php print_r(json_encode($this->hasAnimation)); ?>,
                     hasContAnim:  <?php print_r(json_encode($this->hasContAnim)); ?>,
                     animationData: <?php print_r(json_encode($this->animationData)); ?>,
                     videoData: <?php print_r(json_encode($this->videoData)); ?>,
                     hasYoutube: <?php print_r(json_encode($this->hasYoutube)); ?>,
                     hasVimeo: <?php print_r(json_encode($this->hasVimeo)); ?>,
                     settings: <?php print_r(json_encode($settingsData)); ?>,
                     close: <?php print_r(json_encode($this->closeData)); ?>,
                     forceLoad: <?php print_r(json_encode($this->forceLoad)); ?>,
                     forceStep: <?php print_r(json_encode($this->forceStep)); ?>,
                     hasDesktopEmbed: <?php print_r(json_encode($this->hasDesktopEmbed)); ?>,
                     hasMobileEmbed: <?php print_r(json_encode($this->hasMobileEmbed)); ?>,
                     schedule:<?php $popup_schedule = get_post_meta( $this->popupID, 'popup_schedule', true ); print_r($popup_schedule ? $popup_schedule : '{}');?>,
                     embedLock: false,
                     timers: [],
                  }



                  document.addEventListener("DOMContentLoaded", function(event) {
                     brave_init_popup(<?php print_r(absint($this->popupID));?>, brave_popup_data[<?php print_r(absint($this->popupID));?>]);
                  });

                  <?php 
                     $elementScripts = implode('', $this->elementScripts);
                     print_r(esc_js($elementScripts)); 
                  ?>

            </script>

         <?php
      }


      public function popup_get_element_fonts($element) { 
         $systemfonts = array('Arial', 'Arial Black', 'Cursive', 'Impact', 'Lucida Grande', 'Geneva', 'Verdana', 'Courier', 'Georgia', 'Palatino Linotype', 'None');
         if(isset($element->fontFamily) && !in_array( $element->fontFamily, $this->popupfonts) && !in_array($element->fontFamily, $systemfonts)){
            $this->popupfonts[] = $element->fontFamily;
         }
         if(isset($element->titlefontFamily) && !in_array( $element->titlefontFamily, $this->popupfonts) && !in_array($element->titlefontFamily, $systemfonts)){
            $this->popupfonts[] = $element->titlefontFamily;
         }
         if(isset($element->type) && $element->type === 'form' && isset($element->formData->settings->style->fontFamily) && !in_array( $element->formData->settings->style->fontFamily, $this->popupfonts) && !in_array($element->formData->settings->style->fontFamily, $systemfonts)){
            $this->popupfonts[] = $element->formData->settings->style->fontFamily;
         }
         if(isset($element->type) && $element->type === 'form' && isset($element->formData->settings->button->fontFamily) && !in_array( $element->formData->settings->button->fontFamily, $this->popupfonts) && !in_array($element->formData->settings->button->fontFamily, $systemfonts)){
            $this->popupfonts[] = $element->formData->settings->button->fontFamily;
         }
      }

      public function popup_get_dynamicData() {
         $hasDynamicData = false;
         if($this->popupData->steps){
            foreach ($this->popupData->steps as $key => $step) {
               $mobileElements = $step->mobile->content;
               $desktopElements = $step->desktop->content;
               $allElements = array_merge($desktopElements,$mobileElements);
               foreach ($allElements as $key => $element) {
                  if(!$hasDynamicData){
                     if(!empty($element->dynamic) || !empty($element->action->actionData->dynamicURL) ){
                        $hasDynamicData = true;
                     }
                  }
               }
            }
         }
         if($hasDynamicData){
            if(!isset($GLOBALS['bravepop_dynamic_data'])){
               $dynamicData = bravepop_dynamic_data();
               $GLOBALS['bravepop_dynamic_data'] = $dynamicData;
               return $dynamicData;
            }else{
               return $GLOBALS['bravepop_dynamic_data'];
            }
         }else{
            return false;
         }
      }

       
      public function popup_renderCSSClasses($stepSettings) { 
         if(!$stepSettings){ return ''; }
         $noContent = isset($stepSettings->content) && is_array($stepSettings->content) && count($stepSettings->content) === 0 ? 'brave_popup__step--noContent' : '' ;
         $position = isset($stepSettings->position) ? esc_attr($stepSettings->position) : 'center';
         $layout = isset($stepSettings->layout) ? esc_attr($stepSettings->layout) : 'boxed';
         $closeIcon = isset($stepSettings->closeButton) ? esc_attr($stepSettings->closeButton) : 'icon';
         $closeButtonPosition = isset($stepSettings->closeButtonPosition) ? esc_attr($stepSettings->closeButtonPosition) : 'inside_right';
         if($this->popupType === 'landing'){$position = 'top_center';}

         $classes = [];

         $classes[] = $noContent;
         $classes[] = 'position_'.$position;
         $classes[] = 'closeButton_'.$closeIcon;
         $classes[] = 'brave_popup__step--'.$layout;
         $classes[] = 'closeButtonPosition_'. $closeButtonPosition; 
         if(isset($stepSettings->fullWidth) && $stepSettings->fullWidth === true) {  $classes[] = 'brave_popup_fullWidth';  }
         if(isset($stepSettings->fullHeight) && $stepSettings->fullHeight === true) {  $classes[] = 'brave_popup_fullHeight';  }
         if(isset($stepSettings->overlay) && $stepSettings->overlay === false){}else{ $classes[] = 'has_overlay'; }
         if(isset($stepSettings->overlayClose) && $stepSettings->overlayClose === true){ $classes[] = 'has_overlay_close'; }
         if(isset($stepSettings->autoClose) && $stepSettings->autoClose === true){ $classes[] = 'has_autoClose'; }
         if((isset($stepSettings->scrollbar) && $stepSettings->scrollbar === true) || $this->popupType ==='landing') {  $classes[] = 'brave_popup_show_scrollbar';  }
         
         //return json_encode($stepSettings);
         return implode(" ",$classes);
      } 

      public function popup_renderCloseButton($stepSettings, $stepIndex, $position='above') { 
         if(!$stepSettings || (isset($stepSettings->closeButton) && ($stepSettings->closeButton === 'none'))){ return ''; }
         $closeStep = isset($stepSettings->closeStep) && $stepSettings->closeStep !== 'none' ? $stepSettings->closeStep : json_encode(false);
         $closeButtonPosition = isset($stepSettings->closeButtonPosition) ? $stepSettings->closeButtonPosition : 'inside_right';
         $closeButtonType = isset($stepSettings->closeButton) && $stepSettings->closeButton === 'text' ? 'brave_popup__close--text' :'brave_popup__close--icon';
         $closeposition = 'brave_popup__close--'.$closeButtonPosition.'';
         $closeButtonText = isset($stepSettings->closeButtonText) ? $stepSettings->closeButtonText : 'Yes, Hide this Popup.';
         $closeIconSVG = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><rect x="0" fill="none" width="20" height="20"/><g><path d="M14.95 6.46L11.41 10l3.54 3.54-1.41 1.41L10 11.42l-3.53 3.53-1.42-1.42L8.58 10 5.05 6.47l1.42-1.42L10 8.58l3.54-3.53z"/></g></svg>';
         $closeText = isset($stepSettings->closeButton)  &&  $stepSettings->closeButton === 'text' ? '<span class="brave_popup__close__customtext">'.$closeButtonText.'</span>' : $closeIconSVG;
         
         return '<div class="brave_popup__close '.$closeposition.' '.$closeButtonType.'" onclick="brave_close_popup(\''.$this->popupID.'\', '.$stepIndex.', '.$closeStep.')">'.$closeText.'</div>';
         
      } 

      public function popup_renderOverlay($stepSettings, $stepIndex) { 
         if(!$stepSettings || (isset($stepSettings->overlay) && ($stepSettings->overlay === false))){ return ''; }
         $overlayClose = !empty($stepSettings->overlayClose) ? 'onclick="brave_close_popup(\''.$this->popupID.'\', '.$stepIndex.')"' : '';
         $bgImage = '';
         if(isset($stepSettings->overlayImage) && isset($stepSettings->overlayImage->image)){
            $bgImage = '<img class="brave_popup__step__overlay__img" src="'.$stepSettings->overlayImage->image.'" alt="Overlay Image" />';
         }
         return '<div class="brave_popup__step__overlay '.($overlayClose ? 'brave_popup__step__overlay--closable' : '').'" '.$overlayClose.'>'.$bgImage.'</div>';
      } 

      public function popup_renderElements($stepSettings, $stepIndex, $device) { 
         if(!$stepSettings){ return ''; }

         $goalElementIDs = isset($this->popupData->settings->goalAction->elementIDs->$device) ?  explode(",",$this->popupData->settings->goalAction->elementIDs->$device)  : array();

         $elements = '<div class="brave_popup__step__content">';
            $elements .= '<div class="brave_popup__step__elements">';
               if(isset($stepSettings->content)){
                  foreach ($stepSettings->content as $index => $element) {
                     if(isset($element->type) && (!isset($element->hidden) || $element->hidden === false)){
                        switch ($element->type) {
                           case 'text':
                              $elmCalss = new BravePop_Element_Text($element, $this->popupID, $stepIndex, $index, $device, in_array($element->id, $goalElementIDs ), $this->dynamicData);
                              $elements .= $elmCalss->render();
                              $elmStyle = $elmCalss->render_css();
                              $this->elementStyles[$element->id] = $elmStyle;
                              $this->popup_get_element_fonts($element);

                              break;
                           case 'button':
                              $elmCalss = new BravePop_Element_Button($element, $this->popupID, $stepIndex, $index, $device, in_array($element->id, $goalElementIDs ), $this->dynamicData);
                              $elements .= $elmCalss->render();
                              $elmStyle = $elmCalss->render_css();
                              $this->elementStyles[$element->id] = $elmStyle;
                              $this->popup_get_element_fonts($element);
                              break;
                           case 'shape':
                              $elmCalss = new BravePop_Element_Shape($element, $this->popupID, $stepIndex, $index, $device, in_array($element->id, $goalElementIDs ), $this->dynamicData);
                              $elements .= $elmCalss->render();
                              $elmStyle = $elmCalss->render_css();
                              $this->elementStyles[$element->id] = $elmStyle;
                              break;
                           case 'image':
                              $elmCalss = new BravePop_Element_Image($element, $this->popupID, $stepIndex, $index, $device, in_array($element->id, $goalElementIDs ), $this->dynamicData);
                              $elements .= $elmCalss->render();
                              $elmStyle = $elmCalss->render_css();
                              $this->elementStyles[$element->id] = $elmStyle;
                              break;
                           case 'sticker':
                              if (class_exists('BravePop_Element_Sticker')) {
                                 $elmCalss = new BravePop_Element_Sticker($element, $this->popupID, $stepIndex, $index, $device, in_array($element->id, $goalElementIDs ), $this->dynamicData);
                                 $elements .= $elmCalss->render();
                                 $elmStyle = $elmCalss->render_css();
                                 $this->elementStyles[$element->id] = $elmStyle;
                              }
                              break;
                           case 'list':
                              $elmCalss = new BravePop_Element_List($element, $this->popupID, $stepIndex, $index, $device);
                              $elements .= $elmCalss->render();
                              $elmStyle = $elmCalss->render_css();
                              $this->elementStyles[$element->id] = $elmStyle;
                              $this->popup_get_element_fonts($element);
                              break;
                           case 'social':
                              if (class_exists('BravePop_Element_Social')) {
                                 $elmCalss = new BravePop_Element_Social($element, $this->popupID, $stepIndex, $index);
                                 $elements .= $elmCalss->render();
                                 $elmStyle = $elmCalss->render_css();
                                 $this->elementStyles[$element->id] = $elmStyle;
                              }
                              break;
                           case 'video':
                              if (class_exists('BravePop_Element_Video')) {
                                 $elmCalss = new BravePop_Element_Video($element, $this->popupID, $stepIndex, $index, $device);
                                 $elements .= $elmCalss->render();
                                 $elmStyle = $elmCalss->render_css();
                                 $elmJavascript = $elmCalss->render_js();
                                 $this->elementStyles[$element->id] = $elmStyle;
                                 $this->elementScripts[$element->id] = $elmJavascript;
                                 $this->videoData[$stepIndex] = new stdClass();
                                 $this->videoData[$stepIndex]->$device = $element;
                                 $videoType = isset($element->videoType) ? $element->videoType : 'youtube';
                                 
                                 if($this->hasVideo === false){  $this->hasVideo = true; }
                                 if($this->hasYoutube === false && $videoType === 'youtube'){  $this->hasYoutube = true; }
                                 if($this->hasVimeo === false && $videoType === 'vimeo'){ $this->hasVimeo = true; }
                              }
                              break;
                           case 'dynamic':
                              if (class_exists('BravePop_Element_Dynamic')) {
                                 $elmCalss = new BravePop_Element_Dynamic($element, $this->popupID, $stepIndex, $index, $device);
                                 $elements .= $elmCalss->render();
                                 $elmStyle = $elmCalss->render_css();
                                 $elmJavascript = $elmCalss->render_js();
                                 $this->elementStyles[$element->id] = $elmStyle;
                                 $this->elementScripts[$element->id] = $elmJavascript;
                                 $this->popup_get_element_fonts($element);
                              }
                              break;
                           case 'countdown':
                              if (class_exists('BravePop_Element_Countdown')) {
                                 $elmCalss = new BravePop_Element_Countdown($element, $this->popupID, $stepIndex, $index, $device);
                                 $elements .= $elmCalss->render();
                                 $elmStyle = $elmCalss->render_css();
                                 $elmJavascript = $elmCalss->render_js();
                                 $this->elementStyles[$element->id] = $elmStyle;
                                 $this->elementScripts[$element->id] = $elmJavascript;
                                 $this->popup_get_element_fonts($element);
                              }
                              break;
                           case 'form':
                              $elmCalss = new BravePop_Element_Form($element, $this->popupID, $stepIndex, $index, $device, in_array($element->id, $goalElementIDs ));
                              $elements .= $elmCalss->render();
                              $elmStyle = $elmCalss->render_css();
                              $elmJavascript = $elmCalss->render_js();
                              $this->elementStyles[$element->id] = $elmStyle;
                              $this->elementScripts[$element->id] = $elmJavascript;
                              $this->popup_get_element_fonts($element);
                              break;
                           case 'search':
                              if (class_exists('BravePop_Element_Search')) {
                                 $elmCalss = new BravePop_Element_Search($element, $this->popupID, $stepIndex, $index, $device);
                                 $elements .= $elmCalss->render();
                                 $elmStyle = $elmCalss->render_css();
                                 $this->elementStyles[$element->id] = $elmStyle;
                                 $this->popup_get_element_fonts($element);
                              }
                              break;
                           case 'single':
                              $elmCalss = new BravePop_Element_Single($element, $this->popupID, $stepIndex, $index, $device);
                              $elements .= $elmCalss->render();
                              $elmStyle = $elmCalss->render_css();
                              $this->elementStyles[$element->id] = $elmStyle;
                              $this->popup_get_element_fonts($element);
                              break;
                           case 'product':
                              if (class_exists('BravePop_Element_Product') && BRAVEPOP_WOO_ACTIVE ) {
                                 $elmCalss = new BravePop_Element_Product($element, $this->popupID, $stepIndex, $index, $device);
                                 $elements .= $elmCalss->render();
                                 $elmStyle = $elmCalss->render_css();
                                 $this->elementStyles[$element->id] = $elmStyle;
                                 $this->popup_get_element_fonts($element);
                              }
                              break;
                           case 'posts':
                              if (class_exists('BravePop_Element_Posts')) {
                                 $elmCalss = new BravePop_Element_Posts($element, $this->popupID, $stepIndex, $index, $device, in_array($element->id, $goalElementIDs ));
                                 $elements .= $elmCalss->render();
                                 $elmStyle = $elmCalss->render_css();
                                 $elmJavascript = $elmCalss->render_js();
                                 $this->elementScripts[$element->id] = $elmJavascript;
                                 $this->elementStyles[$element->id] = $elmStyle;
                                 $this->popup_get_element_fonts($element);
                                 $this->hasWpPosts = true;
                              }
                              break;
                           case 'products':
                              if ( BRAVEPOP_WOO_ACTIVE ) {
                                 $elmCalss = new BravePop_Element_Products($element, $this->popupID, $stepIndex, $index, $device, in_array($element->id, $goalElementIDs ));
                                 $elements .= $elmCalss->render();
                                 $elmStyle = $elmCalss->render_css();
                                 $elmJavascript = $elmCalss->render_js();
                                 $this->elementScripts[$element->id] = $elmJavascript;
                                 $this->elementStyles[$element->id] = $elmStyle;
                                 $this->popup_get_element_fonts($element);
                                 $this->hasWpProducts = true;
                              }
                              break;
                           case 'login':
                              if (class_exists('BravePop_Element_Login')) {
                                 $elmCalss = new BravePop_Element_Login($element, $this->popupID, $stepIndex, $index, $device);
                                 $elements .= $elmCalss->render();
                                 $elmStyle = $elmCalss->render_css();
                                 $this->elementStyles[$element->id] = $elmStyle;
                                 $this->popup_get_element_fonts($element);
                                 $this->hasLoginElement = true;
                              }
                              break;
                           case 'code':
                              $elmCalss = new BravePop_Element_Code($element, $this->popupID, $stepIndex, $index, $device, in_array($element->id, $goalElementIDs ));
                              $elements .= $elmCalss->render();
                              $elmStyle = $elmCalss->render_css();
                              $this->elementStyles[$element->id] = $elmStyle;
                              break;
                           default:
                             // $elmCalss = '';
                              break;
                        }
                     }
                     
                  }
               }
            $elements .= '</div>';
            $elements .= isset($stepSettings->backgroundImage->image) && $stepSettings->backgroundImage->image ? '<div class="brave_popup__step__content__overlay"></div>' : '';
         $elements .= '</div>';
         return $elements;

      } 


      public function popup_renderPopup($stepSettings, $stepIndex, $device) { 
         if(!$stepSettings){ return ''; }
         $closeButtonAbove = ''; $closeButtonBelow = ''; $closeButtonTop = '';
         $closeButtonPosition = isset($stepSettings->closeButtonPosition) ? $stepSettings->closeButtonPosition : 'inside_right';
         if(!isset($this->closeData[$stepIndex])){ $this->closeData[$stepIndex] = new stdClass(); }
         if(!isset($this->closeData[$stepIndex]->$device)){ $this->closeData[$stepIndex]->$device = new stdClass(); }
         if(isset($stepSettings->autoClose) && $stepSettings->autoClose){
            $this->closeData[$stepIndex]->$device->autoClose = true;
            $this->closeData[$stepIndex]->$device->autoCloseDuration = isset($stepSettings->autoCloseDuration) ? $stepSettings->autoCloseDuration : 10;
         }

         if($closeButtonPosition !== 'below_right' && $closeButtonPosition !== 'below_left' && $closeButtonPosition !== 'below_center' && $closeButtonPosition !== 'top_left' && $closeButtonPosition !== 'top_right'){
            $closeButtonAbove = '<div class="brave_popup__step__close">'.$this->popup_renderCloseButton($stepSettings, $stepIndex, 'above').'</div>';
         }
         if($closeButtonPosition === 'below_right' || $closeButtonPosition === 'below_left' || $closeButtonPosition === 'below_center'){
            $closeButtonBelow = '<div class="brave_popup__step__close">'.$this->popup_renderCloseButton($stepSettings, $stepIndex, 'below').'</div>';
         }
         if($closeButtonPosition === 'top_left' || $closeButtonPosition === 'top_right'){
            $closeButtonTop = '<div class="brave_popup__step__close">'.$this->popup_renderCloseButton($stepSettings, $stepIndex, 'top').'</div>';
         }

         return $closeButtonTop .
                  '<div class="brave_popup__step__inner">
                     <div class="brave_popupSections__wrap">
                        <div class="brave_popupMargin__wrap">
                           '.$closeButtonAbove .'
                           <div class="brave_popup__step__popup">'.$this->popup_renderElements($stepSettings, $stepIndex, $device).'</div>
                           '.$closeButtonBelow .'
                        </div>
                     </div>
                  </div>';

      } 

      public function popup_render() {  ?>
         <?php $exitAnim = ''; ?>
            <div class="brave_popup" id="brave_popup_<?php print_r(esc_attr($this->popupID)); ?>" data-loaded="false" >
               <!-- <p><?php //print_r($this->popupData); ?></p> -->
               <?php 
                  if($this->popupData->steps){
                     foreach ($this->popupData->steps as $key => $step) {
                        $mobilenoContent = isset($step->mobile->content) && is_array($step->mobile->content) && count($step->mobile->content) === 0 ? true : false ;
                        $mobilenoContentClass = $mobilenoContent ? 'brave_popup__step--mobile-noContent' : 'brave_popup__step--mobile-hasContent' ;
                        $desktopExitAnim =  isset($step->desktop->animation->exit->preset) && $step->desktop->animation->exit->preset !== 'none' ? true  : false;
                        $desktopExitAnimType =  $desktopExitAnim && isset($step->desktop->animation->exit->preset) ? 'data-exitanimtype="'.esc_attr($step->desktop->animation->exit->preset).'"' : '';
                        $desktopExitAnimDuration = $exitAnim && $desktopExitAnimType && isset($step->desktop->animation->exit->duration) ? 'data-exitanimlength="'.esc_attr((Int)$step->desktop->animation->exit->duration/1000).'"' : 'data-exitanimlength="'.(0.5).'"';
                        $dekstopPopupLayout = isset($step->desktop->layout) ? 'data-layout="'.esc_attr($step->desktop->layout).'"' : 'data-layout="boxed"';
                        $dekstopPopupPosition = isset($step->desktop->position) ? 'data-position="'.esc_attr($step->desktop->position).'"' : 'data-position="center"';
                        $noMobileContentData = $mobilenoContent ? 'data-nomobilecontent="true"' : 'data-nomobilecontent="false"';
                        $mobileExitAnim =  isset($step->mobile->animation->exit->preset) && $step->mobile->animation->exit->preset !== 'none' ? true  : false;
                        $mobileExitAnimType =  $mobileExitAnim && isset($step->mobile->animation->exit->preset) ? 'data-exitanimtype="'.$step->mobile->animation->exit->preset.'"' : '';
                        $mobileExitAnimDuration = $exitAnim && $mobileExitAnimType && isset($step->mobile->animation->exit->duration) ? 'data-exitanimlength="'.esc_attr((Int)$step->mobile->animation->exit->duration/1000).'"' : 'data-exitanimlength="'.(0.5).'"';
                        $mobilePopupLayout = isset($step->mobile->layout) ? 'data-layout="'.esc_attr($step->mobile->layout).'"' : 'data-layout="boxed"';
                        $mobilePopupPosition = isset($step->mobile->position) ? 'data-position="'.esc_attr($step->mobile->position).'"' : 'data-position="center"';
                        if(isset($step->desktop->layout) && esc_attr($step->desktop->layout) === 'embedded'){  $this->hasDesktopEmbed = true; }
                        if(isset($step->mobile->layout) && esc_attr($step->mobile->layout) === 'embedded'){  $this->hasMobileEmbed = true; }
                        //error_log($this->popupID.$key.$mobilenoContent);
                        print_r('<div id="brave_popup_'.$this->popupID.'__step__'.$key.'" class="brave_popup__step_wrap '.esc_attr($mobilenoContentClass).'">');
                        if(isset($step->desktop)){
                           $stepWidth = isset($step->desktop->width) ? absint($step->desktop->width) : 700; $stepHeight = isset($step->desktop->height) ? absint($step->desktop->height) : 500;
                           $dekstopPopupPosition = $this->popupType ==='landing' ? 'data-position="top_center"' : $dekstopPopupPosition;
                           print_r('<div class="brave_popup__step brave_popup__step__desktop '.$this->popup_renderCSSClasses($step->desktop).'" '.$noMobileContentData.' data-width="'.esc_attr($stepWidth).'" data-height="'.esc_attr($stepHeight).'" data-open="false" style="z-index:'.(99999 - $key).'" '.$desktopExitAnimType.' '.$desktopExitAnimDuration.' '.$dekstopPopupLayout.' '.$dekstopPopupPosition.'>'.$this->popup_renderPopup($step->desktop, $key, 'desktop').''.$this->popup_renderOverlay($step->desktop, $key).'</div>');
                        } 
                        if(isset($step->mobile)){
                           $mobileStepWidth = isset($step->mobile->width) ? absint($step->mobile->width) : 320; $mobileStepHeight = isset($step->mobile->height) ? absint($step->mobile->height) : 480;
                           $mobilePopupPosition = $this->popupType ==='landing' ? 'data-position="top_center"' : $mobilePopupPosition;
                           print_r('<div class="brave_popup__step brave_popup__step__mobile '.$this->popup_renderCSSClasses($step->mobile).'" data-width="'.esc_attr($mobileStepWidth).'" data-height="'.esc_attr($mobileStepHeight).'" data-open="false" style="z-index:'.(99999 - $key).'" '.$noMobileContentData.' '.$mobileExitAnimType.' '.$mobileExitAnimDuration.' '.$mobilePopupLayout.' '.$mobilePopupPosition.'>'.$this->popup_renderPopup($step->mobile, $key, 'mobile').''.$this->popup_renderOverlay($step->mobile, $key).'</div>');
                        } 
                        print_r('</div>');
                     }
                  }
               ?>
            </div>
         <?php
      }

      public function popup_generate_styles($stepSettings, $index, $device){
         if(!$stepSettings || !$device){ return ''; }
         //Popup
         $popupLayout = isset($stepSettings->layout) ? $stepSettings->layout : 'boxed';
         $popupWidth = isset($stepSettings->width) ? $stepSettings->width : 700;
         $popupHeight = isset($stepSettings->height) ? $stepSettings->height : 450;
         $fullWidth = isset($stepSettings->fullWidth) ? $stepSettings->fullWidth :false;
         $fullHeight = isset($stepSettings->fullHeight) ? $stepSettings->fullHeight :false;
         $popupPosition = isset($stepSettings->position) ? $stepSettings->position : 'center';
         $popupFont = isset($stepSettings->fontFamily) ? 'font-family:'.$stepSettings->fontFamily.';' : 'font-family:Arial;';
         if($this->popupType === 'landing'){$popupPosition = 'top_center';}

         $this->popup_get_element_fonts($stepSettings);


         $width =  $popupWidth.'px'; if($fullWidth === true || ($popupLayout==='float')){ $width = '100%'; }
         $height =  $popupHeight.'px'; if($fullHeight === true || ($popupLayout==='sidebar')){ $height = '100%'; }
         $shadowStyle = ''; $borderRadius = ''; $borderStyle = '';
         if(isset($stepSettings->shadow) && $stepSettings->shadow === true){
            $shadowSize = isset($stepSettings->shadowSize) ? $stepSettings->shadowSize.'px' : '15px';  
            $shadowColorOpacity = isset($stepSettings->shadowColor) && isset($stepSettings->shadowColor->opacity) ? $stepSettings->shadowColor->opacity : 0.15;
            $shadowColor = isset($stepSettings->shadowColor) && isset($stepSettings->shadowColor->rgb) ? 'rgba('.$stepSettings->shadowColor->rgb.', '.$shadowColorOpacity.')' : 'rgba(0, 0, 0, 0.2)';
            $shadowStyle = 'box-shadow: 0 0 '.$shadowSize.' '.$shadowColor.';';
         }
         if(isset($stepSettings->borderRadius) && $stepSettings->borderRadius > 0){
            $borderRadius = 'border-radius: '.$stepSettings->borderRadius.'px;';
         }
         if(isset($stepSettings->borderSize) && $stepSettings->borderSize > 0){
            $borderSize = $stepSettings->borderSize; 
            $borderColorOpacity = isset($stepSettings->borderColor) && isset($stepSettings->borderColor->opacity) ? $stepSettings->borderColor->opacity : 1;
            $borderColor = isset($stepSettings->borderColor) && isset($stepSettings->borderColor->rgb) ? 'rgba('.$stepSettings->borderColor->rgb.', '.$borderColorOpacity.')' : 'rgba(0, 0, 0, 1)';
            $borderStyle = 'border: '.$borderSize.'px solid '.$borderColor.' ;';
         }

         //Popup Background
         $backgroundColorOpacity = isset($stepSettings->backgroundColor) && isset($stepSettings->backgroundColor->opacity) ? $stepSettings->backgroundColor->opacity : 1;
         $backgroundColor = isset($stepSettings->backgroundColor) && isset($stepSettings->backgroundColor->rgb) ? 'background-color: rgba('.$stepSettings->backgroundColor->rgb.', '.$backgroundColorOpacity.');' : 'background-color: rgba(255,255,255, 1);';
         $backgroundImage = '';$backgroundSize = '';$backgroundPositionX ='';$backgroundPositionY = ''; $backgroundOverlay ='';
         if(isset($stepSettings->backgroundImage) && isset($stepSettings->backgroundImage->image)){
            $backgroundImage = 'background-image: url('.$stepSettings->backgroundImage->image.');';
            $backgroundImageAutoFit = isset($stepSettings->backgroundImage->autoFit) && $stepSettings->backgroundImage->autoFit=== false ? false : true;
            $backgroundSize = isset($stepSettings->backgroundImage->size) && $backgroundImageAutoFit === false ? 'background-size: '.$stepSettings->backgroundImage->size.'%;' : 'background-size: cover;';
            $backgroundPositionX = isset($stepSettings->backgroundImage->posX) ? 'background-position-x: '.$stepSettings->backgroundImage->posX.'%;' : '';
            $backgroundPositionY = isset($stepSettings->backgroundImage->posY) ? 'background-position-y: '.$stepSettings->backgroundImage->posY.'%;' : '';
            $backgroundOverlay = bravepop_generate_style_props(isset($stepSettings->backgroundImageOverlay) ? $stepSettings->backgroundImageOverlay : '', 'background-color', '0,0,0', '0');

         }

         
         //Overlay
         $overlaybgColor = ''; 
         $overlaybgColorRGB = isset($stepSettings->overlayColor->rgb) ? $stepSettings->overlayColor->rgb :'0,0,0';
         $overlaybgColorOpacity = isset($stepSettings->overlayColor) && isset($stepSettings->overlayColor->opacity) ? $stepSettings->overlayColor->opacity :'0.7';
         $overlayBlur =  isset($stepSettings->overlayImage) && isset($stepSettings->overlayImage->blur) ? 'filter: blur('.$stepSettings->overlayImage->blur.'px); transform: scale(1.1); ' : '';
         $overlayOpacity = isset($stepSettings->overlayImage) && isset($stepSettings->overlayImage->opacity) ? 'opacity: '.$stepSettings->overlayImage->opacity.';'  : '';

         //Close Button
         $closeButton = isset($stepSettings->closeButton)  ? $stepSettings->closeButton : 'icon';
         $closeButtonSize = isset($stepSettings->closeButton) && $stepSettings->closeButton === 'text' ? 13 : 24;
         $closeButtonPosition = isset($stepSettings->closeButtonPosition) ? $stepSettings->closeButtonPosition : 'inside_right';
         $closePositionVal = 'auto';
         $closeColorRGB = isset($stepSettings->closeColor) && isset($stepSettings->closeColor->rgb) ? $stepSettings->closeColor->rgb :'0,0,0';
         $closeColorOpacity = isset($stepSettings->closeColor) && isset($stepSettings->closeColor->opacity) ? $stepSettings->closeColor->opacity :'1';
         $closeColor = isset($stepSettings->closeColor) && isset($stepSettings->closeColor->rgb) ? 'rgba('.$stepSettings->closeColor->rgb.', '.$closeColorOpacity.')' : 'rgba(0,0,0, 1)';
         $closeButtonSize = isset($stepSettings->closeButtonSize) ? $stepSettings->closeButtonSize : $closeButtonSize;
         $closePos = ($closeButtonPosition === 'below_right' || $closeButtonPosition === 'below_left' || $closeButtonPosition === 'below_center') ? 'below' : 'above';
         
         if($closePos === 'below'){
            $closePositionVal = ($closeButtonSize + ($closeButton === 'icon' ? ($closeButtonSize/3) :  $closeButtonSize + 10));
            $closePositionStyle = 'bottom:-'.$closePositionVal.'px';
         }
         if($closePos === 'above'){
            $closePositionVal = ($closeButtonSize + ($closeButton === 'icon' ? ($closeButtonSize/3) :  $closeButtonSize) );
            $closePositionStyle = 'top:-'.$closePositionVal.'px';
         }

         //POPUP Margin
         $margin ='';
         if(isset($stepSettings->margin)){
            $marginTop = isset($stepSettings->margin->top) ? 'top:'.$stepSettings->margin->top.'px; ' : '';
            $marginLeft = isset($stepSettings->margin->left) ? 'left:'.$stepSettings->margin->left.'px; ' : '';
            $margin = $marginTop.$marginLeft;
            if($this->popupType === 'landing'){
               $margin = !empty($stepSettings->margin->top) ? 'margin:'.$stepSettings->margin->top.'px 0; ' : '';
            }
         }
  
         //POPUP Position
         $popPositionStyle = '';
         if((isset($stepSettings->fullHeight) && $stepSettings->fullHeight === false) || !isset($stepSettings->fullHeight)){
            if($popupPosition === 'center' || $popupPosition === 'center_left' || $popupPosition === 'center_right' ){
               $marginTopVal = round($popupHeight / 2);
               $popPositionStyle = 'margin-top:-'.$marginTopVal.'px;';
            }
         }


         //Final Style
         $popupStyle = '#brave_popup_'.$this->popupID.'__step__'.$index.' .brave_popup__step__'.$device.' .brave_popup__step__inner{ width: '.$width.';  height: '.$height.';'.$borderRadius . $popPositionStyle . $popupFont.'}';
         $popupStyle = $popupFont ? $popupStyle.'#brave_popup_'.$this->popupID.'__step__'.$index.' .brave_popup__step__'.$device.' .brave_element__wrap{ '. $popupFont.'}': $popupStyle;
         $popupMarginStyle = $margin ? '#brave_popup_'.$this->popupID.'__step__'.$index.' .brave_popup__step__'.$device.' .brave_popupMargin__wrap{ '. $margin.'}':'';

         $popupInnerStyle = ($shadowStyle || $borderRadius || $borderStyle) ? '#brave_popup_'.$this->popupID.'__step__'.$index.' .brave_popup__step__'.$device.' .brave_popup__step__popup{ '.$shadowStyle . $borderRadius. $borderStyle.' }':'';
         $popupBackground = $backgroundColor || $backgroundImage ? '#brave_popup_'.$this->popupID.'__step__'.$index.' .brave_popup__step__'.$device.' .brave_popup__step__content{ '.$backgroundColor . $backgroundImage . $backgroundSize . $backgroundPositionX . $backgroundPositionY.' }': '';
         $popupBackgroundOverlay = $backgroundOverlay ? '#brave_popup_'.$this->popupID.'__step__'.$index.' .brave_popup__step__'.$device.' .brave_popup__step__content__overlay{ '.$backgroundOverlay.' }': '';

         $overlayStyle = $overlaybgColorRGB ? '#brave_popup_'.$this->popupID.'__step__'.$index.' .brave_popup__step__'.$device.' .brave_popup__step__overlay{ background-color: rgba('.$overlaybgColorRGB.', '.$overlaybgColorOpacity.');}' : '';
         $overlayImageStyle = isset($stepSettings->overlayImage) ? '#brave_popup_'.$this->popupID.'__step__'.$index.' .brave_popup__step__'.$device.' .brave_popup__step__overlay__img{ '.$overlayBlur . $overlayOpacity.' }' : '';
         $closeStyle = '#brave_popup_'.$this->popupID.'__step__'.$index.' .brave_popup__step__'.$device.' .brave_popup__close{ font-size:'.$closeButtonSize.'px'.'; width:'.$closeButtonSize.'px'.'; color:'.$closeColor.';'.$closePositionStyle.'}
                        #brave_popup_'.$this->popupID.'__step__'.$index.' .brave_popup__step__'.$device.' .brave_popup__close svg{ width:'.$closeButtonSize.'px'.'; height:'.$closeButtonSize.'px;}
                        #brave_popup_'.$this->popupID.'__step__'.$index.' .brave_popup__step__'.$device.' .brave_popup__close svg path{ fill:'.$closeColor.';}';

         //Animation 
         $contAnim = isset($stepSettings->animation->continious->enable) ? $stepSettings->animation->continious->enable  : false;
         $contAnimType = isset($stepSettings->animation->continious->preset) ? $stepSettings->animation->continious->preset  : 'none';
         $contAnimDuration = isset($stepSettings->animation->continious->duration) ? (Int)$stepSettings->animation->continious->duration / 1000  : 0.5;
         $contAnimDelay = isset($stepSettings->animation->continious->delay) ? (Int)$stepSettings->animation->continious->delay / 1000 : 0;
         $contAnimInfinite = $contAnimDelay === 0 ? 'infinite':'';

         
         //Open Animation
         $openAnim =  isset($stepSettings->animation->load->preset) && $stepSettings->animation->load->preset !== 'none' ? true  : false;
         $openAnimType =  isset($stepSettings->animation->load->preset) ? $stepSettings->animation->load->preset : false;
         $openAnimDuration =  isset($stepSettings->animation->load->duration) ? (Int)$stepSettings->animation->load->duration/1000 : 0.5;

         //Close Animation
         $exiAnim =  isset($stepSettings->animation->exit->preset) && $stepSettings->animation->exit->preset !== 'none' ? true  : false;
         $exitAnimType =  isset($stepSettings->animation->exit->preset) ? $stepSettings->animation->exit->preset : false;
         $exitAnimDuration =  isset($stepSettings->animation->exit->duration) ? (Int)$stepSettings->animation->exit->duration/1000 : 0.5;

         $popupAnimation = $contAnim && $contAnimType !== 'none' ? '#brave_popup_'.$this->popupID.'__step__'.$index.' .brave_popup__step__'.$device.' .brave_popupSections__wrap.brave_element-'.$this->popupID.'_contAnim{ animation: brave'.$contAnimType.' '.$contAnimDuration.'s '.$contAnimInfinite.'; animation-timing-function: linear;  }' : '';
         $popupAnimationHover = $contAnim && $contAnimType !== 'none' ? '#brave_popup_'.$this->popupID.'__step__'.$index.' .brave_popup__step__'.$device.' .brave_popupSections__wrap.brave_element-'.$this->popupID.'_contAnim:hover{ animation: none;  }' : '';

         $popupExitAnimation = $exiAnim ? '#brave_popup_'.$this->popupID.'__step__'.$index.' .brave_popup__step__'.$device.' .brave_popupSections__wrap.brave_element-'.$this->popupID.'_'.$index.'_exitAnim{ animation: brave'.$exitAnimType.' '.$exitAnimDuration.'s linear; animation-timing-function: linear; animation-direction: reverse; opacity:0; }' : '';

         $popupOpenAnimation = $openAnim ? '#brave_popup_'.$this->popupID.'__step__'.$index.' .brave_popup__step__'.$device.' .brave_popupSections__wrap.brave_element-'.$this->popupID.'_'.$index.'_openAnim{ animation: brave'.$openAnimType.' '.$openAnimDuration.'s linear; animation-timing-function: linear; }' : '';

         //ELEMENT STYLES
         $elementStyles = $this->popup_generate_element_styles($stepSettings, $index, $device);
         $bodyStyle = '';
         //If the popup is fullwidth and set to top (Hellobar), add top body margin to push the body down.
         
         if($popupPosition === 'top_center' && $popupLayout ==='float'){
            if(!isset($stepSettings->fullHeight) || (isset($stepSettings->fullHeight) && $stepSettings->fullHeight === false)){
               //$bodyStyle = 'html{margin-top:'.$popupHeight.'px!important;}';
               //.brave_popup__step__elements { width: 1024px; height: '.$popupHeight.'px; margin: 0 auto;}
               //.brave_popup .brave_element {  position: relative; float: left;}
               //.brave_popup__step__elements { position: relative;  z-index: 1; width: auto; height: 100%; float: left; left: 50%; right: 0; transform: translate(-50%, 0); }
            }
            
         }
         //error_log($this->popupID.'__step__'.$index.$popupPosition.$popupLayout.$popupHeight);
         //error_log(json_encode($stepSettings));
         return $bodyStyle . $popupStyle .$popupMarginStyle . $popupInnerStyle . $popupBackground . $popupBackgroundOverlay . $overlayStyle . $overlayImageStyle . $closeStyle. $popupAnimation. $popupAnimationHover. $popupExitAnimation. $popupOpenAnimation. $elementStyles;

      }

      public function popup_generate_element_styles($stepSettings, $stepIndex, $device){
         $elementStyles = '';
         if(isset($stepSettings->content)){
            $groupData = array();
            foreach ($stepSettings->content as $index => $element) {
               if($element->type === 'group'){
                  $elmID = $element->id; $groupData[$elmID] = $element;
               }
            }
            foreach ($stepSettings->content as $index => $element) {
               $width = isset($element->width) ?  'width: '.$element->width.'px;' : '';
               $height = isset($element->height) ?  'height: '.$element->height.'px;' : '';
               $top = isset($element->top) ?  'top: '.$element->top.'px;' : '';
               $left = isset($element->left) ?  'left: '.$element->left.'px;' : '';
               $zIndex = isset($element->left) ?  'z-index: '.$index.';' : '';
               $rotate = isset($element->rotate) ?  'transform: rotate('.$element->rotate.'deg);' : '';
               $breakFree = isset($element->breakFree) && $element->breakFree == true ?  'position: fixed;' : '';
               $contAnim = isset($element->animation->continious->enable) ? $element->animation->continious->enable  : false;
               $contAnimType = isset($element->animation->continious->preset) ? $element->animation->continious->preset  : 'none';
               $contAnimDuration = isset($element->animation->continious->duration) ? (Int)$element->animation->continious->duration / 1000  : 0.5;
               $contAnimDelay = isset($element->animation->continious->delay) ? (Int)$element->animation->continious->delay / 1000 : 0;
               //If element is inside group
               if(isset($element->group) && isset($groupData[$element->group])){
                  if($groupData[$element->group]->top){
                     $top = isset($element->top) ?  'top: '.($element->top + $groupData[$element->group]->top).'px;' : '';
                  }
                  if($groupData[$element->group]->left){
                     $left = isset($element->left) ?  'left: '.($element->left + $groupData[$element->group]->left).'px;' : '';
                  }
               }

               //Open Animation
               $openAnim =  isset($element->animation->load->preset) && $element->animation->load->preset !== 'none' ? true  : false;
               $openAnimType =  isset($element->animation->load->preset) ? $element->animation->load->preset : false;
               $openAnimDuration =  isset($element->animation->load->duration) ? (Int)$element->animation->load->duration/1000 : 0.5;
               $openAnimDelay = isset($element->animation->load->delay) ? (Int)$element->animation->load->delay / 1000 : 0;
               $openAnimDelayStyle = $openAnimDelay ? 'animation-delay:'.$openAnimDelay.'s;' : '';

               $contAnimInfinite = $contAnimDelay === 0 ? 'infinite':'';

               $elementStyle = '#brave_popup_'.$this->popupID.'__step__'.$stepIndex.' #brave_element-'.$element->id.'{ '.$width .  $height .  $top .  $left . $zIndex . $breakFree .'}';
               $elementRotateStyle = $rotate?'#brave_popup_'.$this->popupID.'__step__'.$stepIndex.' #brave_element-'.$element->id.' .brave_element__styler{ '.$rotate.'}':'';
      
               $elementContAnimation = $contAnim && $contAnimType !== 'none' ? '#brave_popup_'.$this->popupID.'__step__'.$stepIndex.' #brave_element-'.$element->id.'.brave_element-'.$element->id.'_contAnim{ animation: brave'.$contAnimType.' '.$contAnimDuration.'s '.$contAnimInfinite.'; animation-timing-function: linear;  }' : '';
               $elementAnimationHover = $contAnim && $contAnimType !== 'none' ? '#brave_popup_'.$this->popupID.'__step__'.$stepIndex.' #brave_element-'.$element->id.'.brave_element-'.$element->id.'_contAnim:hover{ animation: none;  }' : '';

               $elementOpenAnimation = $openAnim ? '#brave_popup_'.$this->popupID.'__step__'.$stepIndex.' #brave_element-'.$element->id.'.brave_element-'.$element->id.'_'.$stepIndex.'_openAnim{ animation: brave'.$openAnimType.' '.$openAnimDuration.'s linear; animation-timing-function: linear; '.$openAnimDelayStyle.' }' : '';

               $elementStyles .= $elementStyle.$elementRotateStyle.$elementContAnimation.$elementAnimationHover.$elementOpenAnimation;
            }
         }

         return $elementStyles;

      }


   }

}

?>