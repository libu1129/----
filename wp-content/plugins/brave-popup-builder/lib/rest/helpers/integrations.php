<?php


function bravepop_get_integration_lists($service='', $apiKey='', $secretKey='', $accessToken='', $apiURL='', $refresh='', $domain=''){
   //error_log('get_integration_lists: '.$service . $apiKey . $secretKey . $accessToken. $apiURL .$refresh);
   if(!$service){ return false; }
   $currentSettings = get_option('_bravepopup_settings');
   $currentIntegrations = $currentSettings && isset($currentSettings['integrations']) ? $currentSettings['integrations'] : array() ;

   if(!$apiKey && !$secretKey && !$accessToken && !$apiURL && !$refresh){
      $apiKey = isset($currentIntegrations[$service]->api)  ? $currentIntegrations[$service]->api  : '';
      $secretKey = isset($currentIntegrations[$service]->secret)  ? $currentIntegrations[$service]->secret  : '';
      $accessToken = isset($currentIntegrations[$service]->access)  ? $currentIntegrations[$service]->access  : '';
      $apiURL = isset($currentIntegrations[$service]->url)  ? $currentIntegrations[$service]->url  : '';
      $refresh = isset($currentIntegrations[$service]->refresh)  ? $currentIntegrations[$service]->refresh  : '';
      $domain = isset($currentIntegrations[$service]->domain)  ? $currentIntegrations[$service]->domain  : '';
   }

   if($service === 'mailchimp') { 
      $mailchimp = new BravePop_Mailchimp();  
      $lists = $mailchimp->get_lists($apiKey);
      return $lists;
   }
   if($service === 'mailjet')   { 
      $mailjet =   new BravePop_Mailjet();
      $lists = $mailjet->get_lists($apiKey, $secretKey);
      //error_log(json_encode($lists));
      return $lists;
   }
   if($service === 'sendgrid')   { 
      $sendgrid =   new BravePop_SendGrid();
      $lists = $sendgrid->get_lists($apiKey);
      //error_log(json_encode($lists));
      return $lists;
   }
   if($service === 'sendinblue')   { 
      $sendinblue =   new BravePop_SendinBlue();
      $lists = $sendinblue->get_lists($apiKey);
      //error_log(json_encode($lists));
      return $lists;
   }
   if($service === 'hubspot')   { 
      $hubspot =   new BravePop_Hubspot();
      $lists = $hubspot->get_lists($apiKey);
      //error_log(json_encode($lists));
      return $lists;
   }
   if($service === 'activecampaign')   { 
      $activeCamp =   new BravePop_ActiveCampaign();
      $lists = $activeCamp->get_lists($apiURL, $apiKey);
      //error_log(json_encode($lists));
      return $lists;
   }
   if($service === 'convertkit')   { 
      $convertkit =   new BravePop_ConvertKit();
      $lists = $convertkit->get_lists($apiKey);
      //error_log(json_encode($lists));
      return $lists;
   }
   if($service === 'constantcontact')   { 
      $constantcontact =   new BravePop_ConstantContact();
      $lists = $constantcontact->get_lists($apiKey, $secretKey, $accessToken);
      //error_log(json_encode($lists));
      return $lists;
   }
   if($service === 'getresponse')   { 
      $getresponse =   new BravePop_GetResponse();
      $lists = $getresponse->get_lists( $accessToken);
      //error_log(json_encode($lists));
      return $lists;
   }
   if($service === 'aweber')   { 
      $aweber =   new BravePop_Aweber();
      //error_log(json_encode($refresh));
      $lists = $aweber->get_lists($refresh);
      //error_log(json_encode($lists));
      return $lists;
   }
   if($service === 'zoho')   { 
      $zoho =   new BravePop_Zoho();
      $lists = $zoho->get_lists( $apiKey, $secretKey, $refresh, $domain);
      //error_log(json_encode($lists));
      return $lists;
   }
   if($service === 'mailerlite')   { 
      $mailjet =   new BravePop_MailerLite();
      $lists = $mailjet->get_lists($apiKey);
      //error_log(json_encode($lists));
      return $lists;
   }
   if($service === 'moosend')   { 
      $moosend =   new BravePop_Moosend();
      $lists = $moosend->get_lists($apiKey);
      //error_log(json_encode($lists));
      return $lists;
   }
   if($service === 'campaignmonitor')   { 
      $moosend =   new BravePop_CampaignMonitor();
      $lists = $moosend->get_lists($apiKey, $secretKey);
      //error_log(json_encode($lists));
      return $lists;
   }
   if($service === 'mailpoet' && class_exists(\MailPoet\API\API::class)) { 
      $mailpoet = new BravePop_MailPoet();  
      $lists = $mailpoet->get_lists();
      return $lists;
   }
   if($service === 'tnp' && class_exists('TNP')) { 
      $the_newsletter_plugin = new BravePop_TNP();  
      $lists = $the_newsletter_plugin->get_lists();
      return $lists;
   }
}

function bravepop_update_integrations( $integrations ){
   $currentSettings = get_option('_bravepopup_settings');
   $currentIntegrations = $currentSettings && isset($currentSettings['integrations']) ? $currentSettings['integrations'] : array() ;
   
   $updatedIntegrations = $currentIntegrations;
   if($integrations){
      $decodedIntegration = json_decode($integrations);
      
      if(isset($decodedIntegration->service)){
         
         $apiSettings = new stdClass();
         $apiSettings->api = isset($decodedIntegration->api) ? $decodedIntegration->api : '';
         $apiSettings->secret = isset($decodedIntegration->secret) ? $decodedIntegration->secret : '';
         $apiSettings->access = isset($decodedIntegration->access) ? $decodedIntegration->access : '';
         $apiSettings->url = isset($decodedIntegration->url) ? $decodedIntegration->url : '';
         $apiSettings->refresh = isset($decodedIntegration->refresh) ? $decodedIntegration->refresh : '';
         $apiSettings->domain = isset($decodedIntegration->domain) ? $decodedIntegration->domain : '';
         $updatedIntegrations[$decodedIntegration->service] = $apiSettings;
         $validateIntegration = false;
         
         if($decodedIntegration->service === 'zoho'){
            $zoho =   new BravePop_Zoho();
            $accessToken = $zoho->get_access_token( $apiSettings->api, $apiSettings->secret, $apiSettings->refresh, $apiSettings->domain);
            $validateIntegration = $accessToken;
         }else{
            $validateIntegration = bravepop_get_integration_lists($decodedIntegration->service, $apiSettings->api, $apiSettings->secret, $apiSettings->access, $apiSettings->url, $apiSettings->refresh, $apiSettings->domain );
         }

         if($validateIntegration !== false){
            //error_log('Lists Found: '.$validateIntegration);
            $updatedIntegrations[$decodedIntegration->service]->enabled = true;
            $settings = array( 'integrations' => $updatedIntegrations );
            BravePopup_Settings::save_settings( $settings );
         }else{
            error_log('NO LISTSS FOUND!!!!!!');
            $updatedIntegrations[$decodedIntegration->service]->enabled = false;
            $settings = array( 'integrations' => $updatedIntegrations );
            BravePopup_Settings::save_settings( $settings );
            return new WP_REST_Response(array('error'=>'Invalid API key'));
         }

      }
   }

   return new WP_REST_Response(get_option('_bravepopup_settings'));
}

function bravepop_remove_integration( $service ){
   if(!$service){  return false;  }
   $currentSettings = get_option('_bravepopup_settings');
   $currentIntegrations = $currentSettings && isset($currentSettings['integrations']) ? $currentSettings['integrations'] : array() ;
   
   if(isset($currentIntegrations[$service])){
      unset($currentIntegrations[$service]);
   }
   //error_log(json_encode($currentIntegrations));

   $settings = array( 'integrations' => $currentIntegrations );
   BravePopup_Settings::save_settings( $settings );

   return new WP_REST_Response(array('removed'=>true));
}


add_action('wp_ajax_bravepop_ajax_zoho_init_token', 'bravepop_ajax_zoho_init_token', 0);
add_action('wp_ajax_nopriv_bravepop_ajax_zoho_init_token', 'bravepop_ajax_zoho_init_token');
function bravepop_ajax_zoho_init_token(){
   if(empty($_POST['client_id']) || empty($_POST['client_secret']) || empty($_POST['code']) || empty($_POST['domain'])){ wp_die(); }
   $zohoDomain = isset($_POST['domain']) ? $_POST['domain'] : 'com';
   $args = array( 
      'method' => 'POST',
      'headers' => array( 
         'Content-Type' => 'application/x-www-form-urlencoded'  
      ),
   );

   $response = wp_remote_post( 'https://accounts.zoho.'.$zohoDomain.'/oauth/v2/token?&client_id='.$_POST['client_id'].'&client_secret='.$_POST['client_secret'].'&code='.$_POST['code'].'&grant_type=authorization_code', $args );
   $body = wp_remote_retrieve_body( $response );
   $data = json_decode( $body );
   
   if(isset($data->refresh_token)){
      //error_log($data->refresh_token);
      echo $data->refresh_token;
   }else{
      echo 'FALSE';
   }
   wp_die();
}

function bravepop_subscription_failed_notificaion($popupID, $emailAddress, $service, $fullName='', $subEmailAddress){
   if(!$popupID || !$emailAddress || !$subEmailAddress || !$service){ return false; }
   $firstname = $fullName; $lastname = '';
   if(( strpos($fullName, ' ') !== false)){
      $fullname_parts = preg_split('/\s+/', $fullName);
      $firstname = $fullname_parts[0] ? $fullname_parts[0] : '';
      $lastname = $fullname_parts[1] ? $fullname_parts[1] : '';
   }
   $popupName = get_the_title($popupID);
   //error_log($popupName .' '. $emailAddress .' '. $subEmailAddress .' '. $service);
   if($popupName && $emailAddress && $subEmailAddress && $service){
      $sendto =  $emailAddress;
      $subject = '[Brave][Error] Newsletter Subscription Failed';
      $headers = "Content-Type: text/plain; charset=\"iso-8859-1\"";
      $theMessage = "Hi,\r\n\r\nYour Popup '".$popupName."' failed to subscribe a visitor to your Newsletter mailing list (Due to ".$service." API issues, incomplete data or other reasons).\r\nPlease add the visitor to list manually from your ".$service." Dashboard:  \r\n\r\nFirst Name: ".($firstname ? $firstname: 'Not Given')."\r\nLast Name: ".($lastname ? $lastname: 'Not Given')."\r\nEmail Address: ".$subEmailAddress."\r\n\r\nMessage Sent By Brave Popup Builder Plugin.\r\n".get_bloginfo( 'name' )."";
      wp_mail( $sendto, $subject, $theMessage, $headers);
   }
}