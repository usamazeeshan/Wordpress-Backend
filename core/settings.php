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
if( isset($_POST['roadcube_sync_products']) ) {
    roadcube_sync_all_prev_products();
    printf('<div class="notice notice-success is-dismissible"><p>%s</p></div>',__("Products are being synced.",'roadcube'));
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
    <tr>
        <th><?php _e('Sync products manually','roadcube'); ?></th>
        <td>
            <form method="post">
                <input type="submit" value="Sync products" name="roadcube_sync_products" class="button button-primary"/>
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
            <th><?php _e('Create transaction by','roadcube'); ?></th>
            <td>
                <select name="roadcube_create_transaction_settings" id="">
                    <option <?php echo Coupon_Claimer::roadcube_get_setting('roadcube_create_transaction_settings') == "amount" ? "selected" : ""; ?>  value="amount">Amount</option>
                    <option <?php echo Coupon_Claimer::roadcube_get_setting('roadcube_create_transaction_settings') == "product" ? "selected" : ""; ?> value="product">Product</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><?php _e('Offline Store Ids - Seperate by comma(,)','roadcube'); ?></th>
            <td>
                <input type="text" name="roacube_offline_store_ids" id="roacube_offline_store_ids" value="<?php echo Coupon_Claimer::roadcube_get_setting('roacube_offline_store_ids'); ?>">
            </td>
        </tr>
        <tr>
            <th><?php _e('GA4 client ID','roadcube'); ?></th>
            <td>
                <input type="text" name="roacube_ga4_client_id" id="roacube_ga4_client_id" value="<?php echo Coupon_Claimer::roadcube_get_setting('roacube_ga4_client_id'); ?>">
            </td>
        </tr>
        <tr>
            <th><?php _e('GA4 measurement ID','roadcube'); ?></th>
            <td>
                <input type="text" name="roacube_ga4_m_id" id="roacube_ga4_m_id" value="<?php echo Coupon_Claimer::roadcube_get_setting('roacube_ga4_m_id'); ?>">
            </td>
        </tr>
        <tr>
            <th><?php _e('GA4 measurement api secret','roadcube'); ?></th>
            <td>
                <input type="text" name="roacube_ga4_m_api_secret" id="roacube_ga4_m_api_secret" value="<?php echo Coupon_Claimer::roadcube_get_setting('roacube_ga4_m_api_secret'); ?>">
            </td>
        </tr>
        <tr>
            <th><?php _e('Point charge on order status','roadcube'); ?></th>
            <td>
                <select name="roadcube_point_charge_status" name="roadcube_charge_point[]" id="roadcube_charge_point" multiple="multiple">
                    <?php
                    $order_statuses = wc_get_order_statuses();
                    foreach($order_statuses as $key => $value){
                        $selected = Coupon_Claimer::roadcube_get_setting('roadcube_point_charge_status') == str_replace('wc-','',$key) ? 'selected' : '';
                        printf('<option %s value="%s">%s</option>',$selected,str_replace('wc-','',$key),$value);
                    }
                    ?>
                </select>
                <input type="hidden" id="roadcube_charge_point_val" name="roadcube_charge_point_val" value="<?php echo Coupon_Claimer::roadcube_get_setting('roadcube_charge_point_val'); ?>"/>
                <script>
                    jQuery(document).ready(function($){
                        $('#roadcube_charge_point').select2('val')
                        let this_val = $('#roadcube_charge_point_val').val().split(',')
                        console.log(this_val)
                        $('#roadcube_charge_point').val(this_val)
                        $('#roadcube_charge_point').trigger('change')
                        $('#roadcube_charge_point').on('change.select2',function(){
                            $('#roadcube_charge_point_val').val($('#roadcube_charge_point').val().join(','))
                        })
                    })
                </script>
            </td>
        </tr>
        <tr>
            <th><?php _e('Point refund on order status','roadcube'); ?></th>
            <td>
                <select name="roadcube_point_refund_status" name="roadcube_refund_point[]" id="roadcube_refund_point" multiple="multiple">
                    <?php
                    $order_statuses = wc_get_order_statuses();
                    foreach($order_statuses as $key => $value){
                        $selected = Coupon_Claimer::roadcube_get_setting('roadcube_point_refund_status') == str_replace('wc-','',$key) ? 'selected' : '';
                        printf('<option %s value="%s">%s</option>',$selected,str_replace('wc-','',$key),$value);
                    }
                    ?>
                </select>
                <input type="hidden" id="roadcube_refund_point_val" name="roadcube_refund_point_val" value="<?php echo Coupon_Claimer::roadcube_get_setting('roadcube_refund_point_val'); ?>"/>
                <script>
                    jQuery(document).ready(function($){
                        $('#roadcube_refund_point').select2('val')
                        let this_val = $('#roadcube_refund_point_val').val().split(',')
                        console.log(this_val)
                        $('#roadcube_refund_point').val(this_val)
                        $('#roadcube_refund_point').trigger('change')
                        $('#roadcube_refund_point').on('change.select2',function(){
                            $('#roadcube_refund_point_val').val($('#roadcube_refund_point').val().join(','))
                        })
                    })
                </script>
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
// $data = roadcube_save_product_callback(61);
// // update_user_meta(1,'roadcube_mobile','1737008004');
// // $countries = get_option('roadcube_country_data');
// $claimed_coupons = get_user_meta(get_current_user_id(),'roadcube_claimed_coupons',true);
// // $claimed_coupons = [];
// // krsort($claimed_coupons);
// update_option('roadcube_previous_offline_store_transactions',false);
echo '<pre>';
// print_r(get_post_meta(6111,'roadcube_product_created_data',true));
// print_r(get_option('roadcube_product_log',[]));
// print_r(get_option('roadcube_previous_offline_store_transactions',[]));
// print_r(get_option('roadcube_ga4_events',[]));
// print_r(roadcube_get_product_category());
echo '</pre>';