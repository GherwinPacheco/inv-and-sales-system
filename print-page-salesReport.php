<?php
session_start();
include("./includes/login-check.php");

if($_SESSION["level"] == 1){
    header("Location: ./dashboard.php");
    exit();
}

include ("./includes/database.php");
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


$salesGraphType = isset($_GET["salesGraphType"]) ? $_GET["salesGraphType"] : "daily";
$year = isset($_GET["year"]) ? $_GET["year"] : date("Y");
$month = isset($_GET["month"]) ? $_GET["month"] : date("m");
$day = ($year == date("Y") and $month == date("m")) ? date("d") : date("t", strtotime("$year-$month"));

$fromDate = isset($_GET["fromDate"]) ? $_GET["fromDate"] : "";
$toDate = isset($_GET["toDate"]) ? $_GET["toDate"] : "";

$salesRankTime = isset($_GET["salesRankTime"]) ? $_GET["salesRankTime"] : "all time";
$salesRankType = isset($_GET["salesRankType"]) ? $_GET["salesRankType"] : "Top";

$totalData = "";
$transactionCount = "";
$productCount = "";
$dateLabel = "";
$dateValue = "";

$salesRankData = "";
$salesRankData2 = "";
$salesRankLabel = "";
$noLimit = true;
include("./includes/reports-sales-getChartData.php");

$salesGraphHeader = "";
$salesDataCondition = "";
if (isset($_GET["fromDate"]) and isset($_GET["toDate"])) {
    $salesGraphHeader = "Daily Sales From " . date('M d, Y', strtotime($fromDate)) . " To " . date('M d, Y', strtotime($toDate));
    $salesDataCondition = "`date_added` BETWEEN '$fromDate' AND '$toDate'";
} else {
    if ($salesGraphType == "daily" or $salesGraphType == "weekly") {
        $salesGraphHeader = "Daily Sales During " . date('F', mktime(0, 0, 0, $month, 10)) . " " . $year;
        $salesDataCondition = "YEAR(`date_added`) = '$year' AND MONTH(`date_added`) = '$month'";
    } elseif ($salesGraphType == "monthly") {
        $salesGraphHeader = "Monthly Sales During " . $year;
        $salesDataCondition = "YEAR(`date_added`) = '$year'";
    } elseif ($salesGraphType == "yearly") {
        $salesGraphHeader = "Yearly Sales";
        $salesDataCondition = "1";
    }
}


//get the form values
$salesGraphTypeForm = isset($_GET["salesGraphType"]) ?
    '<input type="hidden" name="salesGraphType" value="' . $_GET["salesGraphType"] . '">' :
    "";
$yearForm = isset($_GET["year"]) ?
    '<input type="hidden" name="year" value="' . $_GET["year"] . '">' :
    "";
$monthForm = isset($_GET["month"]) ?
    '<input type="hidden" name="month" value="' . $_GET["month"] . '">' :
    "";


$fromDateForm = isset($_GET["fromDate"]) ?
    '<input type="hidden" name="fromDate" value="' . $_GET["fromDate"] . '">' :
    "";
$toDateForm = isset($_GET["toDate"]) ?
    '<input type="hidden" name="toDate" value="' . $_GET["toDate"] . '">' :
    "";


$salesRankTimeForm = isset($_GET["salesRankTime"]) ?
    '<input type="hidden" name="salesRankTime" value="' . $_GET["salesRankTime"] . '">' :
    "";
$salesRankTypeForm = isset($_GET["salesRankType"]) ?
    '<input type="hidden" name="salesRankType" value="' . $_GET["salesRankType"] . '">' :
    "";

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

    <title>Sales Reports</title>
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
        font-size: 13px;
    }
    .table th, .table td{
        vertical-align: middle;
        padding: 10px 5px;
    }
</style>

<body>
    <div id="buttonDiv" style="margin: 30px; auto; display: flex; justify-content: end">
        <a href="./reports-page-sales.php"><button type="button" class="btn btn-secondary m-3">Back</button></a>
        <button type="button" class="btn btn-primary m-3" onclick="hideButtons()">Print</button>
    </div>


    <div class="main-div" id="main-div" style="width: 215.9mm; margin: auto">
        <div class="header mb-5">
            <h2 class="text-center">Sales Report</h2>
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


        <div class="sales mt-5 mb-5 page">
            <p> 
                <b>Total Sales:&emsp;</b>₱ 
                <?= number_format($db->sql("
                    SELECT SUM(`total`) AS `total` FROM `transactions` WHERE $salesDataCondition
                ")->fetch_assoc()["total"], 2);?>
            </p>
            <p> 
                <b>Total Transactions:&emsp;</b>
                <?= number_format($db->sql("
                    SELECT COUNT(*) AS `transactionCount` FROM `transactions` WHERE $salesDataCondition
                ")->fetch_assoc()["transactionCount"]);
                ?> transactions
            </p>
            <p> 
                <b>Products Sold:&emsp;</b>
                <?= number_format($db->sql("
                    SELECT SUM(`qty`) AS `qty` FROM (
                        SELECT IF(`quantity_type` > 1, (`quantity` * `products`.`bundle`), `quantity`) AS `qty`, `transactions`.`date_added`
                        FROM `transaction_items` 
                        INNER JOIN `products` ON `transaction_items`.`product_id` = `products`.`product_id`
                        INNER JOIN `transactions` ON `transactions`.`transaction_id` = `transaction_items`.`transaction_id`
                        WHERE 1
                    ) AS `total_product_sold`
                    WHERE $salesDataCondition
                ")->fetch_assoc()["qty"]);
                ?> items
            </p>
            <hr><br>
            <!--Sales Graph Header-->
            <h5>
                <?= $salesGraphHeader ?>
            </h5>

            <!--Sales Graph
            <canvas class="salesChart" id="salesChart"></canvas>
            -->

            <table class="table text-center mt-5">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Total Sales</th>
                        <th>Total Transactions</th>
                        <th>Items Sold</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $totalArray = explode(", ", $totalData);
                    $transactionArray = explode(", ", $transactionCount);
                    $productArray = explode(", ", $productCount);
                    $labelArray = explode(", ", $dateLabel);
                    $dateArray = explode(", ", $dateValue);
                    for ($x = 0; $x < count($totalArray); $x++) {
                        echo '
                                <tr>
                                    <th>' . ($x + 1) . '</th>
                                    <td class="text-center">' . str_replace(str_split("[']"), "", $labelArray[$x]) . '</td>
                                    <td class="text-right">₱ ' . number_format(str_replace(str_split("[']"), "", $totalArray[$x]), 2) . '</td>
                                    <td class="text-right">' . number_format(str_replace(str_split("[']"), "", $transactionArray[$x])) . ' transactions</td>
                                    <td class="text-right">' . number_format(str_replace(str_split("[']"), "", $productArray[$x])) . ' items</td>
                                </tr>
                            ';
                    }

                    ?>
                </tbody>
            </table>
        </div>
        
        <div class="topSales mt-5 page">
            <!--Top Selling Product-->
            <h5>
                <?= "Product Sales of " . ucwords($salesRankTime)?>
            </h5>

            <!--<div class="p-5">
                <canvas id="topChart"></canvas>
            </div>-->
            <div class="row  mt-5">
                <table class="table text-center productRankTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Quantity Sold</th>
                            <th>Total Sales</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $salesRankTotal = explode(", ", $salesRankData);
                        $salesRankQty = explode(", ", $salesRankData2);
                        $product = explode(", ", $salesRankLabel);
                        for ($x = 0; $x < count($salesRankTotal) - 1; $x++) {
                            echo '
                                    <tr>
                                        <th>' . ($x + 1) . '</th>
                                        <td class="text-left">' . str_replace(str_split("[']"), "", $product[$x]) . '</td>
                                        <td class="text-right">' . number_format(str_replace(str_split("[']"), "", $salesRankQty[$x])) . '</td>
                                        <td class="text-right">₱ ' . number_format((float)str_replace(str_split("[']"), "", $salesRankTotal[$x]), 2) . '</td>
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

    /*const sCtx = document.getElementById('salesChart');

    new Chart(sCtx, {
        type: 'bar',
        data: {
            labels: <?= $dateLabel ?>,
            datasets: [{
                    label: 'Total of Sales',
                    data: <?= $totalData ?>,
                    borderWidth: 2,
                    backgroundColor: '#5cb85c'
                },
                {
                    label: 'Total Transactions',
                    data: <?= $transactionCount ?>,
                    borderWidth: 2,
                    backgroundColor: '#0275d8',
                    hidden: true
                },
                {
                    label: 'Items Sold',
                    data: <?= $productCount ?>,
                    borderWidth: 2,
                    backgroundColor: '#d9534f',
                    hidden: true
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
                            size: 15
                        }
                    },
                }
            }
        }
    });


    const tCtx = document.getElementById('topChart');

    new Chart(tCtx, {
        type: 'doughnut',
        data: {
            labels: <?= $salesRankLabel ?>,
            datasets: [{
                label: 'Total Sales',
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
                            size: 20
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

