<?php
define( 'SALESFORCE_INSTANCE', '');
define( 'SALESFORCE_CLIENT_ID', '');
define( 'SALESFORCE_CLIENT_SECRET', '');
define( 'SALESFORCE_USERNAME', '');
define( 'SALESFORCE_PASSWORD', '');

if (!function_exists('_get_salesforce_session_bearer_token')) {
    function _get_salesforce_session_bearer_token() {
	
        $salesforce_login_url = "https://" . SALESFORCE_INSTANCE . ".my.salesforce.com/services/oauth2/token";
        $auth_args = [
            'grant_type' => 'password',
            'client_id' => SALESFORCE_CLIENT_ID,
            'client_secret' => SALESFORCE_CLIENT_SECRET,
            'username' => SALESFORCE_USERNAME,
            'password' => SALESFORCE_PASSWORD
        ];

        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $salesforce_login_url );
        curl_setopt( $ch, CURLOPT_POST, true );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $auth_args ) );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        
        // Execute SalesForce web to lead PHP cURL
        $auth_response = curl_exec( $ch );
        if( $auth_response === false ) {            
            curl_close( $cl );
            return NULL;
        }
        curl_close( $ch );
    
        $auth_response_array = json_decode( $auth_response, true );
        $bearer_token = isset( $auth_response_array['access_token'] ) ? $auth_response_array['access_token'] : '';
        if( $bearer_token ) {
            return $bearer_token;
        }
        return NULL;
    }
}

if (!function_exists('_post_lead_to_salesforce_api')) {
    function _post_lead_to_salesforce_api( $auth_token, $lead_id = "", $salesforce_data ) {

        $salesforce_url = "https://" . SALESFORCE_INSTANCE . ".my.salesforce.com/services/data/v54.0/sobjects/Lead/{$lead_id}";        
        $salesforce_data_json = json_encode( $salesforce_data );
    
        //Open cURL connection
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $salesforce_url );

        if ($lead_id)
            curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        else
            curl_setopt( $ch, CURLOPT_POST, true);

        curl_setopt( $ch, CURLOPT_POSTFIELDS, $salesforce_data_json );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen( $salesforce_data_json ),
            'Authorization: Bearer ' . $auth_token
        ] );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        $create_record_response = curl_exec( $ch );
        curl_close( $ch );
    
        return $create_record_response;
    }
}

if (!function_exists('_get_lead_to_salesforce_api')) {
    function _get_lead_to_salesforce_api( $auth_token, $email ) {
        
        $salesforce_url = "https://upscalefinancial--loanapp.my.salesforce.com/services/data/v54.0/sobjects/Lead/Email/{$email}";
        
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $salesforce_url );
        curl_setopt( $ch, CURLOPT_POST, false );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',            
            'Authorization: Bearer ' . $auth_token
        ] );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        $get_record_response = curl_exec( $ch );
        curl_close( $ch );
        
        if (!empty($get_record_response)) {
            return json_decode($get_record_response);
        }

        return false;
    }
}