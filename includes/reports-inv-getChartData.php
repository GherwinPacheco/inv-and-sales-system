<?php
    $order = "1";
    $condition = "";
    $graphHeader = "";
    $limit = "LIMIT 5";
    if(isset($noLimit) and $noLimit){
        $limit = "";
    }

    if($salesRankType == "fast"){
        $order = "DESC";
        $graphHeader .= (isset($noLimit) and $noLimit) ? "Average Product Sold" : "Fast Moving Products";
    }
    elseif($salesRankType == "slow"){
        $order = "ASC";
        $graphHeader .= (isset($noLimit) and $noLimit) ? "Average Product Sold" : "Slow Moving Products";
    }

    $start = "";
    $end = "";
    if($salesRankTime == "last 7 days"){
        $start = date("Y-m-d", strtotime("-7 days"));
        $end = date("Y-m-d");
        $graphHeader .= " of Last 7 Days";
    }
    elseif($salesRankTime == "this month"){
        $start = date("Y-m-d", strtotime(date("Y")."-".date("m")."-01"));
        $end = date("Y-m-d");
        $graphHeader .= " This Month";
    }
    elseif($salesRankTime == "last month"){
        $start = date("Y-m-d", strtotime(date("Y")."-".(date("m") - 1)."-1"));
        $end = date("Y-m-t", strtotime("-1 month"));
        $graphHeader .= " Last Month";
    }
    elseif($salesRankTime == "this year"){
        $start = date("Y-m-d", strtotime(date("Y")."-01-01"));
        $end = date("Y-m-d");
        $graphHeader .= " This Year";
    }
    elseif($salesRankTime == "last year"){
        $start = date("Y-m-d", strtotime((date("Y") - 1)."-01-01"));
        $end = date("Y-m-d", strtotime((date("Y") - 1)."-12-31"));
        $graphHeader .= " Last Year";
    }

    $salesRank = $db->sql("
        SELECT `products`.`product_name`, `products`.`properties`, 
            SUM(IF(`transaction_items`.`quantity_type` = 1, `transaction_items`.`quantity`, (`transaction_items`.`quantity` * `products`.`bundle`))) as `product_sold`,
            (SUM(IF(`transaction_items`.`quantity_type` = 1, `transaction_items`.`quantity`, (`transaction_items`.`quantity` * `products`.`bundle`)) ) / DATEDIFF('$end', '$start')) AS `average_per_day`
        FROM `transaction_items`
            LEFT JOIN `products` ON `transaction_items`.`product_id` = `products`.`product_id`
            LEFT JOIN `transactions` ON `transaction_items`.`transaction_id` = `transactions`.`transaction_id`
        WHERE `transactions`.`date_added` BETWEEN '$start' AND '$end'
        GROUP BY `products`.`product_name` ASC
        ORDER BY `average_per_day` $order
        $limit;
    ");

    $count = 0;
    if($salesRank->num_rows > 0){
        while($row = $salesRank->fetch_assoc()){
            if(round($row["average_per_day"]) > 0){
                $salesRankData .= round($row["average_per_day"]);
                $salesRankLabel .= "'".$row["product_name"].' '.$row["properties"]."'";
                if($count < 10){
                    $salesRankData .= ", ";
                    $salesRankLabel .= ", ";
                }
            }
            
        }
    }
    else{
        $salesRankData .= "0";
        $salesRankLabel .= "'None'";
    }
    
    //$graphHeader .= $order == "fast" ? " (ASC)" : " (DESC)";
    $salesRankData = "[$salesRankData]";
    $salesRankLabel = "[$salesRankLabel]";
?>