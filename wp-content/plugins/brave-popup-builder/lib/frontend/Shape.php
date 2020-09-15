<?php

if ( ! class_exists( 'BravePop_Element_Shape' ) ) {
   

   class BravePop_Element_Shape {

      function __construct($data=null, $popupID=null, $stepIndex, $elementIndex, $device='desktop', $goalItem=false, $dynamicData) {
         $this->data = $data;
         $this->popupID = $popupID;
         $this->stepIndex =  $stepIndex;
         $this->elementIndex = $elementIndex;
         $this->goalItem = $goalItem;
         $this->dynamicData = $dynamicData;
      }

      
      public function render_css() { 
         $elementBlur = !empty($this->data->blur) && !empty($this->data->blurSize) ? '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_element__styler{filter: blur('.$this->data->blurSize.'px)}' : '';
         $elementSize = '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_element_shape-icon{ font-size:'.($this->data->width - 10).'px;}';
         return  $elementSize.$elementBlur;
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
         $actionNewWindow  = isset($this->data->action->actionData->new_window) ? $this->data->action->actionData->new_window : '';
         $actionStepNum  = isset($this->data->action->actionData->step) ? (Int)$this->data->action->actionData->step  - 1 : false;
         if(isset($this->data->action->actionData->dynamicURL)){
            $dynamicURL  = bravepopup_dynamicLink_data($this->data->action->actionData->dynamicURL, $this->dynamicData);
            if(isset($dynamicURL->link)){   $actionURL  =  $dynamicURL->link;   }
         }

         $actionLink = $clickable && ($actionType === 'url' || $actionType === 'dynamic') && $actionURL ? 'onclick="'.$goalAction.'" href="'.$actionURL.'" '.($actionNewWindow ? 'target="_blank"' : '').'':'';
         $actionStep = $clickable && $actionType === 'step' && $actionStepNum >=0 ? 'onclick="brave_action_step('.$this->popupID.', '.$this->stepIndex.', '.$actionStepNum.'); '.$actionInlineTrack.' '.$goalAction.'"':'';
         $actionClose = $clickable && $actionType === 'close' ? 'onclick="brave_close_popup(\''.$this->popupID.'\', \''.$this->stepIndex.'\'); '.$actionInlineTrack.' '.$goalAction.'"':'';

         $html = new stdClass();
         $html->start = '<a class="brave_element__inner_link" '.$actionLink.'  '.$actionStep . $actionClose. $actionTrack. $actionJS.'>';
         $html->end = '</a>';

         return $html;
      }

      public function render( ) { 

         $shape = isset($this->data->shape) ? $this->data->shape : 'square';
         $shapeType =  isset($this->data->shapeType) ? $this->data->shapeType : 'shape';
         $icon =  isset($this->data->icon) ? $this->data->icon : new stdClass();
         $border = new stdClass(); $shadow = new stdClass(); $shapeData  = new stdClass();
         $fillImage =  isset($this->data->fillImage) ? $this->data->fillImage : null;

         $fillColorRGB = isset($this->data->fillColor) && isset($this->data->fillColor->rgb) ? $this->data->fillColor->rgb :'0,0,0';
         $fillColorOpacity = isset($this->data->fillColor) && isset($this->data->fillColor->opacity) ? $this->data->fillColor->opacity :'1';
         $shapeData->fillColor =  'rgba('.$fillColorRGB.', '.$fillColorOpacity.')';
         $shapeData->width = isset($this->data->width) ? $this->data->width.'px' : '100px';
         $shapeData->height = isset($this->data->height) ? $this->data->height.'px' : '100px';
         if(!empty($this->data->border)){
            $border->size = isset($this->data->borderSize) ? $this->data->borderSize : null;
            $border->color =  isset($this->data->borderColor) ? $this->data->borderColor : null;
         }
         if(!empty($this->data->shadow)){
            $shadow->size = isset($this->data->shadowSize) ? $this->data->shadowSize : null;
            $shadow->color = isset($this->data->shadowColor) ? $this->data->shadowColor : null;
         }
         $clickable = isset($this->data->clickable) ? $this->data->clickable : false;
         $clickableHTML = $this->clickable_html();
         $clickStart = $clickable && isset($clickableHTML->start) ? $clickableHTML->start : '';
         $clickEnd = $clickable && isset($clickableHTML->end) ? $clickableHTML->end : '';
         $iconHTML = '';
         if($shapeType === 'icon' && isset($this->data->icon->body)){
            $iconHTML = '<div class="brave_element_shape-icon"><svg viewBox="0 0 '.$icon->width.' '.$icon->height.'" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg" xmlnsXlink="http://www.w3.org/1999/xlink">'.str_replace('currentColor', $shapeData->fillColor ,$icon->body).'</svg></div>';
         }
         if($shapeType === 'icon' && !isset($this->data->icon->body)){
            $iconHTML = '<div class="brave_element_shape-icon"><svg viewBox="0 0 576 512" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg" xmlnsXlink="http://www.w3.org/1999/xlink"><path fill="'.$shapeData->fillColor.'" d="M485.5 0L576 160H474.9L405.7 0h79.8zm-128 0l69.2 160H149.3L218.5 0h139zm-267 0h79.8l-69.2 160H0L90.5 0zM0 192h100.7l123 251.7c1.5 3.1-2.7 5.9-5 3.3L0 192zm148.2 0h279.6l-137 318.2c-1 2.4-4.5 2.4-5.5 0L148.2 192zm204.1 251.7l123-251.7H576L357.3 446.9c-2.3 2.7-6.5-.1-5-3.2z" /></svg></div>';
         }

         return '<div id="brave_element-'.$this->data->id.'" class="brave_element brave_element--shape '.($clickable ? 'brave_element--has-click-action' : '').'">
                  <div class="brave_element__wrap">
                     <div class="brave_element__styler">
                        <div class="brave_element__inner">
                              '.$clickStart.'
                                 '.($iconHTML ? $iconHTML : renderShape($this->data->id, $shape, $shapeData, $fillImage, $border, isset($this->data->shadow) && $this->data->shadow === true ? $shadow: false )).'
                              '.$clickEnd.'
                        </div>
                     </div>
                  </div>
               </div>';
      }


   }


}
?>