<?php
function roadcube_post_to_ga4( $m_secret, $m_id, array $dataset ){
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
        // get the store IDs
        $store_ids = Coupon_Claimer::roadcube_get_setting('roacube_offline_store_ids');
        // get the dataset
        $dataset = [
            "client_id" => $client_id,
            "non_personalized_ads" => false,
            "events" => []
        ];
        // previous store transactions
        $prev_trans = get_option('roadcube_previous_offline_store_transactions') ?: [];
        // ga4 events
        $ga4_events = [];
        if( $store_ids ) {
            $offline_stores = explode(',',$store_ids);
            if( !empty($offline_stores) ) {
                foreach( $offline_stores as $store_id ) {
                    $store_transactions = roadcube_get_offline_transactions($store_id);
                    // $ga4_events[] = $store_transactions;
                    // previous transactions of this store
                    $trans_id_of_this_store = isset($prev_trans[$store_id]) ? $prev_trans[$store_id] : [];
                    // the transaction data
                    if( isset($store_transactions['status']) && $store_transactions['status'] == "success" ) {
                        $transactions = $store_transactions['data']['transactions'];
                        $checked_trans = [];
                        foreach($transactions as $transaction) {
                            $checked_trans[] = $transaction['transaction_id'];
                            if( in_array($transaction['transaction_id'],$trans_id_of_this_store) ) continue;
                            $ga4_events[] = [
                                "name" => "purchase",
                                "params" => [
                                    "transaction_id" => $transaction['transaction_id'],
                                    "store_id" => $store_id,
                                    "store_name" => $transaction['store']['name'],
                                    "user_mobile" => $transaction['user']['mobile'],
                                    "total" => $transaction['total_price']
                                ]
                            ];
                        }
                        // save the transactions which has been checked
                        $prev_trans[$store_id] = $checked_trans;
                    }
                }
                update_option('roadcube_previous_offline_store_transactions',$prev_trans);
            }
        }
        update_option('roadcube_ga4_events',$ga4_events);
        foreach(array_chunk($ga4_events,25) as $event_chunk){
            $dataset['events'] = $event_chunk;
            roadcube_post_to_ga4( $m_secret, $m_id, $dataset);
        }
        /* sample event
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
        */
        roadcube_post_to_ga4( $m_secret, $m_id, $dataset );
    }
}
function roadcube_get_offline_transactions( $store_id ){

    $curl = curl_init();
    $api_key = Coupon_Claimer::roadcube_get_setting('api_key');
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.roadcube.io/v1/p/stores/{$store_id}/transactions?page=1",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            "X-Api-Token: {$api_key}"
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return json_decode($response,true);
}