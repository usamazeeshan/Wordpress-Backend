<?php
$show = true;
$user_id = get_current_user_id();
if( !$user_id ) {
//   echo __('Login first to see the available coupons','roadcube');
//   return false;
    $show = false;
}
$user_mobile = get_user_meta($user_id, 'roadcube_mobile', true);
if( !$user_mobile ) {
    // echo 'User mobile number is not set.';
    // return false;
    $show = false;
}
$data = roadcube_get_available_coupons();
if( isset($data['status']) && $data['status'] == "error" ) {
    if( isset($data['message']) ) {
        echo $data['message'];
    } else {
        echo __('Unknown error occured','roadcube');
    }
    return false;
}
?>
<div class="roadcube-show-gifts-container">
    <div class="roadcube-gifts-holder">
        <?php if(!empty($data['data']['coupons'])){ ?>
            <?php foreach($data['data']['coupons'] as $coupon){ ?>
                <div class="roadcube-gift">
                    <div class="coupon-image">
                        <img src="<?php echo $coupon['image']; ?>" alt="coupon image">
                    </div>
                    <div class="coupon-details">
                        <h2><?php echo $coupon['title']; ?></h2>
                        <p><?php printf('Points: %s',__($coupon['points'],'roadcube')); ?></p>
                        <!-- <p><?php printf('Product code: %s',__($coupon['product_code'],'roadcube')); ?></p> -->
                        <p><?php echo $coupon['description']; ?></p>
                        <?php if($show){ ?>
                        <button data-coupon-id="<?php echo $coupon['coupon_id']; ?>" class="roadcube-claim-coupon"><?php _e('Get it','roadcube'); ?></button>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        <?php } else {
            echo __('No coupon found.');
        } ?>
    </div>
    <div class="roadcube-pagination">
        <?php
        if( $data['data']['pagination']['next_page'] ) {
            printf('<button id="roadcube_next">%s</button>',__('Next','roadcube'));
        }
        if( $data['data']['pagination']['previous_page'] ) {
            printf('<button id="roadcube_prev">%s</button>',__('Previous','roadcube'));
        }
        ?>
    </div>
    <!-- <div class="roadcube-gift">
        <div class="coupon-image">
            <img src="//via.placeholder.com/150x150" alt="">
        </div>
        <div class="coupon-details">
            <h2>Title</h2>
            <p>Description</p>
            <button class="roadcube-claim-coupon">Claim coupon</button>
        </div>
    </div> -->
</div>