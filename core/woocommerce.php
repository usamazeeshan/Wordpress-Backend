<?php
// add_action('woocommerce_thankyou','roadcube_after_checkout_transaction');
// add_action('save_post_shop_order','roadcube_after_checkout_transaction');
// add_action('wp_insert_post','roadcube_after_checkout_transaction');
// function roadcube_after_checkout_transaction( $order_id ){
//     $post = get_post( $order_id );
//     if( $post->post_type != 'shop_order' ) return;
//     if( get_post_meta($order_id,'roadcube_new_transaction',true) ) return;
//     $order = wc_get_order( $order_id );
//     $customer_id = $order->get_customer_id();
//     $email = $order->get_billing_email();
//     $total = $order->get_total();
//     if( !$email || !$total ) return;
//     $data = roadcube_checkout_new_transaction_call($email, $total);
//     update_post_meta($order_id,'roadcube_new_transaction',true);
//     // echo '<pre>';
//     // print_r($data);
//     // echo '</pre>';
// }
add_action('woocommerce_checkout_before_order_review','roadcube_woocommerce_callback');
function roadcube_woocommerce_callback(){
    if( !is_user_logged_in() ) return;
    ?>
    <button type="button" id="roadcube-show-coupons" style="margin-bottom:16px;"><?php _e('Apply loyalty coupon','roadcube'); ?></button>
    <?php
}
add_action('woocommerce_before_calculate_totals','roadcube_apply_the_discount');
function roadcube_apply_the_discount( $cart ){
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) return;
    $user_id = get_current_user_id();
    if( !$user_id || WC()->cart->get_cart_contents_count() == 0 ) return;
    $user_claimed_coupon = get_user_meta($user_id,'roadcube_claimed_coupons',true) ?: [];
    $claimed_coupon = end($user_claimed_coupon);
    if( empty($claimed_coupon) || isset($claimed_coupon['redeemed'])) return;
    $cost = floatval($claimed_coupon['cost']);
    $cart->add_fee('Discount',-1 * $cost);
}
add_action('woocommerce_thankyou','roadcube_add_the_user_coupon_redeemed');
function roadcube_add_the_user_coupon_redeemed( $order_id ){
    $user_id = get_current_user_id();
    if( !$user_id ) return;
    $user_claimed_coupon = get_user_meta($user_id,'roadcube_claimed_coupons',true) ?: [];
    $claimed_coupon = end($user_claimed_coupon);
    $key = key($user_claimed_coupon);
    if( $claimed_coupon && !isset($claimed_coupon['redeemed'])){
        $claimed_coupon['redeemed'] = true;
        $user_claimed_coupon[$key] = $claimed_coupon;
        update_user_meta($user_id,'roadcube_claimed_coupons',$user_claimed_coupon);
    }
}
add_action('woocommerce_order_status_changed','roadcube_trigger_charge_point',10,3);
function roadcube_trigger_charge_point( $order_id, $old_status, $new_status ){
    $charge_status = Coupon_Claimer::roadcube_get_setting('roadcube_charge_point_val') ?: [];
    if( !in_array($new_status,explode(',',$charge_status)) ) return;
    $order = wc_get_order( $order_id );
    $customer_id = $order->get_customer_id();
    $email = $order->get_billing_email();
    $total = $order->get_total();
    if( !$email || !$total ) return;
    $data = roadcube_checkout_new_transaction_call($email, $total);
    // get the API log
    $log = get_option('roadcube_log') ?: [];
    $log[] = $data;
    update_option('roadcube_log',$log);
    // get the API log
    if( isset($data['status']) && $data['status'] == 'success' ) {
        $trans_id = $data['data']['transaction_id'];
        update_post_meta($order_id,'roadcube_point_trans_id',$trans_id);
    }
    // echo '<pre>';
    // print_r($data);
    // echo '</pre>';
}
add_action('woocommerce_order_status_changed','roadcube_trigger_refund_point',10,3);
function roadcube_trigger_refund_point( $order_id, $old_status, $new_status ){
    $refund_status = Coupon_Claimer::roadcube_get_setting('roadcube_refund_point_val') ?: [];
    if( !in_array($new_status,explode(',',$refund_status)) ) return;
    // $order = wc_get_order( $order_id );
    // $customer_id = $order->get_customer_id();
    // $email = $order->get_billing_email();
    // $total = $order->get_total();
    // if( !$email || !$total ) return;
    $trans_id = get_post_meta($order_id,'roadcube_point_trans_id',true);
    $data = roadcube_checkout_cancel_transaction_call($trans_id);
    // get the API log
    $log = get_option('roadcube_log') ?: [];
    $log[] = $data;
    update_option('roadcube_log',$log);
    // get the API log
    // echo '<pre>';
    // print_r($data);
    // echo '</pre>';
}