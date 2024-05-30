<?php
// Save the custom field value
// function save_custom_field_value($post_id, $roadcube_product_sync_data) {
//         update_post_meta($post_id, '_roadcube_product_sync_data', sanitize_text_field($roadcube_product_sync_data));
// }
// add_action('woocommerce_process_product_meta', 'save_custom_field_value');

// global $product;
// $rodecube_product_sync_data = get_post_meta($product->get_id(), '_rodecube_product_sync_data', true);
function roadcube_sync_all_prev_products() {
	
// ===PRODUCT CATEGORY ID=================================================	
	$api_key = Coupon_Claimer::roadcube_get_setting('api_key');
    $store_id = Coupon_Claimer::roadcube_get_setting('store_id');
	$product_category_id = 0;
	// product_category_id
	// update_option('product_category_id', $value);

	$ch = curl_init();
// https://api.roadcube.io/v1/p/stores/3100/product-categories/ // copied from api platform
 	curl_setopt($ch, CURLOPT_URL, "https://api.roadcube.io/v1/p/stores/$store_id/product-categories/");
// 	curl_setopt($ch, CURLOPT_URL, "https://api.roadcube.io/v1/p/stores/$store_id/products?page=");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);

	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	  "Content-Type: application/json",
	  "Accept: application/json",
	  "X-Api-Token: $api_key"
	));
        $response = curl_exec($ch);
// 	echo "<pre>";
// 	var_dump($response);
// 	die();
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_status == 200) {
            
			$decoded_response = json_decode($response, true);
            $product_category_id = $decoded_response['data']['product_categories'][0]['product_category_id'];

// 			echo '$product_category_id' . $product_category_id;
		}
// 	die();
// ===PRODUCT CATEGORY ID=================================================		
	
    $query = new WP_Query(array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => -1
    ));
echo '<ol>';
    while ($query->have_posts()) {
		$productData = wc_get_product( get_the_ID() );
        $query->the_post();
        $product_id = get_the_ID();
        $title = get_the_title();
        $price = get_post_meta($product_id, '_price', true);
        $desc = get_post_field('post_content', $product_id);
		$desc = get_post_field('post_content', $product_id) !== '' ? get_post_field('post_content', $product_id)  : 'The description';
		
		$http_status = '';
		$decoded_response  = '';
		$response = '';
		
		$post_method = '';
		    $meta_data = get_post_meta($product_id, 'roadcube_product_sync', true);
			$alreadySaveProductId = 0;
            if ($meta_data == '') {
                 $product_data = json_decode($meta_data);
// 				echo '$newProductId'; print_r($newProductId);
				$alreadySaveProductId = $product_data->product_id;
// 				echo '<pre>';
// 				var_dump($newProductId);
// 				echo '</pre>';
 				echo  '<li><b>' . $title . '</b> is synced with roadcube.</li>';
				$post_method = 'POST';
			}else{
				$post_method = 'PUT';
				echo '<li><b>' . $title . '</b> already synced and updated on roadcube.</li>';
			}
		
//         $prod_cat_id = Coupon_Claimer::roadcube_get_setting('product_category_id');
		
				$headers = array(
					'Content-Type: application/json',
					'Accept: application/json',
					"X-Api-Token: $api_key"
				);

				$data = array(
					'published' => true,
					'name' => array(
						'el' => $title,
						'en' => $title,
						'it' => $title
					),
					'description' => array(
						'el' => $desc,
						'en' => $desc,
						'it' => $desc
					),
					'retail_price' => $price,
					'wholesale_price' => $price,
					'product_category_id' => $product_category_id,
					'group_product' => false,
					'availability_days' => [0, 1, 2, 3, 5, 6]
				);
		if($post_method == 'POST'){
			 
				// Initialize cURL session
				$ch = curl_init();
				$url = "https://api.roadcube.io/v1/p/stores/$store_id/products";		
				// Set cURL options
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $post_method);
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

				$response = curl_exec($ch);
		// 		echo '<pre>';
		// 		var_dump($response);
		// 		echo '</pre>';
				$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);
			
			} else if($post_method == 'PUT'){
			
			}

        if ($http_status == 200) {
            $decoded_response = json_decode($response, true);
			$roadcube_product_sync_var = array(
            'product_id' => $decoded_response['data']['product']['product_id'],
            'product_category_id' => $decoded_response['data']['product']['product_category']['product_category_id'],
			'reward_points' => $decoded_response['data']['product']['reward_points'],
			'reward_type' => $decoded_response['data']['product']['reward_type'],
);
			
// 			echo "<pre>";
// 			var_dump($roadcube_product_sync_var);
// 			die();
           // Construct the data for the AJAX call
//             $ajax_data = array(
//                 'action' => 'update_product',
//                 'security' => wp_create_nonce('custom-ajax-nonce'),
//                 'product_id' => $product_id,
//                 'roadcube_product_sync' => $roadcube_product_sync_var
//             );
//             
			update_post_meta( $product_id, 'roadcube_product_sync', json_encode($roadcube_product_sync_var) );
// 			if($response){
// 				echo 'roadcube_product_sync updated.';
// 			}
            // Enqueue jQuery if not already loaded
//             wp_enqueue_script('jquery');

            // Output the AJAX script
//             echo "<script>
//                 jQuery.ajax({
//                     type: 'POST',
//                     url: '" . admin_url('admin-ajax.php') . "',
//                     data: " . wp_json_encode($ajax_data) . ",
//                     success: function (response) {
//                         console.log('Nested AJAX Success:', response);
//                         // Handle the response from the nested AJAX call
//                     },
//                     error: function (error) {
//                         console.log('Nested AJAX Error:', error);
//                         // Handle errors from the nested AJAX call
//                     }
//                 });
//             </script>";
        }
    }
	echo '</ol>';
}



// 11-3-2023
 
// Add a custom action to the WooCommerce product submission form
function custom_woocommerce_product_form() {
    global $post;
    
    // Check if we are on the WooCommerce product edit page
    if (isset($post) && $post->post_type === 'product') {
		
		// ===PRODUCT CATEGORY ID=================================================	
	$api_key = Coupon_Claimer::roadcube_get_setting('api_key');
    $store_id = Coupon_Claimer::roadcube_get_setting('store_id');
	$product_category_id = 0;
	$ch = curl_init();

 	curl_setopt($ch, CURLOPT_URL, "https://api.roadcube.io/v1/p/stores/$store_id/product-categories/");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);

	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	  "Content-Type: application/json",
	  "Accept: application/json",
	  "X-Api-Token: $api_key"
	));
        $response = curl_exec($ch);
// 	echo "<pre>";
// 	var_dump($response);
// 	die();
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_status == 200) {
            
			$decoded_response = json_decode($response, true);
            $product_category_id = $decoded_response['data']['product_categories'][0]['product_category_id'];

// 			echo '$product_category_id' . $product_category_id;
		}
// 	die();
// ===PRODUCT CATEGORY ID=================================================		
// 
        // Retrieve and display product data
        $product = wc_get_product($post->ID);
// 		echo '<pre>';
// 		echo var_dump($product);
		$product_id = get_the_ID();
// 		echo '$product_id' . $product_id;
        $title = $product->get_name();
        $desc = $product->get_description() !== '' ? $product->get_description() : 'The Description' ;
        $price = $product->get_price();
		
		$url = "https://api.roadcube.io/v1/p/stores/$store_id/products";
				$headers = array(
					'Content-Type: application/json',
					'Accept: application/json',
					"X-Api-Token: $api_key"
				);

				$data = array(
					'published' => true,
					'name' => array(
						'el' => $title,
						'en' => $title,
						'it' => $title
					),
					'description' => array(
						'el' => $desc,
						'en' => $desc,
						'it' => $desc
					),
					'retail_price' => $price,
					'wholesale_price' => $price,
					'product_category_id' => $product_category_id,
					'group_product' => false,
					'availability_days' => [0, 1, 2, 3, 5, 6]
				);

				// Initialize cURL session
				$ch = curl_init();

				// Set cURL options
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

				$response = curl_exec($ch);
		// 		echo '<pre>';
		// 		var_dump($response);
		// 		echo '</pre>';
				$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);	 
		

        if ($http_status == 200) {
            $decoded_response = json_decode($response, true);
			$roadcube_product_sync_var = array(
            'product_id' => $decoded_response['data']['product']['product_id'],
            'product_category_id' => $decoded_response['data']['product']['product_category']['product_category_id'],
			'reward_points' => $decoded_response['data']['product']['reward_points'],
			'reward_type' => $decoded_response['data']['product']['reward_type'],
);
// 			echo "<pre>";
// 			var_dump($roadcube_product_sync_var);
// 			die();
           // Construct the data for the AJAX call
//             $ajax_data = array(
//                 'action' => 'update_product',
//                 'security' => wp_create_nonce('custom-ajax-nonce'),
//                 'product_id' => $product_id,
//                 'roadcube_product_sync' => $roadcube_product_sync_var
//             );
//             
				$response = get_post_meta( $product_id, 'roadcube_product_sync', true );
				if ($response) {
					// If the value is already set return true
					update_post_meta( $product_id, 'roadcube_product_sync', json_encode($roadcube_product_sync_var) );
				}				
			    
			    echo 'Product is also synced with roadcube.';
            // Enqueue jQuery if not already loaded
//             wp_enqueue_script('jquery');

            // Output the AJAX script
//             echo "<script>
//                 jQuery.ajax({
//                     type: 'POST',
//                     url: '" . admin_url('admin-ajax.php') . "',
//                     data: " . wp_json_encode($ajax_data) . ",
//                     success: function (response) {
//                         console.log('Nested AJAX Success:', response);
//                         // Handle the response from the nested AJAX call
//                     },
//                     error: function (error) {
//                         console.log('Nested AJAX Error:', error);
//                         // Handle errors from the nested AJAX call
//                     }
//                 });
//             </script>";
        }
        
        // You can retrieve other product data as needed
		
// 		echo '<h2>Product Data</h2>';
// 		echo '<p>ID: ' . $product_id . '</p>';
// 		echo '<p>Name: ' . $title . '</p>';
// 		echo '<p>Description: ' . $desc . '</p>';
// 		echo '<p>Price: ' . $price . '</p>';
        
        // You can display other product data here
    }
}
add_action('woocommerce_product_options_general_product_data', 'custom_woocommerce_product_form');

// 11-3-20203



add_action('roadcube_synce_products','roadcube_synce_products_callback');
function roadcube_synce_products_callback( $product_ids ){
  
    $product_log = get_option('roadcube_product_log',[]);
    $product_log = [];
    foreach($product_ids as $product_id){
        $added_log = roadcube_save_product_callback($product_id,true);
        $product_log[] = $added_log;
        update_option('roadcube_product_log',$product_log);
    }
}

function roadcube_get_product_category(){
    $curl = curl_init();
    $api_key = Coupon_Claimer::roadcube_get_setting('api_key');
// 	print_r($api_key);
    $store_id = Coupon_Claimer::roadcube_get_setting('store_id');
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.roadcube.io/v1/p/stores/{$store_id}/products?page=1",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            "X-Api-Token: {$api_key}",
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    $output = json_decode($response,true);
    update_option('roadcube_category_log',$output);
    if( isset($output['status']) && $output['status'] == 'success' ){
        $category_id = false;
        foreach( $output['data']['products'] as $product ) {
            if( isset($product['product_category']) && is_array($product['product_category']) ) {
                $category_id = $product['product_category']['product_category_id'];
                break;
            }
        }
        return $category_id;
    } else {
        return false;
    }
}


// points work
function roadcube_sync_all_product_points(){
	
	$api_key = Coupon_Claimer::roadcube_get_setting('api_key');
    $store_id = Coupon_Claimer::roadcube_get_setting('store_id');
	
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, "https://api.roadcube.io/v1/p/stores/{$store_id}/products?page=");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);

	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	  "Content-Type: application/json",
	  "Accept: application/json",
	  "X-Api-Token: {$api_key}"
	));

	$response = curl_exec($ch);
	curl_close($ch);
	$output = json_decode($response,true);
	
	$query = new WP_Query(array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => -1
    ));	
	
	while ($query->have_posts()) {
		$productData = wc_get_product( get_the_ID() );
        $query->the_post();
        $woo_product_id = get_the_ID();
		$wooProductTitle = get_the_title();

	
		$response = get_post_meta( $product_id, 'roadcube_product_sync', true );
		if ($response) {
			// If the value is already set return true
// 			update_post_meta( $product_id, 'roadcube_product_sync', json_encode($roadcube_product_sync_var) );
		}
    if( isset($output['status']) && $output['status'] == 'success' ){
		
		$i = 1 ;
        foreach( $output['data']['products'] as $product ) {
			
			$roadcube_product_points = array(
            'product_id' => $product['product_id'],
            'reward_points' => $product['reward_points'],
			'reward_type' => $product['reward_type']);
			
			if($wooProductTitle == $product['product_id']){
// 				echo $i.'. Product ' . $wooProductTitle .' points are synced <br/>';				
// 				update_post_meta($woo_product_id, 'roadcube_product_points', json_encode($roadcube_product_points) );
			}
			
        }
	}
}
}

function roadcube_create_product( array $dataset ) {
// 	echo "<pre>";
// 	print_r($dataset);die;
//  	echo "API Request Data: " . json_encode($dataset) . "\n";

    $curl = curl_init();
    $api_key = Coupon_Claimer::roadcube_get_setting('api_key');
    $store_id = Coupon_Claimer::roadcube_get_setting('store_id');
    $product_id = '';
    $method = 'POST';
    if( isset($dataset['product_id']) ){
        $product_id = $dataset['product_id'];
        $method = 'PUT';
    }
      
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.roadcube.io/v1/p/stores/{$store_id}/products/{$product_id}",
//         curl_setopt($ch, CURLOPT_URL, "https://api.roadcube.io/v1/p/stores/$store_id/product-categories/");
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_POSTFIELDS => json_encode($dataset),
        CURLOPT_HTTPHEADER => array(
            "X-Api-Token: {$api_key}",
            'Content-Type: application/json'
        ),
    ));

    $response = curl_exec($curl);
// 	print_r($response);

    curl_close($curl);
//     var_dump($response);
    return json_decode($response,true);
	
}
add_action('save_post_product','roadcube_save_product_callback');
function roadcube_save_product_callback( $product_id, $sync = true ){
    
    $product = wc_get_product( $product_id );
// 	print_r($product);

    
    // get sale price
    $sale_price = $sync ? $product->get_sale_price() : $_POST['_sale_price'];
    $regular_price = $sync ? $product->get_regular_price() : $_POST['_regular_price'];
    $sku = $sync ? $product->get_sku() : $_POST['_sku'];
    $title = $sync ? $product->get_title() : $_POST['post_title'];
    $des = $sync ? $product->get_description() : $_POST['content'];
    $category_id = get_option('roadcube_store_cat_id') ?: roadcube_get_product_category();
// 	echo "<pre>";
// 	print_r($category_id);die;
    update_option('roadcube_store_cat_id',$category_id);
    $product_data = array(
        'published' => true,
        'name' => array(
            'en' => $title,
            'it' => $title,
            'el' => $title
        ),
        'description' => array(
            'en' => $title,
            'el' => $title,
            'it' => $title
        ),
        'retail_price' => $regular_price ?: $sale_price,
// 'retail_price' => (is_numeric($regular_price) && $regular_price > 0) ? $regular_price : ((is_numeric($sale_price) && $sale_price > 0) ? $sale_price : 1),


        'wholesale_price' => $sale_price ?: $regular_price,
        'product_category_id' => $category_id,
        'group_product' => false
    );
    if( get_post_meta($product_id, 'roadcube_product_id', true) ) {
        $product_data['product_id'] = get_post_meta($product_id, 'roadcube_product_id', true);
    }
   
    $created_product = roadcube_create_product($product_data);
     
    $created_product_id = $created_product['data']['product']['product_id'];
    update_post_meta( $product_id, 'roadcube_product_id', $created_product_id );
    update_post_meta( $product_id, 'roadcube_product_created_data', $created_product );
    $created_product['body'] = $product_data;
    $created_product['cat_resp'] = $category_id;
    return $created_product;
}