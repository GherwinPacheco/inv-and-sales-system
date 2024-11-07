<?php
    session_start();
    include("./includes/login-check.php");
    
    include("./includes/database.php");
    $db = new Database();

    include ("./includes/inventory-functions.php");
    $inv = new Inventory($db);

    $inv->updateQuantity("all");
    $inv->updateStockStatus();
    $inv->updateExpirationStatus();

    

    $expiring = $db->sql("
        SELECT 
            `products`.`product_name`, `products`.`properties`, SUM(`batch`.`single_quantity`) AS `single_quantity`, 
            `products`.`unit_single`, `batch`.`expiration_status`, `batch`.`expiration_date`
        FROM `batch`
            INNER JOIN `products` ON `batch`.`product_id` = `products`.`product_id`
        WHERE 
            `batch`.`archive_status` = 0 AND `batch`.`return` = 0 AND 
            (`batch`.`expiration_status` = 2 OR `batch`.`expiration_status` = 3) AND 
            `batch`.`single_quantity` > 0
        GROUP BY `products`.`product_name`, `batch`.`expiration_status`
        ORDER BY `products`.`product_name` ASC, `batch`.`expiration_status` DESC;
    ");

    $expiringRows = "";
    if($expiring->num_rows > 0){
        while($row = $expiring->fetch_assoc()){
            //get the unit of measurements for singles and bundles
            $single_qty = $inv->getQuantityUnit($inv->getUnit($row["unit_single"]), $row["single_quantity"]);

            //Determine expiration status
            $expiration_status = $inv->setExpirationStatus($row["expiration_status"]);
            $expiration_badge = $inv->setExpirationStatusBadge($row["expiration_status"], $row["expiration_date"]);

            $expiringRows .= '
                <tr>
                    <td style="text-align: left">'.wordwrap($row["product_name"].' '.$row["properties"], 15,"<br>\n").'</td>
                    <td style="text-align: right">'.$single_qty.'</td>
                    <td style="text-align: center">'.$expiration_badge.'</td>
                </tr>
            ';
        }
    }

    $critical = $db->sql("
        SELECT
            `product_name`, `properties`, `single_quantity`, `unit_single`, `minimum_stock`, `stock_status`
        FROM `products`
        WHERE `archive_status` = 0 AND (`stock_status` = 2 OR `stock_status` = 3)
    ");
    $criticalRows = "";
    if($critical->num_rows > 0){
        while($row = $critical->fetch_assoc()){
            //get the unit of measurements for singles and bundles
            $single_qty = $inv->getQuantityUnit($inv->getUnit($row["unit_single"]), $row["single_quantity"]);
            
            //Determine stock status
            $stock_status = $inv->setStockStatus($row["stock_status"]);
            $stock_badge = $inv->setStockStatusBadge($row["stock_status"], $row["minimum_stock"], $row["single_quantity"]);

            $criticalRows .= '
                <tr>
                    <td style="text-align: left">'.wordwrap($row["product_name"].' '.$row["properties"], 15,"<br>\n").'</td>
                    <td style="text-align: right">'.$single_qty.'</td>
                    <td style="text-align: center">'.$stock_badge.'</td>
                </tr>
            ';
        }
    }

    $expiringTable = "";
    if($expiringRows !== ""){
        $expiringTable = '
            <div class="danger-items-div my-3">
                <h5 class="card-title">Expiring Items ('.number_format($expiring->num_rows).')</h5>
                <table class="table rounded shadow-lg bg-white">
                    <thead>
                        <th>PRODUCT NAME</th>
                        <th>STOCKS</th>
                        <th>STATUS</th>
                    </thead>
                    <tbody>
                        '.$expiringRows.'
                    </tbody>
                </table>
            </div>
        ';
    }

    $criticalTable = "";
    if($criticalRows !== ""){
        $criticalTable = '
            <div class="danger-items-div my-3">
                <h5 class="card-title">Critical Products ('.number_format($critical->num_rows).')</h5>
                <table class="table">
                    <thead>
                        <th>PRODUCT NAME</th>
                        <th>STOCKS</th>
                        <th>STATUS</th>
                    </thead>
                    <tbody>
                        '.$criticalRows.'
                    </tbody>
                </table>
            </div>
        ';
    }

    $tableList = "";
    
    if($expiringRows !== "" or $criticalRows !== ""){
        $tableList = $expiringTable.$criticalTable;
    }
    else{
        $tableList = '<h5 style="margin: auto; margin-top: 50%; text-align: center">No results</h5>';
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=`, initial-scale=1.0">


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

    <link rel="stylesheet" href="./css/dashboard.css">
    <link rel="icon" type="image/png" href="./assets/logo.png"/>
    
    <title>Dashboard</title>
</head>
<style>
    
    .dashboard-section {
        animation: transitionIn-Y-bottom 0.5s;
    }

    .admin-privileges{
        display: <?php echo $_SESSION["level"] == "2" ? "block" : "none"?>
    }
    
</style>


<body>
    <?php 
        //implement the sidebar
        $main = "Dashboard";
        include("./includes/sidebar.php");

        //add the alert messages
        include("./includes/alert-message.php");
    ?>
    <div class="main-div" id="main-div"> 
        <div class="container-fluid">

            <!--Row 1 (Heading)-->
            <?php include("./includes/header.php"); ?>

          
            
            <div class="dashboard-section row mb-3">
                <div class="col col-lg-8">

                    <div class="row">
                        <div class="col col-lg-3 py-3 px-2">
                            <div class="quick-info bordered rounded shadow-sm justify-content-center text-center p-3">
                                <img class="dataImg" src="./assets/total-sale.png" alt="">
                                <p style="margin: 0; padding: 0"> ₱
                                    <?= number_format($db->sql("
                                        SELECT SUM(`total`) AS `total` FROM `transactions`
                                    ")->fetch_assoc()["total"], 2);
                                    ?>
                                </p>
                                <h5>Total Sales</h5>
                            </div>
                        </div>

                        <div class="col col-lg-3 py-3 px-2">
                            <div class="quick-info bordered rounded shadow-sm justify-content-center text-center p-3">
                                <img class="dataImg" src="./assets/total-transac.png" alt="">
                                <p style="margin: 0; padding: 0">
                                    <?= number_format($db->sql("
                                        SELECT COUNT(*) AS `transaction_count` FROM `transactions`
                                    ")->fetch_assoc()["transaction_count"]);
                                    ?> transactions
                                </p>
                                <h5>Transactions</h5>
                            </div>
                        </div>

                        <div class="col col-lg-3 py-3 px-2">
                            <div class="quick-info bordered rounded shadow-sm justify-content-center text-center p-3">
                                <img class="dataImg" src="./assets/inv-items.png" alt="">
                                <p style="margin: 0; padding: 0">
                                    <?= number_format($db->sql("
                                            SELECT SUM(`batch`.`single_quantity`) AS `product_count` 
                                            FROM `batch`
                                            INNER JOIN `products` ON `batch`.`product_id` = `products`.`product_id`
                                            WHERE `batch`.`archive_status` = 0 AND `products`.`archive_status` = 0 AND`batch`.`return` = 0
                                        ")->fetch_assoc()["product_count"]);
                                    ?> items
                                </p>
                                <h5>Inventory Items</h5>
                            </div>
                        </div>

                        <div class="col col-lg-3 py-3 px-2">
                            <div class="quick-info bordered rounded shadow-sm justify-content-center text-center p-3">
                                <img class="dataImg" src="./assets/inv-value.png" alt="">
                                <p style="margin: 0; padding: 0"> ₱
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
                                <h5>Inventory Value</h5>
                            </div>
                        </div>
                    </div>

                    <h5 class="my-3">Sales for the Last 7 Days</h5>
                    <div class="row chart-row">
                        <canvas id="myChart"></canvas>
                    </div>

                </div>

                <div class="col col-lg-4">
                    <div class="danger-item-list card my-3">
                    <h5 class="card-header bg-blue">Products That Needs Your Attention</h5>
                    <div class="card-body pb-3">
                        <?=$tableList?>
                        <br><br>
                    </div>
                    </div>
                </div>
                

            </div>

        </div>
    </div>

    <!--Charts Data-->
<?php
$totalArray = "";
$dateArray = "";



for($x = 7; $x >= 1; $x--){
    $date = date("Y-m-d", strtotime("-".($x-1)." days"));
    $result = $db->sql("
        SELECT SUM(`total`) AS total, `date_added`
        FROM `transactions`
        WHERE `date_added` = '$date'"
    );

    $date = date("M d Y", strtotime($date));
    $total = 0;
    while($row = $result->fetch_assoc()){
        $total += $row["total"];
    }

    $totalArray .= $total;
    $dateArray .= "'".$date."'";

    if($x >= 1){
        $totalArray .= ", ";
        $dateArray .= ", ";
    }
}

$totalArray = "[$totalArray]";
$dateArray = "[$dateArray]";

?>

<script src="./js/chart.js"></script>
<script>
    const ctx = document.getElementById('myChart');

    new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= $dateArray ?>,
                datasets: [{
                        label: 'Total of Sales',
                        data: <?= $totalArray ?>,
                        borderWidth: 2,
                        backgroundColor: '#5cb85c'
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        align: 'center',
                        labels: {
                            font: {
                                size: 12
                            }
                        },
                    }
                }
            }
        });
</script>
</body>
</html>
