jQuery(document).ready(function($){
    $('#roadcube_verify_btn').on('click', function(e){
        let verify_token = $('#roadcube_phone').attr('data-verify-token')
        if( !verify_token ) {
            Swal.fire({
                icon: "warning",
                text: "Invalid verification token."
            })
            return false
        }
        let verify_number = $('#roadcube_verify_number_input').val()
        if( !verify_number ) {
            Swal.fire({
                icon: "warning",
                text: "Verification code is required."
            })
            return false
        }
        $.ajax({
            url: roadcube.ajax_url,
            dataType: "json",
            type: "POST",
            data: {
                action: "roadcube_verify_phone_number",
                dataset: {verify_token, verify_number}
            },
            success: resp => {
                console.log(resp)
                if( resp.status && resp.status == "success" ) {
                    Swal.fire({
                        icon: "success",
                        text: "Phone number is verified."
                    }).then( response => {
                        window.location.reload()
                    })
                } else {
                    Swal.fire({
                        icon: "error",
                        text: resp.message
                    })
                }
            }
        })
    })
    $('#roadcube_set_phone_number').on('click', function(e){
        let this_btn = $(this)
        let phone = $('#roadcube_phone').val()
        let email = $('#email').val()
        if( !phone ) {
            Swal.fire({
                icon: "warning",
                text: "Put the phone number first."
            })
            return false
        }
        if( !email ) {
            Swal.fire({
                icon: "warning",
                text: "Email is required."
            })
            return false
        }
        this_btn.html('<i class="fa fa-refresh fa-spin"></i> Set phone number')
        $.ajax({
            url: roadcube.ajax_url,
            dataType: "json",
            type: "POST",
            data: {
                action: "roadcube_set_phone_number",
                dataset: {phone, email}
            },
            success: resp => {
                console.log(resp)
                this_btn.html('Set phone number')
                if( resp.status && resp.status == "success" ) {
                    $('#roadcube_phone').attr('data-verify-token',resp.data.email_mobile_identifier)
                    $('#roadcube_verify_number_input').show()
                    $('#roadcube_verify_btn').show()
                    this_btn.hide()
                    // Swal.fire({
                    //     icon: "success",
                    //     text: "Phone number is set."
                    // }).then( response => {
                    //     window.location.reload()
                    // })
                } else {
                    Swal.fire({
                        icon: "error",
                        text: resp.message
                    })
                }
            },
            error: err => {
                this_btn.html('Set phone number')
                console.log(err)
            }
        })
    })
    if(  $('#roadcube_charge_point, #roadcube_refund_point').length > 0 ) {
        $('#roadcube_charge_point, #roadcube_refund_point').select2()
    }
    // redeem the coupon
    $(document).on('click','.roadcube-claim-coupon', e => {
        let this_el = $(e.currentTarget)
        let coupon_id = this_el.attr('data-coupon-id')
        this_el.html('<i class="fa fa-refresh fa-spin"></i> Get it')
        if( !roadcube.user_mobile ) {
            Swal.fire({
                icon: "warning",
                text: "User is not logged in or user is not registered to claim a coupon."
            })
            return false
        }
        let dataset = {
            "user": roadcube.user_mobile,
            "coupon_id": coupon_id
        }
        if( this_el.attr('data-popup-claim') ) {
            let checkout_claim = true
            let title = this_el.attr('data-coupon-title')
            let cost = this_el.attr('data-coupon-cost')
            dataset = {...dataset, checkout_claim, title, cost }
        }
        $.ajax({
            url: roadcube.ajax_url,
            dataType: "json",
            type: "POST",
            data: {
                action: "roadcube_coupon_claim",
                dataset: dataset
            },
            success: resp => {
                console.log(resp)
                if( resp.status && resp.status == "error" ) {
                    Swal.fire({
                        icon: "error",
                        text: resp.message
                    })
                } else if ( resp.status && resp.status == "success" ) {
                    let title = resp.data != undefined ? resp.data.title : resp.title
                    Swal.fire({
                        icon: "success",
                        text: `${title} is yours.`

                    }).then( response => {
                        console.log(response)
                        window.location.reload()
                    })
                }
                console.log(resp)
                this_el.html('Get it')
            },
            error: err => {
                console.log(err)
                this_el.html('Get it')
            }
        })
    })
    $(document).on('click','#roadcube_send_verify_code', e => {
        let this_el = $(e.currentTarget)
        let country_id = $('#roadcube_country_id').val()
        let mobile = $('#roadcube_mobile').val()
        if( !country_id ){
            roadcube_validator_msg("Country is required.")
            return false
        }
        if( !mobile ) {
            roadcube_validator_msg("Mobile is required.")
            return false
        }
        this_el.html('<i class="fa fa-refresh fa-spin"></i> Send Verification code')
        let dataset = { mobile, country_id }
        $.ajax({
            url: roadcube.ajax_url,
            dataType: "json",
            type: "POST",
            data: {
                action: "send_verify_code",
                dataset: dataset
            },
            success: resp => {
                if( resp.status && resp.status == "error" ) {
                    Swal.fire({
                        icon: "error",
                        text: resp.message
                    })
                } else if ( resp.status && resp.status == "success" ) {
                    Swal.fire({
                        icon: "success",
                        text: `Verification code has been sent.`
                    })
                    $('#user_reg_id').val(resp.user_reg_id)
                    $('#roadcube_verify_code').show()
                    this_el.hide()
                    $('#roadcube_verify_phone').show()
                }
                console.log(resp)
                this_el.html('Send Verification code')
            },
            error: err => {
                console.log(err)
                this_el.html('Send Verification code')
            }
        })
    })
    $(document).on('click','#roadcube_verify_phone', e => {
        let this_el = $(e.currentTarget)
        let user_registration_identifier = $('#user_reg_id').val()
        let mobile_verification_code = $('#roadcube_verify_code').val()
        if( !user_registration_identifier ) {
            roadcube_validator_msg("Verification code did not send the phone number.")
            return false
        }
        if( !mobile_verification_code ) {
            roadcube_validator_msg("Verification code is required.")
            return false
        }
        this_el.html('<i class="fa fa-refresh fa-spin"></i> Verify')
        let dataset = { user_registration_identifier, mobile_verification_code }
        $.ajax({
            url: roadcube.ajax_url,
            dataType: "json",
            type: "POST",
            data: {
                action: "verify_phone_number",
                dataset: dataset
            },
            success: resp => {
                if( resp.status && resp.status == "error" ) {
                    Swal.fire({
                        icon: "error",
                        text: resp.message
                    })
                } else if ( resp.status && resp.status == "success" ) {
                    Swal.fire({
                        icon: "success",
                        text: `Phone number has been verified.`
                    })
                    // $('#user_reg_id').val(resp.user_reg_id)
                    $('#roadcube_verify_code').hide()
                    $('#roadcube_verify_phone').hide()
                    $('#user_phone_verification').val('verified')
                }
                console.log(resp)
                this_el.html('Verify')
            },
            error: err => {
                console.log(err)
                this_el.html('Verify')
            }
        })
    })
    // make the pagination work
    $('#roadcube_next, #roadcube_prev').on('click', e => {
        let this_el = $(e.currentTarget)
        let this_el_html = this_el.html()
        let page = this_el.attr('data-page')
        if( !roadcube.user_mobile ) {
            Swal.fire({
                icon: "warning",
                text: "User is not logged in or user is not registered to claim a coupon."
            })
            return false
        }
        this_el.html(`<i class="fa fa-refresh fa-spin"></i> ${this_el_html}`)
        $.ajax({
            url: roadcube.ajax_url,
            dataType: "json",
            type: "POST",
            data: {
                action: "roadcube_coupon_claim",
                dataset: dataset
            },
            success: resp => {
                if( resp.status && resp.status == "error" ) {
                    Swal.fire({
                        icon: "error",
                        text: resp.message
                    })
                } else if ( resp.status && resp.status == "success" ) {
                    let all_gifts = ''
                    for( let i = 0; i < resp.data.coupons.length; i++ ) {
                        let gift_data = resp.data.coupons[i]
                        let a_gift_ = `
                            <div class="roadcube-gift">
                                <div class="coupon-image">
                                    <img src="${gift_data.image}" alt="coupon image">
                                </div>
                                <div class="coupon-details">
                                    <h2>${gift_data.title}</h2>
                                    <p>Points: ${gift_data.points}</p>
                                    <p>Product code: ${gift_data.product_code}</p>
                                    <p>${gift_data.description}</p>
                                    <button data-coupon-id="${gift_data.coupon_id}" class="roadcube-claim-coupon">Claim coupon</button>
                                </div>
                            </div>
                        `
                        all_gifts += a_gift_
                    }
                    $('.roadcube-gifts-holder').html(all_gifts)
                    // next page
                    if( resp.data.pagination.next_page ) {
                        $('#roadcube_next').show();
                        $('#roadcube_next').attr('data-page',resp.data.pagination.next_page)
                    } else {
                        $('#roadcube_next').show();
                    }
                    // previous page
                    if( resp.data.pagination.previous_page ) {
                        $('#roadcube_prev').show();
                        $('#roadcube_prev').attr('data-page',resp.data.pagination.previous_page)
                    } else {
                        $('#roadcube_prev').show();
                    }
                    
                }
                console.log(resp)
                this_el.html(this_el_html)
            },
            error: err => {
                console.log(err)
                this_el.html(this_el_html)
            }
        })

    })
    const roadcube_validator_msg = msg => {
        Swal.fire({
            icon: "warning",
            text: msg
        })
    }
    $('#roadcube-register-btn').on('click', e => {
        e.preventDefault()
        let this_el = $(e.currentTarget)
        let user_exists = this_el.attr('data-user-existing')
        let user_reg_id = $('#user_reg_id').val()
        let this_el_html = this_el.html()
        let username = $('#roadcube_username').val()
        let roadcube_email = $('#roadcube_email').val()
        let country_id = $('#roadcube_country_id').val()
        let mobile = $('#roadcube_mobile').val()
        let gender = $('#roadcube_gender').val()
        let pass = $('#roadcube_pass').val()
        let con_pass = $('#roadcube_con_pass').val()
        let dob = $('#roadcube_dob').val()
        let roadcube_tos = $('#roadcube_tos:checked').val()
        let verified = $('#user_phone_verification').val()
        
        if( roadcube_tos == undefined ) {
            roadcube_validator_msg("Acceptence of terms of service is required.")
            return false
        }
        if( !verified ) {
            roadcube_validator_msg("Phone number is not verified.")
            return false
        }
        if( username == '' && !user_exists ) {
            roadcube_validator_msg("Username is required.")
            return false
        }
        if( roadcube_email == '' && !user_exists ) {
            roadcube_validator_msg("Email is required.")
            return false
        }
        if( !country_id ){
            roadcube_validator_msg("Country is required.")
            return false
        }
        if( !mobile ) {
            roadcube_validator_msg("Mobile is required.")
            return false
        }
        if( !user_reg_id ) {
            roadcube_validator_msg("User mobile is not verified.")
            return false
        }
        if( !gender ) {
            roadcube_validator_msg("Gender is required.")
            return false
        }
        if( !pass ) {
            roadcube_validator_msg("Password is required.")
            return false
        }
        if( !con_pass ) {
            roadcube_validator_msg("Confirm password is required.")
            return false
        }
        if( pass != con_pass ) {
            roadcube_validator_msg("Both password has to be same.")
            return false
        }
        if( !dob ) {
            roadcube_validator_msg("Date of brith is required.")
            return false
        }
        let dataset = {
            username,
            roadcube_email,
            country_id,
            user_reg_id,
            mobile,
            gender,
            pass,
            con_pass,
            dob
        }
        if( user_exists ) {
            dataset = {
                country_id,
                user_reg_id,
                mobile,
                gender,
                pass,
                con_pass,
                dob,
                user_exists
            }
        }
        this_el.html(`<i class="fa fa-refresh fa-spin"></i> ${this_el_html}`)
        $.ajax({
            url: roadcube.ajax_url,
            dataType: "json",
            type: "POST",
            data: {
                action: "roadcube_register_new_user",
                dataset: dataset
            },
            success: resp => {
                if( resp.status && resp.status == "error" ) {
                    Swal.fire({
                        icon: "error",
                        text: resp.message
                    })
                } else if ( resp.status && resp.status == "success" ) {
                    console.log(roadcube.login_url)
                    // window.location.href = roadcube.login_url
                }
                console.log(resp)
                this_el.html(this_el_html)
            },
            error: err => {
                console.log(err)
                this_el.html(this_el_html)
            }
        })
        console.log(roadcube_tos)
        console.log('clicked')
    })
})