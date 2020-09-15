<?php
if ( ! class_exists( 'BravePop_SendinBlue' ) ) {

   class BravePop_SendinBlue {

      function __construct() {
         $braveSettings = get_option('_bravepopup_settings');
         $integrations = $braveSettings && isset($braveSettings['integrations']) ? $braveSettings['integrations'] : array() ;
         $this->api_key = isset($integrations['sendinblue']->api)  ? $integrations['sendinblue']->api  : '';
      }


      public function get_lists($apiKey=''){
         $apiKey  = $apiKey ? $apiKey : $this->api_key;
         $args = array(
            'headers' => array(
               'api-key' => $apiKey,
            )
         );
         $response = wp_remote_get( 'https://api.sendinblue.com/v3/contacts/lists', $args );
         if( is_wp_error( $response ) ) {
            return false; // Bail early
         }

         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );
         //error_log($body);
         if($data && isset($data->lists)){
            $lists = $data->lists;
            $finalLists = array();
            if($lists && is_array($lists)){
               foreach ($lists as $key => $list) {
                  $listItem = new stdClass();
                  $listItem->id = isset($list->id) ? $list->id : '';
                  $listItem->name = isset($list->name) ? $list->name : '';
                  $listItem->count = isset($list->totalSubscribers)  ? $list->totalSubscribers : 0;
                  $finalLists[] = $listItem;
               }
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
            //error_log('API KEY Missing!');
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

         $contactAttr = new stdClass();
         $contactAttr->FIRSTNAME = $firstname;
         $contactAttr->LASTNAME = $lastname;

         $addUserargs = array(
            'method' => 'POST',
            'headers' => array(
               'content-type' => 'application/json',
               'api-key' => $this->api_key,
            ),
            'body' => json_encode(array(
               'listIds'   => array((int)$list_id),
               'email'     => $email,
               'attributes' => array(
                  'FIRSTNAME'=> $firstname,
                  'LASTNAME'=> $lastname,
               )
            ))
         );

         $response = wp_remote_post( 'https://api.sendinblue.com/v3/contacts', $addUserargs );
         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );
         //error_log(json_encode($response));

         if($data && isset($data->id)){
            //error_log(json_encode($data));
            return $data->id; 
         }else{
            return false;
         }


      }


   }

}
?>