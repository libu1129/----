<?php
if ( ! class_exists( 'BravePop_Mailchimp' ) ) {
   
   class BravePop_Mailchimp {

      function __construct() {
         $braveSettings = get_option('_bravepopup_settings');
         $integrations = $braveSettings && isset($braveSettings['integrations']) ? $braveSettings['integrations'] : array() ;
         $this->api_key = isset($integrations['mailchimp']->api)  ? $integrations['mailchimp']->api  : '';
         $this->dc = substr($this->api_key,strpos($this->api_key,'-')+1); 
      }

      public function get_lists($apiKey=''){
         $apiKey  = $apiKey ? $apiKey : $this->api_key;
         $dc      = $apiKey ?substr($apiKey,strpos($apiKey,'-')+1) : $this->dc;

         $args = array(
            'headers' => array(
               'Authorization' => 'Basic ' . base64_encode( 'user:'.  $apiKey )
            )
         );
         $response = wp_remote_get( 'https://'.$dc.'.api.mailchimp.com/3.0/lists/', $args );
         if( is_wp_error( $response ) ) {
            return false; // Bail early
         }

         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );
         $lists = $data->lists;
         $finalLists = array();
         //error_log($apiKey . $body);
         
         if($lists && is_array($lists)){
            
            foreach ($lists as $key => $list) {
               $listItem = new stdClass();
               $listItem->id = isset($list->id) ? $list->id : '';
               $listItem->name = isset($list->name) ? $list->name : '';
               $listItem->count = isset($list->stats) && isset($list->stats->member_count)  ? $list->stats->member_count : 0;
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

         $status = 'subscribed'; // subscribed, cleaned, pending
         
         $firstname = trim($fname);
         $lastname = trim($lname);

         //Convert Full name to firstname and lastname. 
         if(!$lastname && $firstname && strpos($firstname, ' ') !== false){
            $splitted = explode(" ",$firstname);
            $firstname = $splitted[0] ? $splitted[0] : '';
            $lastname = $splitted[1] ? $splitted[1] : '';
         }
         

         $args = array(
            'method' => 'PUT',
            'headers' => array(
               'Authorization' => 'Basic ' . base64_encode( 'user:'. $this->api_key )
            ),
            'body' => json_encode(array(
               'email_address' => $email,
               'merge_fields'  => [
                  'FNAME'     => $firstname,
                  'LNAME'     => $lastname
               ],
               'status'        => 'subscribed'
            ))
         );


         $response = wp_remote_post( 'https://' . $this->dc . '.api.mailchimp.com/3.0/lists/' . $list_id . '/members/' . md5(strtolower($email)), $args );
         

         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );
         if($data && isset($data->id)){
            return true; 
         }else{
            return false;
         }
      }


   }

}
?>