<?php
if(isset($_POST['save_settings'])){
    update_option('roadcube_settings',$_POST);
    roadcube_get_countries();
    printf('<div class="notice notice-success is-dismissible"><p>%s</p></div>',__("Save settings",'roadcube'));
}
?>
<h1><?php _e('Coupon claim settings','roadcube'); ?></h1>
<table class="form-table">
    <form method="post">
        <tr>
            <th><?php _e('API Key','roadcube'); ?></th>
            <td><input required placeholder="<?php _e('API Key','roadcube'); ?>" type="text" value="<?php echo Coupon_Claimer::roadcube_get_setting('api_key'); ?>" name="api_key"/></td>
        </tr>
        <tr>
            <th><?php _e('Store ID','roadcube'); ?></th>
            <td><input required placeholder="<?php _e('Store ID','roadcube'); ?>" type="text" value="<?php echo Coupon_Claimer::roadcube_get_setting('store_id'); ?>" name="store_id"/></td>
        </tr>
        <tr>
            <th><?php _e('Login page URL','roadcube'); ?></th>
            <td><input required placeholder="<?php _e('Login page URL','roadcube'); ?>"  type="url" value="<?php echo Coupon_Claimer::roadcube_get_setting('login_page'); ?>" name="login_page"/></td>
        </tr>
        <tr>
            <th><?php _e('Login redirect page','roadcube'); ?></th>
            <td><input required placeholder="<?php _e('Login redirect page','roadcube'); ?>"  type="url" value="<?php echo Coupon_Claimer::roadcube_get_setting('login_redirect'); ?>" name="login_redirect"/></td>
        </tr>
        <tr>
            <th><input type="submit" class="button button-primary" value="<?php _e('Save settings','roadcube'); ?>" name="save_settings"/></th>
        </tr>
    </form>
</table>
<?php
// // update_user_meta(1,'roadcube_mobile','1737008004');
// // $countries = get_option('roadcube_country_data');
// $claimed_coupons = get_user_meta(get_current_user_id(),'roadcube_claimed_coupons',true);
// // $claimed_coupons = [];
// // krsort($claimed_coupons);
// echo '<pre>';
// print_r(end($claimed_coupons));
// echo '</pre>';