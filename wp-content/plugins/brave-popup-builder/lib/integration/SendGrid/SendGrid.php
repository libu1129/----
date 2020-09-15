<?php
if ( ! class_exists( 'BravePop_SendGrid' ) ) {

   //SENDGRID API
   //https://sendgrid.api-docs.io/v3.0/lists/get-all-lists
   
   class BravePop_SendGrid {

      function __construct() {
         $braveSettings = get_option('_bravepopup_settings');
         $integrations = $braveSettings && isset($braveSettings['integrations']) ? $braveSettings['integrations'] : array() ;
         $this->api_key = isset($integrations['sendgrid']->api)  ? $integrations['sendgrid']->api  : '';
      }


      public function get_lists($apiKey=''){
         $apiKey  = $apiKey ? $apiKey : $this->api_key;
         $args = array(
            'headers' => array(
               'Authorization' => 'Bearer ' . $apiKey,
            )
         );
         $response = wp_remote_get( 'https://api.sendgrid.com/v3/marketing/lists', $args );
         if( is_wp_error( $response ) ) {
            return false; // Bail early
         }

         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );
         if($data && isset($data->result)){
            $lists = $data->result;
            $finalLists = array();
            if($lists && is_array($lists)){
               foreach ($lists as $key => $list) {
                  $listItem = new stdClass();
                  $listItem->id = isset($list->id) ? $list->id : '';
                  $listItem->name = isset($list->name) ? $list->name : '';
                  $listItem->count = isset($list->contact_count)  ? $list->contact_count : 0;
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


         $contacts = array();
         $contact = new stdClass();
         $contact->email = $email;
         $contact->first_name = $firstname;
         $contact->last_name = $lastname;
         $contacts[] = $contact;

         $addUserargs = array(
            'method' => 'PUT',
            'headers' => array(
               'content-type' => 'application/json',
               'Authorization' => 'Bearer ' . $this->api_key,
            ),
            'body' => json_encode(array(
               'list_ids'        => array($list_id),
               'contacts'        => $contacts,
            ))
         );

         $response = wp_remote_post( 'https://api.sendgrid.com/v3/marketing/contacts', $addUserargs );
         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );

         //error_log(json_encode($response));
         if($data && isset($data->job_id)){
            return $data->job_id; 
         }else{
            return false;
         }


      }


   }

}
?>