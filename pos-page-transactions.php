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

    //stores the condition based on the POST request
    $search = "";
    $itemCount = "";
    $page = 0;
    $condition = "1";
    $fromDate = "";
    $toDate = "";

    $loc = isset($_GET["loc"]) ? $_GET["loc"] : "pos-page-transactions";

    if($_SERVER['REQUEST_METHOD'] == 'GET'){
        //for searching of items
        if(isset($_GET["search"])){
            $search = $_GET["search"];
            $condition .= " AND `transaction_id` = '$search'";
        }
        
        if(isset($_GET["fromDate"]) and isset($_GET["toDate"])  and $_GET["toDate"] !== ""){
            $fromDate = $_GET["fromDate"];
            $toDate = $_GET["toDate"];
            $condition .= " AND `date_added` BETWEEN '$fromDate' AND '$toDate'";
        }
        elseif(isset($_GET["fromDate"])){
            $fromDate = $_GET["fromDate"];
            $condition .= " AND `date_added` = '$fromDate'";
        }

        //get the page of table
        if(isset($_GET["page"])){
            $page = $_GET["page"];
        }
        else{
            $page = 1;
        }
    }
    else{
        $page = 1;
    }
    
    //set the query for retrieving the transactions table
    $query = "
        SELECT 
            `transaction_id`, 
            `amount_tendered`, 
            `total`, 
            `change_amount`, 
            `vatable_sales`, 
            `vat_amount`, 
            `discount`, 
            `customer_id`, 
            `date_added`, 
            `time_added`, 
            `added_by`,
            (SELECT COUNT(*) FROM `transaction_items` WHERE `transaction_items`.`transaction_id` = `transactions`.`transaction_id`) AS `t_item_count`
        FROM `transactions`
    ";

    $pageLocation = "./pos-page-transactions.php";
    $limit = 20;
    include("./includes/pagination.php");
    
    //execute the sql query for getting the table of products 
    $result = $db->sql("
        $query
        WHERE $condition
        ORDER BY `date_added` DESC, `time_added` DESC, `transaction_id` DESC
        LIMIT $limit OFFSET $offset;
    ");
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

    <link rel="stylesheet" href="./css/pos.css">
    <link rel="icon" type="image/png" href="./assets/logo.png"/>
    
    <title>Point of Sale</title>
</head>

<style>
    .table-section {
        animation: transitionIn-Y-bottom 0.5s;
    }

    .admin-privileges{
        display: <?php echo $_SESSION["level"] == "2" ? "block" : "none"?>
    }
    
</style>

<body>
    <?php 
        //implement the sidebar
        $main = "Point of Sale";
        $sub = "transactions";
        include("./includes/sidebar.php");

        //add the alert messages
        include("./includes/alert-message.php");

    ?>
    
    <div class="main-div" id="main-div">
        <div class="container-fluid">

            <!--Row 1 (Heading)-->
            <?php include("./includes/header.php"); ?>


            <!--Row 2 (Secondary Heading and Buttons)-->
            <div class="row mb-4">
                <div class="col d-inline-flex align-items-center">

                    <!--Get number of products-->
                    <?php
                        if($_SERVER['REQUEST_METHOD'] == 'GET'){
                            //for searching of items
                            if(isset($_GET["search"])){
                                echo '<h5>Search Results ('.number_format($itemCount).')</h5>';
                            }
                            elseif(isset($_GET["fromDate"]) and isset($_GET["toDate"])){
                                echo '<h5>Filter Results ('.number_format($itemCount).')</h5>';
                            }
                            else{
                                echo '<h5>Transaction History ('.number_format($itemCount).')</h5>';
                            }
                        }
                        else{
                            echo '<h5>Transaction List ('.number_format($itemCount).')</h5>';
                        }
                    ?>
                </div>
                <div class="col d-flex flex-row-reverse align-items-center">
                    <button class="btn btn-blue bordered" id="print-btn"><i class="fa-solid fa-print"></i>&emsp;Print</button>

                    <button type="button" class="btn btn-blue bordered m-1" id="pos-filter" data-toggle="modal" data-target="#transactionFilterModal">
                    <i class="fa-solid fa-calendar-days"></i>&emsp;Filter
                    </button>
                </div>
            </div>

            <!--Row 4 (Table)-->
            <div class="row table-section">
                <div class="col">
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">TRANSACTION ID</th>
                                <th scope="col">AMOUNT TENDERED</th>
                                <th scope="col">CHANGE</th>
                                <th scope="col">VATABLE SALES</th>
                                <th scope="col">VAT AMOUNT</th>
                                <th scope="col">DISCOUNT</th>
                                <th scope="col">TOTAL</th>
                                <th scope="col"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                // output data of each row
                                if ($result->num_rows > 0) {
                                    
                                    //the row number
                                    $rowcount = $offset + 1;

                                    while($row = $result->fetch_assoc()) {
                                        $id = $row["transaction_id"];
                                        $user = $db->sql("SELECT `user_name` FROM `accounts` WHERE `user_id` = '".$row["added_by"]."'")->fetch_assoc()["user_name"];
                                        echo '
                                            <form action="#" method="post"><tr class="main-row" id="transaction-row-'.$id.'">
                                                <th style="text-align: right" scope="row">'.$rowcount.'</t>
                                                <td style="text-align: left" scope="row">TR-'.$id.'</t>
                                                <td style="text-align: right" scope="row">₱ '.number_format($row["amount_tendered"], "2").'</td>
                                                <td style="text-align: right" scope="row">₱ '.number_format($row["change_amount"], "2").'</td>
                                                <td style="text-align: right" scope="row">₱ '.number_format($row["vatable_sales"], "2").'</td>
                                                <td style="text-align: right" scope="row">₱ '.number_format($row["vat_amount"], "2").'</td>
                                                <td style="text-align: right" scope="row">'.$row["discount"].'%</td>
                                                <td style="text-align: right" scope="row">₱ '.number_format($row["total"], "2").'</td>
                                                <td style="text-align: right" scope="row">
                                                    <button type="button" id="collapse-row-btn-'.$id.'" class="btn btn-blue bordered collapse-row-btn">
                                                        <i class="fa-solid fa-pills"></i>&emsp;
                                                        <span class="badge badge-primary">'.$row["t_item_count"].'</span>
                                                    </button>

                                                    <button type="button" class="btn dropdown" type="button" id="actions-button-'.$id.'" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                                    </button>
                                                    <div class="dropdown-menu" aria-labelledby="actions-button">                                                        
                                                        <a id="view-transaction-'.$id.'" class="dropdown-item" data-toggle="modal" data-target="#viewTransactionModal"><i class="fa-solid fa-circle-info"></i>&emsp;View Full Details</a>
                                                        <a href="./print-page-printReceipt.php?transactionId='.$id.'&loc=transactions" id="print-receipt-'.$id.'" class="dropdown-item"><i class="fa-solid fa-receipt"></i>&emsp;Print Receipt</a>
                                                    </div>
                                                </td>
                                            </tr></form>

                                            <tr class="hidden-row">
                                                <td class="hidden-col" colspan="9">
                                                    <div class="collapse collapse-row" id="collapse-row-'.$id.'">
                                                        
                                                    </div>
                                                </td>
                                            </tr>
                                            
                                        ';

                                        //script for ajax inner tables
                                        echo '
                                            <script>
                                                $("#collapse-row-btn-'.$id.'").click(
                                                    function(){
                                                        if(! $("#hidden-table-'.$id.'").length){
                                                            $.post(
                                                                "./ajax/pos-getTransactionItems.php", 
                                                                {transaction_id: "'.$id.'"}, 
                                                                function(result){
                                                                    $("#collapse-row-'.$id.'").html(result);
                                                                    $(".collapse-row").collapse("hide");
                                                                    $("#collapse-row-'.$id.'").collapse("toggle");   
                                                            });
                                                        }
                                                        else{
                                                            $(".collapse-row").collapse("hide"); 
                                                            $("#collapse-row-'.$id.'").collapse("toggle");   
                                                        }


                                                        $("#collapse-row-'.$id.'").on("show.bs.collapse", function () {
                                                            $("#transaction-row-'.$id.'").css("transition", "background 0.3s linear");
                                                            $("#transaction-row-'.$id.'").css("background-color", "#ecf6ff");
                                                        });
            
                                                        $("#collapse-row-'.$id.'").on("hide.bs.collapse", function () {
                                                            $("#transaction-row-'.$id.'").css("background-color", "white");
                                                        });
                                                    }
                                                    
                                                );

                                                
                                            </script>
                                        ';

                                        //script for view modal
                                        echo '
                                            <script>
                                                $("#view-transaction-'.$id.'").click(function(){
                                                    $("#m-transactionId").html("TR-'.$id.'");
                                                    $("#m-total").html(`<b>Total:&emsp;<span class="text-primary">₱ '.number_format($row["total"], 2).'</span></b>`);
                                                    $("#m-amountTendered").html("₱ '.number_format($row["amount_tendered"], 2).'");
                                                    $("#m-change").html("₱ '.number_format($row["change_amount"], 2).'");
                                                    $("#m-vatableSales").html("₱ '.number_format($row["vatable_sales"], 2).'");
                                                    $("#m-vatAmount").html("₱ '.number_format($row["vat_amount"], 2).'");
                                                    $("#m-discount").html("'.$row["discount"].'%");
                                                    $("#m-customerId").html("'.$row["customer_id"].'");
                                                    $("#m-addedBy").html("'.$user.'");
                                                    $("#m-addedDuring").html("'.date("M d, Y", strtotime($row["date_added"])).'<br>'.date("h:i a", strtotime($row["time_added"])).'");
                                                });
                                                
                                            </script>
                                        ';
                                        //increment row count
                                        $rowcount++;
                                    }
                                }
                                else {
                                    //output if there are no results
                                    echo '
                                        <tr>
                                            <td colspan="8" class="text-center">
                                                <br>
                                                <img src="./assets/no_results.svg" style="width: 10%; height: 10%">
                                                <br>
                                                <br>
                                                No Results
                                            </td>
                                        </tr>
                                    ';
                                }
                            ?>
                        </tbody>
                    </table>
                    <?=$pageCount > 1 ? '<p>Page '.$page.' out of '.$pageCount.' pages</p>' : ''?>
                </div>
            </div>
            
            <?=$pagination?>

            
        </div>
    </div>


<!-- Filter Transactions Modal -->
<form class="m-3" action="./pos-page-transactions.php" method="get">
<div class="modal fade" id="transactionFilterModal" tabindex="-1" role="dialog" aria-labelledby="transactionFilterModal" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered modal" role="document">
<div class="modal-content">
<div class="modal-header">
    <h5 class="modal-title" id="exampleModalLongTitle">Date Range</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
    </button>
</div>
    <div class="modal-body">
        <input type="hidden" name="loc" value="pos-page-transactions">
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
                    <input type="date" class="form-control" name="toDate" id="toDate">
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
    <button type="button" class="btn btn-gray" data-dismiss="modal">Close</button>
    <button type="submit" class="btn btn-primary">Apply Filter</button>
    </div>
</div>
</div>
</div>
</form>


<!-- View Product Modal -->
<div class="modal fade" id="viewTransactionModal" tabindex="-1" role="dialog" aria-labelledby="viewTransactionModal" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered modal" role="document">
<div class="modal-content">
<div class="modal-header">
    <h5 class="modal-title" id="exampleModalLongTitle">Transaction Details</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
    </button>
</div>
    <div class="modal-body">
        
        <!--Transaction ID-->
        <div class="row">
            <div class="col">
                <small class="form-text text-muted" id="m-transactionId">...</small>
                <h5 id="m-total">...</h5>
            </div>
        </div>

        <hr>

        <div class="row">
            <div class="col">
                <small class="form-text text-muted">Amount Tendered</small>
                <p><span id="m-amountTendered">...</span></p>
            </div>

            <div class="col">
                <small class="form-text text-muted">Change</small>
                <p><span id="m-change">...</span></p>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <small class="form-text text-muted">Vatable Sales</small>
                <p id="m-vatableSales">...</p>
            </div>

            <div class="col">
                <small class="form-text text-muted">Vat Amount</small>
                <p id="m-vatAmount">...</p>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <small class="form-text text-muted">Discount</small>
                <p id="m-discount">...</p>
            </div>

            <div class="col">
                <small class="form-text text-muted">Customer ID</small>
                <p id="m-customerId">...</p>
            </div>
        </div>

        <hr>

        <div class="row">
            <div class="col">
                <small class="form-text text-muted">Added by</small>
                <p id="m-addedBy">...</p>
            </div>

            <div class="col">
                <small class="form-text text-muted">Added During</small>
                <p id="m-addedDuring">...</p>
            </div>
        </div>

    </div>
    <div class="modal-footer">
    <button type="button" class="btn btn-gray" data-dismiss="modal">Close</button>
    </div>
</div>
</div>
</div>



<!-- Print Transactions Modal -->
<form class="m-3" action="./print-page-transactions.php" method="get">
<div class="modal fade" id="transactionPrintModal" tabindex="-1" role="dialog" aria-labelledby="transactionPrintModal" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered modal" role="document">
<div class="modal-content">
<div class="modal-header">
    <h5 class="modal-title" id="exampleModalLongTitle">Printing Date Range</h5>
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
                    <input type="date" class="form-control" name="toDate" id="toDate">
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
    <button type="button" class="btn btn-gray" data-dismiss="modal">Close</button>
    <button type="submit" class="btn btn-primary">Print</button>
    </div>
</div>
</div>
</div>
</form>

<script src="./js/preventKeydown.js"></script>
<script>
    <?php
        if($pageCount == 1){
            echo '$("#pagination").css("display", "none");';
        }
    ?>

    $("#print-btn").click(function(){
        <?php
            if(isset($_GET["search"]) or isset($_GET["fromDate"]) or isset($_GET["toDate"]) ){
                $sg = isset($_GET["search"]) ? "search=".$_GET["search"] : "";
                $fd = isset($_GET["fromDate"]) ? "fromDate=".$_GET["fromDate"] : "";
                $td = isset($_GET["toDate"]) ? "toDate=".$_GET["toDate"] : "";

                echo 'window.location.href = "./print-page-transactions.php?'.$sg."&".$fd."&".$td.'";';
            }
            else{
                echo '$("#transactionPrintModal").modal("show")';
            }
        ?>
    });
</script>
</body>
</html>