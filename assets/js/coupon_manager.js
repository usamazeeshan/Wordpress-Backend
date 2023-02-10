jQuery(document).ready(function($){
    let coupon_popup
    $('#roadcube-show-coupons').on('click',function(e){
        e.preventDefault()
        coupon_popup = Swal.fire({
            icon: "info",
            title: "Select a coupon to apply",
            html: '<div class="roadcube-show-gifts-container">' + 
                '<div class="roadcube-pagination">' +
                    '<button id="roadcube_prev_popup">Previous</button>' +
                    '<button id="roadcube_next_popup">Next</button>' +
                '</div>' +
                '<div class="roadcube-gifts-holder" style="height: 300px;overflow-y:scroll;">' +
                    
                '</div>' +
            '</div>',
            allowOutsideClick: false,
            showCancelButton: true,
            showConfirmButton: false,
            cancelButtonText: "Close"
        }).then( result => {
            console.log(result)
        })
        let resp = roadcube.coupon_data
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
    })
    $(document).on('click','.roadcube-coupons',function(){
        console.log($(this).attr('data-coupon'))
        // $.ajax({
            
        // })
        // Swal.close()
    })
    $(document).on('click','#roadcube_next_popup, #roadcube_prev_popup', e => {
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
        user_email = roadcube.user_mobile
        let dataset = {
            page, user_email
        }
        this_el.html(`<i class="fa fa-refresh fa-spin"></i> ${this_el_html}`)
        $.ajax({
            url: roadcube.ajax_url,
            dataType: "json",
            type: "POST",
            data: {
                action: "roadcube_get_user_available_coupons",
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
})