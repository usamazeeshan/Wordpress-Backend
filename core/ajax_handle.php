<?php
add_action('wp_ajax_send_verify_code','send_verify_code_callback');
add_action('wp_ajax_nopriv_send_verify_code','send_verify_code_callback');
function send_verify_code_callback(){
    if( isset( $_POST['dataset'])) {
        $data = $_POST['dataset'];
        $mobile = $data['mobile'];
        $country_id = $data['country_id'];
        $user_ini_reg_data = roadcube_user_register_init( [
            'mobile' => $mobile,
            'country_id' => $country_id,
            'tos' => true,
            'verify_mobile' => true
        ] );
        echo json_encode($user_ini_reg_data);
        exit;
    }
    exit;
}
add_action('wp_ajax_verify_phone_number','verify_phone_number_callback');
add_action('wp_ajax_nopriv_verify_phone_number','verify_phone_number_callback');
function verify_phone_number_callback(){
    if( isset( $_POST['dataset'])) {
        $data = $_POST['dataset'];
        $id = $data['user_registration_identifier'];
        $code = $data['mobile_verification_code'];
        $user_ini_reg_data = roadcube_user_register_init( [
            'user_registration_identifier' => $id,
            'mobile_verification_code' => $code,
            'mobile_verify' => true
        ] );
        echo json_encode($user_ini_reg_data);
        exit;
    }
    exit;
}
add_action('wp_ajax_roadcube_register_new_user','roadcube_register_new_user_callback');
add_action('wp_ajax_nopriv_roadcube_register_new_user','roadcube_register_new_user_callback');
function roadcube_register_new_user_callback(){
    if(isset($_POST['dataset'])){
        $data = $_POST['dataset'];
        $username = isset($data['username']) && !isset($data['user_exists']) ? $data['username'] : "";
        $roadcube_email = isset($data['roadcube_email']) && !isset($data['user_exists']) ? $data['roadcube_email'] : "";
        $country_id = $data['country_id'];
        $user_reg_id = $data['user_reg_id'];
        $mobile = $data['mobile'];
        $gender = $data['gender'];
        $dob = $data['dob'];
        $pass = isset($data['pass']) && !isset($data['user_exists']) ? $data['pass'] : "";
        $con_pass = isset($data['con_pass']) && !isset($data['user_exists']) ? $data['con_pass'] : "";
        if( !isset($data['user_exists']) ){
            $user_exists = get_user_by( 'login', $username );
            if( $user_exists ) {
                echo json_encode([
                    'status' => 'error',
                    'message' => __('Username already exists','roadcube'),
                    'data' => $data
                ]);
                exit;
            }
            
            $user_reg_data = roadcube_user_register( [
                'user_registration_identifier' => $user_reg_id,
                'gender' => $gender,
                'password' => $pass,
                'password_confirmation' => $con_pass,
                'birthday' => $dob,
                "marketing" => true
            ]);
        }
        if( isset($user_reg_data['status']) && $user_reg_data['status'] == "error" ) {
            echo json_encode( $user_reg_data );
            exit;
        }
        if( !isset($data['user_exists']) ) {
            $userdata = [
                'user_login' => $username,
                'user_pass' => $pass,
                'user_email' => $roadcube_email
            ];
            $user_id = wp_insert_user( $userdata );
            update_user_meta( $user_id, 'roadcube_gender', $gender );
            update_user_meta( $user_id, 'roadcube_birthday', $dob );
            update_user_meta( $user_id, 'roadcube_mobile', $mobile );
            update_user_meta( $user_id, 'roadcube_country_id', $country_id );
        } else {
            $user_id = $data['user_exists'];
            update_user_meta( $user_id, 'roadcube_mobile', $mobile );
        }
        echo json_encode([
            'status' => 'success',
            'roadcube_gender' => $gender,
            'roadcube_birthday' => $dob,
            'roadcube_mobile' => $mobile,
            'roadcube_country_id' => $country_id,
            'data' => $data
        ]);
        exit;
    }
    exit;
}
add_action('wp_ajax_roadcube_coupon_claim','roadcube_coupon_claim_callback');
function roadcube_coupon_claim_callback(){
    if(isset($_POST['dataset'])){
        $data = $_POST['dataset'];
        $data = roadcube_coupon_claim($data['user'],$data['coupon_id']);
        echo json_encode($data);
        exit;
    }
    exit;
}
add_action('wp_ajax_roadcube_get_available_coupons','roadcube_get_available_coupons_callback');
function roadcube_get_available_coupons_callback(){
    if(isset($_POST['dataset'])){
        $data = $_POST['dataset'];
        $page = $data['page'];
        $data = roadcube_get_available_coupons( $page );
        echo json_encode($data);
        exit;
    }
    exit;
}
add_action('wp_ajax_roadcube_get_user_available_coupons','roadcube_get_user_available_coupons_callback');
function roadcube_get_user_available_coupons_callback(){
    if(isset($_POST['dataset'])){
        $data = $_POST['dataset'];
        $page = $data['page'];
        $user_email = $data['user_email'];
        $data = roadcube_get_user_available_coupons( $page, $user_email );
        echo json_encode($data);
        exit;
    }
    exit;
}