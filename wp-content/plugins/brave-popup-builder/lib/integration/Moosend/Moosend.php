<?php
if ( ! class_exists( 'BravePop_Moosend' ) ) {
   
   class BravePop_Moosend {

      function __construct() {
         $braveSettings = get_option('_bravepopup_settings');
         $integrations = $braveSettings && isset($braveSettings['integrations']) ? $braveSettings['integrations'] : array() ;
         $this->api_key = isset($integrations['moosend']->api)  ? $integrations['moosend']->api  : '';
      }


      public function get_lists($apiKey=''){
         $apiKey  = $apiKey ? $apiKey : $this->api_key;
         $args = array(
            'method' => 'GET',
            'headers' => array('content-type' => 'application/json'),
         );

         $response = wp_remote_get( 'https://api.moosend.com/v3/lists.json?apikey='.$apiKey.'&WithStatistics=true&ShortBy=CreatedOn&SortMethod=ASC', $args );
         if( is_wp_error( $response ) ) {
            return false; // Bail early
         }
         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );

         if($data && isset($data->Context->MailingLists)){
            $lists = $data->Context->MailingLists;
            $finalLists = array();
            if($lists && is_array($lists)){
               foreach ($lists as $key => $list) {
                  $listItem = new stdClass();
                  $listItem->id = isset($list->ID) ? $list->ID : '';
                  $listItem->name = isset($list->Name) ? $list->Name : '';
                  $listItem->count = isset($list->ActiveMemberCount)  ? $list->ActiveMemberCount : 0;
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
         $fullname = $firstname.' '.$lastname;

         $args = array(
            'method' => 'POST',
            'headers' => array('content-type' => 'application/json'),
            'body' => json_encode(array(
               'Email'     => $email,
               'Name'      => trim($fullname),
               //'CustomFields'    => array("Country=USA")
            ))
         );

         $response = wp_remote_post( 'https://api.moosend.com/v3/subscribers/'.$list_id.'/subscribe.json?apikey='.$this->api_key, $args );
         
         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );

         if($data && isset($data->Context) && isset($data->Context->ID)){
            //error_log('##### USER ADDED ##### '. $data->Context->ID);
            return $data->Context->ID; 
         }else{
            //error_log('##### ERROR '. $body);
            return false;
         }

      }

   }

}
?>