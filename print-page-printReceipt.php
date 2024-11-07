<?php
    session_start();
    include("./includes/login-check.php");
    
    include ("./includes/database.php");
    $db = new Database();

    date_default_timezone_set('Asia/Manila');
    $user = $_SESSION["user"];
    $date = date("Y-m-d");
    $time = date("H:i:s");

    $id = $_GET["transactionId"];
    $location = $_GET["loc"];

    $storeName = $db->sql("SELECT `store_name` FROM `store_details` WHERE `id` = 1")->fetch_assoc()["store_name"];
    $storeAddress = $db->sql("SELECT `store_address` FROM `store_details` WHERE `id` = 1")->fetch_assoc()["store_address"];
    $storeTin = $db->sql("SELECT `tin_number` FROM `store_details` WHERE `id` = 1")->fetch_assoc()["tin_number"];
    $contact = $db->sql("SELECT `contact` FROM `store_details` WHERE `id` = 1")->fetch_assoc()["contact"];
?>

<head>
     <!--Jquery-->
     <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    
    <title>Receipt</title>
    <link rel="icon" type="image/png" href="./assets/logo.png"/>
</head>
<style>
    *{
        margin: 0;
        padding: 0;
        font-family: sans-serif;
    }
    hr {
        border: 2px solid black;
        margin: 5px 0px;
    }
    th, td{
        border-bottom: 1px solid black;
    }
    table{
        width: 100%
    }
    td{
        font-size: 12px
    }
    .transactionDetails{
        display: flex;
    }
    .transactionDetails h5{
        width: 100%
    }
    .button-back{
        background-color: #6C757D; 
        color: white; 
    }
    .button-print{
        background-color: #0275d8; 
        color: white; 
    }
    button{
        cursor: pointer;
        padding: 5px 10px; 
        border: none; 
        border-radius: 10px;
        margin: 10px
    }
</style>
<body onload="window.print()" onafterprint="window.location.href = './pos-page-<?=$location?>.php'">
    

<div class="receiptDiv" style="margin: auto; width: 57mm;">
    <h4 style="text-align: center"><?=date("F d, Y")?></h4>
    <br>
    <h2 style="text-align: center"><?=$storeName?></h2>
    <br>
    <h4 style="text-align: center"><?=$storeAddress?></h4>
    <h4 style="text-align: center">TIN: <?=$storeTin?></h4>
    <h4 style="text-align: center"><?=$contact?></h4>
    <hr>
    <h4 style="text-align: center">TR-<?=$id?></h4>
    <hr>
    <h5 style="text-align: center">RECEIPT</h5>
    <br>
    <table >
        <thead>
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $items = $db->sql("
                    SELECT 
                        `transaction_items`.`t_item_id`, `transaction_items`.`transaction_id`, `transaction_items`.`product_id`, 
                        `products`.`product_name`,`products`.`properties`, `transaction_items`.`quantity`, `transaction_items`.`quantity_type`, 
                        `transaction_items`.`unit_id`, `unit_measurement`.`unit_name`, `transaction_items`.`product_promo`, `transaction_items`.`price`, `transaction_items`.`subtotal` 
                    FROM `transaction_items`
                        INNER JOIN `products` ON `transaction_items`.`product_id` = `products`.`product_id`
                        INNER JOIN `unit_measurement` ON `transaction_items`.`unit_id` = `unit_measurement`.`unit_id`
                    WHERE `transaction_id` = '$id'
                ");
                while($row = $items->fetch_assoc()){
                    echo '
                        <tr>
                            <td style="text-align: left; width: 25%">'.wordwrap($row["product_name"].' '.$row["properties"], "20", "<br>\n").'</td>
                            <td style="text-align: right;  width: 25%"><b>'.$row["quantity"].'</td>
                            <td style="text-align: right;  width: 25%">₱ '.number_format($row["price"], 2).'</td>
                            <td style="text-align: right;  width: 25%">₱ '.number_format($row["subtotal"], 2).'</td>
                        </tr>
                    ';
                }
            ?>
        </tbody>
    </table>
    <hr>

    <?php
        $total = $db->sql("SELECT `total` FROM `transactions` WHERE `transaction_id` = '$id'")->fetch_assoc()["total"];
        $amountTendered = $db->sql("SELECT `amount_tendered` FROM `transactions` WHERE `transaction_id` = '$id'")->fetch_assoc()["amount_tendered"];
        $change = $db->sql("SELECT `change_amount` FROM `transactions` WHERE `transaction_id` = '$id'")->fetch_assoc()["change_amount"];
        $vatableSales = $db->sql("SELECT `vatable_sales` FROM `transactions` WHERE `transaction_id` = '$id'")->fetch_assoc()["vatable_sales"];
        $vatAmount = $db->sql("SELECT `vat_amount` FROM `transactions` WHERE `transaction_id` = '$id'")->fetch_assoc()["vat_amount"];
        $discount = $db->sql("SELECT `discount` FROM `transactions` WHERE `transaction_id` = '$id'")->fetch_assoc()["discount"];
        
    ?>
    <div class="transactionDetails">
        <h5 style="text-align: left">Total:</h5><h5 style="text-align: right">₱ <?=number_format($total, 2)?>&emsp;</h5>
    </div>
    <div class="transactionDetails">
        <h5 style="text-align: left">Amount Tendered:</h5><h5 style="text-align: right">₱ <?=number_format($amountTendered, 2)?>&emsp;</h5>
    </div>
    <div class="transactionDetails">
        <h5 style="text-align: left">Change:</h5><h5 style="text-align: right">₱ <?=number_format($change, 2)?>&emsp;</h5>
    </div>
    <div class="transactionDetails">
        <h5 style="text-align: left">Vatable Sales:</h5><h5 style="text-align: right">₱ <?=number_format($vatableSales, 2)?>&emsp;</h5>
    </div>
    <div class="transactionDetails">
        <h5 style="text-align: left">Vat Amount:</h5><h5 style="text-align: right">₱ <?=number_format($vatAmount, 2)?>&emsp;</h5>
    </div>
    <div class="transactionDetails">
        <h5 style="text-align: left">Discount:</h5><h5 style="text-align: right"><?=$discount?>%&emsp;</h5>
    </div>


    <h5 style="text-align: left">&emsp;&emsp;</h5>
    <hr>
    <h5>Disclaimer:<br>&emsp;This is not an official receipt. Please ask the cashier for official receipt if needed.</h5>
</div>
<!--<div id="buttonDiv" style="margin: 30px; auto; display: flex; justify-content: center">
    <a href="./pos-page-<?=$location?>.php"><button type="button" class="button-back">Back</button></a>
    <button type="button" class="button-print" onclick="hideButtons()">Print</button>
</div>-->


<script>
    /*function hideButtons(){
        $("#buttonDiv").css("display", "none");
        window.print();
        $("#buttonDiv").css("display", "flex");
    }*/

    

</script>

</body>