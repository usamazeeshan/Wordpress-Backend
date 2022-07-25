<?php
function roadcube_get_the_trans( $page = 1 ){
    $curl = curl_init();
    $roadcube_settings = get_option('roadcube_settings');
    $api_key = isset($roadcube_settings['api_key']) ? $roadcube_settings['api_key'] : "vchglllp-aw22-000c-nvp4-max823mvlpg8";
    $data = json_encode(array(
        'user' => get_user_meta( get_current_user_id(), 'roadcube_mobile', true )
    ));
    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://api.roadcube.io/v1/p/users/transactions/?page={$page}",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => $data,
      CURLOPT_HTTPHEADER => array(
        "X-API-TOKEN: {$api_key}",
        'Content-Type: application/json'
      ),
    ));
    
    $response = curl_exec($curl);
    
    curl_close($curl);
    $output = json_decode($response,true);
    return $output;
}
