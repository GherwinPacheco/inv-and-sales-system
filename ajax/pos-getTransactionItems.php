<?php
    include("../includes/database.php");
    $db = new Database(); 
    
    $transactionId = $_POST["transaction_id"];
    
    $result = $db->sql("
        SELECT 
            `transaction_items`.`t_item_id`, 
            `transaction_items`.`transaction_id`, 
            `transaction_items`.`product_id`,
            `products`.`barcode_id`, 
            `products`.`product_name`, 
            `products`.`properties`, 
            `transaction_items`.`quantity`, 
            `transaction_items`.`quantity_type`, 
            `transaction_items`.`unit_id`, 
            `unit_measurement`.`unit_name`, 
            `transaction_items`.`product_promo`, 
            `transaction_items`.`price`, 
            `transaction_items`.`subtotal` 
        FROM `transaction_items`
        INNER JOIN `unit_measurement` ON `transaction_items`.`unit_id` = `unit_measurement`.`unit_id`
        INNER JOIN `products` ON `transaction_items`.`product_id` = `products`.`product_id`
        WHERE `transaction_items`.`transaction_id` = '$transactionId'
    ");

    $table = '';
    $table_body = '';

    if ($result->num_rows > 0) {
        // output data of each row
        $rowcount = 1;
        while($row = $result->fetch_assoc()) {

            $id = $row["t_item_id"];
            //$promoBadge = $row["product_promo"] > 0 ? '<span class="badge badge-pill badge-danger">+ '.$row["product_promo"].' free</span>' : '';
            $table_body .= '
                <tr>
                    <th class="border-right" style="text-align: center; padding: 16px">'.$rowcount.'</th>
                    <td style="text-align: left; padding: 16px">'.($row["barcode_id"] !== "" ? $row["barcode_id"] : "N/A").'</td>
                    <td style="text-align: left; padding: 16px">'.$row["product_name"].' '.$row["properties"].'</td>
                    <td style="text-align: right; padding: 16px">'.$row["quantity"].' '.$row["unit_name"].'</td>
                    <td style="text-align: right; padding: 16px">₱ '.number_format($row["price"],2).'</td>
                    <td style="text-align: right; padding: 16px">₱ '.number_format($row["subtotal"],2).'</td>
                    <td style="text-align: left; padding: 16px">'.wordwrap($row["product_promo"],25,"<br>\n").'</td>
                </tr>
            ';

            $rowcount++;
        }
    } 
    else {
        $table_body = "";
    }

    if($table_body !== ""){
        $table = ' 
            <!--AJAX result here-->
            <div class="d-flex flex-row-reverse">

                <table class="table hidden-table" id="hidden-table-'.$transactionId.'">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>BARCODE ID</th>
                            <th>PRODUCT NAME</th>
                            <th>QUANTITY</th>
                            <th>PRICE</th>
                            <th>SUBTOTAL</th>
                            <th>PROMO</th>
                        </tr>
                    </thead>
                    <tbody>
                        '.$table_body.'
                    </tbody>
                </table>
                <h1 class="indent-logo"><i class="bi bi-arrow-return-right"></i></h1>
            </div>
        ';
    }
    
    echo $table;
?>