<?php

add_action('wp_ajax_bravepop_form_submission', 'bravepop_form_submission', 0);
add_action('wp_ajax_nopriv_bravepop_form_submission', 'bravepop_form_submission');

function bravepop_form_submission(){

   if(!isset($_POST['popupID']) || !isset($_POST['stepID'])  || !isset($_POST['device'])  || !isset($_POST['formID'])  || !isset($_POST['formData']) ){ wp_die(); }
   
   // First check the nonce, if it fails the function will break
   $securityPassed = check_ajax_referer('brave-ajax-form-nonce', 'security', false);
   if($securityPassed === false) {
      print_r(json_encode(array('sent'=>false, 'message'=>__('Error Sending! Please reload the page and try again.', 'bravepop'))));
      wp_die();
   }


   // Nonce is checked, get the POST data and sign user on
   $popupID = sanitize_text_field(wp_unslash($_POST['popupID']));
   $popupStep = sanitize_text_field(wp_unslash($_POST['stepID']));
   $popupDevice = sanitize_text_field(wp_unslash($_POST['device']));
   $elementID = sanitize_text_field(wp_unslash($_POST['formID']));
   $formData = json_decode(stripslashes($_POST['formData']));

   //Fetch Form Settings
   $popupData = json_decode(get_post_meta($popupID, 'popup_data', true));
   
   //Incorporate Field Settings with Given Value
   $popupContent =  isset($popupData->steps[$popupStep]->$popupDevice->content) ? $popupData->steps[$popupStep]->$popupDevice->content : array();
   $fieldSettings = new stdClass();
   $actionSettings = new stdClass();


   foreach ($popupContent as $key => $element) {
      if($element->id === $elementID){
         if(isset($element->formData->settings->action)){   $actionSettings = $element->formData->settings->action;    }
         if(isset($element->formData->fields)){ 
            
            foreach ($element->formData->fields as $key => $field) {
               $fieldID = $field->id;
               $fieldSettings->$fieldID  = $field;
               $fieldKey = isset($field->uid) ? $field->uid : '';
               $fieldKey2 = isset($field->uidl) ? $field->uidl : '';
               
                  foreach ((array)$formData as $fID => $fVal) {
                     
                     if($fID === $fieldID){

                        $fieldSettings->$fieldID->value = $fVal;
   
                        //Assign shortcode Values
                        if($fieldKey){
                           if(isset($fVal) && is_string($fVal)){
                              $fieldSettings->$fieldID->$fieldKey = strip_tags($fVal);
                           }  
      
                           if(isset($fVal) && is_array($fVal)){
                              $arrayVal = implode(", ", $fVal);
                              $fieldSettings->$fieldID->$fieldKey = strip_tags($arrayVal);
                           }
      
                           if(isset($fVal) && is_array($fVal) && $field->type === 'input' && $field->validation === 'name'){
                              $firstname = isset($fVal[0]) ? strip_tags($fVal[0]) : '';
                              $lastname = isset($fVal[1]) ? strip_tags($fVal[1]) : '';
                              $fieldSettings->$fieldID->$fieldKey = $firstname;
                              if($fieldKey2){
                                 $fieldSettings->$fieldID->$fieldKey2 = $lastname;
                              }
                           }
                        }
                        /////END////

                     }
                  }
               
               

            }
         }
         
      }
   }

   //error_log(json_encode($fieldSettings));

   //Validatation: Check if Required Fields are empty or not.
   foreach ((array)$fieldSettings as $key => $field) {
      if(isset($field->required) && $field->required === true){
         if(isset($field->type) && $field->type !== 'step'){ 
            if((!isset($field->value) || !$field->value) ){
               print_r(json_encode(array('sent'=>false, 'id' => $key, 'type'=> 'required', 'message' => __('Required.', 'bravepop'))));
               wp_die();
            }
            if(isset($field->value) && $field->type ==='select'  && $field->value === 'none'){
               print_r(json_encode(array('sent'=>false, 'id' => $key, 'type'=> 'required', 'message' => __('Required.', 'bravepop'))));
               wp_die();
            }
            if((!isset($field->value) || !$field->value)  && isset($field->validation) && $field->validation === 'name'){
               if(!$field->value[0] || !$field->value[1]){
                  print_r(json_encode(array('sent'=>false, 'id' => $key, 'type'=> 'required', 'message' => __('Required.', 'bravepop'), 'firstname'=> !$field->value[0] ? true : false, 'lastname'=>  !$field->value[1] ? true : false)));
                  wp_die();
               }
            }
         }
      }
   }

   //error_log('NO ERRORS! CONTINUING....');

   //Get Currently Logged in User Data.
   $current_user = bravepop_getCurrentUser();
   $visitor_country = bravepopup_getvisitorCountry();
   $visitor_ip = bravepop_getVisitorIP();

   //SEND EMAIL TO ADMINS
   if($actionSettings && isset($actionSettings->recieveEmail) && isset($actionSettings->recieveEmail->enable) && $actionSettings->recieveEmail->enable === true){
      if(isset($actionSettings->recieveEmail->emails) && isset($actionSettings->recieveEmail->subject)){
         $custom =  isset($actionSettings->recieveEmail->custom) && $actionSettings->recieveEmail->custom === true ? true : false; 
         $sendto =  $actionSettings->recieveEmail->emails;
         $subject = mb_encode_mimeheader(bravepop_replace_emailShortcodes($actionSettings->recieveEmail->subject, $fieldSettings),"UTF-8");
         $headers = "Content-Type: text/plain; charset=\"iso-8859-1\"";
         
         if($custom && isset($actionSettings->recieveEmail->message) ){
            //User Template Message
            $message = bravepop_replace_emailShortcodes($actionSettings->recieveEmail->message, $fieldSettings);
            $formattedMsg = json_encode($message);
            $theMessage =  str_replace('\n', '\r\n',  $formattedMsg);
            $theMessage = json_decode($theMessage);
         }else{
            //Auto Generated Message
            $theMessage  = "\r\n";
            foreach ((array)$fieldSettings as $key => $field) {
               $defaultKey = isset($field->label) ? $field->label : ''; 
               $defaultKey = !$defaultKey && isset($field->placeholder) ? $field->placeholder : $defaultKey;

               $fieldKey = isset($field->uid) ? $field->uid : $defaultKey;
               $fieldValue = isset($field->value) && is_string($field->value) && $field->value ? strip_tags($field->value) : '';
               $fieldValue = isset($field->value) && is_array($field->value) && $field->value ? strip_tags(implode(", ", $field->value)) : $fieldValue;
               
               if(isset($field->value) && is_array($field->value) && $field->type === 'input' && $field->validation === 'name'){
                  $defaultKey2 = isset($field->secondLabel) ? $field->secondLabel : ''; 
                  $defaultKey2 = !$defaultKey && isset($field->secondPlaceholder) ? $field->secondPlaceholder : $defaultKey2;

                  $fieldKey2 = isset($field->uidl) ? $field->uidl : $defaultKey2;
                  $lastname = isset($field->value[1]) && $field->value[1] ? $field->value[1] : '';
                  $theMessage .= $fieldKey.": ".$fieldValue."\r\n";
                  $theMessage .= $fieldKey2.": ".$lastname."";
                  $theMessage .= "\r\n\r\n";
               }else{
                  if($field->type !== 'step'){
                     $theMessage .= $fieldKey.": ".$fieldValue."";
                     $theMessage .= "\r\n\r\n";
                  }
               }
            }
         }

         if(!empty($actionSettings->recieveEmail->userdata)){
            $user_name = '';
            if(!empty($current_user['name']) && !empty($current_user['username'])){
               $user_name = ': '.$current_user['name'].' ('.$current_user['username'].')';
            }else if(empty($current_user['name']) && !empty($current_user['username'])){
               $user_name = ': '.$current_user['username'];
            }
            $user_type = (!empty($current_user['username']) ?  __('Registered User', 'bravepop') : __('a Visitor ', 'bravepop'));
            $user_country = ($visitor_country ? 'from '.$visitor_country : ' ').($visitor_ip ? ', ip: '. $visitor_ip .'' : '');
            $theMessage .= "------------------------------------------------------------------------------------------------------------------------\r\n";
            $theMessage .= __('Form Submitted by ', 'bravepop').$user_type.$user_name.$user_country ; 
         }

         wp_mail( $sendto, $subject, $theMessage, $headers);

      }
   }


   //SEND EMAIL TO USERS
   if($actionSettings && isset($actionSettings->sendEmail) && isset($actionSettings->sendEmail->enable) && $actionSettings->sendEmail->enable === true){
      $emailAddress = '';
      foreach ((array)$fieldSettings as $key => $field) {
         if(!$emailAddress && isset($field->type) && isset($field->validation) && isset($field->value) && $field->type ==='input' && $field->validation ==='email' && $field->value){
            $emailAddress = $field->value;
         }
      }

      if($emailAddress){
         $sendto =  $emailAddress;
         $subject = mb_encode_mimeheader(bravepop_replace_emailShortcodes($actionSettings->sendEmail->subject, $fieldSettings),"UTF-8");
         $headers = "Content-Type: text/plain; charset=\"iso-8859-1\"";
         $message = bravepop_replace_emailShortcodes($actionSettings->sendEmail->message, $fieldSettings);
         $formattedMsg = json_encode($message);
         $theMessage =  str_replace('\n', '\r\n',  $formattedMsg);
         $theMessage = json_decode($theMessage);
         wp_mail( $sendto, $subject, $theMessage, $headers);
      }
   }

   //Add to Newsletter
   if($actionSettings && isset($actionSettings->newsletter) && isset($actionSettings->newsletter->enable) && $actionSettings->newsletter->enable === true){
      $type = isset($actionSettings->newsletter->enable) ? $actionSettings->newsletter->type : '';
      $listID = isset($actionSettings->newsletter->listID) ? $actionSettings->newsletter->listID : '';
      $emailField = isset($actionSettings->newsletter->emailField) ? $actionSettings->newsletter->emailField : '';
      $nameField = isset($actionSettings->newsletter->nameField) ? $actionSettings->newsletter->nameField : '';
      $phoneField = isset($actionSettings->newsletter->phoneField) ? $actionSettings->newsletter->phoneField : '';

      //error_log($type .' '. $listID .' '. $emailField);
      //error_log(json_encode($actionSettings->newsletter));
      if($type && ($type==='zohocrm' || $listID) && $emailField){
         $emailValue = '';
         $nameValue = '';
         $phoneValue = '';
         //Get the Email and Name values from the FORM
         foreach ((array)$fieldSettings as $key => $field) {
            if($emailField && isset($field->id) && $field->id === $emailField){
               $emailValue = $field->value;
            }
            if($emailField && isset($field->id) && $field->id === $nameField){
               $nameValue = $field->value;
            }
            if($emailField && isset($field->id) && $field->id === $phoneField){
               $phoneValue = $field->value;
            }
         }

         //Finaly Add the User
         if( $emailValue){
            if($type === 'mailchimp'){    $service = new BravePop_Mailchimp();   }
            if($type === 'mailjet'){      $service = new BravePop_Mailjet();   }
            if($type === 'hubspot'){      $service = new BravePop_Hubspot();   }
            if($type === 'constantcontact'){      $service = new BravePop_ConstantContact();   }
            if($type === 'activecampaign'){      $service = new BravePop_ActiveCampaign();   }
            if($type === 'sendgrid'){      $service = new BravePop_SendGrid();   }
            if($type === 'sendinblue'){      $service = new BravePop_SendinBlue();   }
            if($type === 'convertkit'){      $service = new BravePop_ConvertKit();   }
            if($type === 'getresponse'){      $service = new BravePop_GetResponse();   }
            if($type === 'aweber'){      $service = new BravePop_Aweber();   }
            if($type === 'zoho'){      $service = new BravePop_Zoho();   }
            if($type === 'zohocrm'){      $service = new BravePop_ZohoCRM();   }
            if($type === 'mailerlite'){      $service = new BravePop_MailerLite();   }
            if($type === 'moosend'){      $service = new BravePop_Moosend();   }
            if($type === 'mailpoet'){      $service = new BravePop_MailPoet();   }
            if($type === 'campaignmonitor'){      $service = new BravePop_CampaignMonitor();   }
            if($type === 'tnp'){      $service = new BravePop_TNP();   }
            
            if(isset($service)){
               if(!empty($current_user['name']) && empty($nameValue)){  $nameValue = $current_user['name'];  }
               $subScriptionSuccess = $service->add_to_lists($emailValue, $listID, $nameValue, '', $phoneValue);
               if(!$subScriptionSuccess){
                  $emailSent = bravepop_subscription_failed_notificaion($popupID, get_option('admin_email'), $type, $nameValue, $emailValue);
               }
            }
         }


       }

   }

   
   //Send to Zapier/Integromat
   if($actionSettings && isset($actionSettings->webhook) && isset($actionSettings->webhook->enable)  && isset($actionSettings->webhook->url) && $actionSettings->webhook->enable === true && $actionSettings->webhook->url){
      //error_log('PUSH to WebHook');
      $webhook = new BravePop_Webhook();
      $webhook->post($actionSettings->webhook->url, $actionSettings->webhook->type, $fieldSettings, $current_user, $visitor_country, $visitor_ip);
   }

   //FINALLY SEND RESPONSE TO USER-------------------------------------
      $response = array('sent'=> true);
      // Show Custom Message
      if($actionSettings && isset($actionSettings->primaryAction) && $actionSettings->primaryAction === 'content' && isset($actionSettings->primaryActionData->content)){
         $response['primaryAction'] = 'content';
         $response['contentMessage'] = $actionSettings->primaryActionData->content;
         if(isset($actionSettings->primaryActionData->download) && isset($actionSettings->primaryActionData->downloadURL)){
            $response['download'] = true;
            $response['downloadURL'] = $actionSettings->primaryActionData->downloadURL;
         }
         if(!empty($actionSettings->primaryActionData->autoclose) && isset($actionSettings->primaryActionData->autoclosetime)){
            $response['autoclose'] = true;
            $response['autoclosetime'] = $actionSettings->primaryActionData->autoclosetime;
         }
      }
      // Open Another Popup
      if($actionSettings && isset($actionSettings->primaryAction) && $actionSettings->primaryAction === 'popup' && isset($actionSettings->primaryActionData->popup)){
         $response['primaryAction'] = 'popup';
         $response['popupID'] = $actionSettings->primaryActionData->popup;
      }

      // Go to Another Step
      if($actionSettings && isset($actionSettings->primaryAction) && $actionSettings->primaryAction === 'step' && isset($actionSettings->primaryActionData->step)){
         $response['primaryAction'] = 'step';
         $response['step'] = $actionSettings->primaryActionData->step;
      }

      // Redirect User
      if($actionSettings && isset($actionSettings->primaryAction) && $actionSettings->primaryAction === 'redirect' && isset($actionSettings->primaryActionData->redirect)){
         $response['primaryAction'] = 'redirect';
         $response['redirectURL'] = $actionSettings->primaryActionData->redirect;
         $response['redirectAfter'] = isset($actionSettings->primaryActionData->redirectAfter) ? $actionSettings->primaryActionData->redirectAfter : '';
         $response['redirectMessage'] = isset($actionSettings->primaryActionData->redirectMessage) ? $actionSettings->primaryActionData->redirectMessage : '';
      }

      print_r(json_encode($response));

      wp_die();
}


function bravepop_replace_emailShortcodes($message, $fieldValues){
   $finalMessage = $message;
   $regex = "/\[(.*?)\]/";
   preg_match_all($regex, $message, $matches);
   for ($i=0; $i < count($matches[1]) ; $i++) { 
      $match = $matches[1][$i];
      $newvalue = bravepop_get_emailShortcode_value($match, $fieldValues);
      $finalMessage = str_replace($matches[0][$i], $newvalue, $finalMessage);
   }
   return $finalMessage ;
}

function bravepop_get_emailShortcode_value($key, $fieldValues){
   $fieldKey = str_replace(array( '[', ']' ), '', $key);
   $fieldValue = '';
   foreach ($fieldValues as $key => $field) {
      if(isset($field->$fieldKey)){
         $fieldValue = $field->$fieldKey;
      }
   }
   return $fieldValue;
}