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


    $condition = "`archive_status` = '1'";
    $tableType = "";
    $itemCount = "";
    $page = 0;
    $query = "";
    
    $unitSingle = "
        (SELECT `unit_name` FROM `unit_measurement` WHERE `products`.`unit_single` = `unit_measurement`.`unit_id`) AS `unit_single_name`
    ";
    $unitBundle = "
        (SELECT `unit_name` FROM `unit_measurement` WHERE `products`.`unit_bundle` = `unit_measurement`.`unit_id`) AS `unit_bundle_name`
    ";
    if($_SERVER['REQUEST_METHOD'] == 'GET'){
        if(isset($_GET["tableType"])){
            $tableType = $_GET["tableType"];
            if($_GET["tableType"] == "1"){
                //set the query for retrieving the products table
                
                $query = "
                SELECT 
                    `products`.`product_id`, `products`.`barcode_id`, `products`.`product_name`, `products`.`properties`,
                    `category`.`category_name`, `products`.`single_quantity`, `products`.`bundle_quantity`,
                    `products`.`unit_single`, `products`.`unit_bundle`, $unitSingle, $unitBundle, `products`.`minimum_stock`,
                    `products`.`price`, `products`.`stock_status`, `products`.`archive_status`,
                    `products`.`archived_by`, `products`.`date_archived`, `products`.`time_archived`, `products`.`archive_remarks`
                FROM `products` 
                    INNER JOIN `category` ON `products`.category_id = `category`.category_id
                ";
            }
            else{
                $query = "
                SELECT 
                    `products`.`barcode_id`, `products`.`product_name`, `products`.`properties`, `batch`.`batch_id`, `batch`.`product_id`, `batch`.`single_quantity`, `batch`.`bundle_quantity`, 
                    `products`.`unit_single`, `products`.`unit_bundle`, $unitSingle, $unitBundle, `batch`.`price`, `batch`.`expiration_date`, 
                    `batch`.`expiration_status`, `batch`.`archive_status`, `batch`.`archived_by`, `batch`.`date_archived`, `batch`.`time_archived`, `batch`.`archive_remarks`
                FROM `batch`
                    INNER JOIN products ON `batch`.product_id = `products`.product_id
                ";
            }
        }
        else{
            $query = "
            SELECT 
                `products`.`product_id`, `products`.`barcode_id`, `products`.`product_name`, `products`.`properties`,
                `category`.`category_name`, `products`.`single_quantity`, `products`.`bundle_quantity`,
                `products`.`unit_single`, `products`.`unit_bundle`, $unitSingle, $unitBundle, `products`.`minimum_stock`,
                `products`.`price`, `products`.`stock_status`, `products`.`archive_status`,
                `products`.`archived_by`, `products`.`date_archived`, `products`.`time_archived`, `products`.`archive_remarks`
            FROM `products` 
                INNER JOIN `category` ON `products`.category_id = `category`.category_id
            ";
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
        $page = 1;
        $query = "
            SELECT 
                `products`.`product_id`, `products`.`barcode_id`, `products`.`product_name`, `products`.`properties`,
                `category`.`category_name`, `products`.`single_quantity`, `products`.`bundle_quantity`,
                `products`.`unit_single`, `products`.`unit_bundle`, $unitSingle, $unitBundle, `products`.`minimum_stock`,
                `products`.`price`, `products`.`stock_status`, `products`.`archive_status`,
                `products`.`archived_by`, `products`.`date_archived`
            FROM `products` 
                INNER JOIN `category` ON `products`.category_id = `category`.category_id
            ";
    }

    

    $pageLocation = "./inv-page-settings.php#archiveTable";
    include("./includes/pagination.php");
    
    //execute the sql query for getting the table of products 
    $result = $db->sql("
        SELECT *
        FROM (
            $query
        ) as `archived_table`
        WHERE $condition
        ORDER BY `date_archived` ASC 
        LIMIT $limit OFFSET $offset;
    ");

    $table = "";
    if(isset($_GET["tableType"])){
        if($_GET["tableType"] == "1"){
            $table = setProductTable($result, $inv);
        }
        else{
            $table = setBatchTable($result, $inv);
        }
    }
    else{
        $table = setProductTable($result, $inv);
    }

    

    function setProductTable($result, $inv){
        $tr = "";
        if($result->num_rows > 0){
            $rowCount = 1;
            while($row = $result->fetch_assoc()){
                //get the unit of measurements for singles and bundles
                $single_qty = $inv->getQuantityUnit($inv->getUnit($row["unit_single"]), $row["single_quantity"]);
                $bundle_qty = $inv->getQuantityUnit($inv->getUnit($row["unit_bundle"]), $row["bundle_quantity"]);

                //Determine stock status
                $stock_status = $inv->setStockStatus($row["stock_status"]);
                $stock_badge = $inv->setStockStatusBadge($row["stock_status"], $row["minimum_stock"], $row["single_quantity"]);

                $tr .= '
                    <tr>
                        <td style="text-align: center" scope="col">'.$rowCount.'</td>
                        <td style="text-align: left" scope="col">'.$row["barcode_id"].'</td>
                        <td style="text-align: left" scope="col">'.$row["product_name"].' '.$row["properties"].'</td>
                        <td style="text-align: center" scope="col">'.$row["category_name"].'</td>
                        <td style="text-align: center" scope="col">'.$single_qty.'</td>
                        <td style="text-align: center" scope="col">'.$bundle_qty.'</td>
                        <td style="text-align: center" scope="col">₱ '.number_format($row["price"], 2).'</td>
                        <td style="text-align: center" scope="col">'.$stock_badge.'</td>
                        <td style="text-align: right" scope="col">
                            <button type="button" class="btn dropdown" type="button" id="actions-button-'.$row["product_id"].'" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa-solid fa-ellipsis-vertical"></i>
                            </button>
                            <div class="dropdown-menu" aria-labelledby="actions-button">
                                <a id="p-view-archive-'.$row["product_id"].'" class="dropdown-item" data-toggle="modal" data-target="#viewProductArchiveModal"><i class="fa-solid fa-circle-info"></i>&emsp;View Archive Details</a>
                                <a id="restore-'.$row["product_id"].'" class="dropdown-item" href="./inv-form-restoreProduct.php?id='.$row["product_id"].'"><i class="fa-solid fa-clock-rotate-left"></i>&emsp;Restore Product</a>
                            </div>
                        </td>
                    </tr>

                    <script>
                        $("#p-view-archive-'.$row["product_id"].'").click(function(){
                            $("#p-barcodeId").html("'.$row["barcode_id"].'");
                            $("#p-productName").html("'.$row["product_name"].'");
                            $("#p-archiveRemarks").html( ("'.$row["archive_remarks"].'" == "") ? "None" : "'.$row["archive_remarks"].'");
                            $("#p-archivedBy").html("'.$inv->getUsername($row["archived_by"]).'");
                            $("#p-dateArchived").html(`
                                '.date("M d, Y", strtotime($row["date_archived"])).'<br>'.date("h:i a", strtotime($row["time_archived"])).'
                            `);
                        });
                    </script>
                    
                ';
                $rowCount++;
            }
        }
        else{
            $tr .= '
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
        $table = '
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">BARCODE ID</th>
                        <th scope="col">PRODUCT NAME</th>
                        <th scope="col">CATEGORY</th>
                        <th scope="col">SINGLES</th>
                        <th scope="col">BUNDLES</th>
                        <th scope="col">PRICE</th>
                        <th scope="col">STOCK STATUS</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    '.$tr.'
                </tbody>
            </table>
        ';
        return $table;
    }

    function setBatchTable($result, $inv){
        $tr = "";
        if($result->num_rows > 0){
            $rowCount = 1;
            while($row = $result->fetch_assoc()){

                //get the unit of measurements for singles and bundles
                $single_qty = $inv->getQuantityUnit($inv->getUnit($row["unit_single"]), $row["single_quantity"]);
                $bundle_qty = $inv->getQuantityUnit($inv->getUnit($row["unit_bundle"]), $row["bundle_quantity"]);

                //Determine expiration status
                $expiration_status = $inv->setExpirationStatus($row["expiration_status"]);
                $expiration_badge = $inv->setExpirationStatusBadge($row["expiration_status"], $row["expiration_date"]);

                $expirationDate = $row["expiration_date"] != "0000-00-00" ? $row["expiration_date"] : "N/A";

                $tr .= '
                    <tr>
                        <td style="text-align: center" scope="col">'.$rowCount.'</td>
                        <td style="text-align: left" scope="col">'.$row["batch_id"].'</td>
                        <td style="text-align: left" scope="col">'.$row["product_name"].' '.$row["properties"].'</td>
                        <td style="text-align: center" scope="col">'.$single_qty.'</td>
                        <td style="text-align: center" scope="col">'.$bundle_qty.'</td>
                        <td style="text-align: center" scope="col">₱ '.number_format($row["price"], 2).'</td>
                        <td style="text-align: center" scope="col">'.$expirationDate.'</td>
                        <td style="text-align: center" scope="col">'.$expiration_badge.'</td>
                        <td style="text-align: right" scope="col">
                            <button type="button" class="btn dropdown" type="button" id="actions-button-'.$row["batch_id"].'" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa-solid fa-ellipsis-vertical"></i>
                            </button>
                            <div class="dropdown-menu" aria-labelledby="actions-button">
                                <a id="p-view-archive-'.$row["batch_id"].'" class="dropdown-item" data-toggle="modal" data-target="#viewProductArchiveModal"><i class="fa-solid fa-circle-info"></i>&emsp;View Archive Details</a>
                                <a class="dropdown-item" href="./inv-form-restoreBatch.php?id='.$row["batch_id"].'"><i class="fa-solid fa-clock-rotate-left"></i>&emsp;Restore Batch</a>
                            </div>
                            
                        </td>
                    </tr>

                    <script>
                        $("#p-view-archive-'.$row["batch_id"].'").click(function(){
                            $("#p-barcodeId").html("Batch-'.$row["batch_id"].'");
                            $("#p-productName").html("'.$row["product_name"].'");
                            $("#p-archiveRemarks").html( ("'.$row["archive_remarks"].'" == "") ? "None" : "'.$row["archive_remarks"].'");
                            $("#p-archivedBy").html("'.$inv->getUsername($row["archived_by"]).'");
                            $("#p-dateArchived").html(`
                                '.date("M d, Y", strtotime($row["date_archived"])).'<br>'.date("h:i a", strtotime($row["time_archived"])).'
                            `);
                        });
                    </script>
                ';
                $rowCount++;
            }
        }
        else{
            $tr .= '
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
        $table = '
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>BATCH ID</th>
                        <th>PRODUCT NAME</th>
                        <th>SINGLES</th>
                        <th>BUNDLES</th>
                        <th>PRICE</th>
                        <th>EXPIRATION DATE</th>
                        <th>EXPIRATION STATUS</th>
                        <th></th>
                    </tr>
                    
                </thead>
                <tbody>
                    '.$tr.'
                </tbody>
            </table>
        ';
        return $table;
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

    <link rel="stylesheet" href="./css/inventory.css">
    <link rel="stylesheet" href="./css/config.css">
    <link rel="icon" type="image/png" href="./assets/logo.png"/>
    
    <title>Inventory Settings</title>
</head>

<style>
    .settings-section {
        animation: transitionIn-Y-bottom 0.5s;
    }

    .admin-privileges{
        display: <?php echo $_SESSION["level"] == "2" ? "block" : "none"?>
    }
</style>

<body>
    <?php 
        //implement the sidebar
        $main = "Configurations";
        $sub = "inv-settings";
        include("./includes/sidebar.php");

        //add the alert messages
        include("./includes/alert-message.php");
    ?>
    <div class="main-div" id="main-div">
        
        <!--Row 1 (Heading)-->
        <?php include("./includes/header.php"); ?>


        <!--Settings Section Container Div-->
        <div class="container mb-5 settings-section">

            <!--Row 2 Category Section-->
            <?php
                $category = $db->sql("
                    SELECT `category_id`, `category_name`, 
                    (
                        SELECT COUNT(`products`.`product_id`) 
                        FROM `products` 
                        WHERE `products`.`category_id` = `category`.`category_id` AND `products`.`archive_status` = '0'
                    ) as `product_count`
                    FROM `category` 
                    WHERE category_status = 1
                    ORDER BY `category_name` ASC
                ");

                $unit = $db->sql("
                    SELECT `unit_id`, `unit_name`,
                    (
                        SELECT COUNT(`products`.`product_id`) 
                        FROM `products` 
                        WHERE `products`.`unit_single` = `unit_measurement`.`unit_id` OR `products`.`unit_bundle` = `unit_measurement`.`unit_id` AND `products`.`archive_status` = '0'
                    ) as `product_count`
                    FROM `unit_measurement` 
                    WHERE `unit_status` = 1
                    ORDER BY `unit_name` ASC
                ");

                $supplier = $db->sql("
                    SELECT `supplier_id`, `supplier_name`, `contact`,
                    (
                        SELECT COUNT(`batch`.`batch_id`) 
                        FROM `batch` 
                        WHERE `batch`.`supplier_id` = `supplier`.`supplier_id` AND `batch`.`archive_status` = '0'
                    ) as `product_count`
                    FROM `supplier` 
                    WHERE `supplier_status` = 1
                    ORDER BY `supplier_name` ASC
                ");

                $reason = $db->sql("
                    SELECT * FROM archive_reasons WHERE reason_status = 1
                ");
            ?>
            <div class="row mb-5">
                <div class="col col-lg-6">
                    <button class="btn btn-primary my-2" data-toggle="modal" data-target="#addCategoryModal"><i class="fa-solid fa-plus"></i>&emsp;Add</button>
                    <div class="upperTables shadow-lg bg-white rounded">
                        <table class="table">
                            <thead>
                                <th colspan="2">CATEGORIES</th>
                            </thead>
                            <tbody>
                                <?php
                                    while($row = $category->fetch_assoc()){
                                        $link = $row["product_count"] > 0 ? "#" : "#disableCategoryModal";
                                        $color = $row["product_count"] > 0 ? 'secondary' : "primary";
                                        $title = $row["product_count"] > 0 ? 'Category is being used by '. $row["product_count"].' products' : "Disable Category";
                                        echo '
                                            <tr>
                                                <td>'.$row["category_name"].'</td>
                                                <td>
                                                    <a id="disable-category-'.$row["category_id"].'" class="float-right ml-3 text-'.$color.'" data-toggle="modal" data-target="'.$link.'"><i class="fa-solid fa-ban"></i></a>
                                                
                                                    <a id="edit-category-'.$row["category_id"].'" class="float-right" data-toggle="modal" data-target="#editCategoryModal" title="Edit Category"><i class="fa-solid fa-pen"></i></a>
                                                </td>
                                            </tr>

                                            <script>
                                                $("#edit-category-'.$row["category_id"].'").click(function(){
                                                    $("#editCategory-categoryId").val("'.$row["category_id"].'");
                                                    $("#editCategory-oldCategoryName").val("'.$row["category_name"].'");

                                                    $("#editCategory-categoryName").val("'.$row["category_name"].'");
                                                });
                                                
                                                $("#disable-category-'.$row["category_id"].'").click(function(){
                                                    $("#disableCategory-categoryId").val("'.$row["category_id"].'");
                                                    $("#disableCategory-categoryNameText").html("'.$row["category_name"].'");
                                                });
                                            </script>
                                        ';
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col col-lg-6">
                    <button class="btn btn-primary my-2" data-toggle="modal" data-target="#addUnitModal"><i class="fa-solid fa-plus"></i>&emsp;Add</button>
                    <div class="upperTables shadow-lg bg-white rounded">
                        <table class="table">
                            <thead>
                                <th colspan="2">UNIT OF MEASUREMENT</th>
                            </thead>
                            <tbody>
                                <?php
                                    while($row = $unit->fetch_assoc()){
                                        $link = $row["product_count"] > 0 ? "#" : "#disableUnitModal";
                                        $color = $row["product_count"] > 0 ? 'secondary' : "primary";
                                        echo '
                                            <tr>
                                                <td>'.$row["unit_name"].'</td>
                                                <td>
                                                    <a id="disable-unit-'.$row["unit_id"].'" class="float-right ml-3 text-'.$color.'" data-toggle="modal" data-target="'.$link.'"><i class="fa-solid fa-ban"></i></a>

                                                    <a id="edit-unit-'.$row["unit_id"].'" class="float-right" data-toggle="modal" data-target="#editUnitModal" title="Edit Unit"><i class="fa-solid fa-pen"></i></a>
                                                </td>
                                            </tr>


                                            <script>
                                                $("#edit-unit-'.$row["unit_id"].'").click(function(){
                                                    $("#editUnit-unitId").val("'.$row["unit_id"].'");
                                                    $("#editUnit-oldUnitName").val("'.$row["unit_name"].'");

                                                    $("#editUnit-unitName").val("'.$row["unit_name"].'");
                                                });
                                                
                                                $("#disable-unit-'.$row["unit_id"].'").click(function(){
                                                    $("#disableUnit-unitId").val("'.$row["unit_id"].'");
                                                    $("#disableUnit-unitNameText").html("'.$row["unit_name"].'");
                                                });
                                            </script>
                                        ';
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col col-lg-6 mt-5">
                    <button class="btn btn-primary my-2" data-toggle="modal" data-target="#addSupplierModal"><i class="fa-solid fa-plus"></i>&emsp;Add</button>
                    <div class="upperTables shadow-lg bg-white rounded">
                        <table class="table">
                            <thead>
                                <th colspan="3">SUPPLIER</th>
                            </thead>
                            <tbody>
                                <?php
                                    while($row = $supplier->fetch_assoc()){
                                        $color = $row["product_count"] > 0 ? 'secondary' : "primary";
                                        echo '
                                            <tr>
                                                <td>'.$row["supplier_name"].'</td>
                                                <td><small class="text-muted">'.$row["contact"].'</small></td>
                                                <td>
                                                    <a id="disable-supplier-'.$row["supplier_id"].'" class="float-right ml-3 text-primary" data-toggle="modal" data-target="#disableSupplierModal"><i class="fa-solid fa-ban"></i></a>
                                                
                                                    <a id="edit-supplier-'.$row["supplier_id"].'" class="float-right" data-toggle="modal" data-target="#editSupplierModal" title="Edit Category"><i class="fa-solid fa-pen"></i></a>
                                                </td>
                                            </tr>

                                            <script>
                                                $("#edit-supplier-'.$row["supplier_id"].'").click(function(){
                                                    $("#editSupplier-supplierId").val("'.$row["supplier_id"].'");
                                                    $("#editSupplier-supplierName").val("'.$row["supplier_name"].'");
                                                    $("#editSupplier-contact").val("'.$row["contact"].'");
                                                });
                                                
                                                $("#disable-supplier-'.$row["supplier_id"].'").click(function(){
                                                    $("#disableSupplier-supplierId").val("'.$row["supplier_id"].'");
                                                    $("#disableSupplier-supplierNameText").html("'.$row["supplier_name"].'");
                                                });
                                            </script>
                                        ';
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col col-lg-6 mt-5">
                    <button class="btn btn-primary my-2" data-toggle="modal" data-target="#addArchiveReasonModal"><i class="fa-solid fa-plus"></i>&emsp;Add</button>
                    <div class="upperTables shadow-lg bg-white rounded">
                        <table class="table">
                            <thead>
                                <th colspan="3">ARCHIVE/RETURN REASONS</th>
                            </thead>
                            <tbody>
                                <?php
                                    while($row = $reason->fetch_assoc()){
                                        echo '
                                            <tr>
                                                <td>'.$row["reason"].'</td>
                                                <td>
                                                    <a id="disable-reason-'.$row["reason_id"].'" class="float-right ml-3 text-primary" data-toggle="modal" data-target="#disableArchiveReasonModal"><i class="fa-solid fa-ban"></i></a>
                                                
                                                    <a id="edit-reason-'.$row["reason_id"].'" class="float-right" data-toggle="modal" data-target="#editArchiveReasonModal" title="Edit Category"><i class="fa-solid fa-pen"></i></a>
                                                </td>
                                            </tr>

                                            <script>
                                                $("#edit-reason-'.$row["reason_id"].'").click(function(){
                                                    $("#editArchiveReason-reasonId").val("'.$row["reason_id"].'");
                                                    $("#editArchiveReason-oldReasonName").val("'.$row["reason"].'");
                                                    $("#editArchiveReason-reasonName").val("'.$row["reason"].'");
                                                });
                                                
                                                $("#disable-reason-'.$row["reason_id"].'").click(function(){
                                                    $("#disableArchiveReason-reasonId").val("'.$row["reason_id"].'");
                                                    $("#disableArchiveReason-reasonNameText").html("'.$row["reason"].'");
                                                });
                                            </script>
                                        ';
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <hr>

            <div class="row mt-5 mb-3">
                <div class="col">
                    <h5 class="mb-3">Archived Items (<?=$result->num_rows?>)</h5>
                </div>
                <div class="col d-flex flex-row-reverse">
                    <form action="./inv-page-settings.php#archiveTable" method="get">
                        <select class="form-control" name="tableType" id="tableType" onchange="this.form.submit()">
                            <option value="1" <?=$tableType == 1 ? "selected" : ""?>>Archive Products</option>
                            <option value="2" <?=$tableType == 2 ? "selected" : ""?>>Archive Batches</option>
                        </select>
                    </form>
                </div>
                
            </div>
            <div class="row" id="archiveTable" tabindex="1">
                <?=$table?>
            </div>
            <?=$pagination?>



        </div>
    </div>


<!-- View Product Modal -->
<div class="modal fade" id="viewProductArchiveModal" tabindex="-1" role="dialog" aria-labelledby="viewProductArchiveModal" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered modal" role="document">
<div class="modal-content">
<div class="modal-header">
    <h5 class="modal-title" id="exampleModalLongTitle">Archive Details</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
    </button>
</div>
    <div class="modal-body">

        <!--Barcode and Product Name-->
        <div class="row">
            <div class="col">
                <small class="form-text text-muted" id="p-barcodeId">...</small>
                <h5 id="p-productName">...</h5>
            </div>
        </div>

        <hr>

        <div class="row">
            <div class="col">
                <small class="form-text text-muted">Archive Remarks</small>
                <p id="p-archiveRemarks">...</p>
            </div>
        </div>

        <hr>

        <div class="row">
            <div class="col">
                <small class="form-text text-muted">Archived by</small>
                <p id="p-archivedBy">...</p>
            </div>

            
            <div class="col">
                <small class="form-text text-muted">Archived During</small>
                <p id="p-dateArchived">...</p>
            </div>
        </div>
    </div>
    <div class="modal-footer">
    <button type="button" class="btn btn-gray" data-dismiss="modal">Close</button>
    </div>
</div>
</div>
</div>









<!-- Add Category Modal -->
<form action="./inv-form-addCategory.php" method="post">
<div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Add Category</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <label for="addCategory-categoryName">Category Name</label>
        <input type="text" class="form-control" id="addCategory-categoryName" name="categoryName">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-gray" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-success">Add</button>
      </div>
    </div>
  </div>
</div>
</form>


<!-- Edit Category Modal -->
<form action="./inv-form-editCategory.php" method="post">
<div class="modal fade" id="editCategoryModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Edit Category</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="editCategory-categoryId" name="categoryId">
        <input type="hidden" id="editCategory-oldCategoryName" name="oldCategoryName">

        <label for="editCategory-categoryName">Category Name</label>
        <input type="text" class="form-control" id="editCategory-categoryName" name="categoryName" value="">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-gray" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>
</form>

<!-- Disable Category Modal -->
<form action="./inv-form-disableCategory.php" method="post">
<div class="modal fade" id="disableCategoryModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Disable Category</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="disableCategory-categoryId" name="categoryId">
        <center>
            <p>Are you sure you want to disable
            <br>
            <span id="disableCategory-categoryNameText">...</span>?</p>
        </center>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-gray" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-danger">Disable</button>
      </div>
    </div>
  </div>
</div>
</form>









<!-- Add Unit Modal -->
<form action="./inv-form-addUnit.php" method="post">
<div class="modal fade" id="addUnitModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Add Unit</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <label for="addUnit-unitName">Unit Name</label>
        <input type="text" class="form-control" id="addUnit-unitName" name="unitName">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-gray" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-success">Add</button>
      </div>
    </div>
  </div>
</div>
</form>


<!-- Edit Unit Modal -->
<form action="./inv-form-editUnit.php" method="post">
<div class="modal fade" id="editUnitModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Edit Unit</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="editUnit-unitId" name="unitId">
        <input type="hidden" id="editUnit-oldUnitName" name="oldUnitName">

        <label for="editUnit-unitName">Unit Name</label>
        <input type="text" class="form-control" id="editUnit-unitName" name="unitName" value="">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-gray" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>
</form>

<!-- Disable Unit Modal -->
<form action="./inv-form-disableUnit.php" method="post">
<div class="modal fade" id="disableUnitModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Disable Category</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="disableUnit-unitId" name="unitId">
        <center>
            <p>Are you sure you want to disable
            <br>
            <span id="disableUnit-unitNameText">...</span>?</p>
        </center>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-gray" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-danger">Disable</button>
      </div>
    </div>
  </div>
</div>
</form>











<!-- Add Supplier Modal -->
<form action="./inv-form-addSupplier.php" method="post">
<div class="modal fade" id="addSupplierModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Add Supplier</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <label for="addSupplier-supplierName">Supplier Name</label>
        <input type="text" class="form-control" id="addSupplier-supplierName" name="supplierName">
        <br>
        <label for="addSupplier-supplierName">Contact</label>
        <input type="text" class="form-control" id="addSupplier-contact" name="contact">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-gray" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-success">Add</button>
      </div>
    </div>
  </div>
</div>
</form>


<!-- Edit Supplier Modal -->
<form action="./inv-form-editSupplier.php" method="post">
<div class="modal fade" id="editSupplierModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Edit Supplier</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="editSupplier-supplierId" name="supplierId">

        <label for="editSupplier-supplierName">Supplier Name</label>
        <input type="text" class="form-control" id="editSupplier-supplierName" name="supplierName">
        <br>
        <label for="editSupplier-supplierName">Contact</label>
        <input type="text" class="form-control" id="editSupplier-contact" name="contact">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-gray" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </div>
    </div>
  </div>
</div>
</form>


<!-- Disable Unit Modal -->
<form action="./inv-form-disableSupplier.php" method="post">
<div class="modal fade" id="disableSupplierModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Disable Supplier</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="disableSupplier-supplierId" name="supplierId">
        <center>
            <p>Are you sure you want to disable
            <br>
            <span id="disableSupplier-supplierNameText">...</span>?</p>
        </center>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-gray" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-danger">Disable</button>
      </div>
    </div>
  </div>
</div>
</form>











<!-- Add Reason Modal -->
<form action="./inv-form-addArchiveReason.php" method="post">
<div class="modal fade" id="addArchiveReasonModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Add Archive/Return Reason</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <label for="addArchiveReason-reasonName">Reason Name</label>
        <input type="text" class="form-control" id="addArchiveReason-reasonName" name="reason">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-gray" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-success">Add</button>
      </div>
    </div>
  </div>
</div>
</form>


<!-- Edit Reason Modal -->
<form action="./inv-form-editArchiveReason.php" method="post">
<div class="modal fade" id="editArchiveReasonModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Edit Archive/Return Reason</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="editArchiveReason-reasonId" name="reasonId">
        <input type="hidden" id="editArchiveReason-oldReasonName" name="oldReason">

        <label for="editArchiveReason-reasonName">Reason Name</label>
        <input type="text" class="form-control" id="editArchiveReason-reasonName" name="reason" value="">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-gray" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>
</form>

<!-- Disable Category Modal -->
<form action="./inv-form-disableArchiveReason.php" method="post">
<div class="modal fade" id="disableArchiveReasonModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Disable Archive/Return Reason</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="disableArchiveReason-reasonId" name="reasonId">
        <center>
            <p>Are you sure you want to disable
            <br>
            <span id="disableArchiveReason-reasonNameText">...</span>?</p>
        </center>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-gray" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-danger">Disable</button>
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

    $("input").keydown(function(e){
        // Enter was pressed without shift key
        if (e.keyCode == 220)
        {
            // prevent default behavior
            e.preventDefault();
        }
    });
    $("#addCategoryButton").click(function(){
        $("#editCategory").collapse("hide");
        $("#add-categoryName").focus();
    });

    $("#addUnitButton").click(function(){
        $("#editUnit").collapse("hide");
        $("#add-unitName").focus();
    });
    
    $("#addSupplierButton").click(function(){
        $("#editSupplier").collapse("hide");
        $("#add-supplierName").focus();
    });
</script>
</script>
</body>
</html>