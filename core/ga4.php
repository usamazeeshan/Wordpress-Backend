<?php
add_action('init','roadcube_to_ga4');
function roadcube_to_ga4(){
    if( isset( $_GET['roadcube_ga4']) ) {
        $m_id = Coupon_Claimer::roadcube_get_setting('roacube_ga4_m_id');
        if( !$m_id ) return;
        $client_id = Coupon_Claimer::roadcube_get_setting('roacube_ga4_client_id');
        if( !$client_id ) return;
        $m_secret = Coupon_Claimer::roadcube_get_setting('roacube_ga4_m_api_secret');
        if( !$m_secret ) return;
    }
}