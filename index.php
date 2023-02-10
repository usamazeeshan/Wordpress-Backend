<?php
/**
 * 
 * Plugin Name: RoadCube
 * Author: RoadCube
 * Version: 1.0
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
    $user_name = isset($_POST['log']) ? $_PSOT['log'] : false;
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