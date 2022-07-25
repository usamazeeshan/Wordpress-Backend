<?php
if( !defined('ABSPATH') ){
    exit;
}
define('ROADCUBE_TRANS_PATH',plugin_dir_path(__FILE__));
define('ROADCUBE_TRANS_URL',plugin_dir_url(__FILE__));
include(ROADCUBE_TRANS_PATH.'core/api.php');
include(ROADCUBE_TRANS_PATH.'core/ajax_handle.php');
add_shortcode('roadcube_trans_list','roadcube_trans_list_callback');
function roadcube_trans_list_callback(){
    ob_start();
    include(ROADCUBE_TRANS_PATH.'templates/trans_list.php');
    return ob_get_clean();
}
add_action('wp_enqueue_scripts','roadcube_enqueue_script');
function roadcube_enqueue_script(){
    wp_enqueue_script('jquery');
    wp_enqueue_script('roadcube_trans_list_script', ROADCUBE_TRANS_URL.'/assets/js/trans_list.js');
    wp_enqueue_script('roadcube_trans_localize_script', ROADCUBE_TRANS_URL.'/assets/js/localize.js');
    wp_localize_script( 'roadcube_trans_localize_script', 'roadcube_trans', array(
        'ajax_url' => admin_url( 'admin-ajax.php' )
    ) );
    wp_enqueue_style('roadcube-font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css' );
    wp_enqueue_style('roadcube_trans_list_style', ROADCUBE_TRANS_URL.'/assets/css/trans_list.css');
}