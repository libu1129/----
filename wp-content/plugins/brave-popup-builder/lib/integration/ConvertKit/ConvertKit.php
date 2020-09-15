<?php
if ( ! class_exists( 'BravePop_ConvertKit' ) ) {
   
   class BravePop_ConvertKit {

      function __construct() {
         $braveSettings = get_option('_bravepopup_settings');
         $integrations = $braveSettings && isset($braveSettings['integrations']) ? $braveSettings['integrations'] : array() ;
         $this->api_key = isset($integrations['convertkit']->api)  ? $integrations['convertkit']->api  : '';
      }


      public function get_lists($apiKey=''){
         $apiKey  = $apiKey ? $apiKey : $this->api_key;
         $args = array(
            'method' => 'GET',
            'user-agent'  => 'Mozilla/5.0 (Windows; U; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727)',
            'headers' => array(
               'content-type' => 'application/json',
               'accept-encoding'=> '', //Without Specifying empty accept-encoding convertkit sends compressed data which breaks the response.
            ),
         );

         $response = wp_remote_get( 'https://api.convertkit.com/v3/forms?api_key='.$apiKey, $args );
         if( is_wp_error( $response ) ) {
            return false; // Bail early
         }
         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );

         //Alternative File_get_contents---------
            // $response = file_get_contents('https://api.convertkit.com/v3/forms?api_key='.$this->api_key);
            // if( is_wp_error( $response ) ) {
            //    return false; // Bail early
            // }
            // $data = json_decode( $response );
         //---------------------
         

         if($data && isset($data->forms)){
            $lists = $data->forms;
            $finalLists = array();
            if($lists && is_array($lists)){
               foreach ($lists as $key => $list) {
                  $listItem = new stdClass();
                  $listItem->id = isset($list->id) ? $list->id : '';
                  $listItem->name = isset($list->name) ? $list->name : '';
                  $listItem->count = isset($list->SubscriberCount)  ? $list->SubscriberCount : 0;
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
            //error_log('API KEY or SECRET Missing!');
            return false;
         }
         $firstname = trim($fname);
         $lastname = trim($lname);
         $fullname = $firstname;

         $args = array(
            'method' => 'POST',
            'headers' => array(
               'content-type' => 'application/json',
               'accept-encoding'=> '', //Without Specifying empty accept-encoding convertkit sends compressed data which breaks the response.
            ),
            'body' => json_encode(array(
               'email'     => $email,
               'first_name'      => $firstname,
               'api_key'    => $this->api_key
            ))
         );

         $response = wp_remote_post( 'https://api.convertkit.com/v3/forms/' . $list_id . '/subscribe', $args );
         

         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );
         //error_log('Add Response: '.json_encode($response));
         if($data && isset($data->subscription)){
            //error_log('##### USER ADDED #####');
            return $data->subscription; 
         }else{
            return false;
         }

      }



   }

}
?>