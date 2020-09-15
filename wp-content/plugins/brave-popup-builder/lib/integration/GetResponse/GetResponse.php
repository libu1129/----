<?php
if ( ! class_exists( 'BravePop_GetResponse' ) ) {
   
   class BravePop_GetResponse {

      function __construct() {
         $braveSettings = get_option('_bravepopup_settings');
         $integrations = $braveSettings && isset($braveSettings['integrations']) ? $braveSettings['integrations'] : array() ;
         $this->access_key = isset($integrations['getresponse']->access)  ? $integrations['getresponse']->access  : '';
      }


      public function get_lists($accessKey=''){
         $accessKey = $accessKey ? $accessKey : $this->access_key;
         $args = array(
            'headers' => array(
               'Authorization' => 'Bearer ' . $accessKey
            )
         );
         $response = wp_remote_get( 'https://api.getresponse.com/v3/campaigns', $args );
         if( is_wp_error( $response ) ) {
            return false; // Bail early
         }

         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );

         //error_log($body);
         if(!$data || (isset($data->httpStatus) && $data->httpStatus === 401)){
            return false;
         }

         if($data){
            $lists = $data;
            $finalLists = array();
            if($lists && is_array($lists)){
               foreach ($lists as $key => $list) {
                  $listItem = new stdClass();
                  $listItem->id = isset($list->campaignId) ? $list->campaignId : '';
                  $listItem->name = isset($list->name) ? $list->name : '';
                  $finalLists[] = $listItem;
               }
            }
            return json_encode($finalLists);
         }

      }


      public function add_to_lists($email, $list_id, $fname='', $lname='', $phone=''){
         if(!$email || !$list_id){ return null; }
         if(!$this->access_key){ 
            //error_log('ACCESSE KEY Missing!');
            return false;
         }
         $firstname = trim($fname);
         $lastname = trim($lname);
         $fullname = $firstname;

         //Convert firstname and lastname to Fullname. 
         if($firstname && $lastname){
            $fullname = $firstname.' '.$lastname;
         }
         
         $campaign = new stdClass();
         $campaign->campaignId = $list_id;

         $args = array(
            'method' => 'POST',
            'headers' => array(
               'content-type' => 'application/json',
               'Authorization' => 'Bearer ' . $this->access_key,
               'accept-encoding'=> '', 
            ),
            'body' => json_encode(array(
               'campaign'  => $campaign,
               'name'      => $fullname,
               'email'    => $email
            ))
         );

         $response = wp_remote_post( 'https://api.getresponse.com/v3/contacts', $args );
         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );


         if($response && isset($response['response']) && isset($response['response']['code']) && $response['response']['code'] === 202){
            return true; 
         }else{
            return false;
         }

      }


   }

}
?>