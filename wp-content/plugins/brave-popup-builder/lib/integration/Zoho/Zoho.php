<?php
if ( ! class_exists( 'BravePop_Zoho' ) ) {
   
   class BravePop_Zoho {

      function __construct() {
         $braveSettings = get_option('_bravepopup_settings');
         $integrations = $braveSettings && isset($braveSettings['integrations']) ? $braveSettings['integrations'] : array() ;
         $this->api_key = isset($integrations['zoho']->api)  ? $integrations['zoho']->api  : '';
         $this->api_secret = isset($integrations['zoho']->secret)  ? $integrations['zoho']->secret  : '';
         $this->redirect = isset($integrations['zoho']->url)  ? $integrations['zoho']->url  : '';
         $this->refresh_token = isset($integrations['zoho']->refresh)  ? $integrations['zoho']->refresh  : '';
         $this->domain = isset($integrations['zoho']->domain)  ? $integrations['zoho']->domain  : 'com';
      }

      public function get_lists(){
         $access_token  = $this->get_access_token($this->api_key, $this->api_secret, $this->refresh_token); 
         $args = array(  'method' => 'GET','headers' => array( 'Authorization' => 'Zoho-oauthtoken ' . $access_token ) );
         if($access_token){
            $response = wp_remote_get( 'https://campaigns.zoho.com/api/getmailinglists?scope=CampaignsAPI&resfmt=JSON&authtoken='.$access_token, $args );
            if( is_wp_error( $response ) ) {
               return false; // Bail early
            }
            $body = wp_remote_retrieve_body( $response );
            $data = json_decode( $body );
            $lists = $data->list_of_details;
            $finalLists = array();
            if($lists && is_array($lists)){
               foreach ($lists as $key => $list) {
                  $listItem = new stdClass();
                  $listItem->id = isset($list->listkey) ? $list->listkey : '';
                  $listItem->name = isset($list->listname) ? $list->listname : '';
                  $listItem->count = isset($list->noofcontacts)  ? $list->noofcontacts : 0;
                  $finalLists[] = $listItem;
               }
            }
            //error_log(json_encode($data->list_of_details));
            //error_log(json_encode($finalLists));
            return json_encode($finalLists);
         }else{
            //error_log('NO ACCESS TOKEN');
            return false;
         }

      }

      public function get_access_token($apiKey='', $apiSecret='', $refresh_token='', $domain=''){
         $apiKey = $this->api_key ? $this->api_key : $apiKey;
         $apiSecret = $this->api_secret ? $this->api_secret : $apiSecret;
         $refresh_token = $this->refresh_token ? $this->refresh_token : $refresh_token;
         $domain = $this->domain ? $this->domain : $domain;
         if(!$apiKey || !$apiSecret || !$refresh_token || !$domain){ 
            return false;
         }
         //error_log('REFRESH TOKEN: '.$this->refresh_token);
         $args = array( 'method' => 'POST','headers' => array( 'Content-Type' => 'application/x-www-form-urlencoded'  ) );
         $response = wp_remote_post( 'https://accounts.zoho.'.$domain.'/oauth/v2/token?refresh_token='.$refresh_token.'&client_id='.$apiKey.'&client_secret='.$apiSecret.'&grant_type=refresh_token', $args );
         
         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );
         error_log($body);
         return isset($data->access_token) ? $data->access_token : false;
      }


      public function add_to_lists($email, $list_id='', $fname='', $lname='', $phone=''){
         if(!$email || !$list_id){ return null; }
         if(!$this->api_key || !$this->api_secret || !$this->refresh_token){ 
            //error_log('API KEY, SECRET or Access/Refresh Token Missing!');
            return false;
         }

         $firstname = trim($fname);
         $lastname = trim($lname);
         $fullname = $firstname;

         //If FullName, and no last name, extract firstname and lastname from fullname
         if(!$lastname && ( strpos($firstname, ' ') !== false)){
            $fullname_parts = preg_split('/\s+/', $firstname);
            $firstname = $fullname_parts[0] ? $fullname_parts[0] : $firstname;
            $lastname = $fullname_parts[1] ? $fullname_parts[1] : '';
         }

         $access_token  = $this->get_access_token($this->api_key, $this->api_secret, $this->refresh_token, $this->domain); 

         if($access_token){
            $args = array( 'method' => 'POST', 'headers' => array( 'Authorization' => 'Zoho-oauthtoken ' . $access_token ) );
            $contactInfo = '%7B'.($firstname?'First+Name%3A'.$firstname.'%2C':'').($lastname?'Last+Name%3A'.$lastname.'%2C':'').'Contact+Email%3A'.$email.'%7D';
            $response = wp_remote_post( 'https://campaigns.zoho.com/api/v1.1/json/listsubscribe?resfmt=JSON&listkey='.$list_id.'&contactinfo='.$contactInfo, $args );
            $body = wp_remote_retrieve_body( $response );
            $data = json_decode( $body );
            //error_log('$body->status: '.$data->status);
            if(isset($data->status) && $data->status === 'success'){
               //error_log(json_encode($response['response']['code']));
               return $data->status;
            }else{
               return false;
            }

         }else{
            error_log('NO ACCESS TOKEN');
            return false;
         }

      }


   }

}
?>