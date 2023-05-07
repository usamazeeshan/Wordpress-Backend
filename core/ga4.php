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
}
add_action('init','roadcube_to_ga4');
function roadcube_to_ga4(){
    // get the store IDs
    $store_ids = Coupon_Claimer::roadcube_get_setting('roacube_offline_store_ids');
    // if store ids are not set the pause
    if( !$store_ids ) return;
    // string to array of store Ids
    $offline_stores = explode(',',$store_ids);
    // pause if empty store Ids
    if( empty($offline_stores) ) return;
    $i = 1;
    foreach( $offline_stores as $store_id ) {
        wp_schedule_single_event(time() + 100 * $i, 'roadcube_get_trans_to_be_synced_in_ga4', array( $store_id ) );
        $i++;
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
}
function roadcube_get_offline_transactions( $store_id ){

    $curl = curl_init();
    $api_key = Coupon_Claimer::roadcube_get_setting('api_key');
    if( !$api_key ) return;
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
add_action('roadcube_get_trans_to_be_synced_in_ga4','roadcube_get_trans_to_be_synced');
function roadcube_get_trans_to_be_synced( $store_id ){
    // get the measurement ID
    $m_id = Coupon_Claimer::roadcube_get_setting('roacube_ga4_m_id');
    // pause if no measurement Id set
    if( !$m_id ) return;
    // get the client ID
    $client_id = Coupon_Claimer::roadcube_get_setting('roacube_ga4_client_id');
    // pause if no client Id set
    if( !$client_id ) return;
    // get the measurement secret
    $m_secret = Coupon_Claimer::roadcube_get_setting('roacube_ga4_m_api_secret');
    // pause if no measurement secret
    if( !$m_secret ) return;
    // get the dataset
    $dataset = [
        "client_id" => $client_id,
        "non_personalized_ads" => false,
        "events" => []
    ];
    // get all transactions of this store
    $store_transactions = roadcube_get_offline_transactions($store_id);
    // get previous transactions of all stores
    $prev_trans = get_option( 'roadcube_previous_offline_store_transactions' ) ?: [];
    // previous transactions of this store
    $trans_id_of_this_store = isset($prev_trans[$store_id]) ? $prev_trans[$store_id] : [];
    // initiate the ga4_event holder
    $ga4_events = [];
    // check if the get transaction call has a successful return
    if( isset($store_transactions['status']) && $store_transactions['status'] == "success" ) {
        // the transaction data
        $transactions = $store_transactions['data']['transactions'];
        // initialize the checked transaction id holder
        $checked_trans = [];
        foreach($transactions as $transaction) {
            $transaction_id = $transaction['transaction_id'];
            $user_mobile = $transaction['user']['mobile'];
            $checked_trans[] = $transaction_id;
            if( in_array($transaction['transaction_id'],$trans_id_of_this_store) ) continue;
            $event_name = $transaction['total_price'] < 0 ? "roadcube_refund" : "roadcube_offline_purchase";
            $ga4_events[] = [
                "name" => $event_name,
                "params" => [
                    "transaction_id" => $transaction_id,
                    "store_id" => $store_id,
                    "store_name" => $transaction['store']['name'],
                    "user_mobile" => $user_mobile,
                    "value" => abs( $transaction['total_price'] )
                ]
            ];
        }
        // save the transactions which has been checked
        $prev_trans[$store_id] = $checked_trans;
        update_option('roadcube_previous_offline_store_transactions',$prev_trans);
    }
    $j = 1;
    if( empty($ga4_events) ) return;
    foreach(array_chunk($ga4_events,25) as $event_chunk){
        $dataset['events'] = $event_chunk;
        wp_schedule_single_event(time() + 100 * $j, 'roadcube_sync_trans_to_ga4', array( $m_secret, $m_id, $dataset ) );
        $j++;
    }
}
add_action('roadcube_sync_trans_to_ga4','roadcube_sync_trans_to_ga4_as_purchase_callback',10,3);
function roadcube_sync_trans_to_ga4_as_purchase_callback( $m_secret, $m_id, $dataset ){
    roadcube_post_to_ga4( $m_secret, $m_id, $dataset );
}