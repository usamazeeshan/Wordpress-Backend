<?php
add_action('wp_ajax_roadcube_load_trans','roadcube_load_trans_callback');
function roadcube_load_trans_callback(){
    if(isset($_POST['dataset'])){
        $page = $_POST['dataset'];
        echo json_encode(roadcube_get_the_trans($page));
        exit;
    }
    exit;
}