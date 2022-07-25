<?php
$redirect = Coupon_Claimer::roadcube_get_setting('login_redirect') ?: home_url( '/' );
$args = array(
    'echo'            => true,
    'redirect'        => $redirect,
    'remember'        => true,
    'value_remember'  => true
    );
wp_login_form( $args );