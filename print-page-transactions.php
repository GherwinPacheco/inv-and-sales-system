<?php
    session_start();
    include("./includes/login-check.php");
    
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

    //stores the condition based on the POST request
    $search = "";
    $condition = "1";
    $fromDate = "";
    $toDate = "";

    $headerText = "";

    if($_SERVER['REQUEST_METHOD'] == 'GET'){
        //for searching of items
        if(isset($_GET["search"])){
            $search = $_GET["search"];
            $condition .= " AND `transaction_id` = '$search'";
        }
        
        if(isset($_GET["fromDate"]) and isset($_GET["toDate"]) and $_GET["toDate"] !== ""){
            $fromDate = $_GET["fromDate"];
            $toDate = $_GET["toDate"];
            $condition .= " AND `date_added` BETWEEN '$fromDate' AND '$toDate'";
            $headerText = "Transactions between ".date("M d, Y", strtotime($fromDate))." and ".date("M d, Y", strtotime($toDate));
        }
        elseif(isset($_GET["fromDate"])){
            $fromDate = $_GET["fromDate"];
            $condition .= " AND `date_added` = '$fromDate'";
            $headerText = "Transactions during ".date("M d, Y", strtotime($fromDate));
        }

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
    
    //execute the sql query for getting the table of products 
    $result = $db->sql("
        $query
        WHERE $condition
        ORDER BY `date_added` DESC, `time_added` DESC
    ");
    if($result->num_rows < 1){
        header("Location: ./pos-page-transactions.php");
    }
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

    <title>Transaction History</title>
</head>

<style>
    p{
        padding: 0;
        margin: 0;
    }
    table{
        font-size: 13px;
    }
    .table th, .table td{
        vertical-align: middle;
        padding: 10px 5px;
    }
    hr{
        margin: 10px 0px;
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
</style>

<body>
    <div id="buttonDiv" style="margin: 30px; auto; display: flex; justify-content: end">
        <a href="./pos-page-transactions.php"><button type="button" class="btn btn-secondary m-3">Back</button></a>
        <button type="button" class="btn btn-primary m-3" onclick="hideButtons()">Print</button>
    </div>


    <div class="main-div" id="main-div" style="width: 215.9mm; margin: auto">
        <div class="header mb-5">
            <h2 class="text-center">Transactions</h2>
            <br><br>
            <div class="row">
                <div class="col col-6">
                    <h5><?=$storeName?></h5>
                    <p><?=$storeAddress?></p>
                    <p><?=$contact?></p>
                    <p>Prepared by:&emsp;<?=$username?></p>
                </div>
                <div class="col col-3"></div>
                <div class="col col-3">
                    <h6>Date:</h6>
                    <p>&emsp;<?=$date?></p>
                    <p>&emsp;<?=$time?></p>
                </div>
            </div>
            
        </div>

        <div class="page">
            <h5 class="mb-5">
                <?= $headerText ?>
            </h5>

            <table class="table mt-5">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">TRANSACTION ID</th>
                        <!--<th scope="col">ITEMS</th>-->
                        <th scope="col">AMOUNT TENDERED</th>
                        <th scope="col">CHANGE</th>
                        <th scope="col">VATABLE SALES</th>
                        <th scope="col">VAT AMOUNT</th>
                        <th scope="col">DISCOUNT</th>
                        <th scope="col">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        // output data of each row
                        if ($result->num_rows > 0) {
                            
                            //the row number
                            $rowcount = 1;

                            while($row = $result->fetch_assoc()) {
                                $id = $row["transaction_id"];

                                $items = "";

                                /*$itemCount = $db->sql("
                                    SELECT COUNT(*) AS `itemCount`
                                    FROM `transaction_items` 
                                    WHERE `transaction_id` = '$id'
                                ")->fetch_assoc()["itemCount"];

                                $trItems = $db->sql("
                                    SELECT `products`.`product_name` 
                                    FROM `transaction_items` 
                                        INNER JOIN `products` on `transaction_items`.`product_id` = `products`.`product_id`
                                    WHERE `transaction_id` = '$id'
                                ");

                                $x = 0;
                                while($row2 = $trItems->fetch_assoc()){
                                    $items .= wordwrap($row2["product_name"],20,"<br>\n");;

                                    $x++;
                                    if($x < $itemCount){
                                        $items .= "<hr>";
                                    }
                                }*/

                                echo '
                                    <tr>
                                        <th style="text-align: right" scope="row">'.$rowcount.'</t>
                                        <td style="text-align: left" scope="row">TR-'.$id.'</t>
                                        <!--<td>'.$items.'</td>-->
                                        <td style="text-align: right" scope="row">₱ '.number_format($row["amount_tendered"], "2").'</td>
                                        <td style="text-align: right" scope="row">₱ '.number_format($row["change_amount"], "2").'</td>
                                        <td style="text-align: right" scope="row">₱ '.number_format($row["vatable_sales"], "2").'</td>
                                        <td style="text-align: right" scope="row">₱ '.number_format($row["vat_amount"], "2").'</td>
                                        <td style="text-align: right" scope="row">'.$row["discount"].'%</td>
                                        <td style="text-align: right" scope="row">₱ '.number_format($row["total"], "2").'</td>
                                    </tr>
                                ';
                                $rowcount++;
                            }
                            
                        }
                    ?>
                </tbody>
            </table>
        </div>

    </div>

    <script>
        function hideButtons(){
            $("#buttonDiv").css("display", "none");
            window.print();
            $("#buttonDiv").css("display", "flex");
        }
    </script>
</body>