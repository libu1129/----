<?php

if ( ! class_exists( 'BravePop_Element_Text' ) ) {
   

   class BravePop_Element_Text {

      function __construct($data=null, $popupID=null, $stepIndex, $elementIndex, $device='desktop', $goalItem=false, $dynamicData) {
         $this->data = $data;
         $this->popupID = $popupID;
         $this->stepIndex =  $stepIndex;
         $this->elementIndex = $elementIndex;
         $this->goalItem = $goalItem;
         $this->dynamicData = $dynamicData;
      }

      
      public function render_css() { 


         $textAlign = isset($this->data->textAlign) ?  'text-align: '.$this->data->textAlign.';' : '';
         $fontSize = isset($this->data->fontSize) ?   'font-size: '.$this->data->fontSize.'px;' : '';
         $fontFamily = isset($this->data->fontFamily) && ($this->data->fontFamily !== 'None') ?  'font-family: '.$this->data->fontFamily.';' : '';
         $lineHeight = isset($this->data->lineHeight) ?  'line-height: '.$this->data->lineHeight.'em;' : 'line-height: 1.7em;';
         $letterSpacing = isset($this->data->letterSpacing) ?  'letter-spacing: '.$this->data->letterSpacing.'px;' : '';
         $fontBold = isset($this->data->fontBold) && $this->data->fontBold === true ?  'font-weight: bold;' : '';
         $fontUnderline = isset($this->data->fontUnderline) && $this->data->fontUnderline === true ?  'text-decoration: underline;' : '';
         $fontStrike = isset($this->data->fontStrike) && $this->data->fontStrike === true ?  'text-decoration: line-through;' : '';
         $fontItalic = isset($this->data->fontItalic) && $this->data->fontItalic === true ?  'font-style: italic;' : '';
         $fontUppercase = isset($this->data->fontUppercase) && $this->data->fontUppercase === true ?  'text-transform: uppercase;' : '';

         $fontColorRGB = isset($this->data->fontColor) && isset($this->data->fontColor->rgb) ? $this->data->fontColor->rgb :'0,0,0';
         $fontColorOpacity = isset($this->data->fontColor) && isset($this->data->fontColor->opacity) ? $this->data->fontColor->opacity :'1';
         $fontColor = 'color: rgba('.$fontColorRGB.', '.$fontColorOpacity.');';
         


         $elementInnerStyle = '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_element__text_inner{
            '.$textAlign .  $fontSize .  $fontFamily .  $lineHeight . $letterSpacing . $fontBold . $fontUnderline . $fontStrike . $fontItalic . $fontUppercase . $fontColor . 
         '}';

         return  $elementInnerStyle;

      }

      public function dynamicText(){
         $dnmcType = isset($this->data->dynamicData->type) ? $this->data->dynamicData->type : '';
         $dnmcPostType = isset($this->data->dynamicData->post) ? $this->data->dynamicData->post : '';
         $dnmcDataType = isset($this->data->dynamicData->data) ? $this->data->dynamicData->data : '';
         $dnmcIndex = isset($this->data->dynamicData->index) ? $this->data->dynamicData->index : '';
         $dynamicText = '';

         //error_log('dnmcType: '.$dnmcType. ' dnmcPostType: '.$dnmcPostType. ' dnmcDataType: '.$dnmcDataType. ' dnmcIndex: '.$dnmcIndex);
         if($dnmcPostType === 'date'){
            if(!empty($this->dynamicData['date']->$dnmcType)){
               $dynamicText = $this->dynamicData['date']->$dnmcType;
            }
         }else if($dnmcType === 'general'){
            if(!empty($this->dynamicData['general']->$dnmcPostType->$dnmcDataType)){
               $dynamicText = $this->dynamicData['general']->$dnmcPostType->$dnmcDataType;
            }
         }else{
            if(!empty($this->dynamicData[$dnmcPostType]->$dnmcType)){
               foreach ($this->dynamicData[$dnmcPostType]->$dnmcType as $item) {
                  if(($item->index === $dnmcIndex) && !empty($item->$dnmcDataType)){
                     $dynamicText = $item->$dnmcDataType;
                  }
               }
            }
         }

         if($dnmcType){
            
         }

         return $dynamicText;
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
         $actionURL  = isset($this->data->action->actionData->url) ? $this->data->action->actionData->url : '';
         $actionNewWindow  = isset($this->data->action->actionData->new_window) ? $this->data->action->actionData->new_window : '';
         $actionStepNum  = isset($this->data->action->actionData->step) ? (Int)$this->data->action->actionData->step  - 1 : '';
         $actionJS = $actionType === 'javascript' && isset($this->data->action->actionData->javascript) ? 'onclick="'.$this->data->action->actionData->javascript.' '.$actionInlineTrack.' '.$goalAction.'"': '';
         if(isset($this->data->action->actionData->dynamicURL)){
            $dynamicURL  = bravepopup_dynamicLink_data($this->data->action->actionData->dynamicURL, $this->dynamicData);
            if(isset($dynamicURL->link)){   $actionURL  =  $dynamicURL->link; }
         }
         $actionLink = $clickable && ($actionType === 'url' || $actionType === 'dynamic') && $actionURL ? 'onclick="'.$goalAction.'" href="'.$actionURL.'" '.($actionNewWindow ? 'target="_blank"' : '').'':'';
         $actionStep = $clickable && $actionType === 'step' && $actionStepNum >=0 ? 'onclick="brave_action_step('.$this->popupID.', '.$this->stepIndex.', '.$actionStepNum.'); '.$actionInlineTrack.' '.$goalAction.'"':'';
         $actionClose = $clickable && $actionType === 'close' ? 'onclick="brave_close_popup(\''.$this->popupID.'\', \''.$this->stepIndex.'\'); '.$actionInlineTrack.' '.$goalAction.'"':'';

         $html = new stdClass();
         $html->start = '<a class="brave_element__inner_link" '.$actionLink.'  '.$actionStep . $actionClose. $actionTrack.$actionJS.'>';
         $html->end = '</a>';

         return $html;
      }



      public function render( ) { 
         $content = isset($this->data->content) ? html_entity_decode($this->data->content) : '';
         $dynamiClass = '';
         if(!empty($this->data->dynamic) && !empty($this->data->dynamicData->type) && $this->dynamicData){
            $content = $this->dynamicText();
            $dynamiClass = ' brave_element--text_dynamic';
         }
         
         $clickable = !empty($this->data->clickable) ? $this->data->clickable : false;
         $clickableHTML = $this->clickable_html();
         $clickStart = $clickable && isset($clickableHTML->start) ? $clickableHTML->start : '';
         $clickEnd = $clickable && isset($clickableHTML->end) ? $clickableHTML->end : '';
         $scrollbar = !empty($this->data->scrollbar) ? 'brave_element__wrap--has-scrollbar' : '';

         return '<div id="brave_element-'.$this->data->id.'" class="brave_element brave_element--text '.($clickable ? 'brave_element--has-click-action' : ''). $dynamiClass. '">
                  <div class="brave_element__wrap '.$scrollbar.'">
                     <div class="brave_element__styler">
                        <div class="brave_element__inner">
                           '.$clickStart.'
                              <div class="brave_element__text_inner">'.$content.'</div>
                           '.$clickEnd.'
                        </div>
                     </div>
                  </div>
               </div>';
      }


   }


}
?>