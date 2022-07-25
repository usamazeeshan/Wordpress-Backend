jQuery(document).ready(function($){
    $('.roadcube-a-page').on('click',function(){
        let this_html = $(this).html()
        $(this).html('<i class="fa fa-refresh fa-spin"></i>')
        let this_el = $(this)
        $.ajax({
            url: roadcube_trans.ajax_url,
            type: "POST",
            dataType: "json",
            data: {
                action: "roadcube_load_trans",
                dataset: this_html
            },
            success:function(resp){
                console.log(resp)
                if( resp.status && resp.status == "success" ) {
                    let html = ""
                    html += `
                        <tr>
                            <td>Logo</td>
                            <td>Store name</td>
                            <td>Items</td>
                            <td>Transaction type</td>
                            <td>Transaction date</td>
                            <td>Total amount</td>
                            <td>Total points</td>
                        </tr>
                    `
                    for (let i = 0; i < resp.data.transactions.length; i++) {
                        const transaction = resp.data.transactions[i];
                        let date = transaction.created_at.split('T')
                        date = date[0]
                        let trans_type = transaction.transaction_type_name
                        let logo = transaction.store.logo
                        let store_name = transaction.store.name
                        let points = transaction.total_points
                        let items = []
                        for (let index = 0; index < transaction.transaction_items.length; index++) {
                            const an_item = transaction.transaction_items[index];
                            items.push(an_item.product_name)
                        }
                        items = items.join(',')
                        items = items == "Virtual" ? "No product" : items
                        let total_amount = transaction.total_amount
                        let currency = transaction.currency
                        if( total_amount == 0 ) {
                            html += `<tr><td><img src='${logo}'/></td><td>${store_name}</td><td>${items}</td><td>${trans_type}</td><td>${date}</td><td></td><td>${points}</td></tr>`
                        } else {
                            html += `<tr><td><img src='${logo}'/></td><td>${store_name}</td><td>${items}</td><td>${trans_type}</td><td>${date}</td><td>${total_amount}${currency}</td><td>${points}</td></tr>`
                        }
                    }
                    $('.roadcube-trans-list-holder').html(html)
                    this_el.html(this_html)
                    $.each($('.roadcube-a-page'),function(k,v){
                        $(v).removeClass('roadcube-a-page-active')
                    })
                    this_el.addClass('roadcube-a-page-active')
                }
            },
            error: function(err){
                console.log(err)
                this_el.html(this_html)
            }
        })
    })
})