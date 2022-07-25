<div class="roadcube-container">
    <div class="roadcube-form">
      <div class="roadcube-control roadcube-control__username">
          <label><?php _e('Username','roadcube'); ?></label>
          <input type="text" value="" id="roadcube_username" placeholder="Username"/>
      </div>
      <div class="roadcube-control roadcube-control__username">
          <label><?php _e('Email','roadcube'); ?></label>
          <input type="text" value="" id="roadcube_email" placeholder="Email"/>
      </div>
      <div class="roadcube-control roadcube-control__select">
        <label><?php _e('Country','roadcube'); ?></label>
        <select id="roadcube_country_id">
          <option value=""><?php _e('Select a country','roadcube'); ?></option>
          <?php
          $countries = get_option('roadcube_country_data');
          foreach($countries as $country){
            printf('<option value="%s">%s</option>',$country['country_id'],$country['name']);
          }
          ?>
        </select>
      </div>
      <div class="roadcube-control roadcube-control__phone">
        <label><?php _e('Mobile number','roadcube'); ?></label>
        <input type="text" placeholder="<?php _e('Mobile number','roadcube'); ?>" id="roadcube_mobile"/>
        <input type="text" style="display:none;margin-top:8px;" placeholder="<?php _e('Verification code','roadcube'); ?>" id="roadcube_verify_code"/>
        <input type="hidden" id="user_reg_id"/>
        <input type="hidden" id="user_phone_verification"/>
        <button id="roadcube_send_verify_code" style="margin-top:8px;border:0px;background:black;color:white;"><?php _e('Send verification code','roadcube'); ?></button>
        <button id="roadcube_verify_phone" style="margin-top:8px;background:black;color:white;display:none;"><?php _e('Verify','roadcube'); ?></button>
      </div>
      <div class="roadcube-control roadcube-control__select">
          <label><?php _e('Gender','roadcube'); ?></label>
          <select id="roadcube_gender">
            <option value=""><?php _e('Select a gender','roadcube'); ?></option>
            <option value="female"><?php _e('Female','roadcube'); ?></option>
            <option value="male"><?php _e('Male','roadcube'); ?></option>
          </select>
      </div>
      <div class="roadcube-control roadcube-control__pass">
        <label><?php _e('Password','roadcube'); ?></label>
        <input type="password" placeholder="<?php _e('Password','roadcube'); ?>" id="roadcube_pass"/>
      </div>
      <div class="roadcube-control roadcube-control__con_pass">
        <label><?php _e('Confirm Password','roadcube'); ?></label>
        <input type="password" placeholder="<?php _e('Confirm Password','roadcube'); ?>" id="roadcube_con_pass"/>
      </div>
      <div class="roadcube-control roadcube-control__dob">
        <label><?php _e('Birthday','roadcube'); ?></label>
        <input type="date" placeholder="<?php _e('Birthday','roadcube'); ?>" id="roadcube_dob"/>
      </div>
      <div class="roadcube-control roadcube-control__tos">
        <label>
          <input type="checkbox" id="roadcube_tos"/>
          <?php _e('Accept terms of service','roadcube'); ?>
        </label>
      </div>
      <div class="roadcube-control roadcube-control__register">
        <button id="roadcube-register-btn" style="background:black;color:white;"><?php _e('Register','roadcube'); ?></button>
      </div>
    </div>
</div>