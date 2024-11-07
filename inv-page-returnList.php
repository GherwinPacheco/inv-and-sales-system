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
    $returnStatus = isset($_GET["returnStatus"]) ? $_GET["returnStatus"] : "1";
    $condition = "
        `return_status` = '$returnStatus'
    ";

    if($_SERVER['REQUEST_METHOD'] == 'GET'){
        //for searching of items
        if(isset($_GET["search"])){

            $search = $_GET["search"];
            $condition .= " AND `batch_id` = '$search' OR (CONCAT_WS(' ', `product_name`, `properties`) LIKE '%$search%' AND `return_status` = '$returnStatus')";

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
    

    
    //set the query for retrieving the products table
    $query = "
        SELECT 
            `return_list`.`return_id`, 
            `return_list`.`batch_id`, 
            `return_list`.`product_id`, 
            `products`.`product_name` AS `product_name`, 
            `products`.`properties` AS `properties`, 
            `products`.`barcode_id` AS `barcode_id`, 
            `batch`.`single_quantity` AS `single_quantity`, 
            `batch`.`bundle_quantity` AS `bundle_quantity`,
            `products`.`unit_single` AS `unit_single`,
            `products`.`unit_bundle` AS `unit_bundle`,
            `batch`.`expiration_date` AS `expiration_date`, 
            `batch`.`expiration_status` AS `expiration_status`,
            `products`.`expirable` AS `expirable`,
            `batch`.`supplier_id` AS `supplier_id`, 
            `supplier`.`supplier_name` AS `supplier_name`, 
            `return_list`.`return_status`,  
            `return_list`.`added_by`, 
            `return_list`.`date_added`, 
            `return_list`.`time_added`, 
            `return_list`.`date_returned`, 
            `return_list`.`time_returned`, 
            `return_list`.`returned_by`, 
            `return_list`.`return_remarks`,
            `return_list`.`cancel_remarks`
        FROM `return_list`
            INNER JOIN `products` ON `return_list`.`product_id` = `products`.`product_id`
            INNER JOIN `batch` ON `return_list`.`batch_id` = `batch`.`batch_id`
            INNER JOIN  `supplier` ON `batch`.`supplier_id` = `supplier`.`supplier_id`
    ";

    $pageLocation = "./inv-page-returnList.php";
    include("./includes/pagination.php");
    
    //execute the sql query for getting the table of products 
    $result = $db->sql("
        SELECT * FROM (
            $query
        ) AS `return_table`
        WHERE $condition
        ORDER BY `date_added` DESC, `time_added` DESC
        LIMIT $limit OFFSET $offset;
    ");

    $loc = "inv-page-returnList";
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

    <link rel="stylesheet" href="./css/inventory.css">
    <link rel="icon" type="image/png" href="./assets/logo.png"/>
    
    <title>Inventory</title>
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
        $main = "Inventory";
        $sub = "return-list";
        include("./includes/sidebar.php");

        //add the alert messages
        
        include("./includes/alert-message.php");
    ?>
    <div class="main-div" id="main-div"> 
       <div class="container-fluid">

            <!--Row 1 (Heading)-->
            <?php include("./includes/header.php"); ?>



            <!--Row 2 (Secondary Heading and Status Filter)-->
            <div class="row mb-4">
                <div class="col d-inline-flex align-items-center">
                    <!--Get number of products-->
                    <?php
                        if($_SERVER['REQUEST_METHOD'] == 'GET'){
                            //for searching of items
                            if(isset($_GET["search"])){
                                echo '<h5>Search Results ('.$itemCount.')</h5>';
                            }
                            else{
                                echo '<h5>Return List ('.$itemCount.')</h5>';
                            }
                        }
                        else{
                            echo '<h5>Return List ('.$itemCount.')</h5>';
                        }
                    ?>
                </div>

                <div class="col col-sm-2 ">
                    <form action="./inv-page-returnList.php" method="get" id="returnStatusForm">
                        <label for="returnStatus">Status</label>
                        <select class="form-control bg-blue" name="returnStatus" id="returnStatus" onchange="this.form.submit()">
                            <option value="1" <?=$returnStatus == 1 ? "selected" : ""?>>Pending</option>
                            <option value="2" <?=$returnStatus == 2 ? "selected" : ""?>>Completed</option>
                            <option value="0" <?=$returnStatus == 0 ? "selected" : ""?>>Cancelled</option>
                        </select>
                    </form>
                </div>
            </div>


            <!--Table Section-->
            <div class="row table-section">
                <div class="col">
                    <table class="table rounded shadow-lg bg-white">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">BATCH ID</th>
                                <th scope="col">PRODUCT NAME</th>
                                <th scope="col">SINGLES&nbsp;
                                    <a type="button" class="row-tooltip" data-toggle="tooltip" data-placement="top" title="The quantity of products per singles">
                                    <i class="fa-solid fa-question"></i>
                                    </a>
                                </th>
                                <th scope="col">BUNDLES&nbsp;
                                    <a type="button" class="row-tooltip" data-toggle="tooltip" data-placement="top" title="The quantity of products per bundles">
                                    <i class="fa-solid fa-question"></i>
                                    </a>
                                </th>
                                <th scope="col">EXPIRATION DATE</th>
                                <th scope="col">EXPIRATION STATUS</th>
                                <th scope="col"></th>
                            </tr>
                        </thead>
                        <tbody><?php
                        if ($result->num_rows > 0) {
                            $rowCount = $offset + 1;
                            while($row = $result->fetch_assoc()){
                                $id = $row["return_id"];
                                
                                //get the unit of measurements for singles and bundles
                                $single_qty = $inv->getQuantityUnit($inv->getUnit($row["unit_single"]), $row["single_quantity"]);
                                $bundle_qty = $inv->getQuantityUnit($inv->getUnit($row["unit_bundle"]), $row["bundle_quantity"]);
                                                        
                                $expirationDate = $row["expirable"] == 1 ? $row["expiration_date"] : "N/A";
                                

                                //Determine expiration status
                                $expiration_status = $inv->setExpirationStatus($row["expiration_status"]);
                                $expiration_badge = $inv->setExpirationStatusBadge($row["expiration_status"], $row["expiration_date"]);

                                $remarksType = $returnStatus == 0 ? $row["cancel_remarks"] : $row["return_remarks"];
                                $remarksHeader = $returnStatus == 0 ? "Cancel Remarks" : "Return Remarks";

                                $remarks = $remarksType != "" ? $remarksType : "None";
                                

                                $pendingOptions = $returnStatus == '1' ? 
                                    '<a id="return-complete-'.$id.'" class="dropdown-item" data-toggle="modal" data-target="#markCompleteReturnModal"><i class="fa-solid fa-check-double"></i>&emsp;Mark as Completed</a>
                                    <a id="cancel-return-'.$id.'" class="dropdown-item" data-toggle="modal" data-target="#cancelReturnModal"><i class="fa-solid fa-ban"></i>&emsp;Cancel Return</a>' :
                                    '';
                                    
                                echo '
                                    <tr class="main-row" id="return-row-'.$id.'">
                                        <th style="text-align: center" scope="row">'.$rowCount.'</th>
                                        <td style="text-align: left" scope="col">'.$row["batch_id"].'</td>
                                        <td style="text-align: left" scope="col">'.wordwrap($row["product_name"].' '.$row["properties"],30,"<br>\n").'</td>
                                        <td style="text-align: right" scope="col">'.$single_qty.'</td>
                                        <td style="text-align: right" scope="col">'.$bundle_qty.'</td>
                                        <td style="text-align: center" scope="col">'.$expirationDate.'</td>
                                        <td style="text-align: left" scope="col">'.$expiration_badge.'</td>
                                        <td style="text-align: right">  
                                            <button type="button" class="btn btn-white dropdown" type="button" id="actions-return-batch-'.$id.'" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa-solid fa-ellipsis-vertical"></i>
                                            </button>
                                            <div class="dropdown-menu" aria-labelledby="actions-button-batch">
                                                <a id="return-details-'.$id.'" class="dropdown-item" data-toggle="modal" data-target="#viewReturnDetailsModal"><i class="fa-solid fa-circle-info"></i>&emsp;View Details</a>
                                                '.$pendingOptions.'
                                            </div>
                                        </td>
                                    </tr>
                                ';
                                $rowCount++;

                                //script for modal values
                                echo '
                                <script>
                                    $("#return-details-'.$id.'").click(function(){
                                        $("#viewReturnDetails-batchId").html("Batch-'.$row["batch_id"].' Return Details");
                                        $("#viewReturnDetails-barcodeId").html("'.$row["barcode_id"].'");
                                        $("#viewReturnDetails-productName").html("'.$row["product_name"].'");
                                        $("#viewReturnDetails-supplier").html("'.$row["supplier_name"].'");
                                        $("#viewReturnDetails-quantitySingle").html("'.$single_qty.'");
                                        $("#viewReturnDetails-quantityBundle").html("'.$bundle_qty.'");
                                        $("#viewReturnDetails-expirationDate").html("'.$expirationDate.'");
                                        $("#viewReturnDetails-expirationStatus").html("'.$expiration_status.'");
                                        $("#viewReturnDetails-remarksHeader").html(`'.$remarksHeader.'`);
                                        $("#viewReturnDetails-remarks").html(`'.$remarks.'`);
                                        $("#viewReturnDetails-addedBy").html("'.$inv->getUsername($row["added_by"]).'");
                                        $("#viewReturnDetails-addedDuring").html(`
                                            '.date("M d, Y", strtotime($row["date_added"])).'<br>'.date("h:i a", strtotime($row["time_added"])).'
                                        `);
                                        if("'.$returnStatus.'" == "2"){
                                            
                                            $("#viewReturnDetails-returnedBy").html("'.$inv->getUsername($row["added_by"]).'");
                                            $("#viewReturnDetails-returnedDuring").html(`
                                                '.date("M d, Y", strtotime($row["date_added"])).'<br>'.date("h:i a", strtotime($row["time_added"])).'
                                            `);
                                        }
                                        else{
                                            $(".returned-col").css("display", "none");
                                        }
                                    });

                                    $("#return-complete-'.$id.'").click(function(){
                                        $("#markCompleteReturn-returnId").val("'.$row["return_id"].'");
                                        $("#markCompleteReturn-batchIdText").html("Batch-'.$row["batch_id"].'");
                                    });

                                    $("#cancel-return-'.$id.'").click(function(){
                                        $("#cancelReturn-returnId").val("'.$row["return_id"].'");
                                        $("#cancelReturn-batchId").val("'.$row["batch_id"].'");
                                        $("#cancelReturn-batchIdText").html("Batch-'.$row["batch_id"].'");
                                    });
                                    </script>

                                    
                                ';
                            }
                        }
                        else{
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
                            
                        ?></tbody>
                    </table>
                </div>
            </div>

            <?=$pagination?>

       </div>
    </div>
    
    <?php include("./includes/inventory-rtnList-modal.php") ?>

<script src="./js/preventKeydown.js"></script>
<script>
    $("input").keydown(function(e){
        // Enter was pressed without shift key
        if (e.keyCode == 220)
        {
            // prevent default behavior
            e.preventDefault();
        }
    });
    <?php
        if($pageCount == 1){
            echo '$("#pagination").css("display", "none");';
        }
    ?>
    
</script>
</body>
</html>
