<!--Pagination-->
<?php
    $pageCount = "";
    $offsett = 0;
    $limit = isset($limit) ? $limit : 10;
    
    //set the offset for the query
    $offset = ($page - 1) * $limit;

    // get the product count
    $itemCount = $db->sql("
        SELECT COUNT(*) as `item_count`
        FROM (
            $query
        ) as `item_count`
        WHERE $condition
    ")->fetch_assoc()["item_count"];

    //get the amount of pages
    $pageCount = (int)($itemCount / $limit);
    if($pageCount >= 1 and ($itemCount % $limit) > 0){
        $pageCount += 1;
    }
    elseif($pageCount >= 1 and ($itemCount % $limit) == 0){
        $pageCount == $pageCount;
    }
    else{
        $pageCount = 1;
    }


    //get the search and filter values if there is any
    $searchForm = isset($_GET["search"]) ? 
        '<input type="hidden" name="search" value="'.$_GET["search"].'">' : 
        "";
    $categoryForm = isset($_GET["category-filter"]) ? 
        '<input type="hidden" name="category-filter" value="'.$_GET["category-filter"].'">' : 
        "";
    $stockForm = isset($_GET["stock-filter"]) ? 
        '<input type="hidden" name="stock-filter" value="'.$_GET["stock-filter"].'">' : 
        "";
    $expirationForm = isset($_GET["expiration-filter"]) ? 
        '<input type="hidden" name="expiration-filter" value="'.$_GET["expiration-filter"].'">' : 
        "";
    $returnForm = isset($_GET["returnStatus"]) ? 
        '<input type="hidden" name="returnStatus" value="'.$_GET["returnStatus"].'">' : 
        "";
    $fromDate = isset($_GET["fromDate"]) ? 
        '<input type="hidden" name="fromDate" value="'.$_GET["fromDate"].'">' : 
        "";
    $toDate = isset($_GET["toDate"]) ? 
        '<input type="hidden" name="toDate" value="'.$_GET["toDate"].'">' : 
        "";
    $tableTypeForm = isset($_GET["tableType"]) ? 
        '<input type="hidden" name="tableType" value="'.$_GET["tableType"].'">' : 
        "";
    $moduleForm = isset($_GET["module"]) ? 
        '<input type="hidden" name="module" value="'.$_GET["module"].'">' : 
        "";
    $disabledAccForm = isset($_GET["dA"]) ? 
        '<input type="hidden" name="dA" value="'.$_GET["dA"].'">' : 
        "";

    $forms = $searchForm.$categoryForm.$stockForm.$expirationForm.$returnForm.$fromDate.$toDate.$tableTypeForm.$moduleForm.$disabledAccForm;

    
    $paginationNums = "";

    //set the first and last button
    $frs = true;
    $firstButton = '
        <li class="page-item">
            <form action="'.$pageLocation.'" method="get">'.$forms.'
                <button type="submit" class="page-link" name="page" value="1" data-toggle="tooltip" data-placement="bottom" title="First"><i class="fa-solid fa-angles-left"></i></button>
            </form>
        </li>
    ';
    $lst = true;
    $lastButton = '
        <li class="page-item">
            <form action="'.$pageLocation.'" method="get">'.$forms.'
                <button type="submit" class="page-link" name="page" value="'.$pageCount.'" data-toggle="tooltip" data-placement="bottom" title="Last"><i class="fa-solid fa-angles-right"></i></button>
            </form>
        </li>
    ';

    //center the page properly
    $b = 0;
    if($page < 3){
        $b = 0;
        
    }
    elseif($page > 3 and ($page > ($pageCount - 2))){
        $b = $page - (5 - ($pageCount - $page)) ;
        
    }
    else{
        $b = $page - 3;
        
    }

    //determine in the first and last button will appear
    if($page < 4 and $pageCount <= 5){
        $frs = false;
        $lst = false;
    }
    elseif($page < 4 and $pageCount >= 6){
        $frs = false;
        $lst = true;
    }
    elseif($page > 3 and ($page > ($pageCount - 2))){
        $frs = true;
        $lst = false;
    }
    else{
        $frs = true;
        $lst = true;
    }

    //set the paginations
    $count = 0;
    for($x = $b; $x < $pageCount; $x++){
        if(($x + 1) == 0){
            continue;
        }
        if($count == 5){ break; }
        $countctive = ""; $disabled = "";
        if(($x + 1) == $page){
            $countctive = "active";
            $disabled = "disabled";
        }
        
        $paginationNums .= '
        <li class="page-item '.$countctive.'">
            <form action="'.$pageLocation.'" method="get">'.$forms.'
                <button type="submit" class="page-link" name="page" value="'.($x + 1).'" '.$disabled.'>'.($x + 1).'</button>
            </form>
        </li>
        ';
        $count++;
    }

    $pagination = '
    <div class="row">
        <div class="col">
            <nav id="pagination" class="mt-5" aria-label="Page navigation example">
                <ul class="pagination justify-content-center">
                    '.($frs == true ? $firstButton: "").'
                    <li class="page-item '.($page == 1 ? "disabled" : "").' ">
                        <form action="'.$pageLocation.'" method="get">
                            '.$forms.'
                            <button type="submit" class="page-link" name="page" value="'.($page - 1).'" data-toggle="tooltip" data-placement="bottom" title="Previous"><i class="fa-solid fa-chevron-left"></i></button>
                        </form>
                    </li>
                    '.$paginationNums.'
                    <li class="page-item '.($page == $pageCount ? "disabled" : "").'">
                        <form action="'.$pageLocation.'" method="get">
                            '.$forms.'
                            <button type="submit" class="page-link" name="page" value="'.($page + 1).'"><i class="fa-solid fa-chevron-right" data-toggle="tooltip" data-placement="bottom" title="Next"></i></button>
                        </form>
                    </li>
                    '.($lst == true ? $lastButton: "").'
                </ul>
            </nav>
        </div>
    </div>

    ';

    
?>



