<?php

if ( ! class_exists( 'BravePop_Element_Image' ) ) {
   

   class BravePop_Element_Image {

      function __construct($data=null, $popupID=null, $stepIndex, $elementIndex, $device='desktop', $goalItem=false, $dynamicData) {
         $this->data = $data;
         $this->popupID = $popupID;
         $this->stepIndex =  $stepIndex;
         $this->elementIndex = $elementIndex;
         $this->goalItem = $goalItem;
         $this->dynamicData = $dynamicData;
      }

      
      public function render_css() { 

         $borderStyle = '';  $shadowStyle = '';
         if(isset($this->data->border)){
            $borderColorRGB = isset($this->data->border->color) && isset($this->data->border->color->rgb) ? $this->data->border->color->rgb :'0,0,0';
            $borderColorOpacity = isset($this->data->border->color) && isset($this->data->border->color->opacity) ? $this->data->border->color->opacity :'1';
            $borderColor = 'rgba('.$borderColorRGB.', '.$borderColorOpacity.')';
            $borderSize = isset($this->data->border->size) ? $this->data->border->size.'px' : '1px';
            $borderType = isset($this->data->border->style) ? $this->data->border->style : 'solid';
            $borderStyle = 'border: '.$borderSize .' '.$borderType.' '.$borderColor.';';
         }

         $borderRadius = isset($this->data->roundness) ?  'border-radius: '.$this->data->roundness.'px;' : '';
         $opacity = isset($this->data->opacity) ? 'opacity: '.($this->data->opacity/100).';' : '';
         $flip = isset($this->data->flip) && $this->data->flip === true ? 'transform: scaleX(-1);' : '';
         $aspectRatio = !isset($this->data->aspectRatio) ? true : $this->data->aspectRatio;
         $verticalPosition = isset($this->data->verticalPosition) ? $this->data->verticalPosition : 50;
         $horizontalPosition = isset($this->data->horizontalPosition) ? $this->data->horizontalPosition : 0;
         $objectPosition = $aspectRatio === false && isset($this->data->size) && $this->data->size <= 1 ?  'object-position: '.$horizontalPosition.'% '.$verticalPosition.'%;' : '';
         $scale = isset($this->data->size) && $this->data->size > 1 ? 'transform: scale('.$this->data->size.');':'';
         $imgTop =  $verticalPosition && isset($this->data->size) && $this->data->size > 1 ? 'top: '.$verticalPosition.'%;':'';
         $imgLeft =  $horizontalPosition && isset($this->data->size) && $this->data->size > 1 ? 'left: '.$horizontalPosition.'%;':'';
         $contrast = isset($this->data->contrast)  ? 'contrast('.$this->data->contrast.'%)'  : '';
         $brightness = isset($this->data->brightness) ? 'brightness('.$this->data->brightness.'%)' : '';
         $grayscale = isset($this->data->grayscale) && $this->data->grayscale === true  ? 'grayscale(100%)' : '';
         $blur = isset($this->data->blur)  ? 'blur('.$this->data->blur.'px);' : '';
         $filter = ($contrast || $grayscale || $brightness || $blur) ? 'filter: '.$contrast.' '.$grayscale.' '.$brightness.' '.$blur. ';' : '';
         $shadowColorRGB = isset($this->data->shadowColor) && isset($this->data->shadowColor->rgb) ? $this->data->shadowColor->rgb :'0,0,0';
         $shadowColorOpacity = isset($this->data->shadowColor) && isset($this->data->shadowColor->opacity) ? $this->data->shadowColor->opacity :'0.3';
         $shadow = isset($this->data->shadow) ?  'filter: drop-shadow(0 0 '.$this->data->shadow.'px rgba('.$shadowColorRGB.', '.$shadowColorOpacity.'));' : '';

         //overlay
         $overlayColorRGB = isset($this->data->overlay) && isset($this->data->overlay->rgb) ? $this->data->overlay->rgb :'0,0,0';
         $overlayColorOpacity = isset($this->data->overlay) && isset($this->data->overlay->opacity) ? $this->data->overlay->opacity :'1';
         $overlayColor = isset($this->data->overlay) && isset($this->data->overlay->rgb) ?  'background-color: rgba('.$overlayColorRGB.', '.$overlayColorOpacity.');' : '';


         $elementInnerStyle = '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_element__styler{ '. $borderStyle . $borderRadius . $flip . $shadow. $opacity. '}';
         
         $elementImageStyle = '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' img{ '. $objectPosition . $filter . $scale . $imgTop . $imgLeft . '}';
         
         $elementOverlayStyle = $overlayColor ? '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_element__image__overlay{ '. $overlayColor . '}' : '';

         $elementFrame = !empty($this->data->frame) && function_exists('brave_image_frame') ? '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_element__image_inner{ width: 100%; height: 100%; clip-path: url(#'.$this->data->frame.'_'.$this->data->id.')}' : '';

         return  $elementInnerStyle . $elementImageStyle. $elementOverlayStyle . $elementFrame;

      }

      public function clickable_html( ) { 
         $clickable = isset($this->data->clickable) ? $this->data->clickable : false;
         $actionType = isset($this->data->action->type) ? $this->data->action->type : 'none';
         $track = isset($this->data->action->track) ? $this->data->action->track : false;
         $eventCategory = isset($this->data->action->trackData->eventCategory) ? $this->data->action->trackData->eventCategory : 'popup';
         $eventAction = isset($this->data->action->trackData->eventAction) ? $this->data->action->trackData->eventAction : 'click';
         $eventLabel = isset($this->data->action->trackData->eventLabel) ? $this->data->action->trackData->eventLabel : '';
         $actionTrack = ($actionType !== 'step' || $actionType !== 'close') && $track && $clickable ? ' onclick="brave_send_ga_event(\''.$eventCategory.'\', \''.$eventAction.'\', \''.$eventLabel.'\');"':'';
         $actionInlineTrack = ($actionType === 'step' || $actionType === 'close') && $track && $clickable ? ' brave_send_ga_event(\''.$eventCategory.'\', \''.$eventAction.'\', \''.$eventLabel.'\');':'';
         $goalAction = $this->goalItem ? 'brave_complete_goal('.$this->popupID.', \'click\');"':'';

         $actionJS = $actionType === 'javascript' && isset($this->data->action->actionData->javascript) ? 'onclick="'.$this->data->action->actionData->javascript.' '.$actionInlineTrack.' '.$goalAction.'"': '';
         $actionURL  = isset($this->data->action->actionData->url) ? $this->data->action->actionData->url : '';
         if(isset($this->data->action->actionData->dynamicURL)){
            $dynamicURL  = bravepopup_dynamicLink_data($this->data->action->actionData->dynamicURL, $this->dynamicData);
            if(isset($dynamicURL->link)){   $actionURL  =  $dynamicURL->link;   }
         }
         $actionNewWindow  = isset($this->data->action->actionData->new_window) ? $this->data->action->actionData->new_window : '';
         $actionStepNum  = isset($this->data->action->actionData->step) ? (Int)$this->data->action->actionData->step  - 1 : '';

         $actionLink = $clickable && ($actionType === 'url' || $actionType === 'dynamic') && $actionURL ? 'onclick="'.$goalAction.'" href="'.$actionURL.'" '.($actionNewWindow ? 'target="_blank"' : '').'':'';
         $actionStep = $clickable && $actionType === 'step' && $actionStepNum >=0 ? 'onclick="brave_action_step('.$this->popupID.', '.$this->stepIndex.', '.$actionStepNum.'); '.$actionInlineTrack.' '.$goalAction.'"':'';
         $actionClose = $clickable && $actionType === 'close' ? 'onclick="brave_close_popup(\''.$this->popupID.'\', \''.$this->stepIndex.'\'); '.$actionInlineTrack.' '.$goalAction.'"':'';



         $html = new stdClass();
         $html->start = '<a class="brave_element__inner_link" '.$actionLink.'  '.$actionStep . $actionClose. $actionTrack.$actionJS.'>';
         $html->end = '</a>';

         return $html;
      }

      public function dynamicImage(){
         $dnmcType = isset($this->data->dynamicData->type) ? $this->data->dynamicData->type : '';
         $dnmcPostType = isset($this->data->dynamicData->post) ? $this->data->dynamicData->post : '';
         $dnmcIndex = isset($this->data->dynamicData->index) ? $this->data->dynamicData->index : '';
         $dynamicImage = '';

         if(!empty($this->dynamicData[$dnmcPostType]->$dnmcType)){
            foreach ($this->dynamicData[$dnmcPostType]->$dnmcType as $item) {
               if(($item->index === $dnmcIndex) && !empty($item->image)){
                  $dynamicImage = $item->image;
               }
            }
         }
         
         return $dynamicImage;
      }

      public function render( ) { 
         $imageURL = isset($this->data->image) ? $this->data->image : '';

         $overlay = isset($this->data->overlay) ? '<div class="brave_element__image__overlay"></div>' : '';
         $hoverClass = isset($this->data->hover->style) && $this->data->hover->style !== 'none' ? 'brave_element--hasHoverAnim brave_element--image--hover_'.$this->data->hover->style :'';
         $clickable = isset($this->data->clickable) ? $this->data->clickable : false;
         $clickableHTML = $this->clickable_html();
         $clickStart = $clickable && isset($clickableHTML->start) ? $clickableHTML->start : '';
         $clickEnd = $clickable && isset($clickableHTML->end) ? $clickableHTML->end : '';
         $frameSVG = !empty($this->data->frame) && function_exists('brave_image_frame') ? brave_image_frame($this->data->frame, $this->data->id) : '';
         
         if(!empty($this->data->dynamic) && !empty($this->data->dynamicData->type) && $this->dynamicData){
            $imageURL = $this->dynamicImage();
         }

         return '<div id="brave_element-'.$this->data->id.'" class="brave_element brave_element--image '.$hoverClass.' '.($clickable ? 'brave_element--has-click-action' : '').'">
                  <div class="brave_element__wrap">
                     <div class="brave_element__styler">
                        <div class="brave_element__inner">
                           <div class="brave_element__image_inner">
                              '.$clickStart.'
                                 '.$overlay.'
                                 <img class="brave_element__image brave_element_img_item skip-lazy no-lazyload" data-lazy="'.$imageURL.'" src="'.bravepop_get_preloader().'" />
                              '.$clickEnd.'
                           </div>
                           '.$frameSVG.'
                        </div>
                     </div>
                  </div>
               </div>';
      }


   }


}
?>