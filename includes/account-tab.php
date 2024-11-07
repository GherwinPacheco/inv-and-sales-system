<?php
    $user = $_SESSION["user"];
    $username = $db->sql("SELECT `user_name` FROM `accounts` WHERE `user_id` = '$user'")->fetch_assoc()["user_name"];
?>
<a class="dropdown" id="account-menu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    <img class="profile-pic rounded-circle" src="./assets/profiles/user_<?=$user?>.png?t=<?=time()?>" alt="profile_image">
</a>
<span class="account-name lead"><b><?=$username?></b>&emsp;</span>

<div class="dropdown-menu" aria-labelledby="account-button">
    <a class="dropdown-item" href="./account-page.php">
        <i class="fa-solid fa-user"></i>&emsp;My Account
    </a>
    <a class="dropdown-item" href="./logout.php">
        <i class="fa-solid fa-arrow-right-from-bracket"></i>&emsp;Logout
    </a>
</div>