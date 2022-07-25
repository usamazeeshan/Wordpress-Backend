<?php $data = roadcube_get_the_trans(); ?>
<?php if( $data['status'] == "success" ) { ?>
<div class="roadcube-trans-list-container">
    <table class="roadcube-trans-list-holder">
        <?php
        echo '
            <tr>
                <td>Logo</td>
                <td>Store name</td>
                <td>Items</td>
                <td>Transaction type</td>
                <td>Transaction date</td>
                <td>Total amount</td>
                <td>Total points</td>
            </tr>
        ';
        foreach($data['data']['transactions'] as $transaction){
            $transaction_type_name = $transaction['transaction_type_name'];
            $created_at = $transaction['created_at'];
            $total_amount = $transaction['total_amount'];
            $currency = $transaction['currency'];
            $logo = $transaction['store']['logo'];
            $store_name = $transaction['store']['name'];
            $points = $transaction['total_points'];
            $items = [];
            foreach($transaction['transaction_items'] as $an_item){
                $items[] = $an_item['product_name'];
            }
            echo '<tr>';
            $items = implode(',',$items);
            $items = $items == "Virtual" ? "No product" : $items;
            $date = date('Y-m-d',strtotime($created_at));
            if( $total_amount == 0 ) {
                echo "<td><img src='{$logo}'/></td><td>{$store_name}</td><td>{$items}</td><td>{$transaction_type_name}</td><td>{$date}</td><td></td><td>{$points}</td>";
            } else {
                echo "<td><img src='{$logo}'/></td><td>{$store_name}</td><td>{$items}</td><td>{$transaction_type_name}</td><td>{$date}</td><td>{$total_amount}{$currency}</td><td>{$points}</td>";
            }
            echo '</tr>';
        }
        ?>
    </table>
    <div class="roadcube-trans-list-pagination">
        <?php
        $pages = $data['data']['pagination']['total_pages'];
        for ($i=1; $i <= $pages; $i++) {
            $active = $i == 1 ? 'roadcube-a-page-active' : '';
            echo "<div data-page-no='{$i}' class='roadcube-a-page {$active}'>{$i}</div>";
        }
        ?>
    </div>
</div>
<?php
} else {
    echo '<p style="font-size:18px;">'.$data['message'].'</p>';
}