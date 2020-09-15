<?php
if ( ! class_exists( 'BravePop_Hubspot' ) ) {

   class BravePop_Hubspot {

      function __construct() {
         $braveSettings = get_option('_bravepopup_settings');
         $integrations = $braveSettings && isset($braveSettings['integrations']) ? $braveSettings['integrations'] : array() ;
         $this->api_key = isset($integrations['hubspot']->api)  ? $integrations['hubspot']->api  : '';
      }


      public function get_lists($apiKey=''){
         $apiKey  = $apiKey ? $apiKey : $this->api_key;
         if(!$apiKey){ return false; }

         $response = wp_remote_get( 'https://api.hubapi.com/contacts/v1/lists/static?count=30&hapikey='.$apiKey);
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
                  $listItem->id = isset($list->listId) ? $list->listId : '';
                  $listItem->name = isset($list->name) ? $list->name : '';
                  $listItem->count = isset($list->metaData) && isset($list->metaData->size) ? $list->metaData->size : 0;
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

         //Convert Full name to firstname and lastname. 
         if(!$lastname && $firstname && strpos($firstname, ' ') !== false){
            $splitted = explode(" ",$firstname);
            $firstname = $splitted[0] ? $splitted[0] : '';
            $lastname = $splitted[1] ? $splitted[1] : '';
         }


         $addUserargs = array(
            'method' => 'POST',
            'headers' => array(
               'content-type' => 'application/json',
            ),
            'body' => json_encode(array(
               'properties' => array(
                  array( "property"=> "email",  "value"=> $email ),
                  array( "property"=> "firstname", "value"=> $firstname ),
                  array( "property"=> "lastname",  "value"=> $lastname )
               )
            ))
         );

         $response = wp_remote_post( 'https://api.hubapi.com/contacts/v1/contact/?hapikey='.$this->api_key, $addUserargs );
         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );

         //error_log(json_encode($response));

         if($data && isset($data->vid)){
            //error_log(json_encode($data));
            $vid = $data->vid;

            $userToList = array(
               'method' => 'POST',
               'headers' => array(
                  'content-type' => 'application/json',
               ),
               'body' => json_encode(array(
                  'vids' => array($vid)
               ))
            );
            $listresponse = wp_remote_post( 'https://api.hubapi.com/contacts/v1/lists/'.$list_id.'/add?hapikey='.$this->api_key, $userToList );
            $listbody = wp_remote_retrieve_body( $listresponse );
            $listdata = json_decode( $listbody );
            if($listdata && isset($listdata->updated)){
               return $listdata->updated; 
            }else{
               return false;
            }

         }else{
            return false;
         }


      }


   }

}
?>