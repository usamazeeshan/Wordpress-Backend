<?php
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

add_action('woocommerce_thankyou','roadcube_after_checkout_transaction');
add_action('save_post_shop_order','roadcube_after_checkout_transaction');
// add_action('wp_insert_post','roadcube_after_checkout_transaction');
function roadcube_after_checkout_transaction( $order_id ){
	$post = get_post( $order_id );		
	$order = wc_get_order( $order_id );		
	$items = $order->get_items();	

	
	$api_key = Coupon_Claimer::roadcube_get_setting('api_key'); //mviesto8-aade-15hp-9h59-gvkz4dxlvb96
    $store_id = Coupon_Claimer::roadcube_get_setting('store_id'); //3099
	$store_amount =Coupon_Claimer::roadcube_get_setting('specific_amount');
			
	?>
	<script>	
		
var request = new XMLHttpRequest();
request.open('POST', 'https://api.roadcube.io/v1/p/stores/<?php echo $store_id; ?>/transactions/new');

request.setRequestHeader('Content-Type', 'application/json');
request.setRequestHeader('Accept', 'application/json');
request.setRequestHeader('X-Api-Token', '<? echo $api_key; ?>');

request.onreadystatechange = function () {
  if (this.readyState === 4) {
    console.log('Status:', this.status);
    console.log('Headers:', this.getAllResponseHeaders());
    console.log('Body:', this.responseText);
  }
}

 debugger

var body = {
    "user": "6941234567",
    "custom_points_provided": false,
//     "custom_points": 0,
    "products": [
		<?php 
	
	
	foreach ($items as $item) {

// 		$product_id = $items['id'];
		
        // Get the product ID from the item
        $product_id = $item->get_product_id();

        // Check if $product_id is valid
        if ($product_id > 0) {
            // Retrieve the meta data for the product
            $meta_data = get_post_meta($product_id, 'roadcube_product_sync', true);

            if ($meta_data) {
                $newProductId = json_decode($meta_data);
// 				echo '$newProductId'; print_r($newProductId);
				$product_data = $newProductId->product_id;
// 				$productiddd = $product_data->product_id;
// 				echo 'here is product id'. $product_data->product_id;
//  				echo '$productiddd' . $productiddd;
// 				echo '<pre>';
// 				print_r($item);
// 				
// 				// Get the product price
// 				$product_price = $product->get_price();
				echo '{
//  				asdf
 								"product_id":  '. $product_data .',
								 "retail_price": '. $item["total"] .',
								"quantity": '.$item["quantity"].'
							},';
				
//                 if (json_last_error() === JSON_ERROR_NONE) {
//                     $product_data = $newProductId->data->product;
// 					print_r($product_data);

//                     if (isset($product_data->product_id)) {
//                         $product_iddd = $product_data->product_id;
						
// 						// Get the product price
// 						$product_price = $product->get_price();
// 						echo '{
// 								"product_id":  '. $product_iddd .',
// 								 "retail_price": '. $product_price .',
// 								"quantity": '.$item["quantity"].'
// 							},';
                        
//                         echo 'Hello this is id: ' . $product_iddd;

//                     } else {
//                         echo 'product_id not found in JSON data.';
//                     }
//                 } else {
//                     echo 'Failed to decode JSON data. JSON Error: ' . json_last_error_msg();
//                 }
            } else {
                echo 'No valid meta data found.';
            }
        }
    } 

	?>
		
    ]
};
		
 debugger
 var res = request.send(JSON.stringify(body));
		if(request.send){
			console.log('Transaction is created successfully. product id is <?php echo $product_data->product_id; ?> <?php echo '$store_amount ' .$store_amount; ?>');
		}
// 		console.log(res, '1234');
		
		
// 		echo '<pre>';
// 		var_dump($item);
// 		echo 'product_id'. $item["product_id"];
// 		echo 'quantity' . $item["quantity"];
// 		echo 'product_price' . $product_price;
// 		echo '</pre>';
	
</script>
	<?php
	
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
    // echo '<pre>';
    // print_r($data);
    // echo '</pre>';
}
add_action('woocommerce_checkout_before_order_review','roadcube_woocommerce_callback');
function roadcube_woocommerce_callback(){
    if( !is_user_logged_in() ) return;
    ?> <button type="button" id="roadcube-show-coupons"
    style="margin-bottom:16px;"><?php _e('Apply loyalty coupon','roadcube'); ?></button> <?php
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
    $total =   $order->get_subtotal();  //$order->get_total();
    if( !$email || !$total ) return;
    $data = roadcube_checkout_new_transaction_call($email, $total, $order_id);
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
    // $total = $order->get_total );
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
add_action( 'woocommerce_after_shop_loop_item_title', 'roadcube_new_badge_shop_page', 3 );
function roadcube_new_badge_shop_page() {
    if( !Coupon_Claimer::roadcube_get_setting('roadcube_enable_point') ) return;
    global $product;
    $product_id = $product->get_id();
    $product_data = get_post_meta($product_id,'roadcube_product_created_data',true);
    if( isset($product_data['status']) && $product_data['status'] == 'success' ) {
        $price = floor(floatval($product->get_price()));
        $points = intval($product_data['data']['product']['reward_points']);
        $actual_point =  Coupon_Claimer::roadcube_get_setting('roadcube_point_mul_price') ? $price * $points : $points;
        echo '<span class="onsale" style="background: white;font-weight:600;">+' . $actual_point  .' '. esc_html__( ' Πόντοι', 'woocommerce' ) . '</span>';
    }else{
          $point = 1;
     	$price = floor(floatval($product->get_price()));
  
 	$actual_point =  Coupon_Claimer::roadcube_get_setting('roadcube_point_mul_price') ? $price * intval($point) : intval($point);
 	echo '<span class="onsale" style="background: white;font-weight:600;">+' . $actual_point  .' '. esc_html__( ' Πόντοι', 'woocommerce' ) . '</span>';
    }
}

add_action( 'woocommerce_single_product_summary', 'roadcube_new_reward_point');
function roadcube_new_reward_point() {
    if( !Coupon_Claimer::roadcube_get_setting('roadcube_enable_point') ) return;
    global $product;
    $product_id = $product->get_id();
    $product_data = get_post_meta($product_id,'roadcube_product_created_data',true);
    if( isset($product_data['status']) && $product_data['status'] == 'success' ) {
        $price = floor(floatval($product->get_price()));
        $points = intval($product_data['data']['product']['reward_points']);
        $actual_point =  Coupon_Claimer::roadcube_get_setting('roadcube_point_mul_price') ? $price * $points : $points;
        echo '<span class="onsale" style="background: white;font-weight:600;">+' . $actual_point  .' '. esc_html__( ' Πόντοι', 'woocommerce' ) . '</span>';
    }else{
          $point = 1;
     	$price = floor(floatval($product->get_price()));
  
 	$actual_point =  Coupon_Claimer::roadcube_get_setting('roadcube_point_mul_price') ? $price * intval($point) : intval($point);
 	echo '<span class="onsale" style="background: white;font-weight:600;">+' . $actual_point  .' '. esc_html__( ' Πόντοι', 'woocommerce' ) . '</span>';
    }
}



