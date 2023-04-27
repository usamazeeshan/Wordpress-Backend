<?php
function roadcube_post_to_ga4( $m_secret, $m_id, $client_id, array $dataset ){
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://www.google-analytics.com/mp/collect?api_secret={$m_secret}&measurement_id={$m_id}",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($dataset),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;
    // echo $response;
}
add_action('init','roadcube_to_ga4');
function roadcube_to_ga4(){
    if( isset( $_GET['roadcube_ga4']) ) {
        $m_id = Coupon_Claimer::roadcube_get_setting('roacube_ga4_m_id');
        if( !$m_id ) return;
        $client_id = Coupon_Claimer::roadcube_get_setting('roacube_ga4_client_id');
        if( !$client_id ) return;
        $m_secret = Coupon_Claimer::roadcube_get_setting('roacube_ga4_m_api_secret');
        if( !$m_secret ) return;
        $dataset = [
            "client_id" => $client_id,
            "non_personalized_ads" => false,
            "events" => [
                [
                    "name" => "purchase",
                    "params" => [
                        "transaction_id" => "444",
                        "store_id" => "222",
                        "store_name" => "Store 2",
                        "user_mobile" => "01737008004",
                        "total" => "6.99"
                    ]
                ],
                [
                    "name" => "purchase",
                    "params" => [
                        "transaction_id" => "555",
                        "store_id" => "222",
                        "store_name" => "Store 2",
                        "user_mobile" => "01737008004",
                        "total" => "6.99"
                    ]
                ]
            ]
        ];
        roadcube_post_to_ga4( $m_secret, $m_id, $dataset );
    }
}