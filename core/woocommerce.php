<?php
add_action('woocommerce_thankyou','roadcube_after_checkout_transaction');
add_action('save_post_shop_order','roadcube_after_checkout_transaction');
add_action('wp_insert_post','roadcube_after_checkout_transaction');
function roadcube_after_checkout_transaction( $order_id ){
    $post = get_post( $order_id );
    if( $post->post_type != 'shop_order' ) return;
    if( get_post_meta($order_id,'roadcube_new_transaction',true) ) return;
    $order = wc_get_order( $order_id );
    $customer_id = $order->get_customer_id();
    $email = $order->get_billing_email();
    $total = $order->get_total();
    if( !$email || !$total ) return;
    $data = roadcube_checkout_new_transaction_call($email, $total);
    update_post_meta($order_id,'roadcube_new_transaction',true);
    // echo '<pre>';
    // print_r($data);
    // echo '</pre>';
}