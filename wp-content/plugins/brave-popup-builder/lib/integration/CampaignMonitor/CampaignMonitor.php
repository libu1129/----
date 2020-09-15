<?php
if ( ! class_exists( 'BravePop_CampaignMonitor' ) ) {
   
   class BravePop_CampaignMonitor {

      function __construct() {
         $braveSettings = get_option('_bravepopup_settings');
         $integrations = $braveSettings && isset($braveSettings['integrations']) ? $braveSettings['integrations'] : array() ;
         $this->api_key = isset($integrations['campaignmonitor']->api)  ? $integrations['campaignmonitor']->api  : '';
         $this->clientID = isset($integrations['campaignmonitor']->secret)  ? $integrations['campaignmonitor']->secret  : '';
      }


      public function get_lists($apiKey='', $clientID=''){
         $apiKey  = $apiKey ? $apiKey : $this->api_key;
         $clientID  = $clientID ? $clientID : $this->clientID;
         if(!$apiKey || !$clientID){ return; }

         $args = array(
            'method' => 'GET',
            'headers' => array(  'content-type' => 'application/json', 'Authorization' => 'Basic '.base64_encode($apiKey.":x" ) ),
         );

         $response = wp_remote_get( 'https://api.createsend.com/api/v3.2/clients/'.$clientID.'/lists.json', $args );
         if( is_wp_error( $response ) ) {
            return false; // Bail early
         }
         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );
         
         if($data && is_array($data) && isset($data[0])){
            $lists = $data;
            $finalLists = array();
            if($lists && is_array($lists)){
               foreach ($lists as $key => $list) {
                  $listItem = new stdClass();
                  $listItem->id = isset($list->ListID) ? $list->ListID : '';
                  $listItem->name = isset($list->Name) ? $list->Name : '';
                  $finalLists[] = $listItem;
               }
            }
            // error_log(json_encode($finalLists));
            return json_encode($finalLists);
         }else{
            return false;
         }

      }


      public function add_to_lists($email, $list_id, $fname='', $lname='', $phone=''){
         if(!$email || !$list_id){ return null; }
         if(!$this->api_key){ 
            //error_log('API KEY or SECRET Missing!');
            return false;
         }

         $firstname = trim($fname);
         $lastname = trim($lname);
         $fullname = $firstname.($lname ? ' '.$lname : $lname);

         $args = array(
            'method' => 'POST',
            'headers' => array(
               'content-type' => 'application/json', 
               'Authorization' => 'Basic '.base64_encode($this->api_key.":x" )
            ),
            'body' => json_encode(array(
               'EmailAddress'     => $email,
               'Name'      => $firstname,
               'ConsentToTrack' => 'Yes'
            ))
         );
         
         $response = wp_remote_post( 'https://api.createsend.com/api/v3.2/subscribers/'.$list_id.'.json', $args );
         
         if(isset($response['response']['code']) && $response['response']['code'] === 201){
            //error_log('##### USER ADDED #####');
            return $response['response']['code']; 
         }else{
            return false;
         }

      }


   }

}
?>