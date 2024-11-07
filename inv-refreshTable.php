<?php
    session_start();
    include("./includes/login-check.php");
    
    include("./includes/database.php");
    $db = new Database();
    
    include ("./includes/inventory-functions.php");
    $inv = new Inventory($db);
    
    $inv->updateBundleQuantity("all");
    $inv->updateQuantity("all");
    $inv->updateStockStatus();
    $inv->updateExpirationStatus();
    
    header("Location: inv-page-productList.php");
?>