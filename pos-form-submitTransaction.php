<?php
    try{
        session_start();
        include("./includes/login-check.php");
        
        //check request method
        if($_SERVER["REQUEST_METHOD"] !== "POST"){
            //$_SESSION["message"] = "what";
            if(isset($_SERVER['HTTP_REFERER'])){
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            else{
                header("Location: javascript://history.go(-1)");
                exit();
            }
        }

        include ("./includes/database.php");
        $db = new Database();

        date_default_timezone_set('Asia/Manila');
        $user = $_SESSION["user"];
        $date = date("Y-m-d");
        $time = date("H:i:s");

        //transaction details
        $total = $_POST["total"];
        $amountTendered = $_POST["amountTendered"];
        $change = $_POST["change"];
        $vatableSales = $_POST["vatableSales"];
        $vatAmount = $_POST["vatAmount"];
        $discount = $_POST["discount"];
        $customerId = $_POST["customerId"] !== "" ? $_POST["customerId"] : "N/A";

        $validation = isset($_POST["discountValidation"]) ? $_POST["discountValidation"] : "";
        $encValidation = md5($validation);

        $result = $db->sql("
            SELECT discount_validation FROM validations WHERE discount_validation = '$encValidation'
        ");

        if($validation !== "" and $result->num_rows == 0 and $discount > 0){
            $_SESSION["message"] = "validation-wrong";
            header("Location: ./pos-page-cashier.php");
            exit();
        }
    
        $db->sql("
            INSERT INTO `transactions`(
                `transaction_id`, `amount_tendered`, `total`, 
                `change_amount`, `vatable_sales`, `vat_amount`, 
                `discount`, `customer_id`, `date_added`, `time_added`, `added_by`
            ) VALUES (
                '','$amountTendered','$total',
                '$change','$vatableSales','$vatAmount',
                '$discount','$customerId','$date','$time',
                '$user'
            )
        ");



        $transactionId = $db->sql("
            SELECT MAX(`transaction_id`) AS `transaction_id` FROM `transactions`
        ")->fetch_assoc()["transaction_id"];

        //transaction items (arrays)
        $productId = $_POST["productId"];
        $productName = $_POST["productName"];
        $bundle = $_POST["bundle"];
        $quantity = $_POST["quantity"];
        $quantityType = $_POST["quantityType"];
        $unitSingle = $_POST["unitSingle"];
        $unitBundle = $_POST["unitBundle"];
        $promo = $_POST["productPromo"];
        $price = $_POST["price"];
        $subtotal = $_POST["subtotal"];

        $len =  count($productId);

        for($x = 0; $x < $len; $x++){
            $productPromo = '';
            $unit = 0;
            $promoFreebie = 0;
            $productFreebie = 0;
            if($quantityType[$x] == 1){
                $promoRes = $db->sql("
                    SELECT * FROM `promo` WHERE `promo_id` = '".$promo[$x]."'
                ");

                if($promo->num_rows > 0){
                    $prm = $promoRes->fetch_assoc();
                    
                    if($prm["promo_type"] === '1'){
                        $buy_quantity = $prm["buy_quantity"];
                        $get_quantity = $prm["get_quantity"];
                        $productFreebie = $prm["product_freebie"];

                        $freebieProductName = $db->sql("SELECT product_name FROM products WHERE product_id = ".$productFreebie)->fetch_assoc()["product_name"];
                        $freebieProductUnit = $db->sql("SELECT um.unit_name FROM products p INNER JOIN unit_measurement um ON p.unit_single = um.unit_id WHERE product_id = ".$productFreebie)->fetch_assoc()["unit_name"];
                        $freebieProductQuantity = $db->sql("SELECT SUM(single_quantity) AS single_quantity FROM batch WHERE product_id = ".$productFreebie." AND `archive_status` = 0 AND `single_quantity` > 0 AND `return` = 0 AND `expiration_status` < 2")->fetch_assoc()["single_quantity"];
                        $promoFreebie = intdiv($quantity[$x], $buy_quantity) * $get_quantity;

                        if($freebieProductQuantity === "0" or $quantity[$x] < $buy_quantity){
                            $productPromo = "None";
                        }
                        elseif($promoFreebie > $freebieProductQuantity){
                            $productPromo = "Free $freebieProductQuantity $freebieProductUnit of $freebieProductName";
                        }
                        else{
                            $productPromo = "Free $promoFreebie $freebieProductUnit of $freebieProductName";
                        }
                        
                        
                    }
                    elseif($prm["promo_type"] === '2'){
                        $productPromo = $prm["percentage"]."% off";
                    }
                    elseif($prm["promo_type"] === '3'){
                        if($quantity[$x] * $price[$x] >= $prm["min_spend"] ){
                            $saved = ($quantity[$x] * $price[$x]) * ($prm["percentage"] / 100);
                            $productPromo = "Saved ₱ ".number_format($saved, 2);
                        }
                        else{
                            $productPromo = "None";
                        }
                        //$productPromo = ($quantity[$x] * $price[$x]) >= $prm["min_spend"] ? $prm["percentage"]."% discount" : "None";
                        
                    }
                    
                    
                }
                else{
                    $productPromo = "None";
                }

                $unit = $unitSingle[$x];
            }
            else{
                $productPromo = 'None';
                $unit = $unitBundle[$x];
            }
            
            

            $db->sql("
                INSERT INTO `transaction_items`(
                    `t_item_id`, `transaction_id`, `product_id`, 
                    `quantity`, `quantity_type`, `bundle`, `unit_id`, `product_promo`, 
                    `price`, `subtotal`
                ) VALUES (
                    '','$transactionId','".$productId[$x]."',
                    '".$quantity[$x]."', '".$quantityType[$x]."', '".$bundle[$x]."', '".$unit."',
                    '$productPromo', '".$price[$x]."','".$subtotal[$x]."'
                )
            ");

            //deduct items from inventory
            $qtyS = 0;
            $qtyB = 0;
            $invQtyS = 0;
            $invQtyB = 0;
            $batch = $db->sql("
                SELECT `batch_id`, `single_quantity`, `bundle_quantity` 
                FROM `batch` 
                WHERE `product_id` = '".$productId[$x]."' AND `archive_status` = 0 AND `single_quantity` > 0 AND `return` = 0 AND `expiration_status` < 2
                ORDER BY `expiration_date` ASC
            ");
            if($quantityType[$x] == 1){ //if quantity type is singles
                $qtyS = (int)$quantity[$x];
                $qtyB = (int)$bundle[$x] > 0 ? intdiv($qtyS, (int)$bundle[$x]) : 0;
            }
            else{
                $qtyS = (int)$bundle[$x] * (int)$quantity[$x];
                $qtyB = (int)$quantity[$x];
            }

            while($row = $batch->fetch_assoc()){
                $id = $row["batch_id"];
                $invQtyS = $row["single_quantity"];
                $invQtyB = $row["bundle_quantity"];
                if($quantityType[$x] == 2 and $invQtyB == 0){
                    continue;
                }
                if($qtyS > $invQtyS){
                    $db->sql("
                        UPDATE `batch` 
                        SET `single_quantity`='0',`bundle_quantity`='0' WHERE `batch_id` = '".$id."'
                    ");
                    $qtyS = $qtyS - $invQtyS;
                    $qtyB = $qtyB - $invQtyB;
                    
                    continue;
                }
                else{
                    $db->sql("
                        UPDATE `batch` 
                        SET `single_quantity`=(`single_quantity` - ".$qtyS."),
                            `bundle_quantity`=(`bundle_quantity` - ".$qtyB.") 
                        WHERE `batch_id` = '".$id."'
                    ");
                    
                    break;
                }
            }



            //deducting the freebies
            
            $freebie = $db->sql("
                SELECT `batch_id`, `single_quantity`, `bundle_quantity`
                FROM `batch` 
                WHERE `product_id` = '".$productFreebie."' AND `archive_status` = 0 AND `single_quantity` > 0 AND `return` = 0 AND `expiration_status` < 2
                ORDER BY `expiration_date` ASC
            ");

            
            $freebieBundle = (int)$db->sql("SELECT bundle FROM products WHERE product_id = ".$productId[$x])->fetch_assoc()["bundle"];

            $freeBnd = $freebieBundle > 0 ? intdiv($promoFreebie, (int)$freebieBundle) : 0;
            while($row = $freebie->fetch_assoc()){
                $id = $row["batch_id"];
                $invQtyS = $row["single_quantity"];
                $invQtyB = $row["bundle_quantity"];

                if($promoFreebie > $invQtyS or ($invQtyS - $promoFreebie) < 0){
                    $db->sql("
                        UPDATE `batch` 
                        SET `single_quantity`='0',`bundle_quantity`='0' WHERE `batch_id` = '".$id."'
                    ");
                    $promoFreebie = $promoFreebie - $invQtyS;
                    $freeBnd = $freeBnd - $invQtyB;
                    continue;
                }
                else{
                    $db->sql("
                        UPDATE `batch` 
                        SET `single_quantity`=(`single_quantity` - ".$promoFreebie."),
                            `bundle_quantity`=(`bundle_quantity` - ".$freeBnd.") 
                        WHERE `batch_id` = '".$id."'
                    ");
                    
                    break;
                }
            }
        }

        include("./includes/inventory-functions.php");
        $inv = new Inventory($db);

        $inv->updateQuantity("all");
        $inv->updateStockStatus();
        $inv->updateExpirationStatus();

        //add logs
        $db->sql("
            INSERT INTO `user_logs`(
                `log_id`, `user_id`, `activity_type`, `activity_description`, 
                `date_added`, `time_added`
            ) 
            VALUES (
                '','$user','pos','successfully submitted transaction (TR-$transactionId)','$date','$time'
            )
        ");

        $_SESSION["message"] = "transaction-success";
        header("Location: ./print-page-printReceipt.php?transactionId=$transactionId&loc=cashier");
    }
    catch(Exception $e){
        $_SESSION["message"] = "error";
        header("Location: ./pos-page-cashier.php");
    }
    
?>
