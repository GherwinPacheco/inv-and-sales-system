<?php
    include("../includes/database.php");
    $db = new Database(); 

    $search = $_POST["search"];
    $result = $db->sql("
        SELECT `barcode_id`, `product_name`, `properties` 
        FROM `products` 
        WHERE `barcode_id` = '$search' OR CONCAT_WS(' ', `product_name`, `properties`) LIKE '%$search%' AND `archive_status` = '0'
        ORDER BY `product_name`
    ");

    $content = "";
    if($result->num_rows > 0){
        $items = "";
        $rowCount = $result->num_rows;
        $count = 1;
        while($row = $result->fetch_assoc()){
            $items .= '
                <a href="./inv-page-productList.php?search='.$row["product_name"].'" class="list-group-item list-group-item-action border-right-0 border-left-0 border-top-0 '.($count == $rowCount ? "border-bottom-0" : "").'">
                    <span class="text-muted mr-3 border-right" style="font-size: 12px">'.$row["barcode_id"].'</span>

                    <span>'.$row["product_name"].' '.$row["properties"].'</span>
                </a>
            ';
            $count++;
        }
        
        $content .= '
            <div class="list-group">'.$items.'</div>
        ';
    }
    else{
        $content .= '
            <div class="text-center m-3">
                <br>
                <img src="./assets/no_results.svg" style="width: 10%; height: 10%">
                <br>
                <br>
                No Results
            </div>
        ';
    }
    echo '
        <div class="bg-white rouded p-1 w-100 border shadow-sm" style="position: absolute">
            '.$content.'           
        </div>
    ';
?>