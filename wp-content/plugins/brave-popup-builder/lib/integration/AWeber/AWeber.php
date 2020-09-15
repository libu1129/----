<?php
if ( ! class_exists( 'BravePop_Aweber' ) ) {
   
   class BravePop_Aweber {

      function __construct() {
         $braveSettings = get_option('_bravepopup_settings');
         $integrations = $braveSettings && isset($braveSettings['integrations']) ? $braveSettings['integrations'] : array() ;
         $this->api_secret = isset($integrations['aweber']->secret)  ? $integrations['aweber']->secret  : '';
         $this->refresh_token = isset($integrations['aweber']->refresh)  ? $integrations['aweber']->refresh  : '';
      }

      public function get_accoutnID($access_token){
         //First Get the AccountID
         $headerArgs = array( 'headers' => array(  'Authorization' => 'Bearer ' . $access_token ) );
         $response = wp_remote_get( 'https://api.aweber.com/1.0/accounts', $headerArgs );
         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );
         if(isset($data->entries) && is_array($data->entries) && $data->entries[0]){
            return isset($data->entries[0]->id) ? $data->entries[0]->id : '';
         }else{
            return '';
         }
      }

      public function get_access_token($refresh_token=''){
         if(!$this->refresh_token && !$refresh_token){ 
            error_log('Aweber Refresh Token Missing!');
            return false;
         }
         $args = array( 'method' => 'POST','headers' => array( 'Content-Type' => 'application/x-www-form-urlencoded'  ) );
         $response = wp_remote_post( 'https://auth.aweber.com/oauth2/token?grant_type=refresh_token&client_id=RFHkkuTqrezTnsLVZzUt4NVQmEhcbe0p&refresh_token='.$refresh_token, $args );
         
         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );

         //error_log(json_encode($data));

         return isset($data->access_token) ? $data->access_token : '';
      }

      public function get_lists($refresh_token=''){
         $refresh_token  = $refresh_token ? $refresh_token : $this->refresh_token;
         $access_token  = $this->get_access_token($refresh_token); 
         $accountID = $this->get_accoutnID($access_token);
         $headerArgs = array( 'headers' => array(  'Authorization' => 'Bearer ' . $access_token ) );
         $lresponse = wp_remote_get( 'https://api.aweber.com/1.0/accounts/'.$accountID.'/lists', $headerArgs );

         if( is_wp_error( $lresponse ) ) {
            return false; // Bail early
         }

         $lbody = wp_remote_retrieve_body( $lresponse );
         $ldata = json_decode( $lbody );

         //error_log(json_encode($lbody));
         
         $finalLists = array();
         // //error_log($apiKey . $body);
         
         if(isset($ldata->entries) && is_array($ldata->entries)){
            $lists = $ldata->entries;
            foreach ($lists as $key => $list) {
               $listItem = new stdClass();
               $listItem->id = isset($list->id) ? $list->id : '';
               $listItem->name = isset($list->name) ? $list->name : '';
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
         if( !$this->refresh_token){ 
            //error_log('Aweber Refresh Token Missing!');
            return false;
         }

         $firstname = trim($fname);
         $lastname = trim($lname);
         $fullname = $firstname;

         //Convert firstname and lastname to Fullname. 
         if($firstname && $lastname){
            $fullname = $firstname.' '.$lastname;
         }

         $access_token  = $this->get_access_token($this->refresh_token);
         $accountID = $this->get_accoutnID($access_token);

         if($accountID){
            $args = array(
               'method' => 'POST',
               'headers' => array( 'Authorization' => 'Bearer ' . $access_token, 'Content-Type' => 'application/json'  ),
               'body' => json_encode(array(
                  'email' => $email,
                  'name'  => $fullname,
                  'ip_address'    => class_exists('BravePop_Geolocation') ? bravepop_getVisitorIP() : ''
               ))
            );
   
            $response = wp_remote_post( 'https://api.aweber.com/1.0/accounts/'.$accountID.'/lists/'.$list_id.'/subscribers', $args );
            $body = wp_remote_retrieve_body( $response );
            $data = json_decode( $body );

            // error_log(json_encode($body));
            // error_log($data->error->message);

            if((isset($response['response']['code']) && $response['response']['code'] === 201)){
               //error_log(json_encode($response['response']['code']));
               return $response['response']['code'];
            }else if( isset($data->error->message) && ($data->error->message === 'email: Subscriber already subscribed.')){
               return $data->error->message;
            }else{
               return false;
            }

         }else{
            //error_log('NO Account ID');
            return false;
         }

      }


   }

}
?>