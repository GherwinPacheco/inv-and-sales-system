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

    <title>Sales Reports</title>
</head>

<style>
    .sales-section {
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
    $sub = "sales-report";
    include("./includes/sidebar.php");

    //add the alert messages
    include("./includes/alert-message.php");

    ?>

    <div class="main-div" id="main-div">
        <div class="container-fluid">

            <!--Row 1 (Heading)-->
            <?php include("./includes/header.php"); ?>


            <div class="row mb-2">
                <div class="col">
                    <h5>
                            <?= $salesGraphHeader ?>
                        </h5>
                </div>
                <div class="col d-flex flex-row-reverse">
                    <form action="print-page-salesReport.php" method="get">
                        <?=$salesGraphTypeForm . $yearForm . $monthForm . $salesRankTimeForm . $salesRankTypeForm . $fromDateForm . $toDateForm?>
                        <button type="submit" class="btn btn-blue bordered"><i class="fa-solid fa-print"></i>&emsp;Print</button>
                    </form>
                </div>
            </div>

            <!--Row 2 (Sales Report Part)-->
            <div class="row sales-section">

                <!--Left Side-->
                <div class="col col-lg-7 px-4">

                    
                    <!--Sales Info-->
                    <div class="row justify-content-between mb-2">
                        <div class="col col-md-4 px-3 pb-3">
                            <div class="bordered rounded shadow-sm justify-content-center text-center p-3">
                                <img class="dataImg" src="./assets/total-sale.png" alt="">
                                <p style="margin: 0; padding: 0"> ₱
                                    <?= number_format($db->sql("
                                        SELECT SUM(`total`) AS `total` FROM `transactions` WHERE $salesDataCondition
                                    ")->fetch_assoc()["total"], 2);
                                    ?>
                                </p>
                                <h5>Total Sales</h5>
                            </div>

                        </div>
                        <div class="col col-md-4 px-3 pb-3">
                            <div class="bordered rounded shadow-sm justify-content-center text-center p-3">
                                <img class="dataImg" src="./assets/total-transac.png" alt="">
                                <p style="margin: 0; padding: 0">
                                    <?= number_format($db->sql("
                                        SELECT COUNT(*) AS `transactionCount` FROM `transactions` WHERE $salesDataCondition
                                    ")->fetch_assoc()["transactionCount"]);
                                    ?> transactions
                                </p>
                                <h5>Transactions</h5>
                            </div>

                        </div>
                        <div class="col col-md-4 px-3 pb-3">
                            <div class="bordered rounded shadow-sm justify-content-center text-center p-3">
                                <img class="dataImg" src="./assets/item-sold.png" alt="">
                                <p style="margin: 0; padding: 0">
                                    <?= number_format($db->sql("
                                        SELECT SUM(`qty`) AS `qty` FROM (
                                            SELECT IF(`quantity_type` > 1, (`quantity` * `bundle`), `quantity`) AS `qty`, `transactions`.`date_added`
                                            FROM `transaction_items` 
                                            INNER JOIN `transactions` ON `transactions`.`transaction_id` = `transaction_items`.`transaction_id`
                                            WHERE 1
                                        ) AS `total_product_sold`
                                        WHERE $salesDataCondition
                                    ")->fetch_assoc()["qty"]);
                                    ?> items
                                </p>
                                <h5>Total Items Sold</h5>
                            </div>

                        </div>
                    </div>


                    <!--Sales Graph Filter-->
                    <form action="./reports-page-sales.php" method="get">
                        <div class="row mb-2">
                            <div class="col col-sm-9 d-flex">
                                <select class="form-control bg-blue m-1" name="salesGraphType" id="salesGraphTypeMenu">
                                    <option value="daily" <?= $salesGraphType == "daily" ? "selected" : "" ?>>Daily
                                    </option>
                                    <option value="weekly" <?= $salesGraphType == "weekly" ? "selected" : "" ?>>Weekly
                                    </option>
                                    <option value="monthly" <?= $salesGraphType == "monthly" ? "selected" : "" ?>>Monthly
                                    </option>
                                    <option value="yearly" <?= $salesGraphType == "yearly" ? "selected" : "" ?>>Yearly
                                    </option>
                                </select>
                                <select class="form-control bg-blue m-1" name="year" id="yearFilter" <?= $salesGraphType == "yearly" ? "disabled" : "" ?>>
                                    <?php
                                    $my = $db->sql("SELECT MIN(`date_added`) AS `min_year` FROM `transactions`")->fetch_assoc()["min_year"];
                                    $minYear = (int)date("Y", strtotime($my));
                                    for ($x = $minYear; $x <= (int)date("Y"); $x++) {
                                        echo '
                                            <option value="' . $x . '"' . ($year == $x ? "selected" : "") . '>' . $x . '</option>
                                        ';
                                    }
                                    ?>
                                </select>
                                <select class="form-control bg-blue m-1" name="month" id="monthFilter" <?= ($salesGraphType == "monthly" or $salesGraphType == "yearly") ? "disabled" : "" ?>>
                                    <?php
                                    for ($x = 1; $x <= 12; $x++) {
                                        echo '
                                            <option value="' . $x . '" ' . ($month == $x ? "selected" : "") . '>' . date('F', mktime(0, 0, 0, $x, 10)) . '</option>
                                        ';
                                    }
                                    ?>

                                </select>
                                <button type="submit" class="btn btn-primary m-1">Apply</button>
                            </div>
                            <div class="col col-sm-3 d-flex flex-row-reverse border-left">
                                <button type="button" class="btn btn-primary m-1" data-toggle="modal" data-target="#transactionFilterModal"><i class="fa-solid fa-calendar-week"></i>&emsp;Date Range</button>
                            </div>
                            <?= $salesRankTimeForm . $salesRankTypeForm ?>
                        </div>
                    </form>
                    <hr>
                    <!--Sales Graph-->
                    <div class="row p-3 salesChartDiv">
                        <canvas class="salesChart" id="salesChart"></canvas>
                    </div>
                    <div class="row salesTable mb-5">
                        <table class="table text-center">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>DATE</th>
                                    <th>TOTAL SALES</th>
                                    <th>TOTAL TRANSACTIONS</th>
                                    <th>ITEMS SOLD</th>
                                    <th></th>
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
                                                <td style="text-align: center">' . str_replace(str_split("[']"), "", $labelArray[$x]) . '</td>
                                                <td style="text-align: right">₱ ' . number_format(str_replace(str_split("[']"), "", $totalArray[$x]), 2) . '</td>
                                                <td style="text-align: right">' . str_replace(str_split("[']"), "", $transactionArray[$x]) . ' transactions</td>
                                                <td style="text-align: right">' . str_replace(str_split("[']"), "", $productArray[$x]) . ' items</td>
                                                <td>
                                                    <a href="./pos-page-transactions.php?loc=reports-page-sales&' . str_replace(str_split("[']"), "", $dateArray[$x]) . '" class="btn btn-blue">View</a>
                                                </td>
                                            </tr>
                                        ';
                                }

                                ?>
                                <tr>
                                    <th></th>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>


                <!--Top Selling Product-->
                <div class="col col-lg-5">
                    <div class="bordered rounded shadow-sm py-4 px-5">
                        <div class="row mb-3">
                            <h5>
                                <?= $salesRankType . " Selling Products of " . ucwords($salesRankTime) ?>
                            </h5>
                        </div>
                        <form action="./reports-page-sales.php" method="get">
                            <div class="row mb-3">
                                <div class="col col-sm-4"></div>
                                <div class="col col-sm-4">
                                    <select class="form-control bg-blue" name="salesRankTime" id="salesRankTimeMenu" onchange="this.form.submit()">
                                        <option value="all time" <?= $salesRankTime == "all time" ? "selected" : "" ?>>All
                                            Time</option>
                                        <option value="today" <?= $salesRankTime == "today" ? "selected" : "" ?>>Today
                                        </option>
                                        <option value="yesterday" <?= $salesRankTime == "yesterday" ? "selected" : "" ?>>Yesterday</option>
                                        <option value="last 7 days" <?= $salesRankTime == "last 7 days" ? "selected" : ""?>>Last 7 Days</option>
                                        <option value="this month" <?= $salesRankTime == "this month" ? "selected" : "" ?>>This Month</option>
                                        <option value="last month" <?= $salesRankTime == "last month" ? "selected" : "" ?>>Last Month</option>
                                        <option value="this year" <?= $salesRankTime == "this year" ? "selected" : "" ?>>This Year</option>
                                        <option value="last year" <?= $salesRankTime == "last year" ? "selected" : "" ?>>Last Year</option>
                                    </select>
                                </div>
                                <div class="col col-sm-4">
                                    <select class="form-control bg-blue" name="salesRankType" id="salesRankTypeMenu" onchange="this.form.submit()">
                                        <option value="Top" <?= $salesRankType == "Top" ? "selected" : "" ?>>Top Sales
                                        </option>
                                        <option value="Least" <?= $salesRankType == "Least" ? "selected" : "" ?>>Least
                                            Sales</option>
                                    </select>

                                </div>
                                <?= $salesGraphTypeForm . $yearForm . $monthForm ?>
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
                                        <th>QTY SOLD</th>
                                        <th>TOTAL SALES</th>
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
                                                    <td style="text-align: left">' . wordwrap(str_replace(str_split("[']"), "", $product[$x]), 15, "<br>\n") . '</td>
                                                    <td style="text-align: right">' . str_replace(str_split("[']"), "", $salesRankQty[$x]) . '</td>
                                                    <td style="text-align: right">₱ ' . number_format((float)str_replace(str_split("[']"), "", $salesRankTotal[$x]), 2) . '</td>
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
    </div>

    <!-- Filter Transactions Modal -->
    <form class="m-3" action="./reports-page-sales.php" method="get">
        <div class="modal fade" id="transactionFilterModal" tabindex="-1" role="dialog" aria-labelledby="addProductModal" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Date Range</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label for="fromDate">From: </label>
                                    <input type="date" class="form-control" name="fromDate" id="fromDate" required>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label for="fromDate">To: </label>
                                    <input type="date" class="form-control" name="toDate" id="toDate" required>
                                </div>
                            </div>
                        </div>
                        <?= $salesRankTimeForm . $salesRankTypeForm ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-gray" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Apply Filter</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script src="./js/preventKeydown.js"></script>
    <script src="./js/chart.js"></script>
    <script>
        $("#salesGraphTypeMenu").change(function() {
            var val = $(this).val();

            if (val == "monthly") {
                $("#monthFilter").attr("disabled", true);
                $("#yearFilter").attr("disabled", false);
            } else if (val == "yearly") {
                $("#monthFilter").attr("disabled", true);
                $("#yearFilter").attr("disabled", true);
            } else {
                $("#monthFilter").attr("disabled", false);
                $("#yearFilter").attr("disabled", false);
            }
        });

        const sCtx = document.getElementById('salesChart');

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