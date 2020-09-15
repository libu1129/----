<?php

if ( ! class_exists( 'BravePop_Element_Button' ) ) {
   

   class BravePop_Element_Button {

      function __construct($data=null, $popupID=null, $stepIndex, $elementIndex, $device='desktop', $goalItem=false, $dynamicData) {
         $this->data = $data;
         $this->popupID = $popupID;
         $this->stepIndex =  $stepIndex;
         $this->elementIndex = $elementIndex;
         $this->goalItem = $goalItem;
         $this->dynamicData = $dynamicData;
      }


      public function render_css() { 

         $lineHeight = isset($this->data->height) ? 'line-height: '.$this->data->height.'px;' : '';
         $textAlign = isset($this->data->textAlign) ?  'text-align: '.$this->data->textAlign.';' : '';
         $fontSize = isset($this->data->fontSize) ?   'font-size: '.$this->data->fontSize.'px;' : '';
         $letterSpacing = isset($this->data->letterSpacing) ?   'letter-spacing: '.$this->data->letterSpacing.'px;' : '';
         $fontFamily = isset($this->data->fontFamily) && $this->data->fontFamily !== 'None' ?  'font-family: '.$this->data->fontFamily.';' : '';
         $fontBold = isset($this->data->bold) && $this->data->bold === true ?  'font-weight: bold;' : '';
         $fontColorRGB = isset($this->data->fontColor) && isset($this->data->fontColor->rgb) ? $this->data->fontColor->rgb :'0,0,0';
         $fontColorOpacity = isset($this->data->fontColor) && isset($this->data->fontColor->opacity) ? $this->data->fontColor->opacity :'1';
         $fontColor = 'color: rgba('.$fontColorRGB.', '.$fontColorOpacity.');';
         $borderRadius= isset($this->data->borderRadius) ?   'border-radius: '.$this->data->borderRadius.'px;' : '';

         $bgColorRGB = isset($this->data->bgColor) && isset($this->data->bgColor->rgb) ? $this->data->bgColor->rgb :'0,0,0';
         $bgColorOpacity = isset($this->data->bgColor) && isset($this->data->bgColor->opacity) ? $this->data->bgColor->opacity :'1';
         $bgColor = 'background-color: rgba('.$bgColorRGB.', '.$bgColorOpacity.');';

         $borderStyle = '';  $shadowStyle = '';
         if(isset($this->data->border) && $this->data->border === true){
            $borderColorRGB = isset($this->data->borderColor) && isset($this->data->borderColor->rgb) ? $this->data->borderColor->rgb :'0,0,0';
            $borderColorOpacity = isset($this->data->borderColor) && isset($this->data->borderColor->opacity) ? $this->data->borderColor->opacity :'1';
            $borderColor = 'rgba('.$borderColorRGB.', '.$borderColorOpacity.')';
            $borderSize = isset($this->data->borderSize) ? $this->data->borderSize.'px' : '1px';
            $borderStyle = 'border: '.$borderSize .' solid '.$borderColor.';';
         }

         if(isset($this->data->shadow) && $this->data->shadow === true){
            $shadowColorRGB = isset($this->data->shadowColor) && isset($this->data->shadowColor->rgb) ? $this->data->shadowColor->rgb :'0,0,0';
            $shadowColorOpacity = isset($this->data->shadowColor) && isset($this->data->shadowColor->opacity) ? $this->data->shadowColor->opacity :'1';
            $shadowColor = 'rgba('.$shadowColorRGB.', '.$shadowColorOpacity.')';
            $shadowSize = isset($this->data->shadowSize) ? $this->data->shadowSize.'px' : '10px';
            $shadowStyle = 'box-shadow: 0 0 '.$shadowSize .' '.$shadowColor.';';
         }

         $iconSize = isset($this->data->icon) && isset($this->data->fontSize) ? 'font-size: '.(($this->data->fontSize * 85)/100).'px' : '';


         $elementInnerStyle = '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_element__styler{
            '.$textAlign .  $fontSize .  $fontFamily .  $borderRadius . $fontBold . $fontColor . $borderStyle . $shadowStyle . $bgColor .  $lineHeight .$letterSpacing.
         '}';

         $elementIconSize = isset($this->data->icon) ? '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_element-icon{ '.$iconSize. '}' : '';
         $elementHoverStyle = '';

         if(isset($this->data->hoverAnimation) && isset($this->data->hoverColors) && $this->data->hoverAnimation === 'color' ){
            $hoverBgRgb = isset($this->data->hoverColors->background) && isset($this->data->hoverColors->background->rgb) ? $this->data->hoverColors->background->rgb :'0,0,0';
            $hoverBgOpacity = isset($this->data->borderColor) && isset($this->data->borderColor->opacity) ? $this->data->borderColor->opacity :'1';
            $hoverBg = 'background-color: rgba('.$hoverBgRgb.', '.$hoverBgOpacity.');';
            $fontColor = isset($this->data->hoverColors->text) && isset($this->data->hoverColors->background->hex) ? 'color: '.$this->data->hoverColors->text->hex.';' : '';
            $elementHoverStyle = ($hoverBg || $fontColor) ? '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_element__styler:hover{ '.$hoverBg.$fontColor. '}' : '';
         }

         return  $elementInnerStyle . $elementIconSize . $elementHoverStyle;

      }


      public function render( ) { 
         $buttonText = isset($this->data->buttonText) ? esc_html($this->data->buttonText) : '';
         $hoverClass = isset($this->data->hoverAnimation) && $this->data->hoverAnimation !== 'none' ? 'brave_element--hasHoverAnim brave_element--button--hover_'.$this->data->hoverAnimation :'';
         $actionType = isset($this->data->action->type) ? $this->data->action->type : 'none';
         $actionURL  = isset($this->data->action->actionData->url) ? $this->data->action->actionData->url : '';
         $actionNewWindow  = isset($this->data->action->actionData->new_window) ? $this->data->action->actionData->new_window : '';
         $actionStepNum  = isset($this->data->action->actionData->step) ? (Int)$this->data->action->actionData->step  - 1 : '';
         
         $track = isset($this->data->action->track) ? $this->data->action->track : false;
         $eventCategory = isset($this->data->action->trackData->eventCategory) ? $this->data->action->trackData->eventCategory : 'popup';
         $eventAction = isset($this->data->action->trackData->eventAction) ? $this->data->action->trackData->eventAction : 'click';
         $eventLabel = isset($this->data->action->trackData->eventLabel) ? $this->data->action->trackData->eventLabel : '';

         $actionTrack = ($actionType !== 'step' || $actionType !== 'close') && $track ? 'onclick="brave_send_ga_event(\''.$eventCategory.'\', \''.$eventAction.'\', \''.$eventLabel.'\');"':'';
         $actionInlineTrack = ($actionType === 'step' || $actionType === 'close') && $track ? 'brave_send_ga_event(\''.$eventCategory.'\', \''.$eventAction.'\', \''.$eventLabel.'\');':'';
         $goalAction = $this->goalItem ? 'brave_complete_goal('.$this->popupID.', \'click\');"':'';

         $actionJS = $actionType === 'javascript' && isset($this->data->action->actionData->javascript) ? 'onclick="'.$this->data->action->actionData->javascript.' '.$actionInlineTrack.' '.$goalAction.'"': '';

         //Dynamic Data
         $dynamicAttrs = ''; $dynamicClasses = '';
         if(isset($this->data->action->actionData->dynamicURL)){
            $dynamicActionLink  = bravepopup_dynamicLink_data($this->data->action->actionData->dynamicURL, $this->dynamicData);
            //error_log(json_encode($dynamicActionLink));
            if(isset($dynamicURL->link)){   $actionURL  =  $dynamicActionLink->link;  }
            if(!empty($dynamicActionLink->attr)){   $dynamicAttrs = $dynamicActionLink->attr;    }
            if(!empty($dynamicActionLink->classes)){   $dynamicClasses = $dynamicActionLink->classes;    }
         }

         $actionLink = ($actionType === 'dynamic' ||$actionType === 'url') && $actionURL ? 'onclick="'.$goalAction.'" href="'.$actionURL.'" '.($actionNewWindow ? 'target="_blank"' : '').'':'';
         $actionStep = $actionType === 'step' && $actionStepNum >=0 ? 'onclick="brave_action_step('.$this->popupID.', '.$this->stepIndex.', '.$actionStepNum.'); '.$actionInlineTrack.' '.$goalAction.'"':'';
         $actionClose = $actionType === 'close' ? 'onclick="brave_close_popup(\''.$this->popupID.'\', \''.$this->stepIndex.'\'); '.$actionInlineTrack.' '.$goalAction.'"':'';
         $hasClickAction = ($actionType === 'dynamic' || $actionType === 'url' || $actionType === 'step' || $actionType === 'close' || $actionType === 'javascript') ? 'brave_element--has-click-action' : '';
         
         $iconHTML = '';
         $iconColor = isset($this->data->iconColor->rgb) ? 'rgba('.$this->data->iconColor->rgb.', '.(isset($this->data->iconColor->opacity) ? $this->data->iconColor->opacity : 1).')' : '';
         if(isset($this->data->icon->body)){
            $iconHTML = '<span class="brave_element-icon"><svg viewBox="0 0 '.$this->data->icon->width.' '.$this->data->icon->height.'" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg" xmlnsXlink="http://www.w3.org/1999/xlink">'.str_replace('currentColor', $iconColor ,$this->data->icon->body).'</svg></span>';
         }

         if(isset($this->data->action->actionData->dynamicURL->data) && $this->data->action->actionData->dynamicURL->data === 'cart'){
            $fontSize = isset($this->data->fontSize) ? $this->data->fontSize.'px;' : 'inherit';
            $fontColorRGB = isset($this->data->fontColor) && isset($this->data->fontColor->rgb) ? 'rgb('.$this->data->fontColor->rgb.')' :'rgb(0,0,0)';
            $iconHTML .= '<span class="brave_element-cart_icon" style="width: '.$fontSize.'">'.bravepop_renderIcon('check', $iconColor ? $iconColor : $fontColorRGB).'</span>';
         }

         return '<div id="brave_element-'.$this->data->id.'" class="brave_element brave_element--button '.$hoverClass .' '.$hasClickAction.'">
                  <div class="brave_element__wrap">
                     <div class="brave_element__styler">
                        <a class="brave_element__inner_link '.$dynamicClasses.'" '.$actionLink.' '.$actionStep . $actionClose. $actionTrack. $actionJS. $dynamicAttrs.'>
                           '.$iconHTML.'<div class="brave_element__button_text">'.$buttonText.'</div>
                        </a>
                     </div>
                  </div>
               </div>';
      }


   }


}
?>