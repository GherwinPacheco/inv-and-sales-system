<?php
    $limit = "LIMIT 5";
    if(isset($noLimit) and $noLimit){
        $limit = "";
    }
    if(isset($_GET["fromDate"]) and isset($_GET["toDate"])){
        $fd = strtotime($fromDate);
        $td = strtotime($toDate);
        $datediff = $td - $fd;
        $datediff = round($datediff / (60 * 60 * 24)) + 1;
        
        $dateCondition = date("Y-m-d", strtotime($fromDate));
        for($i=1; $i<=$datediff; $i++){
            $result = $db->sql("
                SELECT 
                    SUM(`total`) as `total`, 
                    COUNT(*) as `transactionCount`, 
                    (SELECT SUM(`qty`) AS `qty` FROM (
                        SELECT IF(`quantity_type` > 1, (`quantity` * `bundle`), `quantity`) AS `qty`, `transactions`.`date_added`
                        FROM `transaction_items` 
                        INNER JOIN `transactions` ON `transactions`.`transaction_id` = `transaction_items`.`transaction_id`
                        WHERE 1
                    ) AS `productCount` WHERE `date_added` = '$dateCondition') AS `productCount`,
                    `date_added` 
                FROM `transactions` 
                WHERE `date_added` = '$dateCondition'
                GROUP BY `date_added` ORDER BY `date_added` ASC
            ");
    
            if($result->num_rows > 0){
                while($row = $result->fetch_assoc()){
                    $totalData .= round($row["total"],2);
                    $transactionCount .= $row["transactionCount"];
                    $productCount .= $row["productCount"];
                    $dateLabel .= "'".date("M d Y", strtotime($row["date_added"]))."'";
                    $dateValue .= "'fromDate=".date("Y-m-d", strtotime($row["date_added"]))."'";
                    
                }
            }
            else{
                $totalData .= "0";
                $transactionCount .= "0";
                $productCount .= "0";
                $dateLabel .= "'".date("M d Y", strtotime("$dateCondition"))."'";
                $dateValue .= "'fromDate=".date("Y-m-d", strtotime($dateCondition))."'";
                
            }

            if($i < $datediff){
                $totalData .= ", ";
                $transactionCount .= ", ";
                $productCount .= ", ";
                $dateLabel .= ", ";
                $dateValue .= ", ";
            }
            
            $dateCondition = date('Y-m-d', strtotime("+1 day", strtotime($dateCondition)));
        }
    }
    else{
        if($salesGraphType == "daily"){
            for($i=1; $i<=$day; $i++){
                $result = $db->sql("
                    SELECT 
                        SUM(`total`) as `total`, 
                        COUNT(*) as `transactionCount`, 
                        (SELECT SUM(`qty`) AS `qty` FROM (
                            SELECT IF(`quantity_type` > 1, (`quantity` * `bundle`), `quantity`) AS `qty`, `transactions`.`date_added`
                            FROM `transaction_items` 
                            INNER JOIN `transactions` ON `transactions`.`transaction_id` = `transaction_items`.`transaction_id`
                            WHERE 1
                        ) AS `productCount` WHERE YEAR(`date_added`) = '$year' AND MONTH(`date_added`) = '$month' AND DAY(`date_added`) = '$i') AS `productCount`,
                        `date_added` 
                    FROM `transactions` 
                    WHERE YEAR(`date_added`) = '$year' AND MONTH(`date_added`) = '$month' AND DAY(`date_added`) = '$i'
                    GROUP BY `date_added` ORDER BY `date_added` ASC
                ");
                
                if($result->num_rows > 0){
                    while($row = $result->fetch_assoc()){
                        $totalData .= round($row["total"],2);
                        $transactionCount .= $row["transactionCount"];
                        $productCount .= $row["productCount"];
                        $dateLabel .= "'".date("M d", strtotime($row["date_added"]))."'";
                        $dateValue .= "'fromDate=".date("Y-m-d", strtotime($row["date_added"]))."'";
                        
                    }
                }
                else{
                    $m = date("m", strtotime($year."-".$month."-".$i));
                    $d = date("d", strtotime($year."-".$month."-".$i));
                    $totalData .= "0";
                    $transactionCount .= "0";
                    $productCount .= "0";
                    $dateLabel .= "'".date("M d", strtotime("$year-$m-$d"))."'";
                    $dateValue .= "'fromDate=".date("Y-m-d", strtotime("$year-$m-$d"))."'";
                    
                }
                
                if($i < $day){
                    $totalData .= ", ";
                    $transactionCount .= ", ";
                    $productCount .= ", ";
                    $dateLabel .= ", ";
                    $dateValue .= ", ";
                }

                
            }
        }
        elseif($salesGraphType == "weekly"){
            $x = array(0, 7, 14, 21, 28);
            for($h=0; $h<4; $h++){
                $total = 0;
                $trCount = 0;
                $prCount = 0;
                $result = $db->sql("
                    SELECT 
                        SUM(`total`) as `total`, 
                        COUNT(*) as `transactionCount`, 
                        (SELECT SUM(`qty`) AS `qty` FROM (
                            SELECT IF(`quantity_type` > 1, (`quantity` * `bundle`), `quantity`) AS `qty`, `transactions`.`date_added`
                            FROM `transaction_items` 
                            INNER JOIN `transactions` ON `transactions`.`transaction_id` = `transaction_items`.`transaction_id`
                            WHERE 1
                        ) AS `productCount` WHERE YEAR(`date_added`) = '$year' AND MONTH(`date_added`) = '$month' AND DAY(`date_added`) BETWEEN ".($x[$h]+1)." AND ".$x[$h+1].") AS `productCount`,
                        `date_added` 
                    FROM `transactions` 
                    WHERE YEAR(`date_added`) = '$year' AND MONTH(`date_added`) = '$month' AND DAY(`date_added`) BETWEEN ".($x[$h]+1)." AND ".$x[$h+1]."
                    ORDER BY `date_added` ASC
                ");
                while($row = $result->fetch_assoc()){
                    $total += round($row["total"],2);
                    $trCount += $row["transactionCount"];
                    $prCount += $row["productCount"];
                }
                    
                    

                $totalData .= $total;
                $transactionCount .= $trCount;
                $productCount .= $prCount;
                $dateLabel .= "'"."Week ".($h+1)."'";
                $dateValue .= "'fromDate=".date("Y-m-d", strtotime("$year-$month-".$x[$h]+1))."&toDate=".date("Y-m-d", strtotime("$year-$month-".$x[$h+1]))."'";
                
                if($h < 3){
                    $totalData .= ", ";
                    $transactionCount .= ", ";
                    $productCount .= ", ";
                    $dateLabel .= ", ";
                    $dateValue .= ", ";
                }
                
            }
            
        }
        elseif($salesGraphType == "monthly"){
            $monthCount = $year == date("Y") ? date("m") : 12;
            for($h=0; $h < $monthCount; $h++){
                $total = 0;
                $trCount = 0;
                $prCount = 0;
                $result = $db->sql("
                    SELECT 
                        SUM(`total`) as `total`, 
                        COUNT(*) as `transactionCount`, 
                        (SELECT SUM(`qty`) AS `qty` FROM (
                            SELECT IF(`quantity_type` > 1, (`quantity` * `bundle`), `quantity`) AS `qty`, `transactions`.`date_added`
                            FROM `transaction_items` 
                            INNER JOIN `transactions` ON `transactions`.`transaction_id` = `transaction_items`.`transaction_id`
                            WHERE 1
                        ) AS `productCount` WHERE YEAR(`date_added`) = '$year' AND MONTH(`date_added`) = ".($h + 1).") AS `productCount`,
                        `date_added` 
                    FROM `transactions` 
                    WHERE YEAR(`date_added`) = '$year' AND MONTH(`date_added`) = ".($h + 1)."
                    GROUP BY CONCAT(YEAR(`date_added`), '-', MONTH(`date_added`)) ORDER BY CONCAT(YEAR(`date_added`), '-', MONTH(`date_added`)) ASC
                ");
                while($row = $result->fetch_assoc()){
                    $total += round($row["total"],2);
                    $trCount += $row["transactionCount"];
                    $prCount += $row["productCount"];
                    
                }
                $maxDay = date("t", strtotime("$year-$month-1"));
                $totalData .= $total;
                $transactionCount .= $trCount;
                $productCount .= $prCount;
                $dateLabel .= "'".date("M Y", strtotime("$year-".($h+1)."-1"))."'";
                $dateValue .= "'fromDate=".date("Y-m-d", strtotime("$year-".($h+1)."-1"))."&toDate=".date("Y-m-d", strtotime("$year-".($h+1)."-$maxDay"))."'";
                if($h < $monthCount-1){
                    $totalData .= ", ";
                    $transactionCount .= ", ";
                    $productCount .= ", ";
                    $dateLabel .= ", ";
                    $dateValue .= ", ";
                }
                
            }
        }
        elseif($salesGraphType == "yearly"){
            $minDate = $db->sql("SELECT YEAR(MIN(`date_added`)) AS `minDate` FROM `transactions`")->fetch_assoc()["minDate"];
            for($h = $minDate; $h <= date("Y"); $h++){
                $result = $db->sql("
                    SELECT 
                        SUM(`total`) as `total`,
                        COUNT(*) as `transactionCount`, 
                        (SELECT SUM(`qty`) AS `qty` FROM (
                            SELECT IF(`quantity_type` > 1, (`quantity` * `bundle`), `quantity`) AS `qty`, `transactions`.`date_added`
                            FROM `transaction_items` 
                            INNER JOIN `transactions` ON `transactions`.`transaction_id` = `transaction_items`.`transaction_id`
                            WHERE 1
                        ) AS `productCount` WHERE YEAR(`date_added`) = '$h') AS `productCount`
                    FROM `transactions`
                    WHERE YEAR(`date_added`) = '$h'
                ");
                while($row = $result->fetch_assoc()){
                    $totalData .= round($row["total"],2);
                    $transactionCount .= $row["transactionCount"];
                    $productCount .= $row["productCount"];
                    $dateLabel .= "'".$h."'";
                    $dateValue .= "'fromDate=$h-01-01&toDate=$h-12-31'";
                    if($h <= date("Y")-1){
                        $totalData .= ", ";
                        $transactionCount .= ", ";
                        $productCount .= ", ";
                        $dateLabel .= ", ";
                        $dateValue .= ", ";
                    }
                }
                
            }
            
        }
    }
    

    //echo $totalData."<br>".$transactionCount."<br>".$productCount;
    
    $totalData = "[$totalData]";
    $transactionCount = "[$transactionCount]";
    $productCount = "[$productCount]";
    $dateLabel = "[$dateLabel]";
    $dateValue = "[$dateValue]";

    $order = "1";
    $condition = "";
    if($salesRankType == "Top"){
        $order = "DESC";
    }
    else{
        $order = "ASC";
    }

    if($salesRankTime == "all time"){
        $condition = "1";
    }
    elseif($salesRankTime == "today"){
        $condition = "`transactions`.`date_added` = CURDATE()";
    }
    elseif($salesRankTime == "yesterday"){
        $condition = "`transactions`.`date_added` = CURDATE() - INTERVAL 1 DAY";
    }
    elseif($salesRankTime == "last 7 days"){
        $condition = "`transactions`.`date_added` BETWEEN '".date("Y-m-d", strtotime("-7 day"))."' AND '".date("Y-m-d")."'";
    }
    elseif($salesRankTime == "this month"){
        $condition = "YEAR(`transactions`.`date_added`) = YEAR(CURDATE()) AND MONTH(`transactions`.`date_added`) = MONTH(CURDATE())";
    }
    elseif($salesRankTime == "last month"){
        $condition = "YEAR(`transactions`.`date_added`) = YEAR(CURDATE()) AND MONTH(`transactions`.`date_added`) = MONTH(CURDATE() - INTERVAL 1 MONTH)";
    }
    elseif($salesRankTime == "this year"){
        $condition = "YEAR(`transactions`.`date_added`) = YEAR(CURDATE())";
    }
    elseif($salesRankTime == "last year"){
        $condition = "YEAR(`transactions`.`date_added`) = YEAR(CURDATE() - INTERVAL 1 YEAR)";
    }

    $salesRank = $db->sql("
        SELECT `product_id`, `product_name`, `properties`, SUM(`qty`) AS `quantity`, SUM(`subtotal`) AS `total` FROM (
            SELECT `transaction_items`.`product_id`, `products`.`product_name`, `products`.`properties`, IF(`quantity_type` > 1, `transaction_items`.`bundle` * `quantity`, `quantity`) AS `qty`, `subtotal`
            FROM `transaction_items`
                INNER JOIN `products` ON `transaction_items`.`product_id` = `products`.`product_id`
                INNER JOIN `transactions` ON `transaction_items`.`transaction_id` = `transactions`.`transaction_id`
            WHERE $condition
        ) AS `salesRank`
        GROUP BY `product_id`
        ORDER BY `total` $order, `quantity` $order
        $limit;
    ");

    $count = 0;
    if($salesRank->num_rows > 0){
        while($row = $salesRank->fetch_assoc()){
            $salesRankData .= $row["total"];
            $salesRankData2 .= $row["quantity"];
            $salesRankLabel .= "'".$row["product_name"].' '.$row["properties"]."'";
            if($count < 5){
                $salesRankData .= ", ";
                $salesRankData2 .= ", ";
                $salesRankLabel .= ", ";
            }
        }
    }
    else{
        $salesRankData .= "0";
        $salesRankData2 .= "0";
        $salesRankLabel .= "'None'";
    }
    

    $salesRankData = "[$salesRankData]";
    $salesRankData2 = "[$salesRankData2]";
    $salesRankLabel = "[$salesRankLabel]";
?>