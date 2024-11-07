<?php
    class Inventory{
        private $db;
        private $condition = "1";

        
        function __construct($database) {
            $this->db = $database;
        }


        function getQuantityUnit($unit, $qty){
            $vowels = array("a", "e", "i", "o", "u");
            $val = "";
            if($unit !== "N/A"){
                foreach ($vowels as $value) {
                    if($unit[-1] === $value and $qty > 1){
                        $unit .= "s";
                        break;
                    }
                }
                $val = $qty ." ". $unit;
            }
            else{
                $val = "N/A";
            }
            return $val;
        }


        function getUnit($unit){
            $val = "";
            if($unit){
                $val = $this->db->sql("
                    SELECT `unit_name` FROM `unit_measurement` WHERE `unit_id` = '".$unit."'
                ")->fetch_assoc()["unit_name"];
            }
            else{
                $val = "N/A";
            }
            return $val;
        }


        function getBatchCount($productId){
            return $this->db->sql("
                SELECT COUNT(`batch_id`) as `batch_count`
                FROM `batch`
                WHERE 
                    `product_id` = '".$productId."' AND `single_quantity` > 0 AND `archive_status` = 0 AND `return` = 0
            ")->fetch_assoc()["batch_count"];
            
        }


        function getExpirationStatus($productId){
            return $this->db->sql("
                SELECT MAX(`expiration_status`) as `expiration_status`
                FROM `batch`
                WHERE 
                    `product_id` = '".$productId."' AND `single_quantity` > 0 AND `archive_status` = 0 AND `return` = 0
            ")->fetch_assoc()["expiration_status"];
        }


        function setStockStatus($status){
            $stock_status = "";
            if($status == 1){
                $stock_status = "Available";
            }
            elseif($status == 2){
                $stock_status = "Low Stock";
            }
            elseif($status == 3){
                $stock_status = "Out of Stock";
            }
            return $stock_status;
        }


        function setStockStatusBadge($status, $minimum, $quantity){
            $stock_status = "";
            $bg = "";
            $color = "";
            $bordered = "";

            $shades = array("#555555", "#666666", "#777777", "#999999");
            if($status == 1){
                $bg = "white";
                $color = "black";
                $bordered = "bordered";
                $stock_status = "Available";
            }
            elseif($status == 2){
                $minimumStock = $minimum / 4;
                for($x = 0; $x < 4; $x++){
                    if( $quantity <= (int)($minimumStock * ($x + 1)) ){
                        $bg = $shades[$x];
                        break;
                    }
                }
                $color = "white";
                $stock_status = "Low Stock";
            }
            elseif($status == 3){
                $bg = "#111111";
                $color = "white";
                $stock_status = "Out of Stock";
            }

            $badge = '<span class="badge badge-pill '.$bordered.' p-1 pl-2 pr-2" style="background-color: '.$bg.'; color: '.$color.'">'.$stock_status.'</span>';

            return $badge;
        }


        function setExpirationStatus($status){
            $expiration_status = "";

            if($status == 1){
                $expiration_status = "Good";
            }
            elseif($status == 2){
                $expiration_status = "Nearly Expired";
            }
            elseif($status == 3){
                $expiration_status = "Expired";
            }
            else{
                $expiration_status = "N/A";
            }
            return $expiration_status;
        }

        function setExpirationStatusBadge($status, $expiration_date){
            $expiration_status = "";
            $bg = "";
            $color = "black";

            $shades = array("#FD6104", "#FD9A01", "#FEF001");

            date_default_timezone_set('Asia/Manila');
            $dateToday = date("Y-m-d");

            if($status == 1){
                $bg = "#4eff71";
                $expiration_status = "Good";
            }
            elseif($status == 2){
                for($x = 3; $x > 0; $x--){
                    if( $dateToday >= date("Y-m-d", strtotime("-$x months", strtotime($expiration_date))) and $dateToday <= date("Y-m-d", strtotime("-".($x-1)." months", strtotime($expiration_date))) ){
                        $bg = $shades[$x-1];
                        break;
                    }
                }
                $expiration_status = "Nearly Expired";
            }
            elseif($status == 3){
                $bg = "#F00505";
                $color = "white";
                $expiration_status = "Expired";
            }
            else{
                $expiration_status = "N/A";
            }

            $badge = $expiration_status !== "N/A" ? '<span class="badge badge-pill p-1 pl-2 pr-2" style="background-color: '.$bg.'; color: '.$color.'">'.$expiration_status.'</span>' :
                                            "N/A";
            return $badge;
        }

        function getUsername($id){
            $val = "";
            if($id){
                $val = $this->db->sql("
                SELECT `user_name` FROM `accounts` WHERE `user_id` = '$id'
                ")->fetch_assoc()["user_name"];
            }
            else{
                $val ="N/A"; 
            }
            return $val;
        }

        function setButtonColor($expirationStatus){

            $btn = array(
                "bgcolor"=>"",
                "numcolor"=>""
            );

            if($expirationStatus == 0){
                $btn["bgcolor"] = "btn-white";
                $btn["numcolor"] = "dark";
            }
            elseif($expirationStatus == 1){
                //green if all batch is good
                $btn["bgcolor"] = "btn-green";
                $btn["numcolor"] = "success";
            }
            elseif($expirationStatus == 2){
                //yellow if there is a nearly expired batch
                $btn["bgcolor"] = "btn-yellow";
                $btn["numcolor"] = "warning";
            }
            elseif($expirationStatus == 3){
                //red if there is an expired batch
                $btn["bgcolor"] = "btn-red";
                $btn["numcolor"] = "danger";
            }
            
            return $btn;
        }

        function filterBadge($id, $filter){
            $data = "";
            $color = "";
            $title = "";
            if($filter == "category"){
                $data = '<i class="fa-solid fa-layer-group"></i>&emsp;' . $this->db->sql("SELECT `category_name` FROM `category` WHERE `category_id` = $id")->fetch_assoc()["category_name"];
                $color = "bg-blue";
                $title = "Category";
            }
            elseif($filter == "stock"){
                $data = '<i class="fa-solid fa-layer-group"></i>&emsp;' . $this->setStockStatus($id);
                if($id == 1){
                    $color = "badge-white bordered";
                }
                elseif($id == 2){
                    $color = "badge-secondary";
                }
                elseif($id == 3){
                    $color = "badge-dark";
                }
                $title = "Stock Status";
            }
            elseif($filter == "expiration"){
                $data = '<i class="fa-solid fa-clock"></i>&emsp;' . $this->setExpirationStatus($id);
                if($id == 1){
                    $color = "bg-green";
                }
                elseif($id == 2){
                    $color = "bg-yellow";
                }
                elseif($id == 3){
                    $color = "bg-red";
                }
                $title = "Expiration Status";
            }

            $badge = '
                <span class="badge filter-badge '.$color.' pr-2 pl-2 pt-1 pb-1 m-2" data-toggle="tooltip" data-placement="bottom" title="'.$title.'">
                    '.$data.'
                </span>
            ';
            return $badge;
           
        }

        function updateQuantity($id){
            $condition = "";
            $productId = "";
            if($id == "all"){
                //update quantity of ALL products
                $condition = "1";
                $productId = "`products`.`product_id`";
            } 
            else{
                //update quantity of specific product only
                $condition = "`product_id` = '$id'";
                $productId = "'$id'";
            }
            $this->db->sql("
                UPDATE `products` 
                SET 
                    `single_quantity` = (
                        SELECT SUM(`single_quantity`) 
                        FROM `batch`
                        WHERE 
                            `batch`.`product_id` = $productId AND 
                            `batch`.`single_quantity` > 0 AND 
                            `batch`.`archive_status` = 0 AND 
                            `batch`.`return` = 0
                        ),
                    `bundle_quantity` = (
                        SELECT SUM(`bundle_quantity`) 
                        FROM `batch`
                        WHERE 
                            `batch`.`product_id` = $productId AND 
                            `batch`.`single_quantity` > 0 AND 
                            `batch`.`archive_status` = 0 AND 
                            `batch`.`return` = 0
                        )
                WHERE $condition
            ");

            
        }

        function updateBundleQuantity($id){
            $condition = "";
            $batchId = "";
            if($id == "all"){
                //update quantity of ALL products
                $condition = "1";
            } 
            else{
                //update quantity of specific product only
                $condition = "`batch`.`product_id` = '$id'";
            }
            $this->db->sql("
                UPDATE `batch` 
                INNER JOIN `products` ON `batch`.`product_id` = `products`.`product_id`
                SET `batch`.`bundle_quantity` = IFNULL(
                    `batch`.`single_quantity` DIV `products`.`bundle`, 
                    0)
                WHERE $condition
            ");
        }

        function updateStockStatus(){
            //update stock status of products
            $this->db->sql("
                UPDATE `products` SET `stock_status` = '1' WHERE `single_quantity` > `minimum_stock`;
            ");
            $this->db->sql("
                UPDATE `products` SET `stock_status` = '2' WHERE `single_quantity` <= `minimum_stock`;
            ");
            $this->db->sql("
                UPDATE `products` SET `stock_status` = '3' WHERE `single_quantity` = '0';
            ");

            /*
            UPDATE `products` 
            SET `stock_status`='1' 
            WHERE (
                SELECT SUM(`batch`.`single_quantity`) 
                FROM `batch` 
                WHERE `batch`.`product_id` = `products`.`product_id` AND `batch`.`archive_status` = '0' AND `batch`.`return` = '0') > `minimum_stock`;
            */
        }

        function updateExpirationStatus(){
            //update expiration status
                $this->db->sql("
                    UPDATE `batch`
                        INNER JOIN `products` ON `batch`.`product_id` = `products`.`product_id`
                    SET `batch`.`expiration_status` = '0'
                    WHERE `batch`.`archive_status` = '0' AND `products`.`expirable` = '0';
                ");
                $this->db->sql("
                    UPDATE `batch`
                        INNER JOIN `products` ON `batch`.`product_id` = `products`.`product_id`
                    SET `batch`.`expiration_status` = '1'
                    WHERE `batch`.`archive_status` = '0' AND `products`.`expirable` = '1' AND CURDATE() < (DATE_ADD(`batch`.`expiration_date`, INTERVAL -3 MONTH));
                ");
                $this->db->sql("
                    UPDATE `batch`
                        INNER JOIN `products` ON `batch`.`product_id` = `products`.`product_id`
                    SET `batch`.`expiration_status` = '2'
                    WHERE `batch`.`archive_status` = '0' AND `products`.`expirable` = '1' AND CURDATE() >= (DATE_ADD(`batch`.`expiration_date`, INTERVAL -3 MONTH)) AND CURDATE() < `expiration_date`;
                ");
                $this->db->sql("
                    UPDATE `batch`
                        INNER JOIN `products` ON `batch`.`product_id` = `products`.`product_id`
                    SET `batch`.`expiration_status` = '3'
                    WHERE `batch`.`archive_status` = '0' AND `products`.`expirable` = '1' AND CURDATE() >= `batch`.`expiration_date`;
                ");
                
        }
        
    }
?>