<?php
if ( ! class_exists( 'BravePop_MailerLite' ) ) {
   
   class BravePop_MailerLite {

      function __construct() {
         $braveSettings = get_option('_bravepopup_settings');
         $integrations = $braveSettings && isset($braveSettings['integrations']) ? $braveSettings['integrations'] : array() ;
         $this->api_key = isset($integrations['mailerlite']->api)  ? $integrations['mailerlite']->api  : '';
      }

      public function get_lists($apiKey=''){
         $apiKey  = $apiKey ? $apiKey : $this->api_key;

         $args = array(
            'headers' => array(
               'X-MailerLite-ApiKey' => $apiKey
            )
         );
         $response = wp_remote_get( 'https://api.mailerlite.com/api/v2/groups', $args );
         if( is_wp_error( $response ) ) {
            return false; // Bail early
         }

         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );
         $lists = $data;
         $finalLists = array();

         if($lists && is_array($lists)){
            
            foreach ($lists as $key => $list) {
               $listItem = new stdClass();
               $listItem->id = isset($list->id) ? $list->id : '';
               $listItem->name = isset($list->name) ? $list->name : '';
               $listItem->count = isset($list->total)  ? $list->total : 0;
               $finalLists[] = $listItem;
            }

            //error_log(json_encode($finalLists));
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

         //Convert Full name to firstname and lastname. 
         if(!$lastname && $firstname && strpos($firstname, ' ') !== false){
            $splitted = explode(" ",$firstname);
            $firstname = $splitted[0] ? $splitted[0] : '';
            $lastname = $splitted[1] ? $splitted[1] : '';
         }
         

         $args = array(
            'method' => 'POST',
            'headers' => array(
               'content-type' => 'application/json',
               'X-MailerLite-ApiKey' => $this->api_key
            ),
            'body'=> '{"subscribers": [{"email": "'.$email.'", "fields": {"name": "'.$fname.'"} }] }'
         );


         $response = wp_remote_post( 'https://api.mailerlite.com/api/v2/groups/'.$list_id.'/subscribers/import', $args );
                  
         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );
         //error_log($body);
         if($data && isset($data->errors) && count($data->errors) === 0){
            return true; 
         }else{
            return false;
         }
      }


   }

}
?>