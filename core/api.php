<?php
// https://roadcubepublicapi.docs.apiary.io/#reference/users/initialize/initialize-user's-registration
function roadcube_get_countries(){
    $curl = curl_init();
    $api_key = Coupon_Claimer::roadcube_get_setting('api_key');
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.roadcube.io/v1/p/countries?phone_code=00880",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "X-API-TOKEN: {$api_key}"
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        echo "cURL Error #:" . $err;
    } else {
        $countries = json_decode( $response, true);
        update_option('roadcube_country_data', $countries['data']);
    }
}
function roadcube_user_register_init(array $user_data){
    $curl = curl_init();
    $api_key = Coupon_Claimer::roadcube_get_setting('api_key');
    $url = isset($user_data['mobile_verify']) ? "https://api.roadcube.io/v1/p/users/registration/mobile-verification" : "https://api.roadcube.io/v1/p/users/registration/init";
    unset($user_data['mobile_verify']);
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($user_data),
        CURLOPT_HTTPHEADER => array(
            "X-API-TOKEN: {$api_key}",
            'Content-Type: application/json'
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    if ($err) {
        echo "cURL Error #:" . $err;
    } else {
        $data = json_decode( $response, true);
        if( $data['status'] == "success" ) {
            $user_data['user_reg_id'] = $data['data']['user']['user_registration_identifier'];
            $user_data['status'] = "success";
            $user_data['data'] = $data;
            return $user_data;
        } else {
            $data['stage'] = "initial_registration";
            return $data;
        }
    }
    curl_close($curl);
}
function roadcube_user_register(array $user_data){
    $curl = curl_init();
    $api_key = Coupon_Claimer::roadcube_get_setting('api_key');
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.roadcube.io/v1/p/users/registration/finalize',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($user_data),
        CURLOPT_HTTPHEADER => array(
            "X-API-TOKEN: {$api_key}",
            'Content-Type: application/json'
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
        echo "cURL Error #:" . $err;
    } else {
        $data = json_decode( $response, true);
        if( $data['status'] == "success" ) {
            return $user_data;
        } else {
            $data['stage'] = "final_registration";
            return $data;
        }
    }
}
function roadcube_get_the_point( $user_mobile ){
    $curl = curl_init();
    $api_key = Coupon_Claimer::roadcube_get_setting('api_key');
    $dataset = [
        "user" => $user_mobile
    ];
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.roadcube.io/v1/p/users/points',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($dataset),
        CURLOPT_HTTPHEADER => array(
            "X-API-TOKEN: {$api_key}",
            'Content-Type: application/json'
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
        echo "cURL Error #:" . $err;
    } else {
        $data = json_decode( $response, true);
        return $data;
    }
}
function roadcube_get_available_coupons( $page = 1 ){
    $curl = curl_init();
    $api_key = Coupon_Claimer::roadcube_get_setting('api_key');
    // $dataset = [
    //     "user" => $user_mobile
    // ];
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.roadcube.io/v1/p/coupons?page={$page}",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            "X-API-TOKEN: {$api_key}",
        )
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);
    if ($err) {
        echo "cURL Error #:" . $err;
    } else {
        $data = json_decode( $response, true);
        return $data;
    }
}
function roadcube_coupon_claim( $user_mobile, $coupon_id ){
    $curl = curl_init();
    $api_key = Coupon_Claimer::roadcube_get_setting('api_key');
    $dataset = [
        "user" => $user_mobile,
        "coupon_id" => $coupon_id
    ];
    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://api.roadcube.io/v1/p/users/coupon-claims',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => json_encode($dataset),
    CURLOPT_HTTPHEADER => array(
        "X-API-TOKEN: {$api_key}",
        'Content-Type: application/json'
    ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);
    if ($err) {
        echo "cURL Error #:" . $err;
    } else {
        $data = json_decode( $response, true);
        $data['dataset'] = $dataset;
        return $data;
    }
}

function roadcube_checkout_new_transaction_call($user, $amount){
    $curl = curl_init();
    $api_key = Coupon_Claimer::roadcube_get_setting('api_key');
    $store_id = Coupon_Claimer::roadcube_get_setting('store_id');
    curl_setopt_array($curl, array(
    CURLOPT_URL => "https://api.roadcube.io/v1/p/stores/{$store_id}/transactions/new",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => json_encode(
        array(
            'user' => $user,
            'amount' => $amount
        )
    ),
    CURLOPT_HTTPHEADER => array(
        "X-Api-Token: {$api_key}",
        'Content-Type: application/json'
    ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);
    return json_decode($response,true);
}