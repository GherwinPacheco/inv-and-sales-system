<?php
    include("../includes/database.php");
    $db = new Database(); 

    include ("../includes/inventory-functions.php");
    $inv = new Inventory($db);
    
    $productid = $_POST["product_id"];
    
    $result = $db->sql("
    SELECT 
        `products`.`barcode_id`, `products`.`product_name`, `products`.`properties`, `batch`.`batch_id`, `batch`.`product_id`, `products`.`bundle`, `batch`.`single_quantity`, `batch`.`bundle_quantity`, 
        `products`.`bundle`, `products`.`unit_single`, `products`.`unit_bundle`, `batch`.`price`, `batch`.`expiration_date`, 
        `products`.`expirable`, `batch`.`expiration_status`, `batch`.`supplier_id`, `batch`.`date_added`, `batch`.`time_added`, 
        `batch`.`date_modified`, `batch`.`time_modified`, `batch`.`added_by`, `batch`.`modified_by`,
        `supplier`.`supplier_name`
    FROM `batch`
        INNER JOIN products ON `batch`.product_id = `products`.product_id
        INNER JOIN `supplier` ON `batch`.`supplier_id` = `supplier`.`supplier_id`
    WHERE `batch`.`product_id` = '$productid' AND `batch`.`single_quantity` > 0 AND `batch`.`archive_status` = 0 AND `batch`.`return` = 0
    ORDER BY `batch`.`expiration_date` ASC;
    ");

    $table = '';
    $table_body = '';

    if ($result->num_rows > 0) {
        // output data of each row
        $rowcount = 1;
        while($row = $result->fetch_assoc()) {

            $id = $row["batch_id"];

            //get the unit of measurements for singles and bundles
            $single_qty = $inv->getQuantityUnit($inv->getUnit($row["unit_single"]), $row["single_quantity"]);
            $bundle_qty = $inv->getQuantityUnit($inv->getUnit($row["unit_bundle"]), $row["bundle_quantity"]);
                                    
            $expirationDate = $row["expirable"] == 1 ? $row["expiration_date"] : "N/A";
            

            //Determine expiration status
            $expiration_status = $inv->setExpirationStatus($row["expiration_status"]);
            $expiration_badge = $inv->setExpirationStatusBadge($row["expiration_status"], $row["expiration_date"]);

            $table_body .= '
                <tr>
                    <th style="text-align: right">'.$rowcount.'</th>
                    <td style="text-align: left">'.$row["batch_id"].'</td>
                    <td style="text-align: right">'.$single_qty.'</td>
                    <td style="text-align: right">'.$bundle_qty.'</td>
                    <td style="text-align: right">₱ '.number_format($row["price"],2).'</td>
                    <td style="text-align: center">'.$expirationDate.'</td>
                    <td style="text-align: left">'.$expiration_badge.'</td>
                    <td style="text-align: right">  
                        <button type="button" class="btn btn-white dropdown" type="button" id="actions-button-batch-'.$id.'" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa-solid fa-ellipsis-vertical"></i>
                        </button>
                        <div class="dropdown-menu" aria-labelledby="actions-button-batch">
                            <a id="view-batch-'.$id.'" class="dropdown-item" data-toggle="modal" data-target="#viewBatchModal"><i class="fa-solid fa-circle-info"></i>&emsp;View Batch Details</a>
                            <a id="edit-batch-'.$id.'" class="dropdown-item" data-toggle="modal" data-target="#editBatchModal"><i class="fa-solid fa-pen"></i>&emsp;Edit Batch Details</a>
                            <a id="archive-batch-'.$id.'" class="dropdown-item" data-toggle="modal" data-target="#archiveBatchModal"><i class="fa-solid fa-box-archive"></i>&emsp;Archive Batch</a>
                            <a id="return-batch-'.$id.'" class="dropdown-item" data-toggle="modal" data-target="#returnBatchModal"><i class="fa-solid fa-truck-ramp-box"></i>&emsp;Add to Return List</a>
                        </div>
                    </td>
                </tr>

                <script>
                    $("#view-batch-'.$id.'").click(function(){
                        $("#viewBatch-batchId").html("Batch-'.$row["batch_id"].' Details");
                        $("#viewBatch-barcodeId").html("'.$row["barcode_id"].'");
                        $("#viewBatch-productName").html("'.$row["product_name"].' '.$row["properties"].'");
                        $("#viewBatch-price").html("'.number_format($row["price"],2).'");
                        $("#viewBatch-supplier").html("'.$row["supplier_name"].'");
                        $("#viewBatch-quantitySingle").html("'.$single_qty.'");
                        $("#viewBatch-quantityBundle").html("'.$bundle_qty.'");
                        $("#viewBatch-expirationDate").html("'.$expirationDate.'");
                        $("#viewBatch-expirationStatus").html("'.$expiration_status.'");
                        $("#viewBatch-addedBy").html("'.$inv->getUsername($row["added_by"]).'");
                        $("#viewBatch-modifiedBy").html("'.$inv->getUsername($row["modified_by"]).'");
                        $("#viewBatch-addedDuring").html(`
                            '.date("M d, Y", strtotime($row["date_added"])).'<br>'.date("h:i a", strtotime($row["time_added"])).'
                        `);
                        $("#viewBatch-modifiedDuring").html( ("'.$row["date_modified"].'" == "0000-00-00") && ("'.$row["time_modified"].'" == "00:00:00") ? "N/A" : "'.date("M d, Y", strtotime($row["date_modified"])).'<br>'.date("h:i a", strtotime($row["time_modified"])).'");
                    });

                    $("#edit-batch-'.$id.'").click(function(){
                        $("#editBatch-batchId").val("'.$row["batch_id"].'");
                        $("#editBatch-productId").val("'.$row["product_id"].'");

                        $("#editBatch-bundlePieces").val("'.$row["bundle"].'");
                        $("#editBatch-expirable").val("'.$row["expirable"].'");

                        $("#editBatch-barcodeIdText").html("'.$row["barcode_id"].'");
                        $("#editBatch-productNameText").html("'.$row["product_name"].'");
                        $("#editBatch-batchIdText").html("BATCH-<b>'.$row["batch_id"].'</b>");

                        $("#editBatch-price").val("'.$row["price"].'");
                        $("#editBatch-quantitySingle").val("'.$row["single_quantity"].'");
                        $("#editBatch-supplier").val("'.$row["supplier_id"].'");

                        if("'.$row["bundle"].'" == 0){
                            $("#editBatch-quantityBundle").attr("readonly", true);
                            $("#editBatch-bundlePiecesText").html("");
                        }
                        else{
                            $("#editBatch-quantityBundle").attr("readonly", false);
                            $("#editBatch-bundlePiecesText").html("<b>'.$row["bundle"].' '.$inv->getUnit($row["unit_single"]).'</b> per bundle");
                            $("#editBatch-quantityBundle").val("'.$row["bundle_quantity"].'");
                        }

                        if("'.$row["expirable"].'" == 0){
                            $("#editBatch-expirationDate").attr("readonly", true);
                        }
                        else{
                            $("#editBatch-expirationDate").attr("readonly", false);
                            $("#editBatch-expirationDate").val("'.$row["expiration_date"].'");
                        }
                    });


                    $("#archive-batch-'.$id.'").click(function(){
                        $("#archiveBatch-batchText").html("BATCH-<b>'.$row["batch_id"].'</b> of <br><b>'.$row["product_name"].'</b>");
                        $("#archiveBatch-batchId").val("'.$row["batch_id"].'");
                        $("#archiveBatch-productId").val("'.$row["product_id"].'");
                    });

                    $("#return-batch-'.$id.'").click(function(){
                        $("#returnBatch-batchText").html("BATCH-<b>'.$row["batch_id"].'</b> of <br><b>'.$row["product_name"].'</b>");
                        $("#returnBatch-batchId").val("'.$row["batch_id"].'");
                        $("#returnBatch-productId").val("'.$row["product_id"].'");
                    });
                </script>
                
            ';

            $rowcount++;
        }
    } 
    else 
    {
        $table_body = "";
    }

    if($table_body !== ""){
        $table = ' 
            <!--AJAX result here-->
            <div class="d-flex flex-row-reverse">

                <table class="table hidden-table" id="hidden-table-'.$productid.'">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>BATCH ID</th>
                            <th>SINGLES</th>
                            <th>BUNDLES</th>
                            <th>PRICE</th>
                            <th>EXPIRATION DATE</th>
                            <th>EXPIRATION STATUS</th>
                            <th></th>
                        </tr>
                        
                    </thead>
                    <tbody>
                        '.$table_body.'
                    </tbody>
                </table>
                <h1 class="indent-logo"><i class="bi bi-arrow-return-right"></i></h1>
            </div>
        ';
    }
    
    echo $table;
?>