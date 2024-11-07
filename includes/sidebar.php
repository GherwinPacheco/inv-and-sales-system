 <!--Side Bar-->
<div class="sidebar" id="sidebar">
    <ul class="navbar-nav mr-auto" style="position: relative; height: 100%">
        <li class="nav-item logo-nav" style="margin-bottom: 50px; display: flex; align-items: center">
            <a href="#"><img class="logo" src="./assets/logo.png?t=<?=time()?>"></a>
            <?php
                $col1 = "rgb(66, 135, 255)";
                $col2 = "rgb(144, 194, 59)";
                $activeCol = 1;
                $words = explode(" ", $db->sql("SELECT `store_name` FROM `store_details` WHERE `id` ")->fetch_assoc()["store_name"]);
                $storeName = "";
                for($x = 0; $x < count($words); $x++){
                    $col = $activeCol == 1 ? $col1 : $col2;
                    $storeName .= '<span style="color: '.$col.'">'.strtoupper($words[$x]).'</span>';


                    $activeCol = $activeCol == 1 ? 2 : 1;
                    if($x != count($words)+1){
                        $storeName .= '&nbsp;';
                    }
                }
            ?>
            <?=$storeName?>
        </li>

        <li class="nav-item">
            <a class="nav-link <?php echo $main == "Dashboard" ? "active-nav" : ""; ?>" href="./dashboard.php"><i class="fa-solid fa-house nav-icon"></i>Dashboard</a>
        </li>
        <li class="nav-item">
            <a class="nav-link link-main <?php echo $main == "Inventory" ? "active-nav" : ""; ?>" href="#" data-toggle="collapse" data-target="#inventory-sub" aria-expanded="false" aria-controls="inventory-sub">
            <i class="fa-solid fa-box-open nav-icon"></i>Inventory<i class="fa-solid fa-chevron-right nav-collapse-icon"></i></a>
            <div class="collapse nav-collapse" id="inventory-sub">
                <a class="nav-link link-sub <?php echo $sub == "product-list" ? "active-nav" : ""; ?>" href="./inv-page-productList.php">Product List</a>
                <a class="nav-link link-sub <?php echo $sub == "return-list" ? "active-nav" : ""; ?>" href="./inv-page-returnList.php">Return List</a>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link link-main <?php echo $main == "Point of Sale" ? "active-nav" : ""; ?>" href="#" data-toggle="collapse" data-target="#pos-sub" aria-expanded="false" aria-controls="pos-sub">
            <i class="fa-solid fa-cash-register nav-icon"></i>Point of Sale<i class="fa-solid fa-chevron-right nav-collapse-icon"></i></a>
            <div class="collapse nav-collapse" id="pos-sub">
                <a class="nav-link link-sub <?php echo $sub == "cashier" ? "active-nav" : ""; ?>" href="./pos-page-cashier.php">Cashier</a>
                <a class="nav-link link-sub <?php echo $sub == "transactions" ? "active-nav" : ""; ?>" href="./pos-page-transactions.php">Transaction History</a>
            </div>
        </li>
        <li class="admin-privileges nav-item">
            <a class="nav-link link-main <?php echo $main == "Reports" ? "active-nav" : ""; ?>" href="#" data-toggle="collapse" data-target="#reports-sub" aria-expanded="false" aria-controls="reports-sub">
            <i class="fa-solid fa-chart-line nav-icon"></i>Reports<i class="fa-solid fa-chevron-right nav-collapse-icon"></i></a>
            <div class="collapse nav-collapse" id="reports-sub">
                <a class="nav-link link-sub <?php echo $sub == "inventory-report" ? "active-nav" : ""; ?>" href="./reports-page-inventory.php">Inventory Reports</a>
                <a class="nav-link link-sub <?php echo $sub == "sales-report" ? "active-nav" : ""; ?>" href="./reports-page-sales.php">Sales Reports</a>
            </div>
        </li>
        <li class="admin-privileges nav-item">
            <a class="nav-link link-main <?php echo $main == "Configurations" ? "active-nav" : ""; ?>" href="#" data-toggle="collapse" data-target="#config-sub" aria-expanded="false" aria-controls="config-sub">
            <i class="fa-sharp fa-solid fa-gear nav-icon"></i>Configurations<i class="fa-solid fa-chevron-right nav-collapse-icon"></i></a>
            <div class="collapse nav-collapse" id="config-sub">
                <a class="nav-link link-sub <?php echo $sub == "system-settings" ? "active-nav" : ""; ?>" href="./config-page-systemSettings.php">System Settings</a>
                <a class="admin-privileges nav-link link-sub <?php echo $sub == "inv-settings" ? "active-nav" : ""; ?>" href="./inv-page-settings.php">Inventory Settings</a>
                <a class="admin-privileges nav-link link-sub <?php echo $sub == "pos-settings" ? "active-nav" : ""; ?>" href="./pos-page-settings.php">POS Settings</a>
                <a class="nav-link link-sub <?php echo $sub == "manage-account" ? "active-nav" : ""; ?>" href="./config-page-manageAccount.php">Manage Account</a>
            </div>
        </li>

    </ul>
</div>

<script>
    $("#sidebar").hover(function(){
        $("#main-div").css("margin-left", "280px");
    },
    function(){
        $("#main-div").css("margin-left", "75px");
        $(".nav-collapse").collapse("hide");
    });

    $(".link-main").click(function(){
        $(".nav-collapse").collapse("hide");
    });

</script>