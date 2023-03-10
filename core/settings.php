<?php
if(isset($_POST['save_settings'])){
    update_option('roadcube_settings',$_POST);
    roadcube_get_countries();
    printf('<div class="notice notice-success is-dismissible"><p>%s</p></div>',__("Save settings",'roadcube'));
}
if( isset($_POST['roadcube_sync_users']) ) {
    roadcube_sync_all_prev_users();
    printf('<div class="notice notice-success is-dismissible"><p>%s</p></div>',__("Users are being synced.",'roadcube'));
}
?>
<h1><?php _e('Coupon claim settings','roadcube'); ?></h1>
<table class="form-table">
    <tr>
        <th><?php _e('Sync users manually','roadcube'); ?></th>
        <td>
            <form method="post">
                <input type="submit" value="Sync users" name="roadcube_sync_users" class="button button-primary"/>
            </form>
        </td>
    </tr>
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
            <th><?php _e('Point charge on order status','roadcube'); ?></th>
            <td>
                <select name="roadcube_point_charge_status" id="">
                    <?php
                    $order_statuses = wc_get_order_statuses();
                    foreach($order_statuses as $key => $value){
                        $selected = Coupon_Claimer::roadcube_get_setting('roadcube_point_charge_status') == str_replace('wc-','',$key) ? 'selected' : '';
                        printf('<option %s value="%s">%s</option>',$selected,str_replace('wc-','',$key),$value);
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <th><?php _e('Point refund on order status','roadcube'); ?></th>
            <td>
                <select name="roadcube_point_refund_status" id="">
                    <?php
                    $order_statuses = wc_get_order_statuses();
                    foreach($order_statuses as $key => $value){
                        $selected = Coupon_Claimer::roadcube_get_setting('roadcube_point_refund_status') == str_replace('wc-','',$key) ? 'selected' : '';
                        printf('<option %s value="%s">%s</option>',$selected,str_replace('wc-','',$key),$value);
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <th><input type="submit" class="button button-primary" value="<?php _e('Save settings','roadcube'); ?>" name="save_settings"/></th>
        </tr>
    </form>
</table>
<h1><?php _e('Usage documentation','roadcube'); ?></h1>
<ol>
    <li><code>[roadcube_user_login]</code> - <?php _e('Shortcode to show user login UI.','roadcube'); ?></li>
    <li><code>[roadcube_show_gifts]</code> - <?php _e('Shortcode to show gifts.','roadcube'); ?></li>
    <li><code>[roadcube_get_user_points]</code> - <?php _e('Shortcode to show user points.','roadcube'); ?></li>
    <li><code>[roadcube_existing_user_register_form]</code> - <?php _e('Shortcode to show existing user registration form.','roadcube'); ?></li>
</ol>
<?php
// // update_user_meta(1,'roadcube_mobile','1737008004');
// // $countries = get_option('roadcube_country_data');
// $claimed_coupons = get_user_meta(get_current_user_id(),'roadcube_claimed_coupons',true);
// // $claimed_coupons = [];
// // krsort($claimed_coupons);
// echo '<pre>';
// print_r(end($claimed_coupons));
// echo '</pre>';