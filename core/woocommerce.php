<?php
add_action('woocommerce_thankyou','roadcube_after_checkout_transaction');
function roadcube_after_checkout_transaction( $order_id ){
    if( get_post_meta($order_id,'roadcube_new_transaction',true) ) return;
    $order = wc_get_order( $order_id );
    $customer_id = $order->get_customer_id();
    $email = $order->get_billing_email();
    $total = $order->get_total();
    $data = roadcube_checkout_new_transaction_call($email, $total);
    update_post_meta($order_id,'roadcube_new_transaction',true);
    // echo '<pre>';
    // print_r($data);
    // echo '</pre>';
}