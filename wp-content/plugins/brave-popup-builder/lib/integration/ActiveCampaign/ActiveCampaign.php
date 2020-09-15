<?php
if ( ! class_exists( 'BravePop_ActiveCampaign' ) ) {
   
   class BravePop_ActiveCampaign {

      function __construct() {

         $braveSettings = get_option('_bravepopup_settings');
         $integrations = $braveSettings && isset($braveSettings['integrations']) ? $braveSettings['integrations'] : array() ;
         $this->api_key = isset($integrations['activecampaign']->api)  ? $integrations['activecampaign']->api  : '';
         $this->api_url = isset($integrations['activecampaign']->url)  ? $integrations['activecampaign']->url  : '';
      }


      public function get_lists($apiURL='', $apiKey=''){
         $apiKey  = $apiKey ? $apiKey : $this->api_key;
         $apiURL  = $apiURL ? $apiURL : $this->api_url;

         $args = array(
            'headers' => array(
               'Api-Token' => $apiKey
            )
         );
         $response = wp_remote_get( $apiURL.'/api/3/lists', $args );
         if( is_wp_error( $response ) ) {
            return false; // Bail early
         }

         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );

         if($data && isset($data->lists)){
            $lists = $data->lists;
            $finalLists = array();
            if($lists && is_array($lists)){
               foreach ($lists as $key => $list) {
                  $listItem = new stdClass();
                  $listItem->id = isset($list->id) ? $list->id : '';
                  $listItem->name = isset($list->name) ? $list->name : '';
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
         if(!$this->api_key || !$this->api_url){ 
            //error_log('API KEY or URL Missing!');
            return false;
         }
         $firstname = trim($fname);
         $lastname = trim($lname);
         $fullname = $firstname;

         //Convert firstname and lastname to Fullname. 
         if($firstname && $lastname){
            $fullname = $firstname.' '.$lastname;
         }
         
         //First Add User in ActiveCampaign
         $addUserargs = array(
            'method' => 'POST',
            'headers' => array(
               'content-type' => 'application/json',
               'Api-Token' => $this->api_key
            ),
            'body' => json_encode(array(
               'contact' => array(
                  "email"=> $email,
                  "firstName"=> $firstname,
                  "lastName"=> $lastname
               )
            ))
         );

         $response = wp_remote_post( $this->api_url.'/api/3/contacts', $addUserargs );
         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );

         //error_log(json_encode($response));

         if($data && isset($data->contact) && isset($data->contact->id)){
            //Then Add User to a List in ActiveCampaign
            $user_id = $data->contact->id;

            $userToList = array(
               'method' => 'POST',
               'headers' => array(
                  'content-type' => 'application/json',
                  'Api-Token' => $this->api_key
               ),
               'body' => json_encode(array(
                  'contactList' => array(
                     'list' => $list_id,
                     'contact' => $user_id,
                     'status' => 1
                  ),
               ))
            );
            $listresponse = wp_remote_post( $this->api_url.'/api/3/contactLists', $userToList );
            $listbody = wp_remote_retrieve_body( $listresponse );
            $listdata = json_decode( $listbody );

            //error_log(json_encode($listresponse));
            if($listdata && isset($listdata->contacts)){
               return $listdata->contacts; 
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