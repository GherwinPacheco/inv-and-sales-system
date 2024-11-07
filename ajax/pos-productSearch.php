<?php
    include("../includes/database.php");
    $db = new Database();

    $search = $_POST["search"];
    try{
        $products = $db->sql("
        SELECT 
            `products`.`product_id`, 
            `products`.`barcode_id`, 
            `products`.`product_name`, 
            `products`.`properties`, 
            `products`.`category_id`, 
            `category`.`category_name`, 
            `products`.`bundle`, 
            (SELECT SUM(single_quantity) FROM batch WHERE product_id = `products`.`product_id` AND expiration_status < 2 AND archive_status = 0 AND `return` = 0) AS `single_quantity`, 
            (SELECT SUM(bundle_quantity) FROM batch WHERE product_id = `products`.`product_id` AND expiration_status < 2 AND archive_status = 0 AND `return` = 0) AS `bundle_quantity`, 
            `products`.`unit_single`, 
            `products`.`unit_bundle`, 
            (SELECT `unit_name` 
            FROM `unit_measurement` 
            WHERE `products`.`unit_single` = `unit_measurement`.`unit_id`) as `unit_single_name`,
            (SELECT `unit_name` 
            FROM `unit_measurement` 
            WHERE `products`.`unit_bundle` = `unit_measurement`.`unit_id`) as `unit_bundle_name`,
            IFNULL((
                SELECT `batch`.`price`
                FROM `batch` 
                WHERE 
                    `batch`.`product_id` = `products`.`product_id` AND 
                    `batch`.`single_quantity` > 0 AND 
                    `batch`.`archive_status` = 0 AND 
                    `batch`.`return` = 0
                HAVING    
                    MIN(`batch`.`date_added`)
            ), `products`.`price`) AS `price`,
            `products`.`minimum_stock`,
            `products`.`stock_status`, 
            (SELECT IFNULL(MAX(`expiration_status`), 0)
            FROM `batch` 
            WHERE 
                `batch`.`product_id` = `products`.`product_id` AND 
                `batch`.`single_quantity` > 0 AND 
                `batch`.`archive_status` = 0 AND `batch`.`return` = 0
            ) AS `expiration_status`,
            `products`.`product_description`
        FROM `products` 
        INNER JOIN `category` ON `products`.`category_id` = `category`.`category_id`
        WHERE $search AND `products`.`archive_status` = 0
        ORDER BY `products`.`product_name` ASC
    ");
    
    $productListRow = 1;
    $table = "";
    $script = "";
    if($products->num_rows > 0 ){
        $first = true;
        while($row = $products->fetch_assoc()){
            $id = $row["product_id"];

            $promo = $db->sql("SELECT * FROM `promo` WHERE `product_id` = '$id' AND `promo_status` = 1");
            $promo_badge = '';
            $promoValues = '';

            $subtotal = $row["price"];
            if($promo->num_rows == 1){
                $promo = $promo->fetch_assoc();

                $promo_id = $promo["promo_id"];
                $promo_type = $promo["promo_type"];
                $buy_quantity = $promo["buy_quantity"];
                $get_quantity = $promo["get_quantity"];
                $product_freebie = $promo["product_freebie"];
                $product_freebie_name = $product_freebie !== "0" ? $db->sql("SELECT product_name FROM products WHERE product_id = ".$product_freebie)->fetch_assoc()["product_name"] : "";
                $min_spend = $promo["min_spend"];
                $percentage = $promo["percentage"];

                $promoValues .= '
                    <input type="hidden" id="productPromo-'.$id.'" name="productPromo[]" value="'.$promo_id.'">
                    <input type="hidden" id="promoType-'.$id.'" name="promoType[]" value="'.$promo_type.'">
                    <input type="hidden" id="buyQuantity-'.$id.'" name="buyQuantity[]" value="'.$buy_quantity.'">
                    <input type="hidden" id="getQuantity-'.$id.'" name="getQuantity[]" value="'.$get_quantity.'">
                    <input type="hidden" id="productFreebie-'.$id.'" name="productFreebie[]" value="'.$product_freebie.'">
                    <input type="hidden" id="minSpend-'.$id.'" name="minSpend[]" value="'.$min_spend.'">
                    <input type="hidden" id="promoPercentage-'.$id.'" name="promoPercentage[]" value="'.$percentage.'">
                ';

                $promoTitle = "";
                $promoMessage = "";
                if($promo_type == 1){
                    $promoTitle = "Buy $buy_quantity Get $get_quantity";
                    $promoMessage = "Promo freebies will be deducted to $product_freebie_name";
                }
                elseif($promo_type == 2){
                    $promoTitle = "$percentage% Off Discount";
                    $promoMessage = "Product discount will be automatically applied to the subtotal";
                    $subtotal = $subtotal - ( ($percentage / 100) * $subtotal );
                }
                elseif($promo_type == 3){
                    $promoTitle = "Save $percentage% - Min Spend ₱ ". number_format($min_spend, 2);
                    $promoMessage = "Discount will be applied when subtotal reached ₱ ". number_format($min_spend, 2);
                }
                

                $promo_badge = '
                    <span class="badge badge-pill btn-red promoBadge" id="promoBadge-'.$id.'"
                        data-toggle="popover" data-trigger="hover" title="'.$promoTitle.'"
                        data-content="'.$promoMessage.'" 
                        style="cursor: pointer">
                        <i class="fa-solid fa-tag"></i>&emsp;Promo
                    </span>
                ';
                
            }
            else{
                $promoValues .= '
                    <input type="hidden" id="productPromo-'.$id.'" name="productPromo[]" value="0">
                ';
            }
            
            
            $warning = false;
            $warningSign = "";
            if($row["stock_status"] == "3" or $row["single_quantity"] <= 0 and $row["bundle_quantity"] <= 0){
                $warningSign = '
                    <i class="fa-solid fa-triangle-exclamation text-dark" data-toggle="tooltip" data-placement="top" title="The product is out of stock"></i>
                ';
                $warning = true;
            }
            
            

            $warningButton = $warning ? '
                <form action="./inv-page-productList.php" method="get">
                    <input type="hidden" name="search" value="'.$row["barcode_id"].'">
                    <input type="hidden" name="loc" value="pos-page-cashier">
                    <button type="submit" class="btn btn-white m-0 p-0">
                        '.$warningSign.'
                    </button>
                </form>
            ' : '';

            $addButton = ($row["stock_status"] == "3" or $row["single_quantity"] <= 0 and $row["bundle_quantity"] <= 0) ? 
            '<button type="button" class="btn text-primary" style="opacity: 0" disabled><i class="fa-solid fa-plus"></i></button>' : 
            '<button type="button" class="btn btn-white add-button text-primary" id="add-product-'.$id.'"><i class="fa-solid fa-plus"></i></button>';

            
            //output the product list
            $table .= '
                <tr class="product-row outline-none '.($first ? "selected" : "").'" id="product-row-'.$id.'" tabindex="1">
                    
                    <td style="vertical-align: middle"><b>'.$productListRow.'</b></td>
                    <td style="vertical-align: middle">'.$row["product_name"].' '.$row["properties"].'</td>
                    <td style="text-align: left; vertical-align: middle">'.$row["category_name"].'</td>
                    <td style="text-align: right; vertical-align: middle">₱ '.number_format($row["price"],2).'</td>
                    <td style="vertical-align: middle">
                        <div class="d-flex flex-row-reverse align-items-center">
                            '.$addButton.'
                            &emsp;
                            '.$warningButton.'
                            <input type="hidden" class="barcodeId" value="'.$row["barcode_id"].'">
                        </div>
                    </td>
                </tr>
            ';
            $first = false;
            $productListRow++;

            //the scripts for selected items
            $script .= '
                <script>
                    if($("#item-'.$id.'").length){
                        $("#add-product-'.$id.'").html(`<i class="fa-solid fa-check-double"></i>`);
                        submitToggle();
                    }
                    $("#add-product-'.$id.'").click(function(){
                        if(! $("#item-'.$id.'").length){
                            $(this).html(`<i class="fa-solid fa-check-double"></i>`);
                            $("#selectedItems").append(`
                                <tr class="selected-row outline-none" id="item-'.$id.'" tabindex="1">
                                    <td style="text-align: left">
                                        <input type="hidden" id="productId-'.$id.'" name="productId[]" value="'.$id.'">
                                        <input type="hidden" id="productName-'.$id.'" name="productName[]" value="'.$row["product_name"].' '.$row["properties"].'">
                                        <span id="productNameText-'.$id.'">'.$row["product_name"].' '.$row["properties"].'&emsp;'.$promo_badge.'</span>
                                        
                                        <!--PROMO VALUES-->
                                        '.$promoValues.'
                                    </td>
                                    <td style="text-align: center">
                                        <div class="input-group">
                                            
                                            <input type="hidden" id="unitSingle-'.$id.'" name="unitSingle[]" value="'.$row["unit_single"].'">
                                            <input type="hidden" id="unitBudle-'.$id.'" name="unitBundle[]" value="'.$row["unit_bundle"].'">

                                            <input type="hidden" id="bundle-'.$id.'" name="bundle[]" value="'.$row["bundle"].'">
                                            <input type="hidden" id="singleQuantity-'.$id.'" name="singleQuantity[]" value="'.$row["single_quantity"].'">
                                            <input type="hidden" id="bundleQuantity-'.$id.'" name="bundleQuantity[]" value="'.$row["bundle_quantity"].'">

                                            <input type="number" class="form-control quantity" id="quantity-'.$id.'" name="quantity[]" value="1" min="1" max="'.$row["single_quantity"].'" style="width: 20px" oninput="updateItemSubtotal(\''.$id.'\')" tabindex="1" required>
                                            <div class="input-group-append">
                                                <select class="p-1 bordered quantityType" id="quantityType-'.$id.'" name="quantityType[]" 
                                                    onchange="
                                                        updateItemPrice(\''.$id.'\');
                                                        setMaxQuantity(\''.$id.'\');
                                                    ;" required>
                                                    <option value="1">'.$row["unit_single_name"].'</option>
                                                    '.($row["bundle"] > 0 ? '<option value="2" '.($row["bundle_quantity"] == 0 ? "disabled" : "").'>'.$row["unit_bundle_name"].'</option>' : '').'
                                                </select>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="text-align: right">
                                        <input type="hidden" id="originalPrice-'.$id.'" value="'.$row["price"].'">
                                        <input type="hidden" id="price-'.$id.'" name="price[]" value="'.$row["price"].'">
                                        <span id="priceText-'.$id.'">₱ '.number_format($row["price"], 2).'</span>
                                    </td>
                                    <td style="text-align: right">
                                        <input type="hidden" class="subtotal" id="subtotal-'.$id.'" name="subtotal[]" value="'.$subtotal.'">
                                        <span id="subtotalText-'.$id.'">₱ '.number_format($subtotal, 2).'</span>
                                    </td>
                                    <td style="text-align: denter"><button type="button" class="btn btn-red remove-item" id="remove-item-'.$id.'" onclick="removeList(\''.$id.'\', this);updateTransactionDetails();submitToggle();"><i class="fa-solid fa-xmark"></i></button></td>
                                </tr>
                            `);
                            $("#productSearch").val("");
                            productSearchAjax("1");
                            updateTransactionDetails();
                        }
                        else{
                            $("#quantity-'.$id.'").val(parseInt($("#quantity-'.$id.'").val()) + 1);
                            updateItemSubtotal(\''.$id.'\')
                            $("#productSearch").val("");
                            productSearchAjax("1");
                            updateTransactionDetails();
                        }
                        
                    });
                </script>
            ';
            
            
        }

        
    }
    else{
        //output if there are no results
        $table .= '
            <tr>
                <td colspan="5" class="text-center">
                    <br>
                    <img src="./assets/no_results.svg" style="width: 15%; height: 15%">
                    <br>
                    <br>
                    No Results
                </td>
            </tr>
        ';
    }
}
catch(Exception $e){
    $table .= '
        <tr>
            <td colspan="5" class="text-center">
                <br>
                <img src="./assets/no_results.svg" style="width: 15%; height: 15%">
                <br>
                <br>
                No Results
            </td>
        </tr>
    ';
}

echo $table;
echo $script;
?>