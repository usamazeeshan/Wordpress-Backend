<?php
$user_id = get_current_user_id();
if( !$user_id ) {
  echo 'Login first to see the point';
  return false;
}
$user_data = get_userdata($user_id);
$user_mobile = get_user_meta($user_id, 'roadcube_mobile', true) ?: $user_data->user_email;
if( !$user_mobile ) {
    echo 'User mobile number is not set.';
    return false;
}
$data = roadcube_get_the_point($user_mobile);
if( isset($data['status']) && $data['status'] == "error" ) {
    if( isset($data['message']) ) {
        echo $data['message'];
    } else {
        echo __('Unknown error occured','roadcube');
    }
    return false;
}
?>
<div class="roadcube-container">
    <div class="roadcube-point-holder">
        <p class="roadcube-point-title"><?php _e('Your Points:','roadcube'); ?></p>
        <p class="roadcube-point"><?php echo $data['data']['current_balance']; ?></p>
    </div>
</div>