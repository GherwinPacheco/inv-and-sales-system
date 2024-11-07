<?php
session_start();
include("./includes/login-check.php");

if($_SESSION["level"] == 1){
    header("Location: ./dashboard.php");
    exit();
}

include("./includes/database.php");
$db = new Database();


date_default_timezone_set('Asia/Manila');
$user = $_SESSION["user"];
$date = date("M d, Y");
$time = date("h:i a");

$storeName = $db->sql("SELECT `store_name` FROM `store_details` WHERE `id` = 1")->fetch_assoc()["store_name"];
$storeAddress = $db->sql("SELECT `store_address` FROM `store_details` WHERE `id` = 1")->fetch_assoc()["store_address"];
$storeTin = $db->sql("SELECT `tin_number` FROM `store_details` WHERE `id` = 1")->fetch_assoc()["tin_number"];
$contact = $db->sql("SELECT `contact` FROM `store_details` WHERE `id` = 1")->fetch_assoc()["contact"];
$username = $db->sql("SELECT `user_name` FROM `accounts` WHERE `user_id` = '$user'")->fetch_assoc()["user_name"];
$firstName = $db->sql("SELECT `first_name` FROM `accounts` WHERE `user_id` = '$user'")->fetch_assoc()["first_name"];
$lastName = $db->sql("SELECT `last_name` FROM `accounts` WHERE `user_id` = '$user'")->fetch_assoc()["last_name"];

include ("./includes/inventory-functions.php");
$inv = new Inventory($db);
/*
    SELECT `products`.`product_name`, 
        IFNULL(SUM(IF(`transaction_items`.`quantity_type` = 1, `transaction_items`.`quantity`, (`transaction_items`.`quantity` * `products`.`bundle`))), 0) as `product_sold`,
        IFNULL((SUM(IF(`transaction_items`.`quantity_type` = 1, `transaction_items`.`quantity`, (`transaction_items`.`quantity` * `products`.`bundle`)) ) / DATEDIFF(CURDATE(), CURDATE() - INTERVAL 7 DAY)), 0) AS `average_per_day`
    FROM `transaction_items`
        LEFT JOIN `products` ON `transaction_items`.`product_id` = `products`.`product_id`
        LEFT JOIN `transactions` ON `transaction_items`.`transaction_id` = `transactions`.`transaction_id`
    WHERE `transactions`.`date_added` BETWEEN CURDATE() - INTERVAL 7 DAY AND CURDATE()
    GROUP BY `products`.`product_name` ASC
    ORDER BY `average_per_day` DESC;
*/

$salesRankTime = isset($_GET["salesRankTime"]) ? $_GET["salesRankTime"] : "last 7 days";
$salesRankType = isset($_GET["salesRankType"]) ? $_GET["salesRankType"] : "fast";

$salesRankData = "";
$salesRankLabel = "";
$noLimit = true;
include("./includes/reports-inv-getChartData.php");

?>



<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!--Bootstrap-->
    <link rel="stylesheet" href="./bootstrap/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <script src="./bootstrap/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    
    <!--Jquery-->
    <script src="./js/jquery_3.6.4_jquery.min.js"></script>

    <script src="./bootstrap//popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="./bootstrap/bootstrap.min.js" integrity="sha384-+sLIOodYLS7CIrQpBjl+C7nPvqq+FbNUBDunl/OZv93DB7Ln/533i8e/mZXLi/P+" crossorigin="anonymous"></script>

    <script>
        $(function () {
            $('[data-toggle="tooltip"]').tooltip();
        })
    </script>

    <!--Fontawesome-->
    <link rel="stylesheet" href="./fontawesome/css/all.min.css">

    <link rel="stylesheet" href="./css/styles.css">
    <link rel="stylesheet" href="./css/animations.css">

    <link rel="icon" type="image/png" href="./assets/logo.png" />

    <title>Inventory Reports</title>
</head>

<style>
    p{
        padding: 0;
        margin: 0;
    }
        
    @page {
        size: A4;
        margin: 10.16mm;
    }
    @media print {
        html, body {
            width: 210mm;
            height: 297mm;        
        }
        .page {
            margin: 0;
            border: initial;
            border-radius: initial;
            width: initial;
            min-height: initial;
            box-shadow: initial;
            background: initial;
            page-break-after: always;
        }
    }
    table{
        font-size: 14px;
    }
    .table th, .table td{
        vertical-align: middle;
        padding: 10px 5px;
    }
</style>

<body>
    <div id="buttonDiv" style="margin: 30px; auto; display: flex; justify-content: end">
        <a href="./reports-page-inventory.php"><button type="button" class="btn btn-secondary m-3">Back</button></a>
        <button type="button" class="btn btn-primary m-3" onclick="hideButtons()">Print</button>
    </div>


    <div class="main-div" id="main-div" style="width: 215.9mm; margin: auto">
        <div class="header mb-5">
            <h2 class="text-center">Inventory Reports</h2>
            <br><br>
            <div class="row">
                <div class="col col-6">
                    <h5><?=$storeName?></h5>
                    <p><?=$storeAddress?></p>
                    <p><?=$contact?></p>
                    
                </div>
                <div class="col col-3"></div>
                <div class="col col-3">
                    <h6>Date:</h6>
                    <p>&emsp;<?=$date?></p>
                    <p>&emsp;<?=$time?></p>
                </div>
            </div>
            
        </div>


        <div class="product mt-5 mb-5 page">
            <p> 
                <b>Total Inventory Value:&emsp;</b>₱ 
                <?= number_format($db->sql("
                    SELECT SUM(`value`) AS `stock_value` FROM (
                        SELECT (
                            SELECT (SUM(`batch`.`single_quantity`) * `batch`.`price`)  
                            FROM `batch` 
                            WHERE `batch`.`product_id` = `products`.`product_id` AND `batch`.`archive_status` = 0 AND `batch`.`return` = 0
                        ) as `value` 
                        FROM `products` 
                        WHERE `archive_status` = 0
                        ORDER BY `product_name` ASC
                    ) AS `tbl`
                    ")->fetch_assoc()["stock_value"], 2);
                ?>
            </p>
            <p> 
                <b>Good Products:&emsp;</b>
                <?= number_format($db->sql("
                    SELECT SUM(`batch`.`single_quantity`) AS `quantity` 
                    FROM `batch`
                        INNER JOIN `products` ON `batch`.`product_id` = `products`.`product_id`
                    WHERE `batch`.`archive_status` = 0 AND `products`.`archive_status` = 0 AND`batch`.`return` = 0 AND (`batch`.`expiration_status` = 0 OR `batch`.`expiration_status` = 1)
                    ")->fetch_assoc()["quantity"]);
                ?> items
            </p>
            <p> 
                <b>Expiring Products:&emsp;</b>
                <?= number_format($db->sql("
                    SELECT SUM(`batch`.`single_quantity`) AS `quantity` 
                    FROM `batch`
                        INNER JOIN `products` ON `batch`.`product_id` = `products`.`product_id`
                    WHERE `batch`.`archive_status` = 0 AND `products`.`archive_status` = 0 AND`batch`.`return` = 0 AND (`batch`.`expiration_status` = 2 OR `batch`.`expiration_status` = 3)
                    ")->fetch_assoc()["quantity"]);
                ?> items
            </p>
            <p> 
                <b>Low/Out of Stock Products:&emsp;</b>
                <?= number_format($db->sql("
                    SELECT COUNT(*) AS `productCount` FROM `products` WHERE `archive_status` = 0 AND (`stock_status` = 2 OR `stock_status` = 3)
                    ")->fetch_assoc()["productCount"]);
                ?> products
            </p>
            <hr><br>
            <!--Sales Graph Header-->
            <h5>Products</h5>

            <table class="table">
                <thead>
                    <th>#</th>
                    <th>NAME</th>
                    <th>PRICE</th>
                    <th>STOCKS</th>
                    <th>STOCKS VALUE</th>
                    <th>STOCK STATUS</th>
                </thead>
                <tbody>
                    <?php
                        $allPrd = $db->sql("
                            SELECT *, 
                                (SELECT (SUM(`batch`.`single_quantity`) * `batch`.`price`)  FROM `batch` WHERE `batch`.`product_id` = `products`.`product_id` AND `batch`.`archive_status` = 0 AND `batch`.`return` = 0) as `stock_value` 
                            FROM `products` 
                            WHERE `archive_status` = 0
                            ORDER BY `stock_status` DESC, `product_name` ASC
                        ");
                        if($allPrd->num_rows > 0){
                            $rowCount = 1;
                            while($row = $allPrd->fetch_assoc()){
                                //get the unit of measurements for singles
                                $single_qty = $inv->getQuantityUnit($inv->getUnit($row["unit_single"]), $row["single_quantity"]);

                                //Determine stock status
                                $stock_status = $inv->setStockStatus($row["stock_status"]);
                                $stock_badge = $inv->setStockStatusBadge($row["stock_status"], $row["minimum_stock"], $row["single_quantity"]);

                                echo '
                                    <tr>
                                        <th style="text-align: right; vertical-align: middle">'.$rowCount.'</th>
                                        <td style="text-align: left; vertical-align: middle">'.wordwrap($row["product_name"],30,"<br>\n").'</td>
                                        <td style="text-align: right; vertical-align: middle">₱ '.number_format($row["price"], 2).'</td>
                                        <td style="text-align: right; vertical-align: middle">'.$single_qty.'</td>
                                        <td style="text-align: right; vertical-align: middle">₱ '.number_format($row["stock_value"], 2).'</td>
                                        <td style="text-align: center; vertical-align: middle">'.$stock_status.'</td>
                                    </tr>
                                ';
                                $rowCount++;
                            }
                        }
                        else{
                            echo '
                                <tr colspan="5">
                                    <td>No Products to Show</td>
                                </tr>
                            ';
                        }
                        
                    ?>
                </tbody>
            </table>
        </div>
        
        <div class="batches mt-5 page">
            <!--Top Selling Product-->
            <h5>Product Batches</h5>
            <table class="table">
                <thead>
                    <th>#</th>
                    <th>BATCH ID</th>
                    <th>NAME</th>
                    <th>PRICE</th>
                    <th>STOCKS</th>
                    <th>STOCKS VALUE</th>
                    <th>EXPIRY STATUS</th>
                </thead>
                <tbody>
                    <?php
                        $goodPrd = $db->sql("
                            SELECT `batch`.*, `products`.`product_name`, `products`.`unit_single`, (`batch`.`single_quantity` * `batch`.`price`) AS `stock_value`
                            FROM `batch`
                                INNER JOIN `products` ON `batch`.`product_id` = `products`.`product_id`
                            WHERE `batch`.`archive_status` = 0 AND `batch`.`return` = 0 AND `batch`.`single_quantity` > 0
                            ORDER BY `expiration_status` DESC, `products`.`product_name` ASC
                        ");
                        if($goodPrd->num_rows > 0){
                            $rowCount = 1;
                            while($row = $goodPrd->fetch_assoc()){
                                //get the unit of measurements for singles
                                $single_qty = $inv->getQuantityUnit($inv->getUnit($row["unit_single"]), $row["single_quantity"]);

                                //Determine expiration status
                                $expiration_status = $inv->setExpirationStatus($row["expiration_status"]);
                                $expiration_badge = $inv->setExpirationStatusBadge($row["expiration_status"], $row["expiration_date"]);

                                echo '
                                    <tr>
                                        <th style="text-align: right; vertical-align: middle">'.$rowCount.'</th>
                                        <td style="text-align: left; vertical-align: middle">'.$row["batch_id"].'</td>
                                        <td style="text-align: left; vertical-align: middle">'.wordwrap($row["product_name"],25,"<br>\n").'</td>
                                        <td style="text-align: right; vertical-align: middle">₱ '.number_format($row["price"], 2).'</td>
                                        <td style="text-align: right; vertical-align: middle">'.$single_qty.'</td>
                                        <td style="text-align: right; vertical-align: middle">₱ '.number_format($row["stock_value"], 2).'</td>
                                        <td style="text-align: center; vertical-align: middle">'.$expiration_status.'</td>
                                    </tr>
                                ';
                                $rowCount++;
                            }
                        }
                        else{
                            echo '
                                <tr colspan="7">
                                    <td>No Products to Show</td>
                                </tr>
                            ';
                        }
                        
                    ?>
                </tbody>
            </table>
        </div>
        
        <div class="topSales mt-5 page">
            <h5>
                <?=$graphHeader?>
            </h5>
            <!--<div class="row mb-5">
                <canvas class="p-5" id="topChart"></canvas>
            </div>-->

            <div class="row mt-5">
                <table class="table text-center productRankTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>PRODUCT NAME</th>
                            <th>AVG SOLD PER DAY</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $salesRankTotal = explode(", ", $salesRankData);
                        $product = explode(", ", $salesRankLabel);
                        for ($x = 0; $x < count($salesRankTotal) - 1; $x++) {
                            echo '
                                    <tr>
                                        <th>' . ($x + 1) . '</th>
                                        <td class="text-left">' . str_replace(str_split("[']"), "", $product[$x]) . '</td>
                                        <td class="text-right">' . number_format((str_replace(str_split("[']"), "", $salesRankTotal[$x]))) . ' items</td>
                                    </tr>
                                ';
                        }

                        ?>
                    </tbody>
                </table>
            </div>
            <div class="row mt-5">
                <div class="text-left">
                    <span class="pb-1"><b><?=strtoupper($firstName." ".$lastName)?></b></span>
                    <p>Prepared by</p>
                </div>
                
            </div>
        </div>


    </div>
</body>

<script src="./js/chart.js"></script>
<script>

    function hideButtons(){
        $("#buttonDiv").css("display", "none");
        window.print();
        $("#buttonDiv").css("display", "flex");
    }

    /*const tCtx = document.getElementById('topChart');

    new Chart(tCtx, {
        type: 'doughnut',
        data: {
            labels: <?= $salesRankLabel ?>,
            datasets: [{
                label: 'Average product sold per day',
                data: <?= $salesRankData ?>,
                borderWidth: 1,
                backgroundColor: ['#0275d8', '#5cb85c', '#5bc0de', '#f0ad4e', '#d9534f'],
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    align: 'start',
                    labels: {
                        font: {
                            size: 25
                        }
                    },
                }
            },
            layout: {
                padding: 20
            }
        }
    });*/
</script>

