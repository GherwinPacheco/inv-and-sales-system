<?php
session_start();
include("./includes/login-check.php");

if($_SESSION["level"] == 1){
    header("Location: ./dashboard.php");
    exit();
}

include("./includes/database.php");
$db = new Database();

include ("./includes/inventory-functions.php");
$inv = new Inventory($db);

$inv->updateQuantity("all");
$inv->updateStockStatus();
$inv->updateExpirationStatus();

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

include("./includes/reports-inv-getChartData.php");

//get the form values
$salesRankTimeForm = isset($_GET["salesRankTime"]) ?
    '<input type="hidden" name="salesRankTime" value="'. $_GET["salesRankTime"].'">' :
    "";
$salesRankTypeForm = isset($_GET["salesRankType"]) ?
    '<input type="hidden" name="salesRankType" value="'. $_GET["salesRankType"].'">' :
    "";
?>

<!DOCTYPE html>
<html lang="en">

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

    <link rel="stylesheet" href="./css/reports.css">
    <link rel="icon" type="image/png" href="./assets/logo.png" />

    <title>Inventory Reports</title>
</head>

<style>
    .report-section {
        animation: transitionIn-Y-bottom 0.5s;
    }

    .admin-privileges{
        display: <?php echo $_SESSION["level"] == "2" ? "block" : "none"?>
    }
</style>

<body>
    
    <?php
    //implement the sidebar
    $main = "Reports";
    $sub = "inventory-report";
    include("./includes/sidebar.php");

    //add the alert messages
    include("./includes/alert-message.php");

    ?>

    <div class="main-div" id="main-div">
        <div class="container-fluid">

            <!--Row 1 (Heading)-->
            <?php include("./includes/header.php"); ?>


            <div class="row d-flex flex-row-reverse px-3 mb-2">
                <form action="print-page-inventoryReports.php" method="get">
                    <?=$salesRankTimeForm.$salesRankTypeForm?>
                    <button type="submit" class="btn btn-blue bordered"><i class="fa-solid fa-print"></i>&emsp;Print</button>
                </form>
                
            </div>

            <!--Row 2 (Inventory Report Part)-->
            <div class="row report-section">

                <!--Left Side-->
                <div class="col col-lg-8 px-4">


                    <!--Sales Info-->
                    <div class="row justify-content-between mb-3">
                        <div class="col col-md-3 px-3 pb-3">
                            <div class="bordered rounded shadow-sm justify-content-center text-center p-3">
                                <img class="dataImg" src="./assets/inv-value.png" alt="">
                                <p style="margin: 0; padding: 0">₱ 
                                    <?= number_format($db->sql("
                                        SELECT SUM(`value`) AS `stock_value` FROM (
                                            SELECT  
                                                (SELECT (SUM(`batch`.`single_quantity`) * `batch`.`price`)  FROM `batch` WHERE `batch`.`product_id` = `products`.`product_id` AND `batch`.`archive_status` = 0 AND `batch`.`return` = 0) as `value` 
                                            FROM `products` 
                                            WHERE `archive_status` = 0
                                            ORDER BY `product_name` ASC
                                        ) AS `tbl`
                                        ")->fetch_assoc()["stock_value"], 2);
                                    ?>
                                </p>
                                <h6>Inventory Value</h6>
                            </div>

                        </div>
                        <div class="col col-md-3 px-3 pb-3">
                            <div class="bordered rounded shadow-sm justify-content-center text-center p-3">
                                <img class="dataImg" src="./assets/good-product.png" alt="">
                                <p style="margin: 0; padding: 0">
                                    <?= $db->sql("
                                        SELECT SUM(`batch`.`single_quantity`) AS `quantity` 
                                        FROM `batch`
                                            INNER JOIN `products` ON `batch`.`product_id` = `products`.`product_id`
                                        WHERE `batch`.`archive_status` = 0 AND `products`.`archive_status` = 0 AND`batch`.`return` = 0 AND (`batch`.`expiration_status` = 0 OR `batch`.`expiration_status` = 1)
                                        ")->fetch_assoc()["quantity"];
                                    ?> items
                                </p>
                                <h6>Good Products</h6>
                            </div>

                        </div>
                        <div class="col col-md-3 px-3 pb-3">
                            <div class="bordered rounded shadow-sm justify-content-center text-center p-3">
                                <img class="dataImg" src="./assets/expiring-product.png" alt="">
                                <p style="margin: 0; padding: 0">
                                    <?= $db->sql("
                                        SELECT SUM(`batch`.`single_quantity`) AS `quantity` 
                                        FROM `batch`
                                            INNER JOIN `products` ON `batch`.`product_id` = `products`.`product_id`
                                        WHERE `batch`.`archive_status` = 0 AND `products`.`archive_status` = 0 AND`batch`.`return` = 0 AND (`batch`.`expiration_status` = 2 OR `batch`.`expiration_status` = 3)
                                        ")->fetch_assoc()["quantity"];
                                    ?> items
                                </p>
                                <h6>Expiring Products</h6>
                            </div>

                        </div>
                        <div class="col col-md-3 px-3 pb-3">
                            <div class="bordered rounded shadow-sm justify-content-center text-center p-3">
                                <img class="dataImg" src="./assets/critical-stock.png" alt="">
                                <p style="margin: 0; padding: 0">
                                    <?= $db->sql("
                                        SELECT COUNT(*) AS `productCount` FROM `products` WHERE `archive_status` = 0 AND (`stock_status` = 2 OR `stock_status` = 3)
                                        ")->fetch_assoc()["productCount"];
                                    ?> products
                                </p>
                                <h6>Critical Stocks</h6>
                            </div>

                        </div>
                    </div>


                    <!--Inventory Reports-->
                    <div class="row mx-1 mb-4 mt-3">
                        <h5>Products</h5>
                    </div>
                    <div class="row mx-1 mb-5 inv-table-row">
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
                                                    <td style="text-align: center; vertical-align: middle">'.$stock_badge.'</td>
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

                    <hr>

                    <!--Batches-->
                    <div class="row mx-1 mb-4 mt-5">
                        <h5>Product Batches</h5>
                    </div>
                    <div class="row mx-1 mb-5 inv-table-row">
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
                                                    <td style="text-align: center; vertical-align: middle">'.$expiration_badge.'</td>
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
                    

                </div>


                <!--Fast Moving Product-->
                <div class="col col-lg-4">
                    <div class="inv-pieGraph bordered rounded shadow-sm py-4 px-5">
                        <div class="row mb-3">
                            <h5>
                                <?=$graphHeader?>
                            </h5>
                        </div>
                        <form action="./reports-page-inventory.php" method="get">
                            <div class="row mb-3">
                                <div class="col col-sm-6">
                                    <select class="form-control bg-blue" name="salesRankTime" id="salesRankTimeMenu" onchange="this.form.submit()">
                                        <option value="last 7 days" <?= $salesRankTime == "last 7 days" ? "selected" : ""?>>Last 7 Days</option>
                                        <option value="this month" <?= $salesRankTime == "this month" ? "selected" : ""?>>This Month</option>
                                        <option value="last month" <?= $salesRankTime == "last month" ? "selected" : ""?>>Last Month</option>
                                        <option value="this year" <?= $salesRankTime == "this year" ? "selected" : ""?>>This Year</option>
                                        <option value="last year" <?= $salesRankTime == "last year" ? "selected" : ""?>>Last Year</option>
                                    </select>
                                </div>
                                <div class="col col-sm-6">
                                    <select class="form-control bg-blue" name="salesRankType" id="salesRankTypeMenu" onchange="this.form.submit()">
                                        <option value="fast" <?= $salesRankType == "fast" ? "selected" : ""?>>Fast Moving</option>
                                        <option value="slow" <?= $salesRankType == "slow" ? "selected" : ""?>>Slow Moving</option>
                                    </select>
                                </div>
                            </div>
                        </form>

                        <div class="row mb-5">
                            <canvas id="topChart"></canvas>
                        </div>

                        <div class="row mt-5">
                            <table class="table text-center productRankTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>PRODUCT</th>
                                        <th>AVG SOLD<br>PER DAY</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $salesRankTotal = explode(", ", $salesRankData);
                                    $product = explode(", ", $salesRankLabel);
                                    for ($x = 0; $x < count($salesRankTotal) - 1; $x++) {
                                        echo '
                                                <tr>
                                                    <th style="text-align: left">' . ($x + 1) . '</th>
                                                    <td style="text-align: left">' . wordwrap(str_replace(str_split("[']"), "", $product[$x]), 15, "<br>\n") . '</td>
                                                    <td style="text-align: right">' . (str_replace(str_split("[']"), "", $salesRankTotal[$x])) . ' items</td>
                                                </tr>
                                            ';
                                    }

                                    ?>
                                </tbody>
                            </table>
                        </div>

                </div>

            </div>


        </div>
    </div>

    <script src="./js/preventKeydown.js"></script>
    <script src="./js/chart.js"></script>
    <script>

        const tCtx = document.getElementById('topChart');

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
                                size: 15
                            }
                        },
                    }
                },
                layout: {
                    padding: 20
                }
            }
        });
    </script>
</body>

</html>