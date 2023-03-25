<?php
function roadcube_sync_all_prev_products(){
    $query = new WP_Query( array(
        'post_type' => 'product',
        'post_status'  => 'publish',
        'posts_per_page' => -1
    ) );
    $product_ids = [];
    while($query->have_posts()){
        $query->the_post();
        $product_id = get_the_ID();
        $product_ids[] = $product_id;
    }
    $i = 1;
    foreach(array_chunk($product_ids,10) as $product_ids_chunk) {
        wp_schedule_single_event(time() + 100 * $i, 'roadcube_synce_products', array( $product_ids_chunk ) );
        $i++;
    }
}
add_action('roadcube_synce_products','roadcube_synce_products_callback');
function roadcube_synce_products_callback( $ids ){
    foreach($product_ids as $product_id){
        roadcube_save_product_callback($product_id);
    }
}
function roadcube_get_product_category(){
    $curl = curl_init();
    $api_key = Coupon_Claimer::roadcube_get_setting('api_key');
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
    if( isset($output['status']) && $output['status'] == 'success' ){
        $category_id = $output['data']['products'][0]['product_category']['product_category_id'];
        update_option('roadcube_store_cat_id',$category_id);
        return $category_id;
    } else {
        return false;
    }
}
function roadcube_create_product( array $dataset ) {
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

    curl_close($curl);
    return json_decode($response,true);
}
add_action('save_post_product','roadcube_save_product_callback');
function roadcube_save_product_callback( $product_id ){
    $product = wc_get_product( $product_id );
    $sale_price = $_POST['_sale_price'];
    $regular_price = $_POST['_regular_price'];
    $sku = $_POST['_sku'];
    $title = $_POST['post_title'];
    $des = $_POST['content'];
    $category_id = get_option('roadcube_store_cat_id') ?: roadcube_get_product_category();
    update_option('roadcube_store_cat_id',$category_id);
    $product_data = array(
        'published' => true,
        'name' => array(
            'en' => $title,
            'it' => $title,
            'el' => $title
        ),
        'description' => array(
            'en' => $des,
            'el' => $des,
            'it' => $des
        ),
        'retail_price' => $regular_price,
        'wholesale_price' => $sale_price,
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
    return $created_product;
}