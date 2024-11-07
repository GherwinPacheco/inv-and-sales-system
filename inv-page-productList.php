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

    //REMEMBER!!!
    //Maglagay ka ng session para dun sa mga function na pang correct ng table 
    //para pag first time mag open ng system itatama kaagad yung mga values

    
    //stores the condition based on the POST request
    $condition = "";
    $search = "";
    $category_filter = "";
    $stock_filter = "";
    $expiration_filter = "";
    $itemCount = "";
    $page = 0;

    $loc = isset($_GET["loc"]) ? $_GET["loc"] : "inv-page-productList";

    if($_SERVER['REQUEST_METHOD'] == 'GET'){

        //for searching of items
        if(isset($_GET["search"])){

            $search = $_GET["search"];
            $condition = "`barcode_id` = '$search' OR CONCAT_WS(' ', `product_name`, `properties`) LIKE '%$search%' AND `archive_status` = '0'";

        }
        //for filtering of items
        elseif(isset($_GET["category-filter"]) or isset($_GET["stock-filter"]) or isset($_GET["expiration-filter"])){

            if(isset($_GET["category-filter"])){
                $category_filter = $_GET["category-filter"];
            }
            else{
                $category_filter = "`category_id`";
            }

            if(isset($_GET["stock-filter"])){
                $stock_filter = $_GET["stock-filter"];
            }
            else{
                $stock_filter = "`stock_status`";
            }

            if(isset($_GET["expiration-filter"])){
                $expiration_filter = $_GET["expiration-filter"];
            }
            else{
                $expiration_filter = "`expiration_status`";
            }
            
            $condition = "`category_id` = $category_filter AND `stock_status` = $stock_filter AND `expiration_status` = $expiration_filter AND `archive_status` = '0'";
            
            
        }
        //default page
        else{
            $condition = "`archive_status` = '0'";
            $page = 1;
        }
        
        //get the page of table
        if(isset($_GET["page"])){
            $page = $_GET["page"];
        }
        else{
            $page = 1;
        }
    }
    //default page
    else{
        $condition = "`archive_status` = '0'";
        $page = 1;
    }

    //set the query for retrieving the products table
    $query = "
        SELECT 
            `products`.`product_id`, `products`.`barcode_id`, `products`.`product_name`, `products`.`properties`, 
            `products`.`category_id`, `category`.`category_name`, `products`.`bundle`, 
            `products`.`single_quantity`, `products`.`bundle_quantity`,
            `products`.`unit_single`, `products`.`unit_bundle`,
            `products`.`price`, `products`.`minimum_stock`, `products`.`expirable`, `products`.`stock_status`, `products`.`archive_status`, `products`.`product_description`,
            `products`.`date_added`, `products`.`time_added`, `products`.`date_modified`, `products`.`time_modified`, 
            `products`.`added_by`, `products`.`modified_by`, 

            (SELECT IFNULL(MAX(`expiration_status`), 0)
            FROM `batch` 
            WHERE 
                `batch`.`product_id` = `products`.`product_id` AND 
                `batch`.`single_quantity` > 0 AND 
                `batch`.`archive_status` = 0 AND `batch`.`return` = 0
            ) AS `expiration_status`,

            (SELECT COUNT(`batch_id`)
            FROM `batch` 
            WHERE 
                `batch`.`product_id` = `products`.`product_id` AND 
                `batch`.`single_quantity` > 0 AND 
                `batch`.`archive_status` = 0 AND `batch`.`return` = 0
            ) AS `batch_count`
        FROM `products` 
            INNER JOIN `category` ON `products`.category_id = `category`.category_id
            
    ";

    $pageLocation = "./inv-page-productList.php";
    include("./includes/pagination.php");
    
    //execute the sql query for getting the table of products 
    $result = $db->sql("
        SELECT *
        FROM (
            $query
        ) as `products`
        WHERE $condition
        ORDER BY `product_name` ASC 
        LIMIT $limit OFFSET $offset;
    ");
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
        $sub = "product-list";
        include("./includes/sidebar.php");

        //add the alert messages
        include("./includes/alert-message.php");
    ?>
    <div class="main-div" id="main-div"> 
       <div class="container-fluid">


            <?php include("./includes/header.php"); ?>


            <!--Row 2 (Secondary Heading, Filter Badges, and Buttons)-->
            <div class="row mb-4">
                <div class="col d-inline-flex align-items-center">

                    <!--Get number of products-->
                    <?php
                        if($_SERVER['REQUEST_METHOD'] == 'GET'){
                            //for searching of items
                            if(isset($_GET["search"])){
                                echo '<h5>Search Results ('.$itemCount.')</h5>';
                            }
                            elseif(isset($_GET["category-filter"]) or isset($_GET["stock-filter"]) or isset($_GET["expiration-filter"])){
                                echo '<h5>Filter Results ('.$itemCount.')</h5>'.
                                    (isset($_GET["category-filter"]) ? 
                                        $inv->filterBadge($_GET["category-filter"], "category") : 
                                        "") .
                                    (isset($_GET["stock-filter"]) ? 
                                        $inv->filterBadge($_GET["stock-filter"], "stock") : 
                                        "") .
                                    (isset($_GET["expiration-filter"]) ? 
                                        $inv->filterBadge($_GET["expiration-filter"], "expiration") : 
                                        "");
                                
                            }
                            else{
                                echo '<h5>Products List ('.$itemCount.')</h5>';
                            }
                        }
                        else{
                            echo '<h5>Products List ('.$itemCount.')</h5>';
                        }
                    ?>
                    
                    
                </div>
                <div class="col d-flex flex-row-reverse">                
                    <!--<a href="./inv-refreshTable.php"><button type="button" class="btn btn-blue bordered m-1" id="inv-refresh" data-toggle="tooltip" data-placement="bottom" title="Refresh Table">
                        <i class="fa-solid fa-arrows-rotate"></i>
                    </button></a>-->
                    <button type="button" class="btn btn-blue bordered m-1" id="logs-btn" data-toggle="modal" data-target="#inventoryLogsModal"><i class="fa-solid fa-clock-rotate-left"></i>&emsp;Logs</button>
                    <button type="button" class="btn btn-primary bordered m-1" data-toggle="modal" data-target="#addProductModal">
                        <i class="fa-solid fa-plus"></i>&emsp;Add Product
                    </button>
                </div>
            </div>


            <!--Row 3 (Table Filtering)-->
            <form id="filter-form" class="filter-form" action="./inv-page-productList.php" method="get"><div class="row bordered rounded m-1 mb-4 shadow-sm">
                <input type="hidden" name="loc" value="inv-page-productList">
                <div class="col-9 p-3 d-flex bg-white">
                    <p class="mr-3">Filter:</p>
                    <select class="filter bg-blue bordered form-control ml-3 mr-3" name="category-filter" id="category-filter">
                        <option value="0" selected disabled>Select Category</option>
                        <?php
                            $category_db = $db->sql("
                                SELECT * 
                                FROM `category`
                                WHERE category_status = 1
                                ORDER BY `category_name` ASC
                            ");
                            while($row = $category_db->fetch_assoc()) {
                                echo '<option value="'.$row["category_id"].'">'.$row["category_name"].'</option>';
                            }
                        ?>
                        
                    </select>
                    <select class="filter bg-blue bordered form-control ml-3 mr-3" name="stock-filter" id="stock-filter">
                        <option value="0" selected disabled>Select Stock Status</option>
                        <option value="1">Available</option>
                        <option value="2">Low Stock</option>
                        <option value="3">Out of Stock</option>
                    </select>
                    <select class="filter bg-blue bordered form-control ml-3" name="expiration-filter" id="expiration-filter">
                        <option value="0" selected disabled>Select Expiration Status</option>
                        <option value="1">Good</option>
                        <option value="2">Nearly Expired</option>
                        <option value="3">Expired</option>
                    </select>
                </div>
                <div class="col d-flex flex-row-reverse p-3 bg-white">
                    <button type="button" id="clear-filter" class="btn btn-gray ">
                        <i class="fa-solid fa-xmark"></i>&emsp;Clear
                    </button>
                    <button type="submit" class="btn btn-blue mr-3">
                        <i class="fa-solid fa-filter"></i>&emsp;Apply
                    </button>
                </div>
            </div></form>
            

            <!--Row 4 (Table)-->
            <div class="row table-section px-1">
                <div class="col">
                <table class="table rounded shadow-lg bg-white">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">BARCODE ID</th>
                            <th scope="col">PRODUCT NAME</th>
                            <th scope="col">CATEGORY</th>
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
                            <th scope="col">PRICE</th>
                            <th scope="col">STOCK STATUS</th>
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
                                    //get id
                                    $id = $row["product_id"];

                                    //get the unit of measurements for singles and bundles
                                    $single_qty = $inv->getQuantityUnit($inv->getUnit($row["unit_single"]), $row["single_quantity"]);
                                    $bundle_qty = $inv->getQuantityUnit($inv->getUnit($row["unit_bundle"]), $row["bundle_quantity"]);
                                    
                                    //Determine stock status
                                    $stock_status = $inv->setStockStatus($row["stock_status"]);
                                    $stock_badge = $inv->setStockStatusBadge($row["stock_status"], $row["minimum_stock"], $row["single_quantity"]);

                                    //set the color of batch button depending if there is an expired product
                                    $btnColors = $inv->setButtonColor($row["expiration_status"]);
                                    $btn = $row["batch_count"] !== "0" ? '
                                        <button type="button" id="collapse-row-btn-'.$id.'" class="btn '.$btnColors["bgcolor"].' bordered collapse-row-btn">
                                        <i class="fa-solid fa-layer-group"></i>&emsp;
                                            <span class="badge badge-'.$btnColors["numcolor"].'">'.$row["batch_count"].'</span>
                                        </button>' :
                                        "";

                                    $productLogs = "";

                                    $user = $_SESSION["user"];
                                    $logs = $db->sql("
                                        SELECT *, `accounts`.`user_name` 
                                        FROM `user_logs`
                                            INNER JOIN `accounts` ON `user_logs`.`user_id` = `accounts`.`user_id`
                                        WHERE `user_logs`.`user_id` = '$user' AND `activity_type` = 'inventory' AND `product_id` =  $id
                                        ORDER BY `date_added` DESC,`time_added` DESC
                                        LIMIT 50
                                    ");

                                    if($logs->num_rows > 0){
                                        while($lg = $logs->fetch_assoc()){
                                            $productLogs .= '
                                                <li class="list-group-item">
                                                    <small class="text-muted">LOG: '.$lg["log_id"].'</small>
                                                    <br>
                                                    <div class="ml-5">'.$lg["user_name"].' '.$lg["activity_description"].'</div>
                                                    <small class="text-muted float-right">'.date("F d, Y", strtotime($lg["date_added"])).'&emsp;'.date("h:i a", strtotime($lg["time_added"])).'</small>
                                                    <br>
                                                </li>
                                            ';
                                        }
                                    }
                                    else{
                                        $productLogs .=  '
                                            <li class="list-group-item  p-5 text-center">
                                                <small class="text-muted">No Activities to Show</small>
                                            </li>
                                        ';
                                    }
                                        

                                    //output table
                                    echo '
                                        <tr class="main-row" id="product-row-'.$id.'">
                                            <th class="border-right" style="text-align: center;" scope="row">'.$rowcount.'</t>
                                            <td style="text-align: left" scope="col">'.($row["barcode_id"] !== "" ? $row["barcode_id"] : "N/A").'</td>
                                            <td style="text-align: left" scope="col">'.wordwrap($row["product_name"].' '.$row["properties"], "30", "<br>\n").'</td>
                                            <td style="text-align: left" scope="col">'.$row["category_name"].'</td>
                                            <td style="text-align: right" scope="col">'.$single_qty.'</td>
                                            <td style="text-align: right" scope="col">'.$bundle_qty.'</td>
                                            <td style="text-align: right" scope="col">₱ '.number_format($row["price"],2).'</td>
                                            <td style="text-align: left" scope="col">'.$stock_badge.'</td>
                                            <td style="text-align: right" scope="col">

                                                '.$btn.'

                                                <button type="button" class="btn dropdown" type="button" id="actions-button-'.$id.'" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                                </button>
                                                <div class="dropdown-menu" aria-labelledby="actions-button">
                                                    <a id="add-batch-'.$id.'" class="dropdown-item" data-toggle="modal" data-target="#addBatchModal"><i class="fa-solid fa-plus"></i>&emsp;Add New Batch</a>
                                                    <a id="view-product-'.$id.'" class="dropdown-item" data-toggle="modal" data-target="#viewProductModal"><i class="fa-solid fa-circle-info"></i>&emsp;View Product Details</a>
                                                    <a id="edit-product-'.$id.'" class="dropdown-item" data-toggle="modal" data-target="#editProductModal"><i class="fa-solid fa-pen"></i>&emsp;Edit Product Details</a>
                                                    <a id="archive-product-'.$id.'" class="dropdown-item" data-toggle="modal" data-target="#archiveProductModal"><i class="fa-solid fa-box-archive"></i>&emsp;Archive Product</a>
                                                    <a id="product-logs-'.$id.'" class="dropdown-item" data-toggle="modal" data-target="#productLogsModal"><i class="fa-solid fa-clock-rotate-left"></i>&emsp;Product Logs</a>
                                                </div>
                                            </td>
                                        </tr>


                                        <tr class="hidden-row">
                                            <td class="hidden-col" colspan="9">
                                                <div class="collapse collapse-row" id="collapse-row-'.$id.'">
                                                    
                                                </div>
                                            </td>
                                        </tr>
                                    ';
                                    //increment row count
                                    $rowcount++;


                                    //script for ajax on inner tables
                                    echo '
                                        <script>
                                            $("#collapse-row-btn-'.$id.'").click(
                                                function(){
                                                    if(! $("#hidden-table-'.$id.'").length){
                                                        $.post(
                                                            "./ajax/inventory-getbatch.php", 
                                                            {product_id: "'.$id.'"}, 
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
                                                }
                                            );

                                            $("#collapse-row-'.$id.'").on("show.bs.collapse", function () {
                                                $("#product-row-'.$id.'").css("transition", "background 0.3s linear");
                                                $("#product-row-'.$id.'").css("background-color", "#ecf6ff");
                                            });

                                            $("#collapse-row-'.$id.'").on("hide.bs.collapse", function () {
                                                $("#product-row-'.$id.'").css("background-color", "white");
                                            });

                                        </script>
                                    ';


                                    //script for modal values
                                    echo '
                                        <script>
                                            $("#add-batch-'.$id.'").click(function(){
                                                $("#addBatch-productId").val("'.$id.'");
                                                $("#addBatch-barcodeId").val("'.$row["barcode_id"].'");
                                                $("#addBatch-productName").val("'.$row["product_name"].'");
                                                $("#addBatch-bundlePieces").val("'.$row["bundle"].'");
                                                $("#addBatch-expirable").val("'.$row["expirable"].'");

                                                $("#addBatch-barcodeIdText").html("'.$row["barcode_id"].'");
                                                $("#addBatch-productNameText").html("'.$row["product_name"].' '.$row["properties"].'");

                                                $("#addBatch-price").val("'.$row["price"].'");

                                                if("'.$row["bundle"].'" == 0){
                                                    $("#addBatch-quantityBundle").attr("readonly", true);
                                                    $("#addBatch-bundlePiecesText").html("");
                                                }
                                                else{
                                                    $("#addBatch-quantityBundle").attr("readonly", false);
                                                    $("#addBatch-bundlePiecesText").html("<b>'.$row["bundle"].' '.$inv->getUnit($row["unit_single"]).'</b> per bundle");
                                                }

                                                if("'.$row["expirable"].'" == 0){
                                                    $("#addBatch-expirationDate").attr("readonly", true);
                                                }
                                                else{
                                                    $("#addBatch-expirationDate").attr("readonly", false);
                                                }
                                            });


                                            $("#view-product-'.$id.'").click(function(){
                                                $("#viewProduct-barcodeId").html("'.$row["barcode_id"].'");
                                                $("#viewProduct-productName").html("'.$row["product_name"].' '.$row["properties"].'");
                                                $("#viewProduct-category").html("'.$row["category_name"].'");
                                                $("#viewProduct-price").html("'.number_format($row["price"],2).'");
                                                $("#viewProduct-bundlePieces").html(("'.$row["bundle"].'" === "0") ? "N/A" : "'.$inv->getQuantityUnit($inv->getUnit($row["unit_single"]), $row["bundle"]).'");
                                                $("#viewProduct-quantitySingle").html("'.$single_qty.'");
                                                $("#viewProduct-quantityBundle").html("'.$bundle_qty.'");
                                                $("#viewProduct-minimumStock").html("'.$inv->getQuantityUnit($inv->getUnit($row["unit_single"]), $row["minimum_stock"]).'");
                                                $("#viewProduct-stockStatus").html("'.$stock_status.'");
                                                $("#viewProduct-productDescription").html( ("'.$row["product_description"].'" == "") ? "None" : "'.$row["product_description"].'");
                                                $("#viewProduct-addedBy").html("'.$inv->getUsername($row["added_by"]).'");
                                                $("#viewProduct-modifiedBy").html("'.$inv->getUsername($row["modified_by"]).'");
                                                $("#viewProduct-addedDuring").html(`
                                                    '.date("M d, Y", strtotime($row["date_added"])).'<br>'.date("h:i a", strtotime($row["time_added"])).'
                                                `);
                                                $("#viewProduct-modifiedDuring").html( ("'.$row["date_modified"].'" == "0000-00-00") && ("'.$row["time_modified"].'" == "00:00:00") ? "N/A" : "'.date("M d, Y", strtotime($row["date_modified"])).'<br>'.date("h:i a", strtotime($row["time_modified"])).'");
                                            });


                                            $("#edit-product-'.$id.'").click(function(){
                                                $("#editProduct-productId").val("'.$id.'");
                                                $("#editProduct-oldBarcode").val("'.$row["barcode_id"].'");
                                                $("#editProduct-barcodeId").val("'.$row["barcode_id"].'");
                                                $("#editProduct-productName").val("'.$row["product_name"].'");
                                                $("#editProduct-productProperties").val("'.$row["properties"].'");
                                                $("#editProduct-category").val("'.$row["category_id"].'");
                                                $("#editProduct-minimumStock").val("'.$row["minimum_stock"].'");
                                                $("#editProduct-price").val("'.$row["price"].'");
                                                $("#editProduct-unitSingle").val("'.$row["unit_single"].'");


                                                $("#editProduct-oldBundlePieces").val("");
                                                $("#editProduct-oldUnitBundle").val("");

                                                
                                                if("'.$row["bundle"].'" == 0){
                                                    $(".editProduct-bundle-form").attr("readonly", true);
                                                    $("select.editProduct-bundle-form option").prop("disabled", true);
                                                    $("#editProduct-bundlePieces").val("");
                                                    $("#editProduct-unitBundle").val("");
                                                    $("#editProduct-noBundle").prop("checked", true);
                                                }
                                                else{
                                                    $(".editProduct-bundle-form").attr("readonly", false);
                                                    $("select.editProduct-bundle-form option").prop("disabled", false);
                                                    $("#editProduct-bundlePieces").val("'.$row["bundle"].'");
                                                    $("#editProduct-oldBundlePieces").val("'.$row["bundle"].'");
                                                    $("#editProduct-unitBundle").val("'.$row["unit_bundle"].'");
                                                    $("#editProduct-oldUnitBundle").val("'.$row["unit_bundle"].'");
                                                    $("#editProduct-noBundle").prop("checked", false);
                                                }

                                                $("#editProduct-expirable").val("'.$row["expirable"].'");
                                                $("#editProduct-description").val("'.$row["product_description"].'");
                                            });


                                            $("#archive-product-'.$id.'").click(function(){
                                                $("#archiveProduct-productName").html("'.$row["product_name"].'");
                                                $("#archiveProduct-productId").val("'.$id.'");
                                            });

                                            $("#product-logs-'.$id.'").click(function(){
                                                $("#productLogsModal .modal-body").html(`'.$productLogs.'`);
                                            });
                                        </script>
                                    ';
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
    
    <?php include("./includes/inventory-prdList-modal.php") ?>
    
<script src="./js/preventKeydown.js"></script>
<script>
    //clear all values in filter form
    $("#clear-filter").click(function(){
        $(".filter").val("0");
    });

    /* initializate the tooltips after ajax requests, if not already done */    
    $( document ).ajaxComplete(function( event, request, settings ) {
        $('[data-toggle="tooltip"]').not( '[data-original-title]' ).tooltip();
    });

    //requires filter form the have atleast 1 value
    $('#filter-form').submit(function (evt) {
        var category = $("#category-filter").val();
        var stock = $("#stock-filter").val();
        var expiration = $("#expiration-filter").val();
        
        if( !(category >= 1) && !(stock >= 1) && !(expiration >= 1)){
            evt.preventDefault();
        }
    });

    <?php
        if($pageCount == 1){
            echo '$("#pagination").css("display", "none");';
        }
    ?>

    $("textarea").keydown(function(e){
    // Enter was pressed without shift key
    if (e.keyCode == 13 && !e.shiftKey)
    {
        // prevent default behavior
        e.preventDefault();
    }
    });

    $("input").keydown(function(e){
        // Enter was pressed without shift key
        if (e.keyCode == 220)
        {
            // prevent default behavior
            e.preventDefault();
        }
    });


    $("#inventory-search").on("input", function(){
        if($(this).val() !== ""){
            $.post(
                "./ajax/inventory-search-recommend.php", 
                {search: $(this).val()}, 
                function(result){
                    $("#search-rec").html(result);
                }
            );
        }
        else{
            $("#search-rec").html("");
        }
    });

    $("body").click(function(){
        $("#search-rec").html("");
    });

</script>

</body>
</html>
