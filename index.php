<?php
/**
 * 
 * Plugin Name: RoadCube
 * Author: RoadCube
 * Version: 1.0.1
 * Text Domain: roadcube
 * 
 */
if(!defined('ABSPATH')){
    exit;
}
if( !class_exists( 'Coupon_Claimer') ) {
    class Coupon_Claimer{
        static protected $instance = NULL;
        static function get_instance(){
            if( self::$instance == NULL ) {
                self::$instance == new self();
            }
            return self::$instance;
        }
        function __construct(){
            $this->consts();
            $this->includes();
            // registration shortcode
            add_shortcode('roadcube_register',[$this,'roadcube_register_callback']);
            // enqueue
            add_action('wp_enqueue_scripts',[$this,'roadcube_enqueue']);
            // admin enqueue
            add_action('admin_enqueue_scripts',[$this,'roadcube_admin_enqueue']);
            // settings
            add_action('admin_menu',[$this,'roadcube_settings']);
            // user form shortcode
            add_shortcode('roadcube_user_register_form',[$this,'roadcube_user_register_form']);
            // existing user form shortcode
            add_shortcode('roadcube_existing_user_register_form',[$this,'roadcube_existing_user_register_form']);
            // get user point shortcode
            add_shortcode('roadcube_get_user_points',[$this,'roadcube_get_user_points_callback']);
            // get user show gifts
            add_shortcode('roadcube_show_gifts',[$this,'roadcube_show_gifts_callback']);
            // get user show gifts
            add_shortcode('roadcube_user_login',[$this,'roadcube_user_login_callback']);
        }
        function roadcube_show_gifts_callback(){
            ob_start();
            include(ROADCUBE_PATH.'templates/show_gifts.php');
            return ob_get_clean();
        }
        function roadcube_admin_enqueue(){
            $version = rand(1,9).'.'.rand(0,9);
            wp_enqueue_style( 'roaddcube_select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css' );
            wp_enqueue_script( 'jquery' );
            // enqueue swal
            wp_enqueue_script('roadcube_swal2', '//cdn.jsdelivr.net/npm/sweetalert2@11');
//             wp_enqueue_script('roadcube_select2_script', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js');
wp_enqueue_script('roadcube_select2_script', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array(), '4.1.0-rc.0');             
            wp_enqueue_script('roadcube_script', ROADCUBE_URL.'assets/js/script.js',array(), $version);
            // localize ajax
            wp_enqueue_script('roadcube_localize', ROADCUBE_URL.'assets/js/localize.js');
            $localize_data =  array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'login_url' => Coupon_Claimer::roadcube_get_setting('login_page') ?: wp_login_url()
            );
            wp_localize_script('roadcube_localize','roadcube',$localize_data);
        }
        function roadcube_enqueue(){
            $version = rand(1,9).'.'.rand(0,9);
            // css
            wp_enqueue_style('roadcube_style', ROADCUBE_URL.'assets/css/style.css');
            wp_enqueue_style( 'roadcube-font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css' );
            // js
            wp_enqueue_script('jquery');
            wp_enqueue_script('roadcube_swal2', '//cdn.jsdelivr.net/npm/sweetalert2@11');
            wp_enqueue_script('roadcube_script', ROADCUBE_URL.'assets/js/script.js',array(), $version);
            wp_enqueue_script('roadcube_coupon_manager_script', ROADCUBE_URL.'assets/js/coupon_manager.js',array(), $version);
            wp_enqueue_script('roadcube_localize', ROADCUBE_URL.'assets/js/localize.js');
            $localize_data =  array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'login_url' => Coupon_Claimer::roadcube_get_setting('login_page') ?: wp_login_url()
            );
            if( get_current_user_id() ) {
                $user_id = get_current_user_id();
                $user_data = get_userdata( $user_id );
                $user_email = $user_data->user_email;
                $user_mobile = get_user_meta( $user_id, 'roadcube_mobile', true );
                $user_mobile = $user_data->user_email;
                if( $user_mobile) {
                    $localize_data['user_mobile'] = $user_mobile;
                }
                $localize_data['coupon_data'] = roadcube_get_user_available_coupons( 1, $user_email );
            }
            wp_localize_script('roadcube_localize', 'roadcube', $localize_data);
        }
        function roadcube_user_register_form(){
            ob_start();
            include(ROADCUBE_PATH.'templates/register-template.php');
            return ob_get_clean();
        }
        function roadcube_existing_user_register_form(){
            ob_start();
            include(ROADCUBE_PATH.'templates/register-to-server-template.php');
            return ob_get_clean();
        }
        function roadcube_settings(){
            add_menu_page(
                __('RoadCube','roadcube'),
                __('RoadCube','roadcube'),
                'manage_options',
                'roadcube_settings',
                [$this,'roadcube_settings_callback']
            );
        }
        static function roadcube_get_setting( $name ) {
            $settings = get_option('roadcube_settings');
            if( $settings && is_array($settings) && array_key_exists($name,$settings) ) {
                return $settings[$name];
            }
            return false;
        }
        function roadcube_settings_callback(){
            include(ROADCUBE_PATH.'core/settings.php');
        }
        function consts(){
            define('ROADCUBE_PATH',plugin_dir_path(__FILE__));
            define('ROADCUBE_URL',plugin_dir_url(__FILE__));
        }
        function includes(){
            include(ROADCUBE_PATH.'core/api.php');
            include(ROADCUBE_PATH.'core/ajax_handle.php');
            include(ROADCUBE_PATH.'core/user_meta_field.php');
            include(ROADCUBE_PATH.'roadcube-transaction-list/index.php');
            include(ROADCUBE_PATH.'core/woocommerce.php');
            include(ROADCUBE_PATH.'core/create_product.php');
            include(ROADCUBE_PATH.'core/ga4.php');
        }
        function activate(){
            flush_rewrite_rules();
        }
        function roadcube_user_login_callback(){
            ob_start();
            include(ROADCUBE_PATH.'templates/login_ui.php');
            return ob_get_clean();
        }
        function roadcube_get_user_points_callback(){
            ob_start();
            include(ROADCUBE_PATH.'templates/get_user_points.php');
            return ob_get_clean();
        }
        function roadcube_register_callback(){
            ob_start();
            include(ROADCUBE_PATH.'templates/register-template.php');
            return ob_get_clean();
        }
    }
    Coupon_Claimer::get_instance();
}
add_action( 'login_init', 'myplugin_add_login_fields' );
 
function myplugin_add_login_fields() {
    $user_name = isset($_POST['log']) ? $_POST['log'] : false;
    if( !$user_name ) return;
    global $wpdb;
    $table = $wpdb->prefix.'usermeta';
    $results = $wpdb->get_results("SELECT * FROM $table WHERE meta_value='$user_name'",ARRAY_A);
    if( count($results) > 0 ) {
        $user_id = $results[0]['user_id'];
        $user_data = get_userdata( $user_id );
        $_POST['user_login'] = $user_data->user_login;
    }
}
register_activation_hook(__FILE__, 'roadcube_sync_all_prev_users');
function roadcube_sync_all_prev_users(){
    $users = get_users();
    if( empty($users) ) return;
    $emails = [];
    $i = 1;
    foreach($users as $user){
        $emails[] = $user->user_email;
    }
    $i = 1;
    foreach(array_chunk($emails,10) as $email_chunk) {
        wp_schedule_single_event(time() + 100 * $i, 'roadcube_synce_users', array( $email_chunk ) );
        $i++;
    }
}
add_action('roadcube_synce_users','roadcube_synce_users_callback');
function roadcube_synce_users_callback( $emails ){
    foreach($emails as $email){
        $user_sync_log = [];
        $user_sync_log[] = roadcube_create_user_by_email($email);
        update_option('roadcube_user_sync_log',$user_sync_log);
    }
}



//==================== Points against total price at checkout

// Add points to the checkout page
function my_points_checkout_display() {
    $conversion_rate = 1; // Set the conversion rate (e.g., 1 point per 1 dollar)
    $order_total = WC()->cart->subtotal; // Get the order total
    
    $points_earned = $order_total * $conversion_rate; // Calculate the points earned

    echo '<div class="my-points-badge">Points Earned: ' . $points_earned . '</div>';
    
   // echo '<div style="position: fixed; top: 50%; right: 20px; transform: translateY(-50%); padding: 10px; background-color: #ff0000; color: #fff; font-size: 16px; border-radius: 4px; z-index: 9999;">Points Earned: ' . $points_earned . '</div>';
   echo '<div style="position: fixed; top: 30px; right: 30px; padding: 6px 10px; background-color: #ffffff; color: #333333; font-size: 14px; font-weight: bold; border: 2px solid #333333; border-radius: 20px; z-index: 9999;">Points: ' . $points_earned . '</div>';


}

add_action('woocommerce_review_order_before_payment', 'my_points_checkout_display');

// Save points in user meta
function my_points_save_user_points($order_id) {
    $order = wc_get_order($order_id);
    $user_id = $order->get_user_id();
    $points_earned = $order->get_subtotal(); // Assuming points earned = order subtotal
    
    update_user_meta($user_id, 'my_points', $points_earned);
}
add_action('woocommerce_order_status_completed', 'my_points_save_user_points');

// Create shortcode to display points on user profile
function my_points_display_user_points() {
    $user_id = get_current_user_id();
    $points_earned = get_user_meta($user_id, 'my_points', true);
    
    return '<p>Points Earned: ' . $points_earned . '</p>';
}
add_shortcode('my_points', 'my_points_display_user_points');

function my_points_display_user_wp_points($user) {
    $points_earned = get_user_meta($user->ID, 'my_points', true);

    ?> <h2><?php _e('Points', 'your-text-domain'); ?></h2>
<table class="form-table">
    <tr>
        <th><label for="my_points"><?php _e('Points Earned', 'roadcube'); ?></label></th>
        <td><input type="text" id="my_points" name="my_points" value="<?php echo esc_attr($points_earned); ?>"
                class="regular-text" readonly /></td>
    </tr>
</table> <?php
}
add_action('show_user_profile', 'my_points_display_user_wp_points');
add_action('edit_user_profile', 'my_points_display_user_wp_points');

function my_points_save_user_wp_points($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return;
    }
    
    if (isset($_POST['my_points'])) {
        $points_earned = sanitize_text_field($_POST['my_points']);
        update_user_meta($user_id, 'my_points', $points_earned);
    }
}
add_action('personal_options_update', 'my_points_save_user_wp_points');
add_action('edit_user_profile_update', 'my_points_save_user_wp_points');
  
function ch_add_points_earned_endpoint() {
    add_rewrite_endpoint( 'points-earned', EP_ROOT | EP_PAGES );
}
  
add_action( 'init', 'ch_add_points_earned_endpoint' );
  
// ------------------
// 2. Add new query var
  
function ch_points_earned_query_vars( $vars ) {
    $vars[] = 'points-earned';
    return $vars;
}
  
add_filter( 'query_vars', 'ch_points_earned_query_vars', 0 );
  
// ------------------
// 3. Insert the new endpoint into the My Account menu
  
function ch_add_points_earned_link_my_account( $items ) {
    $items['points-earned'] = 'Points Earned';
    return $items;
}
  
add_filter( 'woocommerce_account_menu_items', 'ch_add_points_earned_link_my_account' );
  
// ------------------
// 4. Add content to the new tab
  
function ch_points_earned_content() {
   echo '<h3>Your Rewarded Points Are:</h3>';
   echo do_shortcode( ' [roadcube_get_user_points] ' );

}
  
add_action( 'woocommerce_account_points-earned_endpoint', 'ch_points_earned_content' );

//======================== Insert new tab for Phone verification

function ch_verify_phone_endpoint() {
    add_rewrite_endpoint( 'verify-phone', EP_ROOT | EP_PAGES );
}
  
add_action( 'init', 'ch_verify_phone_endpoint' );
  
// ------------------
// 2. Add new query var
  
function ch_verify_phone_query_vars( $vars ) {
    $vars[] = 'verify-phone';
    return $vars;
}
  
add_filter( 'query_vars', 'ch_verify_phone_query_vars', 0 );
  
// ------------------
// 3. Insert the new endpoint into the My Account menu
  
function ch_verify_phone_link_my_account( $items ) {
    $items['verify-phone'] = 'Verify Phone Number';
    return $items;
}
  
add_filter( 'woocommerce_account_menu_items', 'ch_verify_phone_link_my_account' );
  
// ------------------
// 4. Add content to the new tab
  
function ch_verify_phone_content() {
   echo do_shortcode( ' [roadcube_verify_phone_shortcode] ' );
}
  
add_action( 'woocommerce_account_verify-phone_endpoint', 'ch_verify_phone_content' );


//========================== Handle redeem coupon functionality on the basis of cart total and amount specified
// Add the submenu item
function add_amount_settings_submenu() {
    add_submenu_page(
        'roadcube_settings',
        'Amount Settings',
        'Amount Settings',
        'manage_options',
        'amount_settings',
        'render_amount_settings_page'
    );
}

add_action('admin_menu', 'add_amount_settings_submenu');

// Render the Amount Settings Page
function render_amount_settings_page() {
    ?> <div class="wrap">
    <h2>Amount Settings</h2>
    <form method="post" action=""> <?php
            wp_nonce_field('update_specific_amount', 'specific_amount_nonce');
            $specific_amount = get_option('specific_amount', ''); // Retrieve the specific amount from options
            ?> <label for="specific_amount">Enter Specific Amount:</label>
        <input type="text" name="specific_amount" id="specific_amount"
            value="<?php echo esc_attr($specific_amount); ?>">
        <p class="description">Enter the specific amount here.</p>
        <input type="submit" name="save_specific_amount" class="button-primary" value="Save">
    </form>
</div> <?php
}

// Handle form submission
function handle_specific_amount_form_submission() {
    if (isset($_POST['save_specific_amount'])) {
        if (isset($_POST['specific_amount_nonce']) && wp_verify_nonce($_POST['specific_amount_nonce'], 'update_specific_amount')) {
            $specific_amount = sanitize_text_field($_POST['specific_amount']);
            update_option('specific_amount', $specific_amount); // Save the specific amount to options
        }
    }
}

add_action('admin_init', 'handle_specific_amount_form_submission');
function check_cart_total() {
    global $woocommerce;
    $specific_amount = floatval(get_option('specific_amount')); // Convert to a float
 //   echo ($specific_amount);
// Get the cart total and remove any currency symbols
    // Calculate the cart total
    $cart_total = 0;
    foreach ( $woocommerce->cart->get_cart() as $cart_item ) {
        $cart_total += $cart_item['line_total'] + $cart_item['line_tax'];
    }
// 	echo "<br/>";
// 	echo($cart_total);
    if ($cart_total < $specific_amount) {		
        // Remove coupon form on both cart and checkout pages
       // remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10);
        //remove_action('woocommerce_before_cart', 'woocommerce_cart_coupon', 10);
        // Hide the element with ID "roadcube-show-coupons"
		?> <script>
jQuery(document).ready(function($) {
    $('#roadcube-show-coupons').hide();
});
</script> <?php
    }
}

add_action('woocommerce_before_cart', 'check_cart_total');
add_action('woocommerce_before_checkout_form', 'check_cart_total');

// If it's the cart or checkout page, add extra JS code
function add_js_code() {
    if (is_cart() || is_checkout()) {
        echo '<script>
            jQuery(document).ready(function($) {
                $(".woocommerce-page .woocommerce-cart-form .actions button.button").click(function() {
                    // Wait for 2 seconds and then refresh the page
                    setTimeout(function() {
                        location.reload();
                    }, 2000); // 2000 milliseconds (2 seconds)
                });
            });
        </script>';
    }
}
//add_action('wp_footer', 'add_js_code');



function woocommerce_product_custom_fields()
{
  $args = array(
        'id' => 'roadcube_product_sync',
        'label' => __('Roadcube Product Sync', 'rps'),
  );
  woocommerce_wp_text_input($args);
}
 
add_action('woocommerce_product_options_general_product_data', 'woocommerce_product_custom_fields');


function woocommerce_product_point_field()
{
  $args = array(
        'id' => 'roadcube_product_points',
        'label' => __('Roadcube Product Point', 'rpp'),
  );
  woocommerce_wp_text_input($args);
}
 
add_action('woocommerce_product_options_general_product_data', 'woocommerce_product_point_field');
