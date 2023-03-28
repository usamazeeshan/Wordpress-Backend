<?php
add_action( 'show_user_profile', 'roadcube_user_profile_fields' );
add_action( 'edit_user_profile', 'roadcube_user_profile_fields' );

function roadcube_user_profile_fields( $user ) { ?>
    <?php
    if( !is_admin() ) {
        return;
    }
    ?>
    <h3><?php _e("RoadCube registered phone number", "blank"); ?></h3>
    <table class="form-table">
    <tr>
        <th><label for="roadcube_phone"><?php _e("Roadcube phone number","roadcube"); ?></label></th>
        <td>
            <input style="margin-bottom:8px;" type="text" placeholder="Phone number" name="roadcube_phone" id="roadcube_phone" value="<?php echo esc_attr( get_user_meta( $user->ID, 'roadcube_mobile', true ) ); ?>" class="regular-text" />
            <?php if( !get_user_meta( $user->ID, 'roadcube_mobile', true ) ) { ?>
                <input style="margin-bottom:8px;display:none;" type="text" placeholder="Verify code" name="roadcube_phone" id="roadcube_verify_number_input" value="" class="regular-text" />
                <button type="button" class="button button-primary" id="roadcube_set_phone_number">Set phone number</button>
                <button type="button" style="display:none;" class="button button-primary" id="roadcube_verify_btn">Verify phone number</button>
            <?php } ?>
            <!-- <span class="description"><?php _e("Please enter your roadcube phone number."); ?></span> -->
        </td>
    </tr>
    </table>
<?php }
// add_action( 'personal_options_update', 'save_roadcube_user_profile_fields' );
// add_action( 'edit_user_profile_update', 'save_roadcube_user_profile_fields' );

function save_roadcube_user_profile_fields( $user_id ) {
    if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'update-user_' . $user_id ) ) {
        return;
    }
    
    if ( !current_user_can( 'edit_user', $user_id ) ) { 
        return false; 
    }
    update_user_meta( $user_id, 'roadcube_mobile', $_POST['roadcube_phone'] );
}
add_action('user_register','roadcube_wp_insert_user',10,2);
function roadcube_wp_insert_user($user_id, $user_data){
    $email = $user_data['user_email'];
    roadcube_create_user_by_email($email);
}